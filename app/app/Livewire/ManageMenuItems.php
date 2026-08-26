<?php

namespace App\Livewire;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Reemplaza la vieja `ItemsRelationManager` (tabla plana con un selector
 * manual de "aparece dentro de") por un árbol arrastrable: reordenar se hace
 * arrastrando, y convertir un enlace en submenú se hace arrastrándolo dentro
 * de otro — sin tocar ningún desplegable (pedido del cliente, Fase 10).
 *
 * Tope de 2 niveles (raíz + submenú, sin submenú-de-submenú): es la misma
 * profundidad que ya renderizan `site-header`/`site-footer` — permitir más
 * nivel acá crearía enlaces que el sitio público no sabría dibujar.
 */
class ManageMenuItems extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Menu $record;

    /**
     * @return Collection<int|string, \Illuminate\Database\Eloquent\Collection<int, MenuItem>>
     */
    public function getItemsProperty(): Collection
    {
        return collect(
            MenuItem::query()
                ->where('menu_id', $this->record->id)
                ->orderBy('sort_order')
                ->get()
                ->groupBy('parent_id')
                ->all()
        );
    }

    public function toggleActive(int $itemId): void
    {
        $item = MenuItem::query()->where('menu_id', $this->record->id)->findOrFail($itemId);

        $item->update(['is_active' => ! $item->is_active]);
    }

    /**
     * @param  array<int, array{id: int, parent_id: int|null, sort_order: int}>  $tree
     */
    public function updateOrder(array $tree): void
    {
        $ids = collect($tree)->pluck('id');

        $items = MenuItem::query()
            ->where('menu_id', $this->record->id)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        if ($items->count() !== $ids->unique()->count()) {
            return;
        }

        $parentIdsInPayload = collect($tree)->pluck('parent_id')->filter()->unique();

        foreach ($tree as $node) {
            $parentId = $node['parent_id'] ?? null;

            if ($parentId === null) {
                continue;
            }

            $parent = $items->get($parentId);

            if ((! $parent) || $parent->parent_id !== null || $parentIdsInPayload->contains($node['id'])) {
                Notification::make()
                    ->warning()
                    ->title('No se pudo mover ese enlace')
                    ->body('Un submenú no puede tener a su vez submenús propios.')
                    ->send();

                return;
            }
        }

        foreach ($tree as $node) {
            $items->get($node['id'])?->update([
                'parent_id' => $node['parent_id'] ?? null,
                'sort_order' => $node['sort_order'],
            ]);
        }
    }

    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Agregar enlace')
            ->modalHeading(fn (array $arguments): string => filled($arguments['parent_id'] ?? null) ? 'Nuevo submenú' : 'Nuevo enlace')
            ->schema($this->itemForm())
            ->action(function (array $data, array $arguments): void {
                $parentId = $arguments['parent_id'] ?? null;

                MenuItem::query()->create([
                    ...$this->toDatabaseAttributes($data),
                    'menu_id' => $this->record->id,
                    'parent_id' => $parentId,
                    'sort_order' => 1 + (MenuItem::query()
                        ->where('menu_id', $this->record->id)
                        ->where('parent_id', $parentId)
                        ->max('sort_order') ?? -1),
                ]);
            });
    }

    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Editar')
            ->modalHeading('Editar enlace')
            ->schema($this->itemForm())
            ->fillForm(function (array $arguments): array {
                $item = MenuItem::query()->where('menu_id', $this->record->id)->findOrFail($arguments['item']);

                return $this->toFormState($item->attributesToArray());
            })
            ->action(function (array $data, array $arguments): void {
                $item = MenuItem::query()->where('menu_id', $this->record->id)->findOrFail($arguments['item']);

                $item->update($this->toDatabaseAttributes($data));
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Borrar')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Borrar enlace')
            ->modalDescription('Si este enlace tiene submenús, también se borran. Esta acción no se puede deshacer.')
            ->action(function (array $arguments): void {
                MenuItem::query()->where('menu_id', $this->record->id)->findOrFail($arguments['item'])->delete();
            });
    }

    /**
     * @return array<int, mixed>
     */
    private function itemForm(): array
    {
        return [
            Tabs::make('label_idiomas')
                ->tabs([
                    Tab::make('Español')->schema([
                        TextInput::make('label.es')
                            ->label('Texto del enlace')
                            ->required()
                            ->maxLength(255),
                    ]),
                    Tab::make('Italiano')
                        ->schema([TextInput::make('label.it')->label('Texto del enlace')->maxLength(255)])
                        ->visible(fn () => SiteSetting::italianEnabled()),
                ]),

            Radio::make('link_type')
                ->label('Este enlace apunta a')
                ->options([
                    'page' => 'Una página del sitio',
                    'post' => 'Una noticia',
                    'url' => 'Una dirección web escrita a mano',
                ])
                ->default('url')
                ->live()
                ->required(),

            Select::make('linkable_page_id')
                ->label('Página')
                ->options(fn () => Page::query()->get()->mapWithKeys(
                    fn (Page $page) => [$page->id => $page->getTranslation('title', 'es')]
                ))
                ->searchable()
                ->preload()
                ->required(fn (Get $get) => $get('link_type') === 'page')
                ->visible(fn (Get $get) => $get('link_type') === 'page'),

            Select::make('linkable_post_id')
                ->label('Noticia')
                ->options(fn () => Post::query()->get()->mapWithKeys(
                    fn (Post $post) => [$post->id => $post->getTranslation('title', 'es')]
                ))
                ->searchable()
                ->preload()
                ->required(fn (Get $get) => $get('link_type') === 'post')
                ->visible(fn (Get $get) => $get('link_type') === 'post'),

            TextInput::make('url')
                ->label('Dirección web (URL)')
                ->helperText('Puede ser una ruta interna (ej: /contacto) o una dirección completa (ej: https://...).')
                ->maxLength(255)
                ->required(fn (Get $get) => $get('link_type') === 'url')
                ->visible(fn (Get $get) => $get('link_type') === 'url'),

            Toggle::make('open_in_new_tab')->label('Abrir en una pestaña nueva'),
            Toggle::make('is_active')->label('Activo (visible en el sitio)')->default(true),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toDatabaseAttributes(array $data): array
    {
        $linkType = $data['link_type'] ?? 'url';

        $data['linkable_type'] = match ($linkType) {
            'page' => Page::class,
            'post' => Post::class,
            default => null,
        };

        $data['linkable_id'] = match ($linkType) {
            'page' => $data['linkable_page_id'] ?? null,
            'post' => $data['linkable_post_id'] ?? null,
            default => null,
        };

        if ($linkType !== 'url') {
            $data['url'] = null;
        }

        unset($data['link_type'], $data['linkable_page_id'], $data['linkable_post_id']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function toFormState(array $data): array
    {
        $data['link_type'] = match ($data['linkable_type'] ?? null) {
            Page::class => 'page',
            Post::class => 'post',
            default => 'url',
        };

        $data['linkable_page_id'] = $data['linkable_type'] === Page::class ? $data['linkable_id'] : null;
        $data['linkable_post_id'] = $data['linkable_type'] === Post::class ? $data['linkable_id'] : null;

        return $data;
    }

    public function render()
    {
        return view('livewire.manage-menu-items');
    }
}
