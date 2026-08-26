<x-layouts.app title="Galería — Colegio Dante Alighieri" description="Fotos de actividades y eventos del Colegio Dante Alighieri.">
    <x-breadcrumbs :items="[['label' => 'Vida escolar', 'url' => \App\Models\Page::publishedUrl('vida-escolar')], ['label' => 'Galería', 'url' => null]]" />
    <main id="contenido" tabindex="-1" class="container section">
        <h1>Galería</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin-top:var(--spacing-2)">Fotos de las actividades, eventos y celebraciones del colegio a lo largo del año.</p>

        @if($galleries->isEmpty())
            <x-empty-state icon="image">No hay álbumes publicados todavía.</x-empty-state>
        @else
            @foreach($galleries as $gallery)
                <section class="content-block">
                    <h2>{{ $gallery->title }}</h2>
                    @if($gallery->event_date)
                        <p class="caption">{{ $gallery->event_date->translatedFormat('d \d\e F \d\e Y') }}</p>
                    @endif
                    <x-gallery :images="$gallery->media->map(fn ($m) => ['url' => $m->conversionUrl('medium') ?? $m->url(), 'alt' => $m->alt ?? ''])" layout="grid" />
                </section>
            @endforeach
        @endif
    </main>
</x-layouts.app>
