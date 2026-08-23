# 01 — Repo de referencia: IPG

## Qué va acá

El código fuente completo del proyecto IPG — la web institucional autoadministrable que ya
está funcionando en producción. Es **el patrón que vamos a replicar**, no una inspiración
vaga: de acá salen el modelo de datos, la estructura del panel, el editor de texto enriquecido,
la gestión de medios, los menús y el manejo de SEO.

## Cómo cargarlo

**Opción A — clonar (recomendada):**

```bash
cd _insumos/01-referencia-ipg
git clone <url-del-repo-de-ipg> .
```

Con el historial de git completo es mejor: se puede ver cómo evolucionó el proyecto y por qué
se tomó cada decisión.

**Opción B — copiar los archivos:**

Copiá la carpeta del proyecto, **sin**:

- `vendor/`
- `node_modules/`
- `storage/logs/`, `storage/framework/cache/`
- `public/build/`, `public/storage/`
- **`.env`** ← importante: no traigas credenciales de producción de IPG a esta carpeta

## Qué es imprescindible que esté

- [ ] `composer.json` y `composer.lock` — dicen exactamente qué paquetes usa
- [ ] `package.json`
- [ ] `app/` completo — modelos, controladores, servicios
- [ ] `database/migrations/` — **el modelo de datos vive acá**
- [ ] `routes/`
- [ ] `resources/views/` — vistas del panel y del sitio
- [ ] `config/`
- [ ] `tests/` si existen

## Qué sería muy útil además

- [ ] Acceso a una instancia funcionando (aunque sea de staging) para ver el panel en uso
- [ ] Capturas del panel de IPG
- [ ] Cualquier documentación interna del proyecto
- [ ] Saber qué le gustó y qué le molestó al cliente de IPG de su panel — eso vale más que
      cualquier análisis del código

## Qué va a hacer Claude con esto

Leer el repo completo en la Fase 0 y escribir el mapa en `docs/01-analisis-descubrimiento.md` §A:
qué se copia tal cual, qué se adapta, qué no aplica, y qué falta y hay que construir para Dante.

## Nota legal

Verificar que el código es propiedad de webparaguay o que hay derecho a reutilizarlo. Si IPG
fue un desarrollo para un cliente con cesión de derechos, revisar el contrato antes de replicar
el código en otro proyecto.
