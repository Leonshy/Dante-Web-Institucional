<x-layouts.app title="Buscar — Colegio Dante Alighieri" description="Busque páginas, noticias, documentos y comunicados del Colegio Dante Alighieri." :indexable="false">
    <main id="contenido" class="container section">
        <h1>Resultados de búsqueda</h1>
        @livewire('search-page', ['query' => $query])
    </main>
</x-layouts.app>
