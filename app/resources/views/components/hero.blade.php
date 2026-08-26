@props(['title', 'subtitle' => null, 'image' => null, 'imageAlt' => '', 'ctaLabel' => null, 'ctaUrl' => null, 'display' => false])
<section class="hero {{ $image ? 'hero--media' : 'hero--plain' }}">
    @if($image)
        <div class="hero-bg">
            <img src="{{ $image }}" alt="{{ $imageAlt }}" width="1600" height="900" loading="eager" fetchpriority="high">
            <div class="hero-scrim" aria-hidden="true"></div>
        </div>
    @endif

    <div class="container">
        <div class="hero-content">
            <h1 @class(['display' => $display])>{{ $title }}</h1>

            @if($subtitle)
                <p class="body-lg hero-subtitle">{{ $subtitle }}</p>
            @endif

            @if($ctaLabel && $ctaUrl)
                <a class="btn btn-primary hero-cta" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
            @endif

            {{ $slot ?? '' }}
        </div>
    </div>
</section>
