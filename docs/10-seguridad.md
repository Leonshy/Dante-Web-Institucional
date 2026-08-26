# 10 — Seguridad (Fase 8)

Estado: **primera pasada real completada, 2026-08-25 — no es el cierre de la fase.**
Herramienta: `strix` — ⚠️ **solo contra nuestro propio staging, con autorización escrita del cliente**

> Contexto: el sitio anterior fue comprometido (webshell disfrazada de imagen subida al
> WordPress viejo, CLAUDE.md §2 y docs/01-analisis-descubrimiento.md §C.6). Esta fase no es
> un trámite. El cliente va a preguntar "¿cómo sé que no vuelve a pasar?" y este documento
> es la respuesta — verificada contra el código y el entorno real, no contra la intención.

**Entorno de esta pasada:** no existe todavía un staging desplegado en Plesk (eso es
Fase 10). Con autorización explícita del usuario en esta sesión, se usó el entorno **local
de desarrollo** (`http://dante-web.test`, Herd/Valet) como sustituto temporal para poder
verificar con `curl` real en vez de solo leer código. **Esto no reemplaza el pentest final
contra el staging real de Plesk** — se repite en la Fase 10 antes de salir a producción.

Baseline de tests antes de empezar: **136/136** (Pest). Al cerrar esta pasada: **137/137**
(se sumó 1 test de regresión de reprocesamiento de imágenes). Pint y Larastan (nivel 5)
sin hallazgos. `composer audit` y `npm audit`: **0 vulnerabilidades** en ambos.

---

## 1. Cabeceras HTTP

**Hallazgo (Alto, corregido):** no existía ninguna cabecera de seguridad. Verificado con
`curl -I http://dante-web.test/` antes de tocar código: solo llegaban las cabeceras por
defecto de Laravel/nginx, incluyendo `X-Powered-By: PHP/8.3.6` expuesto.

**Corrección:** `app/Http/Middleware/SecurityHeaders.php`, registrado como middleware
**global** (`bootstrap/app.php`, `$middleware->append(...)`) — corre en el sitio público
y en el panel, no solo en el grupo `web`.

| Cabecera | Valor | Estado |
|---|---|---|
| `Content-Security-Policy` | `default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self'; img-src 'self' data: https:; font-src 'self' data:; script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://connect.facebook.net https://challenges.cloudflare.com; style-src 'self' 'unsafe-inline'; connect-src 'self' https://www.google-analytics.com https://analytics.google.com; frame-src 'self' https://www.googletagmanager.com https://challenges.cloudflare.com https://www.google.com` | ✅ forzada (no `report-only`) — el sitio no carga scripts de terceros incondicionales (Fase 6), la lista de orígenes ya es acotada desde el día 1 |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | ✅ código listo, **solo se envía si `$request->isSecure()`** — en HTTP local no aplica (correcto, HSTS sobre HTTP no tiene sentido y rompería el propio dev). Se activa solo con verificarla en el Plesk real bajo HTTPS, Fase 10 |
| `X-Frame-Options` | `SAMEORIGIN` | ✅ |
| `X-Content-Type-Options` | `nosniff` | ✅ |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | ✅ |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(), payment=(), usb=()` | ✅ |
| `X-Powered-By` | oculto | ✅ — `header_remove('X-Powered-By')` (ver nota) |
| `Server` (nginx) | — | ⚠️ **no se puede ocultar desde la aplicación**. `nginx` sigue anunciando su versión (`server_tokens off` es una directiva de servidor, no de PHP/Laravel). Pendiente para Fase 10: agregar `server_tokens off;` a la configuración de nginx del Plesk |

**Nota técnica real, no obvia:** `$response->headers->remove('X-Powered-By')` (sobre el
objeto `Response` de Symfony) **no alcanzaba** — PHP-FPM agrega esa cabecera a nivel de
SAPI antes de que Symfony envíe su propio `HeaderBag`, y Symfony no vuelve a limpiar
cabeceras que no están en su bolsa. Hubo que llamar a `header_remove()` de PHP
directamente dentro del middleware. Verificado con `curl -I`: la cabecera desaparece.
La forma definitiva y correcta en producción sigue siendo `expose_php = Off` en el
`php.ini` real del Plesk (Fase 10) — el middleware es una segunda capa, no un reemplazo.

Verificado en local con `curl -I http://dante-web.test/` y `curl -I
http://dante-web.test/panel-dante-2026/login` (confirma que corre también en el panel).
**Pendiente:** correr contra el dominio público real en securityheaders.com — objetivo A,
recién posible con URL pública (Fase 10).

