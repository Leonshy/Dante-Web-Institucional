<?php

namespace App\Services\Media;

use App\Models\Media as MediaModel;
use enshrined\svgSanitize\Sanitizer as SvgSanitizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\AvifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Subida y reprocesamiento de medios — adapta el patrón de IPG
 * (docs/01-analisis-descubrimiento.md §A.4) agregando lo que a IPG le falta:
 * reprocesamiento real de imágenes (WebP) y sanitización de SVG antes de
 * aceptarlo. El antecedente del WordPress comprometido incluye una webshell
 * disfrazada de imagen (docs/01 §C.6) — el MIME real se verifica con `finfo`,
 * nunca por extensión declarada.
 */
class MediaUploadService
{
    public function __construct(private readonly SvgSanitizer $svgSanitizer) {}

    public function upload(UploadedFile $file, string $folder = 'general', ?string $alt = null): MediaModel
    {
        $realMime = $this->detectRealMime($file);

        if (! in_array($realMime, config('dante.media.allowed_mimes'), true)) {
            throw ValidationException::withMessages([
                'file' => "El tipo de archivo detectado ({$realMime}) no está permitido.",
            ]);
        }

        $this->assertNoDoubleExtension($file->getClientOriginalName());

        $randomName = Str::uuid()->toString().'.'.$this->extensionFor($realMime, $file);
        $path = trim($folder, '/').'/'.$randomName;

        $type = $this->typeFor($realMime);
        $svgSanitized = false;

        if ($realMime === 'image/svg+xml') {
            $clean = $this->svgSanitizer->sanitize($file->getContent());
            Storage::disk('media')->put($path, $clean);
            $svgSanitized = true;
        } elseif ($type === 'image' && $realMime !== 'image/gif') {
            // Reprocesar y volver a codificar el archivo original (no solo
            // guardar una copia intacta) — destruye cualquier payload que
            // viaje embebido en los bytes de la imagen (esteganografía,
            // polyglots tipo GIF89a/PHP). El antecedente del WordPress
            // comprometido incluía justamente un archivo "imagen" con
            // código ejecutable embebido (docs/01 §C.6). GIF queda afuera
            // porque Intervention Image no reencodea animaciones sin perder
            // los frames; se acepta el riesgo documentado en docs/10-seguridad.md §4.
            $this->reencodeAndStore($file, $path, $realMime);
        } else {
            Storage::disk('media')->putFileAs(
                dirname($path) === '.' ? '' : dirname($path),
                $file,
                basename($path),
            );
        }

        $conversions = [];

        if ($type === 'image' && $realMime !== 'image/svg+xml' && $realMime !== 'image/gif') {
            $conversions = $this->generateConversions($path, $realMime);
        }

        return MediaModel::query()->create([
            'user_id' => Auth::id(),
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $randomName,
            'mime_type' => $realMime,
            'path' => $path,
            'disk' => 'media',
            'size' => $file->getSize(),
            'type' => $type,
            'alt' => $alt,
            'folder' => $folder,
            'conversions' => $conversions,
            'svg_sanitized' => $svgSanitized,
        ]);
    }

    private function reencodeAndStore(UploadedFile $file, string $path, string $mime): void
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath($file->getRealPath());

        $encoder = match ($mime) {
            'image/png' => new PngEncoder,
            'image/webp' => new WebpEncoder(quality: 90),
            'image/avif' => new AvifEncoder(quality: 80),
            default => new JpegEncoder(quality: 90),
        };

        Storage::disk('media')->put($path, (string) $image->encode($encoder));
    }

    private function detectRealMime(UploadedFile $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        return $mime ?: 'application/octet-stream';
    }

    private function assertNoDoubleExtension(string $originalName): void
    {
        // ej. "logo.fw.php" — patrón exacto del webshell encontrado en el WP viejo
        // (docs/01-analisis-descubrimiento.md §C.6).
        $parts = explode('.', $originalName);

        if (count($parts) > 2) {
            $dangerous = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'exe', 'sh', 'htaccess'];
            $middleExtensions = array_slice($parts, 1, -1);

            foreach ($middleExtensions as $ext) {
                if (in_array(strtolower($ext), $dangerous, true)) {
                    throw ValidationException::withMessages([
                        'file' => 'El nombre del archivo no es válido.',
                    ]);
                }
            }
        }

        $lastExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (in_array($lastExt, ['php', 'phtml', 'phar', 'exe', 'sh', 'htaccess', 'cgi', 'pl'], true)) {
            throw ValidationException::withMessages([
                'file' => 'El tipo de archivo no está permitido.',
            ]);
        }
    }

    private function typeFor(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'image',
            str_starts_with($mime, 'video/') => 'video',
            default => 'document',
        };
    }

    private function extensionFor(string $mime, UploadedFile $file): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/avif' => 'avif',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'video/mp4' => 'mp4',
            default => $file->getClientOriginalExtension() ?: 'bin',
        };
    }

    /** @return array<string, string> */
    private function generateConversions(string $path, string $mime): array
    {
        $manager = new ImageManager(new Driver);
        $image = $manager->decodePath(Storage::disk('media')->path($path));

        $conversions = [];
        $webpPath = (string) preg_replace('/\.[^.]+$/', '-webp.webp', $path);
        Storage::disk('media')->put($webpPath, (string) $image->encode(new WebpEncoder(quality: 80)));
        $conversions['webp'] = $webpPath;

        foreach (config('dante.media.responsive_widths') as $width) {
            if ($image->width() <= $width) {
                continue;
            }

            $resized = clone $image;
            $resized->scaleDown(width: $width);
            $variantPath = (string) preg_replace('/\.[^.]+$/', "-{$width}w.webp", $path);
            Storage::disk('media')->put($variantPath, (string) $resized->encode(new WebpEncoder(quality: 80)));
            $conversions["w{$width}"] = $variantPath;
        }

        return $conversions;
    }
}
