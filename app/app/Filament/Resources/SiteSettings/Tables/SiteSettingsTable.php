<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Nombre')->searchable(),
                TextColumn::make('group')->label('Sección')->badge(),
                TextColumn::make('value')->label('Valor actual')->limit(40),
            ])
            ->defaultSort('group')
            ->filters([
                SelectFilter::make('group')->label('Sección')->options([
                    'general' => 'General',
                    'contacto' => 'Contacto',
                    'idioma' => 'Idioma',
                    'integraciones' => 'Integraciones',
                    'formularios' => 'Formularios',
                ]),
            ])
            ->recordActions([EditAction::make()->label('Editar')]);
    }
}
