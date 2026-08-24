<?php

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Page;
use App\Models\Post;
use App\Services\Search\SearchService;

it('encuentra páginas publicadas por título', function () {
    Page::factory()->create(['title' => ['es' => 'Admisiones 2027'], 'status' => 'published']);
    Page::factory()->create(['title' => ['es' => 'Contacto general'], 'status' => 'published']);

    $results = app(SearchService::class)->search('Admisiones');

    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('page');
    expect($results->first()->title)->toBe('Admisiones 2027');
    expect($results->first()->url)->toBe('/'.Page::query()->where('title->es', 'Admisiones 2027')->first()->slug);
});

it('encuentra noticias publicadas por título o contenido', function () {
    Post::factory()->create([
        'title' => ['es' => 'Acto de fin de año'],
        'excerpt' => ['es' => 'Resumen del acto.'],
        'content' => ['es' => '<p>El colegio celebró el acto de fin de año con familias.</p>'],
        'status' => 'published',
    ]);

    $results = app(SearchService::class)->search('fin de año');

    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('post');
});

it('encuentra documentos y comunicados publicados', function () {
    Document::factory()->create(['title' => ['es' => 'Reglamento interno 2027'], 'status' => 'published']);
    Announcement::factory()->create(['title' => ['es' => 'Suspensión de clases por lluvia'], 'status' => 'published']);

    $results = app(SearchService::class)->search('Reglamento');
    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('document');

    $results = app(SearchService::class)->search('Suspensión');
    expect($results)->toHaveCount(1);
    expect($results->first()->type)->toBe('announcement');
});

it('no devuelve contenido en borrador ni archivado', function () {
    Page::factory()->create(['title' => ['es' => 'Página borrador'], 'status' => 'draft']);
    Post::factory()->create(['title' => ['es' => 'Noticia archivada'], 'status' => 'archived']);
    Document::factory()->create(['title' => ['es' => 'Documento borrador'], 'status' => 'draft']);
    Announcement::factory()->create(['title' => ['es' => 'Comunicado archivado'], 'status' => 'archived']);

    $results = app(SearchService::class)->search('borrador');
    expect($results)->toHaveCount(0);

    $results = app(SearchService::class)->search('archivad');
    expect($results)->toHaveCount(0);
});

it('devuelve colección vacía si el término está vacío', function () {
    $results = app(SearchService::class)->search('   ');

    expect($results)->toHaveCount(0);
});

it('el endpoint público responde JSON con resultados de contenido publicado', function () {
    Post::factory()->create(['title' => ['es' => 'Jornada de puertas abiertas'], 'status' => 'published']);

    $response = $this->getJson('/buscar?q=puertas');

    $response->assertOk();
    $response->assertJsonPath('total', 1);
    $response->assertJsonPath('results.0.type', 'post');
    $response->assertJsonPath('results.0.title', 'Jornada de puertas abiertas');
});

it('el endpoint exige un término mínimo de 2 caracteres', function () {
    $response = $this->getJson('/buscar?q=a');

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('q');
});

it('el endpoint exige el parámetro q', function () {
    $response = $this->getJson('/buscar');

    $response->assertStatus(422);
});

it('limita la cantidad de búsquedas por minuto', function () {
    for ($i = 0; $i < 30; $i++) {
        $this->getJson('/buscar?q=colegio');
    }

    $response = $this->getJson('/buscar?q=colegio');

    $response->assertStatus(429);
});
