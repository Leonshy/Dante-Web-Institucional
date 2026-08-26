<x-layouts.app title="Comunicados — Colegio Dante Alighieri" description="Comunicados y circulares vigentes del Colegio Dante Alighieri.">
    <x-breadcrumbs :items="[['label' => 'Vida escolar', 'url' => \App\Models\Page::publishedUrl('vida-escolar')], ['label' => 'Comunicados', 'url' => null]]" />
    <main id="contenido" tabindex="-1" class="container section">
        <h1>Comunicados</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin:var(--spacing-2) 0 var(--spacing-6)">Circulares y avisos vigentes de la administración.</p>

        @if($announcements->isEmpty())
            <x-empty-state icon="megaphone">No hay comunicados publicados por el momento.</x-empty-state>
        @else
            <ul style="list-style:none;margin:0;padding:0">
                @foreach($announcements as $announcement)
                    <li class="content-block">
                        @if($announcement->is_pinned)
                            <span class="chip">Fijado</span>
                        @endif
                        <span class="caption">{{ optional($announcement->published_at)->translatedFormat('d \d\e F \d\e Y') }}</span>
                        <h3>{{ $announcement->title }}</h3>
                        <div class="body">{!! $announcement->content !!}</div>
                    </li>
                @endforeach
            </ul>
            {{ $announcements->links('pagination.dante') }}
        @endif
    </main>
</x-layouts.app>
