<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('label')->label('Nombre')->disabled(),
            TextInput::make('key')->label('Clave interna')->disabled(),
            Toggle::make('value')
                ->label('Valor')
                ->visible(fn ($record) => $record?->type === 'boolean')
                ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0')
                ->formatStateUsing(fn ($record) => (bool) $record?->value),
            Textarea::make('value')
                ->label('Valor')
                ->visible(fn ($record) => $record?->type !== 'boolean')
                ->rows(3),
        ]);
    }
}
