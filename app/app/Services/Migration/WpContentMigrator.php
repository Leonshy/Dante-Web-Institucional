<?php

namespace App\Services\Migration;

use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Migración de páginas y noticias del WordPress viejo — docs/07-migracion-wordpress.md.
 *
 * Solo migra el contenido que `docs/01-analisis-descubrimiento.md` §C.2 marcó
 * explícitamente como "migrar" (no "revisar" ni "descartar" — esas son
 * decisiones editoriales ya tomadas, no una clasificación que se infiere acá).
 * El slug nuevo de cada página sale de la tabla `redirects` (mapa 301 ya
 * aprobado en la Fase 1), no se inventa.
 *
 * Algunas páginas viejas con destino "migrar" apuntan a una ruta que la Fase 4
 * ya usa para un controller dedicado (`/contacto`, `/documentos`,
 * `/vida-escolar/galeria`) — crear una Page ahí sería inalcanzable (el catch-all
 * de páginas corre después de esas rutas fijas) o pisaría contenido real.
 * Esos casos se excluyen de la migración automática y quedan listados en
 * `docs/01-analisis-descubrimiento.md` §E como pregunta abierta.
 */
class WpContentMigrator
{
    /**
     * Slug viejo (post_name) => sección del menú, derivada del primer
     * segmento del `to_path` en `redirects` (institucion/oferta-educativa/
     * admisiones/vida-escolar). Excluye los 3 casos de colisión con rutas de
     * la Fase 4 (contacto, descarga-de-documentos, galeria/galeria-sede-fndo),
     * `inicio` (la home ya tiene contenido propio de Fase 2), `noticias`
     * (plantilla de listado, no contenido editorial) y `eventos` (era un
     * listado dinámico de Divi por categoría — `et_pb_blog
     * include_categories="10"` — sin ningún párrafo de contenido real que
     * migrar; se descubrió al revisar la página migrada vacía, ver
     * docs/07-migracion-wordpress.md).
     */
    private const PAGES_TO_MIGRATE = [
        'acerca-de-la-sociedad', 'administracion', 'autoridades',
        'biblioteca-irene-borello-de-amodei', 'certificacion-internacional',
        'cursos-de-italiano', 'enlaces-de-interes', 'estatutos-sociales',
        'formulacion-de-pre-inscripcion',
        'formulario-de-pre-inscripcion-sede-fernando-de-la-mora',
        'historia', 'inscripciones-2', 'instituto-de-lengua-y-cultura',
        'mision-vision-objetivos-y-valores', 'quienes-somos',
    ];

    /** Slugs viejos con destino "migrar" que colisionan con una ruta fija de la Fase 4. */
    private const PAGES_COLLIDING = [
        'contacto' => 'colisiona con la ruta fija /contacto (ContactController)',
        'descarga-de-documentos' => 'colisiona con la ruta fija /documentos (DocumentController)',
        'galeria' => 'colisiona con la ruta fija /vida-escolar/galeria (GalleryController)',
    ];

    private const POSTS_TO_MIGRATE = [
        'la-scuola-dante-alighieri-celebra-su-129-aniversario-con-musica-y-arte',
        'mercado-navideno-a-la-italiana-en-asuncion',
        'historico-presidente-de-italia-sergio-mattarella-visita-colegio-dante-alighieri',
    ];

    public function __construct(private readonly WpHtmlCleaner $cleaner) {}

    /**
     * @param  array<string, string>  $imageMap  clave normalizada (WpHtmlCleaner::normalizeImageKey) => URL nueva
     * @param  array<int, int>  $mediaIdMap  ID de adjunto WP => ID de Media nuevo
     * @return array{pages: array<int, array{old_slug: string, new_slug: string, action: string}>, posts: array<int, array{old_slug: string, new_slug: string, action: string}>, colliding: array<int, array{old_slug: string, reason: string, word_count: int}>, unresolved_links: array<int, string>, unresolved_images: array<int, string>}
     */
    public function migrate(array $imageMap, array $mediaIdMap, bool $dryRun = false): array
    {
        $linkMap = Redirect::query()->pluck('to_path', 'from_path')->all();

        $pages = [];
        $posts = [];
        $unresolvedLinks = [];
        $unresolvedImages = [];

        foreach (self::PAGES_TO_MIGRATE as $oldSlug) {
            $result = $this->migratePage($oldSlug, $linkMap, $imageMap, $mediaIdMap, $dryRun);

            if ($result !== null) {
                $pages[] = $result['summary'];
                array_push($unresolvedLinks, ...$result['unresolved_links']);
                array_push($unresolvedImages, ...$result['unresolved_images']);
            }
        }

        foreach (self::POSTS_TO_MIGRATE as $oldSlug) {
            $result = $this->migratePost($oldSlug, $linkMap, $imageMap, $mediaIdMap, $dryRun);

            if ($result !== null) {
                $posts[] = $result['summary'];
                array_push($unresolvedLinks, ...$result['unresolved_links']);
                array_push($unresolvedImages, ...$result['unresolved_images']);
            }
        }

        return [
            'pages' => $pages,
            'posts' => $posts,
            'colliding' => $this->collidingPagesReport(),
            'unresolved_links' => array_values(array_unique($unresolvedLinks)),
            'unresolved_images' => array_values(array_unique($unresolvedImages)),
        ];
    }

