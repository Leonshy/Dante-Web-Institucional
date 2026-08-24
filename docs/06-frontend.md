# 06 — Frontend (Fase 4)

Estado: **✅ Fase 4 cerrada (2026-08-24).** Ver §9 para el cierre completo. Queda pendiente,
no bloqueante, la verificación cruzada en dispositivos físicos (se retoma en Fase 9).
Herramienta: `emil-design-eng` (usada para el motion, §4) · `context7` para docs de
Livewire 4 / Tailwind

---

## 1. Estructura de vistas (real, no la propuesta original)

```
resources/views/
├── components/
│   ├── layouts/app.blade.php   ← layout público único (header + footer + @vite + @fonts)
│   ├── site-header.blade.php · site-footer.blade.php
│   ├── hero.blade.php · accordion.blade.php · breadcrumbs.blade.php · tabs.blade.php
│   ├── button.blade.php · alert.blade.php · empty-state.blade.php · filter-tabs.blade.php
│   ├── gallery.blade.php · page-blocks.blade.php (dispatcher de bloques, ver §2)
│   ├── card/{news,section,person}.blade.php
│   ├── form/{text,textarea,select,checkbox}.blade.php
│   └── icon/{search,menu,close,download,chevron-down,check-circle,alert-circle}.blade.php
├── blocks/                     ← un partial por cada uno de los 16 tipos del catálogo
│   ├── hero, texto, imagen-texto, tarjetas, cta, cifras, listado-noticias,
│   │   galeria-block, faq, video, testimonios, mapa, formulario,
│   │   listado-comunicados, documentos, selector-sede
├── pagination/dante.blade.php  ← vista de paginación de marca (¡OJO! va en
│                                  resources/views/pagination/, NO en views/vendor/pagination/
│                                  — Laravel resuelve `->links('pagination.dante')` por esa
│                                  ruta con notación de puntos; vendor/ es solo para vistas de
│                                  paquete publicadas bajo namespace `paquete::vista`. Bug real
│                                  encontrado y corregido en el cierre de esta sesión)
├── home.blade.php · pages/{show,landing}.blade.php · posts/{index,show}.blade.php
├── documents/index.blade.php · announcements/index.blade.php · calendar/index.blade.php
├── galleries/index.blade.php · contact/show.blade.php · search/index.blade.php
├── livewire/search-page.blade.php (App\Livewire\SearchPage)
└── errors/{404,500}.blade.php
```

Los Blade components de layout usan el namespace `x-layouts.app`, no `x-app-layout` — así
quedó armado por el agente que hizo el trabajo base, se mantiene por consistencia.

**Regla:** cada componente de `docs/04-ui-design-system.md` §3 que se usa en al menos un
mockup de alta fidelidad tiene su componente Blade. No se construyeron los que no se usan en
ningún mockup (ej. "Tabla de datos" es de uso interno del panel, ya vive en Filament).

---

## 2. Plantillas

| Plantilla | Ruta | Vista | Livewire | Estado |
|---|---|---|---|---|
| Inicio | `/` | `home.blade.php` | no | ☑ |
| Página institucional | `/{slug}` | `pages/show.blade.php` | no | ☑ |
| Landing de sección | `/{slug}` (template=landing) | `pages/landing.blade.php` | no | ☑ |
| Listado de noticias | `/noticias` | `posts/index.blade.php` | no (filtros por querystring + paginación de marca) | ☑ |
| Detalle de noticia | `/noticias/{slug}` | `posts/show.blade.php` | no | ☑ |
| Contacto | `/contacto` | `contact/show.blade.php` | no (formulario postea a `/contacto`, honeypot + throttle ya de Fase 3) | ☑ |
| Búsqueda | `/buscar` | `search/index.blade.php` | sí (`App\Livewire\SearchPage`, reutiliza `SearchService` de Fase 3) | ☑ |
| Descargas (documentos) | `/documentos` | `documents/index.blade.php` | no | ☑ |
| 404 | — | `errors/404.blade.php` | no | ☑ |
| 500 | — | `errors/500.blade.php` | no (sin mockup dedicado, mismo lenguaje visual del 404) | ☑ |
| Comunicados (bonus, sin mockup de Fase 2) | `/vida-escolar/comunicados` | `announcements/index.blade.php` | no | ☑ |
| Calendario (bonus) | `/vida-escolar/calendario` | `calendar/index.blade.php` | no | ☑ |
| Galería (bonus) | `/vida-escolar/galeria` | `galleries/index.blade.php` | no | ☑ |

