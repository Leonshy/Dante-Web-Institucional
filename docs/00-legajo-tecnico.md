# Legajo Técnico — Web Institucional Dante

> Formato estándar de webparaguay. Las secciones marcadas **(a completar en Fase 0)** se
> llenan con datos reales una vez leídos los tres insumos. Lo demás ya está definido.

---

## 0. Ficha del proyecto

| Campo | Valor |
|---|---|
| **Nombre** | Web Institucional Dante — reemplazo de WordPress por Laravel |
| **Cliente** | Dante (`dante.edu.py`) — institución educativa, Paraguay |
| **Estado** | Fase 0 — descubrimiento |
| **Responsable** | Leonardo Chi (webparaguay) |
| **Equipo** | 2 desarrolladores senior + 1 junior avanzado |
| **Versión del legajo** | 1.0 |
| **Fecha** | Agosto 2026 |
| **Tipo** | Proyecto de cliente (no producto interno) |

---

## 1. Resumen ejecutivo

Dante tiene hoy un sitio institucional en WordPress que **estuvo comprometido**. Más allá del
incidente de seguridad, el sitio arrastra los problemas típicos de un WordPress con años y
plugins acumulados: estructura de contenido que creció sin criterio, rendimiento pobre en
móvil, dependencia de un ecosistema de plugins que hay que mantener actualizado, y un panel
de administración que el cliente usa a medias porque nunca fue diseñado para él.

Este proyecto reemplaza ese sitio por una **web institucional nueva en Laravel**, con panel de
administración propio, construida sobre un patrón que webparaguay ya tiene funcionando en
producción (el proyecto IGP). El contenido actual se migra completo, pero **reorganizado según
una arquitectura de información diseñada desde las tareas reales de los usuarios**, no heredada
de cómo quedó el WordPress. El diseño sigue el manual de marca de Dante, con estilos propios y
motion cuidado.

**Por qué ahora:** el compromiso del WordPress fuerza una decisión que igual había que tomar.
Restaurar el WordPress deja al cliente en la misma superficie de ataque y con el mismo sitio
mediocre. Reconstruir en Laravel elimina la superficie de plugins de terceros, da control total
sobre el rendimiento y la seguridad, y deja al cliente en la plataforma que webparaguay
mantiene todos los días. Además, el patrón de IGP hace que el costo de ingeniería sea una
fracción de lo que sería empezar de cero.

---

## 2. Problema y oportunidad

### El dolor concreto

- **Seguridad:** WordPress comprometido. La superficie de ataque (núcleo + tema + N plugins de
  terceros) es imposible de asegurar de forma sostenida con un equipo chico.
- **Contenido desordenado:** años de páginas agregadas sin arquitectura. Los usuarios no
  encuentran lo que buscan; el colegio recibe por teléfono preguntas que la web debería responder.
- **Rendimiento:** el tráfico institucional educativo es mayoritariamente móvil, y un WordPress
  con plugins y tema pesado rinde mal ahí.
- **Autoadministración a medias:** el cliente depende del proveedor para cambios que debería
  poder hacer solo.
- **Dependencia de mantenimiento:** cada actualización de plugin es un riesgo de romper el sitio.

### Quién paga y por qué

Paga la institución. La compra se justifica por tres cosas, en este orden: recuperar el sitio
después del incidente, no volver a pasar por lo mismo, y tener finalmente una web que refleje
la marca y que puedan administrar solos.

### Oportunidad para webparaguay

Este proyecto es la **segunda instancia del patrón IGP**. Cada institución que se hace con este
patrón baja el costo marginal de la siguiente. El objetivo secundario, explícito, es que al
terminar Dante quede un **patrón reutilizable de web institucional educativa autoadministrable**,
listo para vender al resto del mercado de colegios y universidades privadas de Paraguay — un
segmento con presupuesto, con necesidad recurrente y donde el "servidores en Paraguay + atención
cercana" pesa.

---

## 3. Visión y propuesta de valor

**Una web institucional que el colegio administra solo, que carga rápido en el celular de un
padre en Asunción, y que no depende de treinta plugins de terceros para existir.**

Apoyo en los diferenciadores de webparaguay:

