<?php

use App\Models\SiteSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fila única (singleton) para IDs y credenciales de integraciones —
 * administrable desde `App\Filament\Pages\IntegrationSettings`, con
 * un interruptor de activo/inactivo por integración (pedido explícito
 * del cliente: todo desde el panel, nada en `.env`).
 *
 * Los secretos (`meta_capi_access_token`, `turnstile_secret_key`) usan el
 * cast `encrypted` de Eloquent (AES-256, misma `APP_KEY`) — nunca quedan en
 * texto plano en la base, a diferencia de un `SiteSetting` genérico.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('ga_enabled')->default(false);
            $table->string('google_analytics_id')->nullable();
            $table->string('google_tag_manager_id')->nullable();

            $table->boolean('meta_enabled')->default(false);
            $table->string('meta_pixel_id')->nullable();
            $table->text('meta_capi_access_token')->nullable();
            $table->string('meta_capi_test_event_code')->nullable();

            $table->boolean('turnstile_enabled')->default(false);
            $table->string('turnstile_site_key')->nullable();
            $table->text('turnstile_secret_key')->nullable();

            $table->timestamps();
        });

        // Migra los IDs no-secretos que ya vivían en `site_settings` (grupo
        // "integraciones", Fase 6) — nunca hubo valores reales del cliente
        // todavía, pero si alguien cargó algo en desarrollo no se pierde.
        $legacy = SiteSetting::query()
            ->whereIn('key', ['google_analytics_id', 'google_tag_manager_id', 'meta_pixel_id'])
            ->pluck('value', 'key');

        if ($legacy->isNotEmpty()) {
            DB::table('integration_settings')->insert([
                'ga_enabled' => filled($legacy->get('google_tag_manager_id')),
                'google_analytics_id' => $legacy->get('google_analytics_id'),
                'google_tag_manager_id' => $legacy->get('google_tag_manager_id'),
                'meta_enabled' => filled($legacy->get('meta_pixel_id')),
                'meta_pixel_id' => $legacy->get('meta_pixel_id'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        SiteSetting::query()
            ->whereIn('key', ['google_analytics_id', 'google_tag_manager_id', 'meta_pixel_id'])
            ->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
