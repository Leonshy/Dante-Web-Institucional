<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad HTTP — Fase 8 (docs/10-seguridad.md §1). No existían
 * ninguna antes de esta fase (verificado con `curl -I` contra dante-web.test).
 *
 * CSP en modo de aplicación (no report-only): el sitio no carga scripts de
 * terceros de forma incondicional (Fase 6, banner de consentimiento), así que
 * la lista de orígenes es acotada desde el día uno. Los IDs reales de GA4/GTM/
 * Meta se cargan solo tras consentimiento y desde dominios fijos conocidos.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            // Alpine/Livewire necesitan 'unsafe-eval'/'unsafe-inline' — Alpine evalúa
            // las expresiones de `x-data`/`@click`/etc. con `new Function()` internamente,
            // con o sin Livewire de por medio. GTM/Meta Pixel se cargan bajo demanda tras
            // consentimiento (Fase 6) desde sus dominios fijos.
            //
            // Hallazgo real de Fase 9 (QA): el comentario de arriba ya decía que hacía
            // falta 'unsafe-eval', pero la directiva real solo tenía 'unsafe-inline' —
            // un desajuste entre la intención documentada en Fase 8 y el código. Efecto
            // concreto, reproducido con Playwright: `window.Alpine` llegaba a existir
            // pero cada expresión (`x-data`, `@click`) tiraba
            // "Evaluating a string as JavaScript violates ... 'unsafe-eval'" y quedaba
            // completamente inerte — el menú móvil no abría en NINGUNA plantilla pública
            // (con o sin Livewire), ni el acordeón, tabs, galería, hero-slider ni el
            // banner de cookies. Corregido agregando el token que ya decía el comentario.
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://connect.facebook.net https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline'",
            "connect-src 'self' https://www.google-analytics.com https://analytics.google.com",
            "frame-src 'self' https://www.googletagmanager.com https://challenges.cloudflare.com https://www.google.com",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        // HSTS solo tiene sentido bajo HTTPS real — activarlo en HTTP local
        // rompería el propio entorno de desarrollo (Herd sirve por HTTP).
        if ($request->isSecure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // `X-Powered-By` la agrega PHP-FPM a nivel de SAPI antes de que
        // Symfony/Laravel envíen su propio `HeaderBag` — quitarla solo del
        // objeto Response (`$response->headers->remove(...)`) no alcanza,
        // porque Symfony no vuelve a limpiar cabeceras que no están en su
        // bolsa. Hay que usar `header_remove()` de PHP directamente. Esto es
        // solo una segunda capa: la forma correcta y definitiva es
        // `expose_php = Off` en el `php.ini` del Plesk real (Fase 10,
        // docs/10-seguridad.md §1/§5) — acá no hay control sobre ese archivo.
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }
        $response->headers->remove('Server');

        return $response;
    }
}
