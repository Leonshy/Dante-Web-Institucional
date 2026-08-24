# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Decidido (no delegado): Laravel 13 (PHP 8.3+), Blade, Livewire 4 + Alpine.js, Tailwind CSS +
Vite, panel de administración en **Filament 5** (`docs/decisiones/ADR-001-panel-admin.md`),
MySQL 8/MariaDB. Despliegue en Plesk (webparaguay), sin Docker, sin Redis/Supervisor
garantizados. Ver `CLAUDE.md` §3 — fuente de verdad del stack, no se duplica acá.

## Users

Audiencia primaria: **padres que están evaluando el colegio** — son quienes deciden la
matrícula, y la arquitectura del sitio los sirve primero (`docs/02-ux-arquitectura-informacion.md`
§1). Audiencias secundarias: padres de alumnos actuales, alumnos (secundaria/instituto de
idiomas), docentes/postulantes, ex-alumnos, prensa/instituciones. Detalle completo con tareas
por audiencia en `docs/02-ux-arquitectura-informacion.md` §1–2.

## Product Purpose

Reemplazar el sitio institucional de Dante en WordPress — que estuvo comprometido (webshell
activa confirmada, ver `docs/01-analisis-descubrimiento.md` §C.6) — por un sitio nuevo en
Laravel, autoadministrable por el cliente sin ayuda del proveedor, multiidioma español/italiano,
fiel al manual de marca. Éxito = el cliente publica contenido sin llamar a webparaguay
(`docs/00-legajo-tecnico.md` §9, KPI principal).

## Positioning

Colegio bilingüe español-italiano en Asunción, afiliado a la **Società Dante Alighieri**
(Roma) — el mecanismo diferencial es la afiliación institucional oficial y la certificación
internacional **PLIDA** (Proyecto Lengua Italiana Dante Alighieri, obligatoria para ciertos
grados). Hitos reales que sostienen esto: 129° aniversario de la Scuola, visita del presidente
de Italia Sergio Mattarella en 2024. Ningún colegio de italiano sin esa afiliación puede
reclamar lo mismo con la misma legitimidad.

## Operating Context

