<?php

use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('guarda los bloques nuevos del catálogo (galería, FAQ, testimonios, mapa)', function () {
    $page = Page::factory()->create();
    $gallery = Gallery::factory()->create();

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'blocks' => [
                'bloque-1' => [
                    'type' => 'galeria',
                    'data' => [
                        'gallery_id' => $gallery->id,
                        'layout' => 'grid',
                        'site' => 'asuncion',
                    ],
                ],
                'bloque-2' => [
                    'type' => 'faq',
                    'data' => [
                        'items' => [
                            ['question' => ['es' => '¿Cómo me inscribo?'], 'answer' => ['es' => 'Completando el formulario.']],
                        ],
                    ],
                ],
                'bloque-3' => [
                    'type' => 'testimonios',
                    'data' => [
                        'items' => [
                            ['name' => 'María López', 'role' => 'Madre de alumno', 'text' => ['es' => 'Excelente institución.']],
                        ],
                    ],
                ],
                'bloque-4' => [
                    'type' => 'mapa',
                    'data' => [
                        'address' => 'Av. España 123, Asunción',
                        'latitude' => -25.2637,
                        'longitude' => -57.5759,
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->blocks)->toHaveCount(4);
    expect($page->blocks[0]['type'])->toBe('galeria');
    expect($page->blocks[0]['data']['gallery_id'])->toBe($gallery->id);
    expect($page->blocks[1]['data']['items'][0]['question']['es'])->toBe('¿Cómo me inscribo?');
    expect($page->blocks[2]['data']['items'][0]['name'])->toBe('María López');
    expect($page->blocks[3]['data']['address'])->toBe('Av. España 123, Asunción');
});

it('guarda el bloque de documentos descargables por categoría, el selector de sede y el listado de comunicados', function () {
    $page = Page::factory()->create();
    $category = Category::factory()->create(['type' => 'document']);

    $this->livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'blocks' => [
                'bloque-1' => [
                    'type' => 'documentos',
                    'data' => [
                        'category_id' => $category->id,
                    ],
                ],
                'bloque-2' => [
                    'type' => 'selector_sede',
                    'data' => [
                        'site' => 'fernando-de-la-mora',
                    ],
                ],
                'bloque-3' => [
                    'type' => 'listado_comunicados',
                    'data' => [
                        'count' => 4,
                        'site' => 'ambas',
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $page->refresh();

    expect($page->blocks)->toHaveCount(3);
    expect($page->blocks[0]['data']['category_id'])->toBe($category->id);
    expect($page->blocks[1]['data']['site'])->toBe('fernando-de-la-mora');
    expect($page->blocks[2]['data']['count'])->toBe(4);
});
