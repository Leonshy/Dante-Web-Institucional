# 12 — Despliegue y puesta en producción (Fase 10)

Estado: **en curso (arrancada 2026-08-26).** Todo lo que no requiere acceso real al Plesk/DNS
está listo: script de despliegue (`scripts/deploy-plesk.sh`), plantilla de variables de
entorno (`app/.env.production.example`), plan de cutover y checklists de verificación
(abajo), manual del cliente con capturas reales (`docs/manual-cliente/`). Bloqueado en lo que
sí requiere el servidor real: staging, cron, SSL, cutover — pendientes de la pregunta #13
(acceso Plesk, abierta desde la Fase 0).

---

## 1. Entornos

| Entorno | URL | Base | Notas |
|---|---|---|---|
| Local | `dante.test` | local | |
| Staging | `staging.dante.edu.py` | `dante_staging` | `noindex`, protegido por contraseña |
| Producción | `dante.edu.py` | `dante_prod` | |

---

## 2. Configuración del Plesk

- [ ] Dominio/subdominio creado, raíz del documento apuntando a **`app/public`**
- [ ] Versión de PHP correcta seleccionada
- [ ] Extensiones PHP requeridas activas (`mbstring`, `intl`, `gd` o `imagick`, `zip`, `bcmath`, `exif`, `fileinfo`)
- [ ] `memory_limit` y `upload_max_filesize` suficientes
- [ ] OPcache activo
- [ ] Composer disponible
- [ ] Node disponible para el build — **o** los assets se compilan localmente y se suben ya
      construidos (más simple y más seguro; decidir y documentar acá)
- [ ] Base de datos y usuario creados, con permisos mínimos
- [ ] SSL Let's Encrypt emitido + renovación automática verificada
- [ ] Redirección forzada a HTTPS
- [ ] www / no-www unificado
- [ ] Brotli o Gzip activo
- [ ] Cabeceras de caché en estáticos

**Decisión sobre el build de assets:** ☐ en el servidor ☐ localmente y se sube

---

## 3. Variables de entorno de producción

**Actualizado 2026-08-26** — la plantilla real y siempre vigente es
`app/.env.production.example` (nuevo en esta sesión, generado a partir de
`app/.env.example` real del repo, no inventado). **Nunca copiar el `.env.example`
de desarrollo a producción tal cual** — tiene `APP_DEBUG=true`, `MAIL_MAILER=log`,
SQLite, etc.

Cambios importantes respecto de lo que decía esta sección antes de Fase 6/8:

- **GA4/GTM, Meta Pixel + Conversions API y Cloudflare Turnstile ya NO son variables de
  entorno** — se cargan desde el panel → Integraciones (`App\Models\IntegrationSetting`,
  con los secretos cifrados en la base, ver ADR de Fase 6/`docs/08-seo.md` §6). No hay
  `GTM_ID`/`META_CAPI_TOKEN`/`TURNSTILE_SECRET_KEY` que configurar acá.
- El 2FA tampoco es una variable de entorno (`DANTE_REQUIRE_2FA` se eliminó) — es opt-in
  por usuario desde su perfil (ADR-003).
- `DANTE_ADMIN_PATH` sigue siendo la variable real (no `ADMIN_PATH`) para la ruta no
  adivinable del panel.
- Los respaldos si usan un disco S3/SFTP real, van en `BACKUP_DESTINATION_DISKS` (no
  `BACKUP_DISK`) — ver `config/backup.php`.

Ver `app/.env.production.example` para la plantilla completa con cada variable comentada.
Los valores reales (contraseñas, `APP_KEY`, credenciales SMTP) **nunca van al repo** — se
cargan a mano en el `.env` del servidor o vía el gestor de variables del Plesk.

---

## 4. Procedimiento de despliegue

