<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Blocks\PageBlocks;
use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Título')
                ->schema([
                    Tabs::make('titulo_idiomas')
                        ->tabs([
                            Tab::make('Español')->schema([
                                TextInput::make('title.es')
                                    ->label('Título')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, ?Page $record) {
                                        if (! $record) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->maxLength(255),
                            ]),
                            Tab::make('Italiano')
                                ->schema([TextInput::make('title.it')->label('Título')->maxLength(255)])
                                ->visible(fn () => SiteSetting::italianEnabled()),
                        ]),
                    TextInput::make('slug')
                        ->label('Dirección web de la página (URL)')
                        ->helperText('Se genera sola a partir del título en español, pero podés editarla. Ej: "institucion/historia"')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),

            Section::make('Contenido')
                ->description('Armá la página combinando bloques. El italiano es opcional dentro de cada bloque — si lo dejás vacío, se muestra el texto en español.')
                ->schema([
                    Builder::make('blocks')
                        ->hiddenLabel()
                        ->blocks(PageBlocks::for())
                        ->addActionLabel('Agregar bloque')
                        ->collapsible()
                        ->blockNumbers(false),
                ]),

            Section::make('Ubicación en el sitio')
                ->description('Dónde aparece esta página dentro del menú y la navegación.')
                ->schema([
                    Select::make('site_section')
                        ->label('Sección del menú')
                        ->options([
                            'institucion' => 'Institución',
                            'oferta-educativa' => 'Oferta educativa',
                            'admisiones' => 'Admisiones',
                            'vida-escolar' => 'Vida escolar',
                            'general' => 'General (sin sección, ej. Inicio, Contacto)',
                        ])
                        ->required(),
                    Select::make('parent_id')
                        ->label('Página dentro de (opcional)')
                        ->helperText('Elegí una página "padre" si esta es una subpágina, por ejemplo "Historia" dentro de "Institución".')
                        ->relationship('parent', 'slug')
                        ->searchable()
                        ->preload()
                        ->nullable(),
                    TextInput::make('sort_order')
                        ->label('Orden dentro del menú')
                        ->numeric()
                        ->default(0)
                        ->helperText('Los números más bajos aparecen primero.'),
                ])->columns(3),

            Section::make('Publicación')
                ->schema([
                    Select::make('status')
                        ->label('Estado')
                        ->options([
                            'draft' => 'Borrador (no visible en el sitio)',
                            'published' => 'Publicada',
                            'archived' => 'Archivada (fuera de menús, visible solo por enlace directo)',
                        ])
                        ->default('draft')
                        ->required(),
                ]),

            Section::make('Buscadores (SEO)')
                ->description('Cómo se ve esta página en Google. Si lo dejás vacío, se usa el título de la página.')
                ->collapsed()
                ->schema([
                    Tabs::make('seo_idiomas')
                        ->tabs([
                            Tab::make('Español')->schema([
                                TextInput::make('seo_title.es')
                                    ->label('Título para buscadores')
                                    ->maxLength(60),
                                Textarea::make('seo_description.es')
                                    ->label('Descripción para buscadores')
                                    ->maxLength(160)
                                    ->rows(2),
                            ]),
                            Tab::make('Italiano')->schema([
                                TextInput::make('seo_title.it')->label('Título para buscadores')->maxLength(60),
                                Textarea::make('seo_description.it')->label('Descripción para buscadores')->maxLength(160)->rows(2),
                            ])->visible(fn () => SiteSetting::italianEnabled()),
                        ]),
                    Toggle::make('is_indexable')
                        ->label('Permitir que Google indexe esta página')
                        ->default(true),
                ]),
        ]);
    }
}
