#!/usr/bin/env bash
#
# Despliegue repetible a Plesk — Fase 10 (docs/12-deploy-plesk.md §4).
#
# Se corre EN EL SERVIDOR (vía SSH), desde la raíz del repo clonado — la
# carpeta que CONTIENE `app/`, no `app/` en sí (el Laravel vive en el
# subdirectorio `app/` del repo, ver CLAUDE.md §4).
#
# Este script NUNCA compila assets de Vite: el Node del Plesk es demasiado
# viejo para el build, y el cliente pidió explícitamente no subir
# `public/build` a GitHub (los binarios compilados no van a control de
# versiones). Los assets siempre se transfieren aparte por scp desde la
# máquina local con `scripts/push-assets.sh` — antes o después de correr
# este script, el orden no importa (`public/build` está en `.gitignore`,
# así que `git pull` nunca lo toca).
#
# Requisitos ya confirmados antes de correr esto la primera vez:
#   - Repo clonado en el servidor (`git clone` del remoto público)
#   - `~/dante-app` es un symlink a `<repo>/app` (no una copia separada)
#   - .env real cargado en `app/.env` (a partir de .env.production.example)
#   - Base de datos y usuario MySQL creados
#   - `php artisan key:generate --force` corrido al menos una vez
#
# Uso (en el servidor):
#   cd ~/dante-web-institucional && ./scripts/deploy-plesk.sh
set -euo pipefail

php_bin="${PHP_BIN:-php}"
app_dir="app"

if [ ! -d "$app_dir" ] || [ ! -f "$app_dir/artisan" ]; then
    echo "Error: correr este script desde la raíz del repo (la carpeta que contiene app/)." >&2
    exit 1
fi

echo "==> 1/8 Trayendo el código"
git pull origin main

cd "$app_dir"

app_env=$("$php_bin" artisan tinker --execute="echo config('app.env');" 2>/dev/null || true)
if [ "$app_env" != "production" ]; then
    echo "Aviso: APP_ENV no resolvió a 'production' (valor leído: '${app_env:-desconocido}')." >&2
    echo "Si esto es staging, es esperable — Ctrl+C para cancelar, Enter para seguir." >&2
    read -r _
fi

echo "==> 2/8 Poniendo el sitio en mantenimiento"
"$php_bin" artisan down --render="errors::503" --retry=60

# Si algo falla de acá en adelante, el `trap` levanta el sitio antes de salir
# en vez de dejarlo colgado en mantenimiento.
trap '"$php_bin" artisan up' EXIT

echo "==> 3/8 Dependencias de Composer (sin dev, autoloader optimizado)"
# Hallazgo real (Fase 10): un bootstrap/cache viejo puede quedar apuntando a
# paquetes de dev que ya no están instalados ("Laravel\Pail\PailServiceProvider
# not found") — se limpia antes de reinstalar para no arrastrar ese estado.
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> 4/8 Migraciones"
"$php_bin" artisan migrate --force

echo "==> 5/8 Cachés de configuración, rutas, vistas y eventos"
"$php_bin" artisan config:cache
"$php_bin" artisan route:cache
"$php_bin" artisan view:cache
"$php_bin" artisan event:cache

echo "==> 6/8 Enlace de almacenamiento público"
# Se borra y recrea siempre, sin depender de que `storage:link` detecte un
# enlace ya existente pero roto (hallazgo real del primer despliegue manual
# por tar, Fase 10 — acá ya no debería repetirse porque `git pull` nunca
# trae `public/storage`, pero el borrado previo no hace daño).
rm -f public/storage
"$php_bin" artisan storage:link

echo "==> 7/8 Permisos de storage/ y bootstrap/cache"
chmod -R ug+rwX storage bootstrap/cache

echo "==> 8/8 Verificación mínima post-deploy"
"$php_bin" artisan about --only=environment

echo "==> Levantando el sitio"
trap - EXIT
"$php_bin" artisan up

echo "Despliegue terminado."
echo "Si hay assets de CSS/JS nuevos, correr scripts/push-assets.sh desde la máquina local."
