@props(['url' => null, 'title', 'text' => null, 'image' => null, 'imageSrcset' => null, 'imageAlt' => '', 'ctaLabel' => 'Ver más'])
@php $tag = $url ? 'a' : 'div'; @endphp
<{{ $tag }} class="card" @if($url) href="{{ $url }}" @endif {{ $attributes }}>
    <div class="card-media">
        @if($image)
            <img
                src="{{ $image }}"
                @if($imageSrcset)
                    srcset="{{ $imageSrcset }}"
                    sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
                @endif
                alt="{{ $imageAlt }}" width="480" height="270" loading="lazy"
            >
        @else
            <span class="seal-lg" aria-hidden="true"></span>
        @endif
    </div>
    <div class="card-body">
        <h3>{{ $title }}</h3>
        @if($text)
            <p>{{ $text }}</p>
        @endif
        @if($url)
            <span class="btn-link">{{ $ctaLabel }} →</span>
        @endif
    </div>
</{{ $tag }}>
