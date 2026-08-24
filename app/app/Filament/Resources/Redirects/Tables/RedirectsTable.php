<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_path')->label('URL vieja')->searchable()->copyable(),
                TextColumn::make('to_path')->label('URL nueva')->searchable(),
                TextColumn::make('status_code')->label('Tipo')->badge(),
                TextColumn::make('hits')->label('Usos')->sortable(),
                IconColumn::make('is_active')->label('Activa')->boolean(),
            ])
            ->defaultSort('hits', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')]),
            ]);
    }
}
