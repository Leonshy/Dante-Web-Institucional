<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DANTE_ADMIN_PASSWORD', Str::password(16));

        $user = User::query()->updateOrCreate(
            ['email' => env('DANTE_ADMIN_EMAIL', 'admin@dante.edu.py')],
            [
                'name' => 'Administrador Dante',
                'password' => $password,
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles(['administrador']);

        if (! env('DANTE_ADMIN_PASSWORD')) {
            $this->command?->warn("Usuario admin creado: {$user->email} / contraseña generada: {$password} — cambiarla en el primer ingreso.");
        }
    }
}
