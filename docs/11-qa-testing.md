# 11 — QA y testing (Fase 9)

Estado: **cerrada la ejecución de QA de esta sesión.** Suite automatizada en verde
(Pest 192/192, Playwright 65 pasados / 95 saltados por diseño, 0 fallos). Quedan
pendientes explícitos, no simulables en este entorno: verificación en dispositivos
físicos reales (iOS/Android) y la aceptación del cliente sobre staging — ver §4 y §7.

**Actualización post-sesión (mismo día):** los hallazgos #7 y #8 de abajo (honeypot sin
mensaje claro, 2FA sin test automatizado) ya no están abiertos — se resolvieron a pedido
del cliente al reemplazar el 2FA por app autenticadora (TOTP, obligatorio) por 2FA por
código de email (opt-in por usuario, ver ADR-003). El nuevo diseño sí se puede probar con
Pest sin desactivar ninguna protección real, y el honeypot ahora redirige con un mensaje
en vez de una página en blanco. Ver `tests/Feature/Filament/EmailTwoFactorAuthenticationTest.php`
y `app/Http/Responses/HoneypotRedirectResponder.php`. Suite creció de 188 a 192 tests.
Herramienta: `playwright-cli` — ⚠️ **solo credenciales de prueba, nunca las reales del
cliente** (usuario `qa.playwright@dante-web.test`, creado por `php artisan dante:e2e-fixtures`,
nunca se usó ni se documenta ninguna credencial real).

---

## 1. Tests automatizados (Pest)

**188/188 en verde** (656 → 668 aserciones tras esta fase), corridos con
`vendor/bin/pest` sobre `dante-web.test` en local (SQLite en memoria, ver `phpunit.xml`).
Antes de esta fase: 137/137 (Fase 8). Se sumaron 51 tests reales, no duplicados.

| Suite | Casos | Estado |
|---|---|---|
| Autenticación del panel | login correcto/incorrecto, usuario desactivado, rate limit (6º intento bloqueado), logout, redirección sin sesión, reset de contraseña, perfil | ✅ `tests/Feature/Filament/PanelAuthenticationTest.php` |
| 2FA | por email, opt-in por usuario (ADR-003): alerta persistente sin activar, activación con código real, exige código en el próximo login, código incorrecto/reusado rechazado | ✅ `tests/Feature/Filament/EmailTwoFactorAuthenticationTest.php` (nuevo, post-sesión) |
| Permisos por rol | cada recurso del panel prueba al menos "un rol sin el permiso no puede listar"; se sumaron en esta fase los que faltaban (Documentos, Galerías, Redirecciones) y, más importante, la autorización real de **borrar** para roles que sí pueden ver/editar pero no deben poder borrar (Páginas, Documentos, Comunicados, Calendario, Galerías) — probando `$user->can('delete', $modelo)` directo contra la Policy, no solo que el botón esté oculto | ✅ 18 archivos en `tests/Feature/Filament/` |
| CRUD de páginas | crear, editar, publicar, despublicar, borrar, slug único, sanitización, bloques, portada/SEO desde el picker de medios, destacado en home | ✅ `PageResourceTest.php`, `PageBlocksTest.php` |
| CRUD de noticias | + categorías, destacados, sanitización | ✅ `PostResourceTest.php` |
| Bloques de contenido | 16/16 del catálogo, agregar/reordenar/eliminar, sanitización recursiva dentro de `data` | ✅ `PageBlocksTest.php` (ya existía de Fase 3/4) |
| Medios | subida válida, MIME real, tipo inválido rechazado, conversiones | ✅ `MediaResourceTest.php`, `MediaUploadServiceTest.php` |
| Menús | CRUD, jerarquía, orden | ✅ `MenuResourceTest.php` |
| Formularios | Contacto: envío válido + mail (`Notification::fake`), honeypot, rate limit, validación, Turnstile (4 casos: apagado, apagado con claves cargadas, token inválido, token válido, sin token). **Pre-inscripción: sin cobertura Pest hasta esta sesión** — agregado `tests/Feature/PreRegistrationFormTest.php` (mail, honeypot, rate limit, validación, sede inválida) | ✅ `PublicFormsTest.php`, `TurnstileTest.php`, `PreRegistrationFormTest.php` (nuevo) |
| Redirecciones | 301 al destino correcto, sin bucles, las 39 reales verificadas una por una | ✅ `RedirectResourceTest.php`, `Redirects301VerificationTest.php`, `RedirectMiddlewareTest.php` |
| Sanitización HTML | `<script>`, `onerror=`, imágenes de dominio externo bloqueadas | ✅ `HtmlSanitizerTest.php` + regresión en `PageResourceTest.php`/`AnnouncementResourceTest.php` |
| Rutas públicas | 200 en las 12 plantillas, 404 en borrador/inexistente | ✅ `PublicContentRoutesTest.php`, `PageControllerTest.php`, `HomeControllerTest.php` |
| SEO | meta, canonical, JSON-LD (`EducationalOrganization`, `BreadcrumbList`, `Article`, `FAQPage`, `WebSite`+`SearchAction`) | ✅ `SeoMetadataTest.php` |
| Sitemap | válido, excluye borradores y `noindex` | ✅ `SitemapAndRobotsTest.php` |
| Auditoría | cada cambio queda registrado con usuario y fecha, solo visible para administrador | ✅ `ActivityLogResourceTest.php` |
| Cabeceras de seguridad | CSP, HSTS, X-Frame-Options, etc. | ✅ `SecurityHeadersTest.php` (Fase 8) |
| Cookies / consentimiento | ningún script de terceros incondicional | ✅ `CookieConsentTest.php` |
| Integraciones | Meta Conversions API, ajustes cifrados del panel | ✅ `MetaConversionsApiTest.php`, `IntegrationSettingsTest.php` |
| Rendimiento | N+1, caché de consulta | ✅ `PerformanceTest.php` (Fase 7) |

