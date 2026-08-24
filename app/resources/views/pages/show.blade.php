<x-layouts.app :title="$page->effectiveSeoTitle().' — Colegio Dante Alighieri'" :description="$page->getTranslation('seo_description', app()->getLocale())" :indexable="$page->is_indexable">
    <x-breadcrumbs :items="$breadcrumbs" />
    <main id="contenido">
        {{-- Si el primer bloque es un hero, ese bloque ya trae su propio <h1> —
             no se duplica acá para mantener un solo h1 por página. --}}
        @if(($blocks[0]['type'] ?? null) !== 'hero')
            <div class="container section" style="padding-bottom:0">
                <h1>{{ $page->title }}</h1>
            </div>

            @if($page->coverMedia)
                <div class="container">
                    <img src="{{ $page->coverMedia->conversionUrl('w1200') ?? $page->coverMedia->url() }}"
                         alt="{{ $page->coverMedia->alt ?? '' }}" width="1200" height="675"
                         style="width:100%;height:auto;border-radius:var(--radius-md);margin-bottom:var(--spacing-6)" loading="eager">
                </div>
            @endif
        @endif

        @if($siblings->isNotEmpty())
            <div class="container">
                <div class="layout-with-aside" style="margin-top:var(--spacing-6)">
                    <aside class="side-nav" aria-label="Páginas relacionadas">
                        <h2>En esta sección</h2>
                        <ul>
                            @foreach($siblings as $sibling)
                                <li>
                                    <a href="{{ url('/'.$sibling->slug) }}" @if($sibling->is($page)) aria-current="page" @endif>
                                        {{ $sibling->title }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </aside>
                    <div>
                        <x-page-blocks :blocks="$blocks" />
                    </div>
                </div>
            </div>
        @else
            <x-page-blocks :blocks="$blocks" />
        @endif
    </main>
</x-layouts.app>
