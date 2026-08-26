# 08 — SEO técnico e integraciones (Fase 6)

Estado: **implementado — ver detalle por sección.** Prioridad seguida, según CLAUDE.md: (1)
redirecciones 301, (2) metadatos editables, (3) JSON-LD, (4) sitemap/robots, (5) estructura
semántica, (6) integraciones. Código en `app/`, tests en `app/tests/Feature/`.

---

## 1. SEO en página

| Elemento | Implementación | Editable desde el panel | Estado |
|---|---|---|---|
| `<title>` | `{título} — Colegio Dante Alighieri`, override por `seo_title` (ES/IT) | sí (Páginas y Noticias) | ✅ |
| `meta description` | `seo_description` (ES/IT), cae a un texto institucional por defecto en el layout si no hay página | sí | ✅ |
| `link rel=canonical` | autogenerado con `url()->current()`, sobreescribible con `canonical_url` (solo Páginas — Noticias no lo necesitan, no hay duplicación real) | sí | ✅ |
| `meta robots` | `index`/`noindex` por página (`is_indexable`) **y** bloqueo global automático fuera de `production` (`config('dante.seo.block_indexing')`, lee `APP_ENV`/`DANTE_BLOCK_INDEXING`) — ver §4 | sí (por página) + automático por entorno | ✅ |
| Un solo `h1` por página | Ya resuelto en Fase 4 (bug de doble/cero `h1` corregido); sin cambios en esta fase | — | ✅ (Fase 4) |
| Jerarquía de encabezados sin saltos | Verificado en Fase 4 (accesibilidad); sin cambios acá | — | ✅ (Fase 4) |
| URLs limpias, en español, sin fechas ni IDs | Ya resuelto en Fase 3/4 (`slug` editable, jerarquía real de `Page`) | slug editable | ✅ |
| Enlaces internos con texto descriptivo | Revisado: breadcrumbs, tarjetas y menús usan el título real como texto de enlace, no "click aquí" | — | ✅ |
| Alt en todas las imágenes | Obligatorio en el panel desde Fase 3 (`MediaPicker`) | sí | ✅ (Fase 3) |
| Migas de pan visibles + marcadas | `<x-breadcrumbs>` (visual, Fase 4) ahora también emite `BreadcrumbList` (JSON-LD) — ver §3 | — | ✅ |

Campo nuevo agregado esta fase: `Page.canonical_url` ya existía en la base desde la Fase 3
pero **no estaba expuesto en el formulario del panel** (`PageForm`) ni se pasaba a la vista —
las páginas siempre usaban el canonical autogenerado. Corregido: ahora es un campo de texto
"avanzado" en la sección "Buscadores (SEO)", y `pages/show.blade.php` / `pages/landing.blade.php`
lo pasan al layout.

---

## 2. Open Graph y Twitter Cards

| Etiqueta | Valor por defecto | Sobreescribible | Estado |
|---|---|---|---|
| `og:title` | `seo_title` o `title` | sí | ✅ |
| `og:description` | `seo_description` | sí | ✅ |
| `og:image` | `seo_image` de la página → `cover_media` → (nada) | sí (Páginas/Noticias) | ✅ (sin imagen de marca de respaldo, ver más abajo) |
| `og:type` | `website` en páginas, `article` en noticias | auto | ✅ |
| `og:url` | canonical | auto | ✅ |
| `og:locale` | `es_PY` / `it_IT` según idioma activo | auto | ✅ |
| `og:site_name` | `SiteSetting::get('site_name')` | vía panel (Configuración) | ✅ |
| `twitter:card` | `summary_large_image` si hay `og:image`, si no `summary` | auto | ✅ |

**Pendiente — imagen OG por defecto de la marca (1200×630 con el logotipo):** no existe en los
insumos de la Fase 2 (`_insumos/02-marca-dante/` no trae un asset de este tamaño, solo los
logotipos SVG). Por instrucción explícita del proyecto no se generó una imagen nueva en esta
sesión. Queda resuelto en código (`config('dante.seo.default_og_image')`, variable de entorno
`DANTE_DEFAULT_OG_IMAGE`) — cuando el cliente/diseño entregue el archivo, subirlo a
`public/images/` y cargar la ruta en `.env` de producción, sin tocar código. Mientras tanto:
páginas/noticias sin imagen propia simplemente no emiten `og:image` (mejor que una imagen rota).

