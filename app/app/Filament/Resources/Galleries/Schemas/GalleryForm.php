<?php

namespace App\Filament\Resources\Galleries\Schemas;

use App\Filament\Forms\Components\MediaPicker;
use App\Filament\Tables\MediaLibraryTable;
use App\Models\Gallery;
use App\Models\SiteSetting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Álbum')->schema([
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
                DatePicker::make('event_date')->label('Fecha del evento')->required(),
                Select::make('site')
                    ->label('Sede')
                    ->options([
                        'asuncion' => 'Asunción',
                        'fernando-de-la-mora' => 'Fernando de la Mora',
                        'ambas' => 'Ambas sedes',
                    ])
                    ->default('ambas')
                    ->required(),
            ]),
            Section::make('Fotos del álbum')->schema([
                MediaPicker::make('media')
                    ->label('Fotos')
                    ->multiple()
                    ->tableConfiguration(MediaLibraryTable::class)
                    ->afterStateHydrated(function ($component, ?Gallery $record) {
                        $component->state($record ? $record->media->pluck('id')->all() : []);
                    }),
            ]),
            Section::make('Publicación')->schema([
                Select::make('status')
                    ->label('Estado')
                    ->options(['draft' => 'Borrador', 'published' => 'Publicado'])
                    ->default('draft')
                    ->required(),
            ]),
        ]);
    }
}
