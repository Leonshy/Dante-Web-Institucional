<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::query()
            ->where('status', 'published')
            ->orderByDesc('event_date')
            ->get();

        return view('galleries.index', compact('galleries'));
    }
}
