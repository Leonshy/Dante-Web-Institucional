---
description: Muestra el estado real del proyecto Dante
---

Dame el estado del proyecto, verificado contra los archivos — no contra lo que diga el
encabezado de `PLAN.md`.

1. Leé `PLAN.md` y contá, por fase, ítems tildados vs. total.
2. Leé cada documento de `docs/` y fijate cuánto está realmente completo (las secciones
   vacías o con placeholders no cuentan como hechas).
3. Revisá qué hay en `_insumos/` y qué falta.
4. Si existe `app/`, revisá el estado del código: ¿corre la suite? ¿pasa Pint? ¿pasa Larastan?
5. Listá los ADR existentes y las decisiones que siguen abiertas.
6. Listá las preguntas abiertas al cliente que siguen sin respuesta.

Presentámelo así:

- **Fase actual y % de avance**
- **Bloqueantes** — qué impide avanzar ahora mismo y qué necesito hacer yo
- **Riesgos que se activaron** — comparando contra la sección 8 del legajo
- **Siguientes 3 acciones concretas**

Sé directo. Si algo está más atrasado de lo que parece, decilo.
