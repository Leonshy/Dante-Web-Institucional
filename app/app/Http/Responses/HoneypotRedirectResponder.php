<?php

namespace App\Http\Responses;

use Closure;
use Illuminate\Http\Request;
use Spatie\Honeypot\SpamResponder\SpamResponder;

/**
 * Reemplaza el `BlankPageResponder` por defecto de `spatie/laravel-honeypot`
 * (Fase 9, hallazgo real: una página en blanco sin ningún texto deja a un
 * visitante real que disparó el honeypot por error — ej. autocompletado muy
 * rápido del navegador — sin ninguna pista de qué pasó ni qué hacer). Vuelve
 * al formulario con un mensaje claro, igual que cualquier otro error de
 * validación del sitio.
 */
class HoneypotRedirectResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next)
    {
        return redirect()
            ->back()
            ->withInput($request->except(['my_name', 'valid_from']))
            ->with('form_error', 'No pudimos procesar el envío. Por favor, esperá unos segundos y volvé a intentar.');
    }
}
