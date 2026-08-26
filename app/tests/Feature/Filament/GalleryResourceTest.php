<?php

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los álbumes en el panel', function () {
    Gallery::factory()->count(2)->create();

    $this->livewire(ListGalleries::class)->assertSuccessful();
});

it('elige varias fotos de la biblioteca de medios desde el picker', function () {
    $photos = Media::factory()->count(2)->create();

    $this->livewire(CreateGallery::class)
        ->fillForm([
            'title' => ['es' => 'Aniversario 2026'],
            'event_date' => now()->toDateString(),
            'site' => 'ambas',
            'status' => 'draft',
            'media' => $photos->pluck('id')->all(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $gallery = Gallery::query()->whereJsonContains('title->es', 'Aniversario 2026')->firstOrFail();

    expect($gallery->media)->toHaveCount(2);
});

it('mantiene las fotos existentes de un álbum si no se suben nuevas', function () {
    $gallery = Gallery::factory()->create();
    $media = Media::factory()->count(2)->create();
    $gallery->media()->sync([
        $media[0]->id => ['sort_order' => 0],
        $media[1]->id => ['sort_order' => 1],
    ]);

    $this->livewire(EditGallery::class, ['record' => $gallery->getRouteKey()])
        ->fillForm(['status' => 'published'])
        ->call('save')
        ->assertHasNoFormErrors();

    $gallery->refresh();

    expect($gallery->media)->toHaveCount(2)
        ->and($gallery->status)->toBe('published');
});

it('editor_general ve galerías pero no puede borrarlas (permiso real, no solo el botón oculto)', function () {
    $editorGeneral = User::factory()->create(['is_active' => true]);
    $editorGeneral->assignRole('editor_general');
    $gallery = Gallery::factory()->create();

    expect($editorGeneral->can('viewAny', Gallery::class))->toBeTrue()
        ->and($editorGeneral->can('delete', $gallery))->toBeFalse();
});

it('un rol sin permiso sobre galerías no puede ver el listado', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListGalleries::class)->assertForbidden();
});
