<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sede')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre de la sede')
                        ->helperText('Ej: "Asunción", "Fernando de la Mora", "Instituto de Lengua Italiana".')
                        ->required()
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label('Mostrar en el sitio (contacto y pie de página)')
                        ->default(true),
                ]),

            Section::make('Contacto')
                ->schema([
                    TextInput::make('academic_email')
                        ->label('Correo académico')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('administrative_email')
                        ->label('Correo administrativo')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->helperText('Podés poner más de un número, ej: "+595 (21) 491 622 · +595 984 464500".')
                        ->maxLength(255),
                    TextInput::make('whatsapp')
                        ->label('WhatsApp')
                        ->helperText('Formato internacional, ej: 595984464500.')
                        ->maxLength(255),
                ])->columns(2),

            Section::make('Ubicación y horario')
                ->schema([
                    Textarea::make('address')
                        ->label('Dirección')
                        ->rows(2),
                    Textarea::make('schedule')
                        ->label('Horario de atención')
                        ->rows(2),
                    Textarea::make('maps_embed_url')
                        ->label('Google Maps — URL de embebido')
                        ->helperText('Compartir → Insertar un mapa → copiar solo la URL del src.')
                        ->rows(2),
                ]),

            Section::make('Orden')
                ->description('Determina en qué posición aparece esta sede en la página de contacto y en el pie de página.')
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Orden')
                        ->numeric()
                        ->default(0)
                        ->helperText('Los números más bajos aparecen primero.'),
                ]),
        ]);
    }
}
