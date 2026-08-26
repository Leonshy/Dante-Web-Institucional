@php
    // JSON-LD `EducationalOrganization` global (docs/08-seo.md §3) — datos de
    // contacto reales desde SiteSetting, no inventados. Los campos vacíos se
    // omiten en vez de escribir cadenas vacías (evita advertencias del
    // validador de schema.org).
    $siteName = \App\Models\SiteSetting::get('site_name', 'Colegio Dante Alighieri');
    $phone = \App\Models\SiteSetting::get('contact_phone');
    $email = \App\Models\SiteSetting::get('contact_email');
    $address = \App\Models\SiteSetting::get('address_asuncion');
    $facebook = \App\Models\SiteSetting::get('social_facebook_url');
    $instagram = \App\Models\SiteSetting::get('social_instagram_url');

    $schema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        'name' => config('dante.seo.organization_name'),
        'legalName' => config('dante.seo.organization_legal_name'),
        'url' => url('/'),
        'logo' => asset('images/logo-dante.svg'),
        'telephone' => $phone ?: null,
        'email' => $email ?: null,
        'address' => $address ? [
            '@type' => 'PostalAddress',
            'streetAddress' => $address,
            'addressLocality' => 'Asunción',
            'addressCountry' => 'PY',
        ] : null,
        'sameAs' => array_values(array_filter([$facebook, $instagram])) ?: null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
