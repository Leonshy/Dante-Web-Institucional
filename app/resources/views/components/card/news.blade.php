@props(['url', 'title', 'excerpt' => null, 'category' => null, 'date' => null, 'image' => null, 'imageAlt' => ''])
<a class="card" href="{{ $url }}" {{ $attributes }}>
    <div class="card-media">
        @if($image)
            <img src="{{ $image }}" alt="{{ $imageAlt }}" width="480" height="270" loading="lazy">
        @else
            <span class="seal-lg" aria-hidden="true"></span>
        @endif
    </div>
    <div class="card-body">
        @if($category)
            <span class="chip">{{ $category }}</span>
        @endif
        <h3>{{ $title }}</h3>
        @if($excerpt)
            <p>{{ $excerpt }}</p>
        @endif
        @if($date)
            <p class="meta">{{ $date }}</p>
        @endif
    </div>
</a>
