# CLAUDE.md — Proyecto Web Institucional Dante

> Este archivo es el contexto permanente del proyecto. Claude lo lee automáticamente
> al iniciar cualquier sesión en esta carpeta. **No lo borres ni lo muevas.**
> Si algo de acá cambia, actualizalo en el momento — este archivo es la fuente de verdad.

---

## 0. Regla de oro

Antes de escribir una sola línea de código de la web nueva, hay que **leer los tres
insumos** que están en `_insumos/` (repo IPG, manual de marca, WordPress actual + DB).
El proyecto **no** arranca de cero: arranca copiando un patrón ya probado en producción
(IPG) y vistiéndolo con la marca de Dante.

Trabajamos **por fases** (ver `PLAN.md`). No se salta de fase sin cerrar la anterior con
su entregable escrito en `docs/`. Cada decisión no trivial se registra como ADR en
`docs/decisiones/`.

---

## 1. Qué estamos construyendo

**Reemplazo total del sitio institucional actual de Dante (WordPress) por una web nueva
en Laravel**, con su propio panel de administración, lista para producción.

La salida esperada:

- Toda la información actual del sitio, **migrada y reorganizada** según una arquitectura
  de información nueva (no un calco del WordPress: el contenido se conserva, la estructura
  se rehace según UX).
- Diseño **fiel al manual de marca de Dante**, con estilos propios, intuitivo y con
  animación/motion cuidado (no plantilla genérica).
- **Autoadministración completa**: el cliente edita todo el contenido del sitio desde el
  panel, sin tocar código — con editor de texto enriquecido, gestión de medios, menús,
  y control de SEO por página.
- **Integraciones**: Meta (Pixel + Conversions API), Google Analytics 4 / GTM, captcha en
  formularios, y todo lo que use hoy el sitio actual.
- Rendimiento, SEO técnico, seguridad y QA tratados como fases propias, no como parche final.

**Fuera de alcance de esta primera entrega** (posible fase posterior, no se construye ahora
pero el modelo de datos no debe cerrarle la puerta): área privada de alumnos/padres/docentes,
inscripciones en línea, pasarela de pagos.

---

## 2. Contexto crítico de seguridad ⚠️

El WordPress actual de `dante.edu.py` **estuvo comprometido**. Esto condiciona cómo se
trabaja con el insumo #3:

- **NUNCA levantar el WordPress actual localmente ni ejecutar su código PHP.** Ni para
  "ver cómo se veía". Usar capturas de pantalla y el HTML renderizado, nada más.
- Del WordPress solo se consumen **datos**: el dump SQL y la carpeta `wp-content/uploads/`.
- El dump SQL se importa a una base **separada y de solo lectura** (`dante_wp_legacy`),
  jamás a la base de la web nueva. La migración lee de ahí y escribe en la base nueva.
- Los archivos de `uploads/` se escanean antes de copiarse: **solo se aceptan extensiones
  de imagen/documento en lista blanca** (jpg, jpeg, png, gif, webp, avif, svg, pdf, docx,
  xlsx, mp4). Cualquier `.php`, `.phtml`, `.htaccess`, `.ico` sospechoso o archivo con doble
  extensión se descarta y se registra en `docs/07-migracion-wordpress.md`.
- Los SVG se sanitizan antes de servirse (pueden llevar JS embebido).
- Ningún usuario, hash de contraseña ni sesión del WordPress se migra. Los usuarios del
  panel nuevo se crean de cero.
- La web nueva **no comparte credenciales** con la vieja.

---

## 3. Stack técnico

Estándar de webparaguay, con las versiones vigentes a agosto 2026:

| Capa | Elección | Nota |
|---|---|---|
| Lenguaje | PHP 8.3 (mínimo 8.3, ideal 8.4) | Verificar qué corre el Plesk destino antes de fijar |
| Framework | **Laravel 13.x** | Soporte activo hasta sep-2027, seguridad hasta mar-2028 |
| Base de datos | MySQL 8 / MariaDB | Charset `utf8mb4_unicode_ci` |
| Vistas | **Blade** | |
| Interactividad | **Livewire 4** + Alpine.js | |
| CSS | **Tailwind CSS** + Vite | Tokens del manual de marca en `tailwind.config` |
| Panel admin | **Filament 5** | Decidido en ADR-001 (`docs/decisiones/ADR-001-panel-admin.md`): IPG tiene panel a medida sin bloques, menús, 2FA, auditoría, buscador ni redirecciones — se reutiliza su modelo de datos, no su capa Blade/controllers |
| Despliegue | Servidor **Plesk** de webparaguay | Sin Docker en producción, sin acceso root garantizado |
| Testing | Pest 3 (unit/feature) + Playwright (E2E) | |

**Restricción de infraestructura:** el destino es Plesk. Asumir que **no hay Redis ni
Supervisor** hasta confirmar lo contrario. Colas y caché arrancan en `database`/`file`;
si Redis está disponible, se sube después. Los cron van por el Programador de tareas de
Plesk apuntando a `php artisan schedule:run`.

**Paquetes de referencia** (confirmar contra lo que ya usa IPG antes de sumar nada nuevo —
la regla es reutilizar, no coleccionar dependencias):

- `spatie/laravel-medialibrary` — medios y conversiones de imagen
- `spatie/laravel-sitemap` — sitemap.xml
- `spatie/laravel-backup` — respaldos
- `spatie/laravel-csp` — Content-Security-Policy
- `spatie/laravel-honeypot` — antispam en formularios
- `laravel/fortify` — auth del panel con 2FA
- Editor enriquecido: **el mismo que use IPG** (TipTap / CKEditor 5 / Trix). No introducir uno distinto.

