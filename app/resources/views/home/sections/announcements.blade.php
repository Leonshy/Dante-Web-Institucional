<section class="section reveal">
    <div class="container">
        @if($announcements->isEmpty())
            <x-empty-state icon="megaphone">
                No hay comunicados vigentes por el momento.
                <x-slot:cta><a class="btn btn-secondary" href="{{ url('/vida-escolar/comunicados') }}">Ver todos los comunicados</a></x-slot:cta>
            </x-empty-state>
        @else
            <div class="section-head"><h2>Comunicados</h2></div>
            <ul style="list-style:none;margin:0;padding:0">
                @foreach($announcements as $announcement)
                    <li class="content-block"><h3>{{ $announcement->title }}</h3></li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
