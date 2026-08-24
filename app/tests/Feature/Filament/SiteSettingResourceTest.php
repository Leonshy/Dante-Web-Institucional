<?php

use App\Filament\Resources\SiteSettings\Pages\EditSiteSetting;
use App\Filament\Resources\SiteSettings\Pages\ListSiteSettings;
use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

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

it('un usuario sin permiso no puede ver la configuración global', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListSiteSettings::class)->assertForbidden();
});
