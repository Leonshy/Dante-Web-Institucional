<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Contracts\Support\Responsable;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * `sitemap.xml` dinámico (docs/08-seo.md §4) — se genera en cada petición a
 * partir de la base, con `lastmod` real (`updated_at`). Excluye borradores,
 * archivadas y todo lo marcado `noindex`. Sin caché de archivo: el volumen
 * del sitio no lo justifica (spatie/laravel-sitemap ya está entre los
 * paquetes de referencia, CLAUDE.md §3).
 */
class SitemapController extends Controller
{
    public function index(): Responsable
    {
        $sitemap = Sitemap::create()
            ->add(
                Url::create(url('/'))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(1.0)
            );

        Page::query()
            ->where('status', 'published')
            ->where('is_indexable', true)
            ->get()
            ->each(function (Page $page) use ($sitemap): void {
                $sitemap->add(
                    Url::create(url('/'.$page->urlPath()))
                        ->setLastModificationDate($page->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.8)
                );
            });

        Post::query()
            ->where('status', 'published')
            ->where('is_indexable', true)
            ->get()
            ->each(function (Post $post) use ($sitemap): void {
                $sitemap->add(
                    Url::create(route('posts.show', $post->slug))
                        ->setLastModificationDate($post->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                        ->setPriority(0.6)
                );
            });

        $sitemap->add(Url::create(route('posts.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        $sitemap->add(Url::create(route('documents.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));
        $sitemap->add(Url::create(route('contact.show'))->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

        return $sitemap;
    }
}
