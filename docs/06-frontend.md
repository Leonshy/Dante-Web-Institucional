# 06 — Frontend (Fase 4)

Estado: **vacío — completar en Fase 4**
Herramienta: `emil-design-eng` para el motion · `context7` para docs de Livewire 4 / Tailwind

---

## 1. Estructura de vistas

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← layout público
│   └── partials/{head,header,footer,scripts}.blade.php
├── components/                ← espejo de la biblioteca de la Fase 2
│   ├── button.blade.php
│   ├── card.blade.php
│   └── ...
├── blocks/                    ← un archivo por tipo de bloque del constructor
│   ├── hero.blade.php
│   ├── text.blade.php
│   └── ...
├── pages/
├── posts/
└── errors/{404,500}.blade.php
```

**Regla:** cada componente de `docs/04-ui-design-system.md` §3 tiene exactamente un
componente Blade. Si un diseño necesita algo que no está en la biblioteca, se agrega
primero a la biblioteca.

---

## 2. Plantillas

| Plantilla | Vista | Livewire | Estado |
|---|---|---|---|
| Inicio | | | ☐ |
| Página con bloques | | | ☐ |
| Nivel educativo | | | ☐ |
| Listado de noticias | | sí (filtros, paginación) | ☐ |
| Detalle de noticia | | | ☐ |
| Contacto | | sí (formulario) | ☐ |
| Búsqueda | | sí | ☐ |
| Descargas | | | ☐ |
| 404 / 500 | | | ☐ |

---

## 3. Imágenes

- `srcset` + `sizes` en toda imagen de contenido
- Formatos: AVIF → WebP → JPG como respaldo
- `loading="lazy"` **salvo** la imagen del LCP (esa lleva `fetchpriority="high"` y precarga)
- `width` y `height` explícitos siempre — es la causa número uno de CLS
- Portadas de video con `poster`, el video nunca en autoplay con sonido

---

## 4. Motion implementado

Según el catálogo de `docs/04-ui-design-system.md` §5.

| Elemento | Implementado con | Respeta reduced-motion | Estado |
|---|---|---|---|
| | | ☐ | ☐ |

**Verificación obligatoria:** con `prefers-reduced-motion: reduce` activo en el sistema
operativo, el sitio no tiene ningún movimiento — solo cambios de opacidad. Probarlo de verdad,
no asumirlo.

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

- [ ] `<html lang="es">`
- [ ] Enlace "saltar al contenido" como primer elemento enfocable
- [ ] Landmarks: `header`, `nav`, `main`, `aside`, `footer`
- [ ] Un solo `h1` por página, sin saltos de nivel
- [ ] `:focus-visible` visible en todo lo interactivo — **nunca `outline: none` sin reemplazo**
- [ ] Menú móvil accesible por teclado, con trampa de foco y cierre con `Esc`
- [ ] Modales con trampa de foco y devolución del foco al cerrar
- [ ] Formularios con `<label>` asociado, errores con `aria-describedby` y `role="alert"`
- [ ] Imágenes con alt real; las decorativas con `alt=""`
- [ ] Carruseles con controles y pausa
- [ ] Contraste verificado sobre el render final, no sobre el diseño

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

## 8. Cierre de la Fase 4

**Coincide con el diseño aprobado:** ☐
**Sin errores de consola:** ☐
**Luz verde para Fase 5:** ☐
