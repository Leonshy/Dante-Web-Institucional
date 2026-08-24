# ADR-001 — Enfoque del panel de administración

---

**Estado:** aceptada
**Fecha:** 2026-08-24
**Fase:** 0
**Decide:** Leonardo Chi (webparaguay), con análisis técnico de Claude sobre el repo IPG

## Contexto

`CLAUDE.md` §3 exige decidir en Fase 0 entre replicar el patrón de panel del repo de
referencia IPG o usar Filament 5 como fallback. La lectura completa de
`_insumos/01-referencia-ipg/IGP-Metales/` (ver `docs/01-analisis-descubrimiento.md` §A) muestra
que IPG tiene un panel 100 % a medida (controllers CRUD clásicos + Blade, sin Livewire ni
Filament, recarga completa de página), pero le faltan piezas grandes que Dante sí necesita:
constructor de bloques, gestión de menús desde el panel, 2FA, registro de auditoría, buscador
interno, redirecciones 301 gestionables, sanitización HTML server-side y CRUD de categorías.

## Opciones consideradas

| Opción | A favor | En contra |
|---|---|---|
| Replicar el patrón de IPG tal cual | Consistencia con lo que webparaguay ya conoce y mantiene; cero curva de aprendizaje | Cada pieza que le falta a IPG (bloques, menús, 2FA, auditoría, buscador, redirecciones, sanitización) se construye desde cero — infla la Fase 3 (48–64 h ya es la fase más pesada) |
| Filament 5 | Resuelve de fábrica CRUDs, relaciones, formularios, subida de medios, filtros y permisos; existen plugins maduros para 2FA y activity log que cubren gran parte de lo que le falta a IPG; reduce horas de Fase 3 con un equipo de 3 que incluye un junior | Se aparta del panel Blade puro de IPG — el patrón de "panel a medida" no queda como activo reutilizable tal cual para el próximo cliente de este tipo |

## Decisión

Se usa **Filament 5** para el panel de administración de Dante, reutilizando del repo IPG el
**modelo de datos y sus convenciones** (tabla `media` central, `site_settings` clave-valor,
`categories` polimórfica, `spatie/laravel-permission` para roles) pero no su capa de
controllers/vistas Blade a medida.

## Motivo

Contra los criterios del proyecto — rentabilidad, velocidad de implementación, viabilidad con
equipo chico, tecnología probada: la lista de "falta en IPG" (`docs/01-analisis-descubrimiento.md`
§A.4) es larga y coincide casi exactamente con lo que Filament resuelve de fábrica (resources,
relation managers, form builder) o con plugins ya maduros de su ecosistema (2FA, activity log).
Construir todo eso a mano sobre el patrón Blade de IPG es más trabajo para el mismo resultado,
con un equipo de 3 que incluye un perfil junior — exactamente el escenario donde Filament rinde
más. El modelo de datos de IPG sigue siendo el insumo válido: la decisión es sobre la capa de
presentación/administración del panel, no sobre cómo se estructuran las tablas.

## Consecuencias

- La Fase 3 arranca instalando Filament 5 sobre Laravel 13, no replicando `app/Http/Controllers/Admin/*` de IPG.
- El editor de texto enriquecido de IPG es TinyMCE sin sanitización server-side — Filament no
  resuelve esto solo: hay que sanitizar el HTML en el servidor de todos modos (lista blanca de
  etiquetas/atributos), tal como ya estaba previsto en `CLAUDE.md` §7 y §3, independientemente
  del framework de panel.
- El "patrón reutilizable" que queda documentado al cerrar el proyecto (`docs/00-legajo-tecnico.md`
  §3, "producto derivado") va a ser un panel Filament + modelo de datos tipo-IPG, no un panel
  Blade a medida — ajustar esa expectativa para futuros proyectos similares.
- Revertir esta decisión más adelante (volver a un panel a medida) implicaría rehacer toda la
  capa de administración — costo alto. No se espera revertir salvo que Filament 5 muestre
  limitaciones serias durante la Fase 3, en cuyo caso se documenta un ADR nuevo que reemplace este.
