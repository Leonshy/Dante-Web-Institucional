# 01 — Análisis y descubrimiento (Fase 0)

Estado: **vacío — completar en Fase 0**

---

## A. Análisis del repo de IGP

> Insumo: `_insumos/01-referencia-ipg/`
> Objetivo: entender el patrón para replicarlo, no para reinventarlo.

### A.1 Datos del proyecto de referencia

| Campo | Valor |
|---|---|
| Versión de Laravel | `laravel/framework: ^12.0` (composer.json). No usa Laravel 13. |
| Versión de PHP | `php: ^8.2` (composer.json) |
| Livewire / Filament / otro | **Ninguno.** Panel a medida: controllers clásicos (`App\Http\Controllers\Admin\*`) + rutas resource + vistas Blade con `<form>` y `fetch`/JS puntual. No hay Livewire, Alpine solo aparece suelto en algunas vistas, no hay Filament ni ningún paquete de admin panel en `composer.json`. |
| CSS | Tailwind CSS 4 vía `@tailwindcss/vite` (`package.json`). No usa Bootstrap pese a que las clases del admin usan prefijo `igp-admin-*` (CSS propio, no Bootstrap real, a pesar del nombre `d-lg-none` que sugiere clases estilo Bootstrap mezcladas — a confirmar si hay un CSS custom que las define). |
| Editor de texto enriquecido | **TinyMCE** (`"tinymce": "^8.3.2"` en `package.json`, cargado en `resources/views/layouts/admin.blade.php` y en `resources/views/admin/documents/form.blade.php`). Sin HTMLPurifier ni ninguna sanitización server-side: el HTML que entrega TinyMCE se guarda tal cual en columnas `longText content` y se imprime sin escapar (`{!! $page->content !!}`, `{!! $post->content !!}`, etc. en `resources/views/frontend/*`). **Riesgo de XSS almacenado**, agravado por el hecho de que el sitio anterior fue comprometido. |
| Gestión de medios | Tabla `media` propia (no usa Spatie Media Library). `App\Services\MediaService::upload()` genera nombre aleatorio (`Str::uuid()`), guarda en `storage/app/public/media/{folder}/` vía disco `public` de Flysystem, detecta tipo por MIME (`getMimeType()` de Symfony, basado en `finfo`). **No reprocesa imágenes** (no genera variantes/tamaños), **no sanitiza SVG** y **acepta subir SVG directamente** (`MediaController::store` permite `mimes:...,svg,...`) sin ningún filtrado de contenido — riesgo de XSS vía SVG. `intervention/image ^3.11` está declarado en `composer.json` pero **no se usa en ningún archivo** (dependencia muerta). |
| Paquetes clave (`composer.json`) | `intervention/image ^3.11` (no usado), `laravel/framework ^12.0`, `laravel/tinker`, `league/flysystem-local ^3.31`, `spatie/laravel-permission ^6.24` (roles/permisos). Dev: `laravel/pint`, `pestphp` no está instalado (usa PHPUnit por defecto, ver A.3 Tests). |
| Estructura del panel admin | Prefijo de rutas `/admin` (`routes/web.php`), agrupadas por `name('admin.')` y middleware `admin` (alias de `App\Http\Middleware\AdminMiddleware`). Un `Route::resource()` por tipo de contenido (`pages`, `products`, `services`, `posts`, `documents`, `certifications`, `banners`, `gallery`, `users`) con `->except(['show'])` — no hay vista de detalle, solo listado/formulario. Controllers en `app/Http/Controllers/Admin/*`, uno por recurso, con `index/create/store/edit/update/destroy` y validación **inline con `$request->validate()`** dentro del propio controller (no usan Form Requests salvo para el formulario público de contacto — `ContactRequest`, `QuoteRequest`). Vistas en `resources/views/admin/{recurso}/{index,form}.blade.php` compartiendo `layouts/admin.blade.php` (sidebar + topbar fijos, sin Livewire, recarga completa de página en cada acción). |

### A.2 Modelo de datos de IGP

Tabla por tabla: nombre, propósito, relaciones, si aplica a Dante.

| Tabla | Propósito | Relaciones | ¿Aplica a Dante? |
|---|---|---|---|
| `users` (+ migración que agrega `avatar`, `is_active`) | Usuarios del panel admin | `HasRoles` (Spatie) | Sí, tal cual. Falta `is_active` como patrón de "desactivar sin borrar" — útil para Dante. |
| `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` (paquete `spatie/laravel-permission`) | Roles y permisos | Vincula con `users` | Sí, tal cual. IGP solo usa 2 roles hardcodeados (`admin`, `editor`) verificados con `hasAnyRole()` en el middleware — no hay gestión granular de permisos por módulo, es binario. |
| `site_settings` | Configuración global clave-valor (nombre del sitio, logo, teléfono, redes, IDs de analytics/captcha, etc.) | Ninguna (independiente) | Sí, tal cual. Patrón simple `key/value/type/group` con caché de 1h (`Cache::remember`) — encaja perfecto con "configuración global editable" de Dante. |
| `media` | Biblioteca central de archivos subidos (imágenes, documentos, video externo) | `belongsTo(User)`; referenciada por `media_id` desde `pages`, `products`, `services`, `posts`, `documents`, `certifications`, `gallery_items`, `banners` | Sí, tal cual el patrón de tabla central de medios con soft deletes. Falta reforzar seguridad (ver A.3). |
| `categories` | Taxonomía polimórfica por campo `type` (product/service/news/document/gallery), con jerarquía `parent_id` | `belongsTo`/`hasMany` recursiva a sí misma; `hasMany` hacia `products`, `posts`, `documents` | Aplica el patrón de taxonomía simple, pero para Dante el `type` relevante sería `news`/`document`/`gallery` (no `product`/`service`, que son específicos de una empresa comercial). **Ojo:** IGP no tiene un CRUD de categorías en el admin (solo se cargan por seeder) — eso hay que construirlo para Dante si se necesita administrar categorías desde el panel. |
| `pages` | Páginas institucionales de contenido libre (about, quality, training, home), con SEO por página | `belongsTo(User)`, `belongsTo(Media)` (imagen destacada) | Sí, es el corazón del sitio institucional. Este es el tipo de contenido que más aplica a Dante (secciones "Sobre nosotros", "Propuesta educativa", etc.), aunque en IGP el campo `section` está hardcodeado a 3-4 valores fijos — para Dante conviene un modelo de páginas con jerarquía/árbol (ver A.2 vs. `docs/02` §7) en vez de `section` fijo. |
| `products` | Catálogo de productos (metales) con SEO, destacado, adjunto | `belongsTo(User)`, `belongsTo(Category)`, `belongsTo(Media)`, `belongsToMany(Media)` vía `product_media` | **No aplica a Dante.** Es 100% específico del catálogo comercial de IGP Metales. |
| `services` | Catálogo de servicios de IGP, con ícono y adjunto | `belongsTo(User)`, `belongsTo(Media)`, `belongsToMany(Media)` vía `service_media` | Parcialmente análogo: Dante podría necesitar algo similar para "Programas académicos" u "Oferta educativa", pero no es el mismo dominio — se adapta el patrón (CRUD + SEO + imagen destacada), no la tabla. |
| `posts` | Blog / novedades, con excerpt, destacado, fecha de publicación, SEO | `belongsTo(User)`, `belongsTo(Category)`, `belongsTo(Media)` | Sí, tal cual. Es exactamente el tipo de contenido "Noticias / Novedades" que Dante va a necesitar. |
| `documents` | Documentos técnicos descargables (fichas, catálogos) con adjunto y SEO | `belongsTo(User)`, `belongsTo(Category)`, `belongsTo(Media)` | Aplica el patrón (listado + descarga de PDF con SEO), útil para "Documentos institucionales" o "Circulares" de Dante, aunque el contenido específico ("documentos técnicos" industriales) no aplica. |
| `certifications` | Certificaciones de calidad de la empresa (ISO, etc.) con imagen y adjunto | `belongsTo(Media)` | No aplica tal cual (certificaciones industriales), pero el patrón de "tarjetas simples con imagen + adjunto + orden" podría adaptarse a "Reconocimientos" o "Acreditaciones" del colegio si aplicara. |
| `gallery_items` | Galería de fotos/videos | `belongsTo(Media)` | Sí, aplica directamente — galería de eventos/actividades escolares es un contenido típico institucional. |
| `contacts` | Envíos de formularios de contacto y cotización | Referencias sueltas (`unsignedBigInteger`, no FK real) a `products`/`services` | El formulario de contacto aplica tal cual; los campos de cotización (`product_service_interest`, `product_id`, `service_id`) son específicos de venta y no aplican a Dante — se adapta a formularios de "admisión"/"contacto" simples. |
| `product_media`, `service_media` | Tablas pivote para galerías de imágenes por producto/servicio | `belongsTo(Product\|Service)`, `belongsTo(Media)` | No aplica tal cual (dependen de `products`/`services`), pero el patrón pivote reusable para "múltiples imágenes por contenido" sí es útil si Dante necesita galerías embebidas en una página o noticia. |
| `banners` | Banners/slides rotativos por posición (home, products, services) con CTA | `belongsTo(Media)` | Sí, aplica el patrón (banner con imagen, texto, CTA y posición) para el home de Dante. |
| `cache`, `jobs` (tablas por defecto de Laravel) | Driver de cache y colas basados en BD (`CACHE_STORE=database`, `QUEUE_CONNECTION=database`) | — | Sí, tal cual — confirma que IGP **no depende de Redis ni Supervisor**, corre 100% sobre MySQL, lo cual es directamente compatible con la restricción de Plesk de Dante. |

