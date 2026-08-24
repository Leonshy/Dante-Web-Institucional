<x-layouts.app :title="$post->title.' — Colegio Dante Alighieri'" :description="$post->excerpt" :indexable="$post->is_indexable">
    <x-breadcrumbs :items="[['label' => 'Noticias', 'url' => route('posts.index')], ['label' => $post->title, 'url' => null]]" />
    <main id="contenido" class="container section">
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
                    <img src="{{ $media->conversionUrl('large') ?? $media->url() }}" alt="{{ $media->alt ?? '' }}" width="1200" height="675" loading="eager">
                </div>
            @else
                <div class="article-media" role="img" aria-label="Fotografía de la noticia pendiente de carga">
                    <span class="seal-xl" aria-hidden="true"></span>
                </div>
            @endif

            <div class="body">{!! $post->content !!}</div>

            <p style="margin-top:var(--spacing-6)"><a class="btn-link" href="{{ route('posts.index') }}">← Volver a Noticias</a></p>
        </article>

        @if($related->isNotEmpty())
            <section aria-label="Noticias relacionadas" style="margin-top:var(--spacing-12)">
                <h2 style="margin-bottom:var(--spacing-6)">Noticias relacionadas</h2>
                <div class="related-grid">
                    @foreach($related as $item)
                        <x-card.news :url="route('posts.show', $item->slug)" :title="$item->title" />
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</x-layouts.app>
