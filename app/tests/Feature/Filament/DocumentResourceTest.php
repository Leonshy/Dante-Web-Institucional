<?php

use App\Filament\Resources\Documents\Pages\CreateDocument;
use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Document;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los documentos en el panel', function () {
    Document::factory()->count(2)->create();

    $this->livewire(ListDocuments::class)->assertSuccessful();
});

it('muestra el enlace público del archivo de un documento para copiar', function () {
    $media = Media::factory()->create();
    $document = Document::factory()->create(['media_id' => $media->id]);

    $this->livewire(ListDocuments::class)
        ->assertTableColumnStateSet('public_url', $media->url(), record: $document);
});

it('elige el archivo de la biblioteca de medios desde el picker', function () {
    $media = Media::factory()->create();

    $this->livewire(CreateDocument::class)
        ->fillForm([
            'title' => ['es' => 'Reglamento interno'],
            'site' => 'ambas',
            'status' => 'draft',
            'media_id' => $media->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $document = Document::query()->whereJsonContains('title->es', 'Reglamento interno')->firstOrFail();

    expect($document->media_id)->toBe($media->id);
});

it('exige el archivo al crear un documento', function () {
    $this->livewire(CreateDocument::class)
        ->fillForm([
            'title' => ['es' => 'Sin archivo'],
            'site' => 'ambas',
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasFormErrors(['media_id' => 'required']);
});

it('mantiene el archivo existente si no se sube uno nuevo', function () {
    $media = Media::factory()->create();
    $document = Document::factory()->create(['media_id' => $media->id]);

    $this->livewire(EditDocument::class, ['record' => $document->getRouteKey()])
        ->fillForm(['is_current' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $document->refresh();

    expect($document->media_id)->toBe($media->id)
        ->and($document->is_current)->toBeFalse();
});

it('editor_academico ve documentos pero no puede borrarlos (permiso real, no solo el botón oculto)', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');
    $document = Document::factory()->create();

    expect($editorAcademico->can('viewAny', Document::class))->toBeTrue()
        ->and($editorAcademico->can('delete', $document))->toBeFalse();
});

it('un rol sin permiso sobre documentos no puede ver el listado', function () {
    $editorNoticias = User::factory()->create(['is_active' => true]);
    $editorNoticias->assignRole('editor_noticias_marketing');

    $this->actingAs($editorNoticias);

    $this->livewire(ListDocuments::class)->assertForbidden();
});
