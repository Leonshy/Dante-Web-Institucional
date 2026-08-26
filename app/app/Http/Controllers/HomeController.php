<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Gallery;
use App\Models\HomeSetting;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $homeSettings = HomeSetting::current();
        $enabledSections = $homeSettings->enabledSectionsInOrder();

        $featuredPages = in_array('featured_pages', $enabledSections, true)
            ? Page::query()
                ->with('coverMedia')
                ->where('is_featured_home', true)
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->get()
            : Collection::make();

        $posts = in_array('news', $enabledSections, true)
            ? Post::query()
                ->with(['category', 'featuredMedia'])
                ->where('status', 'published')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
            : Collection::make();

        $announcements = in_array('announcements', $enabledSections, true)
            ? Announcement::query()
                ->where('status', 'published')
                ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
            : Collection::make();

        $documents = in_array('documents', $enabledSections, true)
            ? Document::query()
                ->where('status', 'published')
                ->where('is_current', true)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
            : Collection::make();

        // Se carga la relación completa (no un `limit(1)` en el eager load: Eloquent
        // no soporta "límite por grupo" sin funciones de ventana, un `limit` acá
        // aplicaría sobre el total combinado de las 3 galerías, no por álbum). El
        // volumen de medios por álbum es bajo, así que sigue siendo 1 sola consulta.
        $galleries = in_array('gallery', $enabledSections, true)
            ? Gallery::query()
                ->with('media')
                ->where('status', 'published')
                ->orderByDesc('event_date')
                ->limit(3)
                ->get()
            : Collection::make();

        $heroSlides = $this->resolveHeroSlideImages($homeSettings->heroSlidesForLocale());

        // Sección fija "Nuestra propuesta educativa": no es parte de las
        // secciones administrables del panel de Inicio, siempre se muestra
        // justo después del hero (no negociable, pedido explícito del cliente).
        $languageInstituteImage = Page::where('slug', 'oferta-educativa/instituto-de-lengua-y-cultura')->first()?->coverMedia;
        $italianCoursesImage = Page::where('slug', 'oferta-educativa/cursos-de-italiano')->first()?->coverMedia;
        $offeringImage = Page::where('slug', 'vida-escolar/biblioteca')->first()?->coverMedia;

        return view('home', [
            'homeSettings' => $homeSettings,
            'enabledSections' => $enabledSections,
            'heroSlides' => $heroSlides,
            'stats' => $homeSettings->statsForLocale(),
            'featuredPages' => $featuredPages,
            'posts' => $posts,
            'announcements' => $announcements,
            'documents' => $documents,
            'galleries' => $galleries,
            'languageInstituteImage' => $languageInstituteImage,
            'italianCoursesImage' => $italianCoursesImage,
            'offeringImage' => $offeringImage,
        ]);
    }

    /**
     * Agrega `image_url`/`image_alt` a cada slide del hero, resueltos desde
     * `Media` (el slide solo guarda `media_id`, elegido con el `MediaPicker`
     * en el panel de Inicio).
     *
     * @param  array<int, array<string, mixed>>  $slides
     * @return array<int, array<string, mixed>>
     */
    private function resolveHeroSlideImages(array $slides): array
    {
        $mediaIds = collect($slides)->pluck('media_id')->filter()->values();

        if ($mediaIds->isEmpty()) {
            return $slides;
        }

        $mediaById = Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id');

        return collect($slides)->map(function (array $slide) use ($mediaById) {
            $media = $mediaById->get($slide['media_id'] ?? null);

            if ($media === null) {
                $slide['image_url'] = null;
                $slide['image_srcset'] = null;
                $slide['image_alt'] = '';

                return $slide;
            }

            $slide['image_url'] = $media->conversionUrl('large') ?? $media->url();
            $slide['image_srcset'] = $media->srcset();
            $slide['image_alt'] = $media->alt ?? '';

            return $slide;
        })->all();
    }
}
