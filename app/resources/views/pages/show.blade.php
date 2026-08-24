<x-layouts.app :title="$page->effectiveSeoTitle().' — Colegio Dante Alighieri'" :description="$page->getTranslation('seo_description', app()->getLocale())" :indexable="$page->is_indexable">
    <x-breadcrumbs :items="$breadcrumbs" />
    <main id="contenido">
        <div class="container section" style="padding-bottom:0">
            <h1>{{ $page->title }}</h1>
        </div>

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