    /**
     * @param  array<string, string>  $linkMap
     * @param  array<string, string>  $imageMap
     * @param  array<int, int>  $mediaIdMap
     * @return array{summary: array{old_slug: string, new_slug: string, action: string}, unresolved_links: array<int, string>, unresolved_images: array<int, string>}|null
     */
    private function migratePage(string $oldSlug, array $linkMap, array $imageMap, array $mediaIdMap, bool $dryRun): ?array
    {
        $wp = DB::connection('wp_legacy')->table('posts')
            ->where('post_type', 'page')->where('post_name', $oldSlug)
            ->first();

        if ($wp === null) {
            return null;
        }

        $newPath = $linkMap['/'.$oldSlug] ?? null;

        if ($newPath === null || $newPath === '/') {
            return null;
        }

        $newSlug = ltrim($newPath, '/');
        $siteSection = $this->siteSectionFor($newSlug);
        $cleaned = $this->cleaner->clean($wp->post_content, $linkMap, $imageMap);
        $thumbnailId = $this->thumbnailIdFor((int) $wp->ID);
        $coverMediaId = $thumbnailId !== null ? ($mediaIdMap[$thumbnailId] ?? null) : null;

        // Las páginas armadas en Divi no usan la imagen destacada nativa de
        // WordPress para el fondo de sección — va como atributo del shortcode
        // (`background_image="..."`). Sin esto, ninguna página con hero de
        // fondo migraba portada, aunque el archivo real sí estaba en `uploads/`.
        if ($coverMediaId === null) {
            $coverMediaId = $this->diviBackgroundCoverMediaId((string) $wp->post_content);
        }

        if ($dryRun) {
            return [
                'summary' => ['old_slug' => $oldSlug, 'new_slug' => $newSlug, 'action' => 'dry-run'],
                'unresolved_links' => $cleaned['unresolved_links'],
                'unresolved_images' => $cleaned['unresolved_images'],
            ];
        }

        $attributes = [
            'title' => ['es' => $wp->post_title],
            'slug' => $newSlug,
            'template' => 'default',
            'site_section' => $siteSection,
            'cover_media_id' => $coverMediaId,
            'blocks' => [
                ['type' => 'texto', 'data' => ['content' => ['es' => $cleaned['html']]]],
            ],
            'status' => 'published',
            'published_at' => $wp->post_date,
        ];

        // La Fase 3 ya sembró un árbol de páginas placeholder en estos mismos
        // slugs (PageTreeSeeder) — se completa esa fila con el contenido real
        // en vez de intentar crear una duplicada (choca con el índice único
        // de `slug`). wp_legacy_id se atrasa a la próxima corrida idempotente.
        $existing = Page::query()->where('wp_legacy_id', $wp->ID)->orWhere('slug', $newSlug)->first();
        $wasCreated = $existing === null;

        $page = $existing ?? new Page;
        $page->forceFill($attributes + ['wp_legacy_id' => $wp->ID])->save();

        return [
            'summary' => ['old_slug' => $oldSlug, 'new_slug' => $newSlug, 'action' => $wasCreated ? 'creada' : 'actualizada'],
            'unresolved_links' => $cleaned['unresolved_links'],
            'unresolved_images' => $cleaned['unresolved_images'],
        ];
    }

