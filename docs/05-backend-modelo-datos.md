# 05 — Backend y modelo de datos (Fase 3)

Estado: **en progreso — vertical completa funcionando (páginas + auth + roles + medios +
formularios + redirecciones), resto del catálogo de contenido con modelo de datos listo y
panel pendiente de pulir. Ver §9 cierre para el detalle exacto de qué falta.**

> Punto de partida obligatorio: el análisis de IPG en `docs/01-analisis-descubrimiento.md` §A.
> No se diseña un modelo de datos nuevo si IPG ya tiene uno que funciona.

Código en `app/` (Laravel 13 instalado el 2026-08-24).

---

## 1. Decisiones de arquitectura

| Decisión | Elección | ADR / nota |
|---|---|---|
| Enfoque del panel admin | **Filament 5**, reutilizando modelo de datos de IPG (`media`, `site_settings`, `categories`, roles) | ADR-001 |
| Multiidioma | **`spatie/laravel-translatable`** — columnas JSON `{"es": "...", "it": "..."}` en cada campo editorial, en vez de tablas de traducción separadas. Motivo: menos joins, encaja natural con Filament (`campo.es` / `campo.it` como nombres de campo con notación de punto), y es el patrón más simple para un equipo chico. El toggle "italiano habilitado" vive en `site_settings` (`SiteSetting::italianEnabled()`) y todos los formularios del panel ocultan la pestaña "Italiano" cuando está apagado | ADR-002. Decisión de implementación, no requiere ADR nuevo |
| Auth del panel | **Sistema nativo de Filament 5** (`multiFactorAuthentication()`, TOTP con `AppAuthentication`), no Fortify | **Se aparta de CLAUDE.md/PLAN.md**, que mencionaban Fortify como paquete de referencia. Motivo: Filament 5 trae MFA (TOTP) nativo, ya integrado con sus páginas de login/perfil — instalar Fortify en paralelo duplicaría rutas de login y complicaría la integración sin aportar nada que Filament no resuelva ya. Fortify se desinstaló del proyecto. 2FA obligatorio (`isRequired`), excepto en entorno `testing` para no bloquear la suite automatizada |
| Roles y permisos | `spatie/laravel-permission`, **4 roles reales** (no 2 binarios como IPG): `administrador`, `editor_general`, `editor_noticias_marketing`, `editor_academico`. Permisos granulares `{módulo}.{acción}` (`view/create/update/delete/publish`) por cada uno de los 14 módulos del panel | `docs/01-analisis-descubrimiento.md` §E pregunta #2 |
| Autorización | Policies de Laravel, una por modelo, todas usando el trait `App\Policies\Concerns\AuthorizesViaPermissions` (permiso granular, no rol hardcodeado) | — |
| Editor de texto enriquecido | `Filament\Forms\Components\RichEditor` (Trix por debajo) en vez de TinyMCE de IPG — es el que trae Filament nativo, evita cargar una librería JS externa duplicada | Se aparta de "el mismo editor que IPG" por practicidad de integración con Filament; el comportamiento (HTML editable) es equivalente |
| Sanitización HTML | `ezyang/htmlpurifier`, lista blanca de etiquetas/atributos configurada en `config/dante.php`, aplicada en `App\Services\Html\HtmlSanitizer` antes de guardar cualquier bloque de texto enriquecido (páginas y noticias) | Ver §5 |
| Gestión de medios | Tabla `media` propia (patrón IPG), subida vía `App\Services\Media\MediaUploadService`: nombre aleatorio (UUID), MIME real verificado con `finfo`, SVG sanitizado con `enshrined/svg-sanitize`, conversión WebP + tamaños responsivos con `intervention/image` v4 | Ver §5 y `docs/01` §A.4 |
| Constructor de bloques | Columna `blocks` (JSON) directamente en `pages`, no una tabla polimórfica `content_blocks` separada. Motivo: Filament `Builder` está diseñado para bindear directo a una columna JSON; una relación morfológica agregaba complejidad de integración (columna de "tipo" de bloque) sin beneficio real para el volumen de contenido de Dante | Catálogo completo en `docs/02` §8; implementados 7 de 16 bloques, ver §9 |
| Estrategia de caché | `database` (Plesk sin Redis asumido), patrón `Cache::remember` de IPG para `site_settings` | — |
| Colas | `database` (sin Supervisor asumido) | — |

---

## 2. Modelo de datos

### Tablas implementadas

