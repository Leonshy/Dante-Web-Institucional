# 04 — Sistema de diseño / UI (Fase 2)

Estado: **vacío — completar en Fase 2**
Herramienta: `impeccable` (correr `/impeccable init` antes de empezar)

---

## 1. Tokens de diseño

> Extraídos del manual de marca. Se vuelcan tal cual a `tailwind.config.js`.

### Color

| Token | Valor | Uso | Contraste sobre blanco | Contraste sobre el color |
|---|---|---|---|---|
| `brand-primary` | | | | |
| `brand-primary-hover` | | | | |
| `brand-secondary` | | | | |
| `brand-accent` | | | | |
| `neutral-50 … 900` | | escala de grises | | |
| `success` | | | | |
| `warning` | | | | |
| `danger` | | | | |
| `info` | | | | |

⚠️ **Toda combinación texto/fondo tiene que pasar AA (4.5:1 texto normal, 3:1 texto grande).**
Si un color del manual no pasa, se documenta acá la variante accesible y se le explica al
cliente por qué. No se usa un color inaccesible "porque está en el manual".

| Combinación del manual que NO pasa | Ratio | Variante propuesta | Ratio nuevo | ¿Aprobada? |
|---|---|---|---|---|

### Tipografía

| Token | Familia | Peso | Tamaño | Interlineado | Uso |
|---|---|---|---|---|---|
| `display` | | | | | |
| `h1` | | | | | |
| `h2` | | | | | |
| `h3` | | | | | |
| `h4` | | | | | |
| `body-lg` | | | | | |
| `body` | | | | | |
| `body-sm` | | | | | |
| `caption` | | | | | |
| `overline` | | | | | |

**Licencia de las tipografías:** ☐ verificada — tipo:
**Sustituto si no hay licencia web:**
**Estrategia de carga:** autoalojadas, `woff2`, `font-display: swap`, subconjunto latino,
precarga de la fuente del `h1`.

### Espaciado

Escala base de 4 px: `4 8 12 16 24 32 48 64 96 128`

### Radios, sombras, bordes

| Token | Valor |
|---|---|
| `radius-sm` / `md` / `lg` / `full` | |
| `shadow-sm` / `md` / `lg` | |
| `border-default` | |

### Breakpoints

| Nombre | Ancho | Nota |
|---|---|---|
| `sm` | 640px | |
| `md` | 768px | |
| `lg` | 1024px | |
| `xl` | 1280px | |
| `2xl` | 1536px | |

Contenedor máximo:

---

## 2. Grilla y layout

- Columnas:
- Canaleta:
- Márgenes por breakpoint:
- Ritmo vertical:

---

## 3. Biblioteca de componentes

Cada componente con sus variantes y **todos** sus estados: reposo, hover, `focus-visible`,
activo, deshabilitado, cargando, error.

| Componente | Variantes | Estados definidos | Diseñado | Implementado |
|---|---|---|---|---|
| Botón | primario, secundario, fantasma, enlace, destructivo | | ☐ | ☐ |
| Campo de texto | | | ☐ | ☐ |
| Área de texto | | | ☐ | ☐ |
| Select | | | ☐ | ☐ |
| Checkbox / radio | | | ☐ | ☐ |
| Subida de archivo | | | ☐ | ☐ |
| Tarjeta | noticia, sección, persona | | ☐ | ☐ |
| Acordeón | | | ☐ | ☐ |
| Tabs | | | ☐ | ☐ |
| Migas de pan | | | ☐ | ☐ |
| Paginación | | | ☐ | ☐ |
| Modal | | | ☐ | ☐ |
| Alerta / toast | éxito, error, info, advertencia | | ☐ | ☐ |
| Encabezado del sitio | escritorio, móvil, con scroll | | ☐ | ☐ |
| Pie de página | | | ☐ | ☐ |
| Hero | | | ☐ | ☐ |
| Galería / carrusel | | | ☐ | ☐ |
| Tabla de datos | | | ☐ | ☐ |
| Buscador | | | ☐ | ☐ |
| Banner de cookies | | | ☐ | ☐ |
| Estado vacío | | | ☐ | ☐ |
| Esqueleto de carga | | | ☐ | ☐ |

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
| Entrada de página | | | | |
| Scroll reveal de secciones | | | | |
| Hover de tarjeta | | | | |
| Hover de botón | | | | |
| Apertura de menú móvil | | | | |
| Apertura de acordeón | | | | |
| Modal | | | | |
| Carga de imagen | | | | |
| Contador de cifras | | | | |
| Encabezado al hacer scroll | | | | |

---

## 6. Iconografía y fotografía

**Set de iconos:**
**Grosor y tamaño:**
**Tratamiento fotográfico:** (encuadre, tratamiento de color, uso de personas, calidad mínima)
**Imágenes por defecto:** portada OG, avatar, placeholder de noticia sin imagen

---

## 7. Modo oscuro

☐ Aplica ☐ No aplica — decisión y motivo:

---

## 8. Cierre de la Fase 2

**Diseño aprobado por el cliente:** ☐ — fecha:
**Tokens volcados a `tailwind.config.js`:** ☐
**Luz verde para Fase 3:** ☐