**Cobertura de rutas del panel:** `php artisan route:list` filtrado a `panel-dante-2026`
da **47 rutas** (login, logout, password-reset ×2, profile, y los 15 recursos ×3
—listar/crear/editar— + `home-settings`, `integration-settings`, `site-settings/{record}/edit`
como páginas de ajuste única). Las 18 suites de `tests/Feature/Filament/` tocan cada uno de
los 15 recursos (`activity_log`, `announcements`, `calendar_events`, `categories`, `documents`,
`form_submissions`, `galleries`, `home_settings`, `integration_settings`, `media`, `menus`,
`pages`, `posts`, `redirects`, `site_settings`, `users`) y `PanelAuthenticationTest.php` cubre
login/logout/password-reset/profile. **100 % de las rutas del panel tocadas por al menos un
test.**

```bash
php artisan test          # o vendor/bin/pest
vendor/bin/pest --coverage
```

---

## 2. Tests E2E (Playwright)

Las 5 tareas críticas de `docs/02-ux-arquitectura-informacion.md` §6, en móvil y escritorio,
más el flujo #6 del panel. Corren contra `dante-web.test` (Herd/Valet), nunca contra
producción, con los datos de `php artisan dante:e2e-fixtures` (contenido marcado `[QA E2E]`,
usuario `qa.playwright@dante-web.test`).

| # | Recorrido | Móvil | Escritorio |
|---|---|---|---|
| 1 | Inscribir a mi hijo/a: inicio → Admisiones → pre-inscripción Asunción → envío | ✅ | ✅ |
| 2 | Ver el calendario académico: inicio → pie → calendario, evento visible | ✅ | ✅ |
| 3 | Contactar a la secretaría: inicio → Contacto → datos directos + formulario, y validación de campos obligatorios | ✅ | ✅ |
| 4 | Ver noticias y comunicados recientes: listado → detalle, y comunicados | ✅ | ✅ |
| 5 | Descargar un documento institucional: listado → descarga real del PDF (200, `content-type: pdf`) | ✅ | ✅ |
| 6 | Flujo completo del panel: login → crear página → publicar → verla en el sitio | ✅ | ✅ |

**40/40 pasan** en los 5 proyectos configurados (`playwright.config.ts`): Desktop Chrome,
Desktop Firefox, Desktop Safari (WebKit), Mobile Chrome (Pixel 7), Mobile Safari (iPhone 14).

**Hallazgo real corregido durante esta fase** (documentado en el propio spec,
`e2e/critical-tasks.spec.ts`): la página `/admisiones` no tenía ningún enlace hacia los
formularios de pre-inscripción — se agregaron dos CTA reales, uno por sede.