#### `users`
Columnas propias + `is_active`, `avatar`, `app_authentication_secret`/`app_authentication_recovery_codes`
(MFA nativo de Filament), soft deletes. `HasRoles` (Spatie), `LogsActivity` (auditoría).

#### `site_settings`
`key` (unique) / `value` / `type` (text|textarea|boolean|json) / `group` / `label` / `description`.
Copiado tal cual de IPG. `SiteSetting::get()/set()` cacheado 1h. `italian_enabled` vive acá
(ADR-002).

#### `media`
`user_id`, `name`, `file_name` (aleatorio), `mime_type`, `path`, `disk` (`media`, fuera de
`public/` servido directo — ver §5), `size`, `type` (image/document/video), `alt`, `title`,
`caption`, `folder`, `conversions` (json), `svg_sanitized` (bool), soft deletes.

#### `categories`
Polimórfica por `type` (news/document/gallery), `name`/`description` traducibles (json),
`slug`, `parent_id` (jerarquía), `sort_order`, `is_active`. CRUD real en el panel (en IPG solo
se cargaba por seeder).

#### `pages`
`parent_id` (jerarquía real, reemplaza el `section` fijo de IPG), `title`/`seo_title`/
`seo_description` traducibles, `slug`, `template`, `site_section` (enum: institucion,
oferta-educativa, admisiones, vida-escolar, general), `site` (sede, nullable), `blocks` (json,
constructor de bloques), `cover_media_id`, `seo_image_id`, `canonical_url`, `is_indexable`,
`status` (draft/published/archived), `published_at`, `sort_order`, `created_by`/`updated_by`,
soft deletes.

#### `posts` (noticias)
`title`/`excerpt`/`content`/`seo_title`/`seo_description` traducibles, `slug`, `category_id`,
`featured_media_id` (obligatoria en el panel), `is_featured`, `published_at`, `status`, soft
deletes, `created_by`/`updated_by`.

#### `documents`
`title`/`description` traducibles, `category_id`, `media_id` (el archivo), `site`,
`published_at`, `is_current` (vigente/vencido), `status`, soft deletes. **Migración y modelo
listos, sin recurso de Filament todavía** (ver §9).

#### `announcements` (comunicados)
`title`/`content` traducibles, `published_at`, `valid_until`, `is_pinned`, `audience`
(toda-la-comunidad/asuncion/fernando-de-la-mora), `status`. **Migración y modelo listos, sin
recurso de Filament todavía.**

#### `calendar_events`
`title`/`description` traducibles, `starts_at`/`ends_at`, `all_day`, `level` (nivel educativo),
`related_post_id`/`related_announcement_id`, `status`. **Migración y modelo listos, sin recurso
de Filament todavía.**

#### `galleries` / `gallery_media`
`title`/`description` traducibles, `event_date`, `site`, `status`; pivote `gallery_media` con
`sort_order`. **Migración y modelo listos, sin recurso de Filament todavía.**

#### `menus` / `menu_items`
`menus`: `key`, `name`. `menu_items`: `menu_id`, `parent_id` (jerarquía), `label` (traducible),
`url` (enlace manual), `linkable_type`/`linkable_id` (morph opcional a Page/Post),
`sort_order`, `open_in_new_tab`, `is_active`. **Migración y modelo listos, sin recurso de
Filament todavía** — ver §9.

#### `redirects`
`from_path` (unique), `to_path`, `status_code` (301 default), `hits`, `last_hit_at`,
`is_active`. CRUD completo en el panel + middleware público `HandleRedirects` + comando
`dante:import-redirects` (carga las 39 filas de `docs/redirecciones-301.csv`, idempotente).

#### `form_submissions`
`type` (contacto/pre_inscripcion), `name`, `email`, `phone`, `site` (solo pre-inscripción),
`message`, `status` (nuevo/leido/respondido/archivado), `ip_address`. Recurso de Filament de
solo gestión (sin crear/borrar masivo desde el panel, se cargan solo por el formulario
público).

#### `roles` / `permissions` / tablas pivote (`spatie/laravel-permission`)
14 módulos × 5 acciones = 70 permisos. Ver `database/seeders/PermissionSeeder.php`.

#### `activity_log` (`spatie/laravel-activitylog`)
Registrado vía trait `App\Models\Concerns\HasAuditing` en todos los modelos editoriales y en
`User`. **Falta el recurso de solo lectura en el panel para consultarlo** — ver §9.

---

## 3. Rutas

### Públicas (implementadas)

