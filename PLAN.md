# PLAN.md — Plan de ejecución por fases

Estado global: **Fase 10 en curso — el sitio nuevo está EN PRODUCCIÓN en https://dante.edu.py
desde 2026-10-05** (contenido y medios migrados desde staging, SSL, cron). Falta cerrar:
correo SMTP (credenciales HostGator rechazadas, se retoma después), contraseña del admin,
integraciones reales (GA4/GTM/Meta/Turnstile), Search Console y monitoreo de la
primera semana. Detalle en `docs/12-deploy-plesk.md` §10–§11.
(Estado previo: Fase 9 — QA y testing ✅ cerrada 2026-08-25.)
2FA del panel rediseñado en esta sesión: pasó de TOTP obligatorio a **email, opt-in por
usuario** (ADR-003), pedido explícito del cliente. De paso se cerraron los 2 hallazgos
mayores que había dejado abiertos la Fase 9 (honeypot sin mensaje claro al rechazar un envío,
2FA sin test automatizado) — ver `docs/11-qa-testing.md` §8.
Quedan abiertas, no bloqueantes: preguntas #13 (acceso Plesk) de la Fase 0, #19 (contenido real
de Contacto por sede) y #21 (aranceles vigentes), alta en Search Console/Bing/Google Business
Profile y verificación de Rich Results/OG con URL pública (requieren dominio real), IDs y
credenciales reales de GA4/GTM/Meta/Turnstile (se cargan desde el panel → Integraciones cuando
el cliente las entregue), mejoras futuras de rendimiento (LCP de Home, AVIF, CSS crítico,
compresión/HTTP2/OPcache en Plesk real), pentest real con Strix (Docker y CLI ya listos, falta
solo una API key de LLM), verificación en dispositivos físicos reales y aceptación del cliente
en staging — todo esto se resuelve en la Fase 10, que ya arrancó.
Última actualización: 2026-08-25

> **Cómo se usa:** cada fase tiene un objetivo, un entregable escrito y una condición de
> cierre (*Definition of Done*). No se pasa de fase sin el entregable en `docs/` y el
> checklist tildado acá. Si una fase revela algo que invalida una decisión anterior, se
> escribe un ADR y se vuelve atrás — eso es normal y barato ahora, caro en producción.

Estimación total de referencia: ~~180–240 h~~ **238–328 h** de ingeniería para el equipo de 3
(ajustada al cerrar la Fase 0: volumen real de contenido + multiidioma español/italiano pedido
por el cliente, ver ADR-002 — ver `docs/00-legajo-tecnico.md` §7 para el detalle por fase).

---

## Fase 0 — Descubrimiento, inventario y lectura de insumos
**Objetivo:** saber exactamente qué hay hoy, qué se reutiliza de IGP y qué manda la marca.
**Entregable:** `docs/01-analisis-descubrimiento.md` + `docs/00-legajo-tecnico.md` completado
**Estimado:** 16–24 h

- [x] Confirmar que los tres insumos están en `_insumos/` (ver `_insumos/LEEME.md`) — cargados
      y saneados (ver hallazgos de seguridad en `docs/01-analisis-descubrimiento.md` §C.6)
- [x] **Leer el repo de IGP completo**: estructura de carpetas, modelo de datos, cómo resuelve
      el panel admin, el editor enriquecido, la gestión de medios, los menús, el SEO por página,
      las integraciones. Escribir el mapa en `docs/01-analisis-descubrimiento.md`
- [x] Listar qué de IGP se **copia tal cual**, qué se **adapta** y qué **no aplica**
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
- [x] **ADR-001**: enfoque del panel de administración (replicar IGP vs. Filament 5) — se eligió
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
      modelo de datos de IGP — `media`, `site_settings`, `categories`, roles — no su capa
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
      TinyMCE de IGP, ver `docs/05` §1). Sanitización server-side con lista blanca real
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

## Fase 5 — Migración de contenido ✅ cerrada 2026-08-25
**Objetivo:** todo el contenido del WordPress dentro del Laravel nuevo.
**Entregable:** `docs/07-migracion-wordpress.md`
**Estimado:** 16–24 h

