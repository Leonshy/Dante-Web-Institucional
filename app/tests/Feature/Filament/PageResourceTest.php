<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista las páginas en el panel', function () {
    Page::factory()->count(2)->create();

    $this->livewire(ListPages::class)
        ->assertSuccessful();
});

it('crea una página con título en español y bloque de texto', function () {
    $this->livewire(CreatePage::class)
        ->fillForm([
            'title' => ['es' => 'Historia'],
            'slug' => 'institucion/historia',
            'site_section' => 'institucion',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Page::query()->where('slug', 'institucion/historia')->exists())->toBeTrue();
});

it('exige el título en español', function () {
    $this->livewire(CreatePage::class)
        ->fillForm([
            'title' => ['es' => ''],
            'slug' => 'sin-titulo',
            'site_section' => 'general',
        ])
        ->call('create')
        ->assertHasFormErrors(['title.es' => 'required']);
});

it('sanitiza el HTML del bloque de texto antes de guardar', function () {
    $page = Page::factory()->create();

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'blocks' => [
                'bloque-1' => [
                    'type' => 'texto',
                    'content' => ['es' => '<p>Hola</p><script>alert(1)</script>'],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->blocks[0]['content']['es'])
        ->toContain('<p>Hola</p>')
        ->not->toContain('<script>');
});

it('un usuario sin permiso no puede ver el listado de páginas', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListPages::class)->assertForbidden();
});
