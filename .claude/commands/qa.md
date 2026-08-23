---
description: Pasada de QA sobre lo construido hasta ahora
---

Hacé una pasada de QA sobre el estado actual, siguiendo `docs/11-qa-testing.md`.

1. Corré la suite: `php artisan test`, `vendor/bin/pint --test`, Larastan.
2. Identificá qué rutas del panel **no** tienen ningún test y escribí los que falten.
3. Revisá manualmente:
   - Enlaces internos rotos
   - Imágenes faltantes
   - Textos con placeholders que quedaron ("Lorem", "TODO", "XXX", texto de ejemplo)
   - Datos de contacto sin completar
   - Rutas que devuelven 500
4. Accesibilidad: recorré cada plantilla buscando las violaciones típicas
   (alt faltante, label sin asociar, `outline: none`, jerarquía de encabezados rota,
   contraste insuficiente).
5. Formularios: verificá que validan, que el honeypot está, que el captcha se valida del
   lado servidor y que hay rate limit.
6. Registrá todo lo que encuentres en la tabla de defectos de `docs/11-qa-testing.md` §8,
   con severidad.
7. Decime cuáles son bloqueantes.

⚠️ Si vas a usar Playwright: solo contra local o staging, y solo con credenciales de prueba.
Nunca contra producción ni con credenciales reales del cliente.
