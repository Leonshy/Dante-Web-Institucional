<?php

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Filament\Resources\Menus\RelationManagers\ItemsRelationManager;
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

it('lista los enlaces de un menú en el gestor de ítems', function () {
    $menu = Menu::factory()->create();
    MenuItem::factory()->for($menu)->create(['label' => ['es' => 'Inicio']]);

    $this->livewire(ItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => EditMenu::class,
    ])->assertSuccessful();
});

it('crea un enlace de menú con una URL manual', function () {
    $menu = Menu::factory()->create();

    $this->livewire(ItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => EditMenu::class,
    ])
        ->callTableAction('create', data: [
            'label' => ['es' => 'Contacto'],
            'link_type' => 'url',
            'url' => '/contacto',
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertHasNoTableActionErrors();

    $item = MenuItem::query()->where('menu_id', $menu->id)->first();

    expect($item)->not->toBeNull()
        ->and($item->url)->toBe('/contacto')
        ->and($item->linkable_type)->toBeNull()
        ->and($item->getTranslation('label', 'es'))->toBe('Contacto');
});

it('crea un enlace de menú que apunta a una página interna', function () {
    $menu = Menu::factory()->create();
    $page = Page::factory()->create();

    $this->livewire(ItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => EditMenu::class,
    ])
        ->callTableAction('create', data: [
            'label' => ['es' => 'Institución'],
            'link_type' => 'page',
            'linkable_page_id' => $page->id,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertHasNoTableActionErrors();

    $item = MenuItem::query()->where('menu_id', $menu->id)->first();

    expect($item->linkable_type)->toBe(Page::class)
        ->and($item->linkable_id)->toBe($page->id)
        ->and($item->url)->toBeNull();
});

it('crea un enlace hijo dentro de otro enlace del mismo menú', function () {
    $menu = Menu::factory()->create();
    $parent = MenuItem::factory()->for($menu)->create(['label' => ['es' => 'Institución']]);

    $this->livewire(ItemsRelationManager::class, [
        'ownerRecord' => $menu,
        'pageClass' => EditMenu::class,
    ])
        ->callTableAction('create', data: [
            'label' => ['es' => 'Historia'],
            'link_type' => 'url',
            'url' => '/institucion/historia',
            'parent_id' => $parent->id,
            'sort_order' => 0,
            'is_active' => true,
        ])
        ->assertHasNoTableActionErrors();

    $child = MenuItem::query()->where('label->es', 'Historia')->first();

    expect($child->parent_id)->toBe($parent->id);
});

it('un usuario sin permiso no puede ver el listado de menús', function () {
    $editorAcademico = User::factory()->create(['is_active' => true]);
    $editorAcademico->assignRole('editor_academico');

    $this->actingAs($editorAcademico);

    $this->livewire(ListMenus::class)->assertForbidden();
});
