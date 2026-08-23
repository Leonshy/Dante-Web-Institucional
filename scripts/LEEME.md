# scripts/

Utilidades del proyecto.

| Script | Qué hace | Cuándo |
|---|---|---|
| `verificar-insumos.sh` | Verifica que los tres insumos estén cargados y que no haya archivos peligrosos del WordPress comprometido | Antes de arrancar la Fase 0 |
| `wp-inventario.sql` | Inventario completo del WordPress viejo, incluyendo indicadores de compromiso | Fase 0 |

## Uso

```bash
bash scripts/verificar-insumos.sh
```

```bash
# Importar el dump a una base separada, de solo lectura
mysql -u root -p -e "CREATE DATABASE dante_wp_legacy CHARACTER SET utf8mb4;"
mysql -u root -p dante_wp_legacy < _insumos/03-wordpress-actual/db/dante_wp.sql

# Usuario de solo lectura
mysql -u root -p -e "
  CREATE USER 'wp_lector'@'localhost' IDENTIFIED BY '...';
  GRANT SELECT ON dante_wp_legacy.* TO 'wp_lector'@'localhost';
"

# Correr el inventario
mysql -u wp_lector -p dante_wp_legacy < scripts/wp-inventario.sql > docs/inventario-crudo.txt
```

⚠️ La base legacy es de **solo lectura** y se elimina del entorno una vez terminada y
verificada la migración.