### A.3 Cómo resuelve IGP cada capacidad

| Capacidad | Cómo lo resuelve IGP | Veredicto para Dante |
|---|---|---|
| Panel de administración (estructura, layout, navegación) | Panel a medida sin paquete de admin: rutas `Route::resource()` bajo prefijo `/admin` (`routes/web.php`), un controller por recurso en `app/Http/Controllers/Admin/*` con CRUD clásico (`index/create/store/edit/update/destroy`, sin `show`), vistas Blade en `resources/views/admin/{recurso}/{index,form}.blade.php` compartiendo `layouts/admin.blade.php` (sidebar + topbar hardcodeados, sin componente de navegación dinámica). Recarga completa de página en cada acción, cero JS reactivo salvo scripts sueltos. | **Adaptar.** El patrón "un controller + form + tabla" por tipo de contenido se copia, pero Dante usa Livewire 4 (definido en el stack del proyecto) en vez de recarga completa de página — se traduce la misma estructura de rutas/recursos a componentes Livewire con Form Objects, ganando UX sin reescribir el modelo de datos. |
| Autenticación y roles | Auth de sesión estándar de Laravel (`Auth::attempt`) en `App\Http\Controllers\Admin\AuthController`, con `spatie/laravel-permission` para roles. Solo dos roles hardcodeados y chequeados por nombre literal (`hasAnyRole(['admin', 'editor'])`) en `App\Http\Middleware\AdminMiddleware`, sin uso real de permisos granulares por módulo. Login con `throttle:5,1` en la ruta POST. Bandera `is_active` en `users` para desactivar cuentas sin borrarlas. **No tiene 2FA.** | **Adaptar.** Se copia el patrón sesión + Spatie Permission + `is_active`, pero conviene definir permisos granulares reales (no solo 2 roles binarios) y agregar 2FA dado que el sitio anterior fue comprometido — es una prioridad de seguridad explícita del proyecto. |
| Páginas y jerarquía | Tabla `pages` con campo `section` de valores fijos hardcodeados (`about`, `quality`, `training`, `home`) — **no hay jerarquía real ni árbol de páginas**, cada "página institucional" es prácticamente una ruta fija con su propio controller (`Frontend\PageController::about()`, `::quality()`, etc.). No hay padres/hijos ni slugs anidados. | **Adaptar.** El modelo columna-a-columna (`title`, `content`, SEO por página) se copia, pero para Dante hace falta jerarquía real (padre/hijo, breadcrumbs, slug anidado) porque `docs/02` §7 pide árbol de contenido, cosa que IGP no necesita al tener solo 3-4 páginas fijas. |
| Constructor de bloques de contenido | **No existe.** Cada tipo de contenido tiene un único campo `content` (`longText`) editado con TinyMCE como HTML plano — no hay bloques reordenables, repetibles ni tipados. | **No aplica / hay que construir de cero.** Si Dante necesita layouts de página con secciones distintas (hero, texto+imagen, cards, etc.) hay que diseñarlo desde cero; IGP no ofrece ningún patrón de referencia acá. |
| Editor de texto enriquecido y sanitización | TinyMCE 8 en el cliente (`resources/views/layouts/admin.blade.php`, `admin/documents/form.blade.php`). El HTML resultante se guarda **sin ninguna sanitización server-side** y se imprime sin escapar (`{!! $page->content !!}` en `resources/views/frontend/*`). No hay HTMLPurifier ni lista blanca de ningún tipo. | **Adaptar (parcial) + construir lo que falta.** Se copia TinyMCE como editor, pero la sanitización server-side con lista blanca es 100% responsabilidad de Dante — IGP no la tiene y es exactamente el tipo de brecha que comprometió el WordPress anterior. No copiar este punto tal cual. |
| Subida y conversión de medios | `App\Services\MediaService::upload()`: nombre de archivo aleatorio (`Str::uuid()`), tipo detectado por `getMimeType()` (basado en `finfo`), guardado en disco `public` de Flysystem dentro de `storage/app/public/media/{folder}/`. Tabla `media` central con soft deletes, referenciada por FK `media_id` desde el resto de tablas. **No reprocesa imágenes** (aunque `intervention/image` está en `composer.json`, no se usa en ningún archivo), **acepta subir SVG sin sanitizar** (`MediaController::store` permite `mimes:...,svg,...`). | **Adaptar.** Se copia la tabla `media` central + nombres aleatorios + disco fuera de la raíz servida directamente + soft deletes. Se descarta la falta de reprocesamiento y de sanitización SVG: Dante necesita reprocesar imágenes (usando `intervention/image`, que en IGP está instalado pero muerto) y sanitizar SVG antes de aceptarlo, o directamente no permitir SVG subido por editores. |
| Gestión de menús | **No existe tabla ni panel para menús.** La navegación del sitio público está hardcodeada en `resources/views/partials/navbar.blade.php` con `route()` a nombres de ruta fijos; la navegación del admin está hardcodeada en `layouts/admin.blade.php`. | **No aplica / hay que construir de cero** si el cliente necesita reordenar o agregar ítems de menú sin tocar código — cosa muy probable dado el KPI "el cliente publica sin llamarnos". IGP no ofrece ningún patrón de referencia acá. |
| Campos SEO por página | Cada tabla de contenido (`pages`, `products`, `services`, `posts`, `documents`) repite el mismo bloque de columnas: `meta_title`, `meta_description`, `og_title`, `og_description`, `og_image`, `no_index`. Sin componente ni trait compartido — se repite copy-paste en cada migración/modelo/controller. | **Adaptar.** El conjunto de campos SEO es correcto y se copia, pero conviene extraerlo a un trait/concern o tabla polimórfica (`morphOne`) en vez de repetir columnas en cada tabla, para evitar el copy-paste que tiene IGP. |
| Configuración global editable | `site_settings` (clave/valor/tipo/grupo) gestionada por `Admin\SettingsController` en pestañas (`general`, `contact`, `social`, `integrations`), con `App\Models\SiteSetting::get()/set()` cacheado 1h vía `Cache::remember` (driver `database`, sin Redis) y `App\Helpers\SettingsHelper` como fachada de acceso tipado. | **Copiar tal cual.** Es un patrón simple, robusto y ya compatible con la restricción de Plesk sin Redis — encaja directamente con lo que pide el KPI de autogestión del cliente. |
| Formularios y almacenamiento de envíos | Un único modelo `Contact` para contacto y cotización (`type: contact\|quote`), validado con Form Requests (`ContactRequest`, `QuoteRequest`) — los únicos Form Requests de todo el proyecto —, verificado con hCaptcha (`App\Services\HCaptchaService`) y notificado por correo (`Mail::send` con try/catch silencioso en logs). Listado y cambio de estado en `Admin\ContactController`. **Las rutas públicas `POST /contacto` y `POST /cotizacion` no tienen `throttle` ni ningún rate limiting** — solo el login de admin lo tiene. | **Adaptar.** Se copia el patrón (Form Request + captcha + tabla de envíos + panel de gestión con estados), pero hay que **agregar rate limiting** a los formularios públicos, algo que IGP no hizo y que es una exigencia explícita de este proyecto. |
| Integraciones (analytics, pixel, captcha) | IDs guardados como `site_settings` (`google_analytics_id`, `meta_pixel_id`, `hcaptcha_site_key`/`secret_key`) y expuestos por `SettingsHelper`; hCaptcha verificado server-side en `App\Services\HCaptchaService`. Sin Google Tag Manager ni gestor de consentimiento/cookies. | **Copiar tal cual** el patrón de IDs en `site_settings` + verificación server-side de captcha. Falta gestión de consentimiento de cookies, que no está en IGP y puede ser necesaria para Dante según legislación aplicable. |
| Caché e invalidación | `CACHE_STORE=database` (config/cache.php) — sin Redis. `SiteSetting` cachea 1h por clave y por grupo, invalidando con `Cache::forget()` puntual en `set()` o con `Cache::flush()` completo en `clearCache()` (usado tras guardar en `SettingsController`). No hay caché de vistas ni de queries de contenido (`pages`, `posts`, etc.). | **Copiar tal cual** el uso de caché de base de datos (compatible con Plesk sin Redis), pero revisar el uso de `Cache::flush()` — invalida TODO el caché de la app (incluida la caché de sesión si compartiera store), conviene invalidar por tags o por prefijo si el volumen de settings crece. |
| Buscador interno | **No existe.** No hay ninguna ruta, controller ni columna de búsqueda full-text; los filtros de listados admin son `LIKE` simples sobre 1-2 columnas (ej. `MediaController::index`). | **No aplica / hay que construir de cero** si Dante necesita buscador de contenido público — IGP no lo tiene. |
| Redirecciones | **No existe.** No hay tabla ni middleware de redirects 301, ni gestión desde el panel. | **No aplica / hay que construir de cero.** Es crítico para Dante porque viene de un dominio con URLs de WordPress que hay que redirigir (ver Fase 5, mapa 301 en `docs/01` §C.2) — IGP nunca tuvo esta necesidad porque nació limpio. |
| Registro de auditoría | **No existe.** No hay ningún paquete de activity log ni tabla de auditoría; los modelos no registran quién cambió qué ni cuándo, más allá de `created_at`/`updated_at` y el `user_id` de creación (no de última edición). | **No aplica / hay que construir de cero** si se necesita trazabilidad de cambios — recomendable dado el historial de compromiso de seguridad del sitio anterior. |
| Tests existentes | **Prácticamente ninguno.** `tests/Feature/ExampleTest.php` y `tests/Unit/ExampleTest.php` son los tests por defecto de un `laravel new`, sin modificar. No hay Pest instalado (`composer.json` no lo declara), usa PHPUnit 11 stock. Cero tests de los CRUDs del admin, cero tests de sanitización, cero tests de formularios públicos. | **No copiar — es la brecha más grande.** Dante exige "cada CRUD del panel se entrega con su test de Pest" (CLAUDE.md); IGP no tiene ningún precedente de testing que replicar, hay que construir la suite de Pest desde cero. |
| Despliegue en Plesk | No hay evidencia de scripts de deploy, `Procfile`, Supervisor config ni cron jobs en el repo (no hay `app/Console/Commands` custom, `routes/console.php` está vacío salvo el comando `inspire` por defecto). El `composer.json` trae script `setup` (`composer install` + `key:generate` + `migrate` + `npm build`) pensado para entorno de desarrollo/CI, no para Plesk específicamente. Uso de `CACHE_STORE=database` y `QUEUE_CONNECTION=database` confirma que el proyecto corre sin Redis ni Supervisor, consistente con la restricción de Plesk. | **Copiar el criterio (sin Redis/Supervisor), pero no hay script de deploy que copiar** — hay que documentar el procedimiento de deploy en Plesk para Dante desde cero (no hay precedente en el repo de IGP). |

