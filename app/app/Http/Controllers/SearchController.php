<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Endpoint de solo lectura del buscador interno del sitio. Controller fino:
 * la lógica de búsqueda vive en SearchService (CLAUDE.md §7).
 *
 * Decisión de Fase 4 (docs/06-frontend.md): esta misma ruta `GET /buscar` negocia
 * contenido en vez de sumar una ruta nueva — es menos invasivo sobre lo que ya se
 * probó en Fase 3. `wantsJson()` (Accept: application/json, usado por los tests
 * existentes vía `getJson()`) conserva exactamente el comportamiento anterior;
 * cualquier otra petición (navegación normal) recibe la vista pública, que
 * delega la búsqueda reactiva a `App\Livewire\SearchPage` reutilizando el mismo
 * SearchService — no se duplica lógica de búsqueda.
 */
class SearchController extends Controller
{
    public function index(Request $request, SearchService $service): JsonResponse|View
    {
        if ($request->wantsJson()) {
            /** @var SearchRequest $searchRequest */
            $searchRequest = app()->make(SearchRequest::class);

            $results = $service->search($searchRequest->term());

            return response()->json([
                'query' => $searchRequest->term(),
                'total' => $results->count(),
                'results' => $results->map->toArray()->values(),
            ]);
        }

        return view('search.index', ['query' => (string) $request->query('q', '')]);
    }
}
