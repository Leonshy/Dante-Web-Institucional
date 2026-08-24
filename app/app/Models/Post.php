<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'created_by', 'updated_by', 'category_id', 'featured_media_id', 'seo_image_id',
    'title', 'slug', 'excerpt', 'content', 'seo_title', 'seo_description',
    'is_indexable', 'is_featured', 'published_at', 'status',
])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasAuditing, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'excerpt', 'content', 'seo_title', 'seo_description'];

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

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }
}
