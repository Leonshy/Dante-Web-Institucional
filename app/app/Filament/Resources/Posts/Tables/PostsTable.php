<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))->searchable(),
                TextColumn::make('category.name')->label('Categoría')->badge(),
                IconColumn::make('is_featured')->label('Destacada')->boolean(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('published_at')->label('Publicación')->date('d/m/Y')->sortable(),
                TextColumn::make('public_url')
                    ->label('Enlace público')
                    ->state(fn ($record): string => route('posts.show', $record->slug))
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('¡Enlace copiado!')
                    ->icon('heroicon-o-link'),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(['draft' => 'Borrador', 'published' => 'Publicada', 'archived' => 'Archivada']),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
