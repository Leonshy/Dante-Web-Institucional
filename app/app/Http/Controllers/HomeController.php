<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Page;
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

        // La home todavía no es editable desde el panel (docs/06-frontend.md,
        // pendiente): mientras tanto reutiliza la portada real ya migrada de
        // las páginas institucionales correspondientes, en vez de no mostrar
        // ninguna imagen o inventar una.
        $heroImage = Page::where('slug', 'institucion/quienes-somos')->first()?->coverMedia;
        $languageInstituteImage = Page::where('slug', 'oferta-educativa/instituto-de-lengua-y-cultura')->first()?->coverMedia;
        $italianCoursesImage = Page::where('slug', 'oferta-educativa/cursos-de-italiano')->first()?->coverMedia;
        $offeringImage = Page::where('slug', 'vida-escolar/biblioteca')->first()?->coverMedia;

        return view('home', compact(
            'posts',
            'announcements',
            'heroImage',
            'languageInstituteImage',
            'italianCoursesImage',
            'offeringImage',
        ));
    }
}
