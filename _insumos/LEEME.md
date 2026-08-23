# _insumos — Dónde va cada cosa

Esta carpeta es el material de entrada del proyecto. **No se versiona en git** (está en el
`.gitignore`): contiene material del cliente, un dump de base de datos y posiblemente
credenciales.

---

## Mapa rápido

```
_insumos/
├── 01-referencia-ipg/        ← el repo de IPG
├── 02-marca-dante/           ← el manual de marca + logos + tipografías
├── 03-wordpress-actual/
│   ├── db/                   ← el dump .sql del WordPress
│   ├── uploads/              ← wp-content/uploads/ y NADA más
│   └── export/               ← capturas, sitemap, exports opcionales
└── 04-extra/                 ← todo lo demás
```

---

## ⚠️ Regla de seguridad sobre el insumo 3

El WordPress de Dante **estuvo comprometido**. Del sitio viejo copiamos **datos**, no código:

| ✅ Sí copiar | ❌ No copiar |
|---|---|
| El dump `.sql` | `wp-admin/`, `wp-includes/` |
| `wp-content/uploads/` | `wp-content/themes/` |
| Capturas de pantalla | `wp-content/plugins/` |
| `sitemap.xml`, `robots.txt` | `wp-config.php`, `index.php`, cualquier `.php` |

**El WordPress no se levanta localmente, ni "solo para ver cómo era".** Para eso están las
capturas. El detalle completo del protocolo está en `CLAUDE.md` §2.

---

## Prioridad de carga

Si no tenés todo junto, cargá en este orden — el proyecto puede arrancar con el primero:

1. **`01-referencia-ipg/`** — sin esto no se puede decidir la arquitectura. Es lo primero.
2. **`03-wordpress-actual/db/`** — sin esto no hay inventario de contenido ni estimación real.
3. **`02-marca-dante/`** — se necesita recién en la Fase 2, pero cuanto antes esté, mejor
   (sobre todo para verificar la licencia de las tipografías, que puede ser un costo no previsto).

---

## Verificación

Cuando termines de cargar, corré:

```bash
bash scripts/verificar-insumos.sh
```

Te dice qué falta y si algo quedó donde no va.
