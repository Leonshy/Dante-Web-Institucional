<?php

use App\Filament\Resources\Locations\Pages\CreateLocation;
use App\Filament\Resources\Locations\Pages\EditLocation;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Models\Location;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista las sedes en el panel', function () {
    Location::factory()->count(2)->create();

    $this->livewire(ListLocations::class)->assertSuccessful();
});

it('crea una sede con sus datos de contacto', function () {
    $this->livewire(CreateLocation::class)
        ->fillForm([
            'name' => 'Asunción',
            'academic_email' => 'colegioasu@dante.edu.py',
            'administrative_email' => 'administracionasu@dante.edu.py',
            'phone' => '+595 (21) 491 622',
            'whatsapp' => '595984464500',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $location = Location::query()->where('name', 'Asunción')->firstOrFail();

    expect($location->academic_email)->toBe('colegioasu@dante.edu.py')
        ->and($location->whatsapp)->toBe('595984464500');
});

it('actualiza una sede existente', function () {
    $location = Location::factory()->create(['name' => 'Sede vieja']);

    $this->livewire(EditLocation::class, ['record' => $location->getRouteKey()])
        ->fillForm(['name' => 'Sede nueva'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->refresh()->name)->toBe('Sede nueva');
});

it('un usuario sin permiso no puede ver el listado de sedes', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListLocations::class)->assertForbidden();
});

it('un editor general sí puede administrar sedes', function () {
    $editorGeneral = User::factory()->create(['is_active' => true]);
    $editorGeneral->assignRole('editor_general');

    $this->actingAs($editorGeneral);

    $this->livewire(ListLocations::class)->assertSuccessful();
});
