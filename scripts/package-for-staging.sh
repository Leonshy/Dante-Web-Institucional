#!/usr/bin/env bash
#
# Empaqueta la app para subir por scp mientras no haya `git pull` configurado
# en el servidor (Fase 10 — Node del Plesk es muy viejo para compilar Vite,
# ver docs/12-deploy-plesk.md §2). Corre `npm run build` primero.
#
# Hallazgo real (2026-08-26): un primer intento de este empaquetado incluyó
# `public/storage` — el symlink que `php artisan storage:link` crea en LOCAL,
# apuntando a una ruta del disco de esta máquina — y al extraerlo en el
# servidor pisó el symlink correcto, dejando todos los medios con 403. `tar`
# no respeta `.gitignore` (ahí sí está excluido), así que acá se excluye a mano.
#
# Uso: ./scripts/package-for-staging.sh
# Genera: /tmp/dante-redeploy.tar.gz
set -euo pipefail

cd "$(dirname "$0")/../app"

echo "==> Compilando assets (Vite)"
npm run build

echo "==> Empaquetando"
COPYFILE_DISABLE=1 tar -czf /tmp/dante-redeploy.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='public/storage' \
    --exclude='storage/logs/*' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*' \
    --exclude='storage/app/public/media' \
    --exclude='database/database.sqlite' \
    --exclude='.git' \
    --exclude='e2e' \
    --exclude='playwright-report' \
    --exclude='test-results' \
    .

ls -lh /tmp/dante-redeploy.tar.gz
echo "Listo. Subir con scp y extraer sobre ~/dante-app en el servidor —"
echo "después recordar: rm -f public/storage && php artisan storage:link"
