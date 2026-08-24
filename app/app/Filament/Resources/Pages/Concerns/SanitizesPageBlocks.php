<?php

namespace App\Filament\Resources\Pages\Concerns;

use App\Services\Html\HtmlSanitizer;

/**
 * Nunca confiar en el HTML que llega del editor enriquecido, aunque venga de
 * un usuario del panel ya autenticado (docs/05-backend-modelo-datos.md §5).
 * Sanitiza recursivamente cualquier campo `content`/`text` dentro de los
 * bloques de la página antes de guardar.
 */
trait SanitizesPageBlocks
{
    protected function sanitizeBlocks(array $data): array
    {
        if (! isset($data['blocks']) || ! is_array($data['blocks'])) {
            return $data;
        }

        $sanitizer = app(HtmlSanitizer::class);

        foreach ($data['blocks'] as $blockKey => $block) {
            if (! isset($block['data']) || ! is_array($block['data'])) {
                continue;
            }

            $data['blocks'][$blockKey]['data'] = $this->sanitizeBlockFields($block['data'], $sanitizer);
        }

        return $data;
    }

    /**
     * Recorre recursivamente los campos de un bloque (incluidos los repetidores,
     * ej. `faq`/`testimonios`) sanitizando cualquier campo `content`/`text`/`answer`
     * bilingüe (`{"es": "...", "it": "..."}`) que encuentre en el camino.
     */
    private function sanitizeBlockFields(array $fields, HtmlSanitizer $sanitizer): array
    {
        $richTextFields = ['content', 'text', 'answer'];

        foreach ($fields as $key => $value) {
            if (! is_array($value)) {
                continue;
            }

            if (in_array($key, $richTextFields, true) && $this->isLocalizedString($value)) {
                foreach ($value as $locale => $html) {
                    $fields[$key][$locale] = $sanitizer->clean($html);
                }

                continue;
            }

            $fields[$key] = $this->sanitizeBlockFields($value, $sanitizer);
        }

        return $fields;
    }

    private function isLocalizedString(array $value): bool
    {
        return ! empty($value) && collect($value)->every(fn ($v) => is_string($v));
    }
}
