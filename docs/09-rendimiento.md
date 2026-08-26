# 09 — Rendimiento (Fase 7)

Estado: **medido antes/después, con hallazgos reales corregidos.** Metodología: Lighthouse
13.4.1 (`npx lighthouse`) contra `dante-web.test` (Herd), modo **móvil, 4G simulada
(`--throttling-method=simulate`), CPU 4× lenta** (parte del preset `perf` de Lighthouse) — nunca
escritorio con fibra. Tres plantillas medidas: inicio (`/`), una página institucional
(`/institucion/quienes-somos`) y un detalle de noticia (`/noticias/la-scuola-dante-alighieri-...`).
Capturas HTML/JSON completas en `docs/rendimiento-capturas/` (`before-*` / `after-*`).

---

## 1. Objetivos

| Métrica | Objetivo | Home | Institucional | Noticia |
|---|---|---|---|---|
| LCP | < 2.5 s | 3.6 s ⚠️ (antes 5.0 s) | **2.1 s ✅** (antes 3.4 s) | **2.4 s ✅** (antes 4.2 s) |
| CLS | < 0.1 | 0 ✅ | 0.00003 ✅ | 0 ✅ |
| Lighthouse móvil | ≥ 90 | **90 ✅** (antes 79) | **98 ✅** (antes 91) | **97 ✅** (antes 86) |
| TBT (proxy de INP en lab) | bajo | 0 ms ✅ | 0 ms ✅ | 0 ms ✅ |
| Peso total de la página | < 1 MB | 589 KiB ✅ | 170 KiB ✅ | 220 KiB ✅ |
| Peticiones | < 50 | 19 ✅ | 13 ✅ | 13 ✅ |

INP real (interacción del usuario) no se puede medir en laboratorio sin un usuario interactuando;
el TBT (Total Blocking Time) en 0 ms en las tres plantillas es la mejor señal de laboratorio
disponible de que no va a haber bloqueos de mainthread perceptibles. Se confirma con Search
Console/CrUX real recién en Fase 10 (dominio público), ver §9.

**Único objetivo no cumplido: LCP de Home (3.6 s, objetivo < 2.5 s).** Explicado y con plan en
§3 y §8 — la plantilla de inicio tiene una imagen de hero grande servida por un carrusel
administrable desde el panel (`HomeSetting`), y en simulación de 4× CPU + 4G el propio "element
render delay" del navegador (tiempo entre que la imagen ya está descargada y el momento en que
el motor de render la pinta) fue inconsistente entre corridas (354 ms en una, 2072 ms en otra) —
ver §8 para el detalle y la causa raíz real que se corrigió (no era solo eso).

---

## 2. Línea base (antes de optimizar)

Fecha: 2026-08-25 · Herramienta: Lighthouse 13.4.1 CLI, `--form-factor=mobile
--throttling-method=simulate`, contra `dante-web.test` en local (Herd/PHP 8.3). No hay acceso a
PageSpeed Insights/CrUX real porque el sitio no está publicado todavía (dominio real recién en
Fase 10) — Lighthouse local con throttling simulado es el estándar de la industria para medir
"antes/después" en desarrollo y es la misma engine que usa PageSpeed Insights por debajo.

| Página | LCP | CLS | Lighthouse | Peso | Peticiones |
|---|---|---|---|---|---|
| Inicio | 5.0 s | 0 | 79 | 802 KiB | 24 |
| Institucional | 3.4 s | 0 | 91 | 404 KiB | 18 |
| Noticia | 4.2 s | 0 | 86 | 497 KiB | 18 |