- [x] Comando `artisan dante:migrate-wp` idempotente (verificado corriéndolo dos veces — mismos
      conteos), con conexión secundaria `wp_legacy` de solo lectura (usuario MySQL `wp_lector`,
      GRANT SELECT únicamente, ver `app/config/database.php`)
- [x] Mapeo `wp_posts`/`wp_postmeta`/`wp_term_relationships` → `Page`/`Post`, según el destino
      "migrar" ya decidido en `docs/01-analisis-descubrimiento.md` §C.2 (16 páginas, 3 noticias;
      "Destacada" en WP se usaba como flag, no categoría real, se mapeó a `is_featured`)
- [x] Limpieza del HTML: shortcodes de Divi, artefactos de Word, atributos/clases del tema —
      `App\Services\Migration\WpHtmlCleaner`, siempre pasa después por `HtmlSanitizer` (Fase 3)
- [x] Migración de `uploads/` con el filtrado de seguridad de `CLAUDE.md` §2 — 128 medios reales
      migrados de 1064 archivos totales, 936 descartados y registrados con motivo (variantes de
      tamaño autogeneradas, caché de plugins como `wp-file-manager-pro`, extensiones fuera de
      lista blanca) en `docs/07-migracion-wordpress.md`
- [x] Reescritura de URLs internas — incluye un bug real encontrado y corregido: enlaces
      relativos de Divi (`../historia/`) no resolvían contra el mapa de redirecciones
- [~] Metadatos SEO — **nada que migrar**: el WordPress viejo no tenía ningún plugin de SEO
      instalado (sin claves Yoast/RankMath en `wp_postmeta`). Los títulos/descripciones reales
      ya están en `docs/03-copywriting.md` §3 (Fase 2), se cargan manual desde el panel
- [x] Carga del mapa de redirecciones 301 — ya hecho en Fase 3 (`dante:import-redirects`),
      confirmado vigente (39 filas)
- [x] **Verificación**: conteos origen/destino (16/16 páginas, 3/3 noticias, 128/128 medios) +
      revisión manual de 6 páginas (37%) y las 3 noticias (100%) — sin artefactos de
      Divi/Word, sin imágenes rotas. Encontrados y corregidos 2 bugs reales: un enlace
      absoluto al dominio de staging viejo dentro de un bloque de texto, y un breadcrumb que
      enlazaba a una página padre en borrador (404 público) — ver `docs/07-migracion-wordpress.md`
      §Verificación y test de regresión en `tests/Feature/PageControllerTest.php`
- [x] Reporte final en `docs/07-migracion-wordpress.md`, generado automáticamente por el comando

**3 páginas con destino "migrar" no se migraron** — su URL nueva ya la sirve un controller
dedicado de la Fase 4 (`/contacto`, `/documentos`, `/vida-escolar/galeria`), crear una Page ahí
sería inalcanzable o pisaría la ruta real. **Resuelto** (pregunta #22 en
`docs/01-analisis-descubrimiento.md` §E): el contenido viejo se reescribió como introducción
breve en cada página nueva — el detalle completo de la decisión, página por página, está en
`docs/07-migracion-wordpress.md`.

**DoD:** no hay contenido del sitio viejo que no esté en el nuevo o justificado por escrito
como descartado. **Cumplido.**

---

## Fase 6 — SEO técnico e integraciones ✅ cerrada 2026-08-25
**Objetivo:** que el cambio de plataforma no cueste tráfico, y que el marketing tenga sus datos.
**Entregable:** `docs/08-seo.md`
**Estimado:** 12–16 h

- [x] Metaetiquetas por página desde el panel, con valores por defecto sensatos — incluye
      `canonical_url` de `Page`, que existía en la base desde la Fase 3 pero no estaba expuesto
      en el formulario ni en la vista (corregido)
- [~] Open Graph y Twitter Cards, con imagen por defecto de la marca — implementado completo;
      la imagen OG de marca (1200×630) no existe en los insumos de Fase 2, queda pendiente de
      diseño (no se generó una imagen nueva, por instrucción explícita). Ver `docs/08-seo.md` §2
