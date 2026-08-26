<?php

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Filament\Auth\Pages\EditProfile;
use Filament\Auth\Pages\Login;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->user = User::factory()->create([
        'is_active' => true,
        'password' => Hash::make('contrasena-segura-123'),
    ]);
    $this->user->assignRole('administrador');
});

it('permite iniciar sesión en el panel con credenciales correctas', function () {
    $this->livewire(Login::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'contrasena-segura-123',
        ])
        ->call('authenticate');

    $this->assertAuthenticatedAs($this->user, 'web');
});

it('rechaza el inicio de sesión con contraseña incorrecta', function () {
    $this->livewire(Login::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'contrasena-equivocada',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    $this->assertGuest('web');
});

it('rechaza el inicio de sesión de un usuario desactivado', function () {
    $inactive = User::factory()->create([
        'is_active' => false,
        'password' => Hash::make('contrasena-segura-123'),
    ]);
    $inactive->assignRole('administrador');

    $this->livewire(Login::class)
        ->fillForm([
            'email' => $inactive->email,
            'password' => 'contrasena-segura-123',
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['email']);

    $this->assertGuest('web');
});

it('bloquea el login tras demasiados intentos fallidos (rate limit)', function () {
    RateLimiter::clear('filament-panel-auth|'.request()->ip());

    for ($i = 0; $i < 5; $i++) {
        $this->livewire(Login::class)
            ->fillForm([
                'email' => $this->user->email,
                'password' => 'contrasena-equivocada',
            ])
            ->call('authenticate');
    }

    // El sexto intento debe quedar bloqueado por el rate limiter del panel,
    // incluso con la contraseña correcta.
    $this->livewire(Login::class)
        ->fillForm([
            'email' => $this->user->email,
            'password' => 'contrasena-segura-123',
        ])
        ->call('authenticate');

    $this->assertGuest('web');
});

it('cierra la sesión del panel correctamente', function () {
    $this->actingAs($this->user);

    $this->assertAuthenticatedAs($this->user, 'web');

    $this->post(route('filament.admin.auth.logout'));

    $this->assertGuest('web');
});

it('un usuario no autenticado es redirigido al login al pedir una página del panel', function () {
    $this->get(config('dante.admin_path'))
        ->assertRedirect(config('dante.admin_path').'/login');
});

it('un usuario autenticado y activo accede al dashboard del panel', function () {
    $this->actingAs($this->user)
        ->get(config('dante.admin_path'))
        ->assertSuccessful();
});

it('un usuario desactivado no puede acceder al dashboard aunque tenga sesión', function () {
    $inactive = User::factory()->create(['is_active' => false]);
    $inactive->assignRole('administrador');

    $this->actingAs($inactive)
        ->get(config('dante.admin_path'))
        ->assertForbidden();
});

it('permite pedir el restablecimiento de contraseña desde el panel', function () {
    $this->livewire(RequestPasswordReset::class)
        ->fillForm(['email' => $this->user->email])
        ->call('request');

    $this->assertDatabaseHas('password_reset_tokens', ['email' => $this->user->email]);
});

it('un usuario autenticado puede ver su página de perfil del panel', function () {
    $this->actingAs($this->user);

    $this->livewire(EditProfile::class)->assertSuccessful();
});
