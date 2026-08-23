# 02 — UX y arquitectura de información (Fase 1)

Estado: **vacío — completar en Fase 1**
Herramienta: `ux-flow-designer`

---

## 1. Audiencias

| Audiencia | Qué viene a buscar | Prioridad | % estimado del tráfico |
|---|---|---|---|
| Padres que están evaluando el colegio | Propuesta educativa, niveles, aranceles, cómo inscribir, ubicación | Alta | |
| Padres de alumnos actuales | Calendario, comunicados, noticias, contacto con secretaría | Alta | |
| Alumnos | | | |
| Docentes / postulantes a docencia | | | |
| Ex-alumnos | | | |
| Prensa / instituciones | | | |

> El sitio institucional educativo tiene un sesgo fuerte: la audiencia que **decide la compra**
> son los padres que evalúan. Si la arquitectura no los sirve primero, el sitio no cumple su
> función comercial, por más completo que sea para los demás.

---

## 2. Tareas principales por audiencia

Ordenadas por frecuencia × importancia. Cada tarea tiene que resolverse en ≤ 3 clics.

| # | Audiencia | Tarea | Frecuencia | Importancia | ¿Resuelta hoy? |
|---|---|---|---|---|---|
| 1 | | | | | |

---

## 3. Card sorting → arquitectura nueva

Partiendo del inventario de `docs/01-analisis-descubrimiento.md` §C.2.

### Agrupaciones resultantes

### Contenido que se fusiona

| Páginas viejas | Página nueva | Motivo |
|---|---|---|

### Contenido que se descarta

| Página vieja | Motivo del descarte |
|---|---|

### Contenido nuevo que hay que crear

| Página nueva | Motivo | Quién provee el contenido |
|---|---|---|

---

## 4. Mapa del sitio nuevo

```
Inicio
├── Institucional
│   ├── ...
├── Niveles educativos
│   ├── ...
├── ...
```

Profundidad máxima: 3 niveles. Cualquier cosa más profunda es una señal de que la
arquitectura está mal.

---

## 5. Navegación

| Ubicación | Contenido | Comportamiento |
|---|---|---|
| Menú principal (escritorio) | | |
| Menú principal (móvil) | | |
| Barra de utilidades | | |
| Navegación contextual / lateral | | |
| Pie de página | | |
| Migas de pan | | |
| Buscador | | |

**Regla:** el menú principal no lleva más de 7 elementos de primer nivel. Si no entran, la
arquitectura está mal, no el menú.

---

## 6. Flujos de las tareas críticas

Un diagrama por tarea. Formato: pantalla → acción → pantalla, con los puntos de abandono
marcados.

### Flujo 1 — "Quiero saber cómo inscribir a mi hijo"

### Flujo 2 —

### Flujo 3 —

### Flujo 4 —

### Flujo 5 —

---

## 7. Tipos de contenido y sus campos

> Esto alimenta directo el modelo de datos de la Fase 3. Cuanto más preciso acá, menos
> retrabajo después.

### Página

| Campo | Tipo | Obligatorio | Nota |
|---|---|---|---|
| Título | texto | sí | |
| Slug | texto | sí | editable, autogenerado |
| Padre | relación | no | jerarquía |
| Bloques de contenido | repetidor | sí | ver catálogo de bloques |
| Imagen de portada | medio | no | |
| SEO: título | texto | no | por defecto = título |
| SEO: descripción | texto | no | |
| SEO: imagen OG | medio | no | |
| Estado | enum | sí | borrador / publicado / archivado |

### Noticia

| Campo | Tipo | Obligatorio | Nota |
|---|---|---|---|

### (otros tipos de contenido que surjan)

---

## 8. Catálogo de bloques de contenido

Los bloques que el cliente puede combinar para armar una página desde el panel.

| Bloque | Campos | Dónde se usa |
|---|---|---|
| Hero | título, bajada, imagen/video, CTA | inicio, cabeceras de sección |
| Texto enriquecido | contenido HTML | todas |
| Imagen + texto | imagen, posición, título, texto, CTA | |
| Galería | medios, disposición | |
| Tarjetas | repetidor (icono, título, texto, enlace) | |
| Acordeón / FAQ | repetidor (pregunta, respuesta) | |
| CTA destacado | título, texto, botón, fondo | |
| Video | URL o archivo, portada | |
| Cifras / logros | repetidor (número, etiqueta) | |
| Testimonios | repetidor (foto, nombre, rol, texto) | |
| Mapa | dirección, coordenadas | contacto |
| Formulario | selector de formulario | contacto |
| Listado de noticias | categoría, cantidad | |
| Documentos descargables | repetidor (archivo, título) | |

> Menos bloques bien hechos > muchos bloques mediocres. Empezar con los que el inventario
> de contenido realmente demanda; agregar después es barato.

---

## 9. Wireframes

Plantillas únicas a wireframear (baja fidelidad, sin color, con contenido real aproximado):

- [ ] Inicio
- [ ] Página institucional (con bloques)
- [ ] Sección de nivel educativo
- [ ] Listado de noticias
- [ ] Detalle de noticia
- [ ] Contacto
- [ ] Resultados de búsqueda
- [ ] 404
- [ ] Listado de documentos / descargas

Cada uno en versión escritorio y móvil.

Ubicación de los archivos:

---

## 10. Mapa de redirecciones 301

> **Crítico para no perder SEO.** Este archivo se carga tal cual en el panel en la Fase 5.

Formato: CSV en `docs/redirecciones-301.csv`

| URL vieja | URL nueva | Tipo | Verificada |
|---|---|---|---|
| | | 301 | ☐ |

Toda URL del inventario de la Fase 0 tiene que aparecer acá, aunque sea para apuntar al
inicio. Una URL sin redirección es un 404 y un enlace perdido.

---

## 11. Revisión de accesibilidad de los wireframes

- [ ] Jerarquía de encabezados coherente: un solo `h1`, sin saltos de nivel
- [ ] Orden de tabulación lógico
- [ ] Objetivos táctiles ≥ 44×44 px
- [ ] Enlaces con texto descriptivo (nada de "hacé clic acá")
- [ ] Contenido no dependiente solo del color
- [ ] Formularios con etiquetas visibles, no solo placeholder
- [ ] Enlace de "saltar al contenido"

---

## 12. Cierre de la Fase 1

**Aprobado por el cliente:** ☐ sí ☐ no — fecha:
**Luz verde para Fase 2:** ☐
