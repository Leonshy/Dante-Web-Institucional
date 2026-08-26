<section class="section reveal">
    <div class="container">
        <div class="section-head"><h2>Galería</h2></div>
        @if($galleries->isEmpty())
            <x-empty-state icon="image">Todavía no hay álbumes publicados.</x-empty-state>
        @else
            <div class="cards-grid">
                @foreach($galleries as $gallery)
                    @php $cover = $gallery->media->first(); @endphp
                    <x-card.section
                        :title="$gallery->title"
                        :text="optional($gallery->event_date)->translatedFormat('d \d\e F \d\e Y')"
                        :url="route('galleries.index')"
                        :image="$cover?->conversionUrl('medium') ?? $cover?->url()"
                        :image-srcset="$cover?->srcset()"
                        cta-label="Ver galería"
                    />
                @endforeach
            </div>
            <p style="margin-top:var(--spacing-6)"><a class="btn btn-secondary" href="{{ route('galleries.index') }}">Ver toda la galería</a></p>
        @endif
    </div>
</section>
