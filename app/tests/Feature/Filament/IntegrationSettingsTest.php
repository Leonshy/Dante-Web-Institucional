<?php

use App\Filament\Pages\IntegrationSettings;
use App\Models\IntegrationSetting;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('carga la página de integraciones', function () {
    $this->livewire(IntegrationSettings::class)->assertSuccessful();
});

it('guarda los IDs y credenciales de cada integración', function () {
    $this->livewire(IntegrationSettings::class)
        ->fillForm([
            'ga_enabled' => true,
            'google_tag_manager_id' => 'GTM-ABC1234',
            'google_analytics_id' => 'G-ABC1234567',
            'meta_enabled' => true,
            'meta_pixel_id' => '123456789012345',
            'meta_capi_access_token' => 'EAAtoken-secreto',
            'meta_capi_test_event_code' => 'TEST12345',
            'turnstile_enabled' => true,
            'turnstile_site_key' => '0x4AAAAAAA_site',
            'turnstile_secret_key' => '0x4AAAAAAA_secret',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $settings = IntegrationSetting::current()->refresh();

    expect($settings->google_tag_manager_id)->toBe('GTM-ABC1234')
        ->and($settings->meta_pixel_id)->toBe('123456789012345')
        ->and($settings->meta_capi_access_token)->toBe('EAAtoken-secreto')
        ->and($settings->turnstile_secret_key)->toBe('0x4AAAAAAA_secret')
        ->and($settings->turnstileActive())->toBeTrue();
});

it('guarda los secretos cifrados en la base, nunca en texto plano', function () {
    IntegrationSetting::current()->update([
        'meta_capi_access_token' => 'EAAtoken-secreto',
        'turnstile_secret_key' => '0x4AAAAAAA_secret',
    ]);

    $raw = DB::table('integration_settings')->first();

    expect($raw->meta_capi_access_token)->not->toContain('EAAtoken-secreto')
        ->and($raw->turnstile_secret_key)->not->toContain('0x4AAAAAAA_secret');
});

it('apagar una integración la deja inactiva aunque los datos sigan guardados', function () {
    $settings = IntegrationSetting::current();
    $settings->update([
        'meta_enabled' => false,
        'meta_pixel_id' => '123456789012345',
        'meta_capi_access_token' => 'token',
    ]);

    expect($settings->refresh()->metaConversionsApiActive())->toBeFalse();
});

it('un usuario sin permiso no puede ver las integraciones', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    expect(IntegrationSettings::canAccess())->toBeFalse();
});

it('el editor de noticias y marketing sí puede ver y editar las integraciones', function () {
    $marketing = User::factory()->create(['is_active' => true]);
    $marketing->assignRole('editor_noticias_marketing');

    $this->actingAs($marketing);

    expect(IntegrationSettings::canAccess())->toBeTrue();
});
