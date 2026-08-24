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
            ->map(function (array $block) use ($locale): array {
                $resolved = [];

                foreach ($block as $field => $value) {
                    if ($field === 'type') {
                        continue;
                    }

                    $resolved[$field] = is_array($value) && (array_key_exists('es', $value) || array_key_exists('it', $value))
                        ? ($value[$locale] ?? $value['es'] ?? null)
                        : $value;
                }

                return ['type' => $block['type'], 'data' => $resolved];
            })
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
}
