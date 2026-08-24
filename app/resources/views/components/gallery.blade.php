@props(['images', 'layout' => 'grid']) {{-- array de ['url' => string, 'alt' => string] --}}
@if($images->isEmpty())
    <x-empty-state icon="image">No hay fotos cargadas en esta galería todavía.</x-empty-state>
@elseif($layout === 'carrusel')
    <div class="carousel" x-data="{ index: 0 }">
        <div class="carousel-track" x-ref="track">
            @foreach($images as $image)
                <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" width="640" height="360" loading="lazy">
            @endforeach
        </div>
        <div class="carousel-nav">
            <button type="button" class="icon-btn" aria-label="Foto anterior"
                    @click="$refs.track.scrollBy({ left: -$refs.track.clientWidth * 0.9, behavior: 'smooth' })">‹</button>
            <button type="button" class="icon-btn" aria-label="Foto siguiente"
                    @click="$refs.track.scrollBy({ left: $refs.track.clientWidth * 0.9, behavior: 'smooth' })">›</button>
        </div>
    </div>
@else
    <div class="gallery-grid">
        @foreach($images as $image)
            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" width="300" height="300" loading="lazy">
        @endforeach
    </div>
@endif