    /**
     * @param  array<string, string>  $linkMap
     * @param  array<string, string>  $imageMap
     * @param  array<int, int>  $mediaIdMap
     * @return array{summary: array{old_slug: string, new_slug: string, action: string}, unresolved_links: array<int, string>, unresolved_images: array<int, string>}|null
     */
    private function migratePost(string $oldSlug, array $linkMap, array $imageMap, array $mediaIdMap, bool $dryRun): ?array
    {
        $wp = DB::connection('wp_legacy')->table('posts')
            ->where('post_type', 'post')->where('post_name', $oldSlug)
            ->first();

        if ($wp === null) {
            return null;
        }

        $cleaned = $this->cleaner->clean($wp->post_content, $linkMap, $imageMap);
        $excerpt = trim((string) $wp->post_excerpt) !== ''
            ? $wp->post_excerpt
            : Str::limit(trim(strip_tags($cleaned['html'])), 180);

        $isFeatured = DB::connection('wp_legacy')->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tt.term_taxonomy_id', 'tr.term_taxonomy_id')
            ->join('terms as t', 't.term_id', 'tt.term_id')
            ->where('tr.object_id', $wp->ID)->where('tt.taxonomy', 'category')->where('t.name', 'Destacada')
            ->exists();

        $thumbnailId = $this->thumbnailIdFor((int) $wp->ID);
        $featuredMediaId = $thumbnailId !== null ? ($mediaIdMap[$thumbnailId] ?? null) : null;

        if ($dryRun) {
            return [
                'summary' => ['old_slug' => $oldSlug, 'new_slug' => $oldSlug, 'action' => 'dry-run'],
                'unresolved_links' => $cleaned['unresolved_links'],
                'unresolved_images' => $cleaned['unresolved_images'],
            ];
        }

        $post = Post::query()->updateOrCreate(
            ['wp_legacy_id' => $wp->ID],
            [
                'title' => ['es' => $wp->post_title],
                'slug' => $oldSlug,
                'excerpt' => ['es' => $excerpt],
                'content' => ['es' => $cleaned['html']],
                'featured_media_id' => $featuredMediaId,
                'is_featured' => $isFeatured,
                'status' => 'published',
                'published_at' => $wp->post_date,
            ]
        );

        return [
            'summary' => ['old_slug' => $oldSlug, 'new_slug' => $oldSlug, 'action' => $post->wasRecentlyCreated ? 'creada' : 'actualizada'],
            'unresolved_links' => $cleaned['unresolved_links'],
            'unresolved_images' => $cleaned['unresolved_images'],
        ];
    }

    /**
     * Extrae la primera `background_image="..."` de un shortcode Divi
     * (`et_pb_section`/`et_pb_fullwidth_header`) y busca el `Media` ya
     * migrado que corresponde a ese archivo por nombre (sin extensión ni
     * sufijo de tamaño), ya que no hay un ID de adjunto que los vincule.
     */
    private function diviBackgroundCoverMediaId(string $rawContent): ?int
    {
        if (! preg_match('/background_image="([^"]+)"/', $rawContent, $matches)) {
            return null;
        }

        $path = parse_url($matches[1], PHP_URL_PATH);

        if ($path === null) {
            return null;
        }

        $name = pathinfo($path, PATHINFO_FILENAME);
        $name = (string) preg_replace('/-\d+x\d+$/', '', $name);

        return Media::query()->where('name', $name)->value('id');
    }

    private function siteSectionFor(string $newSlug): string
    {
        $first = strtok($newSlug, '/');

        return in_array($first, ['institucion', 'oferta-educativa', 'admisiones', 'vida-escolar'], true)
            ? $first
            : 'general';
    }

    private function thumbnailIdFor(int $postId): ?int
    {
        $value = DB::connection('wp_legacy')->table('postmeta')
            ->where('post_id', $postId)->where('meta_key', '_thumbnail_id')
            ->value('meta_value');

        return $value !== null ? (int) $value : null;
    }

    /** @return array<int, array{old_slug: string, reason: string, word_count: int}> */
    private function collidingPagesReport(): array
    {
        return collect(self::PAGES_COLLIDING)->map(function (string $reason, string $oldSlug) {
            $wp = DB::connection('wp_legacy')->table('posts')
                ->where('post_type', 'page')->where('post_name', $oldSlug)
                ->first();

            return [
                'old_slug' => $oldSlug,
                'reason' => $reason,
                'word_count' => $wp !== null ? str_word_count(strip_tags((string) $wp->post_content)) : 0,
            ];
        })->values()->all();
    }
}
