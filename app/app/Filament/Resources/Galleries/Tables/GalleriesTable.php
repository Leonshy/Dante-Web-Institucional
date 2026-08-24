<?php

namespace App\Filament\Resources\Galleries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GalleriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))->searchable(),
                TextColumn::make('site')->label('Sede')->badge(),
                TextColumn::make('media_count')->label('Fotos')->counts('media'),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('event_date')->label('Fecha del evento')->date('d/m/Y')->sortable(),
            ])
            ->defaultSort('event_date', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(['draft' => 'Borrador', 'published' => 'Publicado']),
                SelectFilter::make('site')->label('Sede')->options(['asuncion' => 'Asunción', 'fernando-de-la-mora' => 'Fernando de la Mora', 'ambas' => 'Ambas sedes']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
