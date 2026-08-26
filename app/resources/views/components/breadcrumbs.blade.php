@props(['items' => []])
{{-- $items: array de ['label' => string, 'url' => string|null] — el último sin url es la página actual --}}
@if(count($items) > 0)
@php
    // JSON-LD BreadcrumbList (docs/08-seo.md §3) — mismos elementos que el
    // <nav> visible de abajo, "Inicio" incluido como primer ítem siempre.
    $breadcrumbList = collect([['label' => 'Inicio', 'url' => url('/')]])
        ->concat(collect($items)->map(fn ($item) => [
            'label' => $item['label'],
            'url' => $item['url'] ?? null,
        ]))
        ->values()
        ->map(fn ($item, $index) => array_filter([
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?: null,
        ]))
        ->all();
@endphp
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $breadcrumbList,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<nav class="breadcrumbs container" aria-label="Ruta de navegación">
    <ol>
        <li><a href="{{ url('/') }}">Inicio</a></li>
        @foreach($items as $item)
            @if(!empty($item['url']) && !$loop->last)
                <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
            @else
                <li aria-current="page">{{ $item['label'] }}</li>
            @endif
        @endforeach
    </ol>
</nav>
@endif
