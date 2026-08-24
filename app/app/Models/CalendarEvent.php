<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

#[Fillable([
    'created_by', 'related_post_id', 'related_announcement_id', 'title', 'description',
    'starts_at', 'ends_at', 'all_day', 'level', 'status',
])]
class CalendarEvent extends Model
{
    /** @use HasFactory<CalendarEventFactory> */
    use HasAuditing, HasFactory, HasTranslations;

    public array $translatable = ['title', 'description'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
        ];
    }
}
