<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
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
    'created_by', 'updated_by', 'parent_id', 'cover_media_id', 'seo_image_id',
    'title', 'slug', 'template', 'site_section', 'site', 'blocks',
    'seo_title', 'seo_description', 'canonical_url', 'is_indexable',
    'status', 'published_at', 'sort_order',
])]
class Page extends Model
{
    use HasAuditing, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'seo_title', 'seo_description'];

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
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
                'data' => self::resolveBlockLocale(is_array($block['data'] ?? null) ? $block['data'] : [], $locale),
            ])
            ->all();
    }

    /**
     * Resuelve recursivamente los campos bilingües (`{"es": "...", "it": "..."}`)
     * de los datos de un bloque, incluidos los que están dentro de repetidores
     * (ej. `faq`/`testimonios`), al idioma pedido.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function resolveBlockLocale(array $data, string $locale): array
    {
        return collect($data)->map(function ($value) use ($locale) {
            if (! is_array($value)) {
                return $value;
            }

            if (array_key_exists('es', $value) || array_key_exists('it', $value)) {
                return $value[$locale] ?? $value['es'] ?? null;
            }

            return array_is_list($value)
                ? collect($value)->map(fn ($item) => is_array($item) ? self::resolveBlockLocale($item, $locale) : $item)->all()
                : self::resolveBlockLocale($value, $locale);
        })->all();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

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
}
