<?php

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista la configuración global en el panel', function () {
    SiteSetting::query()->create([
        'key' => 'contact_email',
        'value' => 'info@dante.edu.py',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Correo de contacto',
    ]);

    $this->livewire(ListSiteSettings::class)
        ->assertSuccessful();
});

it('actualiza el valor de una configuración de texto', function () {
    $setting = SiteSetting::query()->create([
        'key' => 'contact_phone',
        'value' => '021-000-000',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Teléfono de contacto',
    ]);

    $this->livewire(EditSiteSetting::class, ['record' => $setting->getRouteKey()])
        ->fillForm(['value' => '021-111-111'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->refresh()->value)->toBe('021-111-111');
});

it('activa/desactiva un booleano con la acción de la tabla, sin pasar por Editar', function () {
    $setting = SiteSetting::query()->create([
        'key' => 'italian_enabled',
        'value' => '0',
        'type' => 'boolean',
        'group' => 'idioma',
        'label' => 'Mostrar el sitio en italiano',
    ]);

    Cache::put(SiteSetting::CACHE_PREFIX.'italian_enabled', false, 3600);

    $this->livewire(ListSiteSettings::class)
        ->assertTableActionExists('toggle', record: $setting)
        ->callTableAction('toggle', $setting);

    expect($setting->refresh()->value)->toBe('1')
        ->and(Cache::get(SiteSetting::CACHE_PREFIX.'italian_enabled'))->toBeNull();
});

it('un booleano no tiene botón de Editar en el listado — se activa con la acción de la propia tabla', function () {
    $setting = SiteSetting::query()->create([
        'key' => 'italian_enabled',
        'value' => '0',
        'type' => 'boolean',
        'group' => 'idioma',
        'label' => 'Mostrar el sitio en italiano',
    ]);

    $this->livewire(ListSiteSettings::class)
        ->assertTableActionHidden('edit', $setting);
});

it('el formulario de edición muestra el nombre humano de la configuración, no "Nombre"/"Clave interna"', function () {
    $setting = SiteSetting::query()->create([
        'key' => 'contact_phone',
        'value' => '021-000-000',
        'type' => 'text',
        'group' => 'general',
        'label' => 'Teléfono de contacto',
    ]);

    $this->livewire(EditSiteSetting::class, ['record' => $setting->getRouteKey()])
        ->assertSee('Teléfono de contacto')
        ->assertDontSee('Clave interna');
});

it('un usuario sin permiso no puede ver la configuración global', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListSiteSettings::class)->assertForbidden();
});