Pendiente de verificación manual (requiere el sitio en un dominio público, no aplica a este
entorno de desarrollo): depurador de Facebook y validador de tarjetas de X — Fase 9/10.

---

## 3. Datos estructurados (JSON-LD)

| Tipo | Dónde | Estado |
|---|---|---|
| `EducationalOrganization` | Global, en el `<head>` del layout (`x-schema.organization`) — nombre, logo, teléfono/email/dirección desde `SiteSetting`, `sameAs` con redes sociales si están cargadas | ✅ |
| `BreadcrumbList` | Emitido por `<x-breadcrumbs>` en toda página que lo use (páginas internas, noticias, contacto) | ✅ |
| `Article` / `NewsArticle` | Detalle de noticia (`posts/show.blade.php`) — `NewsArticle`, con `publisher`, fechas de publicación/modificación reales | ✅ |
| `FAQPage` | Bloque "Acordeón / Preguntas frecuentes" del constructor de páginas — solo se emite si hay preguntas con respuesta real (evita advertencias con bloques vacíos) | ✅ |
| `WebSite` + `SearchAction` | Inicio, apunta a `/buscar?q={search_term_string}` (el buscador interno de Fase 3) | ✅ |
| `Event` | **No implementado.** No existe un bloque ni modelo de "Evento" en el catálogo de 16 bloques de la Fase 3/4 (hay `CalendarEvent` para el calendario académico, pero es una lista, no fichas de evento individuales con fecha/lugar) — fuera de alcance de esta fase tal como pide la instrucción ("solo si ya existen bloques de ese tipo") | ⛔ no aplica todavía |
| `LocalBusiness` (mencionado en PLAN.md) | Se decidió **no** duplicarlo: `EducationalOrganization` ya cubre nombre/dirección/teléfono y es el tipo correcto de schema.org para una institución educativa (`LocalBusiness` es para comercios) | ✅ (decisión, no un pendiente) |

Validación: no se pudo correr el Rich Results Test real de Google (requiere URL pública, el
entorno es local) — la validación hecha fue manual, cruzando cada JSON-LD emitido contra el
esquema oficial de schema.org (campos requeridos/recomendados de cada tipo). Pendiente para
Fase 9/10 sobre el staging real.

---

## 4. Sitemap y robots

- [x] `sitemap.xml` **dinámico** (`GET /sitemap.xml`, `App\Http\Controllers\SitemapController`,
      con `spatie/laravel-sitemap` — ya estaba en `composer.json`, no se sumó nada nuevo), con
      `lastmod` real (`updated_at` de cada página/noticia)
- [x] Excluye borradores (`status != published`), `noindex` (`is_indexable = false`); no hay
      página de resultados de búsqueda que deba excluirse aparte (`/buscar` no se referencia)
- [x] No hace falta índice de sitemaps — muy por debajo de 50.000 URLs (39 del inventario + lo
      que se cargue después)
- [x] `robots.txt` **dinámico** (`GET /robots.txt`, `App\Http\Controllers\RobotsController`),
      referencia el sitemap cuando no está bloqueado
- [x] `robots.txt` de staging bloquea todo, producción no — **resuelto de raíz, no solo
      verificado**: se **eliminó el archivo estático** `public/robots.txt` (el error más caro y
      común según CLAUDE.md es justamente que un archivo físico con `Disallow: /` de staging
      viaje "olvidado" a producción, porque el webserver lo sirve directo sin pasar por Laravel).
      Ahora `robots.txt` siempre se genera en código a partir de
      `config('dante.seo.block_indexing')`, que por defecto es `true` en cualquier entorno que
      no sea `APP_ENV=production` y solo se apaga con la variable de entorno
      `DANTE_BLOCK_INDEXING=false` en el `.env` real de producción — nunca editando un archivo.

---

## 5. Redirecciones

Mapa completo en `docs/redirecciones-301.csv` (39 filas) y `docs/07-migracion-wordpress.md` §7.
Verificación real hecha en esta fase (`app/tests/Feature/Redirects301VerificationTest.php`,
4 tests, corridos y en verde):

