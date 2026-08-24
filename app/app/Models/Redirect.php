<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['from_path', 'to_path', 'status_code', 'hits', 'last_hit_at', 'is_active'])]
class Redirect extends Model
{
    use HasAuditing, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
        ];
    }

    public function registerHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }
}
