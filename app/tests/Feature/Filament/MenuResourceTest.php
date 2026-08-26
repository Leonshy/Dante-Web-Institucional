<?php

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Livewire\ManageMenuItems;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\PermissionSeeder;

beforeEach(function () {
    $this->seed(PermissionSeeder::class);

    $this->admin = User::factory()->create(['is_active' => true]);
    $this->admin->assignRole('administrador');

    $this->actingAs($this->admin);
});

it('lista los menús en el panel', function () {
    Menu::factory()->count(2)->create();

    $this->livewire(ListMenus::class)->assertSuccessful();
});

it('crea un menú', function () {
    $this->livewire(CreateMenu::class)
        ->fillForm([
            'key' => 'principal',
            'name' => 'Menú principal',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Menu::query()->where('key', 'principal')->exists())->toBeTrue();
});

it('exige un identificador único de menú', function () {
    Menu::factory()->create(['key' => 'principal']);

    $this->livewire(CreateMenu::class)
        ->fillForm([
            'key' => 'principal',
            'name' => 'Otro menú',
        ])
        ->call('create')
        ->assertHasFormErrors(['key' => 'unique']);
});

it('lista los enlaces de un menú en el árbol arrastrable', function () {
    $menu = Menu::factory()->create();
    MenuItem::factory()->for($menu)->create(['label' => ['es' => 'Inicio']]);

    $this->livewire(ManageMenuItems::class, ['record' => $menu])->assertSuccessful();
});

it('crea un enlace de menú con una URL manual', function () {
    $menu = Menu::factory()->create();

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->callAction('create', data: [
            'label' => ['es' => 'Contacto'],
            'link_type' => 'url',
            'url' => '/contacto',
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $item = MenuItem::query()->where('menu_id', $menu->id)->first();

    expect($item)->not->toBeNull()
        ->and($item->url)->toBe('/contacto')
        ->and($item->linkable_type)->toBeNull()
        ->and($item->parent_id)->toBeNull()
        ->and($item->getTranslation('label', 'es'))->toBe('Contacto');
});

it('crea un enlace de menú que apunta a una página interna', function () {
    $menu = Menu::factory()->create();
    $page = Page::factory()->create();

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->callAction('create', data: [
            'label' => ['es' => 'Institución'],
            'link_type' => 'page',
            'linkable_page_id' => $page->id,
            'is_active' => true,
        ])
        ->assertHasNoActionErrors();

    $item = MenuItem::query()->where('menu_id', $menu->id)->first();

    expect($item->linkable_type)->toBe(Page::class)
        ->and($item->linkable_id)->toBe($page->id)
        ->and($item->url)->toBeNull();
});

it('crea un enlace hijo dentro de otro enlace del mismo menú (botón "+ Submenú")', function () {
    $menu = Menu::factory()->create();
    $parent = MenuItem::factory()->for($menu)->create(['label' => ['es' => 'Institución']]);

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->callAction('create', data: [
            'label' => ['es' => 'Historia'],
            'link_type' => 'url',
            'url' => '/institucion/historia',
            'is_active' => true,
        ], arguments: ['parent_id' => $parent->id])
        ->assertHasNoActionErrors();

    $child = MenuItem::query()->where('label->es', 'Historia')->first();

    expect($child->parent_id)->toBe($parent->id);
});

it('activa/desactiva un enlace directamente desde el listado, sin abrir el formulario', function () {
    $menu = Menu::factory()->create();
    $item = MenuItem::factory()->for($menu)->create(['is_active' => true]);

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->call('toggleActive', $item->id);

    expect($item->refresh()->is_active)->toBeFalse();
});

it('reordena y anida enlaces arrastrando (persistidos vía updateOrder)', function () {
    $menu = Menu::factory()->create();
    $a = MenuItem::factory()->for($menu)->create(['sort_order' => 0]);
    $b = MenuItem::factory()->for($menu)->create(['sort_order' => 1]);

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->call('updateOrder', [
            ['id' => $b->id, 'parent_id' => null, 'sort_order' => 0],
            ['id' => $a->id, 'parent_id' => $b->id, 'sort_order' => 0],
        ]);

    expect($a->refresh()->parent_id)->toBe($b->id)
        ->and($a->sort_order)->toBe(0)
        ->and($b->refresh()->sort_order)->toBe(0);
});

it('no deja anidar un enlace que ya tiene submenús propios (no más de 2 niveles)', function () {
    $menu = Menu::factory()->create();
    $grandparentCandidate = MenuItem::factory()->for($menu)->create();
    $parentWithChildren = MenuItem::factory()->for($menu)->create();
    $existingChild = MenuItem::factory()->for($menu)->create(['parent_id' => $parentWithChildren->id]);

    $this->livewire(ManageMenuItems::class, ['record' => $menu])
        ->call('updateOrder', [
            ['id' => $grandparentCandidate->id, 'parent_id' => null, 'sort_order' => 0],
            ['id' => $parentWithChildren->id, 'parent_id' => $grandparentCandidate->id, 'sort_order' => 0],
            ['id' => $existingChild->id, 'parent_id' => $parentWithChildren->id, 'sort_order' => 0],
        ]);

    expect($parentWithChildren->refresh()->parent_id)->toBeNull();
});

it('un usuario sin permiso no puede ver el listado de menús', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListMenus::class)->assertForbidden();
});