| Método | Ruta | Controlador | Nombre |
|---|---|---|---|
| POST | `/contacto` | `FormSubmissionController@contact` | `forms.contact` |
| POST | `/admisiones/pre-inscripcion` | `FormSubmissionController@preRegistration` | `forms.pre-registration` |
| GET | `/{path}` | fallback → `abort(404)` | — |

Middleware `honeypot` (`spatie/laravel-honeypot`) + `throttle:5,1` en ambos POST. El middleware
`App\Http\Middleware\HandleRedirects` corre en el grupo `web` completo, antes del fallback —
resuelve las 301 sin necesitar rutas propias por URL vieja.

**Pendiente (Fase 4):** `/`, `/noticias`, `/noticias/{slug}`, `/buscar`, `/sitemap.xml`, y el
renderizado real de `pages.show` — hoy el catch-all solo devuelve 404 porque el frontend público
no es objeto de esta fase.

### Panel

Prefijo configurable por `.env` → `DANTE_ADMIN_PATH` (nunca `/admin` en producción), leído en
`config/dante.php` y usado por `AdminPanelProvider::path()`.

---

## 4. Panel de administración

### Recursos de Filament implementados

| Recurso | CRUD completo | Multiidioma | SEO | Test Pest |
|---|---|---|---|---|
| Páginas (`PageResource`) | Sí (con jerarquía, bloques, publicación) | Sí | Sí | Sí (`PageResourceTest`, 5 casos incl. sanitización) |
| Noticias (`PostResource`) | Sí | Sí | Campos en el modelo, sin pestaña SEO propia en el form todavía | No — pendiente |
| Medios (`MediaResource`) | Sí (subida vía `MediaUploadService`) | — | Alt text obligatorio en la UI | Sí, a nivel de servicio (`MediaUploadServiceTest`, 4 casos) |
| Categorías (`CategoryResource`) | Sí | Sí | — | No — pendiente |
| Redirecciones (`RedirectResource`) | Sí | — | — | Sí (`RedirectResourceTest`, 2 casos) + `RedirectMiddlewareTest` (3 casos) |
| Configuración (`SiteSettingResource`) | Solo edición (sin crear/borrar — los valores son fijos, cargados por seeder) | — | — | No — pendiente |
| Usuarios (`UserResource`) | Sí (con asignación de rol) | — | — | No — pendiente |
| Formularios recibidos (`FormSubmissionResource`) | Solo lectura/gestión de estado | — | — | Cubierto indirectamente por `PublicFormsTest` |
| Documentos, Comunicados, Calendario, Galería, Menús, Auditoría | **No implementados como recurso de Filament** — modelo de datos y migraciones sí están | — | — | No |

### Estructura de navegación (real, según roles seedeados)

| Sección | Permiso | Roles con acceso |
|---|---|---|
| Páginas | `pages.*` | administrador, editor_general |
| Noticias | `posts.*` | administrador, editor_noticias_marketing |
| Categorías | `categories.*` | administrador, editor_general |
| Medios | `media.*` | administrador, editor_general, editor_noticias_marketing |
| Redirecciones | `redirects.*` | administrador |
| Configuración | `settings.*` | administrador, editor_noticias_marketing (IDs de Analytics/Ads/Meta) |
| Usuarios | `users.*` | administrador |
| Formularios recibidos | `form_submissions.*` | administrador, editor_general |
| Calendario / Comunicados / Documentos | `calendar_events.*`, `announcements.*`, `documents.*` | administrador, editor_academico |

### Requisitos de usabilidad del panel

- [x] Etiquetas y textos de ayuda en español, sin jerga ("Dirección web de la página" en vez de
      "slug", explicación de por qué importa el alt text)
- [ ] Vista previa antes de publicar — pendiente
- [ ] Autoguardado de borradores — pendiente
- [x] Confirmación antes de acciones destructivas (comportamiento nativo de Filament en
      `DeleteAction`/`DeleteBulkAction`)
- [ ] Mensajes de error que digan qué hacer, no qué falló — parcial (validaciones nativas de
      Filament ya son claras, no se auditó cada mensaje)
- [x] Campos obligatorios marcados y validados en el momento (`->required()`, `->live()`)
- [ ] Subida de imágenes con recorte y aviso si el peso es excesivo — pendiente (hoy valida
      tipo MIME pero no advierte peso en la UI)
- [x] Alt text con recordatorio visible de por qué importa (`MediaForm`)

---

## 5. Sanitización del HTML del editor

Implementado en `App\Services\Html\HtmlSanitizer` (`ezyang/htmlpurifier`). Lista blanca
configurada en `config/dante.php` → `html_sanitizer`:

