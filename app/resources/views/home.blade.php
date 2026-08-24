<x-layouts.app title="Colegio Dante Alighieri — bilingüe español-italiano, Asunción"
    description="Colegio bilingüe afiliado a la Società Dante Alighieri, con más de un siglo de historia y certificación internacional PLIDA.">

    <main id="contenido">
        <x-hero
            display
            title="Colegio Dante Alighieri — educación bilingüe español-italiano en Asunción"
            subtitle="Afiliados a la Società Dante Alighieri de Roma. Educación bilingüe desde el nivel inicial, con certificación internacional PLIDA."
            cta-label="Quiero inscribir a mi hijo/a"
            cta-url="{{ url('/admisiones') }}"
        />

        <section class="section reveal">
            <div class="container">
                <div class="section-head">
                    <h2>Nuestra propuesta educativa</h2>
                    <p class="body-lg" style="max-width:720px;color:var(--color-neutral-700)">Instituto de Lengua y Cultura, Cursos de Italiano y certificación internacional PLIDA, obligatoria en ciertos grados.</p>
                </div>
                <div class="cards-grid">
                    <x-card.section title="Instituto de Lengua y Cultura" text="Cursos de italiano para niños, jóvenes y adultos, dentro y fuera del colegio." url="{{ url('/oferta-educativa/instituto-de-lengua-y-cultura') }}" />
                    <x-card.section title="Cursos de Italiano" text="Niveles y certificación PLIDA (Proyecto Lengua Italiana Dante Alighieri)." url="{{ url('/oferta-educativa/cursos-de-italiano') }}" />
                    <x-card.section title="Oferta educativa completa" text="Educación bilingüe español-italiano, desde el nivel inicial hasta la certificación internacional." url="{{ url('/oferta-educativa') }}" />
                </div>
            </div>
        </section>

        <section class="section reveal" style="background:var(--color-neutral-100)">
            <div class="container">
                <div class="section-head"><h2>Más de un siglo de historia</h2></div>
                <div class="stats-grid">
                    <div><span class="stat-number">129°</span><p>Aniversario de la Scuola Dante Alighieri</p></div>
                    <div><span class="stat-number">2024</span><p>Visita del presidente de Italia, Sergio Mattarella</p></div>
                    <div><span class="stat-number">Afiliados</span><p>a la Società Dante Alighieri, Roma</p></div>
                </div>
            </div>
        </section>

        <section class="section reveal">
            <div class="container">
                <div class="section-head"><h2>Noticias del colegio</h2></div>
                @if($posts->isEmpty())
                    <x-empty-state icon="newspaper">Todavía no hay noticias publicadas.</x-empty-state>
                @else
                    <div class="cards-grid">
                        @foreach($posts as $post)
                            <x-card.news
                                :url="route('posts.show', $post->slug)"
                                :title="$post->title"
                                :excerpt="$post->excerpt"
                                :category="$post->category?->name"
                                :date="optional($post->published_at)->translatedFormat('d \d\e F \d\e Y')"
                                :image="$post->featuredMedia?->conversionUrl('medium')"
                            />
                        @endforeach
                    </div>
                    <p style="margin-top:var(--spacing-6)"><a class="btn btn-secondary" href="{{ route('posts.index') }}">Ver todas las noticias</a></p>
                @endif
            </div>
        </section>

        <section class="section reveal">
            <div class="container">
                @if($announcements->isEmpty())
                    <x-empty-state icon="megaphone">
                        No hay comunicados vigentes por el momento.
                        <x-slot:cta><a class="btn btn-secondary" href="{{ url('/vida-escolar/comunicados') }}">Ver todos los comunicados</a></x-slot:cta>
                    </x-empty-state>
                @else
                    <div class="section-head"><h2>Comunicados</h2></div>
                    <ul style="list-style:none;margin:0;padding:0">
                        @foreach($announcements as $announcement)
                            <li class="content-block"><h3>{{ $announcement->title }}</h3></li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>

        <section class="section reveal">
            <div class="container">
                <div class="cta-block">
                    <h2>¿Quiere conocer el colegio?</h2>
                    <p>Complete la pre-inscripción y lo contactamos.</p>
                    <a class="btn btn-primary" href="{{ url('/admisiones') }}">Quiero inscribir a mi hijo/a</a>
                </div>
            </div>
        </section>
    </main>

</x-layouts.app>
