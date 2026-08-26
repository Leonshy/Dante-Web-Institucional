<?php

use App\Models\Page;
use App\Models\Post;

beforeEach(function () {
    config(['dante.seo.block_indexing' => false]);
});

it('incluye canonical, Open Graph y JSON-LD de organización en una página publicada', function () {
    $page = Page::factory()->create([
        'slug' => 'institucion/historia',
        'title' => ['es' => 'Historia'],
        'status' => 'published',
        'is_indexable' => true,
        'blocks' => [['type' => 'texto', 'data' => ['content' => ['es' => '<p>Texto real.</p>']]]],
    ]);

    $response = $this->get('/'.$page->slug);

    $response->assertOk()
        ->assertSee('rel="canonical"', false)
        ->assertSee('og:title', false)
        ->assertSee('og:type" content="website"', false)
        ->assertSee('"@type":"EducationalOrganization"', false)
        ->assertSee('"@type":"BreadcrumbList"', false);
});

it('agrega meta robots noindex cuando la página no es indexable', function () {
    $page = Page::factory()->create([
        'slug' => 'pagina-no-indexable',
        'status' => 'published',
        'is_indexable' => false,
    ]);

    $this->get('/'.$page->slug)->assertSee('name="robots" content="noindex, nofollow"', false);
});

it('bloquea la indexación de todo el sitio cuando el entorno lo exige, sin importar la página', function () {
    config(['dante.seo.block_indexing' => true]);

    $page = Page::factory()->create([
        'slug' => 'pagina-indexable',
        'status' => 'published',
        'is_indexable' => true,
    ]);

    $this->get('/'.$page->slug)->assertSee('name="robots" content="noindex, nofollow"', false);
});

it('respeta la URL canónica manual cuando está cargada', function () {
    $page = Page::factory()->create([
        'slug' => 'duplicada',
        'status' => 'published',
        'canonical_url' => 'https://dante.edu.py/institucion/historia',
    ]);

    $this->get('/'.$page->slug)
        ->assertSee('<link rel="canonical" href="https://dante.edu.py/institucion/historia">', false);
});

it('incluye JSON-LD NewsArticle en el detalle de una noticia', function () {
    $post = Post::factory()->create([
        'slug' => 'noticia-real',
        'title' => ['es' => 'Una noticia real'],
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get('/noticias/'.$post->slug)
        ->assertSee('"@type":"NewsArticle"', false)
        ->assertSee('og:type" content="article"', false);
});

it('incluye JSON-LD WebSite con SearchAction en el inicio', function () {
    $this->get('/')->assertSee('"@type":"WebSite"', false)->assertSee('SearchAction', false);
});

it('incluye JSON-LD FAQPage cuando la página tiene un bloque de preguntas frecuentes', function () {
    $page = Page::factory()->create([
        'slug' => 'admisiones-faq',
        'status' => 'published',
        'blocks' => [[
            'type' => 'faq',
            'data' => ['items' => [['question' => ['es' => '¿Cómo me inscribo?'], 'answer' => ['es' => 'Completando el formulario.']]]],
        ]],
    ]);

    $this->get('/'.$page->slug)->assertSee('"@type":"FAQPage"', false);
});
