<x-layouts.app title="Calendario académico — Colegio Dante Alighieri" description="Calendario académico del Colegio Dante Alighieri.">
    <x-breadcrumbs :items="[['label' => 'Vida escolar', 'url' => url('/vida-escolar')], ['label' => 'Calendario académico', 'url' => null]]" />
    <main id="contenido" class="container section">
        <h1>Calendario académico</h1>

        @if($events->isEmpty())
            <x-empty-state icon="calendar">No hay eventos cargados en el calendario todavía.</x-empty-state>
        @else
            <ul class="doc-link-list">
                @foreach($events as $event)
                    <li>
                        <span>
                            <strong>{{ $event->title }}</strong><br>
                            <span class="caption">{{ $event->starts_at->translatedFormat('d \d\e F \d\e Y') }}@if(!$event->all_day) — {{ $event->starts_at->format('H:i') }}@endif</span>
                        </span>
                        @if($event->level)
                            <span class="chip">{{ $event->level }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </main>
</x-layouts.app>
