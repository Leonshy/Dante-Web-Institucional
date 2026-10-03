#!/usr/bin/env bash
#
# Vuelca SOLO el contenido de la base de staging, listo para importar en
# producción (después de `migrate --force`). Se corre en el servidor.
#
# Excluye a propósito: users, sessions, cache, jobs, form_submissions,
# activity_log, roles/permisos (los crea PermissionSeeder) e
# integration_settings (sus secretos están cifrados con el APP_KEY de staging
# y no se descifrarían en producción; se cargan de nuevo desde el panel).
#
# Uso: ./scripts/dump-contenido.sh BASE_STAGING USUARIO_MYSQL > contenido.sql
#      (pide la contraseña por teclado; nunca va en la línea de comandos)
set -euo pipefail

db="${1:?Uso: dump-contenido.sh BASE_STAGING USUARIO_MYSQL > contenido.sql}"
user="${2:?Falta el usuario MySQL}"
staging_host="${STAGING_HOST:-dante.webparaguay.com}"
prod_host="${PROD_HOST:-dante.edu.py}"

tables=(
    site_settings home_settings locations categories pages posts documents
    announcements calendar_events media galleries gallery_media
    menus menu_items redirects
)

echo "SET FOREIGN_KEY_CHECKS=0;"
mysqldump -u "$user" -p --no-create-info --complete-insert --skip-triggers \
    --skip-add-locks --default-character-set=utf8mb4 "$db" "${tables[@]}" \
    | sed "s#${staging_host//./\\.}#${prod_host}#g"
echo "SET FOREIGN_KEY_CHECKS=1;"
