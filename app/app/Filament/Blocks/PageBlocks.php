<?php

namespace App\Filament\Blocks;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\SiteSetting;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Catálogo de bloques del constructor de páginas — implementa los 16 bloques
 * de docs/02-ux-arquitectura-informacion.md §8, cubriendo todo el inventario
 * de contenido real migrado.
 *
 * Cada bloque envuelve sus campos de texto en pestañas Español/Italiano —
 * el italiano solo se muestra si está habilitado en la configuración global
 * (ADR-002). Los campos quedan guardados como {"es": "...", "it": "..."}.
 */
class PageBlocks
{
    /** @return array<int, Block> */
    public static function for(): array
    {
        return [
            Block::make('hero')
                ->label('Hero (portada de la página)')
                ->icon('heroicon-o-photo')
                ->schema([
                    self::bilingual('title', fn (string $name) => TextInput::make($name)->label('Título')->required($name === 'title.es')),
                    self::bilingual('subtitle', fn (string $name) => TextInput::make($name)->label('Bajada')),
                    FileUpload::make('image')->label('Imagen de fondo')->image()->directory('bloques'),
                    self::bilingual('cta_label', fn (string $name) => TextInput::make($name)->label('Texto del botón')),
                    TextInput::make('cta_url')->label('Enlace del botón'),
                ]),

            Block::make('texto')
                ->label('Texto enriquecido')
                ->icon('heroicon-o-document-text')
                ->schema([
                    self::bilingual('content', fn (string $name) => RichEditor::make($name)->label('Contenido')->required($name === 'content.es')),
                ]),

            Block::make('imagen_texto')
                ->label('Imagen + texto')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('image')->label('Imagen')->image()->directory('bloques')->required(),
                    self::bilingual('title', fn (string $name) => TextInput::make($name)->label('Título')),
                    self::bilingual('text', fn (string $name) => RichEditor::make($name)->label('Texto')),
                ]),

            Block::make('tarjetas')
                ->label('Tarjetas')
                ->icon('heroicon-o-squares-2x2')
                ->schema([
                    Repeater::make('items')
                        ->label('Tarjetas')
                        ->schema([
                            self::bilingual('title', fn (string $name) => TextInput::make($name)->label('Título')->required($name === 'title.es')),
                            self::bilingual('text', fn (string $name) => TextInput::make($name)->label('Texto')),
                            TextInput::make('url')->label('Enlace (opcional)'),
                        ])
                        ->columns(1),
                ]),

            Block::make('cta')
                ->label('Llamado a la acción destacado')
                ->icon('heroicon-o-megaphone')
                ->schema([
                    self::bilingual('title', fn (string $name) => TextInput::make($name)->label('Título')->required($name === 'title.es')),
                    self::bilingual('text', fn (string $name) => TextInput::make($name)->label('Texto')),
                    self::bilingual('button_label', fn (string $name) => TextInput::make($name)->label('Texto del botón')),
                    TextInput::make('button_url')->label('Enlace del botón'),
                ]),

