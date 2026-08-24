<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property Carbon|null $valid_until
 */
#[Fillable(['created_by', 'title', 'content', 'published_at', 'valid_until', 'is_pinned', 'audience', 'status'])]
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasAuditing, HasFactory, HasTranslations, SoftDeletes;

    public array $translatable = ['title', 'content'];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'valid_until' => 'date',
            'is_pinned' => 'boolean',
        ];
    }

    public function isVigente(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }
}
