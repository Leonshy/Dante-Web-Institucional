<?php

/**
 * Estructura fija del menú principal y accesos secundarios del pie de página,
 * según docs/02-ux-arquitectura-informacion.md §4-5. No es contenido editorial
 * (eso vive en Page/Post/etc. vía el panel) — es la arquitectura de información
 * aprobada del sitio, por eso se declara acá y no en base de datos. Si el cliente
 * pide un menú administrable desde el panel, ya existe el modelo Menu/MenuItem
 * (Fase 3) para reemplazar este archivo sin tocar las vistas.
 */
return [
    'primary' => [
        [
            'label' => 'Institución',
            'url' => '/institucion',
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
            'children' => [
                ['label' => 'Calendario académico', 'url' => '/vida-escolar/calendario'],
                ['label' => 'Comunicados', 'url' => '/vida-escolar/comunicados'],
                ['label' => 'Eventos', 'url' => '/vida-escolar/eventos'],
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
