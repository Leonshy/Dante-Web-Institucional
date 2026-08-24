<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

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
            $trail[] = ['label' => $node->title, 'url' => '/'.$node->slug];
            $node = $node->parent;
        }

        return array_reverse($trail);
    }
}
