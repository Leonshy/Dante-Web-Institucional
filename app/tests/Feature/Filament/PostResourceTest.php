<?php

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Media;
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

it('muestra el enlace público de una noticia para copiar', function () {
    $post = Post::factory()->create(['slug' => 'aniversario-129']);

    $this->livewire(ListPosts::class)
        ->assertTableColumnStateSet('public_url', route('posts.show', 'aniversario-129'), record: $post);
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

it('elige la imagen destacada de la biblioteca de medios desde el picker', function () {
    $media = Media::factory()->create();

    $this->livewire(CreatePost::class)
        ->fillForm([
            'title' => ['es' => 'Noticia con imagen'],
            'excerpt' => ['es' => 'Bajada'],
            'content' => ['es' => '<p>Contenido</p>'],
            'slug' => 'noticia-con-imagen',
            'published_at' => now()->toDateString(),
            'status' => 'draft',
            'featured_media_id' => $media->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::query()->where('slug', 'noticia-con-imagen')->firstOrFail();

    expect($post->featured_media_id)->toBe($media->id);
});

it('permite dejar la noticia sin imagen destacada', function () {
    $post = Post::factory()->create();

    $this->livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm(['featured_media_id' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->featured_media_id)->toBeNull();
});
