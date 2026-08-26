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

    // 2FA por email, opt-in por usuario (ADR-003) — ya no es un flag de
    // configuración: cada usuario lo activa o no desde su perfil, y
    // `AdminPanelProvider` muestra una alerta persistente mientras no lo
    // activó. No hay nada que forzar acá a nivel de aplicación.

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

    /*
    |--------------------------------------------------------------------------
    | SEO — datos institucionales fijos (Fase 6, docs/08-seo.md)
    |--------------------------------------------------------------------------
    |
    | Lo que cambia por página/noticia vive en el modelo (seo_title,
    | seo_description, canonical_url, is_indexable — Fase 3). Esto es lo
    | que no tiene sentido repetir por registro: identidad de la
    | organización para el JSON-LD `EducationalOrganization` y la imagen
    | OG de respaldo cuando ninguna página tiene imagen propia.
    |
    */
    'seo' => [
        'organization_name' => 'Colegio Dante Alighieri',
        'organization_legal_name' => 'Società Dante Alighieri Asunción',
        // Sin imagen OG de marca (1200×630) en los insumos de Fase 2 — pendiente
        // de diseño, ver docs/08-seo.md §2. Cuando exista, va en public/images/.
        'default_og_image' => env('DANTE_DEFAULT_OG_IMAGE'),
        // `staging`/`local` bloquean indexación por defecto (robots.txt +
        // meta robots) para que nunca haga falta acordarse de "sacar" un
        // Disallow: / a mano antes de salir a producción (CLAUDE.md/docs/08 §4).
        'block_indexing' => env('DANTE_BLOCK_INDEXING', env('APP_ENV') !== 'production'),
    ],

    // Integraciones (GA4/GTM, Meta Pixel + Conversions API, Turnstile): ya no
    // viven acá ni en `.env` — son administrables desde el panel, con
    // interruptor de activo/inactivo por integración, ver
    // `App\Models\IntegrationSetting` y `App\Filament\Pages\IntegrationSettings`
    // (docs/08-seo.md §6).

];
