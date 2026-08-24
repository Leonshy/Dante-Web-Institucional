# 04 — Sistema de diseño / UI (Fase 2)

Estado: **vacío — completar en Fase 2**
Herramienta: `impeccable` (correr `/impeccable init` antes de empezar)

---

## 1. Tokens de diseño

> Extraídos del manual de marca. Se vuelcan tal cual a `tailwind.config.js`.

### Color

> Tokens crudos extraídos del manual (`_insumos/02-marca-dante/MANUAL GUIA - DANTE 2023.pptx`,
> ver `docs/01-analisis-descubrimiento.md` §B). Las variantes de texto accesible y la escala de
> grises/estado quedan pendientes de definir en Fase 2 — acá solo se vuelca lo que el manual
> aporta tal cual.

| Token | Valor | Uso | Contraste sobre blanco | Contraste sobre el color |
|---|---|---|---|---|
| `brand-black` | `#191919` | Texto, wordmark del logo | 17.58 (AA ✔) | — |
| `brand-sky` | `#A6E4F8` | Marca/sello del logo, fondos, acentos — **no usar como color de texto** | 1.39 (AA ✘) | pendiente Fase 2 |
| `brand-yellow` | `#FFED4E` | Acento decorativo (franjas, resaltados) — **no usar como color de texto** | 1.20 (AA ✘) | pendiente Fase 2 |
| `brand-gray-light` | `#F1F1F1` | Fondo/superficie | 1.13 (AA ✘, no aplica a texto) | — |
| `brand-gray-mid` | `#B2B2B2` | Bordes, elementos deshabilitados | 2.12 (AA ✘) | — |
| `brand-secondary-green` | `#04884E` | **Solo bandera del isotipo completo**, según restricción explícita del manual — no usar como color funcional de UI sin aprobación del cliente | 4.53 (AA ✔) | — |
| `brand-secondary-red` | `#C41536` | Ídem — solo bandera | 5.99 (AA ✔) | — |
| `brand-secondary-blue` | `#26378C` | Ídem — solo bandera | 10.46 (AA ✔) | — |
| `neutral-50 … 900` | *(a definir)* | escala de grises | | |
| `success` | *(a definir)* | | | |
| `warning` | *(a definir)* | | | |
| `danger` | *(a definir)* | | | |
| `info` | *(a definir)* | | | |

⚠️ **Toda combinación texto/fondo tiene que pasar AA (4.5:1 texto normal, 3:1 texto grande).**
Si un color del manual no pasa, se documenta acá la variante accesible y se le explica al
cliente por qué. No se usa un color inaccesible "porque está en el manual".

| Combinación del manual que NO pasa | Ratio | Variante propuesta | Ratio nuevo | ¿Aprobada? |
|---|---|---|---|---|
| `brand-sky` `#A6E4F8` como texto sobre blanco | 1.39 | No se usa como texto — se restringe a fondos/acentos, con `brand-black` encima | — | resuelto por restricción de uso, no por recolorear |
| `brand-yellow` `#FFED4E` como texto o fondo con texto blanco | 1.20 | No se usa como texto ni como fondo de bloques — solo acento decorativo lineal (franjas, subrayados finos, máx. ~15 % de superficie) | — | resuelto por restricción de uso |

### Neutrales y semánticos

> La marca no define escala de grises ni colores de estado — se construyen acá, ancladas en
> los dos grises que sí da el manual (`brand-gray-light` = `neutral-100`, `brand-gray-mid` =
> `neutral-400`) para que la escala nueva no desentone. Los semánticos (éxito/error/etc.) son
> **paleta propia, no los colores secundarios de marca** — el manual restringe el verde/rojo/azul
> a la bandera del isotipo exclusivamente, así que reusarlos como "success"/"danger" violaría esa
> restricción. Todos verificados AA.

