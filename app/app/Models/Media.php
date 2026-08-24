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
    'wp_legacy_id', 'user_id', 'name', 'file_name', 'mime_type', 'path', 'disk', 'size', 'type',
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

    /**
     * `MediaUploadService` genera las conversiones con el nombre del ancho
     * real (`w400`/`w800`/`w1200`/`w1920`, ver `config('dante.media.responsive_widths')`),
     * no con nombres semánticos — acá se traducen los alias que usa el
     * frontend (`small`/`medium`/`large`) para no tener que acordarse del
     * ancho exacto en cada vista.
     */
    private const CONVERSION_ALIASES = [
        'small' => 'w400',
        'medium' => 'w800',
        'large' => 'w1200',
    ];

    public function conversionUrl(string $key): ?string
    {
        $key = self::CONVERSION_ALIASES[$key] ?? $key;
        $path = ($this->conversions ?? [])[$key] ?? null;

        return $path !== null ? Storage::disk($this->disk)->url($path) : null;
    }
}
