<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use App\Models\Concerns\ResolvesLocaleFields;
use App\Services\Cache\PublicContentCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

/**
 * Página institucional con jerarquía real (docs/01 §A.3, se abandona el `section`
 * fijo de IPG) y bloques de contenido (docs/02 §8).
 *
 * @property array<int, array<string, mixed>>|null $blocks
 */
#[Fillable([
    'wp_legacy_id', 'created_by', 'updated_by', 'parent_id', 'cover_media_id', 'seo_image_id',
    'title', 'slug', 'template', 'site_section', 'site', 'blocks',
    'seo_title', 'seo_description', 'canonical_url', 'is_indexable',
    'status', 'published_at', 'sort_order', 'is_featured_home', 'home_excerpt',
])]
class Page extends Model
{
    use HasAuditing, HasFactory, HasTranslations, ResolvesLocaleFields, SoftDeletes;

    public array $translatable = ['title', 'seo_title', 'seo_description', 'home_excerpt'];

    /**
     * Invalida la caché de consulta pública (`PublicContentCache`, Fase 7,
     * docs/09-rendimiento.md §6) al guardar o borrar — se limpia tanto el slug
     * actual como el original si se editó el slug, para no dejar una entrada
     * vieja apuntando a contenido que ya no existe en esa URL.
     */
    protected static function booted(): void
    {
        static::saved(function (self $page) {
            PublicContentCache::forgetPageSlug($page->slug);
            PublicContentCache::forgetPageSlug($page->getOriginal('slug'));
        });

        static::deleted(function (self $page) {
            PublicContentCache::forgetPageSlug($page->slug);
        });
    }

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'is_featured_home' => 'boolean',
            'published_at' => 'datetime',
            'blocks' => 'array',
        ];
    }

    /**
     * Bloques de esta página en el idioma activo. Cada bloque guarda sus
     * campos de texto anidados por locale (ej. `title.es`, `title.it`).
     *
     * @return array<int, array<string, mixed>>
     */
    public function blocksForLocale(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($this->blocks ?? [])
            ->map(fn (array $block): array => [
                'type' => $block['type'],
                'data' => self::resolveLocaleFields(is_array($block['data'] ?? null) ? $block['data'] : [], $locale),
            ])
            ->all();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function seoImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'seo_image_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function effectiveSeoTitle(?string $locale = null): ?string
    {
        return $this->getTranslation('seo_title', $locale ?? app()->getLocale())
            ?: $this->getTranslation('title', $locale ?? app()->getLocale());
    }

    /**
     * Ruta pública completa de la página, construida a partir de la cadena de
     * padres (ej. `institucion/historia`) — `slug` es único mundialmente pero
     * guarda un solo segmento por nivel (docs/06-frontend.md).
     */
    public function urlPath(): string
    {
        $segments = [];
        $node = $this;

        while ($node !== null) {
            array_unshift($segments, $node->slug);
            $node = $node->parent;
        }

        return implode('/', $segments);
    }

    /**
     * URL pública de una página de sección (ej. "vida-escolar") solo si ya está
     * publicada — evita que breadcrumbs de otras secciones (Comunicados,
     * Calendario, Galería) enlacen a una página de aterrizaje todavía en
     * borrador y den 404 al público.
     */
    public static function publishedUrl(string $slug): ?string
    {
        $isPublished = static::query()->where('slug', $slug)->where('status', 'published')->exists();

        return $isPublished ? url('/'.$slug) : null;
    }
}
