<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ruta del panel de administración
    |--------------------------------------------------------------------------
    |
    | Nunca "admin". Ruta no adivinable definida en .env, tal como pide
    | docs/05-backend-modelo-datos.md §3 y la Fase 8 (seguridad).
    |
    */
    'admin_path' => env('DANTE_ADMIN_PATH', 'panel'),

    /*
    |--------------------------------------------------------------------------
    | 2FA obligatorio en el panel
    |--------------------------------------------------------------------------
    |
    | Definido como requisito no negociable en la Fase 3 (docs/05-backend-modelo-datos.md
    | §4, antecedente de seguridad en docs/01-analisis-descubrimiento.md §C.6). Default
    | `true` — se apaga solo con esta variable de entorno, nunca editando código, así
    | queda un rastro explícito de que alguien lo desactivó a propósito y dónde.
    | **Nunca debe quedar en `false` en producción.**
    |
    */
    'require_2fa' => env('DANTE_REQUIRE_2FA', true),

    /*
    |--------------------------------------------------------------------------
    | Idiomas soportados
    |--------------------------------------------------------------------------
    */
    'locales' => ['es', 'it'],

    'locale_labels' => [
        'es' => 'Español',
        'it' => 'Italiano',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanitización de HTML (editor enriquecido)
    |--------------------------------------------------------------------------
    | Lista blanca — ver docs/05-backend-modelo-datos.md §5.
    */
    'html_sanitizer' => [
        'allowed_tags' => [
            'p', 'br', 'strong', 'em', 'u', 's', 'h2', 'h3', 'h4',
            'ul', 'ol', 'li', 'a', 'blockquote', 'table', 'thead', 'tbody',
            'tr', 'th', 'td', 'img', 'figure', 'figcaption', 'hr',
        ],
        'allowed_attributes' => [
            'href', 'title', 'alt', 'src', 'class', 'colspan', 'rowspan',
        ],
        'allowed_classes' => [
            'text-center', 'text-left', 'text-right',
        ],
        'allowed_protocols' => ['http', 'https', 'mailto', 'tel'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Medios
    |--------------------------------------------------------------------------
    */
    'media' => [
        'max_upload_kb' => 8192,
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif',
            'image/svg+xml',
            'application/pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'video/mp4',
        ],
        'image_conversions' => ['webp'],
        'responsive_widths' => [400, 800, 1200, 1920],
    ],

];
