<x-layouts.app title="Error del servidor — Colegio Dante Alighieri" :indexable="false">
    <main id="contenido" tabindex="-1">
        <div class="not-found">
            <p class="code" aria-hidden="true">500</p>
            <h1>Algo salió mal</h1>
            <p class="body-lg">Tuvimos un problema técnico al procesar su solicitud. Ya estamos al tanto — intente de nuevo en unos minutos.</p>
            <div class="cta-row">
                <a class="btn btn-primary" href="{{ url('/') }}">Ir al inicio</a>
                <a class="btn btn-secondary" href="{{ url('/contacto') }}">Contactar a la secretaría</a>
            </div>
        </div>
    </main>
</x-layouts.app>