| Diferenciador | Cómo se aprovecha |
|---|---|
| **Servidores locales en Paraguay** | Latencia baja para el 100 % del público objetivo. Datos de la institución educativa en el país. Argumento fuerte para una institución con datos de menores |
| **Atención cercana** | Capacitación presencial del panel, acuerdo de mantenimiento, respuesta local ante incidentes. Después de un compromiso, el cliente valora tener a quién llamar |
| **IA en el desarrollo** | El stack de herramientas de IA (impeccable, ux-flow-designer, emil-design-eng, strix, playwright) permite entregar calidad de agencia grande con un equipo de tres. Es lo que hace rentable el proyecto |
| **Stack propio y probado** | Laravel es lo que el equipo mantiene todos los días. No hay curva de aprendizaje ni dependencia de un tema de terceros |

Contra la alternativa (restaurar el WordPress), la propuesta se sostiene en: superficie de
ataque drásticamente menor, rendimiento controlable, y un panel diseñado para ese cliente en
particular en lugar de un `wp-admin` genérico.

---

## 4. Alcance por etapas (columna vertebral)

> Esta sección es el punto de encuentro entre lo técnico y lo financiero.
> El detalle operativo de cada fase está en `PLAN.md`.

### MVP — Sitio institucional en producción (fases 0 a 10)

**Entra:**

