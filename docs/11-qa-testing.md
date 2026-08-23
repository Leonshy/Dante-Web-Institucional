# 11 — QA y testing (Fase 9)

Estado: **vacío — completar en Fase 9**
Herramienta: `playwright-cli` — ⚠️ **solo credenciales de prueba, nunca las reales del cliente**

---

## 1. Tests automatizados (Pest)

| Suite | Casos | Estado |
|---|---|---|
| Autenticación del panel | login correcto/incorrecto, 2FA, bloqueo por intentos, logout | ☐ |
| Permisos por rol | editor no puede borrar, no ve usuarios, no cambia configuración | ☐ |
| CRUD de páginas | crear, editar, publicar, despublicar, borrar, jerarquía, slug único | ☐ |
| CRUD de noticias | + categorías, destacados, orden por fecha | ☐ |
| Bloques de contenido | agregar, reordenar, eliminar, renderizado | ☐ |
| Medios | subida válida, tipo inválido rechazado, tamaño excesivo rechazado, conversiones | ☐ |
| Menús | CRUD, jerarquía, orden | ☐ |
| Formularios | envío válido, validación, honeypot, captcha, rate limit, mail enviado | ☐ |
| Redirecciones | 301 al destino correcto, sin bucles | ☐ |
| Sanitización HTML | payload con `<script>` y `onerror=` queda limpio | ☐ |
| Rutas públicas | 200 en todas las plantillas, 404 correcto | ☐ |
| SEO | meta, canonical, JSON-LD presentes y correctos | ☐ |
| Sitemap | válido, excluye borradores y noindex | ☐ |
| Auditoría | cada cambio queda registrado con usuario y fecha | ☐ |

**Cobertura:** 100 % de las rutas del panel tocadas por al menos un test.

```bash
php artisan test          # o vendor/bin/pest
vendor/bin/pest --coverage
```

---

## 2. Tests E2E (Playwright)

Las 5 tareas críticas de `docs/02-ux-arquitectura-informacion.md` §6, en móvil y escritorio.

| # | Recorrido | Móvil | Escritorio |
|---|---|---|---|
| 1 | | ☐ | ☐ |
| 2 | | ☐ | ☐ |
| 3 | | ☐ | ☐ |
| 4 | | ☐ | ☐ |
| 5 | | ☐ | ☐ |
| 6 | Flujo completo del panel: login → crear página → publicar → verla en el sitio | ☐ | ☐ |

⚠️ Los E2E corren contra **staging con datos de prueba**. Nunca contra producción ni con
credenciales reales del cliente.

---

## 3. Accesibilidad

| Plantilla | axe sin violaciones | Teclado completo | Lector de pantalla |
|---|---|---|---|
| Inicio | ☐ | ☐ | ☐ |
| Institucional | ☐ | ☐ | ☐ |
| Noticias | ☐ | ☐ | ☐ |
| Detalle de noticia | ☐ | ☐ | ☐ |
| Contacto | ☐ | ☐ | ☐ |
| Búsqueda | ☐ | ☐ | ☐ |
| 404 | ☐ | ☐ | ☐ |
| Panel admin | ☐ | ☐ | — |

**Objetivo:** WCAG 2.1 AA.

Pruebas manuales que axe no detecta:
- [ ] Recorrer todo el sitio solo con teclado, sin mouse
- [ ] El foco siempre visible y en orden lógico
- [ ] El menú móvil se abre, se navega y se cierra con `Esc`
- [ ] Los mensajes de error se anuncian
- [ ] Probar una página con lector de pantalla (VoiceOver o NVDA)
- [ ] Zoom al 200 % sin pérdida de contenido ni scroll horizontal

---

## 4. Compatibilidad

| Entorno | Probado | Problemas |
|---|---|---|
| Chrome escritorio (últimas 2) | ☐ | |
| Firefox escritorio | ☐ | |
| Safari escritorio | ☐ | |
| Edge | ☐ | |
| iOS Safari — dispositivo real | ☐ | |
| Chrome Android — dispositivo real | ☐ | |
| Pantalla pequeña (320 px) | ☐ | |
| Pantalla grande (2560 px) | ☐ | |

---

## 5. Verificación de contenido

- [ ] Cero enlaces rotos (`dante:check-links` + rastreo con Screaming Frog o similar)
- [ ] Cero imágenes faltantes
- [ ] Revisión ortográfica de todo el sitio
- [ ] Datos de contacto correctos: teléfonos, dirección, mails, horarios
- [ ] Enlaces a redes sociales correctos
- [ ] Mapa apuntando a la dirección real
- [ ] Fechas y años actualizados (el pie con el año dinámico)
- [ ] Nombre de la institución escrito igual en todo el sitio

---

## 6. Formularios de punta a punta

| Formulario | Llega el mail | Se guarda el registro | Captcha bloquea | Honeypot funciona | Validación clara |
|---|---|---|---|---|---|
| | ☐ | ☐ | ☐ | ☐ | ☐ |

Probar también: envío desde móvil, envío con campos vacíos, envío con caracteres especiales
y tildes, envío repetido (rate limit).

---

## 7. Prueba de aceptación con el cliente

**Entorno:** staging, protegido por contraseña
**Fecha:**
**Participantes:**

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
**Observaciones:**

---

## 8. Registro de defectos

| # | Descripción | Severidad | Detectado en | Estado | Cerrado el |
|---|---|---|---|---|---|
| | | bloqueante / mayor / menor / cosmético | | | |

**Regla:** cero bloqueantes y cero mayores abiertos para pasar a la Fase 10.

---

## 9. CI

- [ ] GitHub Actions (u otro) corriendo Pint, Larastan y Pest en cada push
- [ ] La rama `main` no acepta merge con la suite en rojo

---

## 10. Cierre de la Fase 9

**Suite en verde:** ☐
**Cero defectos bloqueantes:** ☐
**Cliente aprobó en staging:** ☐
**Luz verde para producción:** ☐
