<?php

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista el registro de auditoría en el panel', function () {
    Page::factory()->create();

    $this->livewire(ListActivityLogs::class)
        ->assertSuccessful();
});

it('un usuario sin permiso no puede ver el registro de auditoría', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListActivityLogs::class)->assertForbidden();
});
