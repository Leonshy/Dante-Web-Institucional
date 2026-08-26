<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * El selector ES/IT del header/pie de página existía visualmente desde la
 * Fase 4 pero nunca hizo nada — los botones no tenían ningún enlace ni
 * acción detrás (hallazgo real, Fase 10, reportado por el cliente en
 * staging). Este middleware lee el idioma elegido de la sesión (guardado
 * por `LocaleController::switch()`) y fija el locale de la petición; si el
 * cliente desactivó el italiano desde el panel mientras alguien ya tenía
 * "it" guardado en su sesión, se fuerza de vuelta al español en vez de
 * dejar a esa persona atascada en un idioma que ya no está disponible.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', config('app.locale'));

        if ($locale === 'it' && ! SiteSetting::italianEnabled()) {
            $locale = config('app.locale');
            $request->session()->forget('locale');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
