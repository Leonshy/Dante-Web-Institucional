<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use App\Services\Cache\PublicContentCache;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'wp_legacy_id', 'created_by', 'updated_by', 'category_id', 'featured_media_id', 'seo_image_id',
    'title', 'slug', 'excerpt', 'content', 'seo_title', 'seo_description',
    'is_indexable', 'is_featured', 'published_at', 'status',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasAuditing, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'excerpt', 'content', 'seo_title', 'seo_description'];

    /**
     * Invalida la caché de consulta pública (`PublicContentCache`, Fase 7,
     * docs/09-rendimiento.md §6) al guardar o borrar — mismo criterio que `Page`.
     */
    protected static function booted(): void
    {
        static::saved(function (self $post) {
            PublicContentCache::forgetPostSlug($post->slug);
            PublicContentCache::forgetPostSlug($post->getOriginal('slug'));
        });

        static::deleted(function (self $post) {
            PublicContentCache::forgetPostSlug($post->slug);
        });
    }

    protected function casts(): array
    {
        return [
            'is_indexable' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }
}
