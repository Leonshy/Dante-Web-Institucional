# 12 — Despliegue y puesta en producción (Fase 10)

Estado: **vacío — completar en Fase 10**

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

Plantilla (los valores reales **nunca** en el repo):

```
APP_NAME="..."
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dante.edu.py
APP_KEY=            # generar en el servidor, distinta de la de desarrollo

DB_CONNECTION=mysql
DB_DATABASE=dante_prod
DB_USERNAME=
DB_PASSWORD=

CACHE_STORE=file            # database si conviene; redis si aparece
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

ADMIN_PATH=                 # ruta no adivinable del panel

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_FROM_ADDRESS=

GTM_ID=
GA4_ID=
META_PIXEL_ID=
META_CAPI_TOKEN=
TURNSTILE_SITE_KEY=
TURNSTILE_SECRET_KEY=

BACKUP_DISK=
```

---

## 4. Procedimiento de despliegue

Documentar el procedimiento exacto y repetible. Sugerido:

```bash
# 1. Poner en mantenimiento
php artisan down --secret="..." 

# 2. Traer el código
git pull origin main

# 3. Dependencias
composer install --no-dev --optimize-autoloader

# 4. Assets (si se compilan en el servidor)
npm ci && npm run build

# 5. Migraciones
php artisan migrate --force

# 6. Cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Enlace de almacenamiento
php artisan storage:link

# 8. Levantar
php artisan up
```

- [ ] Procedimiento probado en staging al menos dos veces
- [ ] Plan de reversión escrito (volver al commit anterior + restaurar base)

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

- [ ] **Manual del panel en español, con capturas** — cómo crear una página, publicar una
      noticia, subir imágenes, cambiar el menú, ver los envíos de formularios
- [ ] Sesión de capacitación **grabada** (el que se capacita no siempre es el que después usa el panel)
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