---

## 4. Estructura de esta carpeta

```
proyecto-dante/
├── CLAUDE.md              ← estás acá: contexto permanente
├── EMPEZAR-ACA.md         ← guía de arranque para el humano
├── PLAN.md                ← plan por fases con checklist. Se actualiza al cerrar cada fase
├── EMPEZAR-ACA.md         ← guía de arranque paso a paso
├── docs/                  ← entregables de cada disciplina (se llenan durante el proyecto)
│   ├── 00-legajo-tecnico.md
│   ├── 01..12-*.md
│   └── decisiones/        ← ADRs
├── _insumos/              ← ACÁ van los tres insumos (ver _insumos/LEEME.md)
│   ├── 01-referencia-ipg/
│   ├── 02-marca-dante/
│   ├── 03-wordpress-actual/
│   └── 04-extra/
├── scripts/               ← verificar-insumos.sh · wp-inventario.sql
├── .claude/               ← comandos (/fase, /estado, /adr, /inventario-wp, /qa, /auditar)
│                            y subagentes propios del proyecto
└── app/ (aparece en Fase 3) ← acá se instala el Laravel nuevo
```

El Laravel nuevo se instala en `app/` **recién en la Fase 3**. Antes de eso esta carpeta
es puro análisis y diseño.

---

## 5. Herramientas de IA disponibles y cuándo usar cada una

Están instaladas globalmente en `~/.claude/`. Usalas — están para eso:

| Herramienta | Cuándo usarla en este proyecto |
|---|---|
| **impeccable** | Fase 2 (UI). Correr `/impeccable init` dentro de la carpeta del proyecto al empezar el diseño. Es la skill principal de diseño visual y consistencia de marca |
| **ux-flow-designer** | Fase 1 (UX). Flujos de usuario, wireframes y casos de uso a partir del análisis de contenido |
| **emil-design-eng** | Fase 4 (Frontend). Motion, transiciones, micro-interacciones. La web tiene que sentirse animada sin ser pesada |
| **context7** | Cualquier fase. Docs actualizadas de Laravel 13 / Livewire 4 / Filament 5 / Tailwind. **Usarla antes de escribir código contra una API que no conocés de memoria** — las versiones son nuevas |
| **playwright-cli** | Fase 9 (QA). Tests E2E. ⚠️ Solo credenciales de prueba, nunca las reales del cliente |
| **strix** | Fase 8 (Seguridad). Pentest sobre el staging propio. ⚠️ Solo contra nuestro entorno, con autorización explícita |
| **skill-security-auditor** | Antes de instalar cualquier skill nueva durante el proyecto. Sin excepción |
| **everything-claude-code** | Harness/memoria/hooks. Ya activo de fondo |

---

## 6. Cómo trabajar en este proyecto

1. **Leé el insumo antes de opinar.** Si una decisión depende de cómo lo hace IPG, andá a
   leer el código de IPG. No inventes un patrón nuevo cuando ya hay uno probado.
2. **Una fase a la vez.** Al terminar una fase, escribí su entregable en `docs/` y marcá el
   checklist en `PLAN.md`. Recién ahí se pasa a la siguiente.
3. **Decisiones no triviales → ADR.** Copiá `docs/decisiones/ADR-000-plantilla.md`, numerá,
   completá. Un ADR son 15 líneas, no un ensayo.
4. **Contenido real desde el principio.** Nada de "Lorem ipsum". Se maqueta con el texto
   real migrado del sitio actual (o el reescrito por copywriting). Un diseño validado con
   texto falso miente.
5. **Nada se da por terminado sin test.** Cada módulo del panel necesita al menos un test
   feature de Pest antes de considerarse cerrado.
6. **Preguntá cuando falte info del cliente.** Hay cosas que no están en ningún insumo
   (¿el colegio quiere el sitio en español e italiano? ¿qué formularios reciben datos
   personales de menores?). Listá esas preguntas en `docs/01-analisis-descubrimiento.md`
   en vez de asumir.

---

## 7. Convenciones de código

- **PSR-12**, aplicado con Laravel Pint (`vendor/bin/pint`). Correr antes de cada commit.
- **PHPStan/Larastan nivel 5** como piso.
- Nombres de clases, métodos, variables y tablas: **en inglés**. Contenido, textos de UI,
  labels del panel y comentarios de dominio: **en español**.
- Controllers finos: la lógica va en Actions o Services (`app/Actions/`, `app/Services/`).
- Nada de queries en Blade. Nada de `env()` fuera de `config/`.
- Migraciones siempre reversibles (`down()` real).
- Commits en español, imperativo, con prefijo de fase: `feat(f3): crear CRUD de noticias`.
- Rama por fase: `fase/3-backend`. `main` siempre desplegable.

---

## 8. Datos del proyecto

- **Cliente:** Dante (`dante.edu.py`) — institución educativa, Paraguay
- **Proveedor:** webparaguay — Gerente General y desarrollador: Leonardo Chi
- **Hosting destino:** servidor Plesk de webparaguay (`177.251.252.12`)
- **Mail:** queda en HostGator (`192.185.52.135`) — **el DNS de correo no se toca en el cutover**
- **SSL:** Let's Encrypt vía Plesk
- **Mercado/idioma del contenido:** español paraguayo **+ italiano**, multiidioma real con
  toggle de habilitación — confirmado en Fase 0, ver ADR-002
