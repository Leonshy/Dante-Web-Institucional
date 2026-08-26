<?php

namespace App\Models\Concerns;

/**
 * Resuelve recursivamente campos bilingües (`{"es": "...", "it": "..."}`)
 * dentro de estructuras JSON anidadas (bloques de página, slides del hero,
 * tarjetas de cifras), incluidos los que están dentro de repetidores.
 * Extraído de `Page::resolveBlockLocale()` para reutilizarlo en
 * `HomeSetting` sin duplicar la lógica.
 */
trait ResolvesLocaleFields
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function resolveLocaleFields(array $data, string $locale): array
    {
        return collect($data)->map(function ($value) use ($locale) {
            if (! is_array($value)) {
                return $value;
            }

            if (array_key_exists('es', $value) || array_key_exists('it', $value)) {
                return $value[$locale] ?? $value['es'] ?? null;
            }

            return array_is_list($value)
                ? collect($value)->map(fn ($item) => is_array($item) ? self::resolveLocaleFields($item, $locale) : $item)->all()
                : self::resolveLocaleFields($value, $locale);
        })->all();
    }
}
