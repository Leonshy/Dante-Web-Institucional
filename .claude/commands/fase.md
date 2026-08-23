---
description: Arranca o retoma una fase del PLAN.md
argument-hint: "<número de fase>"
---

Arrancá o retomá la **Fase $1** del proyecto Dante.

Procedimiento:

1. Leé `CLAUDE.md` completo si todavía no lo hiciste en esta sesión.
2. Leé la sección de la Fase $1 en `PLAN.md`: objetivo, checklist, entregable y DoD.
3. Verificá que la fase anterior esté cerrada. Si no lo está, avisame y no sigas.
4. Verificá que los insumos que la fase necesita estén cargados en `_insumos/`. Si falta
   alguno, decime cuál y detenete.
5. Abrí el documento de entrega de la fase en `docs/` y mirá qué ya está completo.
6. Usá la herramienta que corresponde a esta fase según `CLAUDE.md` §5.
7. Trabajá el checklist en orden, **de a un ítem**. Al terminar cada uno, escribí el resultado
   en el documento de `docs/` correspondiente y tildá el checklist en `PLAN.md`.
8. Si una decisión no trivial aparece en el camino, creá un ADR con `/adr`.
9. Al terminar la fase: verificá el DoD, actualizá el registro de fases cerradas en `PLAN.md`
   con las horas reales, y resumime qué quedó hecho y qué necesitás de mí para seguir.

Reglas:

- No inventes contenido. Si un dato tiene que salir de un insumo, andá a leer el insumo.
- Si un dato tiene que salir del cliente y no lo tenemos, agregalo a la tabla de preguntas
  abiertas en `docs/01-analisis-descubrimiento.md` §E en vez de asumirlo.
- No saltes ítems del checklist porque parezcan menores.
- No pases a la fase siguiente sin que yo lo apruebe.
