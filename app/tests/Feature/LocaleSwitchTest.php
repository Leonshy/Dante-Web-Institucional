<?php

use App\Models\Page;
use App\Models\SiteSetting;

/**
 * Hallazgo real (Fase 10, reportado por el cliente en staging): el selector
 * ES/IT del header y del pie de página existía visualmente desde la Fase 4
 * pero los botones no tenían ningún enlace ni acción detrás — no cambiaban
 * nada al hacer clic.
 */
it('cambia el idioma de la sesión al visitar /idioma/it con el italiano activado', function () {
    SiteSetting::set('italian_enabled', '1', 'boolean', 'idioma');

    $this->get('/idioma/it')->assertRedirect();

    expect(session('locale'))->toBe('it');
});

it('vuelve a mostrar el contenido en italiano después de cambiar el idioma', function () {
    SiteSetting::set('italian_enabled', '1', 'boolean', 'idioma');
    $page = Page::factory()->create([
        'slug' => 'historia',
        'status' => 'published',
        'title' => ['es' => 'Historia', 'it' => 'Storia'],
    ]);

    $this->get('/idioma/it');

    $this->get('/'.$page->slug)->assertSee('Storia');
});

it('ignora el cambio a italiano si está desactivado en la configuración', function () {
    SiteSetting::set('italian_enabled', '0', 'boolean', 'idioma');

    $this->get('/idioma/it');

    expect(session('locale'))->not->toBe('it');
});

it('vuelve al español si el italiano se desactiva mientras alguien ya lo tenía elegido', function () {
    SiteSetting::set('italian_enabled', '1', 'boolean', 'idioma');
    $this->get('/idioma/it');
    expect(session('locale'))->toBe('it');

    SiteSetting::set('italian_enabled', '0', 'boolean', 'idioma');

    $this->get('/');

    expect(session('locale'))->not->toBe('it')
        ->and(app()->getLocale())->toBe('es');
});

it('no muestra el selector de idioma si el italiano está desactivado', function () {
    SiteSetting::set('italian_enabled', '0', 'boolean', 'idioma');

    $this->get('/')->assertDontSee('Cambiar idioma del sitio');
});