`GET /buscar` negocia contenido: `wantsJson()` (Accept: application/json) devuelve el JSON de
siempre, cualquier otra petición devuelve la vista pública con el buscador reactivo — no se
sumó una ruta nueva, decisión explícita para no duplicar la superficie ya probada en Fase 3.

---

## 3. Imágenes

- `srcset` + `sizes` en toda imagen de contenido
- Formatos: AVIF → WebP → JPG como respaldo
- `loading="lazy"` **salvo** la imagen del LCP (esa lleva `fetchpriority="high"` y precarga)
- `width` y `height` explícitos siempre — es la causa número uno de CLS
- Portadas de video con `poster`, el video nunca en autoplay con sonido

---

## 4. Motion implementado

Según el catálogo de `docs/04-ui-design-system.md` §5, con los criterios de
`emil-design-eng` (solo `transform`/`opacity`, curvas custom, nada de `ease-in` en UI,
feedback de presión en botones).

| Elemento | Implementado con | Respeta reduced-motion | Estado |
|---|---|---|---|
| Entrada de página | Fade del `<body>` no bloqueante (CSS) | sí | ☑ |
| Scroll reveal de secciones | `.reveal` + `IntersectionObserver` (`resources/js/app.js`), umbral 20%, una sola vez. Ver nota de diseño abajo | sí (media query global) | ☑ |
| Hover de tarjeta | `.card:hover` — `translateY(-2px)` + `shadow-sm→shadow-md` (ya venía de la base) | sí | ☑ |
| Feedback de botón (press) | `.btn:active { transform: scale(0.97) }`, agregado en esta pasada (antes solo cambiaba `opacity`) | sí | ☑ |
| Apertura de menú móvil | `.mobile-nav.is-open` (`translateX`), Alpine.js con trampa de foco y `Esc` (ya venía de la base) | sí | ☑ |
| Apertura de acordeón (FAQ) | `grid-template-rows: 0fr→1fr` + opacity, sin medir alto en JS. Rehecho en esta pasada — antes usaba `x-show` (display:none, sin transición) | sí | ☑ |
| Carga de imagen | `@starting-style` (opacity 0→1), sin JS | sí (regla de transición se anula globalmente) | ☑ |
| Contador de cifras | `resources/js/app.js`, cuenta 0→valor real en 800ms con ease-out cúbico; si el valor no es numérico (ej. "Afiliados") no anima | sí (se salta el conteo, muestra el valor final directo) | ☑ |
| Encabezado al hacer scroll | `.site-header.is-scrolled`, Alpine `@scroll.window` (ya venía de la base) | sí | ☑ |
| Modal | No implementado — ningún mockup de Fase 2 usa un modal en el sitio público | — | — |

