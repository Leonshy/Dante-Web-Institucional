@props(['url' => null, 'title', 'text' => null, 'image' => null, 'imageAlt' => '', 'ctaLabel' => 'Ver más'])
@php $tag = $url ? 'a' : 'div'; @endphp
<{{ $tag }} class="card" @if($url) href="{{ $url }}" @endif {{ $attributes }}>
    <div class="card-media">
        @if($image)
            <img src="{{ $image }}" alt="{{ $imageAlt }}" width="480" height="270" loading="lazy">
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
