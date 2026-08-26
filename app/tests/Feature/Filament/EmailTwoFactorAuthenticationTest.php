<?php

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Filament\Auth\MultiFactor\Email\EmailAuthentication;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

it('muestra la alerta persistente a un usuario sin 2FA por email activado', function () {
    $user = User::factory()->create(['is_active' => true, 'has_email_authentication' => false]);
    $user->assignRole('administrador');

    $response = $this->actingAs($user)->get('/panel-dante-2026');

    $response->assertOk()->assertSee('verificación en dos pasos', false);
});

it('no muestra la alerta a un usuario que ya activó el 2FA por email', function () {
    $user = User::factory()->create(['is_active' => true, 'has_email_authentication' => true]);
    $user->assignRole('administrador');

    $response = $this->actingAs($user)->get('/panel-dante-2026');

    $response->assertOk()->assertDontSee('verificación en dos pasos', false);
});

it('el 2FA por email nunca es obligatorio, sin importar el usuario', function () {
    $user = User::factory()->create(['is_active' => true, 'has_email_authentication' => false]);
    $user->assignRole('administrador');

    // Con el proveedor forzado (isRequired: true) Filament redirige a una
    // pantalla de configuración obligatoria antes de dejar pasar a cualquier
    // página — acá se confirma que el usuario entra derecho al dashboard,
    // sin ninguna pantalla intermedia (ADR-003: opt-in, nunca forzado).
    $response = $this->actingAs($user)->get('/panel-dante-2026');

    $response->assertOk();
});

it('verifica un código válido y rechaza uno incorrecto', function () {
    Notification::fake();

    $user = User::factory()->create(['has_email_authentication' => true]);
    $provider = app(EmailAuthentication::class)->generateCodesUsing(fn () => '123456');

    $provider->sendCode($user);

    expect($provider->verifyCode('999999', $user))->toBeFalse()
        ->and($provider->verifyCode('123456', $user))->toBeTrue()
        // Un código ya usado no se puede reutilizar (se borra de sesión al verificarse).
        ->and($provider->verifyCode('123456', $user))->toBeFalse();
});