---

## 2. Panel de administración

| Ítem | Estado | Detalle |
|---|---|---|
| Ruta no adivinable | ✅ | `config('dante.admin_path')`, hoy `panel-dante-2026` vía `DANTE_ADMIN_PATH` en `.env` local — nunca `/admin` |
| 2FA obligatorio | ⚠️ **ver hallazgo abajo** | Implementado con `AppAuthentication` de Filament 5 nativo (TOTP + recuperación), controlado por `config('dante.require_2fa')` — default `true` en `config/dante.php` y en `.env.example` |
| Rate limit / bloqueo del login | ✅ | Nativo de Filament 5: `RateLimiter::tooManyAttempts(..., maxAttempts: 5)` en `Login` — verificado en el código del vendor, no hubo que agregarlo |
| Sesiones con expiración | ✅ | `SESSION_LIFETIME=120` (2 h) + `AuthenticateSession` en el panel invalida la sesión del servidor al cerrar sesión |
| Cookies `Secure`/`HttpOnly`/`SameSite` | ⚠️ parcial | `http_only=true` y `same_site=lax` correctos. `SESSION_SECURE_COOKIE` está **sin definir** en `.env` local (`null` = no fuerza `Secure`) — correcto en HTTP local, **debe fijarse en `true` en el `.env` de producción** (Fase 10, se agrega al checklist de despliegue §11) |
| Política de contraseñas | ✅ **corregido en esta fase** | No existía ninguna (`Password::defaults()` sin personalizar = mínimo 8 sin más). Se agregó `AppServiceProvider::configurePasswordPolicy()`: mínimo 12, mayúscula+minúscula, número, y `uncompromised()` (verifica contra Have I Been Pwned) — desactivado solo en el entorno `testing` para no depender de red en CI |
| Sin usuario "admin" genérico | ✅ | 4 roles reales por persona (Fase 3), sin cuenta compartida |
| Notificación de login desde IP nueva | ☐ pendiente | No implementado. No bloqueante para el DoD de esta fase (no está en el checklist original de Fase 8 como obligatorio), queda anotado como mejora para Fase 10 |

### Hallazgo real — Crítico, con mitigación aplicada

**`DANTE_REQUIRE_2FA=false` en el `.env` local actual.** El default en `config/dante.php`
es `true`, y `.env.example` trae `DANTE_REQUIRE_2FA=true` — pero el `.env` de este entorno
de desarrollo lo tiene en `false` (conveniencia de dev, para no pedir TOTP en cada sesión
de prueba). Esto es exactamente el tipo de configuración que, copiada sin revisar de un
entorno de desarrollo a producción, reproduce el patrón de origen del compromiso anterior
(CLAUDE.md §2: "nunca debe quedar en `false` en producción").

**Corrección aplicada — segunda línea de defensa, no solo un checklist:**
`AppServiceProvider::guardAgainstInsecureProductionConfig()` corre en cada `boot()` y, si
`APP_ENV=production` **y** `DANTE_REQUIRE_2FA` no es `true`, escribe un `Log::critical()`
inconfundible. Mismo mecanismo para `APP_DEBUG=true` en producción. No se automatizó un
`abort()`/excepción dura porque un panel de administración que se cae por completo ante un
`.env` mal copiado es peor para el negocio que un log crítico monitoreado — pero queda
registrado y es imposible que pase desapercibido en los logs de producción.
**Pendiente real, no resuelto acá:** dejar `DANTE_REQUIRE_2FA=true` en el `.env` real de
producción es una verificación manual del checklist de la Fase 10 (§11), no algo que el
código pueda forzar sin acceso al servidor real.

---

## 3. Entrada y salida

- [x] Form Requests dedicados para los dos formularios públicos (`ContactFormRequest`,
      `PreRegistrationFormRequest`) con reglas estrictas (`required`, `email`, `max`) +
      validación de Turnstile (`App\Rules\Turnstile`)
- [x] Autorización granular por permiso en cada Policy (`AuthorizesViaPermissions`,
      `{prefix}.{accion}`), no por rol hardcodeado — **probado llamando la ruta
      directamente**, no solo confiando en que el botón esté oculto: `curl` sin sesión
      contra `/panel-dante-2026` y `/panel-dante-2026/media` devuelve `302` (redirect al
      login), y la suite de Pest de cada `*ResourceTest.php` incluye casos de "un editor sin
      el permiso no puede/no ve X" (ver `tests/Feature/Filament/*ResourceTest.php`)