Institución educativa en Paraguay con sede en Asunción (y posible sede en Fernando de la Mora,
vigencia a confirmar — pregunta abierta #14). Inscripción de alumnos **100 % presencial en
papel** (cédula, certificado de nacimiento, boletín, fotos) — el sitio nunca procesó ni va a
procesar datos personales de menores online (`docs/01-analisis-descubrimiento.md` §E #3,
análisis propio). El panel lo van a operar 4 roles distintos: administrador, editor general,
editor de noticias/banners + marketing (Google Ads/Analytics/Meta), editor académico
(`docs/01-analisis-descubrimiento.md` §E #2).

## Capabilities and Constraints

- **Multiidioma español/italiano real, con toggle de habilitación** — no es opcional, es un
  requisito confirmado del cliente (ADR-002). Todo contenido existe en los dos idiomas cuando
  el italiano está activo.
- **Sanitización HTML y SVG server-side obligatoria** — el repo de referencia (IPG) no la
  tiene, y el antecedente de compromiso de Dante (webshell vía SVG/PHP disfrazado) la hace no
  negociable (`docs/01-analisis-descubrimiento.md` §A.4, §C.6).
- Sin integraciones de Analytics/GTM/Meta Pixel/captcha configuradas en el sitio actual — se
  construyen de cero en la Fase 6.
- Objetivos de rendimiento: LCP < 2.5 s, INP < 200 ms, CLS < 0.1, Lighthouse móvil ≥ 90
  (`docs/00-legajo-tecnico.md` §5) — tráfico institucional mayoritariamente móvil, con la
  conexión promedio de Paraguay.
- Hosting Plesk sin Docker; asumir sin Redis/Supervisor hasta confirmar (pregunta abierta #13,
  todavía sin respuesta).
- **Tipografía: explícitamente sin definir.** Ni el manual de marca local, ni las guías
  oficiales internacionales de Dante.global, ni el logo vectorial (texto convertido a trazado,
  sin fuente embebida) especifican una tipografía — se agotaron todas las fuentes posibles
  (`docs/01-analisis-descubrimiento.md` §B, pregunta #6). No inventar una: se propone una
  candidata razonada en el propio trabajo de Fase 2 y se deja pendiente de aprobación del
  cliente, no se asume como decidida.

## Brand Commitments

- Nombre: **DANTE** — wordmark de "Società Dante Alighieri Asunción" / "Scuola Dante Alighieri".
- Logo: wordmark condensado muy bold en dos líneas (DAN/TE), con un círculo sólido celeste como
  sello. Versión **compacta** (wordmark + círculo, espacios chicos) y **completa** (con banderas
  Paraguay/Italia, documentos formales). Vectorial oficial disponible en
  `_insumos/02-marca-dante/DANTE società dante alighieri LOGO.ai` (texto en trazado).
- Paleta: negro `#191919` (único color de texto que pasa AA), celeste `#A6E4F8` y amarillo
  `#FFED4E` (acentos/fondos, nunca texto), gris claro `#F1F1F1` y gris medio `#B2B2B2`.
  Secundarios verde `#04884E`/rojo `#C41536`/azul `#26378C` — **restringidos por el propio
  manual a la bandera del isotipo completo, no usar como paleta funcional de UI**.
- Área de resguardo: 1 módulo = 1/2 de la altura de la letra "N" del wordmark.
- Prohibiciones explícitas del manual: no deformar el logo, no cambiarle los colores, no usar
  formas poligonales distintas de rectángulo/cuadrado, **nunca combinar el amarillo con el
  círculo**.
- Detalle completo, incluida la tabla de contraste AA por color, en
  `docs/01-analisis-descubrimiento.md` §B y `docs/04-ui-design-system.md` §1.

## Evidence on Hand

- Inventario completo y real de las 39 piezas de contenido migrables (26 páginas + 13 entradas,
  con URLs, palabras y fecha de última edición) en `docs/01-analisis-descubrimiento.md` §C.2.
- Capturas del sitio actual en vivo (home, contacto, página institucional, listado y detalle de
  noticia) en `_insumos/03-wordpress-actual/export/screenshots/`.
- 9 wireframes de baja fidelidad (escritorio + móvil, accesibles) ya construidos en
  `docs/wireframes/`, resultado de la Fase 1.
- **No hay material fotográfico adicional disponible** más allá de los 128 medios ya migrados
  — el cliente confirmó que por ahora se usa solo lo que ya está en el sitio
  (`docs/01-analisis-descubrimiento.md` §E #9). No inventar fotografía ni asumir una sesión.

## Product Principles

1. **Servir primero a quien decide la matrícula** — Admisiones es un ítem de primer nivel del
   menú, no un sub-ítem de Institución (`docs/02-ux-arquitectura-informacion.md` §1).
2. **Autoadministración real** — cada decisión de UI en el panel se mide contra si una persona
   no técnica puede hacerlo sola.
3. **Multiidioma sin fricción** — el italiano se activa/desactiva sin perder contenido ni
   romper la navegación (ADR-002).
4. **Seguridad como principio de diseño, no parche** — el antecedente de compromiso condiciona
   cada decisión de manejo de contenido subido por el usuario (HTML, SVG, archivos).
5. **Rendimiento móvil ante todo** — cualquier decisión visual se pesa contra el costo en Core
   Web Vitals antes de aprobarse.

## Accessibility & Inclusion

**WCAG 2.1 nivel AA en todo el sitio** — requerimiento explícito del proyecto
(`docs/00-legajo-tecnico.md` §5), no opcional. Navegación completa por teclado con
`:focus-visible`, objetivos táctiles ≥ 44×44 px, jerarquía de encabezados correcta (un solo
`h1` por página), contraste AA verificado color por color contra la paleta de marca (ver
`docs/01-analisis-descubrimiento.md` §B.1 — el celeste y el amarillo de marca **no pasan** como
color de texto, solo el negro).
