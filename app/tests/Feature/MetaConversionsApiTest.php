<?php

use App\Models\IntegrationSetting;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    SiteSetting::query()->create(['key' => 'form_notification_email', 'value' => 'contacto@dante.edu.py', 'group' => 'formularios']);
});

it('no manda el evento Lead a Meta si el visitante no aceptó cookies de marketing', function () {
    IntegrationSetting::current()->update([
        'meta_enabled' => true, 'meta_pixel_id' => '123456789', 'meta_capi_access_token' => 'test-token',
    ]);
    Http::fake();

    $this->post('/contacto', [
        'name' => 'María López',
        'email' => 'maria@example.com',
        'message' => 'Consulta',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    Http::assertNothingSent();
});

it('manda el evento Lead a Meta Conversions API si el visitante aceptó cookies de marketing', function () {
    IntegrationSetting::current()->update([
        'meta_enabled' => true, 'meta_pixel_id' => '123456789', 'meta_capi_access_token' => 'test-token',
    ]);
    Http::fake([
        'graph.facebook.com/*' => Http::response(['events_received' => 1], 200),
    ]);

    $this->withCookie('dante_consent_marketing', '1')->post('/contacto', [
        'name' => 'María López',
        'email' => 'maria@example.com',
        'message' => 'Consulta',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'graph.facebook.com')
            && $request['data'][0]['event_name'] === 'Lead'
            && ! empty($request['data'][0]['event_id']);
    });
});

it('sin token de acceso configurado no manda ninguna llamada, aunque haya consentimiento', function () {
    IntegrationSetting::current()->update(['meta_enabled' => true, 'meta_pixel_id' => '123456789', 'meta_capi_access_token' => null]);
    Http::fake();

    $this->withCookie('dante_consent_marketing', '1')->post('/contacto', [
        'name' => 'María López',
        'email' => 'maria@example.com',
        'message' => 'Consulta',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    Http::assertNothingSent();
});

it('con la integración apagada desde el panel no manda ninguna llamada, aunque haya token y consentimiento', function () {
    IntegrationSetting::current()->update([
        'meta_enabled' => false, 'meta_pixel_id' => '123456789', 'meta_capi_access_token' => 'test-token',
    ]);
    Http::fake();

    $this->withCookie('dante_consent_marketing', '1')->post('/contacto', [
        'name' => 'María López',
        'email' => 'maria@example.com',
        'message' => 'Consulta',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    Http::assertNothingSent();
});
