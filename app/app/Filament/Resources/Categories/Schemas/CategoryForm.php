<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->label('Se usa para')
                ->options(['news' => 'Noticias', 'document' => 'Documentos', 'gallery' => 'Galería'])
                ->required(),
            TextInput::make('name.es')
                ->label('Nombre')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
            TextInput::make('slug')->label('Dirección web (URL)')->required()->unique(ignoreRecord: true),
            Toggle::make('is_active')->label('Activa')->default(true),
        ]);
    }
}
