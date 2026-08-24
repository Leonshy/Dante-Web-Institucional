<?php

namespace App\Services\Search;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Buscador interno del sitio. Solo indexa contenido publicado, con un LIKE
 * simple sobre los campos traducibles relevantes — no hace falta full-text
 * search ni Scout para el volumen del sitio (~40 páginas, un puñado de
 * noticias/documentos/comunicados). Ver docs/05-backend-modelo-datos.md §8.
 *
 * El renderizado público (vista/UI de resultados) llega en la Fase 4; este
 * servicio y su endpoint son solo la capa de datos.
 */
class SearchService
{
    /** Cantidad máxima de resultados por tipo de contenido. */
    private const int RESULTS_PER_TYPE = 10;

    /** Longitud del extracto mostrado bajo el título. */
    private const int EXCERPT_LENGTH = 160;

    /**
     * @return Collection<int, SearchResult>
     */
    public function search(string $term): Collection
    {
        $term = trim($term);

        if ($term === '') {
            return collect();
        }

        $locales = $this->activeLocales();

        return collect()
            ->merge($this->searchPages($term, $locales))
            ->merge($this->searchPosts($term, $locales))
            ->merge($this->searchDocuments($term, $locales))
            ->merge($this->searchAnnouncements($term, $locales))
            ->values();
    }

    /**
     * @return array<int, string>
     */
    private function activeLocales(): array
    {
        return SiteSetting::italianEnabled() ? ['es', 'it'] : ['es'];
    }

    /**
     * @param  array<int, string>  $fields
     * @param  array<int, string>  $locales
     */
    private function matchTranslatable(Builder $query, array $fields, string $term, array $locales): void
    {
        $query->where(function (Builder $inner) use ($fields, $term, $locales): void {
            foreach ($fields as $field) {
                foreach ($locales as $locale) {
                    $inner->orWhere("{$field}->{$locale}", 'like', "%{$term}%");
                }
            }
        });
    }

    /**
     * @param  array<int, string>  $locales
     * @return Collection<int, SearchResult>
     */
    private function searchPages(string $term, array $locales): Collection
    {
        $locale = app()->getLocale();

        $pages = Page::query()
            ->where('status', 'published')
            ->tap(fn (Builder $query) => $this->matchTranslatable($query, ['title'], $term, $locales))
            ->limit(self::RESULTS_PER_TYPE)
            ->get();

        return $pages->map(fn (Page $page) => new SearchResult(
            type: 'page',
            typeLabel: 'Página',
            title: (string) $page->getTranslation('title', $locale),
            excerpt: null,
            url: '/'.$page->slug,
        ));
    }

    /**
     * @param  array<int, string>  $locales
     * @return Collection<int, SearchResult>
     */
    private function searchPosts(string $term, array $locales): Collection
    {
        $locale = app()->getLocale();

        $posts = Post::query()
            ->where('status', 'published')
            ->tap(fn (Builder $query) => $this->matchTranslatable($query, ['title', 'excerpt', 'content'], $term, $locales))
            ->limit(self::RESULTS_PER_TYPE)
            ->get();

        return $posts->map(fn (Post $post) => new SearchResult(
            type: 'post',
            typeLabel: 'Noticia',
            title: (string) $post->getTranslation('title', $locale),
            excerpt: $this->excerptFrom($post->getTranslation('excerpt', $locale) ?: $post->getTranslation('content', $locale)),
            url: '/noticias/'.$post->slug,
        ));
    }

    /**
     * @param  array<int, string>  $locales
     * @return Collection<int, SearchResult>
     */
    private function searchDocuments(string $term, array $locales): Collection
    {
        $locale = app()->getLocale();

        $documents = Document::query()
            ->where('status', 'published')
            ->tap(fn (Builder $query) => $this->matchTranslatable($query, ['title', 'description'], $term, $locales))
            ->limit(self::RESULTS_PER_TYPE)
            ->get();

        return $documents->map(fn (Document $document) => new SearchResult(
            type: 'document',
            typeLabel: 'Documento',
            title: (string) $document->getTranslation('title', $locale),
            excerpt: $this->excerptFrom($document->getTranslation('description', $locale)),
            // Los documentos no tienen una URL pública propia todavía (Fase 4
            // define cómo se descargan/muestran); se deja sin resolver.
            url: null,
        ));
    }

    /**
     * @param  array<int, string>  $locales
     * @return Collection<int, SearchResult>
     */
    private function searchAnnouncements(string $term, array $locales): Collection
    {
        $locale = app()->getLocale();

        $announcements = Announcement::query()
            ->where('status', 'published')
            ->tap(fn (Builder $query) => $this->matchTranslatable($query, ['title', 'content'], $term, $locales))
            ->limit(self::RESULTS_PER_TYPE)
            ->get();

        return $announcements->map(fn (Announcement $announcement) => new SearchResult(
            type: 'announcement',
            typeLabel: 'Comunicado',
            title: (string) $announcement->getTranslation('title', $locale),
            excerpt: $this->excerptFrom($announcement->getTranslation('content', $locale)),
            // Igual que Document: sin página pública propia todavía.
            url: null,
        ));
    }

    private function excerptFrom(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return null;
        }

        return Str::limit(trim(strip_tags($text)), self::EXCERPT_LENGTH);
    }
}
