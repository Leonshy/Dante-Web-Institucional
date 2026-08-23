# EMPEZAR ACÁ

Guía de arranque. Diez minutos y estás trabajando.

---

## Paso 1 — Poner la carpeta donde va

Descomprimí el zip y dejá la carpeta `proyecto-dante/` donde tengas tus proyectos.
Renombrala si querés (`dante-web`, `dante.edu.py`, lo que uses).

Abrila en VS Code:

```bash
code proyecto-dante
```

---

## Paso 2 — Cargar los tres insumos

Esta es la parte que te interesa. **Cada cosa tiene su carpeta:**

| Qué tenés | Dónde va | Cómo |
|---|---|---|
| **Repo de IPG** (web institucional autoadministrable que funciona) | `_insumos/01-referencia-ipg/` | `git clone` adentro, o copiar el código. Sin el `vendor/` ni el `node_modules/` |
| **Manual de marca de Dante** | `_insumos/02-marca-dante/` | El PDF del manual + los logos en vectorial + las tipografías si las tenés |
| **WordPress actual: base de datos** | `_insumos/03-wordpress-actual/db/` | El dump `.sql` (o `.sql.gz`) |
| **WordPress actual: archivos subidos** | `_insumos/03-wordpress-actual/uploads/` | Solo `wp-content/uploads/`. **No el resto del WordPress** |
| Capturas, sitemap, exports extra | `_insumos/03-wordpress-actual/export/` | Opcional pero muy útil |
| Cualquier otra cosa (accesos, notas del cliente, textos) | `_insumos/04-extra/` | |

Cada una de esas carpetas tiene su propio `LEEME.md` explicando qué se espera adentro
y qué **no** hay que poner. Leelos, sobre todo el de WordPress.

⚠️ **Lo más importante:** el WordPress de Dante estuvo comprometido. Del sitio viejo solo
copiamos **datos** (el dump SQL y los uploads). **No copies los archivos PHP del WordPress
ni los levantes localmente.** Está explicado en `CLAUDE.md` §2.

---

## Paso 3 — Proteger lo que no debe subir a git

Ya viene un `.gitignore` que excluye `_insumos/` entero. Los insumos son material del
cliente: dump de base, manual de marca, posiblemente credenciales. **No van al repo.**

Si querés versionar el proyecto:

```bash
cd proyecto-dante
git init
git add .
git commit -m "chore: scaffold inicial del proyecto Dante"
```

Verificá con `git status` que `_insumos/` no aparece.

---

## Paso 4 — Inicializar el diseño

Antes de la fase de UI, corré la skill de diseño dentro de la carpeta:

```
/impeccable init
```

---

## Paso 5 — Arrancar con Claude

Abrí Claude Code en la carpeta. Va a leer `CLAUDE.md` solo. Después:

```
/fase 0
```

Ese comando arranca el descubrimiento: lee el repo de IPG, lee el manual de marca, hace el
inventario del WordPress y completa el legajo técnico.

Si preferís ir a mano, el primer pedido útil es:

> Leé `CLAUDE.md` y `PLAN.md`. Después leé el repo en `_insumos/01-referencia-ipg/` completo
> y escribime en `docs/01-analisis-descubrimiento.md` el mapa de cómo resuelve IPG el panel
> de administración, el modelo de datos, el editor enriquecido, los medios, los menús y el SEO.
> Marcá qué se copia tal cual, qué se adapta y qué no aplica para Dante.

---

## Comandos disponibles

| Comando | Qué hace |
|---|---|
| `/fase <n>` | Arranca o retoma una fase del `PLAN.md`, con su checklist |
| `/estado` | Dónde está el proyecto, qué falta, qué está bloqueado |
| `/adr <tema>` | Crea un registro de decisión de arquitectura numerado |
| `/inventario-wp` | Corre el inventario del WordPress viejo y llena la tabla de contenido |
| `/qa` | Pasada de QA sobre lo construido hasta ahora |
| `/auditar` | Auditoría de seguridad + rendimiento sobre el estado actual |

## Subagentes disponibles

En `.claude/agents/`. Se invocan solos cuando la tarea corresponde, o los llamás por nombre:
`analista-ux`, `copywriter-institucional`, `backend-laravel`, `frontend-blade`,
`seo-rendimiento`, `seguridad-qa`, `migrador-wp`.

---

## Orden de lectura recomendado

1. `CLAUDE.md` — el contexto completo
2. `PLAN.md` — las 10 fases y sus checklists
3. `docs/00-legajo-tecnico.md` — el legajo en formato webparaguay
4. `_insumos/LEEME.md` — el detalle de qué va en cada carpeta
