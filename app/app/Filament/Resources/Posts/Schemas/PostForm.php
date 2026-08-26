<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Tables\MediaLibraryTable;
use App\Models\SiteSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
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

class PostForm
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
                // Contenido de la noticia, uno abajo del otro en la columna
                // izquierda: un Group apila sus hijos por su propia altura,
                // sin esperar a que la columna de configuraciones "complete
                // la fila" (eso dejaba huecos).
                Group::make([
                    Section::make('Noticia')->schema([
                        Tabs::make('idiomas')->tabs([
                            Tab::make('Español')->schema([
                                TextInput::make('title.es')->label('Título')->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state)))
                                    ->maxLength(255),
                                Textarea::make('excerpt.es')->label('Bajada (resumen para el listado)')->required()->rows(2),
                                RichEditor::make('content.es')->label('Contenido')->required(),
                            ]),
                            Tab::make('Italiano')->schema([
                                TextInput::make('title.it')->label('Título')->maxLength(255),
                                Textarea::make('excerpt.it')->label('Bajada')->rows(2),
                                RichEditor::make('content.it')->label('Contenido'),
                            ])->visible(fn () => SiteSetting::italianEnabled()),
                        ]),
                        TextInput::make('slug')->label('Dirección web (URL)')->required()->unique(ignoreRecord: true),
                    ]),
                ])->columnSpan(2),

                // Configuración de la noticia, uno abajo del otro en la
                // columna derecha.
                Group::make([
                    Section::make('Clasificación')->schema([
                        Select::make('category_id')->label('Categoría')->relationship('category', 'name')->searchable()->preload(),
                        DatePicker::make('published_at')->label('Fecha de publicación')->required(),
                        Toggle::make('is_featured')->label('Destacar en el inicio'),
                    ]),
                    Section::make('Imagen destacada')->schema([
                        MediaPicker::make('featured_media_id')
                            ->label('Imagen')
                            ->tableConfiguration(MediaLibraryTable::class),
                    ]),
                    Section::make('Publicación')->schema([
                        Select::make('status')
                            ->label('Estado')
                            ->options(['draft' => 'Borrador', 'published' => 'Publicada', 'archived' => 'Archivada'])
                            ->default('draft')
                            ->required(),
                    ]),
                    Section::make('Buscadores (SEO)')
                        ->description('Cómo se ve esta noticia en Google. Si lo dejás vacío, se usa el título de la noticia.')
                        ->collapsed()
                        ->schema([
                            Tabs::make('seo_idiomas')->tabs([
                                Tab::make('Español')->schema([
                                    TextInput::make('seo_title.es')->label('Título para buscadores')->maxLength(60),
                                    Textarea::make('seo_description.es')->label('Descripción para buscadores')->maxLength(160)->rows(2),
                                ]),
                                Tab::make('Italiano')->schema([
                                    TextInput::make('seo_title.it')->label('Título para buscadores')->maxLength(60),
                                    Textarea::make('seo_description.it')->label('Descripción para buscadores')->maxLength(160)->rows(2),
                                ])->visible(fn () => SiteSetting::italianEnabled()),
                            ]),
                            Toggle::make('is_indexable')
                                ->label('Permitir que Google indexe esta noticia')
                                ->default(true),
                        ]),
                ])->columnSpan(1),
            ]);
    }
}
