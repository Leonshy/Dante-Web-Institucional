<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Carga las sedes con los datos reales que hoy están escritos a mano en
 * `contact/show.blade.php` — dirección y horario de atención quedan vacíos
 * porque esos dos datos nunca se migraron del WordPress original (ver el
 * placeholder "[COMPLETAR CON DATO REAL DE MIGRACIÓN]" que reemplaza esta
 * pantalla): no se inventan, quedan para que el cliente los cargue desde el
 * panel (CLAUDE.md §6.4).
 */
class LocationSeeder extends Seeder
{
    public function run(): void
    {
        if (Location::query()->exists()) {
            return;
        }

        Location::query()->create([
            'name' => 'Asunción',
            'academic_email' => 'colegioasu@dante.edu.py',
            'administrative_email' => 'administracionasu@dante.edu.py',
            'phone' => '+595 (21) 491 622 · +595 984 464500',
            'whatsapp' => '595984464500',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Location::query()->create([
            'name' => 'Fernando de la Mora',
            'academic_email' => 'administracionfdo@dante.edu.py',
            'administrative_email' => 'secret.dante.fdo@hotmail.com',
            'phone' => '+595 (21) 500 370 · +595 984 464501',
            'whatsapp' => '595984464501',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Location::query()->create([
            'name' => 'Instituto de Lengua Italiana',
            'academic_email' => 'institutoda@dante.edu.py',
            'phone' => '+595 974 812022',
            'whatsapp' => '595974812022',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