⚠️ Corren contra **desarrollo local con datos de prueba**. Nunca contra producción ni con
credenciales reales del cliente.

```bash
npx playwright test                                 # los 5 proyectos
npx playwright test --project="Desktop Chrome"       # uno solo
```

---

## 3. Accesibilidad

Suite nueva de esta fase: `e2e/accessibility.spec.ts`, con `@axe-core/playwright` sobre las
11 plantillas públicas + login del panel + dashboard autenticado (corre una sola vez, en
Desktop Chrome — axe evalúa el DOM servido, no cambia por motor de navegador).

| Plantilla | axe sin violaciones | Teclado completo | Lector de pantalla |
|---|---|---|---|
| Inicio | ✅ | ✅ (ver abajo) | ⚠️ manual, no hecha en esta sesión |
| Institucional | ✅ | ✅ | ⚠️ manual, no hecha en esta sesión |
| Noticias (listado + detalle) | ✅ | ✅ | ⚠️ manual, no hecha en esta sesión |
| Contacto | ✅ | ✅ (tab order del formulario verificado) | ⚠️ manual, no hecha en esta sesión |
| Búsqueda | ✅ | ✅ | ⚠️ manual, no hecha en esta sesión |
| 404 | ✅ (con 1 hallazgo real corregido, ver abajo) | ✅ | ⚠️ manual, no hecha en esta sesión |
| Comunicados / Calendario / Galería / Documentos | ✅ | — | — |
| Panel admin (login + dashboard autenticado) | ✅ | — | — |

**Objetivo:** WCAG 2.1 AA. **Resultado real: 13/13 plantillas sin violaciones de axe**
(tras corregir el hallazgo de contraste de abajo).

### Hallazgos reales de accesibilidad, encontrados por axe/E2E y corregidos en esta sesión

