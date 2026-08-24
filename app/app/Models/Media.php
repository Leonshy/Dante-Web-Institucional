<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Database\Factories\MediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Biblioteca central de medios — patrón de IPG (docs/01 §A.4), reforzado con
 * reprocesamiento de imágenes y sanitización de SVG (App\Services\Media\MediaUploadService).
 *
 * @property array<string, string>|null $conversions
 */
#[Fillable([
    'user_id', 'name', 'file_name', 'mime_type', 'path', 'disk', 'size', 'type',
    'alt', 'title', 'caption', 'folder', 'conversions', 'svg_sanitized',
])]
class Media extends Model
{
    /** @use HasFactory<MediaFactory> */
    use HasAuditing, HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'conversions' => 'array',
            'svg_sanitized' => 'boolean',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function conversionUrl(string $key): ?string
    {
        $path = ($this->conversions ?? [])[$key] ?? null;

        return $path !== null ? Storage::disk($this->disk)->url($path) : null;
    }
}
