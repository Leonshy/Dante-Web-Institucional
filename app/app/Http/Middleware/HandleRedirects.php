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

        if ($redirect) {
            $redirect->registerHit();

            return redirect($redirect->to_path, $redirect->status_code);
        }

        return $next($request);
    }
}
