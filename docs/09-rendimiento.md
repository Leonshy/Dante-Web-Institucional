# 09 — Rendimiento (Fase 7)

Estado: **vacío — completar en Fase 7**

---

## 1. Objetivos

| Métrica | Objetivo | Home | Institucional | Noticia |
|---|---|---|---|---|
| LCP | < 2.5 s | | | |
| INP | < 200 ms | | | |
| CLS | < 0.1 | | | |
| TTFB | < 600 ms | | | |
| Lighthouse móvil | ≥ 90 | | | |
| Lighthouse escritorio | ≥ 95 | | | |
| Peso total de la página | < 1 MB | | | |
| Peticiones | < 50 | | | |

**Condición de medición:** Lighthouse móvil, 4G simulada, CPU 4× lenta. Medir así, no en
escritorio con fibra — el usuario real de este sitio es un padre con un celular de gama media.

---

## 2. Línea base (antes de optimizar)

Fecha: · Herramienta: PageSpeed Insights

| Página | LCP | INP | CLS | Lighthouse | Peso |
|---|---|---|---|---|---|
| | | | | | |

**Comparación con el WordPress viejo** (de `docs/01` §C.5) — sirve para mostrarle al cliente
qué compró:

| | WordPress viejo | Laravel nuevo |
|---|---|---|
| LCP móvil | | |
| Lighthouse móvil | | |
| Peso | | |

---

## 3. Imágenes

- [ ] Conversión automática a WebP y AVIF al subir
- [ ] Variantes responsivas generadas (320, 640, 960, 1280, 1920)
- [ ] `srcset` + `sizes` correctos (no servir 1920 px a un móvil)
- [ ] `loading="lazy"` en todo menos el LCP
- [ ] `fetchpriority="high"` + `<link rel="preload">` en la imagen del LCP
- [ ] `width`/`height` explícitos en todas
- [ ] Aviso en el panel si el cliente sube una imagen desproporcionada
- [ ] Compresión con calidad 80–85 (indistinguible y mucho más liviana)

---

## 4. Fuentes

- [ ] Autoalojadas — nada de Google Fonts en tiempo de ejecución (una petición externa más y
      un problema de privacidad)
- [ ] `woff2` únicamente
- [ ] Subconjunto latino
- [ ] `font-display: swap`
- [ ] Precarga de la fuente que usa el `h1`
- [ ] Máximo 2 familias, máximo 4 pesos en total

---

## 5. CSS y JavaScript

- [ ] Purga de Tailwind activa en producción
- [ ] CSS crítico en línea si mueve la aguja del LCP
- [ ] JS diferido salvo lo imprescindible
- [ ] División de código: el JS de una página no se carga en las demás
- [ ] Sin librerías duplicadas
- [ ] Terceros (mapas, videos, widgets) cargados bajo demanda, no al inicio
- [ ] Presupuesto: **< 150 KB de JS comprimido** en la carga inicial

| Recurso | Peso comprimido | ¿Justificado? |
|---|---|---|

---

## 6. Backend

- [ ] Caché de respuesta de páginas públicas, invalidada al publicar
- [ ] `config:cache`, `route:cache`, `view:cache`, `event:cache` en producción
- [ ] Autoload optimizado: `composer install --optimize-autoloader --no-dev`
- [ ] Cero consultas N+1 — `Model::preventLazyLoading()` en desarrollo
- [ ] Índices en toda columna que se filtra, ordena o une
- [ ] Consultas pesadas cacheadas
- [ ] OPcache activo en el Plesk
- [ ] Sin trabajos pesados en el ciclo de la petición: van a cola

**Consultas más lentas detectadas:**

| Consulta | Antes | Después | Cómo se arregló |
|---|---|---|---|

---

## 7. Servidor / Plesk

- [ ] Compresión Brotli (o Gzip como mínimo)
- [ ] HTTP/2 o HTTP/3
- [ ] Cabeceras de caché en estáticos: `Cache-Control: public, max-age=31536000, immutable`
- [ ] Versionado de assets por hash (Vite ya lo hace)
- [ ] Keep-alive activo
- [ ] ¿CDN? — evaluar. **Ojo:** el diferencial de webparaguay es el servidor local; una CDN
      mal elegida puede empeorar la latencia para Paraguay en vez de mejorarla. Medir antes de
      decidir

---

## 8. Resultado final

Fecha: 

| Página | LCP | INP | CLS | Lighthouse móvil | Peso | ¿Cumple? |
|---|---|---|---|---|---|---|
| Inicio | | | | | | ☐ |
| Institucional | | | | | | ☐ |
| Noticia | | | | | | ☐ |
| Contacto | | | | | | ☐ |

**Capturas de PageSpeed Insights guardadas en:**

---

## 9. Monitoreo continuo

- [ ] Core Web Vitals en Search Console revisados mensualmente
- [ ] Alerta si el Lighthouse cae por debajo de 85
