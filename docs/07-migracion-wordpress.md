# 07 — Migración de contenido de WordPress (Fase 5)

Generado automáticamente por `php artisan dante:migrate-wp`. Última corrida: 2026-08-24 20:39:26.

## Medios

- Migrados en esta corrida: 0
- Ya migrados (idempotente): 128
- Descartados: 936

Agrupados por motivo — el detalle completo de variantes de tamaño autogeneradas no
suma información (son cientos, Laravel genera las suyas propias), se muestra una
muestra de hasta 5 rutas por motivo:

| Motivo | Cantidad | Ejemplos |
|---|---|---|
| Extensión fuera de la lista blanca (json) | 2 | `2025/11/google.json`, `2025/10/google.json` |
| Variante de tamaño autogenerada por WordPress — Laravel genera sus propias conversiones responsivas, no hace falta migrarla | 906 | `2024/03/DA-129-placas_OPC-1-150x150.png`, `2024/03/DSC4769-1080x720.jpg`, `2024/03/52549201289_cbb067bb8b_o-1024x683.jpg`, `2024/03/Banner_web-1-opcion-2-1-400x284.png`, `2024/03/dante-Asu-fachadaDante-Asuncion-fachada-9393-2-2048x1365.jpg` |
| Huérfano: no está referenciado por ningún adjunto de la biblioteca de medios de WordPress | 18 | `2024/03/dante-Asu-fachadaDante-Asuncion-fachada-9393-2.jpg`, `2024/03/DSC4769-1.jpg`, `2024/03/52549201289_cbb067bb8b_o.jpg`, `2024/03/DSC4769.jpg`, `2024/02/DSC3480.jpg` |
| Extensión fuera de la lista blanca (md) | 1 | `LEEME.md` |
| Caché/backup del plugin `wp-file-manager-pro`, no es contenido editorial — fuera de alcance | 1 | `wp-file-manager-pro/fm_backup/index.html` |
| Extensión fuera de la lista blanca () | 3 | `2021/01/navigation`, `2018/08/default_upload`, `2016/Expression` |
| Caché/backup del plugin `wpcode`, no es contenido editorial — fuera de alcance | 5 | `wpcode/index.html`, `wpcode/cache/index.html`, `wpcode/cache/docs.json`, `wpcode/cache/library/index.html`, `wpcode/cache/library/snippets.json` |

## Páginas migradas

| URL vieja | Slug nuevo | Acción |
|---|---|---|
| /acerca-de-la-sociedad/ | /institucion/sociedad-dante-alighieri | actualizada |
| /administracion/ | /institucion/administracion | actualizada |
| /autoridades/ | /institucion/autoridades | actualizada |
| /biblioteca-irene-borello-de-amodei/ | /vida-escolar/biblioteca | actualizada |
| /certificacion-internacional/ | /institucion/certificacion-internacional | actualizada |
| /cursos-de-italiano/ | /oferta-educativa/cursos-de-italiano | actualizada |
| /enlaces-de-interes/ | /vida-escolar/enlaces-de-interes | actualizada |
| /estatutos-sociales/ | /institucion/estatutos-sociales | actualizada |
| /eventos/ | /vida-escolar/eventos | actualizada |
| /formulacion-de-pre-inscripcion/ | /admisiones/pre-inscripcion | actualizada |
| /formulario-de-pre-inscripcion-sede-fernando-de-la-mora/ | /admisiones/pre-inscripcion-fernando-de-la-mora | actualizada |
| /historia/ | /institucion/historia | actualizada |
| /inscripciones-2/ | /admisiones | actualizada |
| /instituto-de-lengua-y-cultura/ | /oferta-educativa/instituto-de-lengua-y-cultura | actualizada |
| /mision-vision-objetivos-y-valores/ | /institucion/mision-vision-valores | actualizada |
| /quienes-somos/ | /institucion/quienes-somos | actualizada |

## Noticias migradas

| URL vieja | Acción |
|---|---|
| /la-scuola-dante-alighieri-celebra-su-129-aniversario-con-musica-y-arte/ | actualizada |
| /mercado-navideno-a-la-italiana-en-asuncion/ | actualizada |
| /historico-presidente-de-italia-sergio-mattarella-visita-colegio-dante-alighieri/ | actualizada |

## Requiere decisión manual — colisión con ruta dedicada de la Fase 4

Estas páginas tenían destino "migrar" en `docs/01-analisis-descubrimiento.md` §C.2, pero
su URL nueva ya la sirve un controller dedicado (formulario de contacto, listado de
documentos, galería) — no se creó ninguna Page para no dejar contenido inalcanzable ni
pisar la ruta real. El texto viejo sigue en el WordPress legacy si hace falta.

| Slug viejo | Motivo | Palabras del contenido viejo |
|---|---|---|
| /contacto/ | colisiona con la ruta fija /contacto (ContactController) | 3963 |
| /descarga-de-documentos/ | colisiona con la ruta fija /documentos (DocumentController) | 476 |
| /galeria/ | colisiona con la ruta fija /vida-escolar/galeria (GalleryController) | 646 |

## Enlaces internos sin redirección conocida

Enlaces dentro del contenido migrado que apuntaban a una URL vieja sin fila en `redirects` — quedaron como estaban (referencia potencialmente rota, revisar manualmente):

- `/v2/el-instituto-de-lengua-y-cultura/certificacion-internacional`

## Imágenes sin migrar referenciadas en el contenido

Ninguna — todas las imágenes referenciadas en el contenido migrado se encontraron y migraron.


## Metadatos SEO

El WordPress viejo **no tenía ningún plugin de SEO instalado** (sin claves Yoast ni
RankMath en `wp_postmeta`) — no hay nada que migrar en este punto. Los títulos y
descripciones SEO reales ya están escritos en `docs/03-copywriting.md` §3 (Fase 2) y se
cargan manualmente desde el panel al publicar cada página.

## Verificación

- Conteo origen vs. destino, revisión del 10% de una muestra e imágenes/enlaces rotos:
  pendiente de revisión manual sobre esta corrida.
