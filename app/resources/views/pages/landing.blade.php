<x-layouts.app
    :title="$page->effectiveSeoTitle().' — Colegio Dante Alighieri'"
    :description="$page->getTranslation('seo_description', app()->getLocale())"
    :indexable="$page->is_indexable"
    :canonical="$page->canonical_url ?: null"
    :og-image="$page->seoImage?->conversionUrl('w1200') ?? $page->seoImage?->url() ?? $page->coverMedia?->conversionUrl('w1200') ?? $page->coverMedia?->url()"
>
    <x-breadcrumbs :items="$breadcrumbs" />
    <main id="contenido" tabindex="-1">
        {{-- Igual que pages/show.blade.php: si el primer bloque es un hero, ese
             bloque ya trae su propio <h1> — si no, se agrega uno con el título
             de la página para no dejar la plantilla sin h1. --}}
        @if(($blocks[0]['type'] ?? null) !== 'hero')
            <div class="container page-title-block">
                <h1>{{ $page->title }}</h1>
            </div>
        @endif
        <x-page-blocks :blocks="$blocks" />
    </main>
</x-layouts.app>
