---
description: Inventario del WordPress viejo (Fase 0)
---

Hacé el inventario completo del WordPress actual de Dante.

⚠️ **Antes de nada, leé `CLAUDE.md` §2.** El sitio origen estuvo comprometido. No ejecutes
ni un archivo PHP del WordPress. Solo se leen datos.

Pasos:

1. Verificá que exista el dump en `_insumos/03-wordpress-actual/db/`. Si no está, decímelo
   y detenete.
2. Importalo a una base local llamada `dante_wp_legacy`, con un usuario de solo lectura.
3. Corré `scripts/wp-inventario.sql` y volcá los resultados a
   `docs/01-analisis-descubrimiento.md` §C.1.
4. Generá la tabla de inventario de contenido (§C.2) con **una fila por cada URL pública**:
   URL, título, tipo, cantidad de palabras, última edición. Dejá vacías las columnas de
   tráfico y destino — esas las completamos después.
5. Detectá y listá en §C.3 las integraciones vigentes: buscá en `wp_options` y en el
   contenido rastros de GA, GTM, Meta Pixel, captcha, chat.
6. Listá los formularios encontrados en §C.4.
7. Revisá el contenido en busca de **indicadores de compromiso**: enlaces de spam, iframes
   inyectados, scripts en el contenido, usuarios administradores raros, opciones sospechosas.
   Volcalo en §C.6.
8. Si están los uploads, contá archivos por extensión y marcá todo lo que no sea de la lista
   blanca de `CLAUDE.md` §2.
9. Al terminar, decime el volumen real de contenido y si la estimación del legajo (§7) hay
   que ajustarla.

No resumas la tabla de contenido. Cada URL tiene que estar — de eso depende el mapa de
redirecciones 301, y una URL que falte ahí es un 404 en producción.
