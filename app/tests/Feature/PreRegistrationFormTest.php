<?php

use App\Models\FormSubmission;
use App\Models\SiteSetting;
use App\Notifications\NewFormSubmissionNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Formulario de pre-inscripción (tarea crítica #1, docs/02 §6) de punta a
 * punta con Pest — el E2E de Playwright (`e2e/critical-tasks.spec.ts`) ya
 * cubre el recorrido visual, esto cubre honeypot/validación/rate limit sin
 * depender de un navegador real, igual que `PublicFormsTest` para Contacto.
 */
beforeEach(function () {
    SiteSetting::query()->create([
        'key' => 'form_notification_email',
        'value' => 'contacto@dante.edu.py',
        'group' => 'formularios',
    ]);
});

function preRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Familia López',
        'email' => 'familia@example.com',
        'phone' => '0981123456',
        'site' => 'asuncion',
        'message' => 'Quisiera pre-inscribir a mi hijo para el próximo año.',
        'my_name' => '', // honeypot vacío = humano
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ], $overrides);
}

it('guarda una pre-inscripción válida y notifica por mail', function () {
    Notification::fake();

    $response = $this->post('/admisiones/pre-inscripcion', preRegistrationPayload());

    $response->assertRedirect();
    expect(FormSubmission::query()->where('email', 'familia@example.com')->exists())->toBeTrue();
    Notification::assertSentOnDemand(NewFormSubmissionNotification::class);
});

it('exige nombre, correo, teléfono y sede', function () {
    $response = $this->post('/admisiones/pre-inscripcion', [
        'my_name' => '',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    $response->assertSessionHasErrors(['name', 'email', 'phone', 'site']);
});

it('rechaza una sede que no existe', function () {
    $response = $this->post('/admisiones/pre-inscripcion', preRegistrationPayload(['site' => 'sede-inventada']));

    $response->assertSessionHasErrors(['site']);
});

it('rechaza el envío si el campo honeypot viene relleno (bot)', function () {
    $this->post('/admisiones/pre-inscripcion', preRegistrationPayload([
        'email' => 'bot@example.com',
        'my_name' => 'relleno-por-un-bot',
    ]));

    expect(FormSubmission::query()->where('email', 'bot@example.com')->exists())->toBeFalse();
});

it('limita la cantidad de envíos por hora', function () {
    $payload = preRegistrationPayload();

    for ($i = 0; $i < 5; $i++) {
        $this->post('/admisiones/pre-inscripcion', $payload);
    }

    $response = $this->post('/admisiones/pre-inscripcion', $payload);

    $response->assertStatus(429);
});