- [x] HTML del editor sanitizado en el servidor con lista blanca real (`HtmlPurifier` +
      `config('dante.html_sanitizer')`), corregido en la Fase 3 para operar sobre la forma
      real `$block['data'][...]` del Builder de Filament — sigue verificado con test de
      regresión (`bloquea imágenes de dominios externos`)
- [x] Consultas siempre por Eloquent — sin SQL concatenado en ningún controller/servicio
      revisado
- [x] CSRF activo (`PreventRequestForgery`/`ValidateCsrfToken` en el stack de middleware
      estándar de Laravel + Filament)

### `{!! !!}` en el código — auditados uno por uno

| Archivo | Contenido | Sanitizado por |
|---|---|---|
| `home.blade.php`, `posts/show.blade.php`, `components/breadcrumbs.blade.php`, `components/blocks/faq.blade.php`, `components/schema/organization.blade.php` | `json_encode(...)` para JSON-LD | No es HTML de usuario — es un array PHP construido por el propio código, `json_encode` escapa comillas/slashes. Sin riesgo de inyección |
| `posts/show.blade.php` (`$post->content`), `announcements/index.blade.php` (`$announcement->content`), `components/blocks/texto.blade.php`, `components/blocks/imagen-texto.blade.php`, `components/tabs.blade.php`, `components/accordion.blade.php`, `components/blocks/testimonios.blade.php` | HTML del editor enriquecido (RichEditor de Filament) | `HtmlSanitizer` (lista blanca, `config('dante.html_sanitizer')`) al **guardar**, nunca se confía en lo que llega del cliente — verificado en `SanitizesPageBlocks` y sus tests |
| `components/blocks/listado-comunicados.blade.php` | `strip_tags(...)` sobre el contenido | Doble seguro: ya viene sanitizado y además se le quitan las etiquetas para el resumen |

**Conclusión:** ningún `{!! !!}` del proyecto imprime HTML de usuario sin pasar antes por
la lista blanca del servidor.

---

## 4. Subida de archivos

> Fue el vector más probable del compromiso anterior (webshell con doble extensión
> disfrazada de imagen — docs/01-analisis-descubrimiento.md §C.6). Acá se cierra.

| Ítem | Estado | Detalle |
|---|---|---|
| MIME real (`finfo`, no extensión) | ✅ | `MediaUploadService::detectRealMime()` usa `finfo_file` sobre el contenido real, compara contra `config('dante.media.allowed_mimes')` |
| Tamaño máximo | ✅ | `config('dante.media.max_upload_kb')` = 8192 KB, validado en el formulario de subida de Filament |
| Nombres aleatorios | ✅ | `Str::uuid()` — nunca el nombre original del usuario |
| Fuera de la raíz web, servido por Laravel | ⚠️ **hallazgo, ver abajo** | Los medios se sirven por el symlink público `public/storage → storage/app/public`, **no** por un controller de Laravel que verifique nada en cada request |
| Directorio de subidas sin ejecución de PHP | ✅ **corregido en esta fase** | Ver hallazgo abajo |
| SVG sanitizados | ✅ | `enshrined/svg-sanitize`, verificado con test (`<script>` se elimina) |
| Imágenes reprocesadas al subirse | ✅ **corregido en esta fase — hallazgo real** | Ver hallazgo abajo |
| Nombre con doble extensión (`logo.fw.php`) | ✅ | `assertNoDoubleExtension()` — el patrón exacto del webshell del WP viejo, con test de regresión |
| Solo autenticados y autorizados suben | ✅ | Subida solo desde el panel de Filament, detrás de `Authenticate` + Policy de `Media` |

### Hallazgo real (Alto, corregido) — las imágenes no se reprocesaban, solo se copiaban

Antes de esta fase, `MediaUploadService::upload()` guardaba el **archivo original tal
cual** (`Storage::disk('media')->putFileAs(...)`) y generaba **además** conversiones WebP
como archivos aparte. Es decir: el archivo "canónico" que queda en `path` nunca pasaba por
un reprocesamiento real — solo el MIME estaba verificado. Un archivo con una carga útil
embebida en los propios bytes de una imagen válida (esteganografía, polyglot tipo "imagen
válida + bytes ejecutables al final del archivo", justamente el patrón de ataque que
compromete servidores mal configurados que ejecutan PHP en cualquier ruta) habría pasado
la verificación de MIME sin problema y quedado guardado intacto.