1. **[Corregido] Contraste insuficiente en la página 404** (`resources/views/errors/404.blade.php`,
   `resources/css/app.css` `.not-found .code`) — el "404" decorativo usaba
   `--color-neutral-200` (#e4e4e4) sobre blanco: contraste real de **1.27:1**, muy por debajo
   del 3:1 mínimo de WCAG 2.1 AA para texto grande. Es `aria-hidden="true"` (no lo anuncia un
   lector de pantalla), pero el contraste visual igual tiene que cumplir para usuarios con baja
   visión que sí lo ven. Corregido a `--color-neutral-600` (≈5.7:1).
2. **[Corregido] El enlace "Saltar al contenido principal" no movía el foco** — apuntaba a
   `#contenido`, pero ningún `<main id="contenido">` tenía `tabindex="-1"`, así que activarlo
   con teclado no llevaba el foco a ningún lado (el navegador desplaza la vista pero no puede
   enfocar un elemento no focuseable). Corregido agregando `tabindex="-1"` a los 13 `<main
   id="contenido">` del proyecto (una sola búsqueda y reemplazo, un `<main>` por plantilla).
   Confirmado con un test E2E nuevo que aprieta Tab, activa el skip link con Enter y verifica
   que el foco quedó en `#contenido`.
3. **[Corregido] El menú móvil cerrado seguía en el árbol de accesibilidad y en el tab order**
   (`resources/views/components/site-header.blade.php`) — el panel `#mobile-nav` solo se movía
   fuera de la pantalla con `transform: translateX(100%)` al cerrarse; no tenía
   `display:none` ni `aria-hidden`/`inert`. Un usuario de teclado podía seguir tabulando hacia
   sus enlaces (invisibles) después de "cerrarlo", y un lector de pantalla seguía anunciando su
   contenido. Corregido con `:inert="!mobileOpen"` (Alpine.js), que saca el panel del árbol de
   accesibilidad y del tab order mientras está cerrado sin tocar la animación existente.
   Confirmado con un test E2E que abre el menú, lo cierra con `Escape` y verifica
   `toHaveJSProperty('inert', true)` + que el foco vuelve al botón que lo abrió.

### Pruebas manuales que axe no detecta

- [x] Recorrer todo el sitio solo con teclado, sin mouse — verificado con Playwright
      (`Tab`/`Escape`/`Enter` reales, no simulados), cubre header, menú móvil, formulario de
      Contacto y skip link
- [x] El foco siempre visible y en orden lógico — verificado el orden real del formulario de
      Contacto (Nombre → Teléfono → Email → Mensaje) y que el foco vuelve al botón correcto al
      cerrar el menú móvil
- [x] El menú móvil se abre, se navega y se cierra con `Esc` — cubierto arriba
- [ ] Los mensajes de error se anuncian — no verificado con lector de pantalla real en esta
      sesión (ver abajo)
- [ ] Probar una página con lector de pantalla (VoiceOver o NVDA) — **no hecho en esta
      sesión**: no hay forma de automatizar de verdad la experiencia con NVDA/VoiceOver sin un
      operador humano frente al lector de pantalla real. Queda como pendiente explícito,
      recomendado antes de la aceptación del cliente
- [x] Zoom al 200 % sin pérdida de contenido ni scroll horizontal — la propiedad CSS `zoom` NO
      reproduce el zoom real del navegador (se probó y descartó por dar falsos positivos: no
      reduce los píxeles CSS disponibles, solo magnifica el render). Sustituto correcto usado:
      viewport de 640 px de ancho a escala 1:1 (los mismos píxeles CSS que ve un usuario con
      zoom real al 200 % sobre un diseño de 1280 px) — sin scroll horizontal en las 5 plantillas
      principales, verificado con Playwright

```bash
npx playwright test e2e/accessibility.spec.ts --project="Desktop Chrome"
```

---

## 4. Compatibilidad

| Entorno | Probado | Problemas |
|---|---|---|
| Chrome escritorio (Chromium vía Playwright) | ✅ | Ninguno |
| Firefox escritorio | ✅ | Ninguno |
| Safari escritorio (WebKit vía Playwright) | ✅ | Ninguno |
| Edge | ⚠️ no probado por separado — mismo motor Chromium que "Chrome escritorio", no aporta cobertura real distinta con las herramientas disponibles | — |
| iOS Safari — dispositivo real | ❌ **no disponible en esta sesión** | Sin dispositivo físico. El proyecto "Mobile Safari (iOS)" de Playwright (motor WebKit, viewport iPhone 14) es el sustituto usado, no reemplaza el hardware/OS real (teclado en pantalla, gestos, Safari real de iOS) |
| Chrome Android — dispositivo real | ❌ **no disponible en esta sesión** | Mismo caso: "Mobile Chrome (Android)" (viewport Pixel 7) es el sustituto |
| Pantalla pequeña (320 px) | ✅ | Sin scroll horizontal (mismo criterio que el zoom 200%, verificado en viewport móvil real de los proyectos Playwright) |
| Pantalla grande (2560 px) | ⚠️ no verificado explícitamente en esta sesión — el diseño usa `max-width` en los contenedores principales (ver `docs/04-ui-design-system.md`), riesgo bajo pero no confirmado con una corrida dedicada | — |

**Sigue igual que lo anotado en `docs/01-analisis-descubrimiento.md`:** la verificación en
dispositivos físicos reales (iOS/Android) requiere hardware que no está disponible en este
entorno de desarrollo. No se duplica la pregunta ahí — este es el mismo pendiente, confirmado
otra vez desde el lado de QA.

---

## 5. Verificación de contenido

Suite nueva: `e2e/content-verification.spec.ts`, rastreo real contra el contenido migrado de
la Fase 5 (no fixtures) sobre las 8 plantillas con más enlaces internos.

- [x] **Cero enlaces rotos** — rastreo desde Inicio, Institucional, Noticias, Contacto,
      Comunicados, Calendario, Galería y Documentos, siguiendo todos los enlaces internos que
      encuentra un nivel más: **0 enlaces rotos** de los recorridos
- [x] **Cero imágenes faltantes** — mismas 8 plantillas, cada `<img>` verificado con
      `naturalWidth > 0` (si no carga, es 0): **0 imágenes rotas**
- [x] **Cero `alt` faltante** — se acepta `alt=""` (decorativo, válido en WCAG 1.1.1); el
      defecto real sería la ausencia del atributo. **0 imágenes sin `alt`**
- [ ] Revisión ortográfica de todo el sitio — no automatizable de forma confiable en español
      con las herramientas disponibles en esta sesión (los correctores automáticos genéricos
      dan demasiados falsos positivos con nombres propios, términos de la marca e italiano).
      Recomendado como paso manual antes de la aceptación del cliente
- [x] Datos de contacto correctos: verificado contra el contenido real cargado en Fase 5/6 y
      documentado en `docs/03-copywriting.md` §7 y `docs/01-analisis-descubrimiento.md`
      pregunta #19/#22 — los teléfonos y mails de `/contacto` y el pie de página
      (`colegioasu@dante.edu.py`, +595 (21) 491 622 / 984 464500, +595 (21) 500 370 /
      984 464501, +595 974 812022) coinciden exactamente con los documentados como reales
- [x] Enlaces a redes sociales correctos — cubiertos por el rastreo de enlaces (§5, arriba)
- [ ] Mapa apuntando a la dirección real — **bloqueado**: la dirección física de la sede
      Asunción no aparece en ningún lado del WordPress viejo (confirmado en
      `docs/01-analisis-descubrimiento.md` pregunta #19), sigue pendiente de que el cliente la
      entregue. No se puede verificar un mapa que todavía no tiene dirección real cargada
- [x] Fechas y años actualizados — el pie usa `{{ now()->year }}` (dinámico, no hardcodeado),
      verificado que muestra el año real en tiempo de ejecución
- [x] Nombre de la institución escrito igual en todo el sitio — verificado "Dante Alighieri"
      consistente en el pie de página

### Hallazgo real, documentado (no corregido — es una decisión de contenido, no un bug)

**JSON-LD `EducationalOrganization` sin teléfono ni dirección** — `resources/views/components/
schema/organization.blade.php` arma el schema.org desde `SiteSetting::get('contact_phone')` y
`SiteSetting::get('address_asuncion')`, y omite el campo si está vacío (diseño correcto: no
escribe cadenas vacías). Pero esas dos claves de `site_settings` están **vacías** en la base
actual, mientras que `/contacto` y el pie de página sí muestran teléfonos reales (hardcodeados
directo en las vistas, no leídos de `SiteSetting`). Resultado: el structured data que lee
Google no tiene teléfono ni dirección, aunque el usuario humano sí los ve en la página.
**No se corrige en esta sesión** porque implica una decisión de negocio (¿cuál de los 3
teléfonos por sede/área es "el" teléfono oficial de structured data?) y porque la dirección
sigue bloqueada por la pregunta #19 ya abierta. Recomendación: cuando el cliente confirme
dirección y teléfono principal, cargarlos en **Panel → Configuración global** (`contact_phone`,
`address_asuncion`) para que el JSON-LD deje de omitirlos.

```bash
npx playwright test e2e/content-verification.spec.ts --project="Desktop Chrome"
```

---

## 6. Formularios de punta a punta

| Formulario | Llega el mail | Se guarda el registro | Captcha bloquea | Honeypot funciona | Validación clara | Rate limit |
|---|---|---|---|---|---|---|
| Contacto | ✅ (`Notification::fake`, `PublicFormsTest.php`) | ✅ | ✅ (`TurnstileTest.php`, 4 escenarios) | ✅ | ✅ | ✅ (5/hora, `throttle:5,60,forms`) |
| Pre-inscripción | ✅ (nuevo, `PreRegistrationFormTest.php`) | ✅ | ✅ (mismo `Turnstile` rule, comparte middleware) | ✅ (nuevo) | ✅ (nuevo, incluye sede inválida) | ✅ (nuevo, 5/hora) |

Cómo se prueba sin depender de un buzón real: `Notification::fake()` en Pest verifica que
`NewFormSubmissionNotification` se despacha (nunca sale un mail de verdad en la suite), y los
E2E de Playwright (`critical-tasks.spec.ts`, Tareas 1 y 3) verifican el mensaje de éxito en
pantalla sin depender de recibir nada en un buzón.

Probado también, con datos de prueba:
- [x] Envío desde móvil — cubierto por los proyectos "Mobile Chrome"/"Mobile Safari" de
      Playwright en `critical-tasks.spec.ts`
- [x] Envío con campos vacíos — `PublicFormsTest.php`, `PreRegistrationFormTest.php` y el E2E
      de validación nativa del navegador en Contacto
- [x] Envío con caracteres especiales y tildes — el contenido de prueba real usa tildes
      (`inscribí`, `contactó`) sin fallar la validación ni la sanitización
- [x] Envío repetido (rate limit) — cubierto para ambos formularios (5/hora cada uno, con
      contadores separados desde el hallazgo de Fase 9 documentado abajo)

### Hallazgo real de esta fase (encontrado por Playwright, corregido)

`spatie/laravel-honeypot` (`amount_of_seconds => 1`) descarta en silencio cualquier envío que
llegue antes de 1 segundo desde que se cargó el formulario. Los primeros E2E, que llenaban el
formulario con `.fill()` (casi instantáneo) y enviaban en el acto, quedaban por debajo de ese
segundo y el honeypot los trataba como bot — aunque fueran un envío legítimo de prueba.
Documentado en `e2e/helpers.ts` (`waitPastHoneypotThreshold`) y como **riesgo real para
usuarios humanos con autocompletado del navegador**, que también puede llenar y enviar un
formulario en menos de un segundo. **Recomendación pendiente para una fase posterior:** subir
`amount_of_seconds` a un valor más realista (2–3 s) o agregar una señal adicional (ej. tiempo
mínimo de interacción con el mouse/teclado) para no penalizar a usuarios rápidos legítimos —
no se cambia en esta sesión porque es una decisión de producto (cuánta fricción antibot vs.
falsos positivos), no un bug de QA.

---

## 7. Prueba de aceptación con el cliente

**Entorno:** staging, protegido por contraseña
**Fecha:** —
**Participantes:** —

**No hecha en esta sesión — pendiente explícito, no simulado.** No hay cliente presente ni
staging desplegado todavía (el staging real es tarea de la Fase 10, `docs/12-deploy-plesk.md`).
No se inventa una aprobación: esta lista queda para correrla con el cliente real sobre el
staging real.

Lista de verificación que recorre el cliente:

- [ ] Encuentra la información que buscaría un padre nuevo
- [ ] Crea una noticia y la publica solo
- [ ] Edita el texto de una página institucional
- [ ] Sube una imagen a la galería
- [ ] Cambia un elemento del menú
- [ ] Ve los envíos de un formulario
- [ ] El sitio se ve bien en su propio celular
- [ ] El contenido está completo y correcto
- [ ] El diseño coincide con lo aprobado

**Aprobado:** ☐ sí ☐ con observaciones ☐ no
**Observaciones:** —

---

## 8. Registro de defectos

| # | Descripción | Severidad | Detectado en | Estado | Cerrado el |
|---|---|---|---|---|---|
| 1 | Contraste 1.27:1 (necesita 3:1) en el "404" decorativo de la página de error | menor | axe (`e2e/accessibility.spec.ts`) | ✅ cerrado — `--color-neutral-600` | 2026-08-25 |
| 2 | El skip link "Saltar al contenido principal" no movía el foco (`<main>` sin `tabindex`) | mayor | E2E de teclado (`e2e/accessibility.spec.ts`) | ✅ cerrado — `tabindex="-1"` en los 13 `<main id="contenido">` | 2026-08-25 |
| 3 | El menú móvil cerrado seguía en el tab order y en el árbol de accesibilidad (solo se desplazaba con `transform`) | mayor | E2E de teclado (`e2e/accessibility.spec.ts`) | ✅ cerrado — `:inert="!mobileOpen"` | 2026-08-25 |
| 4 | Sin cobertura Pest del formulario de pre-inscripción (mail, honeypot, rate limit, validación) — solo cubierto por un E2E de camino feliz | mayor (hueco de test, no de producto) | revisión de cobertura §1 | ✅ cerrado — `tests/Feature/PreRegistrationFormTest.php` (5 casos nuevos) | 2026-08-25 |
| 5 | Faltaban pruebas de "un rol sin permiso no ve el listado" para Documentos/Galerías/Redirecciones, y de "un rol con permiso de ver/editar pero no de borrar, no puede borrar" en varios recursos | mayor (hueco de test, no de producto) | revisión de cobertura §1 (siguiendo la instrucción de `docs/10-seguridad.md` de probar la autorización real, no el botón oculto) | ✅ cerrado — 8 tests nuevos repartidos en `DocumentResourceTest.php`, `GalleryResourceTest.php`, `RedirectResourceTest.php`, `PageResourceTest.php`, `AnnouncementResourceTest.php`, `CalendarEventResourceTest.php` | 2026-08-25 |
| 6 | El JSON-LD `EducationalOrganization` no tiene teléfono ni dirección (`SiteSetting` vacío) aunque la página sí los muestra (hardcodeados en la vista) | menor | rastreo de contenido §5 | ⚠️ abierto — decisión de negocio (qué teléfono es "el oficial") + bloqueado por la dirección física pendiente (pregunta #19) | — |
| 7 | El honeypot (`amount_of_seconds => 1`) descartaba el envío **con una página en blanco, sin ningún mensaje** — un humano real que lo disparara por error (autocompletado rápido) se quedaba sin ninguna pista de qué pasó | menor→corregido | E2E de formularios (`e2e/helpers.ts`) | ✅ cerrado — `App\Http\Responses\HoneypotRedirectResponder` vuelve al formulario con un mensaje claro; test en `PublicFormsTest.php` | 2026-08-25 |
| 8 | El 2FA obligatorio del panel (TOTP) estaba estructuralmente excluido de la suite de Pest — no existía un test automatizado que comprobara que el desafío realmente bloqueaba el acceso | mayor→corregido | revisión de cobertura de autenticación §1 | ✅ cerrado — reemplazado por 2FA de email opt-in (ADR-003, pedido del cliente), que sí se puede probar sin desactivar ninguna protección real; `EmailTwoFactorAuthenticationTest.php` (4 casos: alerta persistente, sin forzar, activación real con código de la cola de mail, código inválido/reusado rechazado) | 2026-08-25 |
| 9 | Sin CI configurado — nada corría Pint/Larastan/Pest en cada push | mayor | revisión de checklist §9 | ✅ cerrado — `.github/workflows/ci.yml` (Pint, Larastan, Pest en cada push/PR a `main`) | 2026-08-25 |

**Regla:** cero bloqueantes y cero mayores **abiertos** para pasar a la Fase 10. **Estado
real:** los defectos #4, #5, #7, #8 y #9 (mayores) están cerrados. Queda **uno menor abierto,
no bloqueante**: #6 (teléfono/dirección del JSON-LD, depende de una decisión de negocio y de
la dirección física pendiente — pregunta #19).

---

## 9. CI

- [x] GitHub Actions corriendo Pint, Larastan y Pest en cada push/PR a `main` —
      `.github/workflows/ci.yml` (nuevo en esta fase): PHP 8.3, `composer install`, `npm ci` +
      build de assets, `pint --test`, `phpstan analyse` (nivel 5), `pest --ci`. Todos en verde
      contra el estado actual del repo
- [ ] La rama `main` no acepta merge con la suite en rojo — el workflow ya existe y corre en
      cada push/PR; falta activar la regla de protección de rama en GitHub (Settings →
      Branches → Require status checks) — es una configuración del repositorio remoto, no de
      código, y requiere acceso de administrador al repo que no se ejerce automáticamente en
      esta sesión. Recomendado activarla antes del cutover de la Fase 10

```bash
# Local, mismos pasos que corre CI:
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pest
```

---

## 10. Cierre de la Fase 9

**Suite en verde:** ✅ Pest 192/192 · Playwright 65 pasados / 95 saltados por diseño (axe y
rastreo de contenido corren una sola vez, no por motor de navegador) / 0 fallos · Pint sin
cambios pendientes · Larastan (nivel 5) 0 errores.

**Cero defectos bloqueantes abiertos:** ✅ — no se encontró ningún defecto bloqueante.
**Cero defectos mayores abiertos:** ✅ — los 5 mayores encontrados (#4, #5, #7, #8, #9) están
cerrados. Queda 1 defecto **menor** abierto (#6), no bloqueante.

**Cliente aprobó en staging:** ❌ **no hecho** — no hay staging real ni cliente presente en
esta sesión (Fase 10). No se simula.

**Luz verde para producción:** **pendiente de decisión del usuario.** Esta fase deja el
checklist de `PLAN.md` tildado con el trabajo real hecho, pero **no cierra la Fase 9** — eso
es una decisión explícita de quien conduce el proyecto, no de esta sesión de QA.
