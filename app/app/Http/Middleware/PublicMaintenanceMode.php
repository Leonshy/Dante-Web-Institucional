<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mantenimiento del sitio público, activable desde Configuración
 * (Fase 10, pedido del cliente) — a diferencia de `php artisan down`
 * (Fase 10, docs/12-deploy-plesk.md), esto es un interruptor de contenido:
 * solo corre en el grupo `web` (nunca en el panel, registrado aparte en
 * `AdminPanelProvider` con su propio stack de middleware), así que el
 * cliente puede seguir editando aunque el sitio público esté "cerrado".
 *
 * Hallazgo real (Fase 10): el endpoint de actualización de Livewire
 * (`Route::post(...)->middleware(['web', ...])`, registrado por el propio
 * paquete) también corre en el grupo `web` — panel incluido, ya que el
 * panel usa el mismo endpoint compartido. Sin este chequeo, activar el
 * mantenimiento rompía cualquier acción del panel a mitad de camino
 * (la respuesta 503 le llegaba a Livewire en vez del JSON que esperaba).
 * La ruta exacta trae un hash de versión y puede cambiar, así que el
 * chequeo confiable es la cabecera `X-Livewire` que el propio cliente JS
 * de Livewire siempre manda — nunca la ve un visitante real navegando.
 */
class PublicMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasHeader('X-Livewire')) {
            return $next($request);
        }

        if (! SiteSetting::maintenanceModeEnabled()) {
            return $next($request);
        }

        return response()->view('maintenance', [], 503)->header('Retry-After', '3600');
    }
}
