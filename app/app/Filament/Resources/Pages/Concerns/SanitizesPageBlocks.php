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
        $richTextFields = ['content', 'text'];

        foreach ($data['blocks'] as $blockKey => $block) {
            foreach ($block as $field => $value) {
                if (! in_array($field, $richTextFields, true) || ! is_array($value)) {
                    continue;
                }

                foreach ($value as $locale => $html) {
                    $data['blocks'][$blockKey][$field][$locale] = $sanitizer->clean($html);
                }
            }
        }

        return $data;
    }
}
