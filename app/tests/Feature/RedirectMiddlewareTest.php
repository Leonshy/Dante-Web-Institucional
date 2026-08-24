<?php

use App\Models\Redirect;

it('redirige una URL vieja activa con el código configurado', function () {
    Redirect::query()->create([
        'from_path' => '/acerca-de-la-sociedad',
        'to_path' => '/institucion/sociedad-dante-alighieri',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $response = $this->get('/acerca-de-la-sociedad');

    $response->assertRedirect('/institucion/sociedad-dante-alighieri');
    $response->assertStatus(301);
});

it('registra la cantidad de usos de la redirección', function () {
    $redirect = Redirect::query()->create([
        'from_path' => '/vieja',
        'to_path' => '/nueva',
        'status_code' => 301,
        'is_active' => true,
    ]);

    $this->get('/vieja');

    expect($redirect->fresh()->hits)->toBe(1);
});

it('no redirige si la redirección está inactiva', function () {
    Redirect::query()->create([
        'from_path' => '/inactiva',
        'to_path' => '/nueva',
        'status_code' => 301,
        'is_active' => false,
    ]);

    $response = $this->get('/inactiva');

    $response->assertStatus(404);
});
