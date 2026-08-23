# 08 — SEO técnico e integraciones (Fase 6)

Estado: **vacío — completar en Fase 6**

---

## 1. SEO en página

| Elemento | Implementación | Editable desde el panel | Estado |
|---|---|---|---|
| `<title>` | por defecto: `{título} · {nombre institución}` | sí | ☐ |
| `meta description` | | sí | ☐ |
| `link rel=canonical` | autogenerado, sobreescribible | sí | ☐ |
| `meta robots` | index/noindex por página | sí | ☐ |
| Un solo `h1` por página | | — | ☐ |
| Jerarquía de encabezados sin saltos | | — | ☐ |
| URLs limpias, en español, sin fechas ni IDs | | slug editable | ☐ |
| Enlaces internos con texto descriptivo | | — | ☐ |
| Alt en todas las imágenes | | sí, obligatorio | ☐ |
| Migas de pan visibles + marcadas | | — | ☐ |

---

## 2. Open Graph y Twitter Cards

| Etiqueta | Valor por defecto | Sobreescribible |
|---|---|---|
| `og:title` | seo_title o title | sí |
| `og:description` | seo_description | sí |
| `og:image` | imagen OG de la página → portada → imagen por defecto de la marca | sí |
| `og:type` | `website` / `article` | auto |
| `og:url` | canonical | auto |
| `og:locale` | `es_PY` | |
| `og:site_name` | | |
| `twitter:card` | `summary_large_image` | |

Imagen OG por defecto: 1200×630, con el logotipo. Diseñarla en la Fase 2.

Verificar con el depurador de Facebook y el validador de tarjetas de X.

---

## 3. Datos estructurados (JSON-LD)

| Tipo | Dónde | Estado |
|---|---|---|
| `EducationalOrganization` | todas las páginas (en el layout) — nombre, logo, dirección, teléfono, redes | ☐ |
| `BreadcrumbList` | todas las páginas internas | ☐ |
| `Article` / `NewsArticle` | detalle de noticia | ☐ |
| `FAQPage` | páginas con bloque de acordeón/FAQ | ☐ |
| `WebSite` + `SearchAction` | inicio | ☐ |
| `Event` | si hay calendario de eventos | ☐ |

Validar todo con Rich Results Test y el validador de schema.org. **Sin errores ni advertencias.**

---

## 4. Sitemap y robots

- [ ] `sitemap.xml` dinámico, con `lastmod` real
- [ ] Excluye borradores, `noindex`, resultados de búsqueda y páginas de sistema
- [ ] Si supera 50.000 URLs, índice de sitemaps (improbable acá)
- [ ] `robots.txt` con la referencia al sitemap
- [ ] `robots.txt` de **staging** bloquea todo (`Disallow: /`) — y se verifica que el de
      producción no quedó con eso

---

## 5. Redirecciones

Ver `docs/07-migracion-wordpress.md` §7. Verificación final acá:

- [ ] Las 20 URLs con más tráfico de la línea base redirigen correctamente
- [ ] www / no-www unificado con 301
- [ ] http → https con 301
- [ ] Sin barra final duplicando URLs

---

## 6. Integraciones

### Google Analytics 4 / Tag Manager

| Item | Valor | Estado |
|---|---|---|
| ID de GTM | configurable desde el panel | ☐ |
| ID de GA4 | | ☐ |
| Se carga **solo tras consentimiento** | | ☐ |
| Eventos personalizados: envío de formulario, clic en teléfono, clic en WhatsApp, descarga de documento | | ☐ |
| Conversiones definidas | | ☐ |

### Meta Pixel + Conversions API

| Item | Estado |
|---|---|
| Pixel del lado cliente, tras consentimiento | ☐ |
| **Conversions API del lado servidor** — es lo que sobrevive a los bloqueadores | ☐ |
| Deduplicación por `event_id` entre cliente y servidor | ☐ |
| Eventos: `PageView`, `Lead` (envío de formulario), `Contact` | ☐ |
| Token y Pixel ID en `.env`, no en el repo | ☐ |

### Captcha

**Elección:** Cloudflare Turnstile
**Motivo:** gratis, sin resolver rompecabezas, mejor tasa de conversión que reCAPTCHA, y no
alimenta a Google con datos de los visitantes. Alternativa si el cliente lo exige: reCAPTCHA v3.

- [ ] En todos los formularios públicos
- [ ] Validación **del lado servidor** (el cliente se puede saltar)
- [ ] Sumado a honeypot y rate limit — el captcha solo no alcanza
- [ ] Mensaje de error claro si falla

### Consentimiento de cookies

- [ ] Banner que **bloquea efectivamente** los scripts de terceros hasta aceptar
- [ ] Opciones: aceptar todo / rechazar todo / configurar
- [ ] Rechazar es tan fácil como aceptar (un clic)
- [ ] Preferencia persistida y revocable desde el pie
- [ ] Enlace a la política de cookies

> Un banner que carga el pixel igual antes de aceptar no sirve de nada y es peor que no tenerlo.

### Otras

| Integración | Estado |
|---|---|
| WhatsApp flotante | ☐ |
| Google Maps (embebido diferido, no bloqueante) | ☐ |
| Redes sociales del pie | ☐ |
| Correo transaccional (SMTP + SPF/DKIM del dominio) | ☐ |

---

## 7. Alta en herramientas

- [ ] Google Search Console — propiedad verificada, sitemap enviado
- [ ] Bing Webmaster Tools
- [ ] Google Business Profile verificado y enlazado desde el sitio
- [ ] Accesos entregados al cliente

---

## 8. Verificación final

| Prueba | Resultado |
|---|---|
| Rich Results Test sin errores | ☐ |
| Sitemap accesible y válido | ☐ |
| `robots.txt` correcto en producción | ☐ |
| Las 20 URLs top redirigen bien | ☐ |
| GA4 recibiendo datos | ☐ |
| Meta CAPI recibiendo eventos | ☐ |
| Captcha bloqueando envíos automatizados | ☐ |
| Banner de cookies bloqueando scripts antes de aceptar | ☐ |
| Vista previa OG correcta en Facebook, WhatsApp y LinkedIn | ☐ |

---

## 9. Monitoreo post-lanzamiento (90 días)

| Semana | 404 en Search Console | Sesiones orgánicas | Posición promedio | Nota |
|---|---|---|---|---|
| 1 | | | | |
| 2 | | | | |
| 4 | | | | |
| 8 | | | | |
| 12 | | | | |

> Es normal una caída del 10–20 % las primeras 2–4 semanas tras una migración. Si a las 8
> semanas no recuperó, hay un problema real: revisar redirecciones e indexación.
