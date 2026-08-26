<?php

it('envía las cabeceras de seguridad HTTP en las respuestas públicas', function () {
    $response = $this->get('/');

    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeaderMissing('X-Powered-By');
    $response->assertHeaderMissing('Server');
});

it('la CSP permite `unsafe-eval` — Alpine.js lo necesita para evaluar sus directivas (hallazgo real de Fase 9)', function () {
    // El comentario del middleware ya decía que Alpine/Livewire necesitan
    // 'unsafe-eval', pero la directiva real solo tenía 'unsafe-inline'. Sin
    // este token, `window.Alpine` llega a existir pero cada expresión
    // (`x-data`, `@click`) tira una violación de CSP y queda inerte: el menú
    // móvil no abre en ninguna plantilla pública, ni el acordeón, tabs,
    // galería, hero-slider ni el banner de cookies — reproducido con
    // Playwright (ver docs/11-qa-testing.md §6).
    $response = $this->get('/');

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)->toContain("'unsafe-eval'")
        ->and($csp)->toContain('script-src');
});
