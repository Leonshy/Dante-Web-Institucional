<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

/**
 * Caché de **consultas** (no de respuesta HTTP completa) de las páginas públicas
 * más leídas — Fase 7, docs/09-rendimiento.md §6.
 *
 * Decisión documentada: se descartó cachear la respuesta HTTP completa (vista
 * renderizada) porque el constructor de bloques de `Page` permite insertar un
 * bloque de formulario en **cualquier** página institucional (Fase 3), y ese
 * formulario lleva un token CSRF que Blade genera fresco en cada request. Una
 * caché de vista completa serviría el mismo token a todas las visitas hasta
 * que expire, rompiendo el envío del formulario con un 419 para todos menos
 * el primer visitante. Cacheando solo el resultado de la consulta (el modelo
 * `Page`/`Post`, no el HTML), el `@csrf` de Blade se sigue generando en cada
 * respuesta — cero riesgo, mismo beneficio de no pegarle a la base en cada
 * lectura de una página que casi nunca cambia.
 *
 * Compatible con los drivers `file`/`database` (sin Redis confirmado en Plesk,
 * CLAUDE.md §3): sin tags, invalidación por clave explícita desde los propios
 * modelos (`Page`/`Post`, evento `saved`/`deleted`) — ver `booted()` de cada uno.
 */
class PublicContentCache
{
    private const TTL_SECONDS = 3600;

    /**
     * A propósito **no** usa `Cache::remember()` cuando el resultado es `null`:
     * cachear un "no encontrado" dejaría un 404 falso hasta que expire el TTL
     * si se publica una página nueva con ese slug exacto justo después de que
     * alguien pidió esa URL (no hay modelo todavía para disparar la
     * invalidación por evento). Solo se cachea un resultado real.
     */
    public static function rememberPageBySlug(string $slug, \Closure $resolver): mixed
    {
        return self::rememberIfNotNull(self::pageKey($slug), $resolver);
    }

    public static function rememberPostBySlug(string $slug, \Closure $resolver): mixed
    {
        return self::rememberIfNotNull(self::postKey($slug), $resolver);
    }

    private static function rememberIfNotNull(string $key, \Closure $resolver): mixed
    {
        $cached = Cache::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $value = $resolver();

        if ($value !== null) {
            Cache::put($key, $value, self::TTL_SECONDS);
        }

        return $value;
    }

    public static function forgetPageSlug(?string $slug): void
    {
        if ($slug !== null) {
            Cache::forget(self::pageKey($slug));
        }
    }

    public static function forgetPostSlug(?string $slug): void
    {
        if ($slug !== null) {
            Cache::forget(self::postKey($slug));
        }
    }

    // Sin locale en la clave a propósito: el modelo cacheado trae las
    // traducciones de ambos idiomas en sus columnas JSON (ADR-002), la vista
    // resuelve el idioma activo con `getTranslation()` sobre el mismo objeto.
    private static function pageKey(string $slug): string
    {
        return 'public:page:'.$slug;
    }

    private static function postKey(string $slug): string
    {
        return 'public:post:'.$slug;
    }
}
