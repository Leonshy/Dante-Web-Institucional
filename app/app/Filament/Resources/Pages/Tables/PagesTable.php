<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('slug')->label('URL')->prefix('/')->copyable(),
                TextColumn::make('site_section')->label('Sección')->badge(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'draft' => 'gray',
                        'archived' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'published' => 'Publicada',
                        'draft' => 'Borrador',
                        'archived' => 'Archivada',
                        default => $state,
                    }),
                TextColumn::make('updated_at')->label('Última edición')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('site_section')
                    ->label('Sección')
                    ->options([
                        'institucion' => 'Institución',
                        'oferta-educativa' => 'Oferta educativa',
                        'admisiones' => 'Admisiones',
                        'vida-escolar' => 'Vida escolar',
                        'general' => 'General',
                    ]),
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options(['draft' => 'Borrador', 'published' => 'Publicada', 'archived' => 'Archivada']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