**Etiquetas permitidas:** `p, br, strong, em, u, s, h2, h3, h4, ul, ol, li, a, blockquote,
table, thead, tbody, tr, th, td, img, figure, figcaption, hr` (`figure`/`figcaption` se
registran a mano porque HTMLPurifier no los trae por defecto, ver el método `clean()`).

**Atributos permitidos, por etiqueta** (no una lista plana — HTMLPurifier valida que cada
atributo sea válido para su etiqueta): `a` → `href|title`, `img` → `src|alt|title`, `table` →
`class`, `th`/`td` → `colspan|rowspan`, resto → `class`.

**Siempre eliminado:** `script`, `iframe`, `object`, `embed`, `form`, `style`, atributos `on*`,
`javascript:` en cualquier URL — verificado con test (`HtmlSanitizerTest`, 4 casos).

Se aplica en `mutateFormDataBeforeCreate`/`mutateFormDataBeforeSave` de `CreatePage`/`EditPage`
(recorre los bloques `texto`/`imagen_texto`) y de `CreatePost`/`EditPost` (campo `content`).
**Nunca se confía en el HTML que llega del editor**, ni siquiera de un usuario ya autenticado
del panel.

---

## 6. Gestión de medios y seguridad de subida

`App\Services\Media\MediaUploadService::upload()`:

1. MIME real detectado con `finfo` (nunca por extensión declarada del cliente).
2. Rechaza si no está en la lista blanca de `config('dante.media.allowed_mimes')`.
3. Rechaza nombres con doble extensión peligrosa (`logo.fw.php`, patrón exacto del webshell
   encontrado en el WordPress comprometido, `docs/01` §C.6) o extensión final ejecutable.
4. Nombre de archivo final: UUID aleatorio, nunca el nombre original.
5. Si es SVG: se sanitiza con `enshrined/svg-sanitize` antes de guardarse (`svg_sanitized =
   true` en el registro).
6. Si es imagen (no SVG/GIF): se genera conversión WebP + variantes responsivas
   (`config('dante.media.responsive_widths')`) con `intervention/image` v4.
7. Se guarda en el disco `media` (`storage/app/public/media`, servido vía symlink
   `public/storage/media` — fuera de la raíz servida directamente por código ejecutable, todo
   el contenido de ese disco es estático).

Test: `MediaUploadServiceTest` (sube + conversión webp, rechaza doble extensión, sanitiza SVG,
rechaza MIME no permitido).

---

## 7. Formularios públicos

`ContactFormRequest` / `PreRegistrationFormRequest` (Form Requests dedicados, patrón adaptado de
IPG) → `App\Actions\Forms\StoreFormSubmission` → tabla `form_submissions` + notificación por
mail (`NewFormSubmissionNotification`, destino configurable en `site_settings.form_notification_email`).

Protecciones (ausentes en IPG, `docs/01` §A.3):
- **Honeypot** (`spatie/laravel-honeypot`), campo trampa + verificación de tiempo mínimo de
  llenado, obligatorio en ambas rutas.
- **Rate limiting** `throttle:5,1` (5 envíos por minuto por IP).

**Pendiente:** integración de captcha real (Turnstile) — el punto de integración queda listo
(mismo lugar que honeypot), se agrega en Fase 6 según CLAUDE.md.

Test: `PublicFormsTest` (4 casos: envío válido + notificación, honeypot bloquea bot, validación
de campos obligatorios, rate limit).

---

## 8. Comandos artisan del proyecto

| Comando | Qué hace | Estado |
|---|---|---|
| `dante:import-redirects {csv=docs/redirecciones-301.csv}` | Carga el mapa 301 (idempotente, `updateOrCreate` por `from_path`) | **Implementado y probado** — 39 filas cargadas |
| `dante:migrate-wp` | Migración desde `dante_wp_legacy` | Pendiente — Fase 5 |
| `dante:check-links` | Detecta enlaces internos rotos | Pendiente |
| `dante:sitemap` | Regenera el sitemap | Pendiente — Fase 6 (`spatie/laravel-sitemap` ya instalado) |

---

## 9. Cierre de la Fase 3 (corte de esta sesión)

**No se cerró la fase completa** — se priorizó, según la instrucción del proyecto, tener una
**vertical completa de punta a punta** (páginas con bloques + multiidioma + SEO + auth con
roles + medios seguros + formularios + redirecciones) antes que 17 features a medio hacer.