- Análisis, arquitectura de información nueva y wireframes
- Copywriting completo del sitio, con textos reales
- Sistema de diseño derivado del manual de marca + diseño de alta fidelidad de cada plantilla
- Backend Laravel 13 con panel de administración autoadministrable completo (**Filament 5**,
  ver ADR-001): páginas con constructor de bloques, noticias, categorías, medios, menús,
  configuración global, formularios, **4 roles de usuario** (administrador, editor general,
  editor de noticias/banners + marketing, editor académico — ver pregunta abierta #2),
  redirecciones, campos SEO por página, registro de auditoría
- **Sitio multiidioma español/italiano con toggle de habilitación** (ver ADR-002 — cambio de
  alcance agregado en Fase 0, no estaba en la cotización original)
- Frontend Blade + Livewire, responsivo, con motion
- Migración completa del contenido del WordPress
- SEO técnico + redirecciones 301 + schema.org + sitemap
- Integraciones: GA4/GTM, Meta Pixel + Conversions API, captcha, banner de cookies
- Rendimiento con Core Web Vitals en verde
- Endurecimiento de seguridad + pentest con strix + respaldos automáticos probados
- QA: suite Pest + E2E Playwright + accesibilidad AA
- Despliegue en Plesk, cutover sin tocar el correo, manual del cliente y capacitación

**NO entra en el MVP:**

- Área privada de alumnos, padres o docentes
- Inscripciones o matriculación en línea (el sitio actual tampoco las tiene — la inscripción es
  presencial con documentación física, ver `docs/01-analisis-descubrimiento.md` §E pregunta #3)
- Pasarela de pagos
- Integración con sistema de gestión académica
- Blog con comentarios
- Newsletter con gestión de suscriptores

### v1 — Portal privado (fase posterior, no cotizada acá)

Área autenticada para padres y alumnos: comunicados dirigidos, calendario personal, boletines,
descarga de documentos. El modelo de datos del MVP se diseña para no cerrarle la puerta, pero
no se construye nada de esto ahora.

### v2 — Gestión académica conectada

Integración con el sistema de gestión del colegio, inscripciones en línea, pagos vía Bancard
o Pagopar. Solo si el cliente lo pide y con su propio legajo.

### Producto derivado (interno de webparaguay)

Extracción del patrón "web institucional educativa" como base reutilizable, para reducir el
costo del próximo colegio. **Este es el retorno estratégico del proyecto** y se documenta al
cerrar la Fase 10.

---

## 5. Especificación técnica

> Detalle completo indexado por fase en `docs/05-backend-modelo-datos.md` y `docs/06-frontend.md`.
> Resumen acá.

### Arquitectura

Monolito Laravel clásico, renderizado en servidor con Blade e islas de interactividad con
Livewire. **No hay SPA ni API separada** — para un sitio institucional sería complejidad sin
retorno, y perjudicaría el SEO y el tiempo de primera pintura.

```
Navegador
   │
   ├── Sitio público (Blade + Livewire 4 + Alpine + Tailwind)
   │      └── caché de respuesta, invalidada al publicar
   │
   └── Panel admin (/[ruta-no-adivinable])
          └── Fortify + 2FA + roles + auditoría
                 │
          Laravel 13 (PHP 8.3+)
                 │
          ┌──────┴──────┐
       MySQL 8      Almacenamiento local (medios)
                 │
          Base legacy de solo lectura (solo durante la migración)
```

### Stack

Ver `CLAUDE.md` §3 — es la fuente de verdad del stack y no se duplica acá.

### Componentes del panel

| Componente | Descripción |
|---|---|
| Páginas | Jerárquicas, con constructor de bloques de contenido |
| Bloques | Secciones reutilizables (hero, texto, galería, CTA, acordeón, tarjetas, video, mapa) |
| Noticias | Con categorías, destacados, imagen de portada, fecha de publicación |
| Medios | Biblioteca central, conversiones automáticas, alt text obligatorio |
| Menús | Arrastrar y soltar, jerárquicos, múltiples ubicaciones |
| SEO | Por página: título, descripción, OG, canonical, índice/noindex |
| Formularios | Definición, envíos almacenados, notificación por mail, exportación CSV |
| Redirecciones | Mapa 301 editable |
| Configuración | Datos de contacto, redes, horarios, IDs de analytics, textos globales |
| Usuarios | Roles admin/editor, 2FA obligatorio |
| Auditoría | Historial de cambios: quién, qué, cuándo |

### Integraciones

| Integración | Implementación |
|---|---|
| Google Analytics 4 | Vía GTM, ID configurable desde el panel, bloqueado hasta consentimiento |
| Meta Pixel | Cliente + **Conversions API** del lado servidor |
| Captcha | Cloudflare Turnstile (preferido) o reCAPTCHA v3 |
| Correo saliente | SMTP del servidor. **El correo del dominio queda en HostGator** |
| Search Console / Bing | Verificación por metaetiqueta, envío de sitemap |
| Respaldos | `spatie/laravel-backup` a almacenamiento externo |

### Requerimientos no funcionales

| Requerimiento | Objetivo |
|---|---|
| Rendimiento | LCP < 2.5 s, INP < 200 ms, CLS < 0.1, Lighthouse móvil ≥ 90 |
| Accesibilidad | WCAG 2.1 nivel AA |
| Seguridad | Cero hallazgos Críticos/Altos de strix antes de producción |
| Disponibilidad | 99.5 % mensual |
| Respaldo | Diario, retención 30 días, restauración probada |
| Navegadores | Últimas 2 versiones de Chrome, Firefox, Safari, Edge + iOS/Android |
| Idioma | Español paraguayo (confirmar italiano en Fase 0) |

---

## 6. Modelo de negocio y monetización

Proyecto de cliente, precio cerrado. Dos componentes:

**a) Desarrollo (pago único, por etapas)**

Cotización derivada de la sección 7. Estructura de pago sugerida: 40 % al inicio, 30 % al
aprobar el diseño (cierre de Fase 2), 30 % contra puesta en producción.

**b) Recurrente mensual**

- **Hosting** en servidor Plesk de webparaguay (plan según tráfico y almacenamiento)
- **Mantenimiento**: actualizaciones de dependencias, monitoreo, respaldos verificados,
  bolsa de horas de soporte y cambios menores

El recurrente es lo que hace bueno al proyecto en el mediano plazo. **Un cliente que acaba de
sufrir un compromiso es el mejor candidato posible para vender mantenimiento** — el valor del
servicio es evidente para él en este momento y no lo va a ser dentro de dos años. Cerrar el
mantenimiento junto con el desarrollo, no después.

**Retorno estratégico (interno):** el patrón reutilizable que queda para el próximo colegio.
No se factura pero baja el costo del siguiente proyecto del mismo tipo.

**(A completar en Fase 0: cifras de cotización, una vez conocido el volumen de contenido.)**

---

## 7. Esfuerzo y recursos por etapa

