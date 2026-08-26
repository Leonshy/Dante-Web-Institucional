<?php

use App\Http\Middleware\PublicMaintenanceMode;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

it('el sitio público responde normal cuando el mantenimiento está apagado', function () {
    SiteSetting::set('maintenance_mode', '0', 'boolean', 'general');

    $this->get('/')->assertOk();
});

it('el sitio público muestra la página de mantenimiento cuando está activado', function () {
    SiteSetting::set('maintenance_mode', '1', 'boolean', 'general');

    $response = $this->get('/');

    $response->assertStatus(503)->assertSee('El sitio está en mantenimiento');
});

it('deja pasar las peticiones de actualización de Livewire aunque el mantenimiento esté activado', function () {
    // Hallazgo real (probado en el navegador): el endpoint de actualización
    // de Livewire corre en el mismo grupo `web` que el sitio público — el
    // panel comparte ese mismo endpoint para sus propias acciones. Sin esta
    // excepción, activar el mantenimiento rompía cualquier acción del panel
    // a mitad de camino (le llegaba la página 503 en vez del JSON esperado).
    SiteSetting::set('maintenance_mode', '1', 'boolean', 'general');

    $request = Request::create('/livewire-abc123/update', 'POST');
    $request->headers->set('X-Livewire', 'true');

    $response = (new PublicMaintenanceMode)->handle($request, fn () => response('ok'));

    expect($response->getContent())->toBe('ok');
});
