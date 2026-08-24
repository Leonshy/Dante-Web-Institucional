<?php

namespace App\Services\Migration;

use App\Services\Html\HtmlSanitizer;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Limpieza del `post_content` de WordPress/Divi antes de guardarlo en el
 * modelo nuevo — docs/07-migracion-wordpress.md §5.
 *
 * El contenido real del sitio viejo está envuelto en shortcodes anidados del
 * constructor visual Divi (`[et_pb_section]...[et_pb_text]<p>texto real</p>
 * [/et_pb_text]...[/et_pb_section]`), con decenas de atributos de layout por
 * shortcode y HTML pegado directo de Word (`<o:p>`, `style` inline, clases del
 * tema). Nada de ese ruido es contenido editorial — se descarta todo y se
 * conserva solo el HTML semántico de adentro.
 *
 * Nunca se confía en el resultado de esta limpieza por sí sola: siempre pasa
 * después por `HtmlSanitizer` (HTMLPurifier, lista blanca real), aunque venga
 * de un sitio que ya se sabe comprometido (CLAUDE.md §2).
 */
class WpHtmlCleaner
{
    /** @var array<int, string> dominios del sitio viejo a reconocer en enlaces/imágenes absolutos */
    private const LEGACY_HOSTS = ['dante.edu.py', 'www.dante.edu.py', 'dante.webparaguay.com'];

    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    /**
     * @param  array<string, string>  $linkMap  ruta vieja normalizada (sin dominio, sin barra final) => ruta nueva absoluta
     * @param  array<string, string>  $imageMap  clave normalizada de imagen (ver `normalizeImageKey`) => URL nueva del medio migrado
     * @return array{html: string, unresolved_links: array<int, string>, unresolved_images: array<int, string>}
     */
    public function clean(?string $rawContent, array $linkMap = [], array $imageMap = []): array
    {
        if ($rawContent === null || trim($rawContent) === '') {
            return ['html' => '', 'unresolved_links' => [], 'unresolved_images' => []];
        }

        $html = $this->stripComments($rawContent);
        $html = $this->stripShortcodes($html);
        $html = $this->stripOfficeArtifacts($html);

        $dom = $this->parseFragment($html);
        $wrapper = $dom->getElementsByTagName('div')->item(0);

        if ($wrapper === null) {
            return ['html' => '', 'unresolved_links' => [], 'unresolved_images' => []];
        }

        ['links' => $unresolvedLinks, 'images' => $unresolvedImages] = $this->cleanNode($wrapper, $dom, $linkMap, $imageMap);
        $this->removeEmptyBlocks($wrapper);

        $inner = '';

        foreach (iterator_to_array($wrapper->childNodes) as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return [
            'html' => $this->sanitizer->clean($inner),
            'unresolved_links' => array_values(array_unique($unresolvedLinks)),
            'unresolved_images' => array_values(array_unique($unresolvedImages)),
        ];
    }

    /**
     * Clave de normalización para emparejar una imagen del contenido viejo
     * (que suele referenciar una variante redimensionada por WordPress, ej.
     * `logo-300x225.jpg`) contra el archivo original migrado (`logo.jpg`).
     */
    public static function normalizeImageKey(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');
        $relativePath = preg_replace('#^wp-content/uploads/#i', '', $relativePath) ?? $relativePath;
        $relativePath = strtolower($relativePath);

        // quita el sufijo de tamaño autogenerado por WordPress (`-300x225` antes de la extensión)
        return (string) preg_replace('/-\d+x\d+(?=\.[a-z0-9]+$)/', '', $relativePath);
    }

    private function stripComments(string $html): string
    {
        return (string) preg_replace('/<!--.*?-->/s', '', $html);
    }

    /**
     * Quita shortcodes de WordPress/Divi (`[et_pb_section ...]`, `[/et_pb_section]`,
     * `[gallery ids="1,2"]`, etc.). El contenido real queda entre las etiquetas,
     * como HTML normal — no se pierde nada al sacar solo los corchetes.
     */
    private function stripShortcodes(string $html): string
    {
        $pattern = '/\[\/?[a-zA-Z][a-zA-Z0-9_\-]*(?:\s(?:[^\[\]]|\[[^\[\]]*\])*)?\/?\]/';

        // dos pasadas: hay shortcodes anidados dentro de atributos de otros
        $html = (string) preg_replace($pattern, '', $html);

        return (string) preg_replace($pattern, '', $html);
    }

    /**
     * Artefactos típicos de contenido pegado desde Microsoft Word
     * (`<o:p>`, namespaces `w:`/`v:`), habituales en este dump.
     */
    private function stripOfficeArtifacts(string $html): string
    {
        return (string) preg_replace('/<\/?[ovw]:[a-zA-Z]+[^>]*>/i', '', $html);
    }

    private function parseFragment(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_NOXMLDECL
        );
        libxml_clear_errors();

        return $dom;
    }

