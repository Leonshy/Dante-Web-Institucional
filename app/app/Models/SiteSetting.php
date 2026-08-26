<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Configuración global clave/valor — patrón copiado tal cual de IPG
 * (docs/01-analisis-descubrimiento.md §A.4).
 */
#[Fillable(['key', 'value', 'type', 'group', 'label', 'description'])]
class SiteSetting extends Model
{
    use HasAuditing;

    public const CACHE_PREFIX = 'site_setting:';

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX.$key, 3600, function () use ($key, $default) {
            $setting = self::query()->where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return $setting->type === 'boolean'
                ? (bool) $setting->value
                : $setting->value;
        });
    }

    public static function set(string $key, mixed $value, string $type = 'text', string $group = 'general'): self
    {
        $setting = self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group],
        );

        Cache::forget(self::CACHE_PREFIX.$key);

        return $setting;
    }

    /** El toggle de italiano vive acá, ver ADR-002. */
    public static function italianEnabled(): bool
    {
        return (bool) self::get('italian_enabled', false);
    }

    /**
     * Mantenimiento del sitio público — no toca el panel (Fase 10, pedido del
     * cliente): es un interruptor de contenido, no el `php artisan down` de
     * infraestructura. `App\Http\Middleware\PublicMaintenanceMode` lo lee.
     */
    public static function maintenanceModeEnabled(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }
}
