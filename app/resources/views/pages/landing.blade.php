<x-layouts.app :title="$page->effectiveSeoTitle().' — Colegio Dante Alighieri'" :description="$page->getTranslation('seo_description', app()->getLocale())" :indexable="$page->is_indexable">
    <x-breadcrumbs :items="$breadcrumbs" />
    <main id="contenido">
        {{-- Igual que pages/show.blade.php: si el primer bloque es un hero, ese
             bloque ya trae su propio <h1> — si no, se agrega uno con el título
             de la página para no dejar la plantilla sin h1. --}}
        @if(($blocks[0]['type'] ?? null) !== 'hero')
            <div class="container section" style="padding-bottom:0">
                <h1>{{ $page->title }}</h1>
            </div>
        @endif
        <x-page-blocks :blocks="$blocks" />
    </main>
</x-layouts.app>
