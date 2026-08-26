<x-layouts.app title="Noticias — Colegio Dante Alighieri" description="Noticias y novedades del Colegio Dante Alighieri: actos, visitas y actividades institucionales.">
    <x-breadcrumbs :items="[['label' => 'Noticias', 'url' => null]]" />
    <main id="contenido" tabindex="-1" class="container section">
        <h1>Noticias</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin:var(--spacing-2) 0 var(--spacing-6)">Novedades y noticias del Colegio Dante Alighieri.</p>

        @if($categories->isNotEmpty())
            <x-filter-tabs :options="$categories" :active="$active" param-name="categoria" />
        @endif

        @if($posts->isEmpty())
            <x-empty-state icon="newspaper">Todavía no hay noticias publicadas en esta categoría.</x-empty-state>
        @else
            <div class="cards-grid">
                @foreach($posts as $post)
                    <x-card.news
                        :url="route('posts.show', $post->slug)"
                        :title="$post->title"
                        :excerpt="$post->excerpt"
                        :category="$post->category?->name"
                        :date="optional($post->published_at)->translatedFormat('d \d\e F \d\e Y')"
                        :image="$post->featuredMedia?->conversionUrl('medium') ?? $post->featuredMedia?->url()"
                        :image-srcset="$post->featuredMedia?->srcset()"
                    />
                @endforeach
            </div>
            {{ $posts->onEachSide(1)->links('pagination.dante') }}
        @endif
    </main>
</x-layouts.app>