- [x] Las 39 filas se cargan con `dante:import-redirects`, las 39 activas, las 39 en 301
- [x] **Sin cadenas reales entre filas** (ningún destino de una fila es a la vez origen de otra,
      excluyendo el caso ya cubierto por `HandleRedirects` de filas que no cambian de URL)
- [x] **Ningún destino apunta a un 404** — se probó sirviendo fixtures con el mismo slug que
      cada destino del mapa (páginas y noticias), sin necesidad de esperar a tener el contenido
      real cargado en producción
- [x] Ninguna de las 39 URLs viejas redirige a un 404 real
- [ ] www / no-www unificado con 301 — **no se puede verificar en este entorno** (depende de la
      configuración del vhost/Plesk en el servidor real, no de código Laravel). Queda como
      checklist de Fase 10 (despliegue): configurar la redirección canónica de dominio en Plesk.
- [ ] http → https con 301 — mismo caso, depende de Plesk/Let's Encrypt (Fase 10)
- [x] Sin barra final duplicando URLs — el middleware normaliza `from_path`/`to_path` con
      `rtrim(..., '/')`, ya cubierto desde la Fase 3

**Hallazgo real durante la verificación** (no bloqueante, documentado por transparencia): las
filas `/novedades/ → /noticias` y `/noticias/ → /noticias` (esta última no cambia de URL) forman,
en el papel, algo que parece una cadena de dos saltos. En la práctica **no lo es**: el middleware
`App\Http\Middleware\HandleRedirects` ya anula cualquier fila cuyo destino sea igual a su propio
origen (guarda agregada en la Fase 5 para otro caso), así que `/novedades/` redirige una sola vez
a `/noticias` y ahí se sirve la página real. Se deja registrado para que quien lea el CSV no se
confunda pensando que hay un doble salto en producción.

---

## 6. Integraciones

> **Cambio post-cierre (2026-08-25):** el cliente pidió explícitamente que **todos** los IDs y
> credenciales de integraciones —incluidos los secretos— se carguen y se activen/desactiven
> desde el panel, sin depender de `.env` ni de acceso al servidor. Se creó
> `App\Models\IntegrationSetting` (fila única, como `HomeSetting`) administrada desde
> **Integraciones** (`App\Filament\Pages\IntegrationSettings`, permiso `settings.*` — mismo rol
> que ya administraba estos IDs). Los dos campos realmente secretos
> (`meta_capi_access_token`, `turnstile_secret_key`) usan el cast `encrypted` de Eloquent (AES-256
> con la `APP_KEY` de la app) — nunca quedan en texto plano en la base, verificado en
> `tests/Feature/Filament/IntegrationSettingsTest.php`. `google_analytics_id`/`google_tag_manager_id`/
> `meta_pixel_id` migraron de `SiteSetting` a esta misma tabla (una migración con datos, no solo
> de esquema). `config/dante.php` ya no tiene `meta_capi`/`turnstile` ni `.env.example` sus
> variables — el código de abajo lee `IntegrationSetting::current()`, no `config()`/`env()`.
> Cada integración tiene su propio interruptor (`ga_enabled`, `meta_enabled`,
> `turnstile_enabled`): apagado, no se carga/envía nada, aunque los datos sigan guardados.

### Google Analytics 4 / Tag Manager

| Item | Valor | Estado |
|---|---|---|
| ID de GTM | `IntegrationSetting->google_tag_manager_id`, editable en el panel (Integraciones) | ✅ |
| ID de GA4 | `IntegrationSetting->google_analytics_id` (se configura *dentro* de GTM en el contenedor, el ID acá queda de referencia/documentación para quien administre GTM) | ✅ |
| Interruptor activo/inactivo | `IntegrationSetting->ga_enabled` — apagado, `data-gtm-id` sale vacío en el HTML y `consent.js` no carga nada | ✅ |
| Se carga **solo tras consentimiento** | `resources/js/consent.js` — el `<script src="...gtm.js">` se crea por código recién si `analytics === true` en el consentimiento guardado, y solo si además está activo | ✅ |
| Eventos personalizados (envío de formulario, clic en teléfono/WhatsApp, descarga de documento) | **No implementado en esta fase** — requiere decidir con el cliente qué eventos de GTM disparar y con qué nombres, y no hay cuenta de GTM real para probarlos. Queda como pregunta abierta (ver `docs/01-analisis-descubrimiento.md` §E). El envío de formulario **sí** dispara Meta `Lead` (ver abajo); el equivalente en `dataLayer.push` para GTM es trivial de sumar cuando haya contenedor real para probarlo | ⏳ pendiente de acceso del cliente |
| Conversiones definidas | Pendiente de la cuenta real de GA4/Ads del cliente | ⏳ |

