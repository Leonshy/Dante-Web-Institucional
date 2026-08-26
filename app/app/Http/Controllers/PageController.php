<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\Cache\PublicContentCache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        // Caché de consulta (no de respuesta HTTP completa, ver el porqué en
        // PublicContentCache) — invalidada al guardar/borrar desde el panel.
        $page = PublicContentCache::rememberPageBySlug(
            $slug,
            fn () => Page::query()
                ->with(['coverMedia', 'seoImage'])
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first()
        );

        abort_if($page === null, 404);

        $breadcrumbs = $this->breadcrumbsFor($page);
        $blocks = $page->blocksForLocale();

        if ($page->template === 'landing') {
            return view('pages.landing', compact('page', 'breadcrumbs', 'blocks'));
        }

        $siblings = $page->parent_id
            ? Page::query()
                ->where('parent_id', $page->parent_id)
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->get()
            : collect();

        return view('pages.show', compact('page', 'breadcrumbs', 'blocks', 'siblings'));
    }

    /** @return array<int, array{label: string, url: ?string}> */
    private function breadcrumbsFor(Page $page): array
    {
        $trail = [];
        $node = $page;

        while ($node !== null) {
            $trail[] = ['label' => $node->title, 'url' => $node->status === 'published' ? '/'.$node->slug : null];
            $node = $node->parent;
        }

        return array_reverse($trail);
    }
}
