<?php

use App\Filament\Resources\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\Redirects\Pages\ListRedirects;
use App\Models\Redirect;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
    $admin = User::factory()->create(['is_active' => true]);
    $admin->assignRole('administrador');
    $this->actingAs($admin);
});

it('lista las redirecciones', function () {
    Redirect::factory()->count(3)->create();

    $this->livewire(ListRedirects::class)->assertSuccessful();
});

it('crea una redirección 301', function () {
    $this->livewire(CreateRedirect::class)
        ->fillForm([
            'from_path' => '/pagina-vieja',
            'to_path' => '/pagina-nueva',
            'status_code' => 301,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Redirect::query()->where('from_path', '/pagina-vieja')->exists())->toBeTrue();
});

it('un rol sin permiso sobre redirecciones no puede ver el listado', function () {
    $editorGeneral = User::factory()->create(['is_active' => true]);
    $editorGeneral->assignRole('editor_general');

    $this->actingAs($editorGeneral);

    $this->livewire(ListRedirects::class)->assertForbidden();
});
