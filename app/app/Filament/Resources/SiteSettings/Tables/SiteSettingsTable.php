<?php

namespace App\Filament\Resources\SiteSettings\Tables;

use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class SiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Nombre')->searchable(),
                TextColumn::make('group')->label('Sección')->badge(),
                TextColumn::make('value')
                    ->label('Valor actual')
                    ->limit(40)
                    // Hallazgo real (Fase 10): `Column::visible()` no recibe el
                    // registro de la fila (siempre llega `null` — confirmado
                    // con logging), a diferencia de las Actions, que sí lo
                    // reciben. Por eso acá no hay dos columnas condicionales:
                    // el valor se formatea distinto según el tipo, y el
                    // interruptor real vive en la Action de abajo.
                    ->formatStateUsing(fn (SiteSetting $record, ?string $state): string => $record->type === 'boolean'
                        ? ((bool) $state ? 'Activo' : 'Inactivo')
                        : ($state ?? ''))
                    ->badge(fn (SiteSetting $record): bool => $record->type === 'boolean')
                    ->color(fn (SiteSetting $record): ?string => $record->type === 'boolean'
                        ? ((bool) $record->value ? 'success' : 'gray')
                        : null),
            ])
            ->defaultSort('group')
            ->paginated(false)
            ->filters([
                SelectFilter::make('group')->label('Sección')->options([
                    'general' => 'General',
                    'contacto' => 'Contacto',
                    'idioma' => 'Idioma',
                    'formularios' => 'Formularios',
                ]),
            ])
            ->recordActions([
                // El interruptor sí/no vive acá mismo, sin abrir Editar —
                // esa pantalla no tenía nada más que ofrecer para un booleano.
                Action::make('toggle')
                    ->label(fn (SiteSetting $record): string => (bool) $record->value ? 'Desactivar' : 'Activar')
                    ->icon(fn (SiteSetting $record): string => (bool) $record->value ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (SiteSetting $record): string => (bool) $record->value ? 'gray' : 'success')
                    ->visible(fn (SiteSetting $record): bool => $record->type === 'boolean')
                    ->action(function (SiteSetting $record): void {
                        $record->update(['value' => (bool) $record->value ? '0' : '1']);
                        Cache::forget(SiteSetting::CACHE_PREFIX.$record->key);
                    }),
                // Un booleano ya se activa/desactiva con la acción de arriba —
                // no hay nada más que editar ahí, así que el botón desaparece.
                EditAction::make()->label('Editar')->visible(fn (SiteSetting $record): bool => $record->type !== 'boolean'),
            ]);
    }
}