### Hecho y probado (22/22 tests Pest en verde, Pint limpio, Larastan nivel 5 sin errores)

- Laravel 13 instalado en `app/`, con Pint, Larastan (nivel 5) y Pest 4 configurados.
- Filament 5 como panel, en ruta no adivinable configurable por `.env`.
- Modelo de datos completo (14 tablas de contenido + auth + permisos + auditoría) con soporte
  multiidioma ES/IT vía `spatie/laravel-translatable`.
- Constructor de páginas por bloques (7 de 16 bloques del catálogo: hero, texto enriquecido,
  imagen+texto, tarjetas, CTA destacado, cifras/hitos, listado de noticias).
- Sanitización HTML server-side con lista blanca real (HTMLPurifier), aplicada a páginas y
  noticias.
- Gestión de medios segura: MIME real, nombre aleatorio, SVG sanitizado, conversión WebP +
  responsive, rechazo de dobles extensiones tipo webshell.
- Gestión de redirecciones 301 completa, con las 39 reales precargadas.
- Configuración global editable, incluido el toggle de italiano.
- Formularios de contacto y pre-inscripción: envío + guardado + mail + honeypot + rate limit.
- 4 roles reales con permisos granulares (`spatie/laravel-permission`), Policies por modelo.
- Auth del panel con 2FA (TOTP) obligatorio, nativo de Filament 5.
- Registro de auditoría activo en todos los modelos editoriales (trait `HasAuditing`) — falta
  el recurso de solo lectura en el panel para consultarlo desde la UI.
- Seeders: permisos/roles, usuario admin inicial (contraseña generada y mostrada una sola vez
  en consola), configuración inicial, árbol de páginas fijas del mapa del sitio.

### Pendiente — para la próxima sesión de Fase 3, en orden sugerido

1. **Recursos de Filament que faltan**: Documentos, Comunicados, Calendario académico,
   Galería, Menús (CRUD + orden), Auditoría (solo lectura). El modelo de datos y las
   migraciones de las cinco primeras ya están listos — es "solo" repetir el patrón de
   `PostResource`/`CategoryResource`.
2. **9 bloques de contenido restantes** del catálogo de `docs/02` §8 (galería, acordeón/FAQ,
   video, testimonios, mapa, formulario embebido, documentos descargables, listado de
   comunicados, selector de sede).
3. **Buscador interno** — no implementado.
4. **Exportación CSV** de `form_submissions` — no implementada (el modelo/recurso sí soporta
   filtrar por tipo/estado).
5. Campos SEO explícitos en el formulario de `PostResource` (el modelo ya tiene las columnas).
6. Tests de Pest para los recursos ya implementados que no lo tienen todavía: Post, Category,
   User, SiteSetting.
7. **`dante:migrate-wp`** y `dante:check-links`/`dante:sitemap` — quedan para Fase 5/6 según
   plan original, no se tocaron en esta sesión.
8. Revisar el límite de tamaño de subida y el aviso visual de peso excesivo en `MediaForm`.
9. Confirmar entorno real de Plesk (pregunta abierta #13, sigue sin respuesta) antes de fijar
   definitivamente disco/colas en producción.

### Hallazgo técnico documentado para el equipo

Durante esta sesión se encontró y corrigió un bug real que producía **crashes silenciosos de
PHPUnit/Pest sin ningún mensaje de error** (el proceso terminaba con código de salida 1 y cero
output): dos clases (`Policy` y su trait `AuthorizesViaPermissions`) declaraban la misma
propiedad tipada con valores por defecto distintos, lo cual es un `FatalError` de composición de
clases en PHP que ocurre en tiempo de carga, antes de que el manejador de excepciones de Laravel
pueda interceptarlo. Se solucionó reemplazando la propiedad por un método abstracto
(`permissionPrefix(): string`). Vale la pena recordarlo si vuelve a aparecer un test que "no
imprime nada y sale con código 1".

**Prueba de usabilidad:** pendiente — requiere una persona no técnica probando el panel, no se
hizo en esta sesión (trabajo 100% de backend).
**Suite Pest en verde:** ✅ 22/22.
**Luz verde para Fase 4:** parcial — la vertical de páginas/noticias/medios/formularios ya
soporta maquetar el frontend público correspondiente; los tipos de contenido sin recurso de
Filament (documentos, comunicados, calendario, galería, menús) necesitan su CRUD de panel antes
de que el cliente pueda cargarlos, aunque el modelo de datos ya no bloquea el diseño del
frontend de esas secciones.
