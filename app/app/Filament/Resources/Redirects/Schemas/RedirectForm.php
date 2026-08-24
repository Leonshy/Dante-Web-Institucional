<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')
                ->label('Dirección web vieja')
                ->helperText('Ej: /noticias-antiguas/mi-noticia')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            TextInput::make('to_path')
                ->label('Dirección web nueva a la que redirige')
                ->required()
                ->maxLength(255),
            Select::make('status_code')
                ->label('Tipo de redirección')
                ->options([301 => '301 — permanente', 302 => '302 — temporal'])
                ->default(301)
                ->required(),
            Toggle::make('is_active')->label('Activa')->default(true),
        ]);
    }
}