### Meta Pixel + Conversions API

| Item | Estado |
|---|---|
| Interruptor activo/inactivo | ✅ `IntegrationSetting->meta_enabled` — apagado, ni el Pixel del cliente ni Conversions API se disparan, aunque el pixel ID/token sigan guardados |
| Pixel del lado cliente, tras consentimiento | ✅ `resources/js/consent.js` — mismo patrón que GTM, solo si `marketing === true` y la integración está activa |
| **Conversions API del lado servidor** | ✅ `App\Services\Integrations\MetaConversionsApi` — envía a `graph.facebook.com/v20.0/{pixel_id}/events`, con `email`/`phone` hasheados SHA-256 (nunca en texto plano) |
| Deduplicación por `event_id` | ✅ El `event_id` se genera del lado servidor (`Str::uuid()`) y viaja de vuelta al cliente por `session('meta_event_id')`; `consent.js` dispara el `fbq('track', 'Lead', ..., {eventID})` del navegador con el **mismo** id, si y solo si hay consentimiento de marketing |
| Eventos: `PageView`, `Lead`, `Contact` | `PageView` (al cargar el Pixel tras consentir) y `Lead` (envío de `contacto`/`pre-inscripción`) implementados. `Contact` no se sumó — es redundante con `Lead` para este caso de uso (un solo formulario de contacto, no un flujo de e-commerce) |
| Token y Pixel ID administrables desde el panel | ✅ Los tres (Pixel ID, token de Conversions API, código de evento de prueba) se cargan en Integraciones → Meta Pixel + Conversions API. El token se guarda **cifrado** (`IntegrationSetting::casts()`), nunca en `.env` ni en texto plano en la base |

**Pendiente real, no de código:** el cliente todavía no entregó una cuenta de Meta Business ni
un token de acceso de Conversions API (confirmado en `docs/01-analisis-descubrimiento.md` §E —
"ninguna integración de analytics/pixel/captcha configurada hoy"). Sin token cargado, o con la
integración apagada desde el panel, `MetaConversionsApi::send()` no hace ninguna llamada de red
(`tests/Feature/MetaConversionsApiTest.php` lo cubre) — el código queda listo para activarse el
día que alguien cargue el dato desde el panel, sin tocar código ni redeploy.

Además: la lógica de consentimiento de marketing se respeta **también del lado servidor** — si
el visitante no aceptó cookies de marketing, ni el Pixel del cliente ni la llamada a Conversions
API se disparan (`StoreFormSubmission` lee la cookie `dante_consent_marketing` que escribe
`consent.js`). Un CAPI que ignora el rechazo de cookies no sería consistente con el resto de la
implementación.

### Captcha

**Elección:** Cloudflare Turnstile (según lo definido en `CLAUDE.md`/PLAN.md).

- [x] En los dos formularios públicos existentes (contacto, pre-inscripción) —
      `<x-turnstile-widget>` en ambos partials
- [x] Validación **del lado servidor** — `App\Rules\Turnstile` llama a
      `https://challenges.cloudflare.com/turnstile/v0/siteverify`, nunca confía en el widget del
      cliente. Implementa la interfaz `ImplicitRule` a propósito: sin eso, Laravel no corre la
      regla si el campo directamente no llega en el POST (justo lo que hace un bot que no
      ejecuta el widget)
