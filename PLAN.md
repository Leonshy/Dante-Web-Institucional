# PLAN.md — Plan de ejecución por fases

Estado global: **Fase 0 — no iniciada**
Última actualización: (completar al cerrar cada fase)

> **Cómo se usa:** cada fase tiene un objetivo, un entregable escrito y una condición de
> cierre (*Definition of Done*). No se pasa de fase sin el entregable en `docs/` y el
> checklist tildado acá. Si una fase revela algo que invalida una decisión anterior, se
> escribe un ADR y se vuelve atrás — eso es normal y barato ahora, caro en producción.

Estimación total de referencia: **~180–240 h** de ingeniería para el equipo de 3.
Se ajusta al cerrar la Fase 0, cuando se sepa el volumen real de contenido del WordPress.

---

## Fase 0 — Descubrimiento, inventario y lectura de insumos
**Objetivo:** saber exactamente qué hay hoy, qué se reutiliza de IPG y qué manda la marca.
**Entregable:** `docs/01-analisis-descubrimiento.md` + `docs/00-legajo-tecnico.md` completado
**Estimado:** 16–24 h

- [ ] Confirmar que los tres insumos están en `_insumos/` (ver `_insumos/LEEME.md`)
- [ ] **Leer el repo de IPG completo**: estructura de carpetas, modelo de datos, cómo resuelve
      el panel admin, el editor enriquecido, la gestión de medios, los menús, el SEO por página,
      las integraciones. Escribir el mapa en `docs/01-analisis-descubrimiento.md`
- [ ] Listar qué de IPG se **copia tal cual**, qué se **adapta** y qué **no aplica**
- [ ] **Leer el manual de marca**: paleta, tipografías, logotipo y sus usos, grilla, tono
      de voz, aplicaciones. Extraer los tokens a `docs/04-ui-design-system.md`
- [ ] **Importar el dump del WordPress** a una base separada `dante_wp_legacy` (solo lectura).
      Correr `scripts/wp-inventario.sql` y volcar el resultado
- [ ] **Inventario de contenido**: cada página, entrada, categoría, menú, formulario, archivo
      adjunto y URL pública del sitio actual, con su estado (se migra / se reescribe / se
      descarta / se fusiona). Tabla en `docs/01-analisis-descubrimiento.md`
- [ ] Capturar el sitio actual (screenshots de cada plantilla + `sitemap.xml` + `robots.txt`)
      **sin ejecutar su código**
- [ ] Inventario de **integraciones vigentes**: qué IDs de GA4/GTM/Meta Pixel usa hoy, qué
      formularios existen y a dónde envían, qué captcha usa, qué plugins tienen función
      visible para el usuario final
- [ ] Relevar **SEO actual**: posiciones y páginas con tráfico (Search Console / Analytics del
      cliente), para no matar URLs que rinden
- [ ] Confirmar entorno de destino: versión de PHP del Plesk, si hay Redis, si hay Node para
      el build, límites de memoria y de subida
- [ ] Listar **preguntas abiertas para el cliente** (idioma, formularios con datos de menores,
      quién administra, cuántos usuarios del panel, calendario académico, etc.)
- [ ] **ADR-001**: enfoque del panel de administración (replicar IPG vs. Filament 5)
- [ ] Ajustar la estimación total con el volumen real de contenido

**DoD:** se puede responder sin abrir nada más — cuántas páginas se migran, qué patrón de
panel se usa, y cuáles son los colores y tipografías oficiales.

---

## Fase 1 — UX y arquitectura de información
**Objetivo:** la estructura nueva del sitio, no un calco del WordPress.
**Entregable:** `docs/02-ux-arquitectura-informacion.md` + wireframes
**Estimado:** 20–28 h
**Herramienta:** `ux-flow-designer`

- [ ] Definir **audiencias** y qué viene a buscar cada una (padres que evalúan el colegio,
      padres actuales, alumnos, docentes, aspirantes, ex-alumnos, prensa)
