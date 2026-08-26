<?php

namespace App\Providers;

use App\Policies\ActivityLogPolicy;
use enshrined\svgSanitize\Sanitizer as SvgSanitizer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SvgSanitizer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityLogPolicy::class);

        $this->configurePasswordPolicy();
        $this->guardAgainstInsecureProductionConfig();
    }

    /**
     * Política de contraseñas — Fase 8 (docs/10-seguridad.md §2): mínimo 12
     * caracteres, mayúscula, número y verificación contra bases de
     * filtraciones conocidas (Have I Been Pwned vía `uncompromised()`).
     * `uncompromised()` hace una llamada de red real — se desactiva en
     * `testing` para no depender de internet en la suite de Pest ni en CI.
     */
    private function configurePasswordPolicy(): void
    {
        Password::defaults(function () {
            $rule = Password::min(12)->mixedCase()->numbers();

            return $this->app->environment('testing') ? $rule : $rule->uncompromised();
        });
    }

    /**
     * Segunda línea de defensa, no solo documentación: si alguna vez se
     * despliega con `APP_ENV=production` y `APP_DEBUG` quedó mal configurado
     * (por copiar un `.env` de desarrollo sin revisar, como pasó de origen
     * con el WordPress comprometido — CLAUDE.md §2), lo dejamos en el log
     * como un error crítico e inconfundible en vez de fallar en silencio.
     *
     * El 2FA ya no tiene un guardia acá: desde ADR-003 es opt-in por usuario
     * (nunca obligatorio a nivel de aplicación), así que no hay un estado
     * "mal configurado" que detectar — la alerta persistente del panel
     * (`AdminPanelProvider`) es la presión, no un guardia de arranque.
     */
    private function guardAgainstInsecureProductionConfig(): void
    {
        if (! $this->app->environment('production')) {
            return;
        }

        if (config('app.debug') === true) {
            Log::critical('SEGURIDAD: APP_DEBUG=true en producción. Corregir de inmediato en .env.');
        }
    }
}
