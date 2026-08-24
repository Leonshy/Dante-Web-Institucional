<?php

namespace App\Services\Migration;

use App\Models\Media;
use App\Services\Media\MediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Migración de `wp-content/uploads/` — CLAUDE.md §2 / docs/07-migracion-wordpress.md §4.
 *
 * Solo se migran archivos que:
 *  1. Están registrados como adjunto real en la biblioteca de medios de
 *     WordPress (`tV4yL_posts.post_type = 'attachment'`) — el resto del árbol
 *     de `uploads/` (caché de plugins, backups de WP File Manager, logs de
 *     Optimole) no es contenido editorial, se descarta y se registra.
 *  2. Pasan la lista blanca de extensión **y** el MIME real (`finfo`), sin
 *     doble extensión peligrosa — reutilizando exactamente la misma
 *     verificación que ya usa el panel (`MediaUploadService::upload`), no una
 *     nueva.
 *
 * Nunca hace `copy()` directo al disco: todo archivo aceptado pasa por el
 * mismo pipeline de subida que usa el panel (nombre aleatorio, SVG
 * sanitizado, conversión WebP).
 */
class WpMediaMigrator
{
    /** @var string patrón `-300x225` etc. — variantes autogeneradas por WordPress, no se migran */
    private const RESIZE_SUFFIX = '/-\d+x\d+(?=\.[a-zA-Z0-9]+$)/';

    public function __construct(private readonly MediaUploadService $uploadService) {}

    /**
     * @return array{
     *     migrated: int,
     *     already_migrated: int,
     *     discarded: array<int, array{path: string, reason: string}>,
     *     id_map: array<int, int>,
     *     image_key_map: array<string, string>,
     * }
     */
    public function migrate(string $uploadsPath, bool $dryRun = false): array
    {
        $uploadsPath = rtrim($uploadsPath, '/');
        $attachments = $this->fetchAttachments();

        $migrated = 0;
        $alreadyMigrated = 0;
        $discarded = [];
        $idMap = [];
        $imageKeyMap = [];
        $referenced = [];

        foreach ($attachments as $attachment) {
            $relativePath = $attachment->relative_path;

            if ($relativePath === null || $relativePath === '') {
                $discarded[] = ['path' => "(adjunto #{$attachment->id}, sin archivo asociado)", 'reason' => 'Registro de adjunto sin `_wp_attached_file` en postmeta — no hay archivo que migrar'];

                continue;
            }

            $referenced[strtolower($relativePath)] = true;
            $fullPath = $uploadsPath.'/'.ltrim($relativePath, '/');

            $existing = Media::query()->where('wp_legacy_id', $attachment->id)->first();

            if ($existing !== null) {
                $alreadyMigrated++;
                $idMap[$attachment->id] = $existing->id;
                $imageKeyMap[WpHtmlCleaner::normalizeImageKey($relativePath)] = $existing->url();

                continue;
            }

            if (! is_file($fullPath)) {
                $discarded[] = ['path' => $relativePath, 'reason' => 'Referenciado en la base pero el archivo no existe en el insumo `uploads/`'];

                continue;
            }

            if ($dryRun) {
                $migrated++;

                continue;
            }

            try {
                $media = DB::transaction(function () use ($fullPath, $relativePath, $attachment) {
                    $uploaded = new UploadedFile($fullPath, basename($relativePath), null, null, true);
                    $model = $this->uploadService->upload($uploaded, 'legacy-wp', null);
                    $model->forceFill(['wp_legacy_id' => $attachment->id])->save();

                    return $model;
                });

                $migrated++;
                $idMap[$attachment->id] = $media->id;
                $imageKeyMap[WpHtmlCleaner::normalizeImageKey($relativePath)] = $media->url();
            } catch (ValidationException $exception) {
                $discarded[] = [
                    'path' => $relativePath,
                    'reason' => 'Rechazado por el pipeline de subida: '.implode(' ', $exception->validator->errors()->all()),
                ];
            }
        }

        $discarded = array_merge($discarded, $this->scanOrphans($uploadsPath, $referenced));

        return [
            'migrated' => $migrated,
            'already_migrated' => $alreadyMigrated,
            'discarded' => $discarded,
            'id_map' => $idMap,
            'image_key_map' => $imageKeyMap,
        ];
    }

