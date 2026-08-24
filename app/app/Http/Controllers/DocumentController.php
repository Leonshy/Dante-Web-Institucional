<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('categoria')->toString() ?: null;
        $site = $request->string('sede')->toString() ?: null;

        $documents = Document::query()
            ->where('status', 'published')
            ->where('is_current', true)
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->when($site, fn ($q) => $q->where(fn ($inner) => $inner->where('site', $site)->orWhere('site', 'ambas')))
            ->with(['category', 'file'])
            ->orderByDesc('published_at')
            ->get();

        $categories = Category::query()
            ->where('type', 'document')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [$category->slug => $category->name]);

        return view('documents.index', [
            'documents' => $documents,
            'categories' => $categories,
            'active' => $categorySlug ?? 'todos',
        ]);
    }
}
