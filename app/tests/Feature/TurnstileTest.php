<?php

use App\Models\IntegrationSetting;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    SiteSetting::query()->create([
        'key' => 'form_notification_email',
        'value' => 'contacto@dante.edu.py',
        'group' => 'formularios',
    ]);
});

function contactPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'María López',
        'email' => 'maria@example.com',
        'message' => 'Quisiera más información sobre admisiones.',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ], $overrides);
}

function enableTurnstile(): void
{
    IntegrationSetting::current()->update([
        'turnstile_enabled' => true,
        'turnstile_site_key' => 'test-site-key',
        'turnstile_secret_key' => 'test-secret',
    ]);
}

it('no exige turnstile si no hay claves configuradas (entorno sin Cloudflare todavía)', function () {
    IntegrationSetting::current()->update(['turnstile_enabled' => false]);

    $this->post('/contacto', contactPayload())->assertRedirect();
});

it('no exige turnstile si está apagado desde el panel aunque las claves estén cargadas', function () {
    IntegrationSetting::current()->update([
        'turnstile_enabled' => false,
        'turnstile_site_key' => 'test-site-key',
        'turnstile_secret_key' => 'test-secret',
    ]);

    $this->post('/contacto', contactPayload())->assertRedirect();
});

it('rechaza el envío si turnstile está habilitado y la verificación falla', function () {
    enableTurnstile();

    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false], 200),
    ]);

    $response = $this->post('/contacto', contactPayload(['cf-turnstile-response' => 'token-invalido']));

    $response->assertSessionHasErrors(['cf-turnstile-response']);
});

it('acepta el envío si turnstile está habilitado y la verificación es exitosa', function () {
    enableTurnstile();

    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
    ]);

    $response = $this->post('/contacto', contactPayload(['cf-turnstile-response' => 'token-valido']));

    $response->assertSessionDoesntHaveErrors(['cf-turnstile-response']);
});

it('rechaza el envío si turnstile está habilitado pero no llega ningún token', function () {
    enableTurnstile();

    $response = $this->post('/contacto', contactPayload());

    $response->assertSessionHasErrors(['cf-turnstile-response']);
});