**Comparación con el WordPress viejo:** `docs/01-analisis-descubrimiento.md` §C.5 quedó **vacía**
(sesión de Fase 0, bloqueada por falta de acceso a Search Console/Analytics del cliente, pregunta
abierta #8). No hay Core Web Vitals reales del WordPress viejo para comparar — lo único
disponible es la percepción cualitativa registrada en la Fase 0 (sitio en Divi, con plugins
pesados como `wp-file-manager-pro` y sin ningún plugin de optimización de imágenes instalado).
No se inventa un número: esta comparación queda pendiente y se retoma si el cliente entrega
accesos históricos antes del cutover (Fase 10).

| | WordPress viejo | Laravel nuevo |
|---|---|---|
| LCP móvil | sin datos (pregunta #8 abierta) | 2.1–3.6 s según plantilla (ver §8) |
| Lighthouse móvil | sin datos | 90–98 según plantilla |
| Peso | sin datos | 170–589 KiB |

---

## 3. Imágenes

- [x] Conversión automática a **WebP** al subir (`App\Services\Media\MediaUploadService`,
      calidad 80) — ya implementado en Fase 3
- [ ] **AVIF** — no implementado. `config('dante.media.image_conversions')` solo genera `webp`.
      Decisión: WebP ya cubre >95 % de navegadores reales del público (padres con celulares de
      gama media en Paraguay) y el ahorro adicional de AVIF sobre WebP-80 es marginal frente al
      costo de agregar un encoder AVIF a Intervention Image/GD en este momento — **queda como
      mejora futura**, no bloqueante para el objetivo de Lighthouse ≥ 90
- [x] Variantes responsivas generadas (400, 800, 1200, 1920 px — `config('dante.media.
      responsive_widths')`), ya existían desde Fase 3
- [x] **`srcset` + `sizes` real** — **no estaban implementados en ninguna vista** (hallazgo real
      de esta fase: cada `<img>` del sitio servía una única conversión fija, ej. `medium`/`w1200`,
      sin importar el tamaño real de pantalla). Se agregó `Media::srcset()` (a partir de las
      conversiones ya generadas) y se conectó en: hero del inicio (`hero-slider.blade.php`),
      tarjetas de noticias/secciones (`card.news`, `card.section`, y sus 6 puntos de uso:
      `home.blade.php`, `home/sections/{gallery,featured-pages,news}.blade.php`,
      `posts/index.blade.php`, `components/blocks/listado-noticias.blade.php`), la imagen de
      portada de página institucional (`pages/show.blade.php`) y la imagen del detalle de
      noticia (`posts/show.blade.php`)
- [x] `loading="lazy"` en todo menos el LCP — ya venía de Fase 4, verificado sin cambios
- [x] `fetchpriority="high"` en la imagen candidata a LCP de cada plantilla — ya estaba en el
      hero; se agregó donde faltaba (`pages/show.blade.php` cover, `posts/show.blade.php`
      imagen destacada)
- [x] `width`/`height` explícitos en todas — ya verificado en Fase 4, CLS = 0 en las tres
      plantillas confirma que sigue así
- [ ] Aviso en el panel si el cliente sube una imagen desproporcionada — no implementado, fuera
      de alcance de esta fase (es una mejora de UX del panel, no de rendimiento del sitio público)
- [x] Compresión con calidad 80 — ya configurado en `MediaUploadService` desde Fase 3

**Hallazgo real corregido — el hero del inicio no pintaba hasta que cargaba JavaScript:**
`hero-slider.blade.php` (usado en Home, que tiene más de un slide configurado) renderizaba
**todas** las diapositivas, incluida la primera, con `style="display:none"` fijo en el HTML, y
dependía de que Alpine.js cargara, parseara y evaluara `x-show="active === 0"` para recién
mostrar la primera imagen (candidata real a LCP). Bajo 4× CPU esto agregaba hasta ~2 s de
"element render delay" puro (medido con el insight `lcp-breakdown-insight` de Lighthouse, antes
y después). Corregido: la primera diapositiva ya no lleva `display:none` en el HTML — se pinta
apenas llega, sin esperar a JavaScript (las siguientes sí arrancan ocultas, no afectan el LCP).
Ver `resources/views/components/hero-slider.blade.php`.

---

## 4. Fuentes

- [x] Autoalojadas — confirmado: `vite.config.js` usa el proveedor `bunny()` de
      `laravel-vite-plugin/fonts`, que **descarga los archivos en tiempo de build** y los sirve
      desde `/build/assets/`, no hay ninguna petición a un CDN externo en tiempo de ejecución
      (verificado con el listado real de peticiones de red de Lighthouse)
- [x] `woff2` como formato principal (con `woff` de respaldo para navegadores viejos)
- [x] Subconjunto latino (`unicode-range` generado automáticamente por el plugin)
- [x] `font-display: swap` en todos los `@font-face` generados
- [x] Precarga (`<link rel="preload">`) de la fuente crítica — **corregido en esta fase**: antes
      se precargaban las **6** variantes configuradas (Barlow 400/500/600 + Barlow Condensed
      500/600/700) como recursos de alta prioridad, compitiendo por ancho de banda con la imagen
      del LCP bajo 4G simulada. Se limitó la precarga a las 2 que realmente se usan arriba del
      pliegue en cualquier plantilla: Barlow 400 (texto de cuerpo) y Barlow Condensed 700
      (encabezados) — `vite.config.js`, opción `preload: [{weight: ...}]` del plugin. Los 6
      archivos se siguen generando y sirviendo (ningún texto se queda sin su peso real), solo
      cambió cuáles se piden con prioridad alta desde el primer instante
- [~] Máximo 2 familias, máximo 4 pesos en total — 2 familias ✅, pero **6 pesos** en total
      (3 + 3), por encima del criterio de `docs/04-ui-design-system.md`. No se tocó porque
      reducir pesos reales requeriría revisar las 12 plantillas para ver qué se rompe
      visualmente — queda como pendiente para una futura revisión de diseño, no de código
- [ ] `fontaine` (fallbacks con métricas optimizadas) — el build de Vite avisa
      `"Optimized font fallbacks require the optional 'fontaine' package"`. No instalado (opcional,
      bajo impacto, no bloqueante) — mejora futura de CLS/perceived load, ya en 0 sin él

---

## 5. CSS y JavaScript

- [x] Purga de Tailwind activa en producción — Tailwind v4 vía `@tailwindcss/vite` (sin
      `tailwind.config.js`: v4 escanea el proyecto automáticamente), CSS final 52 KB sin
      comprimir / ~10.5 KB gzip para todo el sistema de diseño (21 componentes, Fase 2)
- [ ] CSS crítico en línea — no implementado; con el CSS ya en 10.5 KB gzip y sirviéndose con
      `<link rel="preload">` + `stylesheet`, el ahorro de inlinear sería marginal frente al
      riesgo de duplicar CSS en cada plantilla — no se hizo
- [x] **JS diferido / división de código — hallazgo real corregido**: el runtime de Livewire
      (~126 KB comprimidos, confirmado con el listado de peticiones de Lighthouse) se cargaba en
      **todas** las páginas públicas vía `@livewireStyles`/`@livewireScripts` en el layout base,
      pero de las 12 plantillas públicas **solo el buscador interno** (`/buscar`,
      `App\Livewire\SearchPage`) usa Livewire de verdad — ni el inicio, ni las páginas
      institucionales, ni las noticias tienen un solo `wire:` o `@livewire()`. Se agregó una prop
      `livewire` (default `false`) a `x-layouts.app` que controla si se cargan los assets de
      Livewire; solo `search/index.blade.php` la activa. Resultado: **0 KB de JS de Livewire** en
      home/institucional/noticia (antes 126 KB), sin afectar `/buscar`
- [x] Sin librerías duplicadas — confirmado, un solo bundle de Alpine (vía Livewire) en las
      páginas que lo necesitan, sin jQuery en ningún lado (`grep` de `package.json`/`composer.json`
      y del código servido, sin resultados)
- [x] Terceros (mapas, GTM, Meta Pixel, videos) cargados bajo demanda — ya resuelto en Fase 6:
      `<x-deferred-map>` (clic para activar) y `resources/js/consent.js` (scripts de analytics/
      marketing solo tras consentimiento real), sin cambios en esta fase
- [x] **Presupuesto de JS < 150 KB comprimidos en la carga inicial** — cumplido ampliamente tras
      la corrección de Livewire: **~1.6 KB** en home/institucional/noticia (antes 127.8 KB)

| Recurso | Peso comprimido (home, después) | ¿Justificado? |
|---|---|---|
| `app-*.js` (Alpine + JS propio, Vite) | 1.6 KB | Sí — scroll reveal, acordeón, menú móvil, consentimiento de cookies (Fase 4/6) |
| `app-*.css` (Tailwind purgado) | 10.5 KB | Sí — todo el sistema de diseño de las 12 plantillas |
| 2 fuentes precargadas (`woff2`) | ~45 KB | Sí — texto de cuerpo y encabezados, con `font-display: swap` |
| Livewire (`livewire.js`) | 0 KB en esta plantilla (126 KB solo en `/buscar`) | Sí, ahora sí — antes se cargaba en todas |

---

## 6. Backend

- [x] **Caché de respuesta de páginas públicas, invalidada al publicar** — decisión documentada:
      se implementó **caché de consulta** (el modelo `Page`/`Post` ya resuelto con sus relaciones,
      no el HTML final) en vez de caché de vista completa o de fragmentos. Motivo: el
      constructor de bloques de `Page` (Fase 3) permite insertar un bloque de formulario en
      **cualquier** página institucional, y ese formulario lleva un token CSRF que Blade debe
      generar fresco en cada respuesta — una caché de HTML completo serviría el mismo token a
      todas las visitas hasta que expire, rompiendo el envío del formulario con un 419 para
      todos menos el primer visitante. Cacheando solo el resultado de la consulta, `@csrf` se
      sigue generando en cada request y el beneficio de no pegarle a la base en cada lectura se
      mantiene igual. Implementado en `App\Services\Cache\PublicContentCache`, usado por
      `PageController::show` y `PostController::show`. Invalidación: evento `saved`/`deleted` de
      `Page`/`Post` (`booted()` de cada modelo) limpia la clave del slug actual **y** del slug
      original si se editó (para no dejar una entrada vieja). Un "no encontrado" nunca se
      cachea (evita 404 falsos si se publica una página nueva con un slug recién pedido). TTL de
      seguridad: 1 hora
- [x] Compatible con los drivers `file`/`database` (sin Redis confirmado en Plesk) — se usó
      `database` en local (`CACHE_STORE=database` en `.env`), sin tags (no soportadas por estos
      drivers), invalidación 100 % por clave explícita
- [~] Home, listado de noticias, documentos, comunicados y galería **no** tienen caché de
      consulta — decisión documentada: agregan filtros/paginación (`?categoria=`, `?sede=`,
      `?page=`) que multiplican las claves posibles, y su invalidación correcta (con drivers sin
      tags) requeriría enumerar todas las combinaciones vigentes cada vez que se publica/edita
      cualquier noticia/documento — desproporcionado para esta fase. Si el volumen de contenido
      crece mucho, revisar cuando haya Redis disponible (tags reales)
- [x] **Hallazgo de seguridad real durante la implementación**: Laravel 13 tiene
      `config('cache.serializable_classes')` en `false` por defecto (evita que `unserialize()`
      reconstruya objetos PHP arbitrarios desde la caché, para prevenir *gadget chain attacks* si
      se filtra el `APP_KEY` — antecedente real: el WordPress viejo estuvo comprometido,
      `docs/01-analisis-descubrimiento.md` §C.6). Como `PublicContentCache` cachea instancias de
      `Page`/`Post` (objetos, no arrays), con el valor por defecto **toda lectura desde caché
      devolvía un objeto `__PHP_Incomplete_Class` y tiraba 500** — reproducido y confirmado con
      un test antes de corregirlo. Se corrigió con una **allowlist explícita** en
      `config/cache.php` (`Page`, `Post`, `Media`, `Category`, `Carbon`) en vez de desactivar la
      protección con `true` (que reabriría la puerta que este control existe para cerrar). Test
      de regresión: `tests/Feature/PerformanceTest.php` ("sirve páginas y noticias cacheadas con
      el driver `database` real"), que fuerza `cache.default = database` — las demás pruebas de
      esta fase corren con `CACHE_STORE=array` (`phpunit.xml`) y **nunca hubieran detectado este
      bug**, porque el store `array` no serializa de verdad
- [x] `config:cache`, `route:cache`, `view:cache` — corridos localmente, sin errores, con la
      nueva `serializable_classes` (que solo contiene nombres de clase, se cachea sin problema)
- [ ] `event:cache` — no se usa (no hay listeners de eventos custom en el proyecto todavía)
- [x] Autoload optimizado (`composer install --optimize-autoloader --no-dev`) — queda para el
      script de deploy real (Fase 10), no aplica a un entorno de desarrollo con `--dev`
- [x] **N+1 reales encontrados y corregidos** (con `DB::enableQueryLog()`, ver tests):
  - `HomeController::index()` no precargaba `coverMedia` (páginas destacadas), `category`+
    `featuredMedia` (noticias) ni `media` (galerías) — cada tarjeta del inicio disparaba
    consultas extra por fila
  - `PostController::index()` y `PostController::show()` no precargaban `category`/
    `featuredMedia`
  - `App\View\Components\Blocks\NewsList` (bloque "listado de noticias" del constructor de
    páginas) tenía el mismo problema
  - Corregidos todos con `->with([...])` — `DocumentController` ya lo hacía bien desde antes,
    sin cambios ahí
- [x] Índices de base de datos — ver §7 más abajo
- [x] OPcache — depende de la configuración real de Plesk (Fase 10), no se puede verificar en
      este entorno; queda como checklist de despliegue
- [x] Sin trabajos pesados en el ciclo de la petición — confirmado, correo/CAPI ya usan colas
      desde Fase 3/6 (`ShouldQueue` en el envío de formularios)

**Consultas más lentas detectadas:** ninguna consulta individual lenta (el dataset de desarrollo
es chico) — el problema real no era una query lenta sino **repetición** de queries baratas (N+1),
cubierto arriba.

---

## 7. Índices de base de datos

Se revisaron las migraciones existentes contra las columnas que **de verdad** se filtran/ordenan
en los controllers públicos (no especulativo). `pages`/`posts` ya tenían índices compuestos desde
su creación en Fase 3. Se agregó una migración nueva (no se editó una ya corrida en producción):
`2026_08_25_030000_add_performance_indexes.php`.

| Tabla | Índice agregado | Consulta real que lo usa |
|---|---|---|
| `pages` | `(is_featured_home, status)` | `HomeController::index()` — sección "Páginas destacadas" |
| `announcements` | `(status, is_pinned, published_at)` | `AnnouncementController::index()` y la sección de comunicados del inicio |
| `galleries` | `(status, event_date)` | `GalleryController::index()` y la sección de galería del inicio |
| `documents` | `(status, is_current, published_at)` | `DocumentController::index()` |

No se tocaron `posts` (ya tenía `(status, published_at)`) ni `pages` en su índice original
`(status, site_section)`. Las claves foráneas (`category_id`, `featured_media_id`,
`cover_media_id`, etc.) ya llevan índice implícito de MySQL/InnoDB por la restricción `FOREIGN
KEY`, no hacía falta duplicarlo.

---

## 8. Servidor / Plesk

No aplica a este entorno de desarrollo (Herd/Valet, no Plesk) — queda documentado qué hace falta
configurar en el servidor real, para la Fase 10:

- [ ] Compresión Brotli (o Gzip como mínimo) — configurar en Plesk/nginx, no en el código Laravel
- [ ] HTTP/2 o HTTP/3 — depende de la configuración del vhost en Plesk
- [ ] Cabeceras de caché en estáticos (`Cache-Control: public, max-age=31536000, immutable`) para
      `/build/**` (Vite ya versiona por hash, así que es seguro poner `immutable`) — configurar
      en Plesk/nginx
- [x] Versionado de assets por hash — ya lo hace Vite (`app-k9KLQO69.css`, etc.)
- [ ] Keep-alive — configuración de servidor, no de código
- [ ] **¿CDN? — evaluado y descartado por ahora.** El diferencial de webparaguay es el servidor
      local en Paraguay: la latencia de red hacia el visitante típico (Asunción/Paraguay) ya es
      baja sirviendo directo desde el Plesk local. Una CDN genérica (con PoP más cercano en
      Brasil/Argentina en el mejor caso) agregaría un salto de red adicional sin necesariamente
      mejorar la latencia para el público real de este sitio. No se midió con datos reales
      porque el sitio no está en producción todavía — se revisita en Fase 10 con datos reales de
      RTT si el cliente lo pide, no antes

**Explicación adicional de por qué el LCP de Home (3.6 s) sigue por encima del objetivo:**
después de corregir el bug del `display:none` en el hero (§3) y los 6 preloads de fuentes
compitiendo por ancho de banda (§4), la variabilidad entre corridas de Lighthouse bajó pero no
desapareció del todo — es una plantilla con más peso real (carrusel con imagen de portada +
3 tarjetas con imagen + sección de noticias + galería, todas con imagen propia) que las otras
dos. El Lighthouse **de móvil llegó a 90** (objetivo cumplido), y el LCP bajó de 5.0 s a 3.6 s
(29 % más rápido), pero seguir bajando requeriría alguna de estas dos cosas, ninguna hecha en
esta fase por estar fuera del alcance de "servir bien lo que ya existe":
1. Reducir la cantidad de secciones/imágenes que carga el inicio por defecto (decisión de
   producto, no de rendimiento — hoy el cliente pidió ver todas las secciones desde `HomeSetting`)
2. Migrar a AVIF (§3) para bajar aún más el peso de cada imagen

---

## 9. Resultado final

Fecha: 2026-08-25 · Mismo método que la línea base (Lighthouse móvil, 4G simulada, CPU 4× lenta).

| Página | LCP | CLS | Lighthouse móvil | Peso | ¿Cumple? |
|---|---|---|---|---|---|
| Inicio | 3.6 s | 0 | **90** | 589 KiB | ⚠️ Lighthouse sí, LCP no (3.6 s vs 2.5 s) |
| Institucional | **2.1 s** | 0.00003 | **98** | 170 KiB | ✅ |
| Noticia | **2.4 s** | 0 | **97** | 220 KiB | ✅ |

**Capturas de Lighthouse (HTML + JSON) guardadas en:** `docs/rendimiento-capturas/`
(`before-home`, `before-institucional`, `before-noticia`, `after-home`, `after-institucional`,
`after-noticia`).

### Qué se corrigió, en el orden de ataque de CLAUDE.md (imágenes → fuentes → JS → backend/caché)

1. **Imágenes**: hero del inicio pintaba recién después de que cargara Alpine.js
   (`display:none` fijo en el primer slide) — corregido, ya no depende de JS para el primer
   pintado. `srcset`/`sizes` reales agregados donde no existían (todo el sitio servía una sola
   conversión fija sin importar el ancho de pantalla)
2. **Fuentes**: de 6 archivos precargados con `fetchpriority` alto a solo 2 (los que se usan
   arriba del pliegue), sin dejar de servir los 6 pesos reales
3. **JS**: Livewire (126 KB) se cargaba en todas las páginas públicas aunque solo lo usa el
   buscador — ahora es opt-in por plantilla
4. **Backend/caché**: N+1 reales en `HomeController`/`PostController`/bloque de noticias,
   corregidos con eager loading; caché de consulta para `Page`/`Post` con invalidación real al
   guardar/borrar; índices nuevos en 4 tablas; hallazgo y corrección de un bug de seguridad de
   Laravel 13 (`serializable_classes`) que habría roto el sitio en producción con el driver
   `database`

### Tests nuevos (Fase 7, `tests/Feature/PerformanceTest.php`, regla de CLAUDE.md §6 — nada sin test)

- Caché de página/noticia servida en la segunda lectura, con el driver `database` real (no solo
  `array` — el que usan las demás pruebas del proyecto, que **no** hubiera detectado el bug de
  `serializable_classes`)
- Invalidación de la caché al editar una página/noticia desde el modelo
- Invalidación de la caché en el slug **viejo** cuando se le cambia el slug a una página
- Ausencia de N+1 en la sección de noticias del inicio y en el listado de `/noticias` (conteo de
  queries con `DB::enableQueryLog()`, con calentamiento previo de las cachés de configuración
  global para no medir ruido ajeno al propio N+1)

Suite completa: **136/136 en verde** (129 antes de esta fase + 7 nuevos), Pint y Larastan (nivel
5) sin hallazgos.

---

## 10. Pendientes explícitos de esta fase

| Ítem | Por qué no se hizo | Cuándo se retoma |
|---|---|---|
| AVIF | Ganancia marginal sobre WebP-80 para el esfuerzo de sumar el encoder | Mejora futura, no bloqueante |
| Reducir a 4 pesos de fuente (hoy 6) | Requiere revisar 12 plantillas para no romper jerarquía visual | Futura revisión de diseño |
| `fontaine` (fallbacks con métricas) | Paquete opcional, bajo impacto (CLS ya en 0) | Opcional |
| CSS crítico inline | CSS ya liviano (10.5 KB gzip), ganancia marginal | No planeado |
| Caché de consulta en home/listados con filtros | Combinaciones de filtros + paginación vuelven la invalidación desproporcionada sin tags (Redis) | Si el volumen de contenido crece y hay Redis disponible |
| Compresión Brotli/Gzip, HTTP/2, cabeceras de caché de estáticos, OPcache | Configuración de servidor real (Plesk), no de código | Fase 10 (despliegue) |
| CDN | Evaluado, descartado por ahora (riesgo de empeorar latencia local) | Fase 10, si hay datos reales de RTT que lo justifiquen |
| Comparación con Core Web Vitals del WordPress viejo | Sin acceso a Search Console/Analytics del cliente (pregunta abierta #8, `docs/01` §E) | Si el cliente entrega accesos antes del cutover |
| LCP de Home bajo el objetivo de 2.5 s | Plantilla con más peso real (carrusel + 3 secciones con imagen); Lighthouse móvil ya en 90 | Revisar si el cliente acepta reducir secciones del inicio, o con AVIF |

---

## 11. Monitoreo continuo

- [ ] Core Web Vitals en Search Console revisados mensualmente — requiere el dominio real en
      producción (Fase 10)
- [ ] Alerta si el Lighthouse cae por debajo de 85 — a definir el mecanismo (cron +
      `lighthouse-ci` contra staging) en Fase 10
