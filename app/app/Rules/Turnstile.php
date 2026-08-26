<?php

namespace App\Rules;

use App\Models\IntegrationSetting;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

/**
 * Cloudflare Turnstile, validado del lado servidor (docs/08-seo.md §6) —
 * el widget del cliente se puede saltar, la única verificación que cuenta
 * es esta llamada a la API de Cloudflare. Se suma al honeypot y al
 * `throttle` ya existentes (Fase 3), nunca lo reemplaza.
 *
 * Implementa `ImplicitRule` (interfaz `passes()`/`message()`, no la más
 * moderna `ValidationRule::validate()`) a propósito: es la única forma de
 * que Laravel siga corriendo esta regla cuando el campo
 * `cf-turnstile-response` viene vacío o directamente ausente del POST —
 * que es exactamente lo que hace un bot que no ejecuta el widget. Con la
 * interfaz moderna (no implícita), un campo ausente se salta la regla
 * entera y el captcha deja de proteger nada.
 */
class Turnstile implements ImplicitRule
{
    private string $errorMessage = 'Completá la verificación anti-robots antes de enviar.';

    public function passes($attribute, $value): bool
    {
        $settings = IntegrationSetting::current();

        if (! $settings->turnstileActive()) {
            // Apagado desde el panel, o sin claves cargadas todavía (pendiente:
            // alta en Cloudflare, docs/01-analisis-descubrimiento.md §E) — no
            // bloquear los formularios por una integración que el cliente
            // decidió no usar todavía.
            return true;
        }

        if (! is_string($value) || trim($value) === '') {
            return false;
        }

        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $settings->turnstile_secret_key,
            'response' => $value,
            'remoteip' => Request::ip(),
        ]);

        if (! $response->successful() || $response->json('success') !== true) {
            $this->errorMessage = 'No pudimos verificar que sos una persona. Intentá de nuevo.';

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->errorMessage;
    }
}
