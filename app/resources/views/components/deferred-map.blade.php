@props(['url' => null, 'title' => 'Ubicación del Colegio Dante Alighieri'])

@php
    $embedUrl = $url ?? \App\Models\SiteSetting::get('google_maps_embed_url');
@endphp
@if($embedUrl)
    {{-- Carga diferida "clic para activar" — el iframe de Google Maps no se
         inserta en el DOM hasta que la persona lo pide, no penaliza el LCP ni
         suma peticiones de terceros a una página que no las necesitó
         (docs/09-rendimiento.md §5, "terceros cargados bajo demanda"). --}}
    <div
        x-data="{ loaded: false }"
        class="map-embed"
    >
        <template x-if="!loaded">
            <button type="button" x-on:click="loaded = true" class="btn-link" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center">
                Mostrar mapa
            </button>
        </template>
        <template x-if="loaded">
            <iframe
                :src="'{{ $embedUrl }}'"
                width="100%"
                height="100%"
                style="border:0"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="{{ $title }}"
            ></iframe>
        </template>
    </div>
@else
    <div class="map-embed" role="img" aria-label="{{ $title }} en el mapa — pendiente de dirección real">
        [Mapa pendiente de configurar desde el panel]
    </div>
@endif
