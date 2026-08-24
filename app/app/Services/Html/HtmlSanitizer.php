<?php

namespace App\Services\Html;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Sanitización server-side del HTML del editor enriquecido — lista blanca,
 * nunca lista negra. Ver docs/05-backend-modelo-datos.md §5.
 *
 * IPG no tiene ninguna sanitización (guarda y muestra el HTML de TinyMCE tal
 * cual con `{!! !!}`), lo que es exactamente el tipo de brecha que comprometió
 * el WordPress anterior de Dante (docs/01-analisis-descubrimiento.md §A.4, §C.6).
 * Nunca confiar en el HTML que llega del cliente, aunque venga de un usuario
 * autenticado del panel.
 */
class HtmlSanitizer
{
    public function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('app/htmlpurifier'));
        $config->set('HTML.Allowed', $this->allowedElements());
        $config->set('CSS.AllowedProperties', []); // sin `style` inline
        $config->set('AutoFormat.RemoveEmpty', true);
        $config->set('URI.AllowedSchemes', array_fill_keys(
            config('dante.html_sanitizer.allowed_protocols'),
            true,
        ));
        // Solo imágenes del propio dominio — nunca fuentes externas sin control.
        $config->set('URI.DisableExternalResources', true);
        $config->set('URI.Host', parse_url(config('app.url'), PHP_URL_HOST) ?: null);

        // HTMLPurifier no trae `figure`/`figcaption` (HTML5) en su definición por
        // defecto — se agregan a mano, siguen dentro de la lista blanca de todos modos.
        $definition = $config->getHTMLDefinition(true);

        if ($definition !== null) {
            $definition->addElement('figure', 'Block', 'Optional: (img | Flow), figcaption?', 'Common');
            $definition->addElement('figcaption', 'Inline', 'Optional: Flow', 'Common');
        }

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($html);
    }

    /**
     * HTMLPurifier valida que cada atributo sea válido para ese elemento
     * específico (ej. `href` no existe en `<p>`) — no alcanza con una lista
     * blanca plana de atributos para todas las etiquetas.
     */
    private function allowedElements(): string
    {
        $tags = config('dante.html_sanitizer.allowed_tags');
        $attributesByTag = [
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'title'],
            'table' => ['class'],
            'th' => ['colspan', 'rowspan'],
            'td' => ['colspan', 'rowspan'],
        ];
        $default = ['class'];

        return collect($tags)
            ->map(function (string $tag) use ($attributesByTag, $default) {
                $attrs = implode('|', $attributesByTag[$tag] ?? $default);

                return "{$tag}[{$attrs}]";
            })
            ->implode(',');
    }
}
