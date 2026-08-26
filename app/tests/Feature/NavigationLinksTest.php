<?php

use App\Models\Menu;
use App\Models\Page;
use Database\Seeders\MenuSeeder;

/**
 * Hallazgo real de Fase 9: el menú principal tenía un ítem "Eventos" apuntando
 * a `/vida-escolar/eventos`, una ruta sin controller ni página real (nunca se
 * construyó ese tipo de contenido, ver `docs/08-seo.md` §3 y
 * `docs/01-analisis-descubrimiento.md` pregunta #16, todavía pendiente). Se
 * retiró el ítem del menú; este test evita que vuelva a aparecer un enlace
 * roto en la navegación principal.
 *
 * Fase 10: el menú principal se administra desde el panel (`Menu`/`MenuItem`)
 * en vez de `config('navigation.primary')` — este test ahora valida contra
 * `Menu::renderTree('primary')`, que es lo que realmente sirve el sitio,
 * sembrado con el mismo contenido que antes vivía en el archivo fijo.
 */
it('no tiene ningún ítem de navegación principal apuntando a una URL sin ruta real', function () {
    $this->seed(MenuSeeder::class);

    $primary = Menu::renderTree('primary');

    // Los hijos con página propia (Institución / Oferta educativa) son
    // `Page` genéricas — se crea una fixture por slug para probar que la
    // ESTRUCTURA de rutas resuelve (el contenido real ya lo verifica la
    // Fase 5). Las rutas con controller dedicado no necesitan fixture.
    $dedicatedControllerRoutes = [
        '/vida-escolar/calendario', '/vida-escolar/comunicados', '/vida-escolar/galeria',
        '/noticias', '/contacto', '/admisiones',
    ];

    foreach ($primary as $item) {
        foreach ($item['children'] ?? [] as $child) {
            if (in_array($child['url'], $dedicatedControllerRoutes, true)) {
                continue;
            }

            Page::query()->firstOrCreate(
                ['slug' => ltrim($child['url'], '/')],
                ['status' => 'published', 'title' => ['es' => $child['label']]],
            );
        }
    }

    Page::query()->firstOrCreate(['slug' => 'admisiones'], ['status' => 'published', 'title' => ['es' => 'Admisiones']]);

    foreach ($primary as $item) {
        foreach ($item['children'] ?? [] as $child) {
            $this->get($child['url'])->assertOk();
        }

        if ($item['linkable'] ?? true) {
            $this->get($item['url'])->assertOk();
        }
    }
});