    /**
     * @param  array<string, string>  $linkMap
     * @param  array<string, string>  $imageMap
     * @return array{links: array<int, string>, images: array<int, string>}
     */
    private function cleanNode(DOMNode $node, DOMDocument $dom, array $linkMap, array $imageMap): array
    {
        $unresolvedLinks = [];
        $unresolvedImages = [];

        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if ($tag === 'b') {
                $this->renameTag($child, $dom, 'strong');
            } elseif ($tag === 'i') {
                $this->renameTag($child, $dom, 'em');
            } elseif ($tag === 'h1') {
                // el h1 del contenido pasa a h2 — el h1 real de la página es el título (docs/07 §5)
                $this->renameTag($child, $dom, 'h2');
            }

            $this->stripDisallowedAttributes($child);

            if (strtolower($child->tagName) === 'a') {
                $unresolvedLink = $this->rewriteLink($child, $linkMap);

                if ($unresolvedLink !== null) {
                    $unresolvedLinks[] = $unresolvedLink;
                }
            }

            if (strtolower($child->tagName) === 'img') {
                $unresolvedImage = $this->rewriteImage($child, $imageMap);

                if ($unresolvedImage !== null) {
                    $unresolvedImages[] = $unresolvedImage;
                }
            }

            $childResult = $this->cleanNode($child, $dom, $linkMap, $imageMap);
            array_push($unresolvedLinks, ...$childResult['links']);
            array_push($unresolvedImages, ...$childResult['images']);
        }

        return ['links' => $unresolvedLinks, 'images' => $unresolvedImages];
    }

    private function renameTag(DOMElement $element, DOMDocument $dom, string $newTag): void
    {
        $replacement = $dom->createElement($newTag);

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $replacement->setAttribute($attribute->nodeName, $attribute->nodeValue ?? '');
        }

        foreach (iterator_to_array($element->childNodes) as $child) {
            $replacement->appendChild($child);
        }

        $element->parentNode?->replaceChild($replacement, $element);
    }

    /**
     * Nunca confiar en clases del tema viejo (`et_pb_*`, `wp-block-*`, `alignleft`)
     * ni en estilos en línea — se descartan todos los atributos salvo los
     * mínimos indispensables por elemento.
     */
    private function stripDisallowedAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        $allowed = match ($tag) {
            'a' => ['href', 'title'],
            'img' => ['src', 'alt', 'title'],
            'td', 'th' => ['colspan', 'rowspan'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            if (! in_array($attribute->nodeName, $allowed, true)) {
                $element->removeAttribute($attribute->nodeName);
            }
        }
    }

    /** @param array<string, string> $linkMap */
    private function rewriteLink(DOMElement $anchor, array $linkMap): ?string
    {
        $href = $anchor->getAttribute('href');

        if ($href === '' || str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:') || str_starts_with($href, '#')) {
            return null;
        }

        $parts = parse_url($href);
        $host = $parts['host'] ?? null;
        $path = $parts['path'] ?? $href;

        $isInternal = $host === null || in_array($host, self::LEGACY_HOSTS, true);

        if (! $isInternal) {
            // enlace externo legítimo, se conserva tal cual
            return null;
        }

        // Contenido viejo de Divi usa enlaces relativos (`../historia/`) que
        // asumían una jerarquía de carpetas que el sitio nuevo no tiene (URLs
        // planas por página) — se resuelven quitando los `../` de más antes
        // de buscar la redirección, en vez de dejarlos como referencia rota.
        $path = preg_replace('#^(\.\./)+#', '', $path) ?? $path;

        $normalized = '/'.trim($path, '/');
        $normalized = $normalized === '/' ? '/' : rtrim($normalized, '/');

        if (isset($linkMap[$normalized])) {
            $anchor->setAttribute('href', $linkMap[$normalized]);

            return null;
        }

        if ($normalized === '/' || $normalized === '') {
            $anchor->setAttribute('href', '/');

            return null;
        }

        // No hay redirección conocida para este enlace interno — se deja como
        // referencia rota detectable (ver docs/07 §8) en vez de inventar un destino.
        return $normalized;
    }

    /** @param array<string, string> $imageMap */
    private function rewriteImage(DOMElement $img, array $imageMap): ?string
    {
        $src = $img->getAttribute('src');

        if ($src === '') {
            $img->parentNode?->removeChild($img);

            return null;
        }

        $path = parse_url($src, PHP_URL_PATH) ?: $src;
        $key = self::normalizeImageKey($path);

        if (isset($imageMap[$key])) {
            $img->setAttribute('src', $imageMap[$key]);

            return null;
        }

        // Imagen no migrada (descartada por seguridad o no encontrada en el
        // insumo) — se quita del contenido en vez de dejar una referencia rota
        // a un dominio externo (que el sanitizador igual bloquearía).
        $img->parentNode?->removeChild($img);

        return $path;
    }

    private function removeEmptyBlocks(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $this->removeEmptyBlocks($child);

            $tag = strtolower($child->tagName);
            $hasMedia = $child->getElementsByTagName('img')->length > 0;
            $isEmpty = trim($child->textContent) === '' && ! $hasMedia && $child->childNodes->length === 0;

            if (in_array($tag, ['p', 'div', 'span'], true) && $isEmpty) {
                $child->parentNode?->removeChild($child);
            }
        }
    }
}
