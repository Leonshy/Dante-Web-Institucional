<?php

namespace App\Filament\Resources\Media\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('upload')
                ->label('Archivo')
                ->storeFiles(false)
                ->visibleOn('create')
                ->required(),
            TextInput::make('alt')
                ->label('Texto alternativo')
                ->helperText('Describí qué muestra la imagen — lo necesitan las personas que usan lector de pantalla y ayuda al posicionamiento en Google.')
                ->maxLength(255),
            TextInput::make('title')->label('Título (opcional)')->maxLength(255),
            TextInput::make('folder')->label('Carpeta')->default('general'),
        ]);
    }
}
