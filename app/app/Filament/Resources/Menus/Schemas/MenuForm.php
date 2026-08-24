<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')
                ->label('Identificador')
                ->helperText('Nombre corto interno para reconocer el menú, ej: "principal" o "pie". No se muestra en el sitio.')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('name')
                ->label('Nombre del menú')
                ->helperText('Ej: "Menú principal" o "Menú del pie de página".')
                ->required()
                ->maxLength(255),
        ]);
    }
}
