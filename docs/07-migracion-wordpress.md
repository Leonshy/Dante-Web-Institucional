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

**Decisión (pregunta abierta #22, resuelta 2026-08-25):** reescribir como introducción
breve arriba de cada página nueva, en vez de descartar o migrar aparte. Al limpiar el
Divi/HTML de las 3 con `WpHtmlCleaner` se confirmó que el "contenido" real detrás del
conteo de palabras era sobre todo ruido de atributos del constructor visual, no texto
editorial:

- **`/contacto/`** (revisado a fondo el 2026-08-25, ver pregunta #19 en
  `docs/01-analisis-descubrimiento.md` §E): el cuerpo real (sin Divi) era una lista de
  contacto estructurada por sede/área — Asunción (académico/administrativo), Fernando de la
  Mora (académico/administrativo) e Instituto de Lengua Italiana — con email y teléfono de
  cada una. Búsqueda exhaustiva en el dump (contenido de la página, `postmeta`, `options` —
  widgets y theme mods —, y las plantillas de header/footer del Theme Builder de Divi)
  confirmó que **no existe dirección física ni horario de atención en ningún lado** del
  WordPress viejo — esos dos siguen `[COMPLETAR CON DATO REAL DE MIGRACIÓN]`, genuinamente
  pendientes del cliente. Los emails/teléfonos sí se cargaron en `/contacto` y en el pie de
  página, con dominio `dante.edu.py` — el dump traía `dante.webparaguay.com` (dominio del
  proveedor), pero el cliente confirmó 2026-08-25 que era solo el entorno de desarrollo.
- **`/descarga-de-documentos/`**: el cuerpo real no tenía texto además del título; la bajada
  que ya traía `resources/views/documents/index.blade.php` la cubre por completo, sin cambios.
- **`/galeria/`**: el cuerpo real eran solo títulos de sub-álbumes (ej. "Graduación 2023"),
  ya representados por los álbumes migrados como `Gallery`; se agregó una bajada corta en
  `resources/views/galleries/index.blade.php` (no tenía ninguna).

## Enlaces internos sin redirección conocida

- `/v2/el-instituto-de-lengua-y-cultura/certificacion-internacional` — **corregido
  2026-08-25**: era un enlace absoluto al dominio de staging viejo dentro del bloque de
  texto de `oferta-educativa/cursos-de-italiano`, apuntaba al contenido que hoy es
  `institucion/certificacion-internacional`. Reescrito a esa URL nueva.

## Imágenes sin migrar referenciadas en el contenido

Ninguna — todas las imágenes referenciadas en el contenido migrado se encontraron y migraron.


## Metadatos SEO

El WordPress viejo **no tenía ningún plugin de SEO instalado** (sin claves Yoast ni
RankMath en `wp_postmeta`) — no hay nada que migrar en este punto. Los títulos y
descripciones SEO reales ya están escritos en `docs/03-copywriting.md` §3 (Fase 2) y se
cargan manualmente desde el panel al publicar cada página.

## Verificación

Revisión manual hecha el 2026-08-25 sobre una muestra de 6 páginas (de 16, 37%) y las 3
noticias (3 de 3, 100%) — bien por encima del mínimo del 10%:

- Páginas revisadas: `institucion/historia`, `institucion/sociedad-dante-alighieri`,
  `vida-escolar/biblioteca`, `admisiones/pre-inscripcion-fernando-de-la-mora`,
  `institucion/certificacion-internacional`, `oferta-educativa/cursos-de-italiano`
- Sin rastros de shortcodes Divi, artefactos de Word o enlaces al dominio viejo en el HTML
  renderizado de la muestra
- Todas las imágenes de la muestra cargan (200) — ninguna rota
- Todos los enlaces internos de la muestra resuelven — se encontró y corrigió un bug real:
  el breadcrumb de una página hija enlazaba a su página padre aunque esa página padre
  estuviera en borrador (`institucion`, `oferta-educativa`, `vida-escolar` son páginas de
  sección todavía sin contenido propio), dando 404 al público. Corregido en
  `App\Http\Controllers\PageController::breadcrumbsFor()` — ya no enlaza a un padre no
  publicado, test de regresión agregado (`tests/Feature/PageControllerTest.php`)
- El enlace absoluto roto al dominio de staging (ver arriba) también se detectó y corrigió
  en esta pasada
