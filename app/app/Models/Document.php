<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'wp_legacy_id', 'created_by', 'category_id', 'media_id', 'title', 'description',
    'site', 'published_at', 'is_current', 'status',
])]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasAuditing, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return [
            'is_current' => 'boolean',
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
    public function file(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
