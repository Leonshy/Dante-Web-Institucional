<?php

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista las categorías en el panel', function () {
    Category::factory()->count(2)->create();

    $this->livewire(ListCategories::class)
        ->assertSuccessful();
});

it('crea una categoría', function () {
    $this->livewire(CreateCategory::class)
        ->fillForm([
            'type' => 'news',
            'name' => ['es' => 'Vida escolar'],
            'slug' => 'vida-escolar',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->where('slug', 'vida-escolar')->exists())->toBeTrue();
});

it('exige el tipo y el nombre', function () {
    $this->livewire(CreateCategory::class)
        ->fillForm([
            'type' => '',
            'name' => ['es' => ''],
            'slug' => 'sin-nombre',
        ])
        ->call('create')
        ->assertHasFormErrors(['type' => 'required', 'name.es' => 'required']);
});

it('un usuario sin permiso no puede ver el listado de categorías', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListCategories::class)->assertForbidden();
});
