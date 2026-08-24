<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $announcements = Announcement::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('home', compact('posts', 'announcements'));
    }
}
