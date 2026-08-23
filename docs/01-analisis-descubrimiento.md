# 01 — Análisis y descubrimiento (Fase 0)

Estado: **vacío — completar en Fase 0**

---

## A. Análisis del repo de IPG

> Insumo: `_insumos/01-referencia-ipg/`
> Objetivo: entender el patrón para replicarlo, no para reinventarlo.

### A.1 Datos del proyecto de referencia

| Campo | Valor |
|---|---|
| Versión de Laravel | |
| Versión de PHP | |
| Livewire / Filament / otro | |
| CSS | |
| Editor de texto enriquecido | |
| Gestión de medios | |
| Paquetes clave (`composer.json`) | |
| Estructura del panel admin | |

### A.2 Modelo de datos de IPG

Tabla por tabla: nombre, propósito, relaciones, si aplica a Dante.

| Tabla | Propósito | Relaciones | ¿Aplica a Dante? |
|---|---|---|---|
| | | | |

### A.3 Cómo resuelve IPG cada capacidad

| Capacidad | Cómo lo resuelve IPG | Veredicto para Dante |
|---|---|---|
| Panel de administración (estructura, layout, navegación) | | copiar / adaptar / no aplica |
| Autenticación y roles | | |
| Páginas y jerarquía | | |
| Constructor de bloques de contenido | | |
| Editor de texto enriquecido y sanitización | | |
| Subida y conversión de medios | | |
| Gestión de menús | | |
| Campos SEO por página | | |
| Configuración global editable | | |
| Formularios y almacenamiento de envíos | | |
| Integraciones (analytics, pixel, captcha) | | |
| Caché e invalidación | | |
| Buscador interno | | |
| Redirecciones | | |
| Registro de auditoría | | |
| Tests existentes | | |
| Despliegue en Plesk | | |

### A.4 Qué se copia, qué se adapta, qué no aplica

**Se copia tal cual:**

**Se adapta:**

**No aplica / se descarta:**

**Falta en IPG y hay que construir para Dante:**

---

## B. Análisis del manual de marca

> Insumo: `_insumos/02-marca-dante/`
> Los tokens extraídos se vuelcan a `docs/04-ui-design-system.md`.

| Elemento | Qué dice el manual | Nota para web |
|---|---|---|
| Logotipo y variantes | | |
| Área de resguardo y tamaño mínimo | | |
| Paleta primaria | | ¿pasa contraste AA? |
| Paleta secundaria / acentos | | |
| Tipografía principal | | **¿tiene licencia web?** |
| Tipografía secundaria | | |
| Jerarquía tipográfica | | |
| Grilla y márgenes | | |
| Tratamiento fotográfico | | |
| Iconografía | | |
| Tono de voz | | |
| Usos prohibidos | | |

**Vacíos del manual que hay que resolver en la Fase 2** (los manuales de marca casi nunca
cubren estados de interfaz, comportamiento responsive ni componentes web):

---

## C. Inventario del WordPress actual

> Insumo: `_insumos/03-wordpress-actual/`
> ⚠️ Protocolo de seguridad de `CLAUDE.md` §2. Base legacy de solo lectura, cero ejecución de código.

### C.1 Cifras generales

Salida de `scripts/wp-inventario.sql`:

| Métrica | Cantidad |
|---|---|
| Páginas publicadas | |
| Entradas publicadas | |
| Borradores / papelera | |
| Tipos de contenido personalizados | |
| Categorías / etiquetas | |
| Elementos de medios | |
| Peso de `uploads/` | |
| Menús y sus elementos | |
| Usuarios | |
| Plugins activos | |
| Tema activo | |
| Formularios (plugin y cantidad) | |

### C.2 Inventario de contenido, página por página

| URL actual | Título | Tipo | Palabras | Última edición | Tráfico | Destino | URL nueva |
|---|---|---|---|---|---|---|---|
| | | | | | | migrar / reescribir / fusionar / descartar | |

> Esta tabla es el insumo directo de la Fase 1 (card sorting) y de la Fase 5 (mapa 301).
> No la resumas: cada URL pública tiene que estar acá.

### C.3 Integraciones vigentes

| Integración | ¿Existe hoy? | ID / configuración | ¿Se mantiene? |
|---|---|---|---|
| Google Analytics | | | |
| Google Tag Manager | | | |
| Meta Pixel | | | |
| Captcha | | | |
| Chat / WhatsApp | | | |
| Mapa | | | |
| Redes sociales embebidas | | | |
| Newsletter | | | |
| Otro | | | |

### C.4 Formularios existentes

| Formulario | Ubicación | Campos | Destino de los datos | ¿Datos de menores? |
|---|---|---|---|---|
| | | | | |

### C.5 Línea base de SEO

Para poder demostrar después que no se perdió tráfico.

| Métrica (últimos 12 meses) | Valor |
|---|---|
| Sesiones orgánicas / mes | |
| Páginas con más tráfico (top 20) | |
| Consultas con más impresiones | |
| Posición promedio | |
| Backlinks relevantes | |
| Core Web Vitals actuales | |

### C.6 Hallazgos del compromiso

Qué se encontró en el dump o en los uploads que confirme o acote el incidente. Archivos
sospechosos, usuarios administradores extraños, contenido inyectado, enlaces de spam en el
contenido.

| Hallazgo | Dónde | Acción |
|---|---|---|

---

## D. Entorno de destino

| Item | Valor | Verificado |
|---|---|---|
| Servidor / panel | Plesk, `177.251.252.12` | |
| Versión de PHP disponible | | |
| Versión de MySQL/MariaDB | | |
| ¿Node/npm para el build? | | |
| ¿Redis? | | |
| ¿Supervisor? | | |
| ¿Acceso SSH? | | |
| ¿Composer instalado? | | |
| Límite de memoria PHP | | |
| Tamaño máximo de subida | | |
| Espacio en disco | | |
| Certificado SSL | Let's Encrypt vía Plesk | |
| Registros DNS actuales (A, MX, TXT) | web → webparaguay, mail → HostGator | |

---

## E. Preguntas abiertas para el cliente

> No asumir. Listar y preguntar.

| # | Pregunta | Por qué importa | Respuesta | Fecha |
|---|---|---|---|---|
| 1 | ¿El sitio necesita versión en italiano además de español? | Cambia el modelo de datos y el alcance por completo | | |
| 2 | ¿Quién va a administrar el sitio y cuántas personas? | Define roles y complejidad del panel | | |
| 3 | ¿Qué formularios reciben datos personales de menores? | Obligaciones legales y de seguridad distintas | | |
| 4 | ¿Hay contenido que quieren eliminar aprovechando el rediseño? | Reduce el volumen de migración | | |
| 5 | ¿Hay calendario académico o comunicados que se publican con frecuencia? | Puede requerir un tipo de contenido propio | | |
| 6 | ¿Tienen las tipografías del manual con licencia web? | Costo de terceros no previsto | | |
| 7 | ¿Quién aprueba diseño y contenido, y en qué plazo? | Riesgo R4 | | |
| 8 | ¿Tienen accesos a Analytics, Search Console y Meta Business? | Sin esto no hay línea base ni integraciones | | |
| 9 | ¿Hay material fotográfico propio y actualizado? | Alternativa: sesión fotográfica o banco de imágenes | | |
| 10 | ¿Qué se hace con el WordPress viejo después del cutover? | Plan de baja | | |

---

## F. Conclusiones de la Fase 0

**Volumen real de contenido:**

**Enfoque del panel decidido (→ ADR-001):**

**Ajuste de estimación:**

**Riesgos nuevos detectados:**

**Luz verde para Fase 1:** ☐ sí ☐ no — motivo:
