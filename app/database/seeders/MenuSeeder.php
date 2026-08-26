<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

/**
 * Carga en base los mismos ítems que hoy vive en `config/navigation.php`,
 * para que activar el menú administrable desde el panel (Fase 10) no cambie
 * nada visualmente el día que `site-header`/`site-footer` empiecen a leer
 * de `Menu::renderTree()` en vez del archivo fijo.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedMenu('primary', 'Menú principal', [
            [
                'label' => 'Institución',
                'children' => [
                    ['label' => 'Quiénes somos', 'url' => '/institucion/quienes-somos'],
                    ['label' => 'Historia', 'url' => '/institucion/historia'],
                    ['label' => 'Misión, visión y valores', 'url' => '/institucion/mision-vision-valores'],
                    ['label' => 'Autoridades', 'url' => '/institucion/autoridades'],
                    ['label' => 'Società Dante Alighieri', 'url' => '/institucion/sociedad-dante-alighieri'],
                    ['label' => 'Certificación internacional', 'url' => '/institucion/certificacion-internacional'],
                    ['label' => 'Estatutos sociales', 'url' => '/institucion/estatutos-sociales'],
                    ['label' => 'Administración', 'url' => '/institucion/administracion'],
                ],
            ],
            [
                'label' => 'Oferta educativa',
                'children' => [
                    ['label' => 'Instituto de Lengua y Cultura', 'url' => '/oferta-educativa/instituto-de-lengua-y-cultura'],
                    ['label' => 'Cursos de Italiano', 'url' => '/oferta-educativa/cursos-de-italiano'],
                ],
            ],
            ['label' => 'Admisiones', 'url' => '/admisiones'],
            [
                'label' => 'Vida escolar',
                'children' => [
                    ['label' => 'Calendario académico', 'url' => '/vida-escolar/calendario'],
                    ['label' => 'Comunicados', 'url' => '/vida-escolar/comunicados'],
                    ['label' => 'Galería', 'url' => '/vida-escolar/galeria'],
                    ['label' => 'Biblioteca "Irene Borello de Amodei"', 'url' => '/vida-escolar/biblioteca'],
                    ['label' => 'Enlaces de interés', 'url' => '/vida-escolar/enlaces-de-interes'],
                ],
            ],
            ['label' => 'Noticias', 'url' => '/noticias'],
            ['label' => 'Contacto', 'url' => '/contacto'],
        ]);

        $this->seedMenu('footer_secondary', 'Pie de página — accesos secundarios', [
            ['label' => 'Documentos', 'url' => '/documentos'],
            ['label' => 'Calendario académico', 'url' => '/vida-escolar/calendario'],
            ['label' => 'Estatutos sociales', 'url' => '/institucion/estatutos-sociales'],
            ['label' => 'Convenio con Ex Alumnos', 'url' => '/institucion/convenio-ex-alumnos'],
            ['label' => 'Buscar en el sitio', 'url' => '/buscar'],
        ]);
    }

    /**
     * @param  array<int, array{label: string, url?: string, children?: array<int, array{label: string, url: string}>}>  $items
     */
    private function seedMenu(string $key, string $name, array $items): void
    {
        $menu = Menu::query()->firstOrCreate(['key' => $key], ['name' => $name]);

        if ($menu->items()->exists()) {
            return;
        }

        foreach ($items as $index => $item) {
            $parent = MenuItem::query()->create([
                'menu_id' => $menu->id,
                'label' => $item['label'],
                'url' => $item['url'] ?? null,
                'sort_order' => $index,
                'is_active' => true,
            ]);

            foreach ($item['children'] ?? [] as $childIndex => $child) {
                MenuItem::query()->create([
                    'menu_id' => $menu->id,
                    'parent_id' => $parent->id,
                    'label' => $child['label'],
                    'url' => $child['url'],
                    'sort_order' => $childIndex,
                    'is_active' => true,
                ]);
            }
        }
    }
}
