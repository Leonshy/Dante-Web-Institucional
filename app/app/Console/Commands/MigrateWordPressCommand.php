<?php

namespace App\Console\Commands;

use App\Services\Migration\WpContentMigrator;
use App\Services\Migration\WpMediaMigrator;
use Illuminate\Console\Command;

/**
 * Migración de contenido del WordPress viejo comprometido — Fase 5,
 * docs/07-migracion-wordpress.md. Idempotente: correrlo varias veces no
 * duplica nada (updateOrCreate por wp_legacy_id).
 *
 * Protocolo de seguridad (CLAUDE.md §2): lee de la conexión `wp_legacy`
 * (solo lectura, usuario con GRANT SELECT únicamente), nunca escribe ahí.
 * Los medios pasan por el mismo pipeline de subida que usa el panel (MIME
 * real, SVG sanitizado). El HTML pasa por HtmlSanitizer antes de guardarse,
 * sin excepción, aunque ya se haya "limpiado" antes.
 */
class MigrateWordPressCommand extends Command
{
    protected $signature = 'dante:migrate-wp
        {--uploads= : Ruta a la carpeta uploads/ del insumo (default: _insumos/03-wordpress-actual/uploads)}
        {--dry-run : No escribe nada, solo reporta qué haría}';

    protected $description = 'Migra páginas, noticias y medios reales del WordPress viejo (Fase 5, protocolo de seguridad CLAUDE.md §2)';

    public function handle(WpMediaMigrator $mediaMigrator, WpContentMigrator $contentMigrator): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $uploadsPath = $this->option('uploads') ?: base_path('../_insumos/03-wordpress-actual/uploads');

        $this->info($dryRun ? 'Simulación (--dry-run), no se escribe nada.' : 'Migrando contenido real.');

        $this->info('Migrando medios...');
        $mediaResult = $mediaMigrator->migrate($uploadsPath, $dryRun);
        $this->info("Medios: {$mediaResult['migrated']} migrados, {$mediaResult['already_migrated']} ya migrados, ".count($mediaResult['discarded']).' descartados.');

        $this->info('Migrando páginas y noticias...');
        $contentResult = $contentMigrator->migrate($mediaResult['image_key_map'], $mediaResult['id_map'], $dryRun);
        $this->info(count($contentResult['pages']).' páginas, '.count($contentResult['posts']).' noticias procesadas.');

        if (! $dryRun) {
            $this->writeReport($mediaResult, $contentResult);
        }

