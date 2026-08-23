---
description: Auditoría de seguridad y rendimiento sobre el estado actual
---

Auditá el estado actual del proyecto. No hace falta que esté terminado: auditá lo que haya.

## Seguridad

Recorré el checklist de `docs/10-seguridad.md` contra el código real:

- Cabeceras HTTP configuradas
- `{!! !!}` en Blade: listá cada uno y decime si su contenido está sanitizado
- Sanitización del HTML del editor: ¿existe? ¿es lista blanca o lista negra? (lista negra = mal)
- Subida de archivos: ¿verifica MIME real o solo extensión? ¿nombres aleatorios? ¿fuera de la raíz web?
- Autorización: ¿hay Policy en cada acción del panel, o solo se oculta el botón?
- `$request->all()` sin `$fillable` acotado
- SQL concatenado
- Rate limiting en login y formularios
- Credenciales en el repo — revisá también el historial de git
- `APP_DEBUG`, `.env`, `.git` expuestos

## Rendimiento

Contra `docs/09-rendimiento.md`:

- Consultas N+1
- Imágenes sin `width`/`height`, sin `srcset`, sin lazy
- JS y CSS que se cargan donde no se usan
- Caché configurada e invalidada correctamente
- Índices faltantes en columnas que se filtran u ordenan
- Fuentes: cantidad, formato, estrategia de carga

## Accesibilidad

- `outline: none` sin reemplazo
- Imágenes sin alt
- Formularios sin label asociado
- Contraste
- Jerarquía de encabezados

## Salida

Un informe con hallazgos ordenados por severidad (Crítico / Alto / Medio / Bajo), cada uno con:
archivo y línea, por qué es un problema, y cómo se arregla.

No propongas refactors de estilo. Solo problemas reales.
