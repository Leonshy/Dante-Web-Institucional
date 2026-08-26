<?php

namespace App\Filament\Tables;

use App\Models\Media;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Grid de la biblioteca de medios, usado dentro de la pestaña "Biblioteca"
 * del campo `App\Filament\Forms\Components\MediaPicker` (ver esa clase para
 * el diseño completo: disparador = miniatura clickeable, modal con pestañas
 * Biblioteca/Subir, sin opción "por URL" — igual que el picker real de IGP).
 */
class MediaLibraryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Media::query())
            ->columns([
                ImageColumn::make('path')
                    ->label('')
                    ->getStateUsing(fn ($record) => $record->type === 'image' ? ($record->conversionUrl('small') ?? $record->url()) : null)
                    ->imageSize(140)
                    ->extraImgAttributes(['class' => 'aspect-square object-cover']),
                TextColumn::make('name')->label('')->limit(30)->searchable(),
            ])
            ->contentGrid(['default' => 2, 'md' => 3, 'xl' => 4])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options(['image' => 'Imágenes', 'document' => 'Documentos', 'video' => 'Videos']),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
