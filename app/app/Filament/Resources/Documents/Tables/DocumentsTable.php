<?php

namespace App\Filament\Resources\Documents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))->searchable(),
                TextColumn::make('category.name')->label('Categoría')->badge(),
                TextColumn::make('site')->label('Sede')->badge(),
                IconColumn::make('is_current')->label('Vigente')->boolean(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('published_at')->label('Publicación')->date('d/m/Y')->sortable(),
                TextColumn::make('public_url')
                    ->label('Enlace público')
                    ->state(fn ($record): ?string => $record->file?->url())
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('¡Enlace copiado!')
                    ->icon('heroicon-o-link'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado']),
                SelectFilter::make('site')->label('Sede')->options(['asuncion' => 'Asunción', 'fernando-de-la-mora' => 'Fernando de la Mora', 'ambas' => 'Ambas sedes']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
