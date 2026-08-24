<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required(),
            TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('password')
                ->label('Contraseña')
                ->password()
                ->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('Dejar en blanco para no cambiarla.'),
            Select::make('roles')
                ->label('Rol en el panel')
                ->relationship('roles', 'name')
                ->options([
                    'administrador' => 'Administrador',
                    'editor_general' => 'Editor general',
                    'editor_noticias_marketing' => 'Editor de noticias y marketing',
                    'editor_academico' => 'Editor académico',
                ])
                ->required()
                ->preload(),
            Toggle::make('is_active')->label('Cuenta activa')->default(true)
                ->helperText('Desactivarla en vez de borrarla si la persona deja el colegio.'),
        ]);
    }
}
