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
