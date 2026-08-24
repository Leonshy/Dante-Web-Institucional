<?php

namespace App\Filament\Resources\Menus\RelationManagers;

use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Gestión de los enlaces (`MenuItem`) de un menú. Se muestran todos los ítems
 * del menú (raíz + hijos) en una sola tabla plana ordenada por jerarquía y
 * orden — pensado para 1-2 niveles como mucho, no un árbol infinito
 * (docs/02 §7). Por eso se sobrescribe la consulta de la tabla en vez de
 * usar solo la relación `Menu::items()` (que filtra a los ítems raíz).
 */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Enlaces del menú';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
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

            Select::make('parent_id')
                ->label('Aparece dentro de (submenú de)')
                ->helperText('Dejalo vacío para que sea un enlace de primer nivel.')
                ->options(fn (?MenuItem $record) => MenuItem::query()
                    ->where('menu_id', $this->getOwnerRecord()->getKey())
                    ->whereNull('parent_id')
                    ->when($record, fn ($query, MenuItem $current) => $query->whereKeyNot($current->getKey()))
                    ->get()
                    ->mapWithKeys(fn (MenuItem $item) => [$item->id => $item->getTranslation('label', 'es')]))
                ->searchable()
                ->nullable(),

            TextInput::make('sort_order')
                ->label('Orden')
                ->helperText('Los números más bajos aparecen primero.')
                ->numeric()
                ->default(0)
                ->required(),

            Toggle::make('open_in_new_tab')->label('Abrir en una pestaña nueva'),
            Toggle::make('is_active')->label('Activo (visible en el sitio)')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => MenuItem::query()->where('menu_id', $this->getOwnerRecord()->getKey()))
            ->defaultSort('parent_id')
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')
                    ->label('Texto')
                    ->formatStateUsing(fn (MenuItem $record) => ($record->parent_id ? '— ' : '').$record->getTranslation('label', 'es'))
                    ->searchable(query: fn ($query, string $search) => $query->where('label', 'like', "%{$search}%")),
                TextColumn::make('resolved_url')
                    ->label('Destino')
                    ->state(fn (MenuItem $record) => $record->resolvedUrl()),
                TextColumn::make('sort_order')->label('Orden')->sortable(),
                IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(fn (array $data): array => $this->toDatabaseAttributes($data)),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data): array => $this->toFormState($data))
                    ->mutateFormDataUsing(fn (array $data): array => $this->toDatabaseAttributes($data)),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Convierte los campos auxiliares del formulario (link_type, linkable_page_id,
     * linkable_post_id) al par polimórfico real que guarda el modelo.
     *
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
     * Reconstruye los campos auxiliares del formulario a partir del registro
     * guardado, para que el modal de edición muestre la opción correcta.
     *
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
}
