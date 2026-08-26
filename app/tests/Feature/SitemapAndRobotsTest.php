<?php

use App\Models\Page;
use App\Models\Post;

it('el sitemap incluye páginas y noticias publicadas e indexables, con lastmod real', function () {
    config(['dante.seo.block_indexing' => false]);

    $page = Page::factory()->create([
        'slug' => 'institucion/historia',
        'status' => 'published',
        'is_indexable' => true,
    ]);
    $post = Post::factory()->create([
        'slug' => 'una-noticia',
        'status' => 'published',
        'is_indexable' => true,
        'published_at' => now(),
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    $response->assertSee(url('/institucion/historia'), false);
    $response->assertSee(route('posts.show', $post->slug), false);
    $response->assertSee('<lastmod>'.$page->updated_at->format('Y-m-d'), false);
});

it('el sitemap excluye borradores y páginas noindex', function () {
    $draft = Page::factory()->create(['slug' => 'borrador', 'status' => 'draft']);
    $noindex = Page::factory()->create(['slug' => 'no-indexable', 'status' => 'published', 'is_indexable' => false]);

    $response = $this->get('/sitemap.xml');

    $response->assertDontSee(url('/'.$draft->slug), false);
    $response->assertDontSee(url('/'.$noindex->slug), false);
});

it('robots.txt permite todo y referencia el sitemap cuando la indexación no está bloqueada', function () {
    config(['dante.seo.block_indexing' => false]);

    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertSee('Disallow:');
    $response->assertDontSee('Disallow: /', false);
    $response->assertSee('Sitemap: '.route('sitemap.index'));
});

it('robots.txt bloquea todo en un entorno que no es de producción', function () {
    config(['dante.seo.block_indexing' => true]);

    $response = $this->get('/robots.txt');

    $response->assertOk()->assertSee('Disallow: /');
});
