<?php

use App\Models\Location;

it('muestra una tarjeta por cada sede activa en la página de contacto', function () {
    Location::factory()->create(['name' => 'Asunción', 'academic_email' => 'colegioasu@dante.edu.py']);
    Location::factory()->create(['name' => 'Fernando de la Mora', 'is_active' => false]);

    $response = $this->get('/contacto');

    $response->assertOk()
        ->assertSee('Asunción')
        ->assertSee('colegioasu@dante.edu.py')
        ->assertDontSee('Fernando de la Mora');
});

it('muestra las sedes activas en columnas en el pie de página', function () {
    Location::factory()->create(['name' => 'Instituto de Lengua Italiana', 'phone' => '+595 974 812022']);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Nuestras sedes')
        ->assertSee('Instituto de Lengua Italiana')
        ->assertSee('+595 974 812022');
});

it('no muestra la sección de sedes en el pie si no hay ninguna activa', function () {
    $response = $this->get('/');

    $response->assertOk()->assertDontSee('Nuestras sedes');
});