### A.4 Qué se copia, qué se adapta, qué no aplica

**Se copia tal cual:**

- Configuración global editable (`site_settings` key/value/type/group + `SiteSetting::get()/set()` cacheado + `SettingsHelper`).
- Caché sobre driver `database` (sin Redis) y colas sobre driver `database` (sin Supervisor) — confirma que el enfoque de IGP es 100% compatible con la restricción de Plesk de Dante.
- Tabla `media` central con FK `media_id` desde el resto de tablas de contenido, soft deletes, nombres de archivo aleatorios (`Str::uuid()`), disco Flysystem separado de la raíz servida directamente.
- El patrón de IDs de integraciones (`google_analytics_id`, `meta_pixel_id`, claves de captcha) como filas de `site_settings`, con verificación server-side del captcha en un Service dedicado.
- El modelo de datos de `posts` (blog/novedades) y `gallery_items` (galería), tal cual, como tipos de contenido institucional válidos para Dante.
- `users` + `spatie/laravel-permission` + bandera `is_active` como base de autenticación y roles.
- Bloque de campos SEO por contenido (`meta_title`, `meta_description`, `og_*`, `no_index`), como conjunto de campos (no como implementación copy-paste, ver "se adapta").

**Se adapta:**

- Panel de administración: se copia la estructura de rutas resource + un controller por recurso, pero se traduce a componentes Livewire 4 en vez de recarga completa de página, y la validación pasa de `$request->validate()` inline a Form Requests dedicados (regla del proyecto: "Form Requests para toda validación").
- Autenticación y roles: se copia sesión + Spatie Permission + `is_active`, pero se diseñan permisos granulares reales (no 2 roles binarios) y se agrega 2FA.
- Páginas institucionales: se copia el conjunto de columnas (`title`, `content`, SEO), pero se reemplaza el campo `section` fijo por jerarquía real de páginas (padre/hijo, slug anidado, breadcrumbs) según `docs/02` §7.
- TinyMCE como editor de texto enriquecido: se mantiene en el cliente, pero se agrega sanitización server-side con lista blanca antes de persistir — IGP no la tiene.
- Subida de medios: se copia la tabla `media` y el patrón de nombre aleatorio, pero se agrega reprocesamiento real de imágenes (activando `intervention/image`, que en IGP está instalado pero sin usar) y sanitización/rechazo de SVG.
- Formularios públicos (`Contact`): se copia el modelo de datos y el patrón Form Request + captcha + panel de gestión con estados, pero se le agrega rate limiting (`throttle`) a las rutas públicas, ausente en IGP.
- Campos SEO: se copia el conjunto de campos, pero se extraen a un trait/concern compartido en vez de repetirlos columna por columna en cada migración como hace IGP.
- Servicios/documentos/certificaciones: se copia el patrón de CRUD + SEO + imagen destacada, pero el contenido específico (catálogo industrial) se reemplaza por equivalentes institucionales (oferta educativa, documentos institucionales, reconocimientos) si el cliente los necesita.
- Caché de settings: se copia el uso de `Cache::remember`, pero se reemplaza `Cache::flush()` global (usado por IGP en `clearCache()`) por invalidación más acotada si el volumen de datos cacheados crece.

**No aplica / se descarta:**

- `products` y `product_media`: catálogo comercial de metales, 100% específico de IGP Metales.
- `services` y `service_media` tal como están modelados (con `icon`, `attachment`, catálogo de servicios industriales) — si Dante necesita "oferta educativa" se construye un tipo de contenido nuevo inspirado en el patrón, no se reutiliza la tabla.
- `certifications` (certificaciones de calidad industrial ISO, etc.) tal cual — no hay equivalente directo en un sitio educativo salvo que el cliente pida "acreditaciones", y en ese caso se adapta el patrón, no la tabla.
- Campos de cotización en `contacts` (`product_service_interest`, `product_id`, `service_id`) — específicos de venta, no aplican a un formulario de contacto/admisión institucional.

**Falta en IGP y hay que construir para Dante:**

- **Constructor de bloques de contenido**: IGP no tiene ninguno (cada tipo de contenido es un único campo `content` de HTML plano). Si Dante necesita layouts de página con secciones distintas, se diseña desde cero.
- **Gestión de menús desde el panel**: la navegación en IGP está hardcodeada en Blade (`partials/navbar.blade.php`, `layouts/admin.blade.php`). Dante necesita que el cliente pueda editar el menú sin tocar código, dado el KPI de autogestión.
- **Sanitización HTML server-side con lista blanca**: IGP guarda y muestra el HTML de TinyMCE sin sanitizar (`{!! !!}` directo). Es la brecha de seguridad más relevante dado que el sitio anterior de Dante fue comprometido — hay que construirla, no hay precedente que copiar.
- **Sanitización/reprocesamiento de imágenes y bloqueo o sanitización de SVG**: `intervention/image` está en `composer.json` de IGP pero no se usa en ningún archivo; no hay ninguna verificación de MIME real más allá de `getMimeType()`, ni reprocesamiento, ni sanitización de SVG.
- **Rate limiting en endpoints públicos**: solo el login de admin tiene `throttle` en IGP; los formularios de contacto y cotización no tienen ningún límite de tasa.
- **2FA para el panel de administración**: no existe en IGP.
- **Jerarquía real de páginas** (árbol, padre/hijo, breadcrumbs): IGP usa un campo `section` con 3-4 valores fijos, no un modelo jerárquico.
- **Buscador interno**: no existe ninguna implementación de búsqueda full-text ni ruta de búsqueda pública en IGP.
- **Redirecciones 301 gestionables**: no existe tabla ni middleware de redirects en IGP — crítico para Dante por la migración desde WordPress (ver `docs/01` §C.2, mapa 301 de Fase 5).
- **Registro de auditoría / activity log**: no existe ningún paquete ni tabla de auditoría en IGP; solo `created_at`/`updated_at` y `user_id` de creación.
- **Suite de tests de Pest**: IGP no tiene tests reales (solo los `ExampleTest` por defecto de `laravel new`, con PHPUnit stock, sin Pest instalado). El CLAUDE.md de Dante exige un test de Pest por cada CRUD del panel — hay que construir la suite entera desde cero.
- **CRUD de categorías desde el panel**: en IGP, `categories` existe como tabla y modelo pero no tiene controller/rutas en el admin — solo se cargan por seeder. Si Dante necesita que el cliente administre categorías (de noticias, documentos, galería), hay que construir ese CRUD.
- **Gestión de consentimiento de cookies**: no existe en IGP, puede ser necesaria para Dante según la integración de analytics/pixel definida.
- **Documentación/procedimiento de despliegue en Plesk**: IGP no tiene ningún script ni guía de deploy específica para Plesk en el repo (el script `composer.json:setup` es genérico de desarrollo/CI).

---

## B. Análisis del manual de marca