**Nota de diseño — por qué `.reveal` necesita la clase `.js` en `<html>`:** en el cierre de
esta sesión se encontró que la versión anterior de `.reveal` ocultaba contenido
(`opacity: 0`) sin que existiera todavía el JS que lo revela, dejando el sitio con secciones
enteras invisibles para cualquier usuario real (`docs/06-frontend.md` §8 #4 más abajo). La
solución: un script síncrono mínimo en el `<head>` agrega la clase `.js` a `<html>` antes de
que el CSS decida ocultar nada — `.reveal` solo empieza oculto si `.js` está presente, así que
si el JS de scroll-reveal alguna vez falla en cargar, el contenido nunca queda invisible por
default. Verificado con scroll real simulado (no solo captura de pantalla completa, que no
dispara `IntersectionObserver`): todas las secciones se revelan correctamente.

**Verificación obligatoria:** con `prefers-reduced-motion: reduce` activo en el sistema
operativo, el sitio no tiene ningún movimiento — solo cambios de opacidad. El CSS ya lo
desactiva globalmente (`app.css`, bloque `@media (prefers-reduced-motion: reduce)`) y el
contador de cifras lo verifica explícitamente en JS antes de animar. Probado con
`window.matchMedia('(prefers-reduced-motion: reduce)')` en DevTools — pendiente la prueba
manual con la preferencia real del sistema operativo en un dispositivo físico (Fase 9, QA).

---

## 5. JavaScript

- Alpine.js para interactividad local, Livewire para lo que toca el servidor.
- **Cero jQuery.** Si algún componente lo pide, se descarta ese componente.
- Todo lo no crítico va con `defer`.
- Nada de librerías de terceros sin justificación escrita — cada KB se paga en móvil.

| Librería | Peso | Justificación |
|---|---|---|
| | | |

---

## 6. Accesibilidad del frontend

Pasada de verificación ítem por ítem cerrada en esta sesión (código + navegador real, no solo
inspección visual).

- [x] `<html lang="{{ locale }}">` — dinámico según `app()->getLocale()` (es/it, ADR-002)
- [x] Enlace "saltar al contenido" como primer elemento enfocable — verificado con Tab real en
      navegador, cae en `#contenido`
- [x] Landmarks: `header`, `nav`, `main`, `aside`, `footer` — presentes en las 12 plantillas
      (`<main id="contenido">` en todas)
- [x] Un solo `h1` por página, sin saltos de nivel — **bug real encontrado y corregido**:
      `pages/show.blade.php` siempre renderizaba `<h1>{{ $page->title }}</h1>` *además* del
      `<h1>` propio del bloque `hero` cuando un editor agregaba ese bloque a una página
      institucional (dos h1 en la misma página); `pages/landing.blade.php` no tenía ningún h1
      si su primer bloque no era `hero`. Corregido en ambas: el h1 de página solo se renderiza
      si el primer bloque no es `hero` (ese bloque ya trae el suyo). Test de regresión en
      `tests/Feature/PageControllerTest.php`
- [x] `:focus-visible` visible en todo lo interactivo — anillo `outline: 2px solid var(--color-info)`
      universal; los dos únicos `outline: none` del CSS (campos de formulario, buscador) tienen
      reemplazo real con `box-shadow`. Verificado con Tab real: el foco es visible y contrasta
- [x] Menú móvil accesible por teclado, con trampa de foco y cierre con `Esc` — ya venía
      correcto de la base (Alpine.js, `trapTab()`, devuelve el foco al botón que abrió)
- [~] Modales con trampa de foco y devolución del foco al cerrar — no aplica: ningún mockup de
      Fase 2 usa un modal en el sitio público
- [x] Formularios con `<label>` asociado, errores con `aria-describedby` y `role="alert"` — los
      4 componentes de formulario (`text`, `textarea`, `select`, `checkbox`) ya lo tenían
      correcto de la base
- [x] Imágenes con alt real; las decorativas con `alt=""` — verificado, ninguna `<img>` sin
      `alt`
- [x] Carruseles con controles y pausa — el carrusel de galería tiene botones anterior/siguiente
      con `aria-label`; no autoplay, así que no necesita pausa
- [x] Contraste verificado sobre el render final — sin combinaciones celeste/amarillo como
      color de texto en ningún lado del CSS (la restricción de `docs/04` §1 se respeta también
      en el código, no solo en el diseño)

**Hallazgo adicional (no es de accesibilidad, pero salió de la misma revisión):** el bloque
`hero` sin imagen cargada (campo opcional en el panel) tiraba un error real —
`$data['image']` se accedía sin `??`/`!empty()`. Corregido.

---

## 7. Verificación cruzada

| Navegador / dispositivo | Probado | Notas |
|---|---|---|
| Chrome escritorio | ☐ | |
| Firefox escritorio | ☐ | |
| Safari escritorio | ☐ | |
| Edge | ☐ | |
| iOS Safari (dispositivo real) | ☐ | |
| Chrome Android (dispositivo real) | ☐ | |

> Probar en dispositivo real, no solo en el emulador de DevTools. El público de este sitio
> entra desde el celular.

---

## 8. Hallazgos y correcciones de esta sesión

El agente que armó la base del frontend se cortó a mitad de camino por un error de la
plataforma (no del código) mientras reescribía `routes/web.php`. Al retomar y verificar todo
en un navegador real antes de dar la fase por buena (regla de `CLAUDE.md`: "para UI o frontend,
arrancar el servidor y probar antes de reportar terminado"), aparecieron varios bugs reales,
todos corregidos y cubiertos con test:

1. **`HomeController` no arrancaba** — la ruta `/` lo llamaba como invokable (`::class` a
   secas) pero el controller tenía `index()`, no `__invoke()`. `route:list` fallaba entero.
2. **`SearchController::index()` tiraba `TypeError`** en cualquier request JSON — el tipo de
   retorno declarado (`Response|View`) no incluye `JsonResponse` (no hereda de
   `Illuminate\Http\Response`, hereda directo de Symfony). Corregido a `JsonResponse|View`.
3. **La fuente de marca (Barlow/Barlow Condensed) nunca se cargaba** — `vite.config.js`
   autoalojaba "Instrument Sans" (el default del starter kit de Laravel, sin usar en ningún
   lado) y a la vista real le faltaba la directiva `@fonts` que emite los `@font-face`. El
   sitio caía silenciosamente a Arial. Corregido: `vite.config.js` autoaloja Barlow (400/500/600)
   y Barlow Condensed (500/600/700) vía Bunny Fonts, `@fonts` agregado al layout.
4. **El sitio se veía en blanco debajo del hero, en cualquier navegador sin
   `prefers-reduced-motion: reduce`** — la clase `.reveal` (preparada para el scroll-reveal de
   la próxima pasada de motion) seteaba `opacity: 0` por defecto, pero el JS que agrega
   `.is-visible` todavía no existe (a propósito, es la siguiente pasada). Resultado: contenido
   real invisible en producción. Neutralizado (`.reveal { opacity: 1 }` hasta que el motion se
   implemente de verdad, atómicamente con su JS).
5. **El contenido de los bloques no se veía en ninguna página** — `Page::blocksForLocale()`
   asumía la forma plana vieja de un bloque (`$block['content']`) en vez de la real que
   persiste el Builder de Filament 5 (`$block['data']['content']`), el mismo error de forma que
   ya se había corregido del lado de la sanitización en el cierre de la Fase 3. Resultado: todo
   el contenido de página quedaba anidado un nivel de más y nunca llegaba a la vista. Corregido
   con un resolver recursivo (cubre también los campos dentro de repetidores como FAQ/testimonios).
6. **Paginación rota (`View [pagination.dante] not found`)** — el archivo estaba en
   `resources/views/vendor/pagination/dante.blade.php`, pero `->links('pagination.dante')`
   resuelve por notación de puntos a `resources/views/pagination/dante.blade.php` (la carpeta
   `vendor/` es solo para vistas de paquete publicadas bajo namespace `paquete::vista`).
   Corregido moviendo el archivo.
7. **`GalleryBlock` no tipaba** — Larastan no podía inferir el tipo de `$gallery->media` porque
   la relación `Gallery::media()` no tenía PHPDoc genérico. Agregado `@return BelongsToMany<Media, $this>`.
8. **Doble o cero `<h1>`, encontrado en la pasada de accesibilidad** — `pages/show.blade.php`
   siempre renderizaba el título de la página como `<h1>` *además* del `<h1>` propio del bloque
   `hero` cuando ese bloque estaba presente; `pages/landing.blade.php` no tenía ningún `<h1>` si
   su primer bloque no era `hero`. Corregido en ambas plantillas (ver §6).
9. **El bloque `hero` sin imagen cargada tiraba error real** — `$data['image']` se accedía sin
   `??`/`!empty()`, y el campo es opcional en el panel. Corregido.

Ítem #3 y #4 son los más serios: sin corregirlos, el sitio hubiera pasado todos los tests
(que no verifican CSS ni fuentes) pero se hubiera visto roto para cualquier visitante real.
Confirma el valor de la regla "probar en navegador antes de reportar terminado".

No había tests de las rutas públicas todavía (el agente se cortó antes de escribirlos) —
se agregaron `tests/Feature/PageControllerTest.php` (incluye el caso que hubiera atrapado el
bug #5, y los casos de h1 único del bug #8) y `tests/Feature/PublicContentRoutesTest.php`
(home, contacto, y que cada listado público solo muestra contenido publicado). 74/74 tests en
verde, Pint y Larastan limpios.

---

## 9. Cierre de la Fase 4 ✅ 2026-08-24

**Coincide con el diseño aprobado:** ☑ (verificado visualmente en navegador — inicio, página
institucional, admisiones con FAQ, 404, noticias, búsqueda — fiel a `docs/alta-fidelidad/` y a
los tokens de `docs/04-ui-design-system.md`)
**Motion:** ☑ implementado y verificado con scroll real en navegador (§4)
**Accesibilidad:** ☑ pasada ítem por ítem cerrada, con dos bugs reales encontrados y
corregidos (h1 duplicado/faltante, crash del bloque hero sin imagen) — ver §6
**Sin errores de consola:** ☑ verificado en Chrome (DevTools) durante las pruebas de motion y
teclado de esta sesión
**Verificación cruzada de navegadores/dispositivos reales (§7):** pendiente — no aplica al
entorno de desarrollo, requiere dispositivos físicos. **No bloqueante** para cerrar la fase
(mismo criterio que la Fase 0 con la pregunta de acceso a Plesk): se retoma en la Fase 9 (QA),
que ya tiene este ítem en su propio checklist de compatibilidad.
**Luz verde para Fase 5:** completa — el renderizado público funciona de punta a punta con
datos reales, listo para recibir el contenido migrado del WordPress viejo.
