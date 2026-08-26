<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use App\Models\SiteSetting;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // "Nombre"/"Clave interna" son de uso interno del backend — el
            // cliente no necesita verlos. El título del campo es directamente
            // el nombre humano de la configuración (ej. "Teléfono de
            // contacto"), no un genérico "Valor". Los booleanos ya no llegan
            // acá: se activan/desactivan en la propia tabla (SiteSettingsTable).
            Textarea::make('value')
                ->label(fn (SiteSetting $record): string => $record->label ?? 'Valor')
                ->rows(3),
        ]);
    }
}
