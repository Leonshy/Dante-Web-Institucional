<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'academic_email', 'administrative_email', 'phone', 'whatsapp',
    'address', 'schedule', 'maps_embed_url', 'sort_order', 'is_active',
])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasAuditing, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
