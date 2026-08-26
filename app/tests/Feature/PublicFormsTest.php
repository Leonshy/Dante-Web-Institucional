<?php

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use App\Notifications\NewFormSubmissionNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    SiteSetting::query()->create([
        'key' => 'form_notification_email',
        'value' => 'contacto@dante.edu.py',
        'group' => 'formularios',
    ]);
});

it('guarda un envío de contacto válido y notifica por mail', function () {
    Notification::fake();

    $response = $this->post('/contacto', [
        'name' => 'María López',
        'email' => 'maria@example.com',
        'phone' => '0981123456',
        'message' => 'Quisiera más información sobre admisiones.',
        'my_name' => '', // campo honeypot vacío = humano
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    $response->assertRedirect();

    expect(FormSubmission::query()->where('email', 'maria@example.com')->exists())->toBeTrue();
    Notification::assertSentOnDemand(NewFormSubmissionNotification::class);
});

it('rechaza el envío si el campo honeypot viene relleno (bot), con un mensaje claro y no en blanco', function () {
    $response = $this->post('/contacto', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'spam',
        'my_name' => 'relleno-por-un-bot',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    expect(FormSubmission::query()->where('email', 'bot@example.com')->exists())->toBeFalse();

    // Hallazgo real de Fase 9: el responder por defecto del paquete
    // (`BlankPageResponder`) devuelve una página vacía, sin ningún texto —
    // un visitante real que lo dispara por error (ej. autocompletado muy
    // rápido) se queda sin ninguna pista de qué pasó. Se reemplazó por
    // `HoneypotRedirectResponder`, que vuelve al formulario con un mensaje.
    $response->assertRedirect();
    expect(session('form_error'))->not->toBeEmpty();
});

it('exige nombre, correo y mensaje', function () {
    $response = $this->post('/contacto', [
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'message']);
});

it('limita la cantidad de envíos por minuto', function () {
    $payload = [
        'name' => 'Repetido',
        'email' => 'repetido@example.com',
        'message' => 'hola',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post('/contacto', $payload);
    }

    $response = $this->post('/contacto', $payload);

    $response->assertStatus(429);
});

it('el límite de envíos de formularios no comparte contador con el del buscador (hallazgo real de Fase 9)', function () {
    // `ThrottleRequests::resolveRequestSignature()` arma la clave solo con
    // `dominio|IP` cuando no se le da un prefijo — sin prefijos distintos en
    // `routes/web.php`, agotar el buscador (30/min) también agotaba este
    // límite de formularios (5/hora) y viceversa, aunque un visitante nunca
    // hubiera tocado el otro endpoint. Reproducido con Playwright (dos envíos
    // de formulario devolvían 429) y corregido con `throttle:...,forms` /
    // `throttle:...,search`.
    for ($i = 0; $i < 10; $i++) {
        $this->get('/buscar?q=colegio');
    }

    $response = $this->post('/contacto', [
        'name' => 'Familia sin relación con el buscador',
        'email' => 'sin.relacion@example.com',
        'message' => 'Este envío no debería verse afectado por las búsquedas.',
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    $response->assertRedirect();
    expect(FormSubmission::query()->where('email', 'sin.relacion@example.com')->exists())->toBeTrue();
});
