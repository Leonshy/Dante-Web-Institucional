<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Formulario')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'contacto' ? 'Contacto' : 'Pre-inscripción'),
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('phone')->label('Teléfono'),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('created_at')->label('Recibido')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')->label('Formulario')->options(['contacto' => 'Contacto', 'pre_inscripcion' => 'Pre-inscripción']),
                SelectFilter::make('status')->label('Estado')->options(['nuevo' => 'Nuevo', 'leido' => 'Leído', 'respondido' => 'Respondido', 'archivado' => 'Archivado']),
            ])
            ->recordActions([EditAction::make()->label('Ver / gestionar')])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()->label('Eliminar')])]);
    }
}
