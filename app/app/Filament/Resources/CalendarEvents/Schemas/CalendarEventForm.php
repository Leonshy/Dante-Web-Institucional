<?php

namespace App\Filament\Resources\CalendarEvents\Schemas;

use App\Models\SiteSetting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class CalendarEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Evento')->schema([
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
            ]),
            Section::make('Fecha y nivel')->schema([
                DateTimePicker::make('starts_at')->label('Comienza')->required(),
                DateTimePicker::make('ends_at')->label('Termina (opcional)'),
                Toggle::make('all_day')->label('Todo el día'),
                Select::make('level')
                    ->label('Nivel educativo')
                    ->options([
                        'inicial' => 'Inicial',
                        'primaria' => 'Primaria',
                        'secundaria' => 'Secundaria',
                        'instituto-de-idiomas' => 'Instituto de Idiomas',
                        'todo-el-colegio' => 'Todo el colegio',
                    ])
                    ->default('todo-el-colegio')
                    ->required(),
            ])->columns(2),
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
