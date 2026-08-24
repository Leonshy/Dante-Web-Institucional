<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

#[Fillable(['created_by', 'title', 'description', 'event_date', 'site', 'status'])]
class Gallery extends Model
{
    use HasAuditing, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
        ];
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'gallery_media')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }
}
