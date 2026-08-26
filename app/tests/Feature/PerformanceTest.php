<?php

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

// Fase 7 — docs/09-rendimiento.md §6. Cobertura de la caché de consulta
// (invalidación al guardar/borrar desde el panel) y de que las secciones del
// inicio y el listado de noticias no vuelvan a generar N+1 (regla de
// CLAUDE.md §6.5: nada se da por terminado sin test).

it('sirve páginas y noticias cacheadas con el driver `database` real (no solo `array`)', function () {
    // Regresión real: `config('cache.serializable_classes')` (Laravel 13)
    // por defecto es `false`, lo que hace que `unserialize()` descarte
    // CUALQUIER objeto PHP al leer de la caché (para prevenir gadget chains,
    // ver config/cache.php) y devuelva `__PHP_Incomplete_Class` — rompía
    // esta página con un 500 en la segunda lectura, ya que las demás pruebas
    // de esta fase corren con `CACHE_STORE=array` (`phpunit.xml`), que nunca
    // serializa de verdad y por eso nunca lo detectó. Se prueba acá
    // explícitamente con el driver `database`, el mismo que se usaría en
    // Plesk sin Redis (CLAUDE.md §3).
    config(['cache.default' => 'database']);

    $page = Page::factory()->create([
        'slug' => 'institucion/pagina-cache-database',
        'title' => ['es' => 'Página real'],
        'status' => 'published',
        'cover_media_id' => Media::factory()->create(['type' => 'image'])->id,
    ]);

    $post = Post::factory()->create([
        'slug' => 'noticia-cache-database',
        'title' => ['es' => 'Noticia real'],
        'status' => 'published',
        'category_id' => Category::factory()->create(['type' => 'news'])->id,
        'featured_media_id' => Media::factory()->create(['type' => 'image'])->id,
    ]);

    $this->get('/'.$page->slug)->assertOk()->assertSee('Página real'); // primera lectura: cachea
    $this->get('/'.$page->slug)->assertOk()->assertSee('Página real'); // segunda lectura: desde caché real

    $this->get('/noticias/'.$post->slug)->assertOk()->assertSee('Noticia real');
    $this->get('/noticias/'.$post->slug)->assertOk()->assertSee('Noticia real');
});

it('sirve una página desde la caché de consulta tras la primera lectura', function () {
    $page = Page::factory()->create([
        'slug' => 'institucion/una-pagina-cacheada',
        'title' => ['es' => 'Título original'],
        'status' => 'published',
    ]);

    $this->get('/'.$page->slug)->assertOk()->assertSee('Título original');

    // Se edita directamente en base, sin pasar por el modelo (y por lo tanto
    // sin disparar el evento `saved` que invalida la caché) — simula lo que
    // vería un segundo visitante mientras la caché sigue vigente.
    DB::table('pages')->where('id', $page->id)->update([
        'title' => json_encode(['es' => 'Título cambiado sin invalidar']),
    ]);

    $this->get('/'.$page->slug)
        ->assertOk()
        ->assertSee('Título original')
        ->assertDontSee('Título cambiado sin invalidar');
});

it('invalida la caché de la página al guardarla desde el modelo', function () {
    $page = Page::factory()->create([
        'slug' => 'institucion/otra-pagina-cacheada',
        'title' => ['es' => 'Antes de editar'],
        'status' => 'published',
    ]);

    $this->get('/'.$page->slug)->assertOk()->assertSee('Antes de editar');

    $page->update(['title' => ['es' => 'Después de editar']]);

    $this->get('/'.$page->slug)
        ->assertOk()
        ->assertSee('Después de editar')
        ->assertDontSee('Antes de editar');
});

it('invalida la caché de la página en el slug viejo si se le cambia el slug', function () {
    $page = Page::factory()->create([
        'slug' => 'institucion/slug-viejo',
        'status' => 'published',
    ]);

    $this->get('/'.$page->slug)->assertOk();

    $page->update(['slug' => 'institucion/slug-nuevo']);

    $this->get('/institucion/slug-viejo')->assertNotFound();
    $this->get('/institucion/slug-nuevo')->assertOk();
});

it('invalida la caché de una noticia al editarla', function () {
    $post = Post::factory()->create([
        'slug' => 'una-noticia-cacheada',
        'title' => ['es' => 'Título original de la noticia'],
        'status' => 'published',
    ]);

    $this->get('/noticias/'.$post->slug)->assertOk()->assertSee('Título original de la noticia');

    $post->update(['title' => ['es' => 'Título editado de la noticia']]);

    $this->get('/noticias/'.$post->slug)
        ->assertOk()
        ->assertSee('Título editado de la noticia')
        ->assertDontSee('Título original de la noticia');
});

it('no repite consultas por fila al mostrar noticias con categoría e imagen en el inicio', function () {
    $this->get('/')->assertOk(); // calienta las cachés de configuración global, ver test de abajo

    $category = Category::factory()->create(['type' => 'news']);
    $image = Media::factory()->create(['type' => 'image']);

    Post::factory()->count(3)->create([
        'status' => 'published',
        'category_id' => $category->id,
        'featured_media_id' => $image->id,
        'published_at' => now(),
    ]);

    DB::enableQueryLog();
    $this->get('/')->assertOk();
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Con 3 noticias, `category`/`featuredMedia` no eager-cargadas dispararían
    // 3+3 consultas extra (una por fila y por relación). Con eager loading,
    // el total de consultas de la petición no depende de cuántas noticias haya.
    expect(count($queries))->toBeLessThan(40);
});

it('no repite consultas por fila en el listado de noticias', function () {
    // `SiteSetting::get()` y otras configuraciones globales usan su propia
    // caché (Cache::remember con TTL largo, patrón de IPG) que una vez
    // "tibia" ya no vuelve a consultar la base — se descarta una primera
    // petición de calentamiento para que no distorsione la comparación de
    // abajo, que solo quiere medir el costo de listar noticias.
    $this->get('/noticias')->assertOk();

    // Cada noticia con su propia categoría y su propia imagen (no la misma
    // para todas): si `category`/`featuredMedia` no estuvieran precargadas,
    // cada fila dispararía su propia consulta al acceder a `$post->category`
    // y a `$post->featuredMedia` en la vista.
    Post::factory()->count(6)->create([
        'status' => 'published',
        'published_at' => now(),
        'category_id' => fn () => Category::factory()->create(['type' => 'news'])->id,
        'featured_media_id' => fn () => Media::factory()->create(['type' => 'image'])->id,
    ]);

    DB::enableQueryLog();
    $this->get('/noticias')->assertOk();
    $queriesWithSix = count(DB::getQueryLog());
    DB::disableQueryLog();
    DB::flushQueryLog();

    Post::factory()->count(3)->create([
        'status' => 'published',
        'published_at' => now(),
        'category_id' => fn () => Category::factory()->create(['type' => 'news'])->id,
        'featured_media_id' => fn () => Media::factory()->create(['type' => 'image'])->id,
    ]);

    DB::enableQueryLog();
    $this->get('/noticias')->assertOk();
    $queriesWithNine = count(DB::getQueryLog());
    DB::disableQueryLog();

    // El número de consultas no debe crecer con la cantidad de noticias
    // listadas (eager loading de `category`/`featuredMedia`) — antes de la
    // corrección, cada noticia extra agregaba hasta 2 consultas más.
    expect($queriesWithNine)->toBe($queriesWithSix);
});
