<?php

namespace App\Services\Integrations;

use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Meta Conversions API — envío del lado servidor (docs/08-seo.md §6). Es lo
 * que sobrevive a los bloqueadores de contenido: el Pixel del navegador
 * puede no llegar a dispararse, esta llamada sale directo desde el servidor.
 *
 * El `event_id` se genera acá y se le devuelve a quien llama para que el
 * mismo id viaje también en el evento del Pixel del cliente (si el usuario
 * dio consentimiento) — así Meta deduplica un único evento en vez de contar
 * dos conversiones por el mismo envío de formulario.
 *
 * Pendiente: el cliente todavía no entregó una cuenta de Meta Business, así
 * que no hay token real cargado (docs/01-analisis-descubrimiento.md §E). Sin
 * token configurado, o con la integración apagada desde el panel
 * (`App\Filament\Pages\IntegrationSettings`), `send()` no hace ninguna
 * llamada de red — queda listo para activarse el día que llegue el dato,
 * sin tocar código.
 */
class MetaConversionsApi
{
    /**
     * @param  array<string, mixed>  $userData  Datos del usuario, sin hashear —
     *                                          acá se hashean (SHA-256) antes de salir, como exige Meta.
     */
    public function send(string $eventName, array $userData, ?Request $request = null): ?string
    {
        $settings = IntegrationSetting::current();

        if (! $settings->metaConversionsApiActive()) {
            return null;
        }

        $pixelId = $settings->meta_pixel_id;
        $accessToken = $settings->meta_capi_access_token;

        $eventId = (string) Str::uuid();

        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => now()->timestamp,
                'event_id' => $eventId,
                'action_source' => 'website',
                'event_source_url' => $request?->fullUrl(),
                'user_data' => $this->hashUserData($userData, $request),
            ]],
        ];

        if ($testCode = $settings->meta_capi_test_event_code) {
            $payload['test_event_code'] = $testCode;
        }

        try {
            Http::asJson()
                ->post("https://graph.facebook.com/v20.0/{$pixelId}/events", [
                    ...$payload,
                    'access_token' => $accessToken,
                ])
                ->throw();
        } catch (\Throwable $e) {
            // Nunca tumbar el envío del formulario porque falló una integración
            // de marketing — se registra y se sigue.
            Log::warning('Meta Conversions API: fallo al enviar evento', [
                'event_name' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }

        return $eventId;
    }

    /**
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    private function hashUserData(array $userData, ?Request $request): array
    {
        $hashed = [];

        foreach (['email', 'phone'] as $field) {
            if (! empty($userData[$field])) {
                $normalized = strtolower(trim((string) $userData[$field]));
                $hashed[$field === 'email' ? 'em' : 'ph'] = [hash('sha256', $normalized)];
            }
        }

        if ($request) {
            $hashed['client_ip_address'] = $request->ip();
            $hashed['client_user_agent'] = $request->userAgent();
        }

        return $hashed;
    }
}
