<x-layouts.app :title="$page->effectiveSeoTitle().' — Colegio Dante Alighieri'" :description="$page->getTranslation('seo_description', app()->getLocale())" :indexable="$page->is_indexable">
    <x-breadcrumbs :items="$breadcrumbs" />
    <main id="contenido">
        <x-page-blocks :blocks="$blocks" />
    </main>
</x-layouts.app>
