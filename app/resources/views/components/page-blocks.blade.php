@props(['blocks']) {{-- resultado de Page::blocksForLocale(): array de ['type' => string, 'data' => array] --}}
@foreach($blocks as $block)
    @switch($block['type'])
        @case('hero')
            <x-blocks.hero :data="$block['data']" />
            @break
        @case('texto')
            <x-blocks.texto :data="$block['data']" />
            @break
        @case('imagen_texto')
            <x-blocks.imagen-texto :data="$block['data']" />
            @break
        @case('tarjetas')
            <x-blocks.tarjetas :data="$block['data']" />
            @break
        @case('cta')
            <x-blocks.cta :data="$block['data']" />
            @break
        @case('cifras')
            <x-blocks.cifras :data="$block['data']" />
            @break
        @case('listado_noticias')
            <div class="section"><div class="container"><x-blocks.news-list :count="(int) ($block['data']['count'] ?? 3)" /></div></div>
            @break
        @case('galeria')
            <div class="section"><div class="container">
                <x-blocks.gallery-block :gallery-id="$block['data']['gallery_id'] ?? null" :layout="$block['data']['layout'] ?? 'grid'" />
            </div></div>
            @break
        @case('faq')
            <x-blocks.faq :data="$block['data']" />
            @break
        @case('video')
            <x-blocks.video :data="$block['data']" />
            @break
        @case('testimonios')
            <x-blocks.testimonios :data="$block['data']" />
            @break
        @case('mapa')
            <x-blocks.mapa :data="$block['data']" />
            @break
        @case('formulario')
            <x-blocks.formulario :data="$block['data']" />
            @break
        @case('listado_comunicados')
            <div class="section"><div class="container"><x-blocks.announcements-list :count="(int) ($block['data']['count'] ?? 5)" /></div></div>
            @break
        @case('documentos')
            <div class="section"><div class="container"><x-blocks.documents-list :category-id="$block['data']['category_id'] ?? null" /></div></div>
            @break
        @case('selector_sede')
            <x-blocks.selector-sede :data="$block['data']" />
            @break
    @endswitch
@endforeach
