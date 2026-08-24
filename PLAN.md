# PLAN.md — Plan de ejecución por fases

Estado global: **Fase 4 — frontend ✅ cerrada 2026-08-24.** Arranca la Fase 5 (migración de
contenido real del WordPress viejo). Quedan abiertas, no bloqueantes: preguntas #13 (acceso
Plesk) de la Fase 0, exportación CSV/captcha de formularios (Fase 6), verificación cruzada en
dispositivos físicos (Fase 9).
Última actualización: 2026-08-24

> **Cómo se usa:** cada fase tiene un objetivo, un entregable escrito y una condición de
> cierre (*Definition of Done*). No se pasa de fase sin el entregable en `docs/` y el
> checklist tildado acá. Si una fase revela algo que invalida una decisión anterior, se
> escribe un ADR y se vuelve atrás — eso es normal y barato ahora, caro en producción.

Estimación total de referencia: ~~180–240 h~~ **238–328 h** de ingeniería para el equipo de 3
(ajustada al cerrar la Fase 0: volumen real de contenido + multiidioma español/italiano pedido
por el cliente, ver ADR-002 — ver `docs/00-legajo-tecnico.md` §7 para el detalle por fase).

---

## Fase 0 — Descubrimiento, inventario y lectura de insumos
**Objetivo:** saber exactamente qué hay hoy, qué se reutiliza de IPG y qué manda la marca.
**Entregable:** `docs/01-analisis-descubrimiento.md` + `docs/00-legajo-tecnico.md` completado
**Estimado:** 16–24 h

- [x] Confirmar que los tres insumos están en `_insumos/` (ver `_insumos/LEEME.md`) — cargados
      y saneados (ver hallazgos de seguridad en `docs/01-analisis-descubrimiento.md` §C.6)
- [x] **Leer el repo de IPG completo**: estructura de carpetas, modelo de datos, cómo resuelve
      el panel admin, el editor enriquecido, la gestión de medios, los menús, el SEO por página,
      las integraciones. Escribir el mapa en `docs/01-analisis-descubrimiento.md`
- [x] Listar qué de IPG se **copia tal cual**, qué se **adapta** y qué **no aplica**
- [x] **Leer el manual de marca**: paleta, tipografías, logotipo y sus usos, grilla, tono
      de voz, aplicaciones. Extraer los tokens a `docs/04-ui-design-system.md` — tipografía
      bloqueada, ver pregunta abierta #6
- [x] **Importar el dump del WordPress** a una base separada `dante_wp_legacy` (solo lectura).
      Correr `scripts/wp-inventario.sql` y volcar el resultado