**Desde el 2026-08-26 el despliegue es por Git, no por `tar`/`scp` manual.** El repo
(`github.com/Leonshy/Dante-Web-Institucional`, público) está clonado en el servidor en
`~/dante-web-institucional`, y `~/dante-app` (la ruta que ya conocía Plesk) es un **symlink**
a `~/dante-web-institucional/app` — no una copia separada. Esto reemplazó el método anterior
de armar un `.tar.gz` a mano y extraerlo (`scripts/package-for-staging.sh`, ya eliminado).

**El servidor solo baja el sitio, no la documentación del proyecto** (pedido del cliente): el
repo tiene `PLAN.md`, `CLAUDE.md`, `docs/`, etc. en la raíz, que son información de referencia
del proyecto, no parte de lo que hay que desplegar. El clon del servidor usa
`git sparse-checkout` para traer solo `app/` (el sitio) y los dos scripts de deploy —
`docs/`, `_insumos/` (además ya ignorado por git), `PLAN.md`, `CLAUDE.md`, etc. nunca se
materializan como archivos ahí, aunque técnicamente sigan existiendo dentro de `.git/` como
parte del historial versionado (no hay forma de clonar "solo una carpeta" sin traer el
historial completo del repo salvo con un repo separado). Configuración (una sola vez, ya
hecha en staging):

```bash
cd ~/dante-web-institucional
git config core.sparseCheckout true
printf 'app/*\nscripts/deploy-plesk.sh\nscripts/push-assets.sh\n' > .git/info/sparse-checkout
git read-tree -m -u HEAD
```

Si el servidor tiene una versión de Git más nueva (2.25+), el equivalente moderno es
`git sparse-checkout set app scripts/deploy-plesk.sh scripts/push-assets.sh` — acá se usó la
sintaxis vieja porque el Plesk de webparaguay trae Git 1.8.3.1.

**Restricción real que no cambia:** el Node del Plesk es demasiado viejo para compilar Vite, y
además el cliente pidió explícitamente que el build compilado (`public/build/`) **nunca** se
suba a GitHub — son binarios, no código fuente. Por eso el despliegue son **dos scripts
separados**, uno por Git y otro por scp, y se pueden correr en cualquier orden:

```bash
# 1. En el servidor (por SSH): trae el código y todo lo demás por `git pull`.
#    Nunca compila nada — no toca `public/build`.
cd ~/dante-web-institucional && ./scripts/deploy-plesk.sh

# 2. Desde la máquina LOCAL: compila los assets acá y los sube por scp,
#    directo a la carpeta public/ del servidor. Nunca pasa por GitHub.
./scripts/push-assets.sh dante.webparaguay.co_cln9tief7cu@177.251.252.12 \
    53931 ~/dante-web-institucional/app/public
```

`deploy-plesk.sh` corre mantenimiento → `git pull origin main` → composer (sin dev) →
migraciones → cachés → `storage:link` → permisos → verificación, con `set -euo pipefail` y un
`trap` que **levanta el sitio de mantenimiento automáticamente si cualquier paso falla** —
nunca se queda colgado en mantenimiento por un error a mitad de camino.

- [x] Procedimiento por Git probado en staging real (`dante.webparaguay.com`) — 2026-08-26:
      clonado el repo, migrado `~/dante-app` a symlink, `git pull` confirmado funcionando sin
      credenciales (repo público), assets subidos por `push-assets.sh` y verificados en el
      navegador (home, panel, Escritorio, Sedes, selector de idioma)
- [ ] Plan de reversión: `git checkout <commit-anterior>` en el servidor + repetir el script
      (las migraciones de este proyecto son reversibles, `php artisan migrate:rollback --force`
      si hace falta deshacer el esquema) + restaurar el backup de la base tomado antes del
      deploy si el rollback de esquema no alcanza

**Hallazgo real del primer despliegue manual (previo a este cambio, ya resuelto):** un paquete
armado a mano con `tar` incluía `public/storage` — el symlink que crea `php artisan
storage:link` en local, apuntando a una ruta de disco de esa máquina — y al extraerse en el
servidor pisó el symlink correcto, dejando los medios migrados con `403`. Con Git esto no
puede repetirse (`/public/storage` está en `.gitignore`, `git pull` nunca lo toca), pero
`deploy-plesk.sh` igual borra y recrea el symlink en cada corrida en vez de confiar en que
`storage:link` detecte uno roto.

