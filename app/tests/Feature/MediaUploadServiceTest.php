<?php

use App\Services\Media\MediaUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Storage::fake('media');
});

it('sube una imagen y genera una conversión webp', function () {
    $file = UploadedFile::fake()->image('foto.jpg', 1600, 900);

    $media = app(MediaUploadService::class)->upload($file, 'noticias', 'Foto de la noticia');

    expect($media->type)->toBe('image')
        ->and($media->alt)->toBe('Foto de la noticia')
        ->and($media->conversions)->toHaveKey('webp');

    Storage::disk('media')->assertExists($media->path);
});

it('reprocesa la imagen y descarta cualquier byte extra embebido (payload tipo polyglot)', function () {
    $original = UploadedFile::fake()->image('foto.jpg', 200, 200)->getContent();
    $payload = "\n<?php system(\$_GET['c']); ?>\n";
    $withPayload = $original.$payload;

    $file = UploadedFile::fake()->createWithContent('foto.jpg', $withPayload);

    $media = app(MediaUploadService::class)->upload($file, 'general');

    $stored = Storage::disk('media')->get($media->path);

    expect($stored)->not->toContain('<?php')
        ->and($stored)->not->toBe($withPayload);
});

it('rechaza un archivo con doble extensión disfrazado de imagen', function () {
    $file = UploadedFile::fake()->createWithContent('logo.fw.php', '<?php echo "hola"; ?>');

    app(MediaUploadService::class)->upload($file, 'general');
})->throws(ValidationException::class);

it('sanitiza un SVG antes de guardarlo', function () {
    $svgWithScript = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><circle r="5"/></svg>';
    $file = UploadedFile::fake()->createWithContent('logo.svg', $svgWithScript);

    $media = app(MediaUploadService::class)->upload($file, 'general');

    expect($media->svg_sanitized)->toBeTrue();

    $stored = Storage::disk('media')->get($media->path);
    expect($stored)->not->toContain('<script');
});

it('rechaza tipos de archivo no permitidos', function () {
    $file = UploadedFile::fake()->create('archivo.exe', 10, 'application/x-msdownload');

    app(MediaUploadService::class)->upload($file, 'general');
})->throws(ValidationException::class);
