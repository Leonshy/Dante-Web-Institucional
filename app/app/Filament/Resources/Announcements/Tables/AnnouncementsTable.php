<?php

namespace App\Filament\Resources\Announcements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))->searchable(),
                TextColumn::make('audience')->label('Dirigido a')->badge(),
                IconColumn::make('is_pinned')->label('Fijado')->boolean(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('published_at')->label('Publicación')->date('d/m/Y')->sortable(),
                TextColumn::make('valid_until')->label('Vigente hasta')->date('d/m/Y'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado']),
                SelectFilter::make('audience')->label('Dirigido a')->options([
                    'toda-la-comunidad' => 'Toda la comunidad',
                    'asuncion' => 'Solo Asunción',
                    'fernando-de-la-mora' => 'Solo Fernando de la Mora',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
