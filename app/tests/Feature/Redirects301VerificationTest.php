<?php

use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Verificación final del mapa de redirecciones 301 (docs/08-seo.md §5,
 * `docs/redirecciones-301.csv`, 39 filas). Es la única parte de la Fase 6
 * que, si falla, no se puede arreglar después sin haber perdido posiciones
 * — se verifica una por una, no solo que el middleware funcione en general
 * (eso ya lo cubre RedirectMiddlewareTest.php).
 */
function loadRedirectCsvRows(): array
{
    $path = base_path('../docs/redirecciones-301.csv');

    if (! File::exists($path)) {
        $path = base_path('docs/redirecciones-301.csv');
    }

    $rows = array_map('str_getcsv', file($path));
    $header = array_map('trim', array_shift($rows));

    return collect($rows)
        ->filter(fn ($row) => count($row) >= 2 && $row[0] !== '')
        ->map(fn ($row) => array_combine($header, $row))
        ->values()
        ->all();
}

beforeEach(function () {
    Artisan::call('dante:import-redirects');
});

it('carga las 39 filas reales del mapa de redirecciones, todas activas y en 301', function () {
    expect(Redirect::query()->count())->toBe(39);
    expect(Redirect::query()->where('is_active', true)->count())->toBe(39);
    expect(Redirect::query()->where('status_code', '!=', 301)->count())->toBe(0);
});

it('el mapa de redirecciones no tiene cadenas: ningún destino es a la vez un origen de otra fila', function () {
    $rows = loadRedirectCsvRows();

    // Las filas que no cambian de URL (from === to) son un caso ya cubierto
    // y guardado explícitamente por App\Http\Middleware\HandleRedirects (no
    // es una cadena real, la guarda ya evita el loop en producción) — se
    // excluyen del conjunto de "orígenes" usado para detectar cadenas de
    // verdad entre filas distintas.
    $realFromPaths = collect($rows)
        ->filter(fn ($row) => (rtrim($row['url_nueva'], '/') ?: '/') !== (rtrim($row['url_vieja'], '/') ?: '/'))
        ->map(fn ($row) => rtrim($row['url_vieja'], '/') ?: '/')
        ->unique();

    $realChains = collect($rows)->filter(function ($row) use ($realFromPaths) {
        $from = rtrim($row['url_vieja'], '/') ?: '/';
        $to = rtrim($row['url_nueva'], '/') ?: '/';

        return $to !== $from && $realFromPaths->contains($to);
    });

    expect($realChains)->toHaveCount(0);
});

it('cada URL destino del mapa resuelve sin dar 404 (páginas y noticias reales, publicadas)', function () {
    // El contenido real ya lo verificó la Fase 5 (docs/07-migracion-wordpress.md);
    // acá se prueba que la ESTRUCTURA de rutas resuelve, publicando fixtures
    // con el mismo slug que cada destino del mapa.
    $rows = loadRedirectCsvRows();
    $toPaths = collect($rows)->map(fn ($row) => rtrim($row['url_nueva'], '/') ?: '/')->unique();

    foreach ($toPaths as $path) {
        if ($path === '/' || in_array($path, ['/contacto', '/documentos', '/noticias', '/admisiones'], true)) {
            // Rutas fijas o páginas ya seedeadas en Fase 3 — se verifican
            // directo sin fixture adicional.
            continue;
        }

        if (str_starts_with($path, '/noticias/')) {
            Post::factory()->create([
                'slug' => Str::after($path, '/noticias/'),
                'status' => 'published',
                'published_at' => now(),
            ]);

            continue;
        }

        Page::factory()->create([
            'slug' => ltrim($path, '/'),
            'status' => 'published',
        ]);
    }

    // Páginas/rutas fijas de la excepción de arriba.
    Page::query()->updateOrCreate(['slug' => 'admisiones'], ['status' => 'published', 'title' => ['es' => 'Admisiones']]);

    foreach ($toPaths as $path) {
        $response = $this->get($path);

        expect($response->status())
            ->not->toBe(404, "El destino {$path} del mapa de redirecciones da 404.");
    }
});

it('ninguna de las 39 URLs viejas del mapa redirige a un 404 real', function () {
    $rows = loadRedirectCsvRows();

    // Fixtures mínimas para que los destinos con contenido real no den 404
    // (misma lógica que el test anterior).
    Page::query()->updateOrCreate(['slug' => 'admisiones'], ['status' => 'published', 'title' => ['es' => 'Admisiones']]);

    foreach (collect($rows)->pluck('url_nueva')->unique() as $to) {
        $to = rtrim($to, '/') ?: '/';

        if (in_array($to, ['/', '/contacto', '/documentos', '/noticias', '/admisiones'], true)) {
            continue;
        }

        if (str_starts_with($to, '/noticias/')) {
            Post::query()->firstOrCreate(
                ['slug' => Str::after($to, '/noticias/')],
                ['status' => 'published', 'published_at' => now(), 'title' => ['es' => 'Noticia'], 'excerpt' => ['es' => 'x'], 'content' => ['es' => 'x']]
            );

            continue;
        }

        Page::query()->firstOrCreate(['slug' => ltrim($to, '/')], ['status' => 'published', 'title' => ['es' => 'Página']]);
    }

    foreach ($rows as $row) {
        $from = rtrim($row['url_vieja'], '/') ?: '/';
        $to = rtrim($row['url_nueva'], '/') ?: '/';

        $response = $this->get($from);

        if ($from === $to) {
            // Fila que no cambia de URL (ej. "/noticias/" → "/noticias") — la
            // guarda de App\Http\Middleware\HandleRedirects la anula a
            // propósito para no producir un loop, así que acá se sirve la
            // página real (200), no un 301. Ver test de "no tiene cadenas".
            $response->assertStatus(200);

            continue;
        }

        $response->assertStatus(301);
        expect($response->headers->get('Location'))->not->toContain('http://dante-web.test/http');
    }
});