- [x] Sumado a honeypot y `throttle:5,1` ya existentes (Fase 3) — nunca los reemplaza
- [x] Mensaje de error claro si falla ("No pudimos verificar que sos una persona...")
- [x] Site key y secret key administrables desde el panel (Integraciones → Turnstile), la secret
      key guardada **cifrada** — con interruptor propio (`turnstile_enabled`): apagado, no exige
      verificación (honeypot + rate limit siguen protegiendo igual), sin importar si las claves
      quedaron cargadas

**Pendiente real, no de código:** falta dar de alta el sitio en el dashboard de Cloudflare del
cliente para obtener las claves reales (pregunta agregada a `docs/01-analisis-descubrimiento.md`
§E). Hasta entonces, con la integración apagada desde el panel, el captcha queda inactivo
(honeypot + rate limit siguen protegiendo igual) — nunca así en producción.

### Consentimiento de cookies

- [x] Banner que **bloquea efectivamente** los scripts de terceros hasta aceptar — no hay ningún
      `<script src="...gtm.js">` ni `fbevents.js` incondicional en el HTML (verificado en
      `tests/Feature/CookieConsentTest.php`); `resources/js/consent.js` los inyecta por código
      recién tras leer el consentimiento guardado
- [x] Opciones: **Aceptar todo** / **Rechazar todo** / **Configurar** (categorías: necesarias,
      analítica, marketing)
- [x] Rechazar es un solo clic, con la misma jerarquía visual que Aceptar (ningún "dark pattern"
      de ocultar el rechazo en un enlace chico)
- [x] Preferencia persistida (`localStorage` + cookie, 180 días) y **revocable**: botón flotante
      "Cookies" siempre visible que reabre el panel de configuración
