<?php

use App\Http\Middleware\HandleRedirects;
use App\Http\Middleware\PublicMaintenanceMode;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Honeypot\ProtectAgainstSpam;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global: corre en toda respuesta HTTP, panel incluido (Fase 8,
        // docs/10-seguridad.md §1) — no solo en el grupo `web`.
        $middleware->append(SecurityHeaders::class);

        $middleware->web(prepend: [
            PublicMaintenanceMode::class,
        ]);

        // SetLocale necesita la sesión ya iniciada (`StartSession`, que
        // corre dentro del propio grupo `web`) — por eso va con `append`,
        // no `prepend` como PublicMaintenanceMode (que no toca la sesión).
        $middleware->web(append: [
            SetLocale::class,
            HandleRedirects::class,
        ]);

        $middleware->alias([
            'honeypot' => ProtectAgainstSpam::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
