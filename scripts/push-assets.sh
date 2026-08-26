#!/usr/bin/env bash
#
# Compila los assets de Vite ACÁ (en local) y los sube por scp al servidor —
# nunca se compilan en el Plesk (Node demasiado viejo para el build) y el
# cliente pidió explícitamente no subir `public/build` a GitHub (binarios
# compilados no van a control de versiones, Fase 10).
#
# Complementa a `scripts/deploy-plesk.sh` (que sí corre por `git pull` en el
# servidor pero nunca toca assets): correr este script antes o después de
# ese, el orden no importa — `public/build` está en `.gitignore`, así que
# `git pull` nunca lo pisa ni lo borra.
#
# Uso: ./scripts/push-assets.sh usuario@host puerto ruta-remota-a-la-carpeta-public
# Ejemplo (la ruta es la carpeta `public/` del servidor, no `public/build`):
#   ./scripts/push-assets.sh dante.webparaguay.co_cln9tief7cu@177.251.252.12 \
#       53931 ~/dante-web-institucional/app/public
set -euo pipefail

remote_user_host="${1:?Uso: ./scripts/push-assets.sh usuario@host puerto ruta-remota-a-public}"
remote_port="${2:?Falta el puerto SSH}"
remote_public_dir="${3:?Falta la ruta remota a la carpeta public/ en el servidor}"

cd "$(dirname "$0")/../app"

echo "==> Compilando assets (Vite)"
npm run build

echo "==> Subiendo public/build por scp"
scp -r -P "$remote_port" public/build "${remote_user_host}:${remote_public_dir}/"

echo "Listo — los assets ya están en el servidor en: ${remote_public_dir}/build"