- [ ] Enlace a la política de cookies — el banner ya enlaza a `/institucion/politica-de-cookies`,
      pero **esa página todavía no existe como contenido publicado** (el texto legal está en
      `docs/03-copywriting.md` §5 como borrador de trabajo, "no publicable sin revisión legal y
      aprobación del cliente" — mismo pendiente ya señalado en el cierre de la Fase 2). Crear la
      `Page` real con ese slug cuando el texto esté aprobado.

### Otras

| Integración | Estado |
|---|---|
| WhatsApp flotante | ✅ `<x-whatsapp-float>`, número desde `SiteSetting` (`whatsapp_number`) — botón oculto si no hay número cargado, no hay un número real en los insumos todavía |
| Google Maps (embebido diferido, no bloqueante) | ✅ `<x-deferred-map>` en `/contacto` — patrón "clic para activar" (el iframe no entra al DOM hasta que se pide), URL de embebido desde `SiteSetting` (`google_maps_embed_url`); sin URL real cargada todavía (dirección pendiente, ver `docs/01` §E #19) muestra un aviso, no rompe la página |
| Redes sociales del pie | ✅ Facebook/Instagram desde `SiteSetting` (`social_facebook_url`/`social_instagram_url`), antes eran enlaces `href="#"` decorativos — ahora no se renderizan si no hay URL cargada |
| Correo transaccional (SMTP + SPF/DKIM del dominio) | Fuera de esta fase — depende del proveedor de correo real (HostGator, `CLAUDE.md` §8: "el DNS de correo no se toca en el cutover"), se revisa en Fase 10 |

---

## 7. Alta en herramientas

No se puede ejecutar sin acceso real — quedan como pendientes explícitos, no simulados:

- [ ] Google Search Console — requiere verificar la propiedad `dante.edu.py`, que solo puede
      hacer quien tiene acceso al DNS o a Search Console del cliente
- [ ] Bing Webmaster Tools — mismo caso
- [ ] Google Business Profile — mismo caso, además requiere datos reales de sede (pendiente
      #19 en `docs/01-analisis-descubrimiento.md` §E)
- [ ] Accesos entregados al cliente

Estas cuatro tareas se agregan formalmente como pendientes de Fase 10 (despliegue), donde ya
existe la ventana de "verificación post-cutover" para hacerlas junto con el cutover real.

---

## 8. Verificación final

| Prueba | Resultado |
|---|---|
| Rich Results Test sin errores | ⏳ pendiente — requiere URL pública (Fase 9/10). Validación manual de estructura hecha en §3 |
| Sitemap accesible y válido | ✅ `GET /sitemap.xml`, XML válido, `lastmod` real — test automatizado |
| `robots.txt` correcto en producción | ✅ por diseño (dinámico, sin archivo estático que "se olvide"), verificado con tests que simulan ambos entornos |
| Las 20 URLs top redirigen bien | ✅ las 39 (todo el mapa, no solo el top 20) — test automatizado, ver §5 |
| GA4 recibiendo datos | ⏳ pendiente — no hay ID de GTM/GA4 real del cliente todavía |
| Meta CAPI recibiendo eventos | ⏳ pendiente — no hay token de acceso real del cliente todavía; el envío está implementado y probado con `Http::fake()` |
| Captcha bloqueando envíos automatizados | ✅ probado con `Http::fake()` simulando éxito/fallo de Cloudflare; sin claves reales queda inactivo (no bloqueante) |
| Banner de cookies bloqueando scripts antes de aceptar | ✅ verificado (ningún script de terceros incondicional en el HTML) |
| Vista previa OG correcta en Facebook, WhatsApp y LinkedIn | ⏳ pendiente — requiere URL pública y, para que se vea bien, la imagen OG de marca que todavía no existe (§2) |

---

## 9. Monitoreo post-lanzamiento (90 días)

| Semana | 404 en Search Console | Sesiones orgánicas | Posición promedio | Nota |
|---|---|---|---|---|
| 1 | | | | |
| 2 | | | | |
| 4 | | | | |
| 8 | | | | |
| 12 | | | | |

> Es normal una caída del 10–20 % las primeras 2–4 semanas tras una migración. Si a las 8
> semanas no recuperó, hay un problema real: revisar redirecciones e indexación.

Esta tabla solo se puede completar después del cutover real (Fase 10) con datos de Search
Console del dominio en producción — no hay forma de adelantarla desde el entorno de desarrollo.

---

## 10. Resumen de archivos de esta fase

| Área | Archivos principales |
|---|---|
| Metadatos / OG | `resources/views/components/layouts/app.blade.php`, `app/Filament/Resources/Pages/Schemas/PageForm.php` |
| JSON-LD | `resources/views/components/schema/organization.blade.php`, `resources/views/components/breadcrumbs.blade.php`, `resources/views/components/blocks/faq.blade.php`, `resources/views/posts/show.blade.php`, `resources/views/home.blade.php` |
| Sitemap / robots | `app/Http/Controllers/SitemapController.php`, `app/Http/Controllers/RobotsController.php`, `config/dante.php` (`seo.block_indexing`) — se **eliminó** `public/robots.txt` |
| Redirecciones (verificación) | `tests/Feature/Redirects301VerificationTest.php` |
| GA4/GTM/Meta Pixel + CAPI | `resources/js/consent.js`, `app/Services/Integrations/MetaConversionsApi.php`, `app/Actions/Forms/StoreFormSubmission.php` |
| Captcha | `app/Rules/Turnstile.php`, `resources/views/components/turnstile-widget.blade.php` |
| Cookies | `resources/views/components/cookie-consent.blade.php`, `resources/js/consent.js` |
| Otras integraciones | `resources/views/components/whatsapp-float.blade.php`, `resources/views/components/deferred-map.blade.php`, `resources/views/components/site-footer.blade.php` |
| Configuración/settings nuevos | `database/seeders/SiteSettingSeeder.php` (`whatsapp_number`, `social_facebook_url`, `social_instagram_url`, `google_maps_embed_url`) |
| **Panel "Integraciones"** (todos los IDs y credenciales, con toggle por integración) | `app/Models/IntegrationSetting.php`, `app/Filament/Pages/IntegrationSettings.php`, `app/Policies/IntegrationSettingPolicy.php`, migración `2026_08_25_020000_create_integration_settings_table.php` |
| Tests nuevos | `SeoMetadataTest.php`, `SitemapAndRobotsTest.php`, `TurnstileTest.php`, `CookieConsentTest.php`, `MetaConversionsApiTest.php`, `Redirects301VerificationTest.php`, `Filament/IntegrationSettingsTest.php` — 34 tests nuevos, suite completa en 129/129 verde, Pint y Larastan (nivel 5) sin hallazgos |
