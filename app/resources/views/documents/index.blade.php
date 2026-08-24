<x-layouts.app title="Documentos — Colegio Dante Alighieri" description="Estatutos, circulares y formularios del Colegio Dante Alighieri, para descargar.">
    <x-breadcrumbs :items="[['label' => 'Documentos', 'url' => null]]" />
    <main id="contenido" class="container section">
        <h1>Documentos</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin-top:var(--spacing-2)">Estatutos, circulares y formularios del colegio, para descargar.</p>

        <div class="filters">
            @if($categories->isNotEmpty())
                <div>
                    <p style="font-weight:600;font-size:13px;color:var(--color-neutral-700);margin-bottom:var(--spacing-1)">Categoría</p>
                    <x-filter-tabs :options="$categories" :active="$active" param-name="categoria" />
                </div>
            @endif
            <form method="GET" action="{{ route('documents.index') }}">
                @if(request('categoria'))
                    <input type="hidden" name="categoria" value="{{ request('categoria') }}">
                @endif
                <x-form.select name="sede" label="Sede" :selected="request('sede')" :options="['' => 'Todas las sedes', 'asuncion' => 'Asunción', 'fernando-de-la-mora' => 'Fernando de la Mora']"
                    onchange="this.form.submit()" />
            </form>
        </div>

        @if($documents->isEmpty())
            <x-empty-state icon="file-text">No hay documentos publicados en esta categoría todavía.</x-empty-state>
        @else
            <table class="doc-table">
                <caption>Documentos disponibles</caption>
                <thead>
                    <tr><th>Documento</th><th>Categoría</th><th>Sede</th><th>Fecha</th><th>Acción</th></tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td>{{ $document->title }}</td>
                            <td>{{ $document->category?->name }}</td>
                            <td>{{ $document->site === 'ambas' ? 'Ambas' : ucfirst((string) $document->site) }}</td>
                            <td>{{ optional($document->published_at)->format('d/m/Y') }}</td>
                            <td><a href="{{ $document->file?->url() }}" aria-label="Descargar {{ $document->title }} en PDF" download>Descargar (PDF)</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="doc-cards">
                @foreach($documents as $document)
                    <div class="doc-card">
                        <h3>{{ $document->title }}</h3>
                        <p class="meta">{{ $document->category?->name }} · {{ $document->site === 'ambas' ? 'Ambas sedes' : ucfirst((string) $document->site) }} · {{ optional($document->published_at)->format('d/m/Y') }}</p>
                        <a href="{{ $document->file?->url() }}" aria-label="Descargar {{ $document->title }} en PDF" download>Descargar (PDF)</a>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</x-layouts.app>
