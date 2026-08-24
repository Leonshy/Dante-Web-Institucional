<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Services\Search\SearchService;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint de solo lectura del buscador interno del sitio. Controller fino:
 * la lógica de búsqueda vive en SearchService (CLAUDE.md §7). La UI pública
 * que consuma esta respuesta llega en la Fase 4.
 */
class SearchController extends Controller
{
    public function index(SearchRequest $request, SearchService $service): JsonResponse
    {
        $results = $service->search($request->term());

        return response()->json([
            'query' => $request->term(),
            'total' => $results->count(),
            'results' => $results->map->toArray()->values(),
        ]);
    }
}
