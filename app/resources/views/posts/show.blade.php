<x-layouts.app
    :title="$post->title.' — Colegio Dante Alighieri'"
    :description="$post->excerpt"
    :indexable="$post->is_indexable"
    og-type="article"
    :og-image="$post->featuredMedia?->conversionUrl('w1200') ?? $post->featuredMedia?->url()"
>
    <x-breadcrumbs :items="[['label' => 'Noticias', 'url' => route('posts.index')], ['label' => $post->title, 'url' => null]]" />
    @php
        // JSON-LD NewsArticle (docs/08-seo.md §3).
        $articleSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $post->title,
            'description' => $post->excerpt,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'image' => $post->featuredMedia?->conversionUrl('w1200') ?? $post->featuredMedia?->url(),
            'mainEntityOfPage' => route('posts.show', $post->slug),
            'publisher' => [
                '@type' => 'EducationalOrganization',
                'name' => config('dante.seo.organization_name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo-dante.svg'),
                ],
            ],
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <main id="contenido" tabindex="-1" class="container section">
        {{-- Desktop: artículo 3/4 + relacionadas 1/4 como sidebar a la derecha.
             Antes las relacionadas iban debajo, a todo el ancho, dejando una
             columna derecha vacía junto al artículo (max-width:760px lo
             angosta para lectura, pero el contenedor sigue siendo de 1280px).
             En mobile el apilado vertical no cambia. --}}
        <div class="article-layout">
            <article class="article-content">
                <h1>{{ $post->title }}</h1>
                <div class="article-meta">
                    @if($post->category)
                        <span class="chip">{{ $post->category->name }}</span>
                    @endif
                    <span>Publicado el {{ optional($post->published_at)->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>

                @if($media = $post->featuredMedia)
                    <div class="article-media">
                        {{-- Es el elemento LCP real de esta plantilla (medido en la línea base,
                             docs/09-rendimiento.md §2): srcset real + fetchpriority, nunca
                             `loading="lazy"` acá. --}}
                        <img
                            src="{{ $media->conversionUrl('large') ?? $media->url() }}"
                            @if($srcset = $media->srcset())
                                srcset="{{ $srcset }}"
                                sizes="(min-width: 1024px) 800px, 100vw"
                            @endif
                            alt="{{ $media->alt ?? '' }}" width="1200" height="675"
                            loading="eager" fetchpriority="high"
                        >
                    </div>
                @else
                    <div class="article-media" role="img" aria-label="Fotografía de la noticia pendiente de carga">
                        <span class="seal-xl" aria-hidden="true"></span>
                    </div>
                @endif

                <div class="body">{!! $post->content !!}</div>

                <x-share-links :title="$post->title" />

                <p style="margin-top:var(--spacing-6)"><a class="btn-link" href="{{ route('posts.index') }}">← Volver a Noticias</a></p>
            </article>

            @if($related->isNotEmpty())
                <aside class="related-sidebar" aria-label="Noticias relacionadas">
                    <h2>Noticias relacionadas</h2>
                    <div class="related-grid">
                        @foreach($related as $item)
                            <x-card.news
                                :url="route('posts.show', $item->slug)"
                                :title="$item->title"
                                :category="$item->category?->name"
                                :date="optional($item->published_at)->translatedFormat('d \d\e F \d\e Y')"
                                :image="$item->featuredMedia?->conversionUrl('medium') ?? $item->featuredMedia?->url()"
                                :image-srcset="$item->featuredMedia?->srcset()"
                            />
                        @endforeach
                    </div>
                </aside>
            @endif
        </div>
    </main>
</x-layouts.app>