---

## 5. Tareas programadas

Programador de tareas de Plesk:

| Tarea | Frecuencia | Comando |
|---|---|---|
| Scheduler de Laravel | cada minuto | `php /ruta/artisan schedule:run` |
| Cola (si no hay Supervisor) | cada minuto | `php /ruta/artisan queue:work --stop-when-empty --max-time=55` |
| Respaldo | diario | vía scheduler |
| Limpieza de respaldos viejos | diario | vía scheduler |

- [ ] Verificado que los cron efectivamente corren (revisar el log)

---

## 6. Plan de cutover

> ⚠️ **El correo del dominio está en HostGator (`192.185.52.135`). En el cutover se cambia
> ÚNICAMENTE el registro A del web. Los MX, SPF, DKIM y cualquier registro de correo NO SE TOCAN.**

### Antes (semana previa)

- [ ] TTL del registro A bajado a 300 s, al menos 48 h antes
- [ ] Congelamiento de contenido acordado con el cliente — fecha:
- [ ] Copia completa del WordPress viejo (archivos + base) guardada offline
- [ ] Registros DNS actuales documentados **en su totalidad**, con captura de pantalla
- [ ] Migración del delta de contenido ejecutada y verificada
- [ ] Producción desplegada y probada por IP o dominio temporal
- [ ] Respaldo del sitio nuevo tomado

### Registros DNS antes del cambio

| Tipo | Nombre | Valor | TTL |
|---|---|---|---|
| A | @ | 177.251.252.12 (nuevo) / anterior: | |
| A | www | | |
| MX | @ | **HostGator — NO TOCAR** | |
| TXT (SPF) | @ | **NO TOCAR** | |
| TXT (DKIM) | | **NO TOCAR** | |
| CNAME | | | |

### Durante

Ventana elegida: (día y hora de menor tráfico — para un colegio, un sábado a la mañana)

- [ ] Cambiar el registro A del web
- [ ] Esperar propagación
- [ ] Verificar con `dig dante.edu.py A` y `dig dante.edu.py MX`
- [ ] Emitir/renovar el SSL en el Plesk
- [ ] Forzar HTTPS

### Verificación inmediata post-cutover

- [ ] El sitio nuevo carga en `https://dante.edu.py`
- [ ] SSL válido, sin advertencias de contenido mixto
- [ ] **Enviar y recibir un correo de prueba a una cuenta del dominio** ← lo más importante
- [ ] Las 20 URLs con más tráfico redirigen bien
- [ ] Los formularios envían y llegan
- [ ] GA4 y Meta reciben eventos
- [ ] Sitemap accesible, `robots.txt` correcto (sin el `Disallow: /` del staging)
- [ ] El panel entra con 2FA
- [ ] El respaldo corre esa noche
- [ ] Search Console: enviar el sitemap nuevo
- [ ] Subir el TTL del registro A a un valor normal

### Después

- [ ] Bajar el WordPress viejo (no solo despublicarlo: apagarlo)
- [ ] Conservar la copia offline por al menos 6 meses
- [ ] Monitorear 404 en Search Console diariamente la primera semana
- [ ] Revisar logs de error diariamente la primera semana

---

## 7. Entrega al cliente

- [x] **Manual del panel en español, con capturas** — `docs/manual-cliente/manual-panel-dante.md`
      (nuevo en esta sesión), 9 capturas reales tomadas del panel local (no maquetas): login,
      escritorio, listado y creación de páginas, editor de bloques, noticias, medios, menús,
      formularios recibidos. Cubre: crear/editar página, publicar noticia, subir imágenes,
      editar menú, ver envíos de formularios, activar 2FA. **Revisar y volver a capturar las
      pantallas** una vez que el contenido de producción esté cargado (hoy tiene datos de
      prueba de QA visibles en el listado de páginas)
- [ ] Sesión de capacitación **grabada** (el que se capacita no siempre es el que después usa
      el panel) — requiere agendar con el cliente, no se puede grabar sin su presencia
