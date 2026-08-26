<?php

namespace App\Filament\Resources\Media\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->type === 'image' ? $record->url() : null)
                    ->size(60),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('alt')->label('Texto alternativo')->limit(40),
                TextColumn::make('type')->label('Tipo')->badge(),
                TextColumn::make('size')->label('Peso')->formatStateUsing(fn (int $state) => number_format($state / 1024, 0).' KB'),
                TextColumn::make('created_at')->label('Subido')->dateTime('d/m/Y')->sortable(),
                TextColumn::make('public_url')
                    ->label('Enlace público')
                    ->state(fn ($record): string => $record->url())
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('¡Enlace copiado!')
                    ->icon('heroicon-o-link'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
