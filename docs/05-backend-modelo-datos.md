# 05 — Backend y modelo de datos (Fase 3)

Estado: **vacío — completar en Fase 3**

> Punto de partida obligatorio: el análisis de IPG en `docs/01-analisis-descubrimiento.md` §A.
> No se diseña un modelo de datos nuevo si IPG ya tiene uno que funciona.

---

## 1. Decisiones de arquitectura

| Decisión | Elección | ADR |
|---|---|---|
| Enfoque del panel admin | | ADR-001 |
| Editor de texto enriquecido | | |
| Gestión de medios | | |
| Constructor de bloques | | |
| Estrategia de caché | | |
| Colas | | |

---

## 2. Modelo de datos

### Diagrama

```
(diagrama de entidades — mermaid o ASCII)
```

### Tablas

#### `pages`

| Columna | Tipo | Nulo | Índice | Nota |
|---|---|---|---|---|
| id | bigint | no | PK | |
| parent_id | bigint | sí | FK, idx | jerarquía |
| slug | string | no | unique | |
| title | string | no | | |
| template | string | no | | |
| seo_title | string | sí | | |
| seo_description | text | sí | | |
| og_image_id | bigint | sí | FK | |
| canonical_url | string | sí | | |
| is_indexable | bool | no | | |
| status | enum | no | idx | draft/published/archived |
| published_at | timestamp | sí | idx | |
| sort_order | int | no | | |
| created_by / updated_by | bigint | | | auditoría |
| timestamps + softDeletes | | | | |

#### `content_blocks`

| Columna | Tipo | Nota |
|---|---|---|
| id | bigint | |
| blockable_type / blockable_id | morphs | página, noticia, etc. |
| type | string | hero, text, gallery… |
| data | json | payload del bloque |
| sort_order | int | |

#### `posts` (noticias)

#### `categories`

#### `media`

#### `menus` / `menu_items`

#### `forms` / `form_fields` / `form_submissions`

#### `redirects`

| Columna | Tipo | Nota |
|---|---|---|
| from_path | string | unique, indexado |
| to_path | string | |
| status_code | int | 301 por defecto |
| hits | int | para saber cuáles importan |
| last_hit_at | timestamp | |

#### `settings`

#### `users` / `roles`

#### `activity_log`

---

## 3. Rutas

### Públicas

| Método | Ruta | Controlador | Nombre |
|---|---|---|---|
| GET | `/` | | `home` |
| GET | `/noticias` | | `posts.index` |
| GET | `/noticias/{slug}` | | `posts.show` |
| GET | `/buscar` | | `search` |
| POST | `/formularios/{form}` | | `forms.submit` |
| GET | `/{path}` | | `pages.show` — catch-all al final |
| GET | `/sitemap.xml` | | |

> El catch-all de páginas va **último**, y antes pasa por el middleware de redirecciones.

### Panel

Prefijo: `/[ruta-no-adivinable]` — definir y guardar en `.env`, no en el repo.

---

## 4. Panel de administración

### Estructura de navegación

| Sección | Permisos | Nota |
|---|---|---|
| Escritorio | todos | resumen, últimos cambios |
| Páginas | admin, editor | |
| Noticias | admin, editor | |
| Categorías | admin | |
| Medios | admin, editor | |
| Menús | admin | |
| Formularios y envíos | admin, editor | |
| Redirecciones | admin | |
| Configuración | admin | |
| Usuarios | admin | |
| Auditoría | admin | solo lectura |

### Roles y permisos

| Acción | Admin | Editor |
|---|---|---|
| Crear/editar contenido | ✅ | ✅ |
| Publicar | ✅ | ✅ |
| Eliminar | ✅ | ❌ |
| Gestionar menús | ✅ | ❌ |
| Gestionar usuarios | ✅ | ❌ |
| Cambiar configuración | ✅ | ❌ |
| Ver auditoría | ✅ | ❌ |

### Requisitos de usabilidad del panel

> El KPI del proyecto es que el cliente publique sin ayuda. El panel se diseña con ese
> criterio, no con el de "que tenga todos los campos".

- [ ] Etiquetas y textos de ayuda **en español, sin jerga técnica** (nada de "slug", "meta
      description" a secas — explicar qué hace cada cosa)
- [ ] Vista previa antes de publicar
- [ ] Autoguardado de borradores
- [ ] Confirmación antes de acciones destructivas
- [ ] Mensajes de error que digan qué hacer, no qué falló
- [ ] Los campos obligatorios marcados y validados en el momento
- [ ] Subida de imágenes con recorte y aviso si el peso es excesivo
- [ ] Alt text con recordatorio visible de por qué importa

---

## 5. Sanitización del HTML del editor

**Nunca confiar en el HTML que llega del editor.** Lista blanca en el servidor:

**Etiquetas permitidas:**
`p, br, strong, em, u, s, h2, h3, h4, ul, ol, li, a, blockquote, table, thead, tbody, tr, th, td, img, figure, figcaption, hr`

**Atributos permitidos:**
`href` (solo `http`, `https`, `mailto`, `tel` y rutas relativas), `title`, `alt`, `src`
(solo dominio propio), `class` (lista blanca), `colspan`, `rowspan`

**Siempre eliminado:** `script`, `iframe` (salvo lista blanca de dominios de video),
`object`, `embed`, `form`, `style`, todo atributo `on*`, `javascript:` en cualquier URL.

Implementación:

---

## 6. Estrategia de caché

| Qué | Dónde | Invalidación |
|---|---|---|
| Páginas públicas | | al publicar/editar |
| Configuración global | | al guardar |
| Menús | | al guardar |
| Consultas pesadas | | |

Arranca en `file`/`database` (Plesk sin Redis asumido). Si hay Redis, se cambia el driver
y nada más.

---

## 7. Comandos artisan del proyecto

| Comando | Qué hace |
|---|---|
| `dante:migrate-wp` | Migración desde la base legacy (Fase 5) |
| `dante:import-redirects` | Carga el CSV del mapa 301 |
| `dante:check-links` | Detecta enlaces internos rotos |
| `dante:sitemap` | Regenera el sitemap |

---

## 8. Tests de la fase

| Módulo | Test feature | Estado |
|---|---|---|
| Auth del panel | login, 2FA, bloqueo por intentos | ☐ |
| Páginas | CRUD, publicación, permisos por rol | ☐ |
| Noticias | CRUD, categorías, publicación | ☐ |
| Medios | subida, conversión, validación de tipo | ☐ |
| Menús | CRUD, orden | ☐ |
| Formularios | envío, honeypot, captcha, rate limit | ☐ |
| Redirecciones | 301 responde correctamente | ☐ |
| Configuración | guardado y lectura | ☐ |
| Auditoría | se registra cada cambio | ☐ |
| Sanitización HTML | el payload malicioso se limpia | ☐ |

---

## 9. Cierre de la Fase 3

**Prueba de usabilidad:** una persona no técnica creó, editó, despublicó y borró contenido
sin ayuda: ☐ — quién y cuándo:
**Suite Pest en verde:** ☐
**Luz verde para Fase 4:** ☐