            Block::make('cifras')
                ->label('Cifras / hitos institucionales')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Repeater::make('items')
                        ->label('Cifras')
                        ->schema([
                            TextInput::make('number')->label('Número o año')->required(),
                            self::bilingual('label', fn (string $name) => TextInput::make($name)->label('Etiqueta')->required($name === 'label.es')),
                        ])
                        ->columns(2),
                ]),

            Block::make('listado_noticias')
                ->label('Listado de noticias')
                ->icon('heroicon-o-newspaper')
                ->schema([
                    TextInput::make('count')->label('Cantidad de noticias a mostrar')->numeric()->default(3),
                ]),

            Block::make('galeria')
                ->label('Galería de fotos')
                ->icon('heroicon-o-photo')
                ->schema([
                    Select::make('gallery_id')
                        ->label('Álbum')
                        ->options(fn () => Gallery::query()->pluck('title', 'id')->map(fn ($title) => is_array($title) ? ($title['es'] ?? reset($title)) : $title))
                        ->searchable()
                        ->required()
                        ->helperText('Los álbumes se cargan primero desde la sección Galerías.'),
                    Select::make('layout')
                        ->label('Disposición')
                        ->options(['grid' => 'Cuadrícula', 'carrusel' => 'Carrusel'])
                        ->default('grid')
                        ->required(),
                    self::siteSelect(),
                ]),

            Block::make('faq')
                ->label('Acordeón / Preguntas frecuentes')
                ->icon('heroicon-o-question-mark-circle')
                ->schema([
                    Repeater::make('items')
                        ->label('Preguntas')
                        ->schema([
                            self::bilingual('question', fn (string $name) => TextInput::make($name)->label('Pregunta')->required($name === 'question.es')),
                            self::bilingual('answer', fn (string $name) => RichEditor::make($name)->label('Respuesta')->required($name === 'answer.es')),
                        ])
                        ->columns(1),
                ]),

            Block::make('video')
                ->label('Video')
                ->icon('heroicon-o-play-circle')
                ->schema([
                    TextInput::make('url')->label('Enlace del video (YouTube / Vimeo)')->url(),
                    FileUpload::make('file')->label('O archivo de video')->directory('bloques')->acceptedFileTypes(['video/mp4']),
                    FileUpload::make('cover')->label('Imagen de portada (opcional)')->image()->directory('bloques'),
                ]),

            Block::make('testimonios')
                ->label('Testimonios')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->schema([
                    Repeater::make('items')
                        ->label('Testimonios')
                        ->schema([
                            FileUpload::make('photo')->label('Foto')->image()->directory('bloques'),
                            TextInput::make('name')->label('Nombre')->required(),
                            TextInput::make('role')->label('Rol (ej: madre de alumno, exalumno)'),
                            self::bilingual('text', fn (string $name) => RichEditor::make($name)->label('Testimonio')->required($name === 'text.es')),
                        ])
                        ->columns(1),
                ]),

            Block::make('mapa')
                ->label('Mapa')
                ->icon('heroicon-o-map-pin')
                ->schema([
                    TextInput::make('address')->label('Dirección')->required(),
                    TextInput::make('latitude')->label('Latitud')->numeric(),
                    TextInput::make('longitude')->label('Longitud')->numeric(),
                ]),

            Block::make('formulario')
                ->label('Formulario')
                ->icon('heroicon-o-envelope')
                ->schema([
                    Select::make('form_type')
                        ->label('Formulario a mostrar')
                        ->options(['contacto' => 'Contacto', 'preinscripcion' => 'Pre-inscripción'])
                        ->required(),
                ]),

            Block::make('listado_comunicados')
                ->label('Listado de comunicados')
                ->icon('heroicon-o-speaker-wave')
                ->schema([
                    TextInput::make('count')->label('Cantidad de comunicados a mostrar')->numeric()->default(5),
                    self::siteSelect(),
                ]),

            Block::make('documentos')
                ->label('Documentos descargables')
                ->icon('heroicon-o-document-arrow-down')
                ->schema([
                    Select::make('category_id')
                        ->label('Categoría de documentos')
                        ->options(fn () => Category::query()->where('type', 'document')->pluck('name', 'id')->map(fn ($name) => is_array($name) ? ($name['es'] ?? reset($name)) : $name))
                        ->searchable()
                        ->required()
                        ->helperText('Se listan todos los documentos vigentes de esta categoría (gestionada en la sección Documentos).'),
                ]),

            Block::make('selector_sede')
                ->label('Selector de sede')
                ->icon('heroicon-o-map')
                ->schema([
                    self::siteSelect(),
                ]),
        ];
    }

    private static function siteSelect(): Select
    {
        return Select::make('site')
            ->label('Sede')
            ->options([
                'asuncion' => 'Asunción',
                'fernando-de-la-mora' => 'Fernando de la Mora',
                'ambas' => 'Ambas sedes',
            ])
            ->default('ambas')
            ->required();
    }

    private static function bilingual(string $field, \Closure $factory): Tabs
    {
        return Tabs::make($field)
            ->contained(false)
            ->tabs([
                Tab::make('Español')->schema([$factory("{$field}.es")]),
                Tab::make('Italiano')->schema([$factory("{$field}.it")])->visible(fn () => SiteSetting::italianEnabled()),
            ]);
    }
}
