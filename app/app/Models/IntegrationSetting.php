<?php

namespace App\Models;

use App\Models\Concerns\HasAuditing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * IDs y credenciales de integraciones (GA4/GTM, Meta Pixel + Conversions
 * API, Cloudflare Turnstile) — fila única (singleton, `current()`),
 * administrable desde `App\Filament\Pages\IntegrationSettings`. Cada
 * integración tiene su propio interruptor `*_enabled`: apagarlo detiene el
 * envío/carga de esa integración aunque los IDs/claves sigan guardados,
 * sin borrar nada (pedido explícito del cliente).
 *
 * @property bool $ga_enabled
 * @property string|null $google_analytics_id
 * @property string|null $google_tag_manager_id
 * @property bool $meta_enabled
 * @property string|null $meta_pixel_id
 * @property string|null $meta_capi_access_token
 * @property string|null $meta_capi_test_event_code
 * @property bool $turnstile_enabled
 * @property string|null $turnstile_site_key
 * @property string|null $turnstile_secret_key
 */
#[Fillable([
    'ga_enabled', 'google_analytics_id', 'google_tag_manager_id',
    'meta_enabled', 'meta_pixel_id', 'meta_capi_access_token', 'meta_capi_test_event_code',
    'turnstile_enabled', 'turnstile_site_key', 'turnstile_secret_key',
])]
class IntegrationSetting extends Model
{
    use HasAuditing;

    protected function casts(): array
    {
        return [
            'ga_enabled' => 'boolean',
            'meta_enabled' => 'boolean',
            'turnstile_enabled' => 'boolean',
            // Cast `encrypted`: AES-256 con la `APP_KEY` de la app — estos dos
            // campos son secretos reales, a diferencia de los IDs de arriba.
            'meta_capi_access_token' => 'encrypted',
            'turnstile_secret_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate([]);
    }

    /** GTM solo se inyecta si la integración está activa Y hay un ID cargado. */
    public function googleTagManagerActive(): bool
    {
        return $this->ga_enabled && filled($this->google_tag_manager_id);
    }

    /** El Pixel del cliente solo se inyecta si la integración está activa Y hay un ID cargado. */
    public function metaPixelActive(): bool
    {
        return $this->meta_enabled && filled($this->meta_pixel_id);
    }

    /** Conversions API necesita, además, el token — puede estar activa sin el Pixel de cliente. */
    public function metaConversionsApiActive(): bool
    {
        return $this->meta_enabled && filled($this->meta_pixel_id) && filled($this->meta_capi_access_token);
    }

    public function turnstileActive(): bool
    {
        return $this->turnstile_enabled && filled($this->turnstile_site_key) && filled($this->turnstile_secret_key);
    }
}
