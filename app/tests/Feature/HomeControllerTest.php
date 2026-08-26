<?php

use App\Models\Document;
use App\Models\HomeSetting;
use App\Models\Page;
use App\Models\Post;

it('no muestra una sección desactivada desde los ajustes del inicio', function () {
    Post::factory()->create(['status' => 'published', 'title' => ['es' => 'Noticia visible']]);

    HomeSetting::current()->update([
        'sections' => [
            ['key' => 'news', 'enabled' => false],
            ['key' => 'featured_pages', 'enabled' => false],
            ['key' => 'announcements', 'enabled' => false],
            ['key' => 'documents', 'enabled' => false],
            ['key' => 'gallery', 'enabled' => false],
        ],
    ]);

    $this->get('/')->assertOk()->assertDontSee('Noticia visible');
});

it('respeta el orden guardado de las secciones del inicio', function () {
    Post::factory()->create(['status' => 'published', 'title' => ['es' => 'Sección de noticias']]);
    Document::factory()->create(['status' => 'published', 'title' => ['es' => 'Sección de documentos']]);

    HomeSetting::current()->update([
        'sections' => [
            ['key' => 'documents', 'enabled' => true],
            ['key' => 'news', 'enabled' => true],
            ['key' => 'featured_pages', 'enabled' => false],
            ['key' => 'announcements', 'enabled' => false],
            ['key' => 'gallery', 'enabled' => false],
        ],
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    expect(strpos($html, 'Sección de documentos'))
        ->toBeLessThan(strpos($html, 'Sección de noticias'));
});

it('muestra una página destacada como tarjeta en el inicio', function () {
    Page::factory()->create([
        'title' => ['es' => 'Historia del colegio'],
        'status' => 'published',
        'is_featured_home' => true,
        'home_excerpt' => ['es' => 'Más de un siglo de trayectoria.'],
    ]);

    HomeSetting::current()->update([
        'sections' => [
            ['key' => 'featured_pages', 'enabled' => true],
            ['key' => 'news', 'enabled' => false],
            ['key' => 'announcements', 'enabled' => false],
            ['key' => 'documents', 'enabled' => false],
            ['key' => 'gallery', 'enabled' => false],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Historia del colegio')
        ->assertSee('Más de un siglo de trayectoria.');
});

it('renderiza las cifras administradas desde el panel', function () {
    HomeSetting::current()->update([
        'stats' => [
            ['value' => '129', 'symbol' => '°', 'title' => ['es' => 'Aniversario'], 'description' => ['es' => 'de la Scuola Dante Alighieri']],
        ],
        'sections' => [
            ['key' => 'news', 'enabled' => false],
            ['key' => 'featured_pages', 'enabled' => false],
            ['key' => 'announcements', 'enabled' => false],
            ['key' => 'documents', 'enabled' => false],
            ['key' => 'gallery', 'enabled' => false],
        ],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('129°', false)
        ->assertSee('Aniversario');
});

it('no enlaza la tarjeta de oferta educativa completa a una URL inexistente (hallazgo real de Fase 9)', function () {
    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('href="'.url('/oferta-educativa').'"')
        ->and($html)->toContain('href="'.url('/institucion/quienes-somos').'"');
});
