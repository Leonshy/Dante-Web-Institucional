<?php

namespace App\Http\Middleware;

use App\Models\Redirect as RedirectModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de redirecciones 301 — no existe en IPG (docs/01 §A.4), crítico
 * para Dante por la migración desde WordPress. Va antes del catch-all de
 * páginas públicas (docs/05-backend-modelo-datos.md §3).
 */
class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.trim($request->path(), '/');

        $redirect = RedirectModel::query()
            ->where('from_path', $path)
            ->where('is_active', true)
            ->first();

        // Nunca redirigir una ruta a sí misma — algunas filas del mapa 301
        // importado en la Fase 3 quedaron con from_path === to_path (URLs que
        // no cambiaron de verdad), lo que sin esta guarda produce un loop de
        // redirección infinito (encontrado en /contacto y /noticias al migrar
        // contenido real en la Fase 5).
        if ($redirect && $redirect->to_path === $path) {
            $redirect = null;
        }

        if ($redirect) {
            $redirect->registerHit();

            return redirect($redirect->to_path, $redirect->status_code);
        }

        return $next($request);
    }
}