- [~] **JSON-LD schema.org**: `EducationalOrganization`, `BreadcrumbList`, `Article` en noticias,
      `FAQPage` donde aplique — los 4 implementados, más `WebSite`+`SearchAction` en el inicio.
      `LocalBusiness` se decidió **no** usar (`EducationalOrganization` es el tipo correcto para
      una institución educativa). `Event` no aplica: no existe todavía un bloque/modelo de
      "evento" individual en el catálogo de 16 bloques — ver `docs/08-seo.md` §3
- [x] `sitemap.xml` dinámico + `robots.txt` — ambos dinámicos (sin archivo estático), con
      `lastmod` real y bloqueo automático de indexación fuera de `production`
- [x] URLs canónicas, jerarquía de `<h1>`–`<h6>` correcta, un solo `h1` por página — canónicas
      nuevas en esta fase, jerarquía de encabezados ya verificada en Fase 4
- [x] **Redirecciones 301** activas y verificadas una por una — las 39 filas del mapa, con test
      automatizado (`Redirects301VerificationTest.php`), sin cadenas ni 404s
- [x] **Google Analytics 4** vía Google Tag Manager, con ID configurable desde el panel — carga
      solo tras consentimiento; sin ID real del cliente todavía (pendiente de datos, no de código)
- [x] **Meta Pixel + Conversions API** (el lado servidor es lo que sobrevive a los bloqueadores)
      — con deduplicación por `event_id`; sin token real del cliente todavía (pendiente de datos)
- [x] **Captcha** en todos los formularios públicos — Cloudflare Turnstile, validado del lado
      servidor, sumado a honeypot + rate limit; sin claves reales de Cloudflare todavía (pendiente
      de datos, el captcha queda inactivo sin bloquear los formularios mientras tanto)
- [x] **Todos los IDs y credenciales de integraciones administrables desde el panel, con
      activar/desactivar por integración** — pedido explícito del cliente tras cerrar el resto de
      la fase: se creó `App\Models\IntegrationSetting` (fila única, cifra los dos campos
      realmente secretos con el cast `encrypted` de Eloquent) y la página
      `App\Filament\Pages\IntegrationSettings` ("Integraciones" en el panel), reemplazando el uso
      de `.env` para el token de Meta Conversions API y las claves de Turnstile. Ver
      `docs/08-seo.md` §6
- [x] **Banner de consentimiento de cookies** que efectivamente bloquea los scripts hasta
      aceptar — verificado que no hay ningún `<script src>` de terceros incondicional en el HTML
- [ ] Alta en Google Search Console y Bing Webmaster, envío del sitemap — requiere acceso al
      dominio/DNS del cliente, no se puede simular. Movido a Fase 10 (cutover)
- [ ] Google Business Profile verificado y enlazado — mismo caso, movido a Fase 10
- [~] Verificar que nada del sitio viejo quedó indexado apuntando a un 404 — verificado a nivel
      de código (las 39 redirecciones del mapa no dan 404); la verificación contra el índice
      real de Google solo se puede hacer con Search Console del dominio real (Fase 10)

**DoD:** el sitio pasa Rich Results Test, el sitemap está enviado y todas las 301 responden.
**Parcialmente cumplido:** las 301 responden y están verificadas (test automatizado). El
Rich Results Test real y el envío del sitemap a Search Console requieren una URL pública —
no aplican a este entorno de desarrollo, quedan para la Fase 10 (despliegue/cutover). Detalle
completo de qué quedó pendiente y por qué, en `docs/08-seo.md` §6–§8.

---

## Fase 7 — Rendimiento ✅ cerrada 2026-08-25
**Objetivo:** Core Web Vitals en verde, en móvil y con la conexión promedio de Paraguay.
**Entregable:** `docs/09-rendimiento.md`
**Estimado:** 10–14 h

- [x] **Objetivos**: LCP < 2.5 s, INP < 200 ms, CLS < 0.1, Lighthouse móvil ≥ 90 — cumplidos en
      institucional (LCP 2.1 s, Lighthouse 98) y noticia (LCP 2.4 s, Lighthouse 97). Home queda en
      Lighthouse 90 (cumple) pero LCP 3.6 s (no cumple el umbral de 2.5 s) — detalle y motivo en
      `docs/09-rendimiento.md` §8/§10
