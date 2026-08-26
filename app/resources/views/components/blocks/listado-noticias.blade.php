@if($posts->isEmpty())
    <x-empty-state icon="newspaper">
        Todavía no hay noticias publicadas.
        <x-slot:cta><a class="btn btn-secondary" href="{{ route('posts.index') }}">Ver todas las noticias</a></x-slot:cta>
    </x-empty-state>
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
    <p style="margin-top:var(--spacing-6)"><a class="btn btn-secondary" href="{{ route('posts.index') }}">Ver todas las noticias</a></p>
@endif
