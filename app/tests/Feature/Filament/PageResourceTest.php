<?php

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Media;
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

it('muestra el enlace público completo de una página, incluida la ruta anidada del padre', function () {
    $parent = Page::factory()->create(['slug' => 'institucion']);
    $child = Page::factory()->create(['slug' => 'historia', 'parent_id' => $parent->id]);

    $this->livewire(ListPages::class)
        ->assertTableColumnStateSet('public_url', url('institucion/historia'), record: $child);
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
                    'data' => [
                        'content' => ['es' => '<p>Hola</p><script>alert(1)</script>'],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->blocks[0]['data']['content']['es'])
        ->toContain('<p>Hola</p>')
        ->not->toContain('<script>');
});

it('bloquea imágenes de dominios externos en el bloque de texto', function () {
    $page = Page::factory()->create();

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'blocks' => [
                'bloque-1' => [
                    'type' => 'texto',
                    'data' => [
                        'content' => ['es' => '<p>Hola</p><img src="http://evil.example.com/x.png">'],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->blocks[0]['data']['content']['es'])->not->toContain('evil.example.com');
});

it('un usuario sin permiso no puede ver el listado de páginas', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListPages::class)->assertForbidden();
});

it('editor_general ve y edita páginas pero no puede borrarlas (permiso real, no solo el botón oculto)', function () {
    $editorGeneral = User::factory()->create(['is_active' => true]);
    $editorGeneral->assignRole('editor_general');
    $page = Page::factory()->create();

    expect($editorGeneral->can('viewAny', Page::class))->toBeTrue()
        ->and($editorGeneral->can('update', $page))->toBeTrue()
        ->and($editorGeneral->can('delete', $page))->toBeFalse();
});

it('elige la portada y la imagen SEO de la biblioteca de medios desde el picker', function () {
    $cover = Media::factory()->create();
    $seoImage = Media::factory()->create();

    $this->livewire(CreatePage::class)
        ->fillForm([
            'title' => ['es' => 'Historia'],
            'slug' => 'institucion/historia-2',
            'site_section' => 'institucion',
            'status' => 'draft',
            'cover_media_id' => $cover->id,
            'seo_image_id' => $seoImage->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()->where('slug', 'institucion/historia-2')->firstOrFail();

    expect($page->cover_media_id)->toBe($cover->id)
        ->and($page->seo_image_id)->toBe($seoImage->id);
});

it('mantiene la portada existente si el picker no cambia su valor', function () {
    $media = Media::factory()->create();
    $page = Page::factory()->create(['cover_media_id' => $media->id]);

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->refresh()->cover_media_id)->toBe($media->id);
});

it('destaca una página en el inicio con su adelanto', function () {
    $page = Page::factory()->create(['is_featured_home' => false]);

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'is_featured_home' => true,
            'home_excerpt' => ['es' => 'Bajada corta para el inicio'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->is_featured_home)->toBeTrue()
        ->and($page->home_excerpt)->toBe('Bajada corta para el inicio');
});