- [ ] **Tareas principales** por audiencia, ordenadas por frecuencia e importancia
- [ ] **Card sorting** del inventario de contenido de la Fase 0 → nueva arquitectura de información
- [ ] **Mapa del sitio nuevo** con jerarquía y profundidad máxima 3 clics
- [ ] **Estructura de navegación**: menú principal, menú de pie, navegación contextual, buscador
- [ ] **Flujos de usuario** de las 5 tareas críticas (ej.: "quiero saber cómo inscribir a mi
      hijo", "quiero ver el calendario", "quiero contactar a la secretaría")
- [ ] **Wireframes** (baja fidelidad) de cada plantilla única: home, página institucional,
      listado de noticias, detalle de noticia, sección de nivel educativo, contacto,
      resultados de búsqueda, 404
- [ ] Definir los **tipos de contenido** que van a existir en el panel y sus campos
      (esto alimenta directo el modelo de datos de la Fase 3)
- [ ] **Mapa de redirecciones 301**: URL vieja → URL nueva, para cada URL del inventario.
      Este archivo es crítico para no perder SEO
- [ ] Revisión de accesibilidad en wireframes (jerarquía de encabezados, orden de foco,
      objetivo mínimo 44×44 px en táctil)

**DoD:** cualquiera del equipo puede dibujar el sitio nuevo de memoria y sabe dónde cae cada
contenido viejo.

---

## Fase 2 — Copywriting y sistema de diseño (UI)
**Objetivo:** el texto real y el lenguaje visual, listos para maquetar.
**Entregables:** `docs/03-copywriting.md` + `docs/04-ui-design-system.md`
**Estimado:** 24–32 h
**Herramienta:** `impeccable` (correr `/impeccable init` en la carpeta antes de empezar)

### Copywriting
- [ ] Definir **tono de voz** a partir del manual de marca (¿formal institucional? ¿cercano?)
- [ ] Reescribir el contenido migrado: títulos, bajadas, cuerpos, CTAs, microcopy de
      formularios, mensajes de error, textos de estados vacíos
- [ ] **Títulos y meta descripciones SEO** de cada página (esto se carga después en el panel)
- [ ] Textos legales: política de privacidad, cookies, aviso legal. Si el sitio recibe datos
      de menores, esto no es opcional
- [ ] Alt text de todas las imágenes que se migran

### UI / Sistema de diseño
- [ ] **Tokens** extraídos del manual de marca: color (con sus variantes y estados), tipografía
      (escala modular), espaciado, radios, sombras, breakpoints → volcados a `tailwind.config.js`
- [ ] Verificar **contraste AA** de cada combinación de color de la marca. Si el manual propone
      una combinación que no pasa, documentarlo y proponer la variante accesible
- [ ] **Biblioteca de componentes**: botones, campos, cards, acordeón, tabs, breadcrumb,
      paginación, modal, alertas, navegación, pie, hero, galería, tabla de datos
- [ ] **Diseño de alta fidelidad** de cada plantilla de la Fase 1, con contenido real
- [ ] Versión **móvil** de cada plantilla (el tráfico institucional es mayoritariamente móvil)
- [ ] Estados: hover, focus visible, activo, deshabilitado, cargando, error, vacío
- [ ] **Plan de motion**: qué se anima, con qué duración y curva, y qué respeta
      `prefers-reduced-motion` (la implementación es Fase 4, acá se define el criterio)
- [ ] Iconografía y tratamiento fotográfico coherentes con la marca

**DoD:** el diseño está aprobado por el cliente y no queda ninguna pantalla por definir.

---

## Fase 3 — Backend Laravel y panel de administración
**Objetivo:** el motor y el panel donde el cliente carga todo.
**Entregable:** `docs/05-backend-modelo-datos.md` + código funcionando
**Estimado:** 48–64 h — **la fase más pesada**

- [ ] Instalar Laravel 13 en `app/`, configurar Pint, Larastan, Pest
- [ ] Implementar el patrón de panel decidido en ADR-001, **replicando IPG**
- [ ] **Modelo de datos** a partir de los tipos de contenido de la Fase 1: páginas, bloques
      de contenido, noticias/entradas, categorías, medios, menús, usuarios, configuración
      global, formularios y sus envíos, redirecciones
- [ ] **Constructor de páginas por bloques** si IPG lo tiene: que el cliente arme una página
      combinando secciones, no editando un campo HTML gigante
- [ ] **Editor de texto enriquecido** — el mismo de IPG. Sanitización del HTML en el servidor
      (lista blanca de etiquetas y atributos), nunca confiar en el editor del cliente
- [ ] **Gestión de medios**: subida, recorte, conversiones automáticas (WebP/AVIF, tamaños
      responsivos), alt text obligatorio, biblioteca reutilizable
- [ ] **Gestión de menús** desde el panel (arrastrar y soltar, jerarquía)
- [ ] **Campos SEO por página**: título, meta descripción, imagen OG, canonical, índice/noindex,
      slug editable
- [ ] **Gestión de redirecciones** desde el panel, precargada con el mapa 301 de la Fase 1
- [ ] **Configuración global** editable: datos de contacto, redes, horarios, IDs de analytics,
      textos del pie
- [ ] **Formularios**: contacto y los que releve la Fase 0. Envío por mail + almacenamiento en
      base + exportación a CSV. Honeypot + captcha + rate limit
- [ ] **Usuarios y roles** del panel: al menos administrador y editor
- [ ] **Auth del panel**: Fortify con 2FA, política de contraseñas, bloqueo por intentos
- [ ] **Registro de auditoría**: quién cambió qué y cuándo (indispensable después de un
      compromiso — el cliente tiene que poder ver el historial)
- [ ] **Buscador interno** del sitio
- [ ] Tests de Pest para cada CRUD del panel
- [ ] **Seeders** con la estructura base (páginas fijas, configuración, usuario admin inicial)

**DoD:** una persona no técnica puede crear, editar, despublicar y borrar cualquier contenido
del sitio desde el panel, sin ayuda.

---

## Fase 4 — Frontend
**Objetivo:** el sitio público, fiel al diseño y con motion.
**Entregable:** `docs/06-frontend.md` + código
**Estimado:** 32–44 h
**Herramienta:** `emil-design-eng` para el motion

- [ ] Layout base Blade + componentes Blade reutilizables espejo de la biblioteca de la Fase 2
- [ ] Maquetar cada plantilla, mobile-first
- [ ] Renderizado de bloques de contenido del constructor de páginas
- [ ] Imágenes responsivas (`srcset`, `sizes`), `loading="lazy"`, dimensiones explícitas para
      no generar CLS
- [ ] **Motion**: transiciones de entrada, scroll reveal, micro-interacciones, transiciones de
      página. Respetar `prefers-reduced-motion`. Nada que bloquee el hilo principal
- [ ] Estados vacíos, de carga y de error del lado público
- [ ] Navegación por teclado completa y `:focus-visible` en todo lo interactivo
- [ ] Páginas 404 y 500 con la identidad de la marca
- [ ] Verificación cruzada de navegadores y en dispositivos reales

**DoD:** el sitio se ve y se comporta como el diseño aprobado, en móvil y escritorio.

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
| 0 | | | |
| 1 | | | |
| 2 | | | |
| 3 | | | |
| 4 | | | |
| 5 | | | |
| 6 | | | |
| 7 | | | |
| 8 | | | |
| 9 | | | |
| 10 | | | |