| Token | Hex | Ratio vs. blanco | Uso |
|---|---|---|---|
| `neutral-50` | `#FAFAFA` | 1.04 (no pasa) | Fondo de página alternativo |
| `neutral-100` | `#F1F1F1` (= `brand-gray-light`) | 1.13 (no pasa) | Fondo de tarjeta/sección |
| `neutral-200` | `#E4E4E4` | 1.27 (no pasa) | Bordes sutiles, divisores |
| `neutral-300` | `#CACACA` | 1.64 (no pasa) | Bordes de input en reposo |
| `neutral-400` | `#B2B2B2` (= `brand-gray-mid`) | 2.12 (no pasa) | Placeholder, íconos deshabilitados |
| `neutral-500` | `#8C8C8C` | 3.36 (AA texto grande) | Texto secundario grande, iconografía |
| `neutral-600` | `#666666` | 5.74 (AA texto normal) | Texto secundario / metadata |
| `neutral-700` | `#464646` | 9.44 (AA texto normal) | Texto de cuerpo alternativo |
| `neutral-800` | `#2E2E2E` | 13.58 (AA texto normal) | Encabezados sobre fondo claro |
| `neutral-900` | `#191919` (= `brand-black`) | 17.58 (AA texto normal) | Texto principal |
| `success` | `#15803D` | 5.02 (AA) | Confirmaciones, envío de formulario OK |
| `danger` | `#B91C1C` | 6.47 (AA) | Errores de validación, estados destructivos |
| `warning-text` | `#92400E` | 7.09 (AA) | Texto de advertencia (usar sobre fondo `#FEF3C7`, no sólido) |
| `info` | `#1D4ED8` | 6.70 (AA) | Mensajes informativos, enlaces sobre fondo claro |

### Tipografía

