#!/bin/bash
#
# Script FIJO que ejecuta la clave SSH dedicada `dante_plesk` (patrón de Cateura).
# Se instala en el servidor FUERA de httpdocs (ej. /var/www/vhosts/dante.edu.py/deploy-dante.sh)
# y se ata a la clave con `command=` en ~/.ssh/authorized_keys:
#
#   command="/var/www/vhosts/dante.edu.py/deploy-dante.sh",no-port-forwarding,no-X11-forwarding,no-agent-forwarding,no-pty ssh-ed25519 AAAA... dante-deploy-claude
#
# Sin importar qué comando mande el cliente, el servidor solo corre esto.
# Los assets de Vite NO pasan por acá (siguen por scripts/push-assets.sh).
set -euo pipefail

REPO=/var/www/vhosts/dante.edu.py/httpdocs   # raíz del clon: contiene app/
cd "$REPO"
PHP_BIN=/opt/plesk/php/8.3/bin/php exec ./scripts/deploy-plesk.sh