        return self::SUCCESS;
    }

    /**
     * @param  array{migrated: int, already_migrated: int, discarded: array<int, array{path: string, reason: string}>}  $mediaResult
     * @param  array{pages: array<int, array{old_slug: string, new_slug: string, action: string}>, posts: array<int, array{old_slug: string, new_slug: string, action: string}>, colliding: array<int, array{old_slug: string, reason: string, word_count: int}>, unresolved_links: array<int, string>, unresolved_images: array<int, string>}  $contentResult
     */
    private function writeReport(array $mediaResult, array $contentResult): void
    {
        $lines = [];
        $lines[] = '# 07 — Migración de contenido de WordPress (Fase 5)';
        $lines[] = '';
        $lines[] = 'Generado automáticamente por `php artisan dante:migrate-wp`. Última corrida: '.now()->toDateTimeString().'.';
        $lines[] = '';
        $lines[] = '## Medios';
        $lines[] = '';
        $lines[] = "- Migrados en esta corrida: {$mediaResult['migrated']}";
        $lines[] = "- Ya migrados (idempotente): {$mediaResult['already_migrated']}";
        $lines[] = '- Descartados: '.count($mediaResult['discarded']);
        $lines[] = '';
        $lines[] = 'Agrupados por motivo — el detalle completo de variantes de tamaño autogeneradas no';
        $lines[] = 'suma información (son cientos, Laravel genera las suyas propias), se muestra una';
        $lines[] = 'muestra de hasta 5 rutas por motivo:';
        $lines[] = '';
        $lines[] = '| Motivo | Cantidad | Ejemplos |';
        $lines[] = '|---|---|---|';

        $byReason = collect($mediaResult['discarded'])->groupBy('reason');

        foreach ($byReason as $reason => $items) {
            $examples = $items->take(5)->map(fn ($item) => "`{$item['path']}`")->implode(', ');
            $lines[] = "| {$reason} | {$items->count()} | {$examples} |";
        }

        $lines[] = '';

        $lines[] = '## Páginas migradas';
        $lines[] = '';
        $lines[] = '| URL vieja | Slug nuevo | Acción |';
        $lines[] = '|---|---|---|';

        foreach ($contentResult['pages'] as $page) {
            $lines[] = "| /{$page['old_slug']}/ | /{$page['new_slug']} | {$page['action']} |";
        }

        $lines[] = '';
        $lines[] = '## Noticias migradas';
        $lines[] = '';
        $lines[] = '| URL vieja | Acción |';
        $lines[] = '|---|---|';

        foreach ($contentResult['posts'] as $post) {
            $lines[] = "| /{$post['old_slug']}/ | {$post['action']} |";
        }

        $lines[] = '';
        $lines[] = '## Requiere decisión manual — colisión con ruta dedicada de la Fase 4';
        $lines[] = '';
        $lines[] = 'Estas páginas tenían destino "migrar" en `docs/01-analisis-descubrimiento.md` §C.2, pero';
        $lines[] = 'su URL nueva ya la sirve un controller dedicado (formulario de contacto, listado de';
        $lines[] = 'documentos, galería) — no se creó ninguna Page para no dejar contenido inalcanzable ni';
        $lines[] = 'pisar la ruta real. El texto viejo sigue en el WordPress legacy si hace falta.';
        $lines[] = '';
        $lines[] = '| Slug viejo | Motivo | Palabras del contenido viejo |';
        $lines[] = '|---|---|---|';

        foreach ($contentResult['colliding'] as $item) {
            $lines[] = "| /{$item['old_slug']}/ | {$item['reason']} | {$item['word_count']} |";
        }

        $lines[] = '';
        $lines[] = '## Enlaces internos sin redirección conocida';
        $lines[] = '';
        $lines[] = count($contentResult['unresolved_links']) > 0
            ? 'Enlaces dentro del contenido migrado que apuntaban a una URL vieja sin fila en `redirects` — quedaron como estaban (referencia potencialmente rota, revisar manualmente):'
            : 'Ninguno — todos los enlaces internos del contenido migrado tenían redirección conocida.';
        $lines[] = '';

        foreach ($contentResult['unresolved_links'] as $link) {
            $lines[] = "- `{$link}`";
        }

        $lines[] = '';
        $lines[] = '## Imágenes sin migrar referenciadas en el contenido';
        $lines[] = '';
        $lines[] = count($contentResult['unresolved_images']) > 0
            ? 'Imágenes que el contenido viejo referenciaba pero no se migraron (descartadas por seguridad o no encontradas en el insumo) — se quitaron del HTML en vez de dejar una referencia rota:'
            : 'Ninguna — todas las imágenes referenciadas en el contenido migrado se encontraron y migraron.';
        $lines[] = '';

        foreach ($contentResult['unresolved_images'] as $image) {
            $lines[] = "- `{$image}`";
        }

        $lines[] = '';
        $lines[] = '## Metadatos SEO';
        $lines[] = '';
        $lines[] = 'El WordPress viejo **no tenía ningún plugin de SEO instalado** (sin claves Yoast ni';
        $lines[] = 'RankMath en `wp_postmeta`) — no hay nada que migrar en este punto. Los títulos y';
        $lines[] = 'descripciones SEO reales ya están escritos en `docs/03-copywriting.md` §3 (Fase 2) y se';
        $lines[] = 'cargan manualmente desde el panel al publicar cada página.';
        $lines[] = '';
        $lines[] = '## Verificación';
        $lines[] = '';
        $lines[] = '- Conteo origen vs. destino, revisión del 10% de una muestra e imágenes/enlaces rotos:';
        $lines[] = '  pendiente de revisión manual sobre esta corrida.';

        file_put_contents(base_path('../docs/07-migracion-wordpress.md'), implode("\n", $lines)."\n");
    }
}
