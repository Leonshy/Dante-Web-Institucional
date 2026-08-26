<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Blocks\PageBlocks;
use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Tables\MediaLibraryTable;
use App\Models\Page;
use App\Models\SiteSetting;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        // `EditRecord`/`CreateRecord` imponen `columns(2)` al schema raíz salvo
        // que el propio formulario ya declare los suyos (`hasCustomColumns()`)
        // — sin este `->columns(3)` acá, el Group de abajo quedaba metido
        // dentro de una sola celda de esa grilla ajena de 2 columnas, dejando
        // la otra mitad de la pantalla vacía en vez de usar el ancho completo.
        return $schema
            ->columns(3)
            ->components([
                // Contenido de la página, uno abajo del otro en la columna
                // izquierda: un Group apila sus hijos por su propia altura,
                // sin esperar a que la columna de configuraciones "complete
                // la fila" (eso era lo que dejaba huecos verticales).
                Group::make([
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
                ])->columnSpan(2),

                // Configuración de la página, uno abajo del otro en la
                // columna derecha.
                Group::make([
                    Section::make('Portada')
                        ->description('Imagen de cabecera de la página. También se usa como miniatura cuando la página se muestra en listados o tarjetas de otras secciones.')
                        ->schema([
                            MediaPicker::make('cover_media_id')
                                ->label('Imagen de portada')
                                ->tableConfiguration(MediaLibraryTable::class),
                        ]),

                    Section::make('Adelanto en el inicio')
                        ->description('Si activás esto, la página aparece como tarjeta en la sección "Páginas destacadas" del inicio (si esa sección está activada desde el panel de Inicio). Usa la misma imagen de portada de arriba.')
                        ->schema([
                            Toggle::make('is_featured_home')
                                ->label('Destacar en el inicio'),
                            Tabs::make('home_excerpt_idiomas')
                                ->tabs([
                                    Tab::make('Español')->schema([
                                        Textarea::make('home_excerpt.es')
                                            ->label('Bajada corta para la tarjeta del inicio')
                                            ->rows(2),
                                    ]),
                                    Tab::make('Italiano')->schema([
                                        Textarea::make('home_excerpt.it')
                                            ->label('Bajada corta para la tarjeta del inicio')
                                            ->rows(2),
                                    ])->visible(fn () => SiteSetting::italianEnabled()),
                                ]),
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
                        ]),

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
                            TextInput::make('canonical_url')
                                ->label('URL canónica (avanzado)')
                                ->helperText('Dejar vacío salvo que esta página duplique el contenido de otra — ahí sí cargá la URL completa de la página "original".')
                                ->url()
                                ->maxLength(255),
                            MediaPicker::make('seo_image_id')
                                ->label('Imagen para compartir (Open Graph)')
                                ->helperText('La imagen que se muestra al compartir esta página en redes sociales. Si la dejás vacía, se usa la imagen de portada.')
                                ->tableConfiguration(MediaLibraryTable::class),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
