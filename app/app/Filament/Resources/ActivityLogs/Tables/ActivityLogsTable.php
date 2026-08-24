<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('causer.name')->label('Usuario')->default('Sistema'),
                TextColumn::make('event')->label('Acción')->badge(),
                TextColumn::make('subject_type')->label('Sobre')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
                TextColumn::make('description')->label('Descripción')->limit(60),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->label('Acción')
                    ->options(['created' => 'Creado', 'updated' => 'Editado', 'deleted' => 'Eliminado']),
            ])
            ->recordActions([
                ViewAction::make()->schema(fn (Schema $schema) => $schema->components([
                    TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                    TextEntry::make('causer.name')->label('Usuario')->default('Sistema'),
                    TextEntry::make('event')->label('Acción'),
                    TextEntry::make('subject_type')->label('Modelo')
                        ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
                    KeyValueEntry::make('changes')->label('Cambios')
                        ->state(fn ($record) => $record->changes()->toArray()),
                ])),
            ])
            ->toolbarActions([]);
    }
}