- [x] Imágenes: WebP ✅ (AVIF no, decisión documentada), tamaños responsivos, `srcset`/`sizes`
      reales agregados (no existían en ninguna vista — hallazgo real de esta fase), lazy loading
      salvo el LCP, dimensiones fijas (CLS = 0 en las tres plantillas)
- [x] Fuentes: autoalojadas (confirmado, sin CDN externo), `font-display: swap`, subconjunto
      latino, precarga reducida de 6 a 2 archivos (los críticos arriba del pliegue) — hallazgo
      real: se precargaban los 6 pesos configurados, compitiendo con la imagen del LCP
- [x] CSS y JS: purga de Tailwind v4 activa, cero jQuery, **hallazgo real corregido**: Livewire
      (126 KB) se cargaba en las 12 plantillas públicas aunque solo lo usa el buscador interno —
      ahora es opt-in por plantilla (`x-layouts.app` con prop `livewire`)
- [x] **Caché de respuesta** — implementada como caché de **consulta** (no de HTML completo, por
      el riesgo de servir un CSRF viejo en páginas con bloque de formulario, ver `docs/09` §6),
      con invalidación real al guardar/borrar `Page`/`Post` desde el panel. Home/listados con
      filtros quedan sin caché de consulta, decisión documentada (sin tags en `file`/`database`)
- [x] Caché de configuración, rutas y vistas en producción (`config:cache`, `route:cache`,
      `view:cache`) — corridos localmente sin errores
- [x] Eliminar consultas N+1 — encontradas y corregidas en `HomeController`, `PostController` y
      el bloque "listado de noticias" del constructor de páginas, con test de regresión
      (`DB::enableQueryLog()`)
- [x] Índices de base de datos — 4 índices nuevos en `pages`/`announcements`/`galleries`/
      `documents`, justificados contra consultas reales de los controllers públicos (migración
      `2026_08_25_030000_add_performance_indexes.php`)
- [ ] Compresión Gzip/Brotli y cabeceras de caché en Plesk — configuración de servidor real, no
      aplica al entorno de desarrollo (Herd/Valet). Documentado como checklist de Fase 10
- [x] Medición **antes y después**, con captura en `docs/09-rendimiento.md` y en
      `docs/rendimiento-capturas/` (Lighthouse HTML + JSON completos, 6 archivos)

**Hallazgo de seguridad real durante esta fase, no bloqueante pero relevante:**
`config('cache.serializable_classes')` (Laravel 13, default `false`) hacía que **toda** lectura
desde la caché de consulta con el driver `database` devolviera un objeto roto
(`__PHP_Incomplete_Class`) y tirara 500 — reproducido, confirmado con test y corregido con una
allowlist explícita de clases en vez de desactivar la protección por completo. Detalle en
`docs/09-rendimiento.md` §6.

**DoD:** PageSpeed Insights móvil ≥ 90 en home, una página institucional y un detalle de noticia.
**Cumplido con reserva:** Lighthouse móvil ≥ 90 en las tres (90/98/97), pero el LCP de Home queda
en 3.6 s (objetivo < 2.5 s) — no bloqueante para el DoD tal como está redactado (pide Lighthouse,
no LCP puntual), documentado como pendiente explícito en `docs/09-rendimiento.md` §10. Suite de
tests: 136/136 en verde (129 antes de esta fase + 7 nuevos), Pint y Larastan (nivel 5) sin
hallazgos.

**Cerrada 2026-08-25.** Quedan como **mejoras futuras**, explícitamente no bloqueantes, decisión
del usuario al cerrar: LCP de Home a < 2.5 s (hoy 3.6 s), AVIF además de WebP, reducir de 4 a
menos pesos de fuente precargados, CSS crítico inline, y la revisión de compresión
Gzip/Brotli/HTTP2/OPcache/CDN en el Plesk real — esta última no se puede hacer sin el servidor
de producción, se retoma en la Fase 10 (despliegue).

---

## Fase 8 — Seguridad ✅ cerrada 2026-08-25
**Objetivo:** que no vuelva a pasar lo del WordPress.
**Entregable:** `docs/10-seguridad.md`
**Estimado:** 12–18 h
**Herramienta:** `strix` (solo contra nuestro staging, con autorización escrita)

