@props(['title', 'subtitle' => null, 'image' => null, 'imageAlt' => '', 'ctaLabel' => null, 'ctaUrl' => null, 'display' => false])
<div class="hero">
    <div class="container">
        <h1 @class(['display' => $display])>{{ $title }}</h1>

        @if($image)
            <div class="hero-media">
                <img src="{{ $image }}" alt="{{ $imageAlt }}" width="1600" height="900" loading="eager" fetchpriority="high">
            </div>
        @else
            <div class="hero-media" role="img" aria-label="Fotografía institucional pendiente de selección">
                <span class="seal-xl" aria-hidden="true"></span>
            </div>
        @endif

        @if($subtitle)
            <p class="body-lg" style="max-width:640px">{{ $subtitle }}</p>
        @endif

        @if($ctaLabel && $ctaUrl)
            <a class="btn btn-primary" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
        @endif

        {{ $slot ?? '' }}
    </div>
</div>
