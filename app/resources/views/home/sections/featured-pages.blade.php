<section class="section reveal">
    <div class="container">
        <div class="section-head">
            <h2>Páginas destacadas</h2>
        </div>
        @if($featuredPages->isEmpty())
            <x-empty-state icon="file-text">Todavía no hay páginas destacadas para el inicio.</x-empty-state>
        @else
            <div class="cards-grid">
                @foreach($featuredPages as $page)
                    <x-card.section
                        :title="$page->title"
                        :text="$page->home_excerpt"
                        :url="url($page->urlPath())"
                        :image="$page->coverMedia?->conversionUrl('medium') ?? $page->coverMedia?->url()"
                        :image-srcset="$page->coverMedia?->srcset()"
                    />
                @endforeach
            </div>
        @endif
    </div>
</section>