- [ ] Credenciales entregadas por canal seguro (gestor de contraseñas, no WhatsApp ni mail plano)
- [ ] Accesos a Analytics, Search Console y Business Profile transferidos
- [ ] Documento de "qué hacer si algo pasa": a quién llamar, en qué horario, qué está cubierto
- [ ] Acuerdo de mantenimiento firmado

---

## 8. Acuerdo de mantenimiento

| Item | Incluido | Frecuencia |
|---|---|---|
| Hosting en servidor Plesk local | | mensual |
| Actualizaciones de dependencias y seguridad | | mensual |
| Monitoreo de disponibilidad | | continuo |
| Respaldos verificados | | diario / prueba de restauración trimestral |
| Renovación de SSL | | automática |
| Bolsa de horas de soporte y cambios menores | | h/mes |
| Reporte mensual (tráfico, disponibilidad, seguridad) | | mensual |

**Fuera del acuerdo:** desarrollos nuevos, nuevas secciones, integraciones adicionales — se
cotizan aparte.

---

## 9. Cierre del proyecto

- [ ] Todos los KPIs de `docs/00-legajo-tecnico.md` §9 medidos y registrados
- [ ] Horas reales vs. estimadas registradas en `PLAN.md`
- [ ] Retrospectiva interna: qué salió bien, qué costó más de lo previsto
- [ ] **Patrón reutilizable extraído** para el próximo colegio — qué se puede empaquetar,
      dónde queda, cuánto acorta el próximo proyecto
- [ ] Actualizar el legajo del pipeline de webparaguay con lo aprendido

---

## 10. Primer despliegue a producción (`dante.edu.py`) — runbook

Mismo patrón que Cateura: clave SSH `dante_plesk` restringida por `command=` a un script fijo
(`scripts/deploy-dante-forced.sh`, instalado como `/var/www/vhosts/dante.edu.py/deploy-dante.sh`,
fuera de `httpdocs/`). Los assets de Vite siguen por `scripts/push-assets.sh` (scp).
Suscripción `danteedu`, mismo puerto y misma IP que staging, PHP 8.3
(`/opt/plesk/php/8.3/bin/php`), base `danteedu_db`, document root `httpdocs/public`.

Una sola vez, en el servidor como `danteedu`:

1. Vaciar `httpdocs/` y clonar: `git clone https://github.com/Leonshy/Dante-Web-Institucional.git .`
   más el sparse-checkout de §4 (`app/*`, los scripts de deploy).
2. `app/.env` desde `app/.env.production.example` (valores reales a mano, nunca al repo).
   `DB_DATABASE=danteedu_db`, `APP_URL=https://dante.edu.py`.
3. `composer install --no-dev --optimize-autoloader`, `key:generate --force`, `migrate --force`.
4. Datos: **no** correr `db:seed` completo (duplicaría páginas y menús que vienen en el dump).
   Solo `db:seed --class=PermissionSeeder --force` y el usuario protegido con
   `DANTE_ADMIN_EMAIL=webmaster@webparaguay.com DANTE_ADMIN_PASSWORD=... php artisan db:seed --class=AdminUserSeeder --force`.
5. Contenido: `scripts/dump-contenido.sh BASE_STAGING USUARIO > contenido.sql` (en staging) e
   importarlo en `danteedu_db`. Reemplaza el dominio de staging por `dante.edu.py`.
6. Medios: copiar `storage/app/public/` de staging a producción (verificar que
   `storage/app/public/media` quede directo, sin anidar una carpeta extra — gotcha de Cateura).
7. `rm -f public/storage && php artisan storage:link`; luego `scripts/push-assets.sh`.
8. Cron de Plesk: `schedule:run` cada minuto. Correo: SMTP en el `.env`.

Después, cada actualización es `ssh -i ~/.ssh/dante_plesk -p <puerto> danteedu@177.251.252.12`
(dispara el deploy) + `push-assets.sh` si cambió CSS/JS.
