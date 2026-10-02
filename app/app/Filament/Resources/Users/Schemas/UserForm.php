<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    private const ROLE_LABELS = [
        'administrador' => 'Administrador',
        'editor_general' => 'Editor general',
        'editor_noticias_marketing' => 'Editor de noticias y marketing',
        'editor_academico' => 'Editor académico',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nombre')->required(),
            TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true)
                ->disabled(fn (?User $record) => $record?->isProtected()),
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
                ->multiple()
                ->options(fn () => Role::query()->get()->mapWithKeys(
                    fn (Role $role) => [$role->id => self::ROLE_LABELS[$role->name] ?? $role->name]
                ))
                ->required()
                ->preload()
                ->disabled(fn (?User $record) => $record?->isProtected()),
            Toggle::make('is_active')->label('Cuenta activa')->default(true)
                ->disabled(fn (?User $record) => $record?->isProtected())
                ->helperText('Desactivarla en vez de borrarla si la persona deja el colegio.'),
        ]);
    }
}
