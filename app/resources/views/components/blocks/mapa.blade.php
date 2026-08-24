@props(['data'])
<div class="section">
    <div class="container">
        @if(!empty($data['latitude']) && !empty($data['longitude']))
            <iframe
                title="Ubicación — {{ $data['address'] ?? 'Colegio Dante Alighieri' }}"
                width="100%" height="360" loading="lazy" style="border:0;border-radius:var(--radius-md)"
                src="https://www.google.com/maps?q={{ $data['latitude'] }},{{ $data['longitude'] }}&output=embed">
            </iframe>
        @else
            <div class="map-embed" role="img" aria-label="Ubicación del Colegio Dante Alighieri — mapa pendiente de coordenadas">
                {{ $data['address'] ?? '[Mapa embebido]' }}
            </div>
        @endif
    </div>
</div>
