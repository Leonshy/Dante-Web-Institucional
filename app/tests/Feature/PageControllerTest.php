<?php

use App\Models\Page;

it('muestra una página publicada con el contenido real de sus bloques', function () {
    $page = Page::factory()->create([
        'slug' => 'institucion/historia',
        'title' => ['es' => 'Historia'],
        'status' => 'published',
        'blocks' => [
            [
                'type' => 'texto',
                'data' => ['content' => ['es' => '<p>Contenido real de la página.</p>']],
            ],
        ],
    ]);

    $this->get('/'.$page->slug)
        ->assertOk()
        ->assertSee('Historia')
        ->assertSee('Contenido real de la página.', false);
});

it('resuelve los campos dentro de repetidores de bloques (FAQ)', function () {
    $page = Page::factory()->create([
        'slug' => 'admisiones',
        'status' => 'published',
        'blocks' => [
            [
                'type' => 'faq',
                'data' => [
                    'items' => [
                        ['question' => ['es' => '¿Cómo me inscribo?'], 'answer' => ['es' => 'Completando el formulario de admisiones.']],
                    ],
                ],
            ],
        ],
    ]);

    $this->get('/'.$page->slug)
        ->assertOk()
        ->assertSee('¿Cómo me inscribo?')
        ->assertSee('Completando el formulario de admisiones.');
});

it('da 404 en una página en borrador', function () {
    $page = Page::factory()->create(['slug' => 'sin-publicar', 'status' => 'draft']);

    $this->get('/'.$page->slug)->assertNotFound();
});

it('da 404 en una página que no existe', function () {
    $this->get('/esta-pagina-no-existe')->assertNotFound();
});

it('muestra un solo h1 aunque el primer bloque sea un hero', function () {
    $page = Page::factory()->create([
        'slug' => 'con-hero',
        'title' => ['es' => 'Título de la página'],
        'status' => 'published',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => ['es' => 'Título del hero']]],
        ],
    ]);

    $html = $this->get('/'.$page->slug)->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->toContain('Título del hero')
        ->not->toContain('Título de la página</h1>');
});

it('el breadcrumb no enlaza a una página padre en borrador', function () {
    $parent = Page::factory()->create(['slug' => 'institucion', 'title' => ['es' => 'Institución'], 'status' => 'draft']);
    $child = Page::factory()->create([
        'slug' => 'institucion/historia',
        'title' => ['es' => 'Historia'],
        'status' => 'published',
        'parent_id' => $parent->id,
    ]);

    $html = $this->get('/'.$child->slug)->assertOk()->getContent();

    expect($html)->toContain('Institución')
        ->not->toContain('href="/institucion"');
});

it('muestra el h1 con el título de la página cuando no hay bloque hero', function () {
    $page = Page::factory()->create([
        'slug' => 'sin-hero',
        'title' => ['es' => 'Título de la página'],
        'status' => 'published',
        'blocks' => [
            ['type' => 'texto', 'data' => ['content' => ['es' => '<p>Texto</p>']]],
        ],
    ]);

    $html = $this->get('/'.$page->slug)->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->toContain('Título de la página');
});
