<?php

namespace App\Filament\Resources\CalendarEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CalendarEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->formatStateUsing(fn ($record) => $record->getTranslation('title', 'es'))->searchable(),
                TextColumn::make('level')->label('Nivel')->badge(),
                TextColumn::make('starts_at')->label('Comienza')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('status')->label('Estado')->badge(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('Estado')->options(['draft' => 'Borrador', 'published' => 'Publicado']),
                SelectFilter::make('level')->label('Nivel')->options([
                    'inicial' => 'Inicial',
                    'primaria' => 'Primaria',
                    'secundaria' => 'Secundaria',
                    'instituto-de-idiomas' => 'Instituto de Idiomas',
                    'todo-el-colegio' => 'Todo el colegio',
                ]),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
