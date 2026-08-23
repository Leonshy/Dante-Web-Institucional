#!/usr/bin/env bash
# Verifica que los insumos del proyecto Dante estén donde corresponde.
# Uso:  bash scripts/verificar-insumos.sh

set -uo pipefail
cd "$(dirname "$0")/.." || exit 1

VERDE='\033[0;32m'; ROJO='\033[0;31m'; AMAR='\033[0;33m'; GRIS='\033[0;90m'; NC='\033[0m'
ok()   { echo -e "  ${VERDE}✔${NC} $1"; }
falta(){ echo -e "  ${ROJO}✘${NC} $1"; }
adv()  { echo -e "  ${AMAR}!${NC} $1"; }
nota() { echo -e "  ${GRIS}·${NC} $1"; }

# Cuenta archivos ignorando los LEEME.md
contar() { find "$1" -type f ! -name 'LEEME.md' 2>/dev/null | wc -l | tr -d ' '; }

echo
echo "═══ Verificación de insumos — proyecto Dante ═══"
echo

LISTO=1

# ── 1. Repo IPG ──────────────────────────────────────────────────────
echo "1. Repo de referencia IPG"
IPG=_insumos/01-referencia-ipg
if [ "$(contar $IPG)" -gt 0 ]; then
  [ -f "$IPG/composer.json" ] && ok "composer.json presente" || { adv "falta composer.json — ¿es un proyecto Laravel?"; }
  [ -d "$IPG/app" ]            && ok "app/ presente"          || adv "falta app/"
  [ -d "$IPG/database/migrations" ] && ok "migrations/ presente (el modelo de datos vive acá)" || adv "faltan las migraciones"
  [ -d "$IPG/resources/views" ] && ok "views/ presente"        || adv "faltan las vistas"
  [ -f "$IPG/.env" ] && adv "⚠️  hay un .env — borralo, no traigas credenciales de IPG acá"
  [ -d "$IPG/vendor" ] && nota "hay vendor/ — podés borrarlo, ocupa espacio y no aporta"
  [ -d "$IPG/node_modules" ] && nota "hay node_modules/ — podés borrarlo"
else
  falta "vacío — cargá el repo de IPG. Sin esto no arranca la Fase 0"
  LISTO=0
fi
echo

# ── 2. Marca ─────────────────────────────────────────────────────────
echo "2. Manual de marca de Dante"
MARCA=_insumos/02-marca-dante
if [ "$(contar $MARCA)" -gt 0 ]; then
  PDFS=$(find $MARCA -iname '*.pdf' | wc -l | tr -d ' ')
  SVGS=$(find $MARCA -iname '*.svg' -o -iname '*.ai' -o -iname '*.eps' | wc -l | tr -d ' ')
  FONTS=$(find $MARCA \( -iname '*.woff2' -o -iname '*.otf' -o -iname '*.ttf' \) | wc -l | tr -d ' ')
  [ "$PDFS"  -gt 0 ] && ok "manual en PDF ($PDFS)"        || adv "no hay PDF del manual"
  [ "$SVGS"  -gt 0 ] && ok "logotipo vectorial ($SVGS)"   || adv "no hay logo vectorial (SVG/AI/EPS) — un PNG no alcanza"
  [ "$FONTS" -gt 0 ] && ok "tipografías ($FONTS)"         || adv "no hay tipografías — ⚠️ verificá la licencia web (costo no previsto)"
else
  adv "vacío — se necesita recién en la Fase 2, pero cargalo cuanto antes"
fi
echo

# ── 3. WordPress ─────────────────────────────────────────────────────
echo "3. WordPress actual"
WP=_insumos/03-wordpress-actual

DUMPS=$(find $WP/db \( -iname '*.sql' -o -iname '*.sql.gz' -o -iname '*.zip' \) 2>/dev/null | wc -l | tr -d ' ')
if [ "$DUMPS" -gt 0 ]; then
  ok "dump de base presente ($DUMPS)"
  find $WP/db \( -iname '*.sql' -o -iname '*.sql.gz' \) -exec du -h {} \; 2>/dev/null | while read -r l; do nota "$l"; done
else
  falta "no hay dump en db/ — sin esto no hay inventario ni estimación real"
  LISTO=0
fi

UP=$(contar $WP/uploads)
if [ "$UP" -gt 0 ]; then
  ok "uploads presentes ($UP archivos, $(du -sh $WP/uploads 2>/dev/null | cut -f1))"
else
  adv "uploads/ vacío — se necesita para migrar las imágenes"
fi

EXP=$(contar $WP/export)
[ "$EXP" -gt 0 ] && ok "material de apoyo en export/ ($EXP archivos)" \
                 || nota "export/ vacío — capturas y datos de Analytics ahorran horas"
echo

# ── 4. Seguridad ─────────────────────────────────────────────────────
echo "4. Verificación de seguridad (WordPress comprometido)"
PELIGRO=$(find $WP -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '*.php5' -o -iname '*.phar' \) 2>/dev/null | wc -l | tr -d ' ')
if [ "$PELIGRO" -gt 0 ]; then
  falta "⚠️⚠️  HAY $PELIGRO ARCHIVOS PHP EN _insumos/03-wordpress-actual/"
  echo -e "        ${ROJO}El WordPress origen estuvo comprometido. NO deben estar acá.${NC}"
  echo -e "        ${ROJO}Revisalos y borralos. Ver CLAUDE.md §2.${NC}"
  find $WP -type f \( -iname '*.php' -o -iname '*.phtml' -o -iname '*.php5' -o -iname '*.phar' \) 2>/dev/null | head -20 | while read -r f; do echo "          $f"; done
  LISTO=0
else
  ok "sin archivos PHP en los insumos"
fi

DOBLE=$(find $WP -type f -regex '.*\.\(jpg\|jpeg\|png\|gif\|webp\|pdf\)\.[a-zA-Z0-9]+$' 2>/dev/null | wc -l | tr -d ' ')
[ "$DOBLE" -gt 0 ] && falta "⚠️  $DOBLE archivos con doble extensión — revisalos" \
                   || ok "sin archivos de doble extensión"

for d in themes plugins wp-admin wp-includes; do
  [ -d "$WP/$d" ] && falta "⚠️  hay $WP/$d — borralo, solo van datos"
done

SVGS=$(find $WP/uploads -iname '*.svg' 2>/dev/null | wc -l | tr -d ' ')
[ "$SVGS" -gt 0 ] && adv "$SVGS archivos SVG — hay que sanitizarlos antes de servirlos"
echo

# ── 5. Git ───────────────────────────────────────────────────────────
echo "5. Control de versiones"
if [ -d .git ]; then
  if git check-ignore -q _insumos 2>/dev/null; then
    ok "_insumos/ está ignorado por git"
  else
    falta "⚠️  _insumos/ NO está ignorado — material del cliente podría subir al repo"
    LISTO=0
  fi
else
  nota "todavía no hay repo git — está bien, el .gitignore ya está listo"
fi
echo

# ── Resultado ────────────────────────────────────────────────────────
echo "─────────────────────────────────────────────"
if [ "$LISTO" -eq 1 ]; then
  echo -e "${VERDE}Insumos listos. Podés arrancar la Fase 0 con  /fase 0${NC}"
else
  echo -e "${ROJO}Faltan insumos o hay algo que corregir. Revisá los ✘ de arriba.${NC}"
fi
echo