- [x] Cabeceras: CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`,
      `Permissions-Policy` — no existía ninguna antes de esta fase, verificadas con
      `curl -I` real. HSTS solo se envía bajo HTTPS (Fase 10), ver `docs/10-seguridad.md` §1
- [x] Panel admin: 2FA obligatorio (nativo Filament 5), sesiones con expiración, `throttle`
      en login (nativo, verificado), y ruta del panel no adivinable — confirmados.
      **Hallazgo real:** `.env` local con `DANTE_REQUIRE_2FA=false`, aceptado para
      desarrollo con segunda línea de defensa (log crítico si ocurre en producción).
      Política de contraseñas (mínimo 12 + verificación contra filtraciones) no existía,
      agregada en esta fase — ver `docs/10-seguridad.md` §2
- [x] Validación estricta de toda entrada (Form Requests) confirmada. Sanitización del HTML
      del editor con lista blanca en el servidor confirmada (ya cerrada en Fase 3), y los
      `{!! !!}` del proyecto auditados uno por uno — ver `docs/10-seguridad.md` §3
- [x] Subida de archivos: MIME real, tamaño máximo y nombres aleatorios ya cumplían.
      **2 hallazgos Altos reales corregidos en esta fase:** las imágenes no se
      reprocesaban (solo se copiaba el original) y el directorio de medios no tenía
      ninguna regla propia contra ejecución de PHP — ver `docs/10-seguridad.md` §4
- [x] `APP_DEBUG=false` confirmado como default de producción (verificación en servidor
      real queda para Fase 10), `.env`/`.git` no accesibles (`curl` → 404), sin listado de
      directorios. **Corregido:** `.gitignore` no cubría `.env.*` en general — ver
      `docs/10-seguridad.md` §5
- [x] Rate limiting: buscador y login ya cumplían. **Hallazgo Medio corregido:**
      formularios públicos tenían `throttle:5,1` (5/min, 60x más laxo que la política
      documentada de 3/hora) — corregido a `throttle:5,60`
- [x] **Respaldos automáticos** — `spatie/laravel-backup` **no estaba instalado**
      (hallazgo Alto). Instalado y configurado en esta fase (base + medios, diario,
      retención 30 días, cifrado, destino configurable) y **prueba de restauración real
      hecha y documentada** (2026-08-25, conteos de tablas/filas idénticos entre origen y
      restaurado) — ver `docs/10-seguridad.md` §7. Destino off-site real queda para
      Fase 10
- [x] Registro de auditoría del panel activo y consultable — ya existía de la Fase 3,
      confirmado con test
- [~] Monitoreo de disponibilidad — depende de un servicio externo real (ej. UptimeRobot),
      no se puede configurar sin dominio/cuenta del cliente. Documentado como pendiente
      explícito de Fase 10, no simulado
- [x] Proceso de actualización de dependencias: `composer audit` + `npm audit`, ambos en
      **0 vulnerabilidades**. Agregado `composer audit-deps`. Falta agendarlo mensual de
      forma real (CI en Fase 9 / cron de Plesk en Fase 10)
- [~] **Pentest con strix** — **no se pudo ejecutar en esta sesión**: Docker ya se dejó
      corriendo y el CLI de Strix (v1.5.3) está instalado, pero faltó una API key de LLM
      (Anthropic/OpenAI) que el usuario no tenía a mano en el momento. Documentado sin
      simular resultado, con los comandos exactos para correrlo apenas exista la key. En su
      lugar, revisión manual dirigida por el mismo checklist contra `dante-web.test`
      (autorizado por el usuario como sustituto temporal del staging que no existe
      todavía) — 9 hallazgos reales (4 Altos, 4 Medios, 1 Bajo), todos corregidos o
      mitigados salvo el pentest automatizado en sí — ver `docs/10-seguridad.md` §10
- [x] Verificado que `.git` no se sirve — `curl` → 404 en local; regla de servidor para
      Plesk real queda en el checklist de la Fase 10

**DoD:** cero hallazgos Críticos o Altos abiertos, y el backup se restauró con éxito al menos
una vez. **Backup restaurado con éxito, verificado con conteos (§7 de `docs/10-seguridad.md`).
Cero hallazgos Críticos.** Los 4 Altos encontrados en la revisión manual de esta sesión están
corregidos y re-verificados.

**Cerrada 2026-08-25**, con el pentest automatizado real de Strix contra un staging como
**pendiente explícito y no bloqueante** (decisión del usuario): faltó solo la API key de LLM en
esta sesión (Docker y el CLI ya están listos); se retoma apenas exista la key, o en la Fase 10
contra el Plesk real — lo que ocurra primero.

---

## Fase 9 — QA y testing ✅ cerrada 2026-08-25
**Objetivo:** encontrar los problemas antes que el cliente.
**Entregable:** `docs/11-qa-testing.md`
**Estimado:** 14–20 h
**Herramienta:** `playwright-cli` (⚠️ solo credenciales de prueba)

- [x] **Pest**: tests feature de cada CRUD del panel, de autenticación, de permisos por rol,
      de envío de formularios y de las rutas públicas principales — 192/192 en verde (137
      antes de esta fase + 55 nuevos), incluida la cobertura que faltaba de pre-inscripción, de
      autorización real de borrado por rol (no solo el botón oculto) y del 2FA por email
- [x] **Playwright E2E**: recorridos completos de las 5 tareas críticas de la Fase 1, en móvil
      y escritorio — 40/40, en Desktop Chrome/Firefox/Safari(WebKit) + Mobile Chrome/Safari
- [x] Cobertura mínima: 100 % de las rutas del panel tocadas por al menos un test — 47 rutas
      de `panel-dante-2026`, 15 recursos + auth, todos con al menos un test
- [x] **Accesibilidad**: axe sobre cada plantilla (13/13 sin violaciones), navegación completa
      por teclado (skip link, menú móvil, tab order de formulario), objetivo WCAG 2.1 AA — 3
      hallazgos reales encontrados y corregidos (contraste del 404, skip link sin foco, menú
      móvil cerrado igual tabulable). Prueba con lector de pantalla real (VoiceOver/NVDA)
      **no hecha** — requiere un operador humano, ver `docs/11-qa-testing.md` §3
- [~] **Compatibilidad**: Chrome, Firefox, Safari cubiertos con los motores reales de
      Playwright (Chromium/Firefox/WebKit). Edge no aporta cobertura distinta de Chrome (mismo
      motor). iOS Safari y Chrome Android **reales no disponibles en esta sesión** (sin
      hardware) — sustituidos por los proyectos móviles de Playwright, no equivalentes al
      dispositivo físico
- [x] Verificación de contenido: enlaces rotos (0), imágenes faltantes (0), datos de contacto
      correctos (verificados contra Fase 5/6) — ortografía completa no automatizada (ver
      `docs/11-qa-testing.md` §5)
- [x] Prueba de los formularios de punta a punta: llega el mail (`Notification::fake`), se
      guarda el registro, el captcha bloquea (Turnstile, 4 escenarios), el honeypot funciona —
      para **ambos** formularios (Contacto y Pre-inscripción, este último sin cobertura antes
      de esta fase)
- [ ] **Prueba de aceptación con el cliente** sobre staging, con lista de verificación firmada
      — no hecha, no hay staging ni cliente presente en esta sesión (Fase 10)
- [x] Registro y cierre de todos los defectos encontrados — `docs/11-qa-testing.md` §8, 9
      defectos reales: **7 cerrados** (3 de accesibilidad, 1 hueco de test de formulario, 1
      hueco de test de autorización, CI agregado, y los 2 mayores que habían quedado abiertos
      por decisión — honeypot sin mensaje claro y 2FA sin test automatizado, resueltos al
      reemplazar el 2FA por TOTP obligatorio por 2FA por email opt-in a pedido del cliente, ver
      ADR-003). Queda **1 menor abierto**, no bloqueante (structured data sin teléfono/dirección
      — bloqueado por dato pendiente del cliente, pregunta #19)

**DoD:** suite verde en CI, cero defectos bloqueantes abiertos, cliente aprobó en staging.
**Cumplido en lo que depende de este entorno:** CI en verde (`.github/workflows/ci.yml`),
cero defectos bloqueantes ni mayores abiertos. **Pendiente, no bloqueante:** la aceptación del
cliente en staging real no se pudo hacer (no hay staging desplegado todavía) — se hace en la
Fase 10, junto con la verificación en dispositivos físicos reales (iOS/Android).

**Cerrada 2026-08-25** por decisión del usuario, con esos dos pendientes explícitos movidos a
la Fase 10.

---

## Fase 10 — Despliegue y puesta en producción
**Objetivo:** el sitio nuevo en línea, sin ventana de caída perceptible.
**Entregable:** `docs/12-deploy-plesk.md`
**Estimado:** 10–14 h

> **Arrancada 2026-08-26, en curso.** Esta fase necesita acceso real al Plesk y al DNS del
> dominio (pregunta #13, abierta desde la Fase 0) para su mitad — no se puede simular ni
> ejercitar sin eso. Se adelantó todo lo que sí se puede dejar listo sin tocar un servidor
> real; el resto queda explícitamente bloqueado hasta tener las credenciales.

- [x] Staging en el Plesk (`dante.webparaguay.com`, desde 2026-08-26) — `noindex` y contraseña
      no re-verificados en esta sesión
- [x] Procedimiento de despliegue documentado y repetible — `scripts/deploy-plesk.sh` (nuevo:
      script real, no solo comandos en el doc), 9 pasos con manejo de errores (`trap` que
      levanta el sitio de mantenimiento si algo falla a mitad de camino)
- [x] Variables de entorno de producción — `app/.env.production.example` (nuevo), corregido
      contra el estado real del código (ya no lista GTM/Meta/Turnstile como variables de
      entorno, eso se movió al panel en la Fase 6 — el doc viejo había quedado desactualizado)
- [x] Cron de Plesk → `schedule:run` cada minuto — instalado en producción 2026-10-05
- [x] Colas: `queue:work --stop-when-empty --max-time=55` cada minuto vía cron — instalado
- [~] SSL Let's Encrypt + HTTP→HTTPS — verificado en producción (cert válido hasta 2026-11-22,
      `http://` redirige 301). www/no-www sin verificar
