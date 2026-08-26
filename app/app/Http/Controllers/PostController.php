<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Services\Cache\PublicContentCache;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('categoria')->toString() ?: null;

        $posts = Post::query()
            ->with(['category', 'featuredMedia'])
            ->where('status', 'published')
            ->when($categorySlug, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $categorySlug)))
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Category::query()
            ->where('type', 'news')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (Category $category) => [$category->slug => $category->name]);

        return view('posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'active' => $categorySlug ?? 'todas',
        ]);
    }

    public function show(string $slug): View
    {
        $post = PublicContentCache::rememberPostBySlug(
            $slug,
            fn () => Post::query()
                ->with(['category', 'featuredMedia'])
                ->where('slug', $slug)
                ->where('status', 'published')
                ->first()
        );

        abort_if($post === null, 404);

        $related = Post::query()
            ->with(['category', 'featuredMedia'])
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->when($post->category_id, fn ($q) => $q->where('category_id', $post->category_id))
            ->orderByDesc('published_at')
            ->limit(2)
            ->get();

        return view('posts.show', compact('post', 'related'));
    }
}
