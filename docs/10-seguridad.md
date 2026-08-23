# 10 — Seguridad (Fase 8)

Estado: **vacío — completar en Fase 8**
Herramienta: `strix` — ⚠️ **solo contra nuestro propio staging, con autorización escrita del cliente**

> Contexto: el sitio anterior fue comprometido. Esta fase no es un trámite. El cliente va a
> preguntar "¿cómo sé que no vuelve a pasar?" y este documento es la respuesta.

---

## 1. Cabeceras HTTP

| Cabecera | Valor | Estado |
|---|---|---|
| `Content-Security-Policy` | (definir — empezar en `report-only`, ajustar, luego forzar) | ☐ |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` | ☐ |
| `X-Frame-Options` | `SAMEORIGIN` | ☐ |
| `X-Content-Type-Options` | `nosniff` | ☐ |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | ☐ |
| `Permissions-Policy` | desactivar cámara, micrófono, geolocalización si no se usan | ☐ |
| `X-Powered-By` / `Server` | ocultos | ☐ |

Verificar en securityheaders.com — objetivo: **A o superior**.

---

## 2. Panel de administración

- [ ] Ruta del panel **no adivinable**, definida en `.env` (no `/admin`)
- [ ] **2FA obligatorio** para todos los usuarios del panel
- [ ] Política de contraseñas: mínimo 12 caracteres, verificadas contra bases de filtraciones
      (`Password::defaults()->uncompromised()`)
- [ ] Bloqueo tras 5 intentos fallidos
- [ ] `throttle` en login y en recuperación de contraseña
- [ ] Sesiones con expiración por inactividad (60 min)
- [ ] Cookies `Secure`, `HttpOnly`, `SameSite=Lax`
- [ ] Cierre de sesión invalida la sesión del servidor
- [ ] Notificación por mail ante inicio de sesión desde IP nueva
- [ ] Sin usuario "admin" genérico — cada persona con su cuenta

---

## 3. Entrada y salida

- [ ] Toda entrada validada con Form Request. **Nada de `$request->all()`** en `create`/`update`
      sin `$fillable` acotado
- [ ] Salida escapada por defecto en Blade (`{{ }}`). Cada `{!! !!}` del proyecto está
      justificado y su contenido pasó por sanitización

| `{!! !!}` en el código | Justificación | Sanitizado por |
|---|---|---|

- [ ] HTML del editor sanitizado en el servidor con lista blanca (`docs/05` §5)
- [ ] Consultas siempre por Eloquent o con bindings — cero SQL concatenado
- [ ] CSRF activo en todos los formularios
- [ ] Autorización por Policy en cada acción del panel — no alcanza con ocultar el botón

---

## 4. Subida de archivos

> Fue el vector más probable del compromiso anterior. Acá se cierra.

- [ ] Lista blanca de tipos MIME **reales** (verificados con `finfo`, no por extensión)
- [ ] Tamaño máximo por archivo
- [ ] Nombres de archivo aleatorios — nunca el nombre original
- [ ] Almacenamiento **fuera de la raíz web**, servido por Laravel
- [ ] El directorio de subidas no ejecuta PHP (regla en Plesk como segunda línea de defensa)
- [ ] SVG sanitizados antes de servirse (pueden contener `<script>`)
- [ ] Imágenes reprocesadas al subirse — eso destruye cualquier payload embebido
- [ ] Solo usuarios autenticados y autorizados pueden subir

---

## 5. Configuración de producción

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` propia y distinta de la de desarrollo
- [ ] `.env` fuera de la raíz web y sin permisos de lectura pública
- [ ] `.git` no accesible por HTTP — **probarlo con `curl https://dante.edu.py/.git/config`**
- [ ] Sin listado de directorios
- [ ] `/storage/logs` no accesible
- [ ] Rutas de desarrollo (Telescope, Debugbar, Horizon) ausentes o protegidas
- [ ] Mensajes de error genéricos de cara al usuario
- [ ] Credenciales solo en `.env`, nunca en el repo — verificar el historial de git también

---

## 6. Rate limiting

| Endpoint | Límite |
|---|---|
| Login del panel | 5 / min por IP |
| Recuperar contraseña | 3 / hora por IP |
| Envío de formularios | 3 / hora por IP |
| Buscador | 30 / min por IP |
| Global | 60 / min por IP |

---

## 7. Respaldos

- [ ] `spatie/laravel-backup` configurado: base + medios
- [ ] Frecuencia: diaria. Retención: 30 días
- [ ] Destino **fuera del mismo servidor**
- [ ] Notificación ante fallo del respaldo
- [ ] **Restauración probada al menos una vez, en un entorno limpio**

> Un respaldo que nunca se restauró no es un respaldo. Documentar la prueba acá:
>
> **Fecha de la prueba de restauración:**
> **Tiempo que tomó:**
> **Resultado:**

---

## 8. Monitoreo y respuesta

- [ ] Monitoreo de disponibilidad con alerta
- [ ] Alerta ante picos de error 500
- [ ] Registro de auditoría del panel activo y consultable por el cliente
- [ ] Logs con rotación y retención definida
- [ ] Procedimiento escrito de qué hacer ante un incidente: a quién se llama, cómo se aísla,
      cómo se restaura

---

## 9. Mantenimiento de dependencias

- [ ] `composer audit` y `npm audit` — **mensual, agendado**, no cuando alguien se acuerde
- [ ] Actualizaciones menores de Laravel aplicadas en el mes
- [ ] Plan de actualización mayor antes del fin de soporte activo (Laravel 13: sep-2027)
- [ ] Este mantenimiento está en el contrato recurrente. Si no está, el sitio se degrada igual
      que el WordPress

---

## 10. Pentest con strix

**Autorización escrita del cliente:** ☐ — fecha:
**Objetivo:** staging propio, `https://staging.dante.edu.py`
**Fecha de ejecución:**

### Hallazgos

| # | Severidad | Hallazgo | Estado | Corregido en |
|---|---|---|---|---|
| | Crítico / Alto / Medio / Bajo | | abierto / corregido / aceptado | |

**Regla de salida a producción:** cero hallazgos Críticos y Altos abiertos. Los Medios se
corrigen o se aceptan por escrito con justificación.

### Re-test tras las correcciones

Fecha: · Resultado:

---

## 11. Lista de verificación previa a producción

- [ ] securityheaders.com ≥ A
- [ ] SSL Labs ≥ A
- [ ] `curl https://dante.edu.py/.git/config` → 404
- [ ] `curl https://dante.edu.py/.env` → 404
- [ ] `APP_DEBUG=false` confirmado en el servidor
- [ ] 2FA activo en todas las cuentas del panel
- [ ] Respaldo corriendo y restauración probada
- [ ] Cero hallazgos Críticos/Altos abiertos
- [ ] Registro de auditoría funcionando
- [ ] Credenciales del cliente entregadas por canal seguro, no por WhatsApp ni mail plano
