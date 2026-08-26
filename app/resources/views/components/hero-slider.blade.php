@props(['slides' => []])
@php
    $slides = collect($slides)->filter(fn (array $slide) => filled($slide['title'] ?? null))->values();
@endphp
@if($slides->isEmpty())
    {{-- Sin slides configurados en el panel de Inicio: no se renderiza hero. --}}
@elseif($slides->count() === 1)
    @php $slide = $slides->first(); @endphp
    <x-hero
        display
        :title="$slide['title']"
        :subtitle="$slide['subtitle'] ?? null"
        :cta-label="$slide['cta_label'] ?? null"
        :cta-url="$slide['cta_url'] ?? null"
        :image="$slide['image_url'] ?? null"
        :image-alt="$slide['image_alt'] ?? ''"
    />
@else
    <section
        class="hero hero-slider"
        x-data="{
            active: 0,
            count: {{ $slides->count() }},
            timer: null,
            reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            next() { this.active = (this.active + 1) % this.count; },
            prev() { this.active = (this.active - 1 + this.count) % this.count; },
            go(i) { this.active = i; this.restart(); },
            restart() {
                clearInterval(this.timer);
                if (this.reducedMotion) return;
                this.timer = setInterval(() => this.next(), 6000);
            },
        }"
        x-init="restart()"
        @mouseenter="clearInterval(timer)"
        @mouseleave="restart()"
    >
        @foreach($slides as $i => $slide)
            {{-- El primer slide se renderiza visible desde el HTML (sin `display:none` de
                 arranque): es el candidato a LCP y no puede depender de que Alpine cargue,
                 parsee y evalúe `x-show` antes del primer pintado — eso es lo que agregaba
                 ~2 s de "element render delay" medidos en la línea base (docs/09-rendimiento.md
                 §2). Los slides siguientes sí arrancan ocultos: nunca son la imagen visible
                 en la primera pintura, así que no afectan el LCP. --}}
            <div class="hero-slide" x-show="active === {{ $i }}" x-transition:enter.opacity.duration.600ms x-transition:leave.opacity.duration.600ms @if($i !== 0) style="display:none" @endif>
                <div class="hero-bg">
                    <img
                        src="{{ $slide['image_url'] ?? '' }}"
                        @if(!empty($slide['image_srcset']))
                            srcset="{{ $slide['image_srcset'] }}"
                            sizes="100vw"
                        @endif
                        alt="{{ $slide['image_alt'] ?? '' }}"
                        width="1600" height="900"
                        loading="{{ $i === 0 ? 'eager' : 'lazy' }}"
                        @if($i === 0) fetchpriority="high" @endif
                    >
                    <div class="hero-scrim" aria-hidden="true"></div>
                </div>
                <div class="container">
                    <div class="hero-content">
                        <h1 class="display">{{ $slide['title'] }}</h1>
                        @if(!empty($slide['subtitle']))
                            <p class="body-lg hero-subtitle">{{ $slide['subtitle'] }}</p>
                        @endif
                        @if(!empty($slide['cta_label']) && !empty($slide['cta_url']))
                            <a class="btn btn-primary hero-cta" href="{{ $slide['cta_url'] }}">{{ $slide['cta_label'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="hero-slider-controls">
            <button type="button" class="hero-slider-arrow hero-slider-arrow--prev" @click="prev(); restart()" aria-label="Slide anterior">
                <x-icon.chevron-down />
            </button>
            <div class="hero-slider-dots" role="tablist" aria-label="Slides del hero">
                @foreach($slides as $i => $slide)
                    <button
                        type="button" role="tab" class="hero-slider-dot"
                        :class="{ 'is-active': active === {{ $i }} }"
                        :aria-selected="(active === {{ $i }}).toString()"
                        @click="go({{ $i }})"
                        aria-label="Ir al slide {{ $i + 1 }}"
                    ></button>
                @endforeach
            </div>
            <button type="button" class="hero-slider-arrow hero-slider-arrow--next" @click="next(); restart()" aria-label="Siguiente slide">
                <x-icon.chevron-down />
            </button>
        </div>
    </section>
@endif
