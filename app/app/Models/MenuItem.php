<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'menu_id', 'parent_id', 'label', 'url', 'linkable_type', 'linkable_id',
    'sort_order', 'open_in_new_tab', 'is_active',
])]
class MenuItem extends Model
{
    use HasAuditing, HasFactory, HasTranslations;

    public array $translatable = ['label'];

    protected function casts(): array
    {
        return [
            'open_in_new_tab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function resolvedUrl(): string
    {
        if ($this->linkable_type === Page::class && $this->linkable instanceof Page) {
            return '/'.$this->linkable->slug;
        }

        if ($this->linkable_type === Post::class && $this->linkable instanceof Post) {
            return '/noticias/'.$this->linkable->slug;
        }

        return $this->url ?? '#';
    }
}
