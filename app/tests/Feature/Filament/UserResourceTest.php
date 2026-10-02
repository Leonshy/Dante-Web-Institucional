<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los usuarios del panel', function () {
    User::factory()->count(2)->create();

    $this->livewire(ListUsers::class)
        ->assertSuccessful();
});

it('crea un usuario con un rol asignado', function () {
    $role = Role::findByName('editor_academico');

    $this->livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Nueva Editora',
            'email' => 'editora@dante.edu.py',
            'password' => 'password-seguro',
            'roles' => [$role->id],
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'editora@dante.edu.py')->firstOrFail();

    expect($user->hasRole('editor_academico'))->toBeTrue();
});

it('exige nombre, correo y rol', function () {
    $this->livewire(CreateUser::class)
        ->fillForm([
            'name' => '',
            'email' => '',
            'password' => 'password-seguro',
            'roles' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'email' => 'required', 'roles' => 'required']);
});

it('un usuario sin permiso no puede ver el listado de usuarios', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListUsers::class)->assertForbidden();
});

describe('cuenta protegida del proveedor', function () {
    beforeEach(function () {
        $this->webmaster = User::factory()->create([
            'email' => 'webmaster@webparaguay.com',
            'is_active' => true,
        ]);
        $this->webmaster->assignRole('administrador');
    });

    it('no se puede borrar, ni siquiera por código', function () {
        expect(fn () => $this->webmaster->delete())->toThrow(RuntimeException::class);
        expect(fn () => $this->webmaster->forceDelete())->toThrow(RuntimeException::class);
        expect(User::query()->where('email', 'webmaster@webparaguay.com')->exists())->toBeTrue();
    });

    it('no puede cambiar de correo ni desactivarse', function () {
        expect(fn () => $this->webmaster->update(['email' => 'otro@dante.edu.py']))->toThrow(RuntimeException::class);
        expect(fn () => $this->webmaster->update(['is_active' => false]))->toThrow(RuntimeException::class);
    });

    it('otro administrador no puede editarla ni borrarla', function () {
        expect($this->admin->can('update', $this->webmaster))->toBeFalse()
            ->and($this->admin->can('delete', $this->webmaster))->toBeFalse();
    });

    it('su propia cuenta sí puede cambiar nombre y contraseña', function () {
        $this->actingAs($this->webmaster);

        expect($this->webmaster->can('update', $this->webmaster))->toBeTrue();
        $this->webmaster->update(['name' => 'Webmaster WebParaguay']);

        expect($this->webmaster->fresh()->name)->toBe('Webmaster WebParaguay');
    });
});
