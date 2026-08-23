---
description: Crea un registro de decisión de arquitectura
argument-hint: "<tema de la decisión>"
---

Creá un ADR sobre: **$ARGUMENTS**

1. Mirá `docs/decisiones/` y tomá el siguiente número libre.
2. Copiá la estructura de `docs/decisiones/ADR-000-plantilla.md`.
3. Nombrá el archivo `ADR-XXX-<tema-en-kebab-case>.md`.
4. Completalo:
   - **Contexto**: qué situación obliga a decidir, en 2–3 frases.
   - **Opciones**: al menos dos reales, con a favor y en contra. Si solo hay una opción,
     no hace falta un ADR.
   - **Decisión**: una frase.
   - **Motivo**: evaluado explícitamente contra los criterios del proyecto — rentabilidad,
     velocidad de implementación, viabilidad con equipo de 3, tecnología probada.
   - **Consecuencias**: qué queda cerrado, qué queda abierto, cuánto costaría revertirla.
5. Si la decisión afecta a `CLAUDE.md` o al legajo técnico, actualizalos en la misma pasada.

Un ADR son 15 líneas. Si te sale más largo, la decisión todavía no está tomada.
