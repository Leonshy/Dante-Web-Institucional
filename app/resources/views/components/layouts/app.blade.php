@props([
    'title' => null,
    'description' => null,
    'indexable' => true,
    'canonical' => null,
    'ogType' => 'website',
    'ogImage' => null,
    // Fase 7 (rendimiento, docs/09-rendimiento.md §5): el runtime de Livewire
    // (~126 KB comprimidos, medido en la línea base) solo lo usa de verdad
    // el buscador interno (`App\Livewire\SearchPage`) — home, páginas
    // institucionales y noticias no tienen ni un solo `wire:` ni
    // `@livewire()`. Cargarlo en todas las plantillas competía por ancho de
    // banda con la imagen del LCP bajo 4G simulada sin ningún beneficio en
    // esas páginas. Por defecto no se incluye; la vista que sí lo necesita
    // pasa `:livewire="true"`.
    'livewire' => false,
])
@php
    $resolvedTitle = $title ?? 'Colegio Dante Alighieri';
    $resolvedDescription = $description ?? 'Colegio bilingüe español-italiano en Asunción, afiliado a la Società Dante Alighieri.';
    $resolvedCanonical = $canonical ?: url()->current();
    // "staging"/"local" bloquean indexación siempre, sin importar el toggle de
    // la página — así el robots.txt/meta robots nunca dependen de que alguien
    // se acuerde de "sacar" el bloqueo a mano antes de salir a producción
    // (CLAUDE.md, docs/08-seo.md §4).
    $blockedByEnvironment = (bool) config('dante.seo.block_indexing');
    $resolvedIndexable = ($indexable ?? true) && ! $blockedByEnvironment;
    $resolvedOgImage = $ogImage ?: config('dante.seo.default_og_image');
    $siteName = \App\Models\SiteSetting::get('site_name', 'Colegio Dante Alighieri');
    // Los interruptores de activo/inactivo de cada integración viven acá:
    // si están apagados desde el panel, el atributo sale vacío y
    // resources/js/consent.js no carga el script, aunque el ID siga guardado.
    $integrations = \App\Models\IntegrationSetting::current();
@endphp
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-gtm-id="{{ $integrations->googleTagManagerActive() ? $integrations->google_tag_manager_id : '' }}"
    data-meta-pixel-id="{{ $integrations->metaPixelActive() ? $integrations->meta_pixel_id : '' }}"
>
<head>
    {{-- Marca que JS está disponible antes de que el CSS decida ocultar contenido para
         animar su entrada — sin esta clase, `.reveal` nunca queda oculto, así el sitio
         nunca depende de JS para mostrar contenido real (docs/06-frontend.md §8 #4). --}}
    <script>document.documentElement.classList.add('js')</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $resolvedTitle }}</title>
    <meta name="description" content="{{ $resolvedDescription }}">
    @if(!$resolvedIndexable)
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ $resolvedCanonical }}">

    {{-- Open Graph / Twitter Cards (docs/08-seo.md §2) --}}
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'it' ? 'it_IT' : 'es_PY' }}">
    @if($resolvedOgImage)
        <meta property="og:image" content="{{ $resolvedOgImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @else
        <meta name="twitter:card" content="summary">
    @endif

    <x-schema.organization />

    {{-- Fuentes autoalojadas vía Vite (`@fonts`, ver docs/09-rendimiento.md §4) — nunca
         se pide nada a un CDN externo de fuentes, así que no hace falta ningún
         `preconnect` a terceros acá (uno "por las dudas" es una conexión TLS
         de arranque desperdiciada, no gratis). --}}
    @fonts
    @vite(['resources/css/app.css'])
    @if($livewire)
        @livewireStyles
    @endif
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido principal</a>

{{-- GTM/GA4 y Meta Pixel solo se inyectan tras consentimiento real —
     resources/js/consent.js crea las etiquetas <script> por código, acá no
     hay ningún <script src="...gtm..."> incondicional (docs/08-seo.md §6). --}}
<x-cookie-consent />

<x-whatsapp-float />

<x-site-header />

{{ $slot }}

<x-site-footer />

@if($livewire)
    @livewireScripts
@endif
@vite(['resources/js/app.js'])
@if(session('meta_event_id'))
    {{-- Deja el event_id disponible para que consent.js dispare el Lead del
         Pixel con el MISMO id que ya mandó el servidor por Conversions API
         (deduplicación, docs/08-seo.md §6) — solo si hay consentimiento de
         marketing, `loadMetaPixel()` ya no corre si no lo hay. --}}
    <script>window.danteQueuedLeadEventId = @json(session('meta_event_id'));</script>
@endif
@stack('scripts')
</body>
</html>
