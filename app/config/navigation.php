<?php

/**
 * Ya NO es la fuente en vivo del menú del sitio (Fase 10): `site-header` y
 * `site-footer` leen de `Menu::renderTree()`, administrable desde el panel
 * ("Menús"). Este archivo queda solo como referencia de la arquitectura de
 * información original (docs/02-ux-arquitectura-informacion.md §4-5) y como
 * contenido de partida de `MenuSeeder`, que carga estos mismos ítems en base
 * la primera vez que corre.
 */
return [
    'primary' => [
        [
            'label' => 'Institución',
            'url' => '/institucion',
            'linkable' => false,
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
            'url' => '/oferta-educativa',
            'linkable' => false,
            'children' => [
                ['label' => 'Instituto de Lengua y Cultura', 'url' => '/oferta-educativa/instituto-de-lengua-y-cultura'],
                ['label' => 'Cursos de Italiano', 'url' => '/oferta-educativa/cursos-de-italiano'],
            ],
        ],
        [
            'label' => 'Admisiones',
            'url' => '/admisiones',
            'children' => [],
        ],
        [
            'label' => 'Vida escolar',
            'url' => '/vida-escolar',
            'linkable' => false,
            'children' => [
                ['label' => 'Calendario académico', 'url' => '/vida-escolar/calendario'],
                ['label' => 'Comunicados', 'url' => '/vida-escolar/comunicados'],
                ['label' => 'Galería', 'url' => '/vida-escolar/galeria'],
                ['label' => 'Biblioteca "Irene Borello de Amodei"', 'url' => '/vida-escolar/biblioteca'],
                ['label' => 'Enlaces de interés', 'url' => '/vida-escolar/enlaces-de-interes'],
            ],
        ],
        [
            'label' => 'Noticias',
            'url' => '/noticias',
            'children' => [],
        ],
        [
            'label' => 'Contacto',
            'url' => '/contacto',
            'children' => [],
        ],
    ],

    'footer_secondary' => [
        ['label' => 'Documentos', 'url' => '/documentos'],
        ['label' => 'Calendario académico', 'url' => '/vida-escolar/calendario'],
        ['label' => 'Estatutos sociales', 'url' => '/institucion/estatutos-sociales'],
        ['label' => 'Convenio con Ex Alumnos', 'url' => '/institucion/convenio-ex-alumnos'],
        ['label' => 'Buscar en el sitio', 'url' => '/buscar'],
    ],
];
