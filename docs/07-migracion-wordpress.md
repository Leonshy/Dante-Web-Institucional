# 07 — Migración desde WordPress (Fase 5)

Estado: **vacío — completar en Fase 5**

> ⚠️ **Leer `CLAUDE.md` §2 antes de tocar nada.** El WordPress origen estuvo comprometido.

---

## 1. Protocolo de seguridad de la migración

- [ ] El dump se importa a la base **`dante_wp_legacy`**, con un usuario MySQL de **solo lectura**
- [ ] Esa base está declarada como conexión secundaria en `config/database.php` y **nunca** se
      escribe en ella
- [ ] **No se ejecuta ni un archivo PHP del WordPress**, ni localmente ni en el servidor
- [ ] Los `uploads/` se copian con lista blanca de extensiones (ver §4)
- [ ] Los SVG se sanitizan antes de servirse
- [ ] **No se migra ningún usuario, hash de contraseña, sesión ni token**
- [ ] Se revisa el contenido en busca de inyecciones (enlaces de spam, iframes, scripts) antes
      de darlo por migrado
- [ ] Una vez terminada y verificada la migración, la base legacy se elimina del entorno de
      desarrollo

---

## 2. Comando de migración

`php artisan dante:migrate-wp [--dry-run] [--only=pages|posts|media|menus]`

**Requisitos:**

- **Idempotente**: correrlo dos veces no duplica nada (clave de correspondencia: el ID de
  WordPress guardado en el registro nuevo)
- `--dry-run` informa qué haría sin escribir
- Registro detallado en `storage/logs/migration.log`
- Transaccional por lote, con reanudación ante fallo

---

## 3. Mapa de correspondencias

| Origen (WordPress) | Destino (Laravel) | Transformación |
|---|---|---|
| `wp_posts` (post_type=page) | `pages` | según el mapa de la Fase 1 |
| `wp_posts` (post_type=post) | `posts` | |
| `wp_posts` (post_type=attachment) | `media` | + conversiones |
| `wp_postmeta._yoast_wpseo_title` | `pages.seo_title` | |
| `wp_postmeta._yoast_wpseo_metadesc` | `pages.seo_description` | |
| `wp_postmeta._thumbnail_id` | imagen de portada | |
| `wp_terms` + `wp_term_taxonomy` | `categories` | |
| `wp_term_relationships` | pivote | |
| `wp_posts` (post_type=nav_menu_item) | `menu_items` | |
| `post_content` | bloques de contenido | ver §5 |
| Permalinks | `redirects` | ver §7 |

**Campos personalizados / ACF encontrados:**

| Campo | Dónde se usa | Destino |
|---|---|---|

---

## 4. Migración de medios

**Lista blanca de extensiones:**
`jpg jpeg png gif webp avif svg pdf docx xlsx pptx mp4 webm`

**Se descarta y se registra:** todo lo demás. Especialmente `.php`, `.phtml`, `.php5`,
`.htaccess`, `.js`, archivos con doble extensión (`foto.jpg.php`), y cualquier archivo cuyo
tipo MIME real no coincida con su extensión.

**Verificación de MIME real:** `finfo`, no la extensión.

| Archivos totales en `uploads/` | |
|---|---|
| Migrados | |
| Descartados por extensión | |
| Descartados por MIME inconsistente | |
| Descartados por huérfanos (sin referencia) | |

### Archivos sospechosos encontrados

| Archivo | Motivo | Acción |
|---|---|---|

> Esta tabla es evidencia del incidente. Guardarla — al cliente le va a interesar y sirve
> para el informe de cierre.

---

## 5. Limpieza del HTML de WordPress

El `post_content` de WordPress viene sucio. Se limpia antes de guardar:

- [ ] Quitar shortcodes (`[...]`) — mapear los que tengan equivalente a un bloque
- [ ] Quitar clases del tema viejo (`wp-block-*`, `alignleft`, clases del constructor visual)
- [ ] Quitar estilos en línea
- [ ] Quitar `<div>` y `<p>` vacíos
- [ ] Convertir `<b>`/`<i>` a `<strong>`/`<em>`
- [ ] Normalizar encabezados: el `h1` del contenido pasa a `h2` (el `h1` es el título de la página)
- [ ] Reescribir las URLs internas (viejas → nuevas)
- [ ] Reescribir las rutas de imágenes al almacenamiento nuevo
- [ ] Pasar el resultado por la misma sanitización del editor (`docs/05` §5)
- [ ] Detectar y quitar inyecciones de spam

**Shortcodes encontrados y su tratamiento:**

| Shortcode | Frecuencia | Reemplazo |
|---|---|---|

---

## 6. Conversión a bloques

Si el contenido plano se convierte a bloques del constructor, la heurística:

| Patrón en el HTML | Bloque destino |
|---|---|
| Párrafos y listas seguidos | `text` |
| Imagen suelta + párrafo | `image_text` |
| Varias imágenes seguidas | `gallery` |
| `h2` + lista de `h3` con texto | `accordion` |

Todo lo que no encaje en un patrón, va a un bloque `text` — es preferible eso a inventar
estructura que el cliente después no entienda.

---

## 7. Redirecciones 301

Origen: `docs/redirecciones-301.csv` de la Fase 1, más lo que salga del inventario.

```bash
php artisan dante:import-redirects docs/redirecciones-301.csv
```

- [ ] Toda URL del inventario tiene una fila
- [ ] Ninguna redirección apunta a un 404
- [ ] Sin cadenas de redirección (A→B→C). Se aplanan a A→C
- [ ] Sin bucles
- [ ] Verificación automática: recorrer el CSV y comprobar que cada origen devuelve 301 al
      destino esperado

---

## 8. Verificación de la migración

| Verificación | Resultado |
|---|---|
| Páginas: origen vs. destino | / |
| Noticias: origen vs. destino | / |
| Medios: origen vs. destino | / |
| Categorías | / |
| Elementos de menú | / |
| Revisión manual de una muestra del 10 % | ☐ |
| Enlaces internos rotos (`dante:check-links`) | |
| Imágenes rotas | |
| Contenido con codificación rota (tildes, ñ) | |
| Redirecciones verificadas | / |

---

## 9. Reporte final

**Migrado:**
**Descartado y por qué:**
**Requiere carga manual del cliente:**
**Diferencias con el sitio viejo que hay que avisarle al cliente:**

---

## 10. Migración del delta (previa al cutover)

Entre la migración y la salida a producción, el cliente probablemente publique algo nuevo en
el WordPress viejo.

- [ ] Fecha de **congelamiento de contenido** acordada con el cliente:
- [ ] Migración del delta ejecutada el:
- [ ] Verificada: ☐