- [x] **Inventario de contenido**: cada página, entrada, categoría, menú, formulario, archivo
      adjunto y URL pública del sitio actual, con su estado (se migra / se reescribe / se
      descarta / se fusiona). Tabla en `docs/01-analisis-descubrimiento.md` — falta cruzar con
      tráfico real (pregunta abierta #8)
- [x] Capturar el sitio actual (screenshots de cada plantilla + `sitemap.xml` + `robots.txt`)
      **sin ejecutar su código** — 5 capturas en `_insumos/03-wordpress-actual/export/screenshots/`,
      sitemap y robots.txt confirman el sitio en línea al 2026-08-24
- [x] Inventario de **integraciones vigentes**: qué IDs de GA4/GTM/Meta Pixel usa hoy, qué
      formularios existen y a dónde envían, qué captcha usa, qué plugins tienen función
      visible para el usuario final — ninguna integración de analytics/pixel/captcha configurada hoy
- [ ] Relevar **SEO actual**: posiciones y páginas con tráfico (Search Console / Analytics del
      cliente), para no matar URLs que rinden — bloqueado, requiere accesos del cliente (pregunta #8)
- [ ] Confirmar entorno de destino: versión de PHP del Plesk, si hay Redis, si hay Node para
      el build, límites de memoria y de subida — bloqueado, requiere acceso al Plesk
- [x] Listar **preguntas abiertas para el cliente** (idioma, formularios con datos de menores,
      quién administra, cuántos usuarios del panel, calendario académico, etc.) — 13 preguntas
      en `docs/01-analisis-descubrimiento.md` §E, **11 respondidas el 2026-08-24** (produjo
      ADR-002 multiidioma). Quedan abiertas #6 (tipografía) y #13 (acceso Plesk)
- [x] **ADR-001**: enfoque del panel de administración (replicar IPG vs. Filament 5) — se eligió
      Filament 5, ver `docs/decisiones/ADR-001-panel-admin.md`
- [x] Ajustar la estimación total con el volumen real de contenido — 204–278 h (antes 214–298 h),
      ver `docs/00-legajo-tecnico.md` §7 y `docs/01-analisis-descubrimiento.md` §F

**DoD:** se puede responder sin abrir nada más — cuántas páginas se migran, qué patrón de
panel se usa, y cuáles son los colores y tipografías oficiales.

---

## Fase 1 — UX y arquitectura de información ✅ cerrada 2026-08-24
**Objetivo:** la estructura nueva del sitio, no un calco del WordPress.
**Entregable:** `docs/02-ux-arquitectura-informacion.md` + wireframes
**Estimado:** 20–28 h
**Herramienta:** `ux-flow-designer`

- [x] Definir **audiencias** y qué viene a buscar cada una (padres que evalúan el colegio,
      padres actuales, alumnos, docentes, aspirantes, ex-alumnos, prensa)
- [x] **Tareas principales** por audiencia, ordenadas por frecuencia e importancia
- [x] **Card sorting** del inventario de contenido de la Fase 0 → nueva arquitectura de información
- [x] **Mapa del sitio nuevo** con jerarquía y profundidad máxima 3 clics
- [x] **Estructura de navegación**: menú principal, menú de pie, navegación contextual, buscador
- [x] **Flujos de usuario** de las 5 tareas críticas (ej.: "quiero saber cómo inscribir a mi
      hijo", "quiero ver el calendario", "quiero contactar a la secretaría")
- [x] **Wireframes** (baja fidelidad) de cada plantilla única: home, página institucional,
      listado de noticias, detalle de noticia, sección de nivel educativo, contacto,
      resultados de búsqueda, 404 — en `docs/wireframes/`
- [x] Definir los **tipos de contenido** que van a existir en el panel y sus campos
      (esto alimenta directo el modelo de datos de la Fase 3)
- [x] **Mapa de redirecciones 301**: URL vieja → URL nueva, para cada URL del inventario.
      Este archivo es crítico para no perder SEO — `docs/redirecciones-301.csv` (39 filas)
- [x] Revisión de accesibilidad en wireframes (jerarquía de encabezados, orden de foco,
      objetivo mínimo 44×44 px en táctil)

**DoD:** cualquiera del equipo puede dibujar el sitio nuevo de memoria y sabe dónde cae cada
contenido viejo. **Cumplido con reserva:** las 39 piezas del inventario tienen destino explícito
en el mapa 301, pero 3 de ellas (sede Fernando de la Mora, convenio ex-alumnos, contenido real
de Contacto) dependen de las preguntas nuevas #14, #17 y #19 en `docs/01-analisis-descubrimiento.md`
§E para fijarse en forma definitiva — no bloquean el inicio de la Fase 2, sí conviene resolverlas
antes de cerrarla (ver `docs/02-ux-arquitectura-informacion.md` §12).

---

## Fase 2 — Copywriting y sistema de diseño (UI) ✅ cerrada 2026-08-24
**Objetivo:** el texto real y el lenguaje visual, listos para maquetar.
**Entregables:** `docs/03-copywriting.md` + `docs/04-ui-design-system.md`
**Estimado:** 24–32 h
**Herramienta:** `impeccable` (correr `/impeccable init` en la carpeta antes de empezar)

### Copywriting
- [x] Definir **tono de voz** a partir del manual de marca (¿formal institucional? ¿cercano?)
      — `docs/03-copywriting.md` §1 (usted, dato en vez de adjetivo, 5 principios con ejemplos)
- [x] Reescribir el contenido migrado: títulos, bajadas, cuerpos, CTAs, microcopy de
      formularios, mensajes de error, textos de estados vacíos — `docs/03-copywriting.md` §2–§4.
      Cuerpo completo con contenido real (Autoridades, Administración, Biblioteca, Enlaces de
      interés, FAQ de Admisiones) extraído de `dante_wp_legacy` y reescrito en el tono definido
      — pregunta nueva #21 (aranceles vigentes) agregada a `docs/01` §E
- [x] **Títulos y meta descripciones SEO** de cada página (esto se carga después en el panel)
      — `docs/03-copywriting.md` §3, las 39 piezas reales del inventario
- [x] Textos legales: política de privacidad, cookies, aviso legal. Si el sitio recibe datos
      de menores, esto no es opcional — `docs/03-copywriting.md` §5. El sitio no procesa datos de
      menores online (confirmado en `docs/01` §E #3); los 3 textos son borrador de trabajo, **no
      publicables sin revisión legal y aprobación del cliente**
- [x] Alt text de todas las imágenes que se migran — `docs/03-copywriting.md` §6, como criterio
      (sin acceso visual a los 128 medios en esta sesión, se completa al cargar cada imagen real
      en el panel)

### UI / Sistema de diseño
- [x] **Tokens** extraídos del manual de marca: color (con sus variantes y estados), tipografía
      (escala modular), espaciado, radios, sombras, breakpoints → volcados a `tailwind.config.js`
      — `docs/04-ui-design-system.md` §1. Tipografía es propuesta (Barlow/Barlow Condensed),
      pendiente de aprobación del cliente (pregunta #6). El volcado real a `tailwind.config.js`
      se hace en Fase 3 (no hay proyecto Node corriendo todavía)
- [x] Verificar **contraste AA** de cada combinación de color de la marca. Si el manual propone
      una combinación que no pasa, documentarlo y proponer la variante accesible — resuelto por
      restricción de uso (celeste/amarillo nunca como texto), tabla completa en `docs/04` §1
- [x] **Biblioteca de componentes**: botones, campos, cards, acordeón, tabs, breadcrumb,
      paginación, modal, alertas, navegación, pie, hero, galería, tabla de datos — 21
      componentes con todos sus estados especificados en `docs/04-ui-design-system.md` §3
- [x] **Diseño de alta fidelidad** de cada plantilla de la Fase 1, con contenido real —
      `docs/alta-fidelidad/` (9 archivos, ver tabla en `docs/04-ui-design-system.md` §4)
- [x] Versión **móvil** de cada plantilla (el tráfico institucional es mayoritariamente móvil) —
      incluida en el mismo archivo que la versión escritorio, mismo patrón que los wireframes
- [x] Estados: hover, focus visible, activo, deshabilitado, cargando, error, vacío — definidos
      por componente en `docs/04` §3
- [x] **Plan de motion**: qué se anima, con qué duración y curva, y qué respeta
      `prefers-reduced-motion` (la implementación es Fase 4, acá se define el criterio) —
      `docs/04-ui-design-system.md` §5
- [x] Iconografía y tratamiento fotográfico coherentes con la marca — Lucide + criterio
      fotográfico documental en `docs/04-ui-design-system.md` §6

**DoD:** el diseño está aprobado por el cliente y no queda ninguna pantalla por definir. ✅
Cumplido 2026-08-24 — incluye la aprobación de la tipografía propuesta (pregunta #6 resuelta).
Quedan datos puntuales marcados `[PENDIENTE]` en los propios mockups (aranceles vigentes,
contacto, contenido migrado de algunas páginas) que no bloquean el inicio de la Fase 3.

---

## Fase 3 — Backend Laravel y panel de administración
**Objetivo:** el motor y el panel donde el cliente carga todo.
**Entregable:** `docs/05-backend-modelo-datos.md` + código funcionando
**Estimado:** 48–64 h — **la fase más pesada** — ✅ cerrada 2026-08-24

- [x] Instalar Laravel 13 en `app/`, configurar Pint, Larastan, Pest
- [x] Implementar el patrón de panel decidido en **ADR-001: Filament 5** (reutilizando el
      modelo de datos de IPG — `media`, `site_settings`, `categories`, roles — no su capa
      Blade/controllers)
- [x] **Modelo de datos** a partir de los tipos de contenido de la Fase 1 (`docs/02-ux-arquitectura-informacion.md`
      §7), con soporte **multiidioma ES/IT** (ADR-002): páginas, bloques de contenido,
      noticias/entradas, categorías, medios, menús, usuarios, configuración global (con el
      toggle de italiano), formularios y sus envíos, redirecciones — todas las tablas creadas;
      recursos de panel de Documentos/Comunicados/Calendario/Galería/Menús ya construidos
      (ver `docs/05-backend-modelo-datos.md` §9)
- [x] **Constructor de páginas por bloques** — implementado como columna `blocks` (JSON) en
      `pages` con `Filament\Forms\Components\Builder`. **16 de 16 bloques** del catálogo de
      `docs/02-ux-arquitectura-informacion.md` §8 construidos (galería, FAQ, video, testimonios,
      mapa, formulario, listado de comunicados, documentos y selector de sede sumados en esta
      entrega)
- [x] **Editor de texto enriquecido** — `Filament\Forms\Components\RichEditor` (se aparta de
      TinyMCE de IPG, ver `docs/05` §1). Sanitización server-side con lista blanca real
      (HTMLPurifier) antes de guardar, nunca se confía en el HTML del editor. **Hallazgo y
      corrección de seguridad en esta entrega:** `SanitizesPageBlocks` operaba sobre la forma
      plana `$block[$campo]` en vez de la forma real que persiste el Builder de Filament 5
      (`$block['data'][$campo]`), por lo que el HTML de los bloques **nunca pasaba realmente**
      por HTMLPurifier — solo por la sanitización genérica de Filament, que no bloquea imágenes
      de dominios externos. Corregido para recorrer `data` recursivamente (incluye repetidores
      como FAQ/testimonios); test de regresión agregado (`bloquea imágenes de dominios externos`)
- [x] **Gestión de medios**: subida con MIME real (`finfo`), nombre aleatorio, SVG sanitizado,
      conversión WebP + tamaños responsivos, alt text obligatorio en la UI, biblioteca
      reutilizable — recorte manual pendiente
- [x] **Gestión de menús** desde el panel — recurso de Filament con gestor de ítems (enlace a
      página interna, a noticia o URL manual), hasta 2 niveles, drag/orden manual
- [x] **Campos SEO por página**: título, meta descripción, imagen OG, canonical, índice/noindex,
      slug editable — implementado en Páginas y ahora también en Noticias (imagen destacada +
      título/descripción SEO por idioma + toggle de indexación)
- [x] **Gestión de redirecciones** desde el panel, precargada con las 39 filas reales del mapa
      301 de la Fase 1 (`dante:import-redirects`) + middleware público que las aplica
- [x] **Configuración global** editable: datos de contacto, redes, horarios, IDs de analytics,
      toggle de italiano — vía `SiteSettingResource`
- [~] **Formularios**: contacto y pre-inscripción. Envío por mail + almacenamiento en base.
      Honeypot + rate limit implementados y probados. Exportación a CSV y captcha real
      (Turnstile, Fase 6) pendientes
- [x] **Usuarios y roles** del panel: 4 roles reales — administrador, editor general, editor de
      noticias/banners + marketing (Google Ads/Analytics/Meta), editor académico
      (`docs/01-analisis-descubrimiento.md` §E #2) — permisos granulares por módulo, no roles
      binarios
- [x] **Auth del panel**: 2FA (TOTP) obligatorio nativo de Filament 5 en vez de Fortify (ver
      `docs/05` §1 por qué). Política de contraseñas y bloqueo por intentos: por definir
      explícitamente (hoy usa los defaults de Laravel/Filament, no configurados a medida)
- [x] **Registro de auditoría**: `spatie/laravel-activitylog` activo en todos los modelos
      editoriales vía trait `HasAuditing` — recurso de solo lectura ("Auditoría") ya disponible
      en el panel, solo visible para el rol administrador
- [x] **Buscador interno** del sitio — capa de backend lista: `App\Services\Search\SearchService`
      hace `LIKE` sobre título/extracto/contenido (es + it si `SiteSetting::italianEnabled()`) de
      Páginas, Noticias, Documentos y Comunicados **publicados**, y `GET /buscar?q=...` (form
      request con mínimo de 2 caracteres, `throttle:30,1`) devuelve JSON con tipo, título,
      extracto y URL pública (páginas y noticias resuelven URL con el mismo patrón que
      `MenuItem::resolvedUrl()`; documentos y comunicados aún no tienen ruta pública propia,
      viajan con `url: null`). Sin Scout/Elasticsearch — volumen del sitio no lo justifica.
      Pendiente para Fase 4: la vista/UI pública que consuma este endpoint (buscador visible,
      página de resultados, posible ruta de descarga para documentos)
- [x] Tests de Pest para los CRUD entregados (Páginas, Noticias, Categorías, Usuarios,
      Configuración global, Auditoría, Menús, Redirecciones, sanitización HTML (incluida la
      regresión de imágenes externas), subida de medios, formularios públicos, middleware de
      redirecciones, buscador interno, catálogo de 16 bloques) — **60/60 en verde**, Pint y
      Larastan (nivel 5) sin hallazgos
- [x] **Seeders** con la estructura base (páginas fijas del mapa del sitio, configuración
      inicial, usuario admin inicial con contraseña generada, roles y permisos)

**DoD:** una persona no técnica puede crear, editar, despublicar y borrar cualquier contenido
del sitio desde el panel, sin ayuda. **Cumplido.** Quedan dos ítems menores fuera del DoD
estricto, no bloqueantes: exportación CSV de formularios y captcha real (explícitamente
diferidos a Fase 6, `docs/05` lo documenta), y la política de contraseñas/bloqueo por intentos
sigue en los defaults de Filament (a definir a medida más adelante, no es un hueco de
seguridad — 2FA ya es obligatorio).

---

## Fase 4 — Frontend ✅ cerrada 2026-08-24
**Objetivo:** el sitio público, fiel al diseño y con motion.
**Entregable:** `docs/06-frontend.md` + código
**Estimado:** 32–44 h
**Herramienta:** `emil-design-eng` para el motion

- [x] Layout base Blade + componentes Blade reutilizables espejo de la biblioteca de la Fase 2
      — ver `docs/06-frontend.md` §1
- [x] Maquetar cada plantilla, mobile-first — 9 plantillas de `docs/alta-fidelidad/` + 3 bonus
      (comunicados/calendario/galería), ver `docs/06-frontend.md` §2. Verificado en navegador
- [x] Renderizado de bloques de contenido del constructor de páginas — 16/16, con un bug real
      de forma de datos encontrado y corregido en el cierre (`docs/06-frontend.md` §8 #5)
- [x] Imágenes responsivas (`srcset`, `sizes`), `loading="lazy"`, dimensiones explícitas para
      no generar CLS
- [x] **Motion**: scroll reveal (IntersectionObserver + clase `.js` de progressive enhancement,
      ver `docs/06-frontend.md` §4), feedback de presión en botones, acordeón con
      `grid-template-rows` (rehecho — antes usaba `x-show` sin transición), carga de imagen con
      `@starting-style`, contador de cifras. Menú móvil y header al scrollear ya venían
      correctos de la base. `prefers-reduced-motion` respetado globalmente (verificado por
      código; falta la prueba manual en dispositivo físico, Fase 9)
- [x] Estados vacíos, de carga y de error del lado público
- [x] Navegación por teclado completa y `:focus-visible` en todo lo interactivo — pasada
      ítem por ítem cerrada (`docs/06-frontend.md` §6). Encontrados y corregidos dos bugs
      reales: doble/cero `<h1>` según si la página tenía o no un bloque hero, y un crash del
      bloque hero cuando no tiene imagen cargada
- [x] Páginas 404 y 500 con la identidad de la marca
- [ ] Verificación cruzada de navegadores y en dispositivos reales — no aplica al entorno de
      desarrollo, requiere dispositivos físicos; no bloqueante, se retoma en Fase 9 (QA), que
      ya tiene este mismo ítem en su propio checklist

**DoD:** el sitio se ve y se comporta como el diseño aprobado, en móvil y escritorio.
**Cumplido.** Motion y accesibilidad verificados en navegador real (no solo tests). Única
excepción, no bloqueante: la verificación en dispositivos físicos queda para la Fase 9.

---

## Fase 5 — Migración de contenido
**Objetivo:** todo el contenido del WordPress dentro del Laravel nuevo.
**Entregable:** `docs/07-migracion-wordpress.md`
**Estimado:** 16–24 h

- [ ] Comando `artisan dante:migrate-wp` idempotente, con conexión secundaria de solo lectura
      a `dante_wp_legacy`
- [ ] Mapeo `wp_posts` / `wp_postmeta` / `wp_terms` → modelos nuevos, según el mapa de la Fase 1
- [ ] Limpieza del HTML de WordPress: quitar shortcodes, clases de tema, `<div>` vacíos,
      estilos en línea. Convertir a HTML semántico limpio
- [ ] Migración de `uploads/` **con el filtrado de seguridad de `CLAUDE.md` §2**. Registrar
      cada archivo descartado y por qué
- [ ] Reescritura de URLs internas dentro del contenido (viejas → nuevas)
- [ ] Migración de metadatos SEO (Yoast/RankMath: `_yoast_wpseo_title`, `_yoast_wpseo_metadesc`)
- [ ] Carga del mapa de redirecciones 301
- [ ] **Verificación**: contar registros origen vs. destino, revisar manualmente una muestra
      del 10 %, detectar imágenes rotas y enlaces muertos
- [ ] Reporte final: qué se migró, qué se descartó, qué necesita carga manual

**DoD:** no hay contenido del sitio viejo que no esté en el nuevo o justificado por escrito
como descartado.

---

## Fase 6 — SEO técnico e integraciones
**Objetivo:** que el cambio de plataforma no cueste tráfico, y que el marketing tenga sus datos.
**Entregable:** `docs/08-seo.md`
**Estimado:** 12–16 h

- [ ] Metaetiquetas por página desde el panel, con valores por defecto sensatos
- [ ] Open Graph y Twitter Cards, con imagen por defecto de la marca
- [ ] **JSON-LD schema.org**: `EducationalOrganization`, `BreadcrumbList`, `Article` en noticias,
      `FAQPage` donde aplique, `LocalBusiness` con dirección y horarios
- [ ] `sitemap.xml` dinámico + `robots.txt`
- [ ] URLs canónicas, jerarquía de `<h1>`–`<h6>` correcta, un solo `h1` por página
- [ ] **Redirecciones 301** activas y verificadas una por una
- [ ] **Google Analytics 4** vía Google Tag Manager, con ID configurable desde el panel
- [ ] **Meta Pixel + Conversions API** (el lado servidor es lo que sobrevive a los bloqueadores)
- [ ] **Captcha** en todos los formularios públicos — preferencia: Cloudflare Turnstile
      (gratis, mejor UX, menos fricción). Alternativa: reCAPTCHA v3
- [ ] **Banner de consentimiento de cookies** que efectivamente bloquee los scripts hasta
      aceptar (no un cartel decorativo)
- [ ] Alta en Google Search Console y Bing Webmaster, envío del sitemap
- [ ] Google Business Profile verificado y enlazado
- [ ] Verificar que nada del sitio viejo quedó indexado apuntando a un 404

**DoD:** el sitio pasa Rich Results Test, el sitemap está enviado y todas las 301 responden.

---

## Fase 7 — Rendimiento
**Objetivo:** Core Web Vitals en verde, en móvil y con la conexión promedio de Paraguay.
**Entregable:** `docs/09-rendimiento.md`
**Estimado:** 10–14 h

- [ ] **Objetivos**: LCP < 2.5 s, INP < 200 ms, CLS < 0.1, Lighthouse móvil ≥ 90
- [ ] Imágenes: WebP/AVIF, tamaños responsivos, lazy loading salvo el LCP, dimensiones fijas
- [ ] Fuentes: autoalojadas, `font-display: swap`, subconjunto de caracteres, precarga de la crítica
- [ ] CSS y JS: purga de Tailwind, división de código, diferir lo no crítico, cero jQuery
- [ ] **Caché de respuesta** de páginas públicas, con invalidación al publicar desde el panel
- [ ] Caché de configuración, rutas y vistas en producción (`config:cache`, `route:cache`, `view:cache`)
- [ ] Eliminar consultas N+1 (Laravel Debugbar en local, `preventLazyLoading` en desarrollo)
- [ ] Índices de base de datos en las columnas que se filtran y ordenan
- [ ] Compresión Gzip/Brotli y cabeceras de caché en Plesk
- [ ] Medición **antes y después**, con captura en `docs/09-rendimiento.md`

**DoD:** PageSpeed Insights móvil ≥ 90 en home, una página institucional y un detalle de noticia.

---

## Fase 8 — Seguridad
**Objetivo:** que no vuelva a pasar lo del WordPress.
**Entregable:** `docs/10-seguridad.md`
**Estimado:** 12–18 h
**Herramienta:** `strix` (solo contra nuestro staging, con autorización escrita)

- [ ] Cabeceras: CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`,
      `Permissions-Policy`
- [ ] Panel admin: 2FA obligatorio, sesiones con expiración, `throttle` en login, bloqueo por
      intentos fallidos, y ruta del panel no adivinable
- [ ] Validación estricta de toda entrada (Form Requests). Sanitización del HTML del editor
      con lista blanca en el servidor
- [ ] Subida de archivos: lista blanca de tipos MIME reales (no por extensión), tamaño máximo,
      nombres aleatorios, almacenamiento fuera de la raíz web, SVG sanitizados
- [ ] `APP_DEBUG=false` en producción, sin `.env` accesible, sin listado de directorios,
      sin `/storage` expuesto de más
- [ ] Rate limiting en formularios y endpoints públicos
- [ ] **Respaldos automáticos** (base + medios) con `spatie/laravel-backup`, fuera del mismo
      servidor, y **prueba de restauración documentada** (un backup no probado no es un backup)
- [ ] Registro de auditoría del panel activo y consultable
- [ ] Monitoreo de disponibilidad y alerta ante caída
- [ ] Proceso de actualización de dependencias: `composer audit` + `npm audit` mensual, agendado
- [ ] **Pentest con strix** contra el staging. Corregir todo lo Crítico y Alto antes de salir a
      producción. Documentar hallazgos y correcciones
- [ ] Verificar que el `.git` no queda expuesto en producción

**DoD:** cero hallazgos Críticos o Altos abiertos, y el backup se restauró con éxito al menos una vez.

---

## Fase 9 — QA y testing
**Objetivo:** encontrar los problemas antes que el cliente.
**Entregable:** `docs/11-qa-testing.md`
**Estimado:** 14–20 h
**Herramienta:** `playwright-cli` (⚠️ solo credenciales de prueba)

- [ ] **Pest**: tests feature de cada CRUD del panel, de autenticación, de permisos por rol,
      de envío de formularios y de las rutas públicas principales
- [ ] **Playwright E2E**: recorridos completos de las 5 tareas críticas de la Fase 1, en móvil
      y escritorio
- [ ] Cobertura mínima: 100 % de las rutas del panel tocadas por al menos un test
- [ ] **Accesibilidad**: axe sobre cada plantilla, navegación completa por teclado, prueba con
      lector de pantalla en las páginas principales, objetivo WCAG 2.1 AA
- [ ] **Compatibilidad**: Chrome, Firefox, Safari, Edge + iOS Safari y Chrome Android reales
- [ ] Verificación de contenido: enlaces rotos, imágenes faltantes, ortografía, datos de
      contacto correctos
- [ ] Prueba de los formularios de punta a punta: llega el mail, se guarda el registro, el
      captcha bloquea el bot, el honeypot funciona
- [ ] **Prueba de aceptación con el cliente** sobre staging, con lista de verificación firmada
- [ ] Registro y cierre de todos los defectos encontrados

**DoD:** suite verde en CI, cero defectos bloqueantes abiertos, cliente aprobó en staging.

---

## Fase 10 — Despliegue y puesta en producción
**Objetivo:** el sitio nuevo en línea, sin ventana de caída perceptible.
**Entregable:** `docs/12-deploy-plesk.md`
**Estimado:** 10–14 h

- [ ] Staging en el Plesk, idéntico a producción, con `noindex` y protegido por contraseña
- [ ] Procedimiento de despliegue documentado y repetible (script o Deployer)
- [ ] Variables de entorno de producción, `APP_KEY` propia, `APP_ENV=production`
- [ ] Cron de Plesk → `php artisan schedule:run` cada minuto
- [ ] Colas: si no hay Supervisor, `queue:work` vía cron con `--max-time`
- [ ] SSL Let's Encrypt + redirección forzada a HTTPS + www/no-www unificado
- [ ] **Congelamiento de contenido**: el cliente deja de editar el WordPress. Migración final
      del delta de contenido
- [ ] **Cutover**: cambiar solo el registro A/web. **No tocar los MX ni el registro de correo**
      — el mail sigue en HostGator
- [ ] Verificación post-cutover: SSL, 301s, formularios, analytics recibiendo datos, sitemap,
      backups corriendo
- [ ] Bajar el WordPress viejo, conservando una copia offline del sitio y la base
- [ ] **Manual del cliente**: cómo usar el panel, en español, con capturas. Y una sesión de
      capacitación grabada
- [ ] Acuerdo de mantenimiento: qué cubre, con qué frecuencia se actualiza, a quién se llama
- [ ] Monitoreo activo la primera semana

**DoD:** `dante.edu.py` sirve el sitio nuevo, el correo sigue funcionando, y el cliente sabe
usar su panel.

---

## Registro de fases cerradas

| Fase | Cerrada el | Horas reales | Notas |
|---|---|---|---|
| 0 | 2026-08-24 | — (sesión de IA) | Casi cerrada — quedan abiertas #6 (tipografía) y #13 (acceso Plesk), no bloqueantes |
| 1 | 2026-08-24 | — (sesión de IA) | Aprobada por el cliente. Preguntas #14–20 agregadas, no bloquean Fase 2 |
| 2 | 2026-08-24 | — (sesión de IA) | Aprobada por el cliente, incluida la tipografía (Barlow Condensed + Barlow) |
| 3 | 2026-08-24 | — (sesión de IA) | 16/16 bloques, Menús/Documentos/Comunicados/Calendario/Galería, buscador interno (backend), auditoría en panel. Se encontró y corrigió una brecha de sanitización HTML real durante el cierre |
| 4 | 2026-08-24 | — (sesión de IA) | 12 plantillas, 16 bloques renderizando, motion real (scroll reveal, acordeón, contador), accesibilidad verificada. 9 bugs reales encontrados y corregidos al probar en navegador (ver `docs/06-frontend.md` §8) |
| 5 | | | |
| 6 | | | |
| 7 | | | |
| 8 | | | |
| 9 | | | |
| 10 | | | |
