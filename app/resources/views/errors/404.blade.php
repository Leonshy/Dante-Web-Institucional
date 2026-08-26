<x-layouts.app title="Página no encontrada — Colegio Dante Alighieri" :indexable="false">
    <main id="contenido" tabindex="-1">
        <div class="not-found">
            <p class="code" aria-hidden="true">404</p>
            <h1>Esta página no existe</h1>
            <p class="body-lg">La dirección que buscó no está disponible. Puede haber cambiado de nombre o ya no existir.</p>
            <form class="searchbar" role="search" action="{{ route('search.index') }}" method="GET" style="margin:var(--spacing-6) auto 0">
                <label for="q">Buscar en el sitio</label>
                <input id="q" name="q" type="search" placeholder="Buscar en el sitio">
                <button type="submit" aria-label="Buscar en el sitio"><x-icon.search /></button>
            </form>
            <div class="cta-row">
                <a class="btn btn-primary" href="{{ url('/') }}">Ir al inicio</a>
                <a class="btn btn-secondary" href="{{ route('search.index') }}">Buscar en el sitio</a>
            </div>
        </div>
    </main>
</x-layouts.app>
