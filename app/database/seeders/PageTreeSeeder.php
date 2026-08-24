<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Estructura base de páginas fijas, según el mapa del sitio de
 * docs/02-ux-arquitectura-informacion.md §4. Contenido de relleno mínimo
 * (título real, cuerpo pendiente de carga editorial en Fase 5) — nunca
 * "Lorem ipsum" (CLAUDE.md §6).
 */
class PageTreeSeeder extends Seeder
{
    public function run(): void
    {
        $home = $this->page('inicio', 'Inicio', 'general', null, 0);

        $institucion = $this->page('institucion', 'Institución', 'institucion', null, 1);
        $this->page('institucion/quienes-somos', 'Quiénes somos', 'institucion', $institucion->id, 1);
        $this->page('institucion/historia', 'Historia', 'institucion', $institucion->id, 2);
        $this->page('institucion/mision-vision-valores', 'Misión, visión y valores', 'institucion', $institucion->id, 3);
        $this->page('institucion/autoridades', 'Autoridades', 'institucion', $institucion->id, 4);
        $this->page('institucion/sociedad-dante-alighieri', 'Acerca de la Sociedad Dante Alighieri', 'institucion', $institucion->id, 5);
        $this->page('institucion/certificacion-internacional', 'Certificación internacional', 'institucion', $institucion->id, 6);
        $this->page('institucion/estatutos-sociales', 'Estatutos sociales', 'institucion', $institucion->id, 7);
        $this->page('institucion/administracion', 'Administración', 'institucion', $institucion->id, 8);

        $oferta = $this->page('oferta-educativa', 'Oferta educativa', 'oferta-educativa', null, 2);
        $this->page('oferta-educativa/instituto-de-lengua-y-cultura', 'Instituto de Lengua y Cultura', 'oferta-educativa', $oferta->id, 1);
        $this->page('oferta-educativa/cursos-de-italiano', 'Cursos de Italiano', 'oferta-educativa', $oferta->id, 2);

        $admisiones = $this->page('admisiones', 'Admisiones', 'admisiones', null, 3);
        $this->page('admisiones/pre-inscripcion', 'Pre-inscripción', 'admisiones', $admisiones->id, 1);

        $vidaEscolar = $this->page('vida-escolar', 'Vida escolar', 'vida-escolar', null, 4);
        $this->page('vida-escolar/eventos', 'Eventos', 'vida-escolar', $vidaEscolar->id, 3);
        $this->page('vida-escolar/galeria', 'Galería', 'vida-escolar', $vidaEscolar->id, 4);
        $this->page('vida-escolar/biblioteca', 'Biblioteca "Irene Borello de Amodei"', 'vida-escolar', $vidaEscolar->id, 5);
        $this->page('vida-escolar/enlaces-de-interes', 'Enlaces de interés', 'vida-escolar', $vidaEscolar->id, 6);

        $this->page('documentos', 'Documentos', 'general', null, 5);
        $this->page('contacto', 'Contacto', 'general', null, 6);
    }

    private function page(string $slug, string $title, string $section, ?int $parentId, int $order): Page
    {
        return Page::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => ['es' => $title],
                'site_section' => $section,
                'parent_id' => $parentId,
                'sort_order' => $order,
                'status' => 'draft',
            ],
        );
    }
}
