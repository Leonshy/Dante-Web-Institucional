@if($announcements->isEmpty())
    <x-empty-state icon="megaphone">
        No hay comunicados vigentes por el momento.
        <x-slot:cta><a class="btn btn-secondary" href="{{ url('/vida-escolar/comunicados') }}">Ver todos los comunicados</a></x-slot:cta>
    </x-empty-state>
@else
    <ul style="list-style:none;margin:0;padding:0">
        @foreach($announcements as $announcement)
            <li class="content-block">
                <span class="chip">{{ optional($announcement->published_at)->translatedFormat('d/m/Y') }}</span>
                <h3>{{ $announcement->title }}</h3>
                <div class="body-sm">{!! \Illuminate\Support\Str::limit(strip_tags($announcement->content), 220) !!}</div>
            </li>
        @endforeach
    </ul>
    <p><a class="btn btn-secondary" href="{{ url('/vida-escolar/comunicados') }}">Ver todos los comunicados</a></p>
@endif
