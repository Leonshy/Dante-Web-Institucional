<?php

use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Models\Media;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los medios en el panel', function () {
    Media::factory()->count(2)->create();

    $this->livewire(ListMedia::class)->assertSuccessful();
});

it('muestra el enlace público de un medio para copiar', function () {
    $media = Media::factory()->create();

    $this->livewire(ListMedia::class)
        ->assertTableColumnStateSet('public_url', $media->url(), record: $media);
});

it('sube un archivo desde el panel usando el mismo servicio de subida seguro', function () {
    Storage::fake('media');

    $this->livewire(CreateMedia::class)
        ->fillForm([
            'upload' => UploadedFile::fake()->image('foto.jpg', 800, 600),
            'alt' => 'Foto institucional',
            'folder' => 'noticias',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $media = Media::query()->where('alt', 'Foto institucional')->firstOrFail();

    expect($media->folder)->toBe('noticias');
    Storage::disk('media')->assertExists($media->path);
});

it('edita el texto alternativo de un medio existente', function () {
    $media = Media::factory()->create(['alt' => 'texto viejo']);

    $this->livewire(EditMedia::class, ['record' => $media->getRouteKey()])
        ->fillForm(['alt' => 'texto nuevo y descriptivo'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($media->refresh()->alt)->toBe('texto nuevo y descriptivo');
});

it('un usuario sin permiso de medios no puede ver la biblioteca', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListMedia::class)->assertForbidden();
});

it('el editor de noticias y marketing sí puede ver y subir medios', function () {
    $editorNoticias = User::factory()->create(['is_active' => true]);
    $editorNoticias->assignRole('editor_noticias_marketing');

    $this->actingAs($editorNoticias);

    $this->livewire(ListMedia::class)->assertSuccessful();
});
