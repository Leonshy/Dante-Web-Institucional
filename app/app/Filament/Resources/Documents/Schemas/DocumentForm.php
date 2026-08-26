<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Tables\MediaLibraryTable;
use App\Models\SiteSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Documento')->schema([
                Tabs::make('idiomas')->tabs([
                    Tab::make('Español')->schema([
                        TextInput::make('title.es')->label('Título')->required()->maxLength(255),
                        Textarea::make('description.es')->label('Descripción (opcional)')->rows(2),
                    ]),
                    Tab::make('Italiano')->schema([
                        TextInput::make('title.it')->label('Título')->maxLength(255),
                        Textarea::make('description.it')->label('Descripción')->rows(2),
                    ])->visible(fn () => SiteSetting::italianEnabled()),
                ]),
                MediaPicker::make('media_id')
                    ->label('Archivo')
                    ->anyFileType()
                    ->tableConfiguration(MediaLibraryTable::class)
                    ->required(),
            ]),
            Section::make('Clasificación')->schema([
                Select::make('category_id')->label('Categoría')->relationship('category', 'name')->searchable()->preload(),
                Select::make('site')
                    ->label('Sede')
                    ->options([
                        'asuncion' => 'Asunción',
                        'fernando-de-la-mora' => 'Fernando de la Mora',
                        'ambas' => 'Ambas sedes',
                    ])
                    ->default('ambas')
                    ->required(),
                DatePicker::make('published_at')->label('Fecha de publicación'),
                Toggle::make('is_current')->label('Vigente')->default(true)
                    ->helperText('Desactivalo cuando el documento quede desactualizado, sin borrarlo del historial.'),
            ])->columns(2),
            Section::make('Publicación')->schema([
                Select::make('status')
                    ->label('Estado')
                    ->options(['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'])
                    ->default('draft')
                    ->required(),
            ]),
        ]);
    }
}
