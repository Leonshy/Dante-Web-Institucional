# 03 — WordPress actual (dante.edu.py)

## ⚠️ Leer esto primero

**Este WordPress estuvo comprometido.** De acá sacamos **datos**, no código. Nada de este
material se ejecuta: ni local, ni en el servidor, ni "solo un momento para ver cómo se veía".

Protocolo completo en `CLAUDE.md` §2.

---

## Estructura

```
03-wordpress-actual/
├── db/          ← el dump .sql
├── uploads/     ← wp-content/uploads/ solamente
└── export/      ← capturas, sitemap, exports (opcional pero muy útil)
```

---

## `db/` — La base de datos

**Qué va:** el dump SQL completo del WordPress.

```bash
# En el servidor origen, o desde phpMyAdmin / Plesk:
mysqldump -u USUARIO -p BASE > dante_wp.sql
gzip dante_wp.sql   # opcional, el .sql.gz también sirve
```

Dejalo como `_insumos/03-wordpress-actual/db/dante_wp.sql` (o `.sql.gz`).

**Qué se hace con él:** se importa a una base separada `dante_wp_legacy` con un usuario de
**solo lectura**. Nunca a la base de la web nueva. El comando de migración lee de ahí.

Si podés, anotá también en `db/notas.md`:
- Prefijo de las tablas (`wp_` o el que sea)
- Versión de WordPress
- Fecha del dump

---

## `uploads/` — Los archivos subidos

**Qué va:** el contenido de `wp-content/uploads/`, con su estructura de carpetas por año/mes.

**Qué NO va:** `themes/`, `plugins/`, `wp-admin/`, `wp-includes/`, ningún `.php` de la raíz.

Si la carpeta es muy pesada, podés traer solo los originales y descartar los tamaños
generados por WordPress (`*-150x150.jpg`, `*-300x200.jpg`, etc.) — el Laravel nuevo va a
generar sus propias variantes.

```bash
# Traer solo originales, sin los tamaños generados por WP:
rsync -av --exclude='*-[0-9]*x[0-9]*.*' origen/uploads/ _insumos/03-wordpress-actual/uploads/
```

**Qué se hace con ellos:** se filtran con lista blanca de extensiones, se verifica el MIME real,
se descarta cualquier `.php`, doble extensión o archivo sospechoso, y los SVG se sanitizan.
Cada descarte queda registrado en `docs/07-migracion-wordpress.md`.

---

## `export/` — Material de apoyo (opcional pero muy útil)

Cualquiera de estas cosas ahorra horas:

- [ ] **Capturas de pantalla** de cada plantilla del sitio actual (inicio, página interna,
      listado de noticias, detalle, contacto), en escritorio y móvil. Esta es la forma segura
      de "ver cómo era"
- [ ] `sitemap.xml` y `robots.txt` del sitio actual — dan la lista de URLs indexadas
- [ ] **Export XML de WordPress** (Herramientas → Exportar) — respaldo del dump, a veces más
      fácil de leer
- [ ] Lista de **plugins activos** con su versión
- [ ] Nombre y versión del **tema activo**
- [ ] Exportación de **Google Analytics**: páginas más visitadas de los últimos 12 meses
- [ ] Exportación de **Search Console**: consultas y páginas con impresiones
- [ ] Cualquier informe del incidente de seguridad, si existe

> Las capturas y la exportación de Analytics son las dos cosas que más valen acá. Con las
> capturas se puede diseñar sin tocar el sitio viejo; con Analytics se sabe qué páginas **no**
> hay que romper.

---

## Si no tenés acceso directo al servidor

Alternativas, en orden de preferencia:

1. Pedirle al proveedor de hosting anterior el dump y los uploads
2. Sacar el dump desde phpMyAdmin del panel de hosting
3. Si el WordPress todavía carga: Herramientas → Exportar (XML) + descargar `uploads/` por FTP
4. Como último recurso: rastrear el sitio público con Screaming Frog o `wget --mirror` para
   recuperar el contenido renderizado. Se pierden metadatos, pero el contenido se recupera

Documentá en `export/notas.md` qué vía usaste y qué quedó afuera.
