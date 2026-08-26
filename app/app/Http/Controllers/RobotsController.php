<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * `robots.txt` dinámico (docs/08-seo.md §4) — a propósito NO es un archivo
 * estático en `public/`. Un archivo físico se sirve directo por el
 * webserver sin pasar por Laravel, así que un `Disallow: /` de staging que
 * alguien "olvida sacar" viaja tal cual a producción — el error más caro y
 * más común de una salida a producción (CLAUDE.md). Al depender de
 * `config('dante.seo.block_indexing')` (que a su vez lee `APP_ENV`/
 * `DANTE_BLOCK_INDEXING`), el comportamiento correcto es automático por
 * entorno y no depende de que alguien se acuerde de nada.
 */
class RobotsController extends Controller
{
    public function index(): Response
    {
        $blocked = (bool) config('dante.seo.block_indexing');

        $lines = $blocked
            ? ['User-agent: *', 'Disallow: /']
            : ['User-agent: *', 'Disallow:', '', 'Sitemap: '.route('sitemap.index')];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }
}
