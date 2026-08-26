#!/usr/bin/env bash
#
# Despliegue repetible a Plesk — Fase 10 (docs/12-deploy-plesk.md §4).
#
# Se corre DESDE la raíz de `app/` en el servidor (vía SSH o el terminal web
# del Plesk), nunca desde la máquina local. Pensado para el caso más simple y
# más seguro: los assets de Vite se compilan ACÁ (requiere Node en el Plesk).
# Si el Plesk no tiene Node disponible, compilar `npm run build` en local y
# subir `public/build/` ya construido — en ese caso, saltar el paso 4 con
# `SKIP_ASSET_BUILD=1 ./scripts/deploy-plesk.sh`.
#
# Requisitos ya confirmados antes de correr esto la primera vez:
#   - .env real cargado en el servidor (a partir de .env.production.example)
#   - Base de datos y usuario MySQL creados
#   - `php artisan key:generate --force` corrido al menos una vez
#
# Uso: ./scripts/deploy-plesk.sh
set -euo pipefail

php_bin="${PHP_BIN:-php}"

if [ ! -f artisan ]; then
    echo "Error: correr este script desde la raíz de app/ (no se encontró artisan)." >&2
    exit 1
fi

app_env=$("$php_bin" artisan tinker --execute="echo config('app.env');" 2>/dev/null || true)
if [ "$app_env" != "production" ]; then
    echo "Aviso: APP_ENV no resolvió a 'production' (valor leído: '${app_env:-desconocido}')." >&2
    echo "Si esto es staging, es esperable — Ctrl+C para cancelar, Enter para seguir." >&2
    read -r _
fi

echo "==> 1/9 Poniendo el sitio en mantenimiento"
"$php_bin" artisan down --render="errors::503" --retry=60

# Si algo falla de acá en adelante, el `trap` levanta el sitio antes de salir
# en vez de dejarlo colgado en mantenimiento.
trap '"$php_bin" artisan up' EXIT

echo "==> 2/9 Trayendo el código"
git pull origin main

echo "==> 3/9 Dependencias de Composer (sin dev, autoloader optimizado)"
composer install --no-dev --optimize-autoloader --no-interaction

if [ "${SKIP_ASSET_BUILD:-0}" != "1" ]; then
    echo "==> 4/9 Compilando assets (Vite)"
    npm ci
    npm run build
else
    echo "==> 4/9 Saltado (SKIP_ASSET_BUILD=1) — assets ya subidos construidos"
fi

echo "==> 5/9 Migraciones"
"$php_bin" artisan migrate --force

echo "==> 6/9 Cachés de configuración, rutas, vistas y eventos"
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
"$php_bin" artisan view:cache
"$php_bin" artisan event:cache

echo "==> 7/9 Enlace de almacenamiento público"
# Hallazgo real (Fase 10, redeploy 2026-08-26): un despliegue manual por
# tar/scp (mientras no había `git pull` configurado en el servidor) subió el
# symlink `public/storage` TAL CUAL estaba en la máquina local — apuntando a
# una ruta de disco que no existe en el servidor — y lo pisó, dejando todos
# los medios en 403. `git pull` nunca tiene este problema (`/public/storage`
# está en `.gitignore`), pero acá se recrea siempre por las dudas, sin
# depender de que `artisan storage:link` detecte un enlace ya existente pero roto.
rm -f public/storage
"$php_bin" artisan storage:link

echo "==> 8/9 Permisos de storage/ y bootstrap/cache"
chmod -R ug+rwX storage bootstrap/cache

echo "==> 9/9 Verificación mínima post-deploy"
"$php_bin" artisan about --only=environment

echo "==> Levantando el sitio"
trap - EXIT
"$php_bin" artisan up

echo "Despliegue terminado. Verificar manualmente docs/12-deploy-plesk.md §6 (checklist post-cutover)."