    /** @return array<int, object{id: int, relative_path: ?string, mime: string}> */
    private function fetchAttachments(): array
    {
        return DB::connection('wp_legacy')
            ->table('posts as p')
            ->leftJoin('postmeta as pm', function ($join) {
                $join->on('pm.post_id', '=', 'p.ID')->where('pm.meta_key', '_wp_attached_file');
            })
            ->where('p.post_type', 'attachment')
            ->select(['p.ID as id', 'pm.meta_value as relative_path', 'p.post_mime_type as mime'])
            ->orderBy('p.ID')
            ->get()
            ->all();
    }

    /**
     * Recorre todo `uploads/` y registra, con motivo, cualquier archivo que no
     * esté referenciado por un adjunto real de WordPress: caché/backups de
     * plugins, variantes de tamaño autogeneradas (Laravel genera las suyas),
     * huérfanos sin explicación, y cualquier extensión fuera de lista blanca.
     *
     * @param  array<string, bool>  $referenced  rutas relativas (minúsculas) de adjuntos reales
     * @return array<int, array{path: string, reason: string}>
     */
    private function scanOrphans(string $uploadsPath, array $referenced): array
    {
        if (! is_dir($uploadsPath)) {
            return [];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'pdf', 'docx', 'xlsx', 'pptx', 'mp4', 'webm'];
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'exe', 'sh', 'htaccess', 'js'];
        $noiseDirectories = ['wpcode', 'wp-file-manager-pro', 'optimole-logs', 'et_temp', 'wpcf7_uploads'];

        $entries = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadsPath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if (! $file->isFile()) {
                continue;
            }

            $relative = ltrim(str_replace($uploadsPath, '', $file->getPathname()), '/');
            $extension = strtolower($file->getExtension());
            $topDir = strtok($relative, '/');

            if (in_array($extension, $dangerousExtensions, true) || $this->hasDangerousDoubleExtension($file->getFilename())) {
                $entries[] = ['path' => $relative, 'reason' => "Extensión peligrosa ({$extension}) — descartado sin excepción, ver CLAUDE.md §2"];

                continue;
            }

            if (in_array($topDir, $noiseDirectories, true)) {
                $entries[] = ['path' => $relative, 'reason' => "Caché/backup del plugin `{$topDir}`, no es contenido editorial — fuera de alcance"];

                continue;
            }

            if (isset($referenced[strtolower($relative)])) {
                continue;
            }

            if (! in_array($extension, $allowedExtensions, true)) {
                $entries[] = ['path' => $relative, 'reason' => "Extensión fuera de la lista blanca ({$extension})"];

                continue;
            }

            if (preg_match(self::RESIZE_SUFFIX, $file->getFilename()) === 1) {
                $entries[] = ['path' => $relative, 'reason' => 'Variante de tamaño autogenerada por WordPress — Laravel genera sus propias conversiones responsivas, no hace falta migrarla'];

                continue;
            }

            $entries[] = ['path' => $relative, 'reason' => 'Huérfano: no está referenciado por ningún adjunto de la biblioteca de medios de WordPress'];
        }

        return $entries;
    }

    private function hasDangerousDoubleExtension(string $filename): bool
    {
        $parts = explode('.', $filename);

        if (count($parts) < 2) {
            return false;
        }

        $dangerous = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'exe', 'sh', 'htaccess'];

        foreach (array_slice($parts, 1) as $extension) {
            if (in_array(strtolower($extension), $dangerous, true)) {
                return true;
            }
        }

        return false;
    }
}
