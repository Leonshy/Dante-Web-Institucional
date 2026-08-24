<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FormSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->disabled(),
            TextInput::make('email')->label('Correo')->disabled(),
            TextInput::make('phone')->label('Teléfono')->disabled(),
            Textarea::make('message')->label('Mensaje')->disabled()->columnSpanFull(),
            Select::make('status')
                ->label('Estado')
                ->options(['nuevo' => 'Nuevo', 'leido' => 'Leído', 'respondido' => 'Respondido', 'archivado' => 'Archivado'])
                ->required(),
        ]);
    }
}