| Fase | Descripción | Horas | Rol principal |
|---|---|---|---|
| 0 | Descubrimiento e inventario | 16–24 | Senior |
| 1 | UX y arquitectura de información | 20–28 | Senior + UX |
| 2 | Copywriting y sistema de diseño | ~~24–32~~ **32–42** (×2 idiomas) | Senior + diseño |
| 3 | Backend y panel admin | ~~48–64~~ **58–76** (modelo de datos multiidioma + 4 roles) | Senior + junior |
| 4 | Frontend | ~~32–44~~ **38–52** (routing e interfaz por idioma) | Senior + junior |
| 5 | Migración de contenido | ~~16–24~~ **10–16** | Junior + senior |
| 6 | SEO e integraciones | ~~12–16~~ **14–20** (hreflang, sitemap por idioma) | Senior |
| 7 | Rendimiento | 10–14 | Senior |
| 8 | Seguridad | ~~12–18~~ **16–22** | Senior |
| 9 | QA y testing | 14–20 | Junior + senior |
| 10 | Despliegue y producción | 10–14 | Senior |
| | **Total** | ~~214–298 h~~ **238–328 h** | |

**Ajustado al cierre de la Fase 0** (`docs/01-analisis-descubrimiento.md` §F +
`docs/decisiones/ADR-002-multiidioma.md`, 2026-08-24): volumen real de contenido chico (39
piezas, ~30 tras descartar duplicados/pruebas) baja la Fase 5; sanitización HTML/SVG propia
(no la resuelve Filament), el peso de explicarle al cliente el hallazgo de seguridad, y **el
multiidioma pedido por el cliente** (ADR-002, no estaba en la cotización original) suben las
Fases 2, 3, 4, 6 y 8. Este último es un rango grueso — se afina en la Fase 3 cuando se defina
la estrategia técnica de traducción del modelo de datos. Pendiente además un ajuste cuando se
confirme el entorno de Plesk (pregunta abierta #13).

**Rango calendario:** 8 a 12 semanas con el equipo trabajando en paralelo con otros proyectos.

**Costos de terceros:**

| Concepto | Costo |
|---|---|
| Hosting Plesk | Infraestructura propia — costo interno |
| SSL Let's Encrypt | Gratis |
| Cloudflare Turnstile | Gratis |
| GA4 / GTM / Meta | Gratis |
| Tipografías del manual de marca | **A verificar en Fase 0** — si son comerciales, hay licencia web a pagar |
| Almacenamiento externo de respaldos | Bajo, según volumen |
| Créditos de IA (Claude) durante el desarrollo | Ya cubierto por la operación |

> ⚠️ El único costo de terceros que puede sorprender es la **licencia web de las tipografías**
> del manual de marca. Verificarlo en la Fase 0, antes de cotizar.

**Estas cifras se ajustan al cerrar la Fase 0** con el volumen real de contenido a migrar,
que es la variable con mayor incertidumbre.

---

## 8. Riesgos y supuestos

| # | Riesgo | Prob. | Impacto | Mitigación |
|---|---|---|---|---|
| R1 | El volumen de contenido del WordPress es mucho mayor de lo estimado | Media | Alto | Inventario cuantitativo en Fase 0 antes de cerrar precio. Cláusula de ajuste por volumen |
| R2 | El dump o los uploads contienen código malicioso que se arrastra a la web nueva | Media | Crítico | Protocolo de `CLAUDE.md` §2: base legacy aislada, lista blanca de extensiones, SVG sanitizados, cero migración de usuarios |
| R3 | El repo de IGP resulta menos reutilizable de lo esperado | Baja | Alto | Se evalúa en la Fase 0, antes de comprometer horas. Fallback: Filament 5, que acorta la Fase 3 |
| R4 | El cliente demora en aprobar contenido y diseño | **Alta** | Medio | Plazos de aprobación explícitos en el contrato. Aprobación tácita a los N días hábiles |
| R5 | Pérdida de posiciones SEO tras el cambio de plataforma | Media | Alto | Mapa 301 exhaustivo, misma estructura de URLs donde rinde, monitoreo en Search Console durante 90 días post-cutover |
| R6 | El manual de marca no cubre casos web (estados, componentes, responsive) | **Alta** | Medio | Extender la marca en la Fase 2 documentando cada decisión, y hacerla aprobar |
| R7 | Las tipografías del manual no tienen licencia web | Media | Bajo | Verificar en Fase 0. Alternativa: sustituto de Google Fonts aprobado por el cliente |
| R8 | El Plesk destino tiene limitaciones (PHP viejo, sin Node, sin Redis) | Media | Medio | Relevar en Fase 0. El diseño ya asume el escenario pesimista |
| R9 | El cliente pide alcance del v1 (portal privado) durante el MVP | Media | Alto | El "NO entra" de la sección 4 está escrito para eso. Cambio de alcance = nuevo legajo |
| R10 | El correo se rompe durante el cutover | Baja | Crítico | En el cutover se cambia **solo el registro A/web**. MX y registros de correo no se tocan |
| R11 | El WordPress actual tiene una webshell con ejecución remota de comandos **activa** (confirmado 2026-08-24, ver `docs/01-analisis-descubrimiento.md` §C.6) — riesgo operativo inmediato para el cliente, no solo histórico | Confirmada | Crítico | Fuera del cronograma de fases: avisar al cliente/hosting para sacar el sitio de línea o bloquear el acceso ya. Ningún dato de usuario/código del WP se migra (ya cubierto por R2) |
| R12 | El manual de marca no define ninguna tipografía ni entrega el logo en vectorial | Confirmada | Medio | Preguntas abiertas #6 y #12. Bloquea el cierre de la Fase 2, no el inicio de la Fase 1 |
| R13 | Contenido de una posible sede secundaria ("Fernando de la Mora") sin editar desde 2016 — no está claro si sigue operando | Media | Bajo | Confirmar con el cliente antes de decidir si se migra, se archiva o se descarta (`docs/01-analisis-descubrimiento.md` §C.2) |

**Supuestos:**

- El repo de IGP es propiedad de webparaguay o hay derecho a reutilizar su código.
- Existe un dump íntegro y reciente del WordPress, y acceso a `wp-content/uploads/`.
- El cliente tiene una persona designada para aprobar contenido y diseño.
- El cliente entrega los accesos a su Analytics, Search Console y cuentas de Meta.
- El manual de marca de Dante está vigente y aprobado por la institución.
- El hosting destino es el Plesk de webparaguay ya en uso para el dominio.

---

## 9. Métricas de éxito / KPIs

### Al cierre del proyecto

| Métrica | Objetivo |
|---|---|
| Lighthouse móvil (home, institucional, noticia) | ≥ 90 |
| Core Web Vitals | LCP < 2.5 s · INP < 200 ms · CLS < 0.1 |
| Hallazgos de seguridad Críticos/Altos abiertos | 0 |
| Accesibilidad | WCAG 2.1 AA en todas las plantillas |
| Contenido migrado | 100 % (o descarte justificado por escrito) |
| Redirecciones 301 funcionando | 100 % de las URLs del inventario |
| Cobertura de rutas del panel con test | 100 % |
| Caída durante el cutover | < 5 minutos |
| Incidentes de correo durante el cutover | 0 |

### A 90 días de producción

| Métrica | Objetivo |
|---|---|
| Tráfico orgánico vs. línea base pre-migración | ≥ 100 % |
| Errores 404 en Search Console | Tendencia a 0 |
| Contenidos publicados por el cliente **sin intervención de webparaguay** | ≥ 10 |
| Tickets de soporte por mes | ≤ 2 |
| Incidentes de seguridad | 0 |
| Restauraciones de respaldo probadas | ≥ 1 |

> El KPI que más importa es **"contenidos publicados por el cliente sin ayuda"**. Si el cliente
> nos sigue llamando para cambiar un texto, el panel falló, por más lindo que haya quedado el sitio.

### Interno de webparaguay

| Métrica | Objetivo |
|---|---|
| Horas reales vs. estimadas | Desvío < 20 % |
| Patrón reutilizable extraído y documentado | Sí, al cerrar Fase 10 |
| Recurrente mensual cerrado (hosting + mantenimiento) | Sí, junto con el desarrollo |