- [ ] **Congelamiento de contenido**: el cliente deja de editar el WordPress. Migración final
      del delta de contenido — pendiente de coordinar fecha con el cliente
- [x] **Cutover**: el registro A de `dante.edu.py` ya apuntaba a `177.251.252.12` al 2026-10-05
      (cambiado antes de la sesión de deploy). MX/correo intactos: `mail.dante.edu.py` →
      HostGator `192.185.52.135`
- [~] Verificación post-cutover: hecho SSL, sitemap (22 URLs, todas 200), robots, imágenes de
      `/storage`. **Pendiente:** formularios (dependen del SMTP), analytics, backups, 2FA,
      las 301 reales con muestra de URLs viejas (`/wp-login.php` da 404, sin redirección)
- [ ] Bajar el WordPress viejo, conservando una copia offline del sitio y la base — pendiente
      del cutover real
- [x] **Manual del cliente**: cómo usar el panel, en español, con capturas — hecho,
      `docs/manual-cliente/manual-panel-dante.md`, 9 capturas reales del panel local (login,
      escritorio, páginas, editor de bloques, noticias, medios, menús, formularios recibidos,
      2FA). **Recapturar las pantallas** una vez que el contenido de producción esté cargado
      (hoy se ven datos de prueba de QA en el listado). La sesión de capacitación grabada
      sigue pendiente — requiere agendar con el cliente presente
- [ ] Acuerdo de mantenimiento: qué cubre, con qué frecuencia se actualiza, a quién se llama —
      estructura del documento lista (`docs/12-deploy-plesk.md` §8), **faltan las decisiones
      comerciales** (precio, horas de soporte mensuales incluidas) que solo puede tomar
      Leonardo, no se inventan acá
- [ ] Monitoreo activo la primera semana — depende de que el sitio esté en línea

**DoD:** `dante.edu.py` sirve el sitio nuevo, el correo sigue funcionando, y el cliente sabe
usar su panel. **No cumplido — en curso.** Lo que dependía solo de código/documentación está
listo; lo que depende de acceso real al Plesk/DNS y de decisiones comerciales, no.

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