> Insumo: `_insumos/02-marca-dante/MANUAL GUIA - DANTE 2023.pptx` (14 láminas, PPTX — **no es
> un PDF**, ver pregunta abierta #11). Los tokens extraídos se vuelcan a `docs/04-ui-design-system.md`.
> No hay logo vectorial suelto (SVG/AI/EPS) en el insumo: los únicos activos gráficos son los
> PNG/JPG embebidos dentro del propio PPTX (extraídos para este análisis). Ver pregunta abierta #12.

| Elemento | Qué dice el manual | Nota para web |
|---|---|---|
| Logotipo y variantes | Wordmark "DANTE" en dos líneas (DAN / TE), tipografía de palo seco condensada muy bold, con kerning ajustado a propósito (las letras se solapan/recortan entre sí como recurso de diseño — no es un error de render). Acompañado de un círculo sólido celeste (`#A6E4F8`) que hace de marca/sello, eco simplificado del isotipo espiral de la Società Dante Alighieri (logo institucional italiano, ver lámina 4/9). Dos versiones: **compacta** (solo el wordmark + círculo, para formatos chicos, redes, indumentaria/bordado) y **completa** (con banderas de Paraguay e Italia debajo, para documentos formales, cartelería, tarjetería). Hay además un lockup combinado "Instituto (IDA) + Società" que integra el isotipo espiral de la Società Dante Alighieri Paraguay junto al wordmark del colegio (láminas 2, 7) | Para la web: usar la versión **compacta** en header/favicon (espacios chicos), la **completa** en pie de página/documentos descargables. El wordmark bitmap no sirve para producción — hace falta el vectorial (pregunta abierta #12) |
| Área de resguardo y tamaño mínimo | No especificado en las 14 láminas | Definir en Fase 2 con criterio propio (regla general: altura de la "D" como margen mínimo) |
| Paleta primaria | Negro `#191919` · Celeste `#A6E4F8` · Amarillo `#FFED4E` · Gris claro `#F1F1F1` · Gris medio `#B2B2B2` (lámina 5) | **Solo el negro pasa contraste AA sobre blanco** (17.6:1). Celeste, amarillo, gris claro y gris medio son colores de acento/fondo, **no sirven como color de texto** — ver tabla de abajo |
| Paleta secundaria / acentos | Verde `#04884E` · Rojo `#C41536` · Azul `#26378C` (lámina 6) — el manual aclara explícitamente: **"solo para aplicar a las banderas del logo completo... no aplicar en elementos ni imágenes"** | Los tres pasan AA sobre blanco (4.5:1 / 6.0:1 / 10.5:1), pero el propio manual restringe su uso a la bandera del isotipo. No usar como paleta funcional de UI (botones, enlaces, estados) sin aprobación explícita del cliente — sería salirse del uso previsto por la marca |
| Tipografía principal | **No especificada por nombre.** El wordmark del logo usa una sans condensada bold (posiblemente custom/rotulada, no necesariamente una familia tipográfica completa con pesos). Lámina 12 solo lista "Tipografía" como ítem de una carpeta de insumos ("Logos / Tipografía / Manuales (ROMA)") sin adjuntar el archivo ni el nombre | **Bloqueante real, no solo de licencia** — ver pregunta abierta #6 (ampliada) |
| Tipografía secundaria | No especificada | — |
| Jerarquía tipográfica | No especificada (el manual es de identidad de marca, no de sistema editorial) | Se define de cero en Fase 2 con una tipografía sustituta (Google Fonts) hasta resolver #6 |
| Grilla y márgenes | No especificado | Se define en Fase 2 |
| Tratamiento fotográfico | No especificado — el manual no incluye fotografía de referencia, solo el logotipo y su paleta | Se define en Fase 2 |
| Iconografía | No especificada | Se define en Fase 2 |
| Tono de voz | No especificado — el manual es puramente de identidad visual (logo + color), no incluye lineamientos de copy | Fase 2 lo define desde cero con el cliente (ver `docs/03-copywriting.md`) |
| Usos prohibidos | No especificado explícitamente, salvo la restricción de la paleta secundaria mencionada arriba | — |

### B.1 Contraste AA — paleta primaria y secundaria sobre blanco

| Color | Hex | Ratio vs. blanco | AA texto normal (4.5:1) | AA texto grande (3:1) |
|---|---|---|---|---|
| Negro | `#191919` | 17.58 | ✔ pasa | ✔ pasa |
| Celeste | `#A6E4F8` | 1.39 | ✘ no pasa | ✘ no pasa |
| Amarillo | `#FFED4E` | 1.20 | ✘ no pasa | ✘ no pasa |
| Gris claro | `#F1F1F1` | 1.13 | ✘ no pasa | ✘ no pasa |
| Gris medio | `#B2B2B2` | 2.12 | ✘ no pasa | ✘ no pasa |
| Verde (secundario, solo bandera) | `#04884E` | 4.53 | ✔ pasa | ✔ pasa |
| Rojo (secundario, solo bandera) | `#C41536` | 5.99 | ✔ pasa | ✔ pasa |
| Azul (secundario, solo bandera) | `#26378C` | 10.46 | ✔ pasa | ✔ pasa |

**Conclusión:** de los 5 colores de la paleta primaria, solo el negro sirve como color de texto.
El celeste y el amarillo son la identidad visual de la marca (fondos, acentos, formas
decorativas — así se usan en las 14 láminas del propio manual: franjas, círculos, resaltados),
pero ninguno de los dos puede llevar texto encima sin oscurecerlo/aclararlo. Esto **no es un
defecto del manual** — es normal que una paleta de marca no esté pensada para accesibilidad web
— pero hay que resolverlo en la Fase 2 con variantes (ej. un celeste/amarillo más saturado u
oscurecido solo para texto, o directamente restringir esos colores a fondos y usar negro/blanco
para el texto encima). Se documenta acá para que no se repita el trabajo de detección en Fase 2.

**Vacíos del manual que hay que resolver en la Fase 2** (los manuales de marca casi nunca
cubren estados de interfaz, comportamiento responsive ni componentes web):

- Tono de voz — el manual no lo cubre en absoluto, se define de cero.
- Tratamiento fotográfico — sin referencia alguna en el manual.
- Toda la jerarquía tipográfica, grilla, espaciado y estados de componentes — el manual es de
  identidad de marca (logo + color), no de sistema de diseño digital.

### B.2 Material adicional recibido (2026-08-24)

El cliente sumó a `_insumos/02-marca-dante/` el logo vectorial y las guías oficiales de la
marca madre — resuelve las preguntas abiertas #11 y #12 (ver §E), no la #6 (tipografía).

- **`DANTE società dante alighieri LOGO.ai`** — logo vectorial oficial. El texto del wordmark
  está **convertido a trazado** (outlines), no como fuente editable — confirmado con `strings`
  sobre el archivo: cero declaraciones `BaseFont`/`FontFile`. Es justamente la práctica estándar
  para entregar un logo final, pero significa que **tampoco de acá se puede recuperar el nombre
  de la tipografía** del wordmark — ni siquiera la fuente original está disponible como dato.
- **"Linee guida all'uso del logo" — Dante.global, Società Dante Alighieri (Roma), Release 2.1,
  octubre 2021.** Es la guía internacional oficial de la marca madre (Dante es un comitato/scuola
  afiliado). Confirma exactamente la misma paleta y el mismo logo ya extraídos del PPTX local
  (`#191919` negro, `#A6E4F8` celeste — Pantone 2985 C —, `#FFED4E` amarillo — Pantone 113 C —,
  `#F1F1F1` gris, sin Pantone). Agrega, más allá de lo que ya teníamos:
  - **Área de resguardo:** 1 módulo = 1/2 de la altura de la letra "N" del wordmark, sin
    interferencias gráficas/textuales/fotográficas dentro de esa área alrededor del logo.
  - **Errores a evitar (explícitos):** no deformar proporciones, no cambiar el color de los
    elementos, no usar formas poligonales distintas de rectángulo/cuadrado, **nunca combinar el
    amarillo con el círculo** (el círculo es exclusivamente celeste/negro/blanco/escala de gris).
  - Existe una versión horizontal del wordmark además de la vertical (la vertical es la
    preferida; la horizontal se usa solo con poco espacio vertical disponible).
- **"Regolamento per l'utilizzo del logo" (IT/EN).** Documento legal, no de diseño: establece
  que la titularidad exclusiva de la marca es de la Società Dante Alighieri (Roma) y que el uso
  por comitati/escuelas afiliadas requiere convenio "Dante.global" vigente. No aporta nada al
  sistema de diseño — se asume que Dante Paraguay ya tiene ese convenio en regla, dado que usa
  el logo activamente en su sitio actual; no es un bloqueante de este proyecto, solo contexto.

**Conclusión sobre tipografía (pregunta #6):** revisado todo el material disponible — el PPTX
local, el logo vectorial y las guías oficiales internacionales — **en ningún lugar se define
una tipografía** para el sistema de marca de Dante/Società Dante Alighieri. No es un vacío del
insumo que se pueda resolver revisando más archivos: es una decisión que nunca se tomó a nivel
de marca. Sigue abierta, ahora con certeza de que hay que resolverla como decisión nueva (elegir
una tipografía web, típicamente de Google Fonts, con carácter condensado/bold acorde al
wordmark) en la Fase 2, no como un dato que falta encontrar.

---

## C. Inventario del WordPress actual

> Insumo: `_insumos/03-wordpress-actual/`
> ⚠️ Protocolo de seguridad de `CLAUDE.md` §2. Base legacy de solo lectura, cero ejecución de código.

### C.1 Cifras generales

> Salida de `scripts/wp-inventario.sql` (adaptado al prefijo real de tablas `tV4yL_`, no `wp_`
> — ver nota al pie de esta sección), corrido contra `dante_wp_legacy` (MariaDB local, aislada,
> puerto 3307, solo para este análisis). Volcado crudo completo en `docs/inventario-crudo.txt`
> (gitignorado, no se sube al repo).

| Métrica | Cantidad |
|---|---|
| Páginas publicadas | 26 |
| Entradas publicadas | 13 (+ 2 en papelera, 1 auto-borrador) |
| Borradores / papelera | 2 posts en trash, 2 snippets `wpcode` en draft |
| Tipos de contenido personalizados | `et_template`(11), `et_pb_layout`(8, Divi Builder), `et_header_layout`(3), `wpcode`(3, snippets de código), `et_footer_layout`(1), `et_theme_builder`(1), `wpcf7_contact_form`(1), `custom_css`(1), `wp_global_styles`(1) — todos son estructura interna del tema Divi/plugins, **no contenido editorial** |
| Categorías / etiquetas | 5 categorías de noticias, 9 menús de navegación (`nav_menu`), resto son taxonomías internas de plugins (Divi, WPCode) |
| Elementos de medios | 128 adjuntos: 104 JPEG, 22 PNG, 1 GIF, 1 PDF |
| Peso de `uploads/` | 384 MB (ya rescatado y limpiado en `_insumos/03-wordpress-actual/uploads/`, ver historial de esta sesión) |
| Menús y sus elementos | 8 menús, 52 elementos en total (`principal`: 25, resto entre 2 y 5 c/u) |
| Usuarios | 6 cuentas admin — **4 son backdoors del atacante, ver C.6** |
| Plugins activos | 10: `contact-form-7`, `fancy-lightbox`, `google-document-embedder`, `insert-headers-and-footers` (migrado a WPCode), `optimole-wp`, `otter-blocks`, `tinymce-advanced`, `wordfence`, **`wp-file-manager`**, `wpvivid-backuprestore` |
| Tema activo | `Divi` — y además existe una copia `Divi_disabled` en el servidor (ver C.6) |
| Formularios (plugin y cantidad) | Contact Form 7, 1 formulario ("Formulario pre-inscripción asunción" — pese al nombre, es un formulario de contacto genérico: nombre/email/asunto/mensaje, ver C.4) |

> **Nota de prefijo:** el dump real usa el prefijo de tablas ofuscado `tV4yL_`, no `wp_` como
> asume `scripts/wp-inventario.sql` por defecto (práctica de seguridad habitual). El script se
> corrió con el prefijo sustituido; si se vuelve a correr, ajustar de la misma forma o
> actualizar el script para que lo detecte solo.

### C.2 Inventario de contenido, página por página

> URLs construidas con `permalink_structure = /%postname%/` (confirmado en `tV4yL_options`) y
> `siteurl = https://dante.edu.py`. Todas las páginas tienen `padre = 0` (sin jerarquía en
> WordPress). **Tráfico:** no disponible — depende del acceso a Analytics/Search Console del
> cliente (pregunta abierta #8), se completa cuando llegue.

**Páginas (26):**

| URL actual | Título | Palabras | Última edición | Destino sugerido |
|---|---|---|---|---|
| /acerca-de-la-sociedad/ | Acerca de la Sociedad | 895 | 2023-11-09 | migrar |
| /administracion/ | Administración | 2024 | 2023-12-14 | migrar |
| /autoridades/ | Autoridades | 1822 | 2024-03-13 | migrar |
| /biblioteca-irene-borello-de-amodei/ | BIBLIOTECA "IRENE BORELLO DE AMODEI" | 1913 | 2024-02-27 | migrar |
| /certificacion-internacional/ | Certificación Internacional | 2470 | 2023-12-12 | migrar |
| /contacto/ | Contactos | 4661 | 2024-01-22 | migrar (revisar por qué tiene 4661 palabras — atípico para una página de contacto, posible contenido de más de una sección mezclado) |
| /cursos-de-italiano/ | Cursos de Italiano | 1078 | 2023-11-09 | migrar |
| /descarga-de-documentos/ | Descarga de Documentos | 563 | 2023-11-09 | migrar |
| /descarga-documentos-fndo-de-la-mora/ | Descarga documentos fndo de la mora | 455 | 2016-10-23 | revisar — posible duplicado/desactualizado de la anterior, sin editar desde 2016 |
| /enlaces-de-interes/ | Enlaces de interés | 1080 | 2024-03-13 | migrar |
| /estatutos-sociales/ | Estatutos Sociales | 567 | 2023-12-12 | migrar |
| /eventos/ | Eventos | 549 | 2023-12-27 | migrar |
| /formulacion-de-pre-inscripcion/ | Formulación de Pre-inscripcion | 1097 | 2023-12-14 | migrar |
| /formulario-de-pre-inscripcion-sede-fernando-de-la-mora/ | Formulario de Pre-Inscripción Sede Fernando de la Mora | 1086 | 2023-12-27 | migrar |
| /galeria/ | Galería | 765 | 2023-12-13 | migrar |
| /galeria-sede-fndo/ | Galeria-Sede-Fndo | 520 | 2016-10-30 | revisar — sin editar desde 2016, ¿sigue vigente la sede? |
| /historia/ | Historia | 3277 | 2023-12-27 | migrar |
| /inicio/ | inicio | 36 | 2025-10-06 | migrar → home (contenido casi vacío, probablemente la home real vive en el Divi Builder, no en `post_content` — revisar en la captura de pantallas) |
| /inscripciones-2/ | Inscripciones 2026 | 5206 | 2025-09-25 | migrar (la más reciente y voluminosa — contenido activo) |
| /instituto-de-lengua-y-cultura/ | Instituto de Lengua y Cultura | 736 | 2024-02-27 | migrar |
| /invitaciones/ | Invitaciones | 545 | 2023-12-27 | revisar — ¿contenido recurrente o de un evento puntual ya pasado? |
| /mision-vision-objetivos-y-valores/ | Misión, Visión, Objetivos y Valores | 2562 | 2024-03-14 | migrar |
| /noticias/ | Noticias | 552 | 2023-12-27 | migrar → listado de noticias (plantilla, no contenido editorial en sí) |
| /novedades/ | Novedades | 598 | 2023-12-27 | revisar — ¿duplicado funcional de "Noticias"? |
| /pagina-ejemplo/ | Página de ejemplo | 168 | 2016-09-29 | **descartar** — contenido de instalación por defecto de WordPress, nunca editado |
| /quienes-somos/ | QUIÉNES SOMOS | 1045 | 2024-03-14 | migrar |

**Entradas / noticias (13):**

| URL actual | Título | Fecha | Palabras | Destino sugerido |
|---|---|---|---|---|
| /la-scuola-dante-alighieri-celebra-su-129-aniversario-con-musica-y-arte/ | La Scuola Dante Alighieri celebra su 129.° aniversario... | 2024-03-13 | 612 | migrar |
| /mercado-navideno-a-la-italiana-en-asuncion/ | Mercado navideño a la italiana en Asunción | 2024-03-13 | 557 | migrar |
| /historico-presidente-de-italia-sergio-mattarella-visita-colegio-dante-alighieri/ | Histórico: Presidente de Italia, Sergio Mattarella, visita colegio Dante Alighieri | 2024-03-13 | 517 | migrar — nota alta relevancia institucional |
| /comunicado-05052020/ | COMUNICADO, 05/05/2020 | 2020-05-06 | 784 | revisar — comunicado puntual (pandemia), evaluar si archivar o descartar |
| /domenica-di-sapori-italiani-feria-familiar-de-la-gastronomia-italiana/ | Domenica di Sapori Italiani | 2017-11-17 | 226 | archivar/descartar — evento de 2017 |
| /concierto-de-piano-de-mateo-servian-sforza/ | Concierto de piano de Mateo Servián Sforza | 2017-09-12 | 111 | archivar/descartar — evento puntual de 2017 |
| /convenio-con-la-asociacion-de-ex-alumnos-del-colegio-dante-alighieri/ | Convenio con la Asociación de Ex Alumnos | 2017-08-09 | 198 | revisar — si el convenio sigue vigente, migrar a página institucional en vez de noticia |
| /semana-de-la-juventud-y-fiesta-de-la-primavera-tematica-hippiechic/ | Semana de la Juventud y Fiesta de la Primavera | 2016-10-25 | 15 | descartar — evento 2016, casi sin contenido |
| /invitacion/ | Invitación | 2016-10-25 | 27 | descartar — sin contenido sustancial |
| /festejo-del-dia-del-folklore-nivel-inicial/ | Festejo del Día del Folklore - Nivel Inicial | 2016-10-25 | 11 | descartar — evento 2016, sin contenido sustancial |
| /noticia-de-prueba-3/ | Noticia de prueba 3 | 2016-10-05 | 103 | **descartar** — contenido de prueba |
| /noticia-de-prueba-2/ | Noticia de prueba 2 | 2016-10-05 | 103 | **descartar** — contenido de prueba |
| /noticia-de-prueba-1/ | Noticia de prueba 1 | 2016-10-05 | 103 | **descartar** — contenido de prueba |

> **Captura del sitio en vivo (2026-08-24, sin ejecutar código, solo lectura vía HTTP):** el
> sitio sigue en línea. `sitemap.xml`/`robots.txt` confirman exactamente las mismas 26 páginas +
> 13 entradas de esta tabla (consistencia cruzada OK). Hallazgo de UX para la Fase 1: la home
> (`https://dante.edu.py/`) muestra en realidad el contenido de la página **"Historia"**, no el
> de la página `/inicio/` (que casi no tiene texto, 36 palabras) — la página "de portada" real
> del sitio y la que se llama "inicio" por slug **no son la misma**. 5 capturas de pantalla
> (home, contacto, página institucional, listado de noticias, detalle de noticia) en
> `_insumos/03-wordpress-actual/export/screenshots/`.
>
> Los "destino sugerido" de esta tabla son un primer corte editorial para agilizar la Fase 1
> (card sorting), no una decisión cerrada — el card sorting formal y la aprobación del cliente
> son los que fijan el mapa 301 definitivo. **Falta la columna "Tráfico"** hasta tener acceso a
> Search Console/Analytics (pregunta abierta #8) — sin eso no se puede confirmar con datos si
> páginas como "Invitaciones" o "Novedades" reciben visitas reales antes de descartarlas.

### C.3 Integraciones vigentes

> Revisado en `tV4yL_options` (todo lo que suele guardar IDs de tracking/captcha) y en los
> snippets de código personalizado (`wpcode`, antes gestionado por "Insert Headers and Footers").
> **No se encontró ningún ID de GA4, GTM, Meta Pixel ni configuración de captcha en ningún
> lugar de la base.** No es que estén ausentes de esta tabla por falta de revisión: se buscó
> explícitamente (`gtag`, `GTM-`, `fbq(`, `recaptcha`, `turnstile`) y no aparece nada.

| Integración | ¿Existe hoy? | ID / configuración | ¿Se mantiene? |
|---|---|---|---|
| Google Analytics | **No encontrado** | — | Se implementa de cero en Fase 6 |
| Google Tag Manager | **No encontrado** | — | Se implementa de cero en Fase 6 |
| Meta Pixel | **No encontrado** | — | Se implementa de cero en Fase 6 |
| Captcha | **No encontrado** — Contact Form 7 no tiene captcha configurado | — | Se implementa Turnstile de cero (CLAUDE.md §3) |
| Chat / WhatsApp | No encontrado en options ni en snippets | — | Preguntar al cliente si lo quieren (no estaba en el alcance original) |
| Mapa | No confirmado — puede estar embebido directo en el HTML de una página vía Divi Builder (no queda en `wp_options`, revisar en la captura de pantalla de "Contacto") | — | A confirmar en captura de pantallas |
| Redes sociales embebidas | No confirmado por esta vía — revisar capturas | — | A confirmar |
| Newsletter | **Sí** — plugin `wpvivid-backuprestore` no es de newsletter, pero existe tabla `tV4yL_wpmm_subscribers` (plugin de mailing list no listado entre los activos, posiblemente desactivado/residual) | Tabla con suscriptores — **puede contener datos personales de contactos reales**, tratar con cuidado si se exporta | Definir con el cliente si se mantiene la función de newsletter |
| Otro | `optimole-wp` (CDN/optimización de imágenes externo), `otter-blocks` (bloques Gutenberg extra), `fancy-lightbox`, `google-document-embedder` (visor de PDFs embebido) | — | Ninguno se mantiene — Laravel resuelve todo esto nativo (medios, PDFs, lightbox propio) |

### C.4 Formularios existentes

| Formulario | Ubicación | Campos | Destino de los datos | ¿Datos de menores? |
|---|---|---|---|---|
| Contact Form 7 — "Formulario pre-inscripción asunción" | Widget/página (a confirmar cuál la embebe) | Nombre, email, asunto, mensaje (genérico — **pese al nombre, no pide datos del alumno/menor**) | Mail a `wordpress@dante.webparaguay.com`, sin guardar en base propia (CF7 no persiste envíos por defecto) | No — los campos son del adulto que contacta, no del alumno |
| Formularios de "pre-inscripción" reales (páginas `/formulacion-de-pre-inscripcion/` y `/formulario-de-pre-inscripcion-sede-fernando-de-la-mora/`) | Contenido de página | **No identificado en esta base** — probablemente embeben un formulario externo (Google Forms u otro) directo en el HTML de la página, no un plugin de WP | Desconocido — depende de qué servicio externo usan | **Posible sí** — hay que confirmar con el cliente qué preguntan estos formularios (pregunta abierta #3) antes de decidir cómo se migran |
|---|---|---|---|---|
| | | | | |

### C.5 Línea base de SEO

Para poder demostrar después que no se perdió tráfico.

| Métrica (últimos 12 meses) | Valor |
|---|---|
| Sesiones orgánicas / mes | |
| Páginas con más tráfico (top 20) | |
| Consultas con más impresiones | |
| Posición promedio | |
| Backlinks relevantes | |
| Core Web Vitals actuales | |

### C.6 Hallazgos del compromiso

Qué se encontró en el dump o en los uploads que confirme o acote el incidente. Archivos
sospechosos, usuarios administradores extraños, contenido inyectado, enlaces de spam en el
contenido.

> ⚠️ **El compromiso parece haber estado activo hasta muy recientemente.** Uno de los hallazgos
> de abajo (usuario backdoor) tiene fecha de registro **2026-07-05** — 7 semanas antes de esta
> sesión (2026-08-23). Esto no es un incidente cerrado del pasado: hay que confirmar con el
> cliente si el WordPress sigue en línea ahora mismo y, si es así, recomendar sacarlo de línea
> o al menos bloquear el acceso público de inmediato, independientemente del cronograma de este
> proyecto. Ningún hallazgo de esta tabla se ejecutó ni se abrió como código — todo se leyó como
> texto plano vía consultas SQL de solo lectura contra la base aislada `dante_wp_legacy`.

| Hallazgo | Dónde | Acción |
|---|---|---|
| **Webshell activa con ejecución remota de comandos.** El snippet de código `wpcode` ID 1226 ("Untitled Snippet"), en estado **`publish` (activo)**, engancha `add_action('wp_footer', ...)` y ejecuta `shell_exec(escapeshellcmd($_GET['seonc']))` cuando la petición trae `?seonc=<comando>&pass=7wondersoftheworld` (contraseña hardcodeada en el propio snippet). Mientras el WordPress esté en línea con este snippet activo, **cualquiera que conozca la URL y la contraseña puede ejecutar comandos arbitrarios en el servidor** | Tabla `tV4yL_posts`, post ID 1226, `post_type='wpcode'` | **No migrar bajo ninguna circunstancia.** Confirmar si el sitio sigue en línea; si sigue, avisar al cliente/hosting para desactivarlo o bloquear el acceso ya, sin esperar al cutover de este proyecto |
| **4 de 6 usuarios administradores son cuentas backdoor del atacante**, no del personal de Dante: `xsh_odtoz1v` (`xsh_odtoz1v@proton.me`, registrado **2026-07-05** — el más reciente y el más alarmante), `rootadmin` (`admin@wordpress.com`, dominio no real, registrado 2025-10-27), `yoast-service` (`yoast-service@wordpress.com`, dominio no real, impersona el plugin Yoast SEO, registrado 2025-10-10), `aios` (`12rvm7s3i8s@hotmail.com`, probable impersonación de "All In One SEO", registrado 2025-10-07). Los 2 usuarios legítimos son `webmaster` (`wordpress@webparaguay.com`, 2016, el original) y `editor` (`7zdw3keobc@yahoo.com`, 2017 — email también raro para ser "el editor", a confirmar con el cliente si es una cuenta real) | Tabla `tV4yL_users` | Confirma que la política de `CLAUDE.md` §2 es correcta: **ningún usuario del WordPress se migra**. Los admin del panel nuevo se crean de cero. Preguntar al cliente si reconoce la cuenta `editor` |
| Plugin **WP File Manager** activo (`wp-file-manager/file_folder_manager.php`) — versión con vulnerabilidad RCE crítica muy conocida (CVE-2020-25213) si no está parchado. Es el candidato más probable como vector de entrada inicial, dado que permite subir/editar archivos del servidor directamente desde el navegador | `active_plugins` en `tV4yL_options` | No se migra ningún plugin. Registrar como hipótesis de vector de entrada en el informe final al cliente (no se puede confirmar con certeza sin logs de acceso del servidor, que no forman parte de este insumo) |
| Existe una copia del tema activo bajo el nombre **`Divi_disabled`** en el servidor, en paralelo al tema `Divi` realmente activo — patrón típico de persistencia de atacante (dejar una copia "apagada" del tema original mientras una versión modificada queda activa, o viceversa). Wordfence marcó decenas de archivos dentro de `Divi_disabled/` y de varios plugins (`optimole-wp`, `otter-blocks`) como "posiblemente malintencionados", **sin revisar (`status='new'`)** — Wordfence es propenso a falsos positivos en archivos de librerías legítimas (vendor de Composer/npm empaquetado), así que esto es una señal a investigar, no una confirmación | Tabla `tV4yL_wfissues` (cientos de filas, `status='new'` = nunca revisadas por nadie) | No se puede auditar más a fondo: el código PHP correspondiente ya fue borrado de `_insumos/` por protocolo de seguridad (ver historial de esta sesión) antes de este análisis de la base. Si se necesita certeza sobre este punto específico, haría falta pedir al cliente/hosting un respaldo de archivos aparte — no es bloqueante para continuar el proyecto |
| Un webshell **disfrazado de imagen** ya fue encontrado y eliminado en `uploads/` durante la carga de insumos de esta sesión: `2016/09/logo-dante-blanco.fw_.php` (nombre que imita un archivo de Adobe Fireworks, extensión real `.php`), junto con dos `.htaccess` sueltos. Ver también el hallazgo de la webshell activa en `wpcode` arriba — son dos mecanismos de persistencia distintos, lo que sugiere más de un acceso o más de un momento de compromiso | `_insumos/03-wordpress-actual/uploads/2016/09/` (ya eliminado) | Ya resuelto — documentado acá como evidencia del patrón de compromiso, no requiere acción adicional sobre los insumos |
| No se encontró contenido inyectado obvio (spam, enlaces ocultos, `<script>`/`<iframe>` extraños) en el texto de páginas y entradas — las secciones 11 y 11b de `scripts/wp-inventario.sql` no devolvieron resultados | `tV4yL_posts.post_content` | Igual conviene una revisión visual rápida del contenido migrado en la Fase 5, por las dudas — la ausencia de coincidencias de texto no descarta inyección vía JS ofuscado en otro lugar (tema, opciones) |

---

## D. Entorno de destino

| Item | Valor | Verificado |
|---|---|---|
| Servidor / panel | Plesk, `177.251.252.12` | |
| Versión de PHP disponible | | |
| Versión de MySQL/MariaDB | | |
| ¿Node/npm para el build? | | |
| ¿Redis? | | |
| ¿Supervisor? | | |
| ¿Acceso SSH? | | |
| ¿Composer instalado? | | |
| Límite de memoria PHP | | |
| Tamaño máximo de subida | | |
| Espacio en disco | | |
| Certificado SSL | Let's Encrypt vía Plesk | |
| Registros DNS actuales (A, MX, TXT) | web → webparaguay, mail → HostGator | |

---

## E. Preguntas abiertas para el cliente

> No asumir. Listar y preguntar.

| # | Pregunta | Por qué importa | Respuesta | Fecha |
|---|---|---|---|---|
| 1 | ¿El sitio necesita versión en italiano además de español? | Cambia el modelo de datos y el alcance por completo | **Sí.** Multiidioma español/italiano real (todo el contenido en los dos idiomas), con un interruptor en el panel para habilitar/deshabilitar el italiano. Ver ADR-002 | 2026-08-24 |
| 2 | ¿Quién va a administrar el sitio y cuántas personas? | Define roles y complejidad del panel | **4 roles:** administrador, editor general, editor de noticias/banners que además gestiona Google Ads/Analytics/Meta, y editor académico (solo información académica). Ver `docs/decisiones/` y memoria del proyecto | 2026-08-24 |
| 3 | ¿Qué formularios reciben datos personales de menores? | Obligaciones legales y de seguridad distintas | El cliente pidió que lo analice yo mismo. **Análisis:** revisé el contenido de las páginas `/formulacion-de-pre-inscripcion/` (ID 287) y `/formulario-de-pre-inscripcion-sede-fernando-de-la-mora/` (ID 597) en `dante_wp_legacy` — ambas usan el formulario nativo de Divi (`et_pb_contact_form`) con solo 3 campos: **Nombre, Teléfono, Email** (del adulto que contacta, no del alumno). La inscripción real de datos del menor (cédula, certificado de nacimiento, fotos, boletín) es **100 % presencial en papel**, según la circular de la página "Inscripciones 2026" (ID 283) — no pasa por el sitio web. **Conclusión: hoy el sitio no recolecta datos personales de menores online.** Nota aparte: el destino actual de esos formularios es `comunica@pressencia.com.py` — un dominio de una agencia externa, no de Dante — a confirmar si sigue vigente o hay que redirigir al nuevo panel/mail del colegio | 2026-08-24 (análisis propio) |
| 4 | ¿Hay contenido que quieren eliminar aprovechando el rediseño? | Reduce el volumen de migración | **No, por ahora no se elimina nada.** Nota: esto probablemente se refiere al contenido institucional real — quedan pendientes de confirmar los casos de contenido de prueba/instalación por defecto de WordPress ("Página de ejemplo", "noticia de prueba 1/2/3") marcados como candidatos a descarte en §C.2; por ahora se migran también salvo que el cliente indique lo contrario | 2026-08-24 |
| 5 | ¿Hay calendario académico o comunicados que se publican con frecuencia? | Puede requerir un tipo de contenido propio | **Sí**, confirmado. Se define como tipo de contenido propio en la Fase 1 | 2026-08-24 |
| 6 | El manual de marca (PPTX, 14 láminas) **no nombra ninguna tipografía** — ni siquiera para verificar licencia. ¿Qué fuente usa el colegio hoy en papelería/cartelería impresa? Si no hay ninguna definida, ¿aprueban una tipografía web nueva (ej. Google Fonts) que respete el carácter condensado/bold del logo? | Sin esto la Fase 2 no puede fijar la tipografía del sitio | El cliente subió el resto del material de marca disponible (ver #11/#12) pidiendo que lo revise de nuevo — ya revisado: **tampoco especifica tipografía**, ni siquiera las guías oficiales internacionales de Dante.global (Società Dante Alighieri, Roma). El logo está entregado con el texto convertido a trazado vectorial (sin fuente embebida), así que no hay forma de recuperar el nombre de la fuente de ningún archivo. **Resuelta** — se propuso Barlow Condensed + Barlow (Google Fonts, mismo ADN condensado del wordmark) en la Fase 2 (`docs/04-ui-design-system.md` §1) y el cliente la aprobó junto con el resto del diseño | 2026-08-24 |
| 7 | ¿Quién aprueba diseño y contenido, y en qué plazo? | Riesgo R4 | **Solo Leonardo, como desarrollador/responsable del proyecto** — sin un circuito de aprobación adicional del lado del cliente | 2026-08-24 |
| 8 | ¿Tienen accesos a Analytics, Search Console y Meta Business? | Sin esto no hay línea base ni integraciones | **No los tiene.** Pidió avanzar el SEO con las herramientas disponibles sin esperar esos accesos, y recordó que el panel debe permitir personalizar SEO por página/entrada — ya estaba previsto así en `PLAN.md` Fase 3 y Fase 6, sin cambios necesarios | 2026-08-24 |
| 9 | ¿Hay material fotográfico propio y actualizado? | Alternativa: sesión fotográfica o banco de imágenes | Por ahora se usa **solo lo que ya está en el sitio actual** (128 medios rescatados). Existe una biblioteca de materiales multimedia del colegio, pero sin acceso de desarrollador por ahora. **No limitar la cantidad de imágenes** en el diseño/panel por esta razón — dejar la biblioteca de medios abierta a crecer | 2026-08-24 |
| 10 | ¿Qué se hace con el WordPress viejo después del cutover? | Plan de baja | **Se baja todo — no hace falta guardar una copia aparte**, ya se cuenta con el dump SQL y los `uploads/` saneados en `_insumos/03-wordpress-actual/`. Esto ya satisface lo que pedía el checklist de la Fase 10 ("conservando una copia offline") | 2026-08-24 |
| 11 | El insumo entregado es un `.pptx`, no el PDF que se esperaba — ¿existe un PDF/versión más reciente o completa del manual de marca, o el PPTX es la fuente definitiva? | El PPTX solo cubre logo + paleta de color, no tono de voz, grilla ni tratamiento fotográfico — hay que saber si eso es "no existe" o "no se entregó" | **Resuelta.** El cliente sumó a `_insumos/02-marca-dante/`: el logo vectorial (`.ai`), las guías oficiales "Linee guida all'uso del logo" (Dante.global, Società Dante Alighieri, Release 2.1/oct-2021) y el "Regolamento per l'utilizzo del logo" (ES/EN). Son las guías internacionales oficiales de la marca madre — confirman exactamente los mismos colores y el mismo logo ya extraídos del PPTX local, y agregan área de resguardo y usos prohibidos (ver §B actualizada). Siguen sin cubrir tono de voz, grilla ni tratamiento fotográfico — eso se define en Fase 2 igual | 2026-08-24 |
| 12 | No hay logo vectorial (SVG/AI/EPS) en el insumo, solo bitmaps PNG/JPG embebidos en el PPTX (máx. ~2048 px de ancho). ¿Pueden conseguir el archivo vectorial original del logo? | Sin vectorial, el logo en el sitio (favicon, header en pantallas retina, impresos) sale de mala calidad o hay que redibujarlo | **Resuelta** — el cliente subió `DANTE società dante alighieri LOGO.ai`, el vectorial oficial. Listo para producción (favicon, header en cualquier resolución) | 2026-08-24 |
| 13 | Necesitamos acceso al Plesk destino (SSH o panel) para confirmar versión de PHP, si hay Redis/Supervisor, límites de memoria y de subida, y espacio en disco (`docs/01-analisis-descubrimiento.md` §D) | Sin esto no se puede cerrar la Fase 0 ni planificar con precisión la Fase 3/10 | Se accede **vía SSH + git**, el mismo patrón que en otros proyectos de webparaguay al servidor `177.251.252.12`. No hay credenciales guardadas en memoria (nunca se guardan) — se coordina el acceso puntual "en su momento", según el cliente. Sigue **abierta** hasta entonces | 2026-08-24 |
| 14 | ¿Sigue operando la sede Fernando de la Mora? Hay una página de documentos y una de galería específicas de esa sede, ambas sin editar desde 2016 | Define si "Documentos" y "Galería" del sitio nuevo necesitan filtro por sede o si se fusionan sin distinción (`docs/02-ux-arquitectura-informacion.md` §3) | Pendiente | |
| 15 | El sitio actual tiene contenido de instalación/prueba de WordPress sin ningún valor editorial: la página "Página de ejemplo" y las entradas "Noticia de prueba 1/2/3" (todas sin editar desde 2016, con títulos explícitos de prueba). Dado que el cliente pidió no eliminar contenido por ahora (pregunta #4), ¿autorizan dar de baja específicamente estas 4 piezas, o prefieren que se migren igual (archivadas, sin acceso desde el menú)? | Evita migrar contenido sin ningún valor institucional, pero respeta la instrucción de no asumir bajas | Pendiente | |
| 16 | Hay 4 entradas/páginas de eventos puntuales con muy poco contenido y sin fecha vigente: "Semana de la Juventud y Fiesta de la Primavera" (2016, 15 palabras), "Invitación" (2016, 27 palabras), "Festejo del Día del Folklore - Nivel Inicial" (2016, 11 palabras) e "Invitaciones" (2023, sin evento identificable en el título). ¿Se archivan sin acceso público o se mantienen visibles como histórico? | Define si quedan en el listado de Eventos/Noticias o solo accesibles por URL directa | Pendiente | |
| 17 | ¿Sigue vigente el convenio con la Asociación de Ex Alumnos del Colegio Dante Alighieri (publicado como noticia en 2017)? | Si sigue vigente, el contenido migra como página institucional permanente (`/institucion/convenio-ex-alumnos`); si no, queda archivado como noticia histórica | Pendiente | |
| 18 | El "Comunicado, 05/05/2020" (contexto de pandemia) — ¿se mantiene visible en el histórico de comunicados o se archiva fuera del listado público? | Afecta si aparece en `/vida-escolar/comunicados` con las demás piezas del nuevo tipo de contenido "Comunicado" | Pendiente | |
| 19 | La página `/contacto/` actual tiene 4661 palabras, muy por encima de lo típico para una página de contacto — sugiere contenido de más de una sección mezclado (posible por cómo Divi anida bloques). ¿Pueden compartir el contenido completo (o el HTML/captura) para decidir en Fase 2 qué queda en Contacto y qué se reubica en otra página? | Sin esto, Contacto no se puede redactar/depurar con criterio en la Fase 2 (copywriting) | Pendiente | |
| 20 | Los formularios de "pre-inscripción" (`/formulacion-de-pre-inscripcion/` y `/formulario-de-pre-inscripcion-sede-fernando-de-la-mora/`) envían hoy a `comunica@pressencia.com.py`, un dominio de una agencia externa, no de Dante. ¿Ese destino sigue vigente o hay que reemplazarlo por un mail/panel propio del colegio en el sitio nuevo? | Define el destino de notificación de los formularios de admisión en la Fase 3 | Pendiente | |
| 21 | La página "Administración" del sitio actual trae aranceles y cuotas del año lectivo **2024** (matrícula ~G. 1.318.000–1.974.000 según nivel, cuotas mensuales similares). ¿Cuáles son los valores vigentes para el año lectivo actual, por nivel? | No se puede publicar la página de Administración del sitio nuevo con cifras desactualizadas — ver `docs/03-copywriting.md` §2.1.2 | Pendiente | |
| 22 | Al migrar el contenido en la Fase 5 (`docs/07-migracion-wordpress.md`), 3 páginas viejas con destino "migrar" resultaron **inalcanzables**: sus URL nuevas (`/contacto`, `/documentos`, `/vida-escolar/galeria`) ya las sirve un controller dedicado de la Fase 4 (formulario de contacto, listado de documentos, listado de galerías), no una página de texto libre. El texto viejo (3963 palabras en Contacto — confirma lo que ya sugería la pregunta #19 —, 476 en "Descarga de documentos", 646 en "Galería") no se migró a ningún lado. ¿Se reescribe como introducción breve arriba del listado/formulario correspondiente, se descarta por completo (la nueva página ya se explica sola), o se migra igual como página institucional aparte con otro slug? | Sin esta decisión ese contenido queda huérfano en el WordPress legacy, que se da de baja al final del cutover (pregunta #10) | Pendiente | |

---

## F. Conclusiones de la Fase 0

**Volumen real de contenido:**

39 piezas de contenido editorial real (26 páginas + 13 entradas) + 128 medios (384 MB, ya
rescatados y saneados) — un volumen **chico**, muy por debajo de lo que suele preocupar al
arrancar un proyecto de migración. De esas 39, un primer corte editorial (§C.2) sugiere que
unas 8–10 son candidatas a descartar o fusionar (contenido de prueba, duplicados, eventos
puntuales de hace casi una década), así que el volumen que realmente migra con cuidado ronda
las 30 piezas. Esto reduce sensiblemente el riesgo R1 ("el volumen es mucho mayor de lo
estimado") — pasa a ser el riesgo inverso: hay tan poco contenido reciente que buena parte de
la Fase 2 (copywriting) va a ser reescribir/ampliar más que traducir lo existente.

**Enfoque del panel decidido (→ ADR-001):**

Filament 5, reutilizando el modelo de datos de IGP (no su capa Blade/controllers). Ver
`docs/decisiones/ADR-001-panel-admin.md`.

**Ajuste de estimación:**

Sobre el rango original de `PLAN.md` (214–298 h totales):

- **Fase 3 (backend/panel), original 48–64 h:** sin cambio grande por Filament en sí (el ahorro
  de no construir CRUDs a mano se compensa con la curva de aprendizaje del equipo si no lo usaron
  antes), pero **suma trabajo no estimado originalmente**: sanitización HTML server-side propia
  (no la resuelve Filament) y filtrado/sanitización de SVG — ninguna de las dos existía en IGP y
  son no negociables dado el antecedente de compromiso. Estimación ajustada: **52–68 h**.
- **Fase 5 (migración de contenido), original 16–24 h:** baja a **10–16 h** — 39 piezas de
  contenido es un volumen chico para el comando `dante:migrate-wp` y su verificación.
- **Fase 8 (seguridad), original 12–18 h:** sube a **16–22 h** — el hallazgo de una webshell
  activa y 4 usuarios backdoor (§C.6) no cambia el checklist de la fase, pero sí el peso que hay
  que darle a explicarle al cliente qué pasó y por qué el endurecimiento nuevo lo previene,
  además de coordinar con el hosting anterior si hace falta evidencia para ellos.
- **Total ajustado: ~204–278 h** (vs. 214–298 h original) — leve baja neta, con el detalle de
  que se mueve horas de "incertidumbre por volumen" a "incertidumbre por deuda de seguridad".
- Este ajuste sigue siendo preliminar: falta confirmar el entorno de Plesk (§D, pregunta #13) y
  el volumen real puede cambiar si el cliente pide sumar contenido nuevo (calendario académico,
  admisiones) al responder las preguntas abiertas.

**Riesgos nuevos detectados** (además de los ya listados en `docs/00-legajo-tecnico.md` §8):

- **R11 (nuevo, Crítico):** el WordPress actual tiene una webshell con ejecución remota de
  comandos **activa a la fecha de este análisis** (2026-08-24), no solo un compromiso histórico.
  Esto es un riesgo operativo inmediato para el cliente, independiente del cronograma de este
  proyecto — requiere acción fuera del alcance de las fases (ver conversación de esta sesión).
- **R12 (nuevo, Medio):** el manual de marca no nombra ninguna tipografía ni incluye vectorial
  del logo — ya cubierto por R7 en `docs/00-legajo-tecnico.md`, pero se confirma que el vacío es
  más amplio de lo que ese riesgo original preveía (no es solo "licencia", es "no hay definición
  en absoluto").
- **R13 (nuevo, Bajo):** hay al menos dos páginas del sitio actual con URLs que sugieren
  contenido de una sede secundaria ("Fernando de la Mora") sin editar desde 2016 — a confirmar
  con el cliente si esa sede sigue operando (pregunta abierta, agregar si la respuesta lo amerita).

**Luz verde para Fase 1:** ☑ sí, con reserva — la Fase 1 (UX/arquitectura de información) no
depende de la tipografía definitiva ni del entorno de Plesk, y ya tiene lo que necesita: el
inventario de contenido completo (§C.2) y el modelo de tipos de contenido que surge de §A.2.
**No dar luz verde al cierre de la Fase 2** (diseño) hasta resolver las preguntas #6 y #12
(tipografía y logo vectorial) — sin eso no se puede fijar el sistema de diseño. El DoD estricto
de esta Fase 0 ("colores y tipografías oficiales" conocidos) queda parcialmente abierto por esa
misma razón: los colores están confirmados, la tipografía no.