**Decisión propuesta (pendiente de aprobación del cliente — pregunta abierta #6):** ni el manual
de marca local, ni las guías oficiales de Dante.global, ni el logo vectorial definen una
tipografía (`docs/01-analisis-descubrimiento.md` §B). Se agotaron las fuentes posibles: esto no
es un dato que falta encontrar, es una decisión nueva. Propuesta razonada:

**`Barlow Condensed` (títulos) + `Barlow` (texto)** — Google Fonts, gratuitas, licencia SIL
Open Font License (sin costo, sin renovación). Son la misma familia tipográfica en dos anchos:
el wordmark del logo es una sans condensada muy bold con kerning ajustado — `Barlow Condensed`
en pesos altos (600–800) tiene el mismo ADN geométrico condensado sin copiar el logo, y al ser
hermana de `Barlow` (ancho normal, muy legible en textos largos como circulares y requisitos de
inscripción), los títulos y el cuerpo comparten una sola familia en vez de un maridaje
arbitrario. Soporte completo de diacríticos español e italiano (á, é, í, ó, ú, ñ, ü, à, è, ì,
ò, ù) — obligatorio por el sitio multiidioma (ADR-002). Alternativa si el cliente prefiere
distancia total del estilo "deportivo/rotulado" que a veces evoca una condensada: `Archivo`
(títulos, peso 700–800) + `Archivo` (texto, 400–500), una sola familia grotesca neutra, también
gratuita y con el mismo soporte de idiomas — más discreta, menos ligada al carácter del logo.

| Token | Familia | Peso | Tamaño (escritorio) | Tamaño (móvil) | Interlineado | Uso |
|---|---|---|---|---|---|---|
| `display` | Barlow Condensed | 900 (Black) | 60px | 40px | 1.05 | Hero de Inicio únicamente |
| `h1` | Barlow Condensed | 800 (ExtraBold) | 44px | 32px | 1.1 | Un solo `h1` por página |
| `h2` | Barlow Condensed | 700 (Bold) | 32px | 26px | 1.15 | Secciones principales |
| `h3` | Barlow Condensed | 700 (Bold) | 24px | 20px | 1.2 | Subsecciones, títulos de tarjeta |
| `h4` | Barlow Condensed | 600 (SemiBold) | 20px | 18px | 1.25 | Encabezados menores, labels de bloque |
| `body-lg` | Barlow | 400 (Regular) | 18px | 17px | 1.6 | Bajadas, intro de página |
| `body` | Barlow | 400 (Regular) | 16px | 16px | 1.6 | Cuerpo de texto general (base) |
| `body-sm` | Barlow | 400 (Regular) | 14px | 14px | 1.5 | Metadata, notas al pie |
| `caption` | Barlow | 500 (Medium) | 13px | 13px | 1.4 | Créditos de imagen, ayuda de campo |
| `overline` | Barlow | 600 (SemiBold) | 12px | 12px | 1.3 | Etiqueta de categoría — mayúsculas, `letter-spacing: 0.08em` |

**Licencia de las tipografías:** ☑ resuelto por elección — ambas son Google Fonts (SIL OFL),
sin costo ni licencia que verificar. Si el cliente aporta después una tipografía corporativa
real (pregunta #6), esta propuesta se reemplaza sin rehacer la escala (los tamaños/pesos se
mantienen, solo cambia la familia).
**Estrategia de carga:** autoalojadas (`woff2`, no Google Fonts CDN — evita una dependencia
externa más, coherente con el criterio de seguridad del proyecto), `font-display: swap`,
subconjunto latino + latino extendido (por los diacríticos italianos), precarga de `Barlow
Condensed` 800 (la fuente del `h1`, típicamente LCP en las plantillas institucionales).

### Espaciado

Escala base de 4 px: `4 8 12 16 24 32 48 64 96 128`

| Token | Valor | Uso típico |
|---|---|---|
| `space-1` | 4px | separación entre ícono y texto |
| `space-2` | 8px | padding interno de chip/badge |
| `space-3` | 12px | padding de botón (vertical) |
| `space-4` | 16px | padding de botón (horizontal), gap entre campos de formulario |
| `space-6` | 24px | padding interno de tarjeta |
| `space-8` | 32px | separación entre bloques dentro de una sección |
| `space-12` | 48px | separación entre secciones en móvil |
| `space-16` | 64px | separación entre secciones en escritorio |
| `space-24` | 96px | padding vertical de secciones hero/CTA destacado |
| `space-32` | 128px | separación entre bloques mayores de home |

### Radios, sombras, bordes

> El manual prohíbe formas poligonales distintas de rectángulo/cuadrado en el **logo**, pero
> no dice nada de la UI en general — se mantienen radios discretos (no completamente
> cuadrados, para no leerse como parte del isotipo, ni muy redondeados, que traicionaría el
> carácter geométrico del wordmark).

| Token | Valor |
|---|---|
| `radius-sm` | 4px — inputs, chips |
| `radius-md` | 8px — botones, tarjetas |
| `radius-lg` | 16px — modales, bloques hero |
| `radius-full` | 9999px — avatares, badges circulares (nunca en amarillo, ver prohibición de marca) |
| `shadow-sm` | `0 1px 2px rgba(25,25,25,0.06)` — tarjetas en reposo |
| `shadow-md` | `0 4px 12px rgba(25,25,25,0.10)` — tarjetas en hover, dropdowns |
| `shadow-lg` | `0 12px 32px rgba(25,25,25,0.14)` — modales |
| `border-default` | 1px solid `neutral-200` |
| `border-strong` | 1px solid `neutral-400` — inputs en foco (junto con el anillo de foco) |

### Breakpoints

| Nombre | Ancho | Nota |
|---|---|---|
| `sm` | 640px | Cambio de navegación móvil → utilidades visibles |
| `md` | 768px | Cards pasan de 1 a 2 columnas |
| `lg` | 1024px | Menú principal horizontal completo, cards a 3 columnas |
| `xl` | 1280px | Ancho de contenedor máximo del contenido |
| `2xl` | 1536px | Solo fondos/hero a sangre completa, el contenido no crece más allá de `xl` |

Contenedor máximo: `1280px`, con `padding` lateral de `space-4` (16px) en móvil y `space-8`
(32px) en escritorio.

---

## 2. Grilla y layout

- **Columnas:** 4 en móvil, 8 en tablet (`md`), 12 en escritorio (`lg` en adelante).
- **Canaleta:** `space-4` (16px) en móvil, `space-6` (24px) en escritorio.
- **Márgenes por breakpoint:** 16px (`< md`), 32px (`md`–`lg`), `auto` centrado con contenedor
  de 1280px (`≥ lg`).
- **Ritmo vertical:** múltiplos de `space-8` (32px) entre secciones en escritorio, `space-6`
  (24px) en móvil. Nunca menos espacio arriba de un título que abajo (regla de jerarquía).

---

## 3. Biblioteca de componentes

Cada componente con sus variantes y **todos** sus estados: reposo, hover, `focus-visible`,
activo, deshabilitado, cargando, error.

| Componente | Variantes | Estados definidos | Diseñado | Implementado |
|---|---|---|---|---|
| Botón | primario (`brand-black` fondo, texto blanco), secundario (borde `brand-black`, fondo transparente), fantasma (sin borde), enlace (subrayado), destructivo (`danger`) | reposo, hover (oscurece 8%), `focus-visible` (anillo 2px `info` + offset 2px), activo (oscurece 12%), deshabilitado (`neutral-300` fondo, `neutral-500` texto, cursor `not-allowed`), cargando (spinner + texto oculto con `aria-busy`) | ☑ | ☐ |
| Campo de texto | default, con ícono, con contador de caracteres | reposo (`border-default`), hover (`neutral-400`), focus (`border-strong` + anillo `info`), error (`border danger` + mensaje bajo el campo con ícono), deshabilitado, con valor | ☑ | ☐ |
| Área de texto | default, con contador | mismos que campo de texto + `resize: vertical` únicamente | ☑ | ☐ |
| Select | nativo estilizado | mismos que campo de texto, más abierto/cerrado | ☑ | ☐ |
| Checkbox / radio | simple, con descripción | reposo, hover, focus (anillo visible, nunca solo `outline:none`), marcado, deshabilitado, error (para checkbox de "acepto términos") | ☑ | ☐ |
| Subida de archivo | dropzone, con preview | reposo, arrastrando encima, subiendo (barra de progreso), subido con éxito, error (tipo/tamaño inválido) | ☑ | ☐ |
| Tarjeta | noticia (imagen+título+fecha+extracto), sección (ícono+título+texto), persona (foto+nombre+cargo — para "Autoridades") | reposo, hover (elevación `shadow-md` + `transform: translateY(-2px)`), focus-visible si es interactiva | ☑ | ☐ |
| Acordeón | simple, con ícono por ítem | cerrado, abierto, hover de cabecera, focus visible en cabecera (rol `button`, `aria-expanded`) | ☑ | ☐ |
| Tabs | horizontal | reposo, hover, seleccionado (subrayado `brand-black` 2px), focus visible, con scroll horizontal en móvil si no entran | ☑ | ☐ |
| Migas de pan | con separador `/` | reposo, hover en cada nivel salvo el actual (no clickeable), truncado con `…` si excede el ancho en móvil | ☑ | ☐ |
| Paginación | numérica + anterior/siguiente | reposo, hover, activa (página actual, `aria-current="page"`), deshabilitada (extremos) | ☑ | ☐ |
| Modal | centrado, con overlay | entrando/saliendo (ver plan de motion), foco atrapado dentro (`focus trap`), cierre con `Esc` y click fuera | ☑ | ☐ |
| Alerta / toast | éxito (`success`), error (`danger`), info (`info`), advertencia (`warning-text` + fondo `#FEF3C7`) | apareciendo, visible, descartable (botón cerrar con `focus-visible`), auto-descarte a los 6s salvo error | ☑ | ☐ |
| Encabezado del sitio | escritorio, móvil (menú hamburguesa), con scroll (se compacta y agrega sombra) | reposo, con scroll, menú móvil abierto/cerrado, selector ES/IT visible solo si el toggle de italiano está activo (ADR-002) | ☑ | ☐ |
| Pie de página | | único (sin variantes de estado, es estático) | ☑ | ☐ |
| Hero | con imagen/video, con CTA | reposo; si hay video, respeta `prefers-reduced-motion` (pausa automática) | ☑ | ☐ |
| Galería / carrusel | grilla, carrusel con flechas | reposo, hover en flechas, foco visible, indicador de posición, teclado (flechas izq/der) | ☑ | ☐ |
| Tabla de datos | (uso interno del panel, no público) | reposo, hover de fila, ordenamiento por columna, vacía | ☑ | ☐ |
| Buscador | input + resultados | reposo, con foco (expandido en móvil), escribiendo (debounce), con resultados, sin resultados (estado vacío propio) | ☑ | ☐ |
| Banner de cookies | banner inferior, no modal bloqueante del contenido | visible antes de decisión, aceptado, rechazado, gestionar preferencias (debe bloquear scripts de verdad, no ser decorativo — `PLAN.md` Fase 6) | ☑ | ☐ |
| Estado vacío | ilustración simple + texto + CTA opcional | único por contexto (sin resultados de búsqueda, sin noticias en una categoría, sin documentos) | ☑ | ☐ |
| Esqueleto de carga | bloques grises pulsantes | animación de pulso respetando `prefers-reduced-motion` (se vuelve estático, no parpadea) | ☑ | ☐ |

> "Diseñado" se marca acá porque el criterio de cada estado ya está definido con precisión
> (esta tabla es la especificación). "Implementado" se marca en la Fase 4, cuando el componente
> existe en código Blade/Livewire real.

---

## 4. Diseños de alta fidelidad

Con **contenido real** de `docs/03-copywriting.md`. Nada de Lorem ipsum.

| Plantilla | Escritorio | Móvil | Aprobada |
|---|---|---|---|
| Inicio | ☐ | ☐ | ☐ |
| Página institucional | ☐ | ☐ | ☐ |
| Nivel educativo | ☐ | ☐ | ☐ |
| Listado de noticias | ☐ | ☐ | ☐ |
| Detalle de noticia | ☐ | ☐ | ☐ |
| Contacto | ☐ | ☐ | ☐ |
| Búsqueda | ☐ | ☐ | ☐ |
| 404 | ☐ | ☐ | ☐ |
| Descargas | ☐ | ☐ | ☐ |

Ubicación de los archivos:

---

## 5. Plan de motion

> La implementación es la Fase 4 (`emil-design-eng`). Acá se define el criterio, para que la
> animación sea un sistema y no una colección de efectos sueltos.

### Principios

- El motion **comunica**: indica jerarquía, continuidad y causa-efecto. No decora.
- Nada dura más de 400 ms. Las micro-interacciones, 150–250 ms.
- Curvas: `ease-out` para entradas, `ease-in` para salidas, `ease-in-out` para movimiento.
- **`prefers-reduced-motion: reduce` desactiva todo lo que no sea un cambio de opacidad.**
  No es opcional.
- Nada que anime `width`, `height`, `top` o `left`: solo `transform` y `opacity`.
- Nada de animación que retrase la interacción del usuario.

### Catálogo

| Elemento | Animación | Duración | Curva | Disparador |
|---|---|---|---|---|
| Entrada de página | `opacity` 0→1 | 200ms | `ease-out` | Carga de la vista (no bloquea interacción) |
| Scroll reveal de secciones | `opacity` 0→1 + `transform: translateY(12px)→0` | 350ms | `ease-out` | Sección entra al 20% en viewport, una sola vez (no repite al volver a scrollear) |
| Hover de tarjeta | `transform: translateY(-2px)` + `shadow-sm→shadow-md` | 150ms | `ease-out` | `:hover` / `:focus-visible` |
| Hover de botón | Oscurecimiento de color de fondo | 150ms | `ease-out` | `:hover` |
| Apertura de menú móvil | `transform: translateX(100%)→0` del panel | 250ms | `ease-out` (entrada) / `ease-in` (salida) | Click en ícono hamburguesa |
| Apertura de acordeón | Altura automática (via `grid-template-rows` para animar sin medir JS) + `opacity` del contenido | 250ms | `ease-in-out` | Click en cabecera |
| Modal | Overlay `opacity` 0→1 (200ms) + panel `opacity`+`transform: scale(0.96)→1` (200ms) | 200ms | `ease-out` (entrada) / `ease-in` (salida) | Apertura/cierre |
| Carga de imagen | `opacity` 0→1 desde el esqueleto de carga | 300ms | `ease-out` | `onload` de la imagen |
| Contador de cifras | Incremento numérico (JS, no CSS) de 0 al valor real | 800ms máx. | `ease-out` | Entra en viewport (respeta reduced-motion: si está activo, muestra el número final directo, sin conteo) |
| Encabezado al hacer scroll | Compactación de altura + `shadow-sm` aparece | 200ms | `ease-out` | Scroll > 80px desde el top |

**`prefers-reduced-motion: reduce`:** todas las animaciones de esta tabla que involucran
`transform`/posición se desactivan y quedan como cambio instantáneo de estado; solo los
cambios de `opacity` de 200ms o menos se conservan. El contador de cifras muestra el valor
final sin animar. Ninguna anima `width`, `height`, `top` ni `left` — todo es `transform` y
`opacity`, según el principio ya establecido arriba.

---

## 6. Iconografía y fotografía

**Set de iconos:** [Lucide](https://lucide.dev) (open source, MIT, trazo consistente 1.5–2px,
> 1000 íconos, cubre todo lo que este proyecto necesita — calendario, documento, mapa,
teléfono, mail, buscador, redes sociales). Se eligió por ser trazo (no relleno), coherente con
el carácter geométrico simple del logo, y no depender de una librería con costo de licencia.

**Grosor y tamaño:** trazo 1.5px por defecto, 2px en tamaños chicos (< 20px) para que no se
pierda el detalle. Tamaños: 16px (inline con texto), 20px (botones/inputs), 24px (navegación),
32px+ (destacados en tarjetas de "Oferta educativa").

**Tratamiento fotográfico:** encuadre documental, no de stock genérico — el material disponible
son fotos reales de actos institucionales, alumnos y edificio (`docs/01-analisis-descubrimiento.md`
§E #9, sin sesión fotográfica nueva prevista por ahora). Criterio: preferir fotos con personas
reales sobre fotos de espacios vacíos, encuadre horizontal para hero (mínimo 1600×900px),
tratamiento de color natural sin filtros artificiales (institución educativa seria, no una
campaña de marketing agresiva). **Uso de personas:** priorizar imágenes donde los rostros de
menores no sean el foco identificable en primer plano para piezas de comunicación pública
amplia (redes, home) — criterio de precaución razonable dado el contexto de un colegio, a
confirmar con el cliente si tienen consentimientos de imagen vigentes antes de decidir cuáles
fotos de alumnos se pueden usar en el sitio nuevo. **Calidad mínima:** 1200px de ancho para
cualquier imagen a página completa, JPEG/WebP con compresión que no genere artefactos visibles.

**Imágenes por defecto:**
- **Portada OG (Open Graph):** versión completa del logo (con banderas) centrada sobre
  `brand-black`, 1200×630px — se genera una vez y se reutiliza como default de todo el sitio
  hasta que cada página cargue su propia imagen OG.
- **Avatar (autoridades sin foto cargada):** inicial del nombre sobre fondo `neutral-200`,
  texto `neutral-700`, forma circular (`radius-full`).
- **Placeholder de noticia sin imagen:** franja `brand-sky` de fondo con el isotipo (círculo)
  centrado en `brand-black`, nunca texto sobre el celeste (no pasa AA).

---

## 7. Modo oscuro

☐ Aplica ☑ No aplica — **motivo:** no está pedido por el cliente, no es una expectativa típica
de un sitio institucional educativo (a diferencia de una herramienta que se usa de noche), y
sumarlo implicaría re-verificar el contraste AA de toda la paleta de marca en su variante
oscura sin que haya un beneficio claro para las audiencias reales del sitio (padres,
alumnos, prensa). Si el cliente lo pide más adelante, es un cambio de alcance nuevo, no un
ajuste menor — se trataría como tal.

---

## 8. Cierre de la Fase 2

**Diseño aprobado por el cliente:** ☐ — fecha: (pendiente — incluye la aprobación de la
tipografía propuesta, pregunta abierta #6)
**Tokens volcados a `tailwind.config.js`:** ☐ — se hace en la Fase 3 al instalar Laravel/Vite,
no antes (no hay proyecto Node corriendo todavía en `app/`)
**Luz verde para Fase 3:** ☑ con reserva — el sistema de diseño, la biblioteca de componentes
y el plan de motion están completos y no bloquean el inicio del backend (Filament 5 no depende
de que el cliente haya aprobado la tipografía). Si el cliente pide otra tipografía después de
iniciada la Fase 3, el cambio es de bajo costo (la escala de tamaños/pesos ya está definida,
solo cambia el nombre de la familia en `tailwind.config.js`).
