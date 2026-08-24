<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista las noticias en el panel', function () {
    Post::factory()->count(2)->create();

    $this->livewire(ListPosts::class)
        ->assertSuccessful();
});

it('crea una noticia con título, bajada y contenido en español', function () {
    $this->livewire(CreatePost::class)
        ->fillForm([
            'title' => ['es' => 'Nueva noticia'],
            'excerpt' => ['es' => 'Bajada de la noticia'],
            'content' => ['es' => '<p>Contenido</p>'],
            'slug' => 'nueva-noticia',
            'published_at' => now()->toDateString(),
            'status' => 'draft',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->where('slug', 'nueva-noticia')->exists())->toBeTrue();
});

it('exige el título y la bajada en español', function () {
    $this->livewire(CreatePost::class)
        ->fillForm([
            'title' => ['es' => ''],
            'excerpt' => ['es' => ''],
            'content' => ['es' => '<p>Contenido</p>'],
            'slug' => 'sin-titulo',
            'published_at' => now()->toDateString(),
        ])
        ->call('create')
        ->assertHasFormErrors(['title.es' => 'required', 'excerpt.es' => 'required']);
});

it('guarda los campos SEO y de indexación de la noticia', function () {
    $post = Post::factory()->create();

    $this->livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'seo_title' => ['es' => 'Título SEO'],
            'seo_description' => ['es' => 'Descripción SEO'],
            'is_indexable' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $post->refresh();

    expect($post->getTranslation('seo_title', 'es'))->toBe('Título SEO')
        ->and($post->is_indexable)->toBeFalse();
});

it('un usuario sin permiso no puede ver el listado de noticias', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListPosts::class)->assertForbidden();
});
