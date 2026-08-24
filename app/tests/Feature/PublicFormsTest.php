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

it('rechaza el envío si el campo honeypot viene relleno (bot)', function () {
    $response = $this->post('/contacto', [
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'spam',
        'my_name' => 'relleno-por-un-bot',
        'valid_from' => encrypt(now()->subSeconds(5)->timestamp),
    ]);

    expect(FormSubmission::query()->where('email', 'bot@example.com')->exists())->toBeFalse();
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