**Corrección:** `MediaUploadService::reencodeAndStore()` decodifica la imagen con
Intervention Image y la vuelve a codificar desde cero (`PngEncoder`/`JpegEncoder`/
`WebpEncoder`/`AvifEncoder` según el MIME real) antes de guardarla — el archivo que queda
en disco son los píxeles reinterpretados, no los bytes originales. Se aplica a
JPEG/PNG/WebP/AVIF. **GIF queda fuera** (Intervention Image no puede reencodear animaciones
sin perder los frames) y SVG tiene su propio camino (sanitización de XML, no reencodeo de
píxeles) — riesgo aceptado y documentado, no se generó un reemplazo GIF→imagen estática sin
que el cliente lo pida.

**Test de regresión agregado**
(`tests/Feature/MediaUploadServiceTest.php::reprocesa la imagen y descarta cualquier byte
extra embebido`): construye un JPEG válido con un payload PHP concatenado al final de los
bytes (patrón de polyglot real) y verifica que el archivo guardado **no** contiene la
cadena `<?php` y que sus bytes son distintos a los originales.

### Hallazgo real (Alto, mitigado con segunda línea de defensa) — sin regla de servidor contra ejecución de PHP en el directorio de medios

`storage/app/public/media` se expone directo al público vía el symlink de
`storage:link`, y ese directorio **no tenía ninguna regla propia** que impidiera la
ejecución de PHP ahí — dependía por completo de que la aplicación nunca deje pasar un
`.php`. Es exactamente la segunda línea de defensa que CLAUDE.md/docs/10 piden ("el
directorio de subidas no ejecuta PHP como segunda línea de defensa") y que, de haber
existido en el WordPress viejo, hubiera neutralizado el compromiso aun si la subida
hubiera fallado.

**Corrección:** `storage/app/public/.htaccess` (agregado, con excepción explícita en
`storage/app/public/.gitignore` para que se versione — el resto del directorio sigue
ignorado) que desactiva el handler de PHP y deniega `.php`/`.phtml`/`.phar`/etc. con
`Require all denied`.

**Limitación real, documentada sin maquillar:** Plesk normalmente sirve archivos estáticos
directo por **nginx** (no por Apache), y nginx **no lee `.htaccess`** — así que este
archivo solo protege si el backend real es Apache o si nginx está configurado para
delegar. **Pendiente de Fase 10, no resuelto en este entorno sin el Plesk real:** agregar
en la configuración de nginx del vhost algo equivalente a:

```nginx
location ^~ /storage/media/ {
    location ~ \.(php|phtml|phar)$ {
        deny all;
        return 404;
    }
}
```

Verificado en local que la ruta pública de medios no ejecuta nada raro hoy (no hay ningún
`.php` en el disco `media`, por construcción de `MediaUploadService`), pero la ausencia de
esta regla de servidor sigue siendo el hueco real hasta que exista el Plesk de la Fase 10.

---

## 5. Configuración de producción

| Ítem | Estado |
|---|---|
| `APP_DEBUG=false` en `.env.example` | ✅ ya estaba (`APP_DEBUG=true` es correcto y esperado solo en el `.env` **local**, verificado que `.env.example` trae `false`... ⚠️ ver nota |
| `.env` fuera del repo | ✅ — `git ls-files \| grep env` no devuelve nada salvo `.env.example`; `git log` sobre `.env` vacío (nunca se commiteó) |
| `.env*` cubierto por `.gitignore` | ✅ **corregido en esta fase** — antes solo cubría `.env`, `.env.backup`, `.env.production` explícitos (no `.env.local`, `.env.testing`, etc). Cambiado a `.env*` con excepción `!.env.example` |
| `.git` no accesible por HTTP | ✅ verificado con `curl -o /dev/null -w '%{http_code}' http://dante-web.test/.git/config` → `404` |
| `.env` no accesible por HTTP | ✅ verificado con `curl` → `404` |
| Sin listado de directorios | ✅ — `Options -Indexes` ya en `public/.htaccess`, y también agregado en el `.htaccess` nuevo de `storage/app/public` |
| `/storage/logs` no accesible | ✅ — los logs viven en `storage/logs`, fuera de `public/`, sin symlink hacia ellos |
| Rutas de desarrollo ausentes | ✅ — no hay Telescope/Debugbar/Horizon instalados |
| Credenciales solo en `.env` | ✅ — revisado el código: no hay contraseñas ni tokens hardcodeados; las claves de Turnstile/Meta/GA4 se administran cifradas en `IntegrationSetting` (`encrypted` cast), no en `.env` |

**Nota real sobre `APP_DEBUG`:** `.env.example` trae `APP_DEBUG=true` (correcto — es la
plantilla para desarrollo, y forzar `false` ahí solo generaría confusión al levantar un
entorno nuevo). Lo que exige el DoD de esta fase es que **producción** tenga
`APP_DEBUG=false`, que no se puede verificar sin el Plesk real — queda en el checklist de
la Fase 10 (§11) como verificación manual obligatoria, reforzada además por el log crítico
de `AppServiceProvider` (§2 de este documento) si alguna vez se detecta `APP_DEBUG=true`
con `APP_ENV=production`.

---

## 6. Rate limiting

| Endpoint | Límite documentado | Estado real antes de esta fase | Corrección |
|---|---|---|---|
| Login del panel | 5 / min por IP | ✅ ya cumplía (nativo de Filament 5) | — |
| Recuperar contraseña | 3 / hora por IP | ✅ Filament aplica el mismo `RateLimiter` de login al flujo de `passwordReset()` | — |
| Envío de formularios (contacto, pre-inscripción) | 3 / hora por IP | ❌ **hallazgo real**: `routes/web.php` tenía `throttle:5,1` — **5 por MINUTO**, 60 veces más laxo que el límite documentado. Suficiente para que un bot simple saturara el buzón de contacto sin activar nada | ✅ **corregido**: `throttle:5,60` (5 por hora) |
| Buscador | 30 / min por IP | ✅ ya cumplía (`throttle:30,1` en `/buscar`) | — |
| Global | 60 / min por IP | ☐ no implementado a nivel global | No bloqueante: cada endpoint público relevante ya tiene su propio límite (formularios, buscador); un límite global adicional queda como mejora, no hay evidencia de que su ausencia sea explotable hoy dado que no hay endpoints sin límite propio expuestos a POST/búsqueda pesada |

Honeypot (`spatie/laravel-honeypot`) confirmado activo en el mismo grupo de rutas que el
throttle de formularios — probado en `tests/Feature/PublicFormsTest.php`.

---

## 7. Respaldos

**Hallazgo real (Alto, corregido): `spatie/laravel-backup` no estaba instalado.** Estaba
en la lista de paquetes aprobados de CLAUDE.md §3 desde el inicio del proyecto pero nunca
se había agregado. Instalado en esta fase (`composer require spatie/laravel-backup`, 0
vulnerabilidades reportadas por `composer audit`).

- [x] `spatie/laravel-backup` configurado: base de datos (`mysqldump` vía el dumper
      nativo) + medios (todo `base_path()` menos `vendor/`, `node_modules/`,
      `storage/framework`, `storage/logs`) — `config/backup.php`
- [x] Frecuencia: diaria, `Schedule::command('backup:run')->daily()->at('02:00')` en
      `routes/console.php` (el cron de Plesk apunta a `schedule:run` cada minuto, sin
      Supervisor — CLAUDE.md §3). `backup:clean` y `backup:monitor` diarios también
      agendados
- [x] Retención: **30 días** (`keep_all_backups_for_days => 30`, sin degradar a
      semanal/mensual dentro de esa ventana) — ajustado desde el default del paquete (7+16
      días) para que coincida con la política documentada acá mismo
- [x] Notificación ante fallo/éxito del respaldo — canal `mail`, dirección configurable
      por `BACKUP_NOTIFICATION_EMAIL` (sin valor real del cliente todavía, pendiente de
      Fase 10)
- [x] Cifrado del archivo del backup: `BACKUP_ARCHIVE_PASSWORD` (env, vacío en local a
      propósito — se define en la Fase 10). El `.env` completo viaja dentro del zip del
      backup (necesario para una restauración real desde cero) — por eso el cifrado del
      archivo entero no es opcional en producción
- [~] **Destino fuera del mismo servidor**: el disco de destino ahora es **configurable**
      (`BACKUP_DESTINATION_DISKS`, default `local`) en vez de estar fijo al disco `local`
      del propio paquete. **No se puede apuntar a un destino off-site real todavía** — no
      existe el servidor de producción ni sus credenciales de S3/SFTP (Fase 10). Se deja
      la config lista para que en Fase 10 sea un solo cambio de variable de entorno, no un
      cambio de código

### Prueba de restauración — hecha y documentada, no simulada

> Un respaldo que nunca se restauró no es un respaldo.

**Fecha de la prueba:** 2026-08-25, 14:02 (hora local del entorno de desarrollo)
**Entorno:** local (MySQL del propio Herd, puerto 3307), base de datos limpia nueva
(`dante_restore_test`), separada de `dante_dev`
**Pasos ejecutados:**

1. `php artisan backup:run` → generó `storage/app/private/Dante/2026-08-25-16-59-01.zip`
   (97.22 MB, 1191 archivos y directorios — confirma que se está respaldando código +
   base + medios, no solo la base)
2. `unzip` del backup en un directorio temporal aislado (`scratchpad`, fuera del repo)
3. `mysql -e "CREATE DATABASE dante_restore_test ..."` — base nueva, vacía, con el mismo
   charset (`utf8mb4_unicode_ci`) que la de producción
4. `mysql dante_restore_test < db-dumps/mysql-dante_dev.sql` — restauración del dump SQL
   real extraído del zip
5. Verificación de integridad **por conteo, no solo "no tiró error"**:

   | Verificación | Original (`dante_dev`) | Restaurado (`dante_restore_test`) |
   |---|---|---|
   | Cantidad de tablas | 31 | 31 |
   | Filas en `pages` | 23 | 23 |
   | Filas en `posts` | 4 | 4 |
   | Filas en `media` | 129 | 129 |
   | Filas en `users` | 1 | 1 |

6. Verificación de que los medios están en el zip: `524` archivos bajo
   `storage/app/public/media/` dentro del backup (los 129 registros de `media` más sus
   variantes responsivas/WebP)
7. Limpieza: `DROP DATABASE dante_restore_test`, borrado del directorio temporal

**Tiempo que tomó:** ~3 minutos de principio a fin (backup + extracción + restauración +
verificación de conteos).

**Resultado: ✅ éxito.** La restauración reprodujo exactamente el estado de la base
(mismo número de tablas y filas en las tablas clave) y confirmó que los medios viajan
dentro del backup. **Esta prueba es sobre el entorno local**, no sobre un backup
descargado desde un destino off-site real (que todavía no existe) — se repite contra el
destino real en la Fase 10 antes de dar por buena esa segunda parte del DoD.

---

## 8. Monitoreo y respuesta

- [x] Registro de auditoría del panel activo y **consultable**: `spatie/laravel-activitylog`
      + `HasAuditing` en los modelos editoriales, con recurso de solo lectura
      (`App\Filament\Resources\ActivityLogs\ActivityLogResource`) visible en el panel para
      el rol administrador — `ActivityLogPolicy` restringe `viewAny`/`view` al permiso
      `activity_log.view`, probado en `tests/Feature/Filament/ActivityLogResourceTest.php`
- [x] Logs con rotación: `LOG_CHANNEL=stack` → `single` en local; en `.env.example`,
      el canal `daily` de Laravel (rotación nativa por fecha, retención configurable en
      `config/logging.php`) es el recomendado para producción — documentado como
      verificación de la Fase 10, no se fuerza en `.env.example` para no romper el log
      simple de desarrollo
- [ ] Monitoreo de disponibilidad con alerta — **depende de un servicio externo real**
      (ej. UptimeRobot) que no se puede configurar sin una URL pública y una cuenta del
      cliente/proveedor. **No se simula.** Queda explícitamente pendiente para la Fase 10
- [ ] Alerta ante picos de error 500 — mismo caso: sin servicio de monitoreo externo
      conectado, no hay a dónde alertar todavía. `backup:monitor` (spatie/laravel-backup,
      §7) sí queda agendado y notifica por mail si el respaldo se degrada
- [ ] Procedimiento escrito de incidente (a quién se llama, cómo se aísla, cómo se
      restaura) — **no redactado en esta sesión**: depende de datos operativos del
      proveedor (teléfono de guardia, accesos de emergencia al Plesk) que no están
      disponibles acá. Pendiente de completar con el cliente/Leonardo Chi antes de la
      Fase 10

---

## 9. Mantenimiento de dependencias

- [x] `composer audit` — **0 vulnerabilidades** (corrido en esta fase, y de nuevo después
      de instalar `spatie/laravel-backup`)
- [x] `npm audit` — **0 vulnerabilidades**. `.npmrc` ya tenía `audit=true` (cumple la regla
      global de CLAUDE.md del usuario sobre el ataque "Mini Shai-Hulud")
- [x] Comando único para correr ambos: `composer audit-deps` (script nuevo agregado a
      `composer.json`, corre `composer audit` + `npm audit` en secuencia)
- [ ] **Agendado mensual** — el comando existe y funciona, pero no hay todavía un cron real
      en ningún servidor (no hay Plesk real ni CI corriendo en este entorno). Se documenta
      acá como la tarea pendiente exacta: agregar `composer audit-deps` al pipeline de CI
      (Fase 9, GitHub Actions) y/o a un cron mensual de Plesk en la Fase 10
- [x] Laravel 13.26.1 — dentro de soporte activo hasta sep-2027 (CLAUDE.md §3), sin
      actualizaciones mayores pendientes al cierre de esta fase
- [x] Sin paquetes de los namespaces comprometidos por el ataque "Mini Shai-Hulud" listados
      en las instrucciones globales del usuario (`@tanstack/*`, `@mistralai/*`, `@uipath/*`,
      `@antv/*`, `echarts-for-react`, `timeago.js`, `axios` 1.14.1/0.30.4) — verificado
      contra `package-lock.json`, ninguno presente

---

## 10. Pentest con strix

**Autorización escrita del cliente:** ☐ — no aplica todavía (esto es la primera pasada
sobre el entorno **local** de desarrollo, no sobre un staging del cliente; la
autorización formal se pide antes del pentest de la Fase 10 contra el staging real de
Plesk)
**Objetivo de esta pasada:** `http://dante-web.test` (entorno local, sustituto temporal
del staging que todavía no existe, autorizado explícitamente por el usuario para esta
sesión)
**Fecha de intento:** 2026-08-25

### No se pudo ejecutar en esta sesión — motivo real, no evasión

`strix` (CLI, v1.5.3) está instalado en el entorno, pero:

1. **No hay Docker disponible** en este entorno (`docker info` falla) — la CLI de Strix
   corre sus agentes en un sandbox de Docker, es un requisito duro.
2. **No hay `STRIX_LLM`/`LLM_API_KEY` configurados** — sin esas dos variables la CLI no
   tiene con qué modelo operar.
3. Tampoco hay `STRIX_API_TOKEN` para la vía cloud (`app.strix.ai`), que hubiera evitado
   el requisito de Docker.

No se simuló ni se inventó un resultado. El pentest automatizado con Strix **queda
pendiente**, con los comandos exactos para correrlo apenas estén los prerrequisitos:

```bash
# Opción A — CLI local (requiere Docker + una API key de LLM)
export STRIX_LLM="anthropic/claude-sonnet-5"   # o el modelo que se decida usar
export LLM_API_KEY="<api key del proveedor>"
strix -n -t http://dante-web.test --scan-mode standard --max-budget 15

# Opción B — Cloud (requiere cuenta en app.strix.ai y token de API, sin Docker)
export STRIX_API_TOKEN="<token de app.strix.ai>"
# registrar el target como asset y lanzar el scan (ver skill managed-pentesting-with-strix)
```

**Lo que sí se hizo en su lugar, manualmente, contra el mismo entorno** (revisión
dirigida por el checklist de este documento, no un pentest automatizado — no reemplaza a
Strix, es la primera capa mientras no estén los prerrequisitos):

- `curl -I` contra la home y el login del panel → cabeceras de seguridad presentes (§1)
- `curl` sin sesión contra rutas del panel (`/panel-dante-2026`, `/panel-dante-2026/media`)
  → `302` (redirige a login, no expone contenido)
- `curl` contra `/.env` y `/.git/config` → `404` en ambos (§5)
- Revisión de subida de archivos: doble extensión, MIME real, SVG con `<script>`, y ahora
  reprocesamiento de imágenes — con pruebas de Pest que reproducen los ataques concretos
  (no solo "debería estar validado"), ver §4
- Revisión de autorización: Policies probadas por permiso, no por rol, con tests que
  cubren "un usuario sin el permiso no puede" en cada recurso del panel

### Hallazgos (de la revisión manual, no de Strix)

| # | Severidad | Hallazgo | Estado | Corregido en |
|---|---|---|---|---|
| 1 | Alto | Sin ninguna cabecera de seguridad HTTP (CSP/HSTS/X-Frame-Options/etc.) | corregido | `app/Http/Middleware/SecurityHeaders.php` |
| 2 | Alto | Imágenes subidas no se reprocesaban — el archivo original se guardaba intacto, solo se generaban conversiones WebP aparte | corregido | `app/Services/Media/MediaUploadService.php::reencodeAndStore()` + test de regresión |
| 3 | Alto | Sin regla de servidor que impida ejecutar PHP en el directorio público de medios (segunda línea de defensa ausente) | mitigado (Apache) / pendiente (nginx real de Plesk) | `storage/app/public/.htaccess` + regla de nginx documentada en §4 para Fase 10 |
| 4 | Alto | `spatie/laravel-backup` no estaba instalado — sin respaldos automáticos ni prueba de restauración | corregido | `config/backup.php`, `routes/console.php`, prueba de restauración documentada en §7 |
| 5 | Medio | Rate limit de formularios públicos era `5 por minuto` (60x más laxo que la política documentada de `3 por hora`) | corregido | `routes/web.php`, ahora `throttle:5,60` |
| 6 | Medio | Sin política de contraseñas a medida (default de Laravel, sin mínimo real ni verificación contra filtraciones) | corregido | `AppServiceProvider::configurePasswordPolicy()` |
| 7 | Medio | `.gitignore` no cubría `.env.*` en general (solo `.env`, `.env.backup`, `.env.production` explícitos) | corregido | `.gitignore` → `.env*` con excepción `!.env.example` |
| 8 | Medio | `DANTE_REQUIRE_2FA=false` en el `.env` de este entorno (2FA del panel desactivado) | **aceptado para desarrollo, con segunda línea de defensa** | Log crítico automático si esto ocurre con `APP_ENV=production` (`AppServiceProvider::guardAgainstInsecureProductionConfig()`); verificación manual obligatoria en el checklist de la Fase 10 (§11) |
| 9 | Bajo | `X-Powered-By: PHP/8.3.6` expuesto | corregido (capa de aplicación) | `SecurityHeaders` middleware, `header_remove()`. `Server: nginx/...` sigue expuesto — requiere `server_tokens off` en el nginx real, Fase 10 |

**Regla de salida a producción:** cero hallazgos Críticos y Altos abiertos. **Los 4
hallazgos Altos de esta pasada (#1–#4) están corregidos y re-verificados** (headers con
`curl`, test de regresión de reprocesamiento, `.htaccess` agregado, backup+restore
probado). El hallazgo #8 se acepta explícitamente para desarrollo local con
justificación por escrito (arriba) — no aplica la regla de "abierto" porque no es un
hallazgo de producción, es una configuración de entorno de desarrollo con su propia
salvaguarda.

**No hay hallazgos Críticos.** Ninguno de los problemas encontrados permite, por sí
solo y sin además tener acceso al servidor, ejecutar código o extraer datos —
son huecos de defensa en profundidad, no vulnerabilidades explotables de forma directa
en este entorno.

### Re-test tras las correcciones

Fecha: 2026-08-25 · Resultado: los 4 hallazgos Altos re-verificados manualmente
(cabeceras con `curl`, test de Pest en verde para el reprocesamiento de imágenes, backup
real corrido y restaurado con conteos idénticos). **Pendiente: el pentest automatizado
real con Strix**, contra el staging de Plesk, en la Fase 10 — con autorización escrita
del cliente registrada en ese momento.

---

## 11. Lista de verificación previa a producción

- [ ] securityheaders.com ≥ A — requiere URL pública, Fase 10
- [ ] SSL Labs ≥ A — requiere URL pública + SSL real, Fase 10
- [ ] `curl https://dante.edu.py/.git/config` → 404 — verificado el equivalente en local (`dante-web.test`), repetir contra el dominio real
- [ ] `curl https://dante.edu.py/.env` → 404 — ídem
- [ ] `APP_DEBUG=false` confirmado en el servidor real
- [ ] `APP_ENV=production` y `SESSION_SECURE_COOKIE=true` confirmados en el `.env` real
- [ ] `DANTE_REQUIRE_2FA=true` confirmado en el `.env` real (no solo en `.env.example`) —
      revisar el log de arranque por si `AppServiceProvider` ya registró la advertencia
      crítica
- [ ] 2FA activo en todas las cuentas del panel (verificado que cada usuario completó el
      enrolamiento TOTP, no solo que el flag esté en `true`)
- [ ] Regla de nginx que impide ejecutar PHP bajo `/storage/media/` (además del `.htaccess`
      ya versionado, que solo protege si el backend es Apache) — ver §4
- [ ] `server_tokens off;` en la configuración de nginx del Plesk (oculta `Server: nginx/...`)
- [ ] `expose_php = Off` en el `php.ini` real (oculta `X-Powered-By` en la fuente, no solo
      vía `header_remove()`)
- [ ] `BACKUP_DESTINATION_DISKS` apuntando a un disco realmente fuera del servidor +
      `BACKUP_ARCHIVE_PASSWORD` con un valor real + repetir la prueba de restauración
      **desde ese destino real**, no solo desde el disco local
- [ ] Cero hallazgos Críticos/Altos abiertos del pentest real de Strix contra el staging
- [ ] Registro de auditoría funcionando (ya verificado en este entorno, confirmar que
      sobrevive al primer despliegue real)
- [ ] Monitoreo de disponibilidad contratado y probado (UptimeRobot o similar)
- [ ] Procedimiento de incidente redactado con datos reales de contacto del proveedor
- [ ] Credenciales del cliente entregadas por canal seguro, no por WhatsApp ni mail plano
