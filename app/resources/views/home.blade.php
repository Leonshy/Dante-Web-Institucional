<x-layouts.app title="Colegio Dante Alighieri — bilingüe español-italiano, Asunción"
    description="Colegio bilingüe afiliado a la Società Dante Alighieri, con más de un siglo de historia y certificación internacional PLIDA.">

    {{-- JSON-LD WebSite + SearchAction (docs/08-seo.md §3) — solo en el inicio. --}}
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('dante.seo.organization_name'),
        'url' => url('/'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => url('/buscar').'?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <main id="contenido" tabindex="-1">
        <x-hero-slider :slides="$heroSlides" />

        <section class="section reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Nuestra propuesta educativa</h2>
                    <p class="body-lg" style="max-width:720px;color:var(--color-neutral-700)">Instituto de Lengua y Cultura, Cursos de Italiano y certificación internacional PLIDA, obligatoria en ciertos grados.</p>
                </div>
                <div class="cards-grid">
                    <x-card.section title="Instituto de Lengua y Cultura" text="Cursos de italiano para niños, jóvenes y adultos, dentro y fuera del colegio." url="{{ url('/oferta-educativa/instituto-de-lengua-y-cultura') }}" :image="$languageInstituteImage?->conversionUrl('medium') ?? $languageInstituteImage?->url()" :image-srcset="$languageInstituteImage?->srcset()" />
                    <x-card.section title="Cursos de Italiano" text="Niveles y certificación PLIDA (Proyecto Lengua Italiana Dante Alighieri)." url="{{ url('/oferta-educativa/cursos-de-italiano') }}" :image="$italianCoursesImage?->conversionUrl('medium') ?? $italianCoursesImage?->url()" :image-srcset="$italianCoursesImage?->srcset()" />
                    <x-card.section title="Oferta educativa completa" text="Educación bilingüe español-italiano, desde el nivel inicial hasta la certificación internacional." url="{{ url('/institucion/quienes-somos') }}" :image="$offeringImage?->conversionUrl('medium') ?? $offeringImage?->url()" :image-srcset="$offeringImage?->srcset()" />
                </div>
            </div>
        </section>

        @include('home.sections.stats')

        @foreach($enabledSections as $section)
            @continue($section === 'stats')
            @include('home.sections.'.str_replace('_', '-', $section))
        @endforeach

        <section class="section reveal">
            <div class="container">
                <div class="cta-block">
                    <h2>{{ $homeSettings->cta_title ?? '¿Quiere conocer el colegio?' }}</h2>
                    <p>{{ $homeSettings->cta_text ?? 'Complete la pre-inscripción y lo contactamos.' }}</p>
                    <a class="btn btn-primary" href="{{ $homeSettings->cta_button_url ?? url('/admisiones') }}">{{ $homeSettings->cta_button_label ?? 'Quiero inscribir a mi hijo/a' }}</a>
                </div>
            </div>
        </section>
    </main>

</x-layouts.app>
