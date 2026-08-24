<?php

namespace App\Services\Search;

/**
 * Un resultado del buscador interno, ya resuelto al idioma activo.
 * Value object simple — no hace falta más que esto para el volumen del sitio.
 */
final class SearchResult
{
    public function __construct(
        public readonly string $type,
        public readonly string $typeLabel,
        public readonly string $title,
        public readonly ?string $excerpt,
        public readonly ?string $url,
    ) {}

    /**
     * @return array{type: string, type_label: string, title: string, excerpt: ?string, url: ?string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'type_label' => $this->typeLabel,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'url' => $this->url,
        ];
    }
}
