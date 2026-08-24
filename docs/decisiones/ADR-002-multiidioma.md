# ADR-002 — Sitio multiidioma (español/italiano) con toggle

---

**Estado:** aceptada
**Fecha:** 2026-08-24
**Fase:** 0
**Decide:** Leonardo Chi (webparaguay), a pedido explícito del cliente

## Contexto

`docs/00-legajo-tecnico.md` §4 y `CLAUDE.md` §1 dejaban el multiidioma **fuera del alcance del
MVP**, condicionado a que el cliente lo pidiera al responder la pregunta abierta #1 de la Fase 0.
El cliente respondió: el sitio debe soportar español e italiano, con **todo el contenido
existente en los dos idiomas** cuando el italiano esté habilitado, y el italiano debe poder
**activarse/desactivarse** desde el panel (no es una decisión de una sola vez al lanzar).
Tiene sentido institucional: Dante es un colegio afiliado a la Società Dante Alighieri
(Italia), con el italiano como parte central de su identidad y oferta educativa.

## Opciones consideradas

| Opción | A favor | En contra |
|---|---|---|
| Sitio solo en español, italiano como fase futura | No toca el alcance ya cotizado, entrega más rápida | No es lo que pidió el cliente — hay que rehacerlo si se pide después, con el costo de migrar contenido ya cargado |
| Multiidioma real desde el modelo de datos (campos traducibles + toggle) | Cumple lo pedido, evita reconstruir el modelo de datos más adelante, el toggle da flexibilidad operativa | Sube el costo y el tiempo de casi todas las fases — contenido duplicado, formularios duplicados, SEO con hreflang |
| Traducción automática en el frontend (widget tipo Google Translate) | Más barato y rápido | No es "multiidioma" real — no permite editorial control por idioma, mala calidad de traducción para contenido institucional, no es lo que el cliente pidió |

## Decisión

El sitio se construye **multiidioma real desde el modelo de datos** (español e italiano),
con un interruptor en la configuración global del panel para habilitar/deshabilitar la
visualización del italiano en el frontend sin perder el contenido cargado.

## Motivo

Es un pedido explícito y no negociable del cliente, alineado con la identidad institucional
(afiliación a la Società Dante Alighieri). La alternativa de traducción automática no cumple
el requisito de "tiene que ser en los dos idiomas" con calidad editorial. Construirlo desde el
modelo de datos de entrada es más barato en el total del proyecto que agregarlo después: cada
tabla de contenido (páginas, noticias, categorías, menús, campos SEO) necesita duplicar sus
campos de texto por idioma, y hacerlo retroactivamente sobre contenido ya cargado en Fase 3–5
sería más caro que diseñarlo bien desde el principio.

## Consecuencias

- **Modelo de datos (Fase 3):** todo campo de texto editorial (`title`, `content`, `excerpt`,
  meta SEO, labels de menú) necesita estrategia de traducción. Evaluar `spatie/laravel-translatable`
  (columnas JSON por idioma) vs. tablas de traducción separadas — decisión técnica a tomar en
  Fase 3, no bloquea el resto de la Fase 0.
- **Panel (Filament, ADR-001):** cada formulario de contenido necesita pestañas o campos
  duplicados por idioma. El toggle de "italiano habilitado/deshabilitado" vive en la
  configuración global (`site_settings`, patrón heredado de IPG).
- **Frontend (Fase 4):** rutas con prefijo de idioma (`/it/...`), selector de idioma visible
  solo si el toggle está activo, fallback a español si falta la traducción de una pieza de
  contenido.
- **SEO (Fase 6):** hreflang `es`/`it`, sitemap por idioma, URLs canónicas por versión.
- **Copywriting (Fase 2):** el trabajo de redacción se duplica — hay que traducir o redactar en
  ambos idiomas todo el contenido nuevo, no solo el español.
- **Estimación:** sube el total del proyecto de forma significativa en las Fases 2, 3, 4 y 6.
  Un ajuste de horas detallado por fase queda pendiente como tarea de seguimiento — la magnitud
  aproximada es de **+15–20 % sobre el total ajustado en `docs/01-analisis-descubrimiento.md`
  §F** (204–278 h → estimado ~235–330 h), a confirmar cuando se diseñe el modelo de datos
  concreto en Fase 3.
- `docs/00-legajo-tecnico.md` §4 se actualiza: "Multiidioma" pasa de "NO entra en el MVP" a
  "Entra en el MVP".
- Revertir esta decisión más adelante (sacar el italiano) es barato — el toggle está pensado
  justamente para eso. Lo caro sería agregarlo después si no se hubiera decidido ahora.
