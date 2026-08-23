# 02 — Manual de marca de Dante

## Qué va acá

Todo el material de identidad visual de la institución. De acá salen los tokens de diseño
(`docs/04-ui-design-system.md`) y el tono de voz del copywriting.

## Estructura sugerida

```
02-marca-dante/
├── manual-de-marca.pdf
├── logos/
│   ├── *.svg      ← vectorial, es lo que más importa
│   ├── *.ai / *.eps
│   └── *.png      ← con fondo transparente, alta resolución
├── tipografias/
│   └── *.woff2 / *.otf / *.ttf
├── fotografia/    ← banco de imágenes propio de la institución
└── otros/         ← papelería, aplicaciones, cualquier pieza de referencia
```

## Imprescindible

- [ ] **El manual en PDF** — paleta con códigos exactos, tipografías, usos del logotipo,
      área de resguardo, usos prohibidos
- [ ] **Logotipo en vectorial** (SVG o AI). Un PNG no alcanza: hace falta escalarlo y
      recolorearlo para el sitio
- [ ] Todas las **variantes del logotipo**: horizontal, vertical, isotipo solo, versión
      monocromática, versión para fondo oscuro

## Muy recomendable

- [ ] **Las tipografías en archivo** + **la información de licencia**
      ⚠️ Este es el punto que más sorpresas da. Una licencia de escritorio **no** habilita el
      uso web. Si el manual usa una tipografía comercial, hay que comprar la licencia web o
      elegir un sustituto. Verificalo en la Fase 0, antes de cotizar
- [ ] Fotografía propia de la institución, actual y de buena resolución
- [ ] Iconografía si el manual define un set
- [ ] Piezas de referencia (folletos, cartelería) para entender cómo se aplica la marca en la práctica

## Lo que el manual seguramente NO va a tener, y hay que resolver en la Fase 2

Los manuales de marca se escriben pensando en imprenta, no en interfaces. Casi con seguridad
falta:

- Estados de interfaz (hover, foco, deshabilitado, error)
- Comportamiento responsive
- Escala tipográfica para pantalla
- Colores de sistema (éxito, error, advertencia)
- Contraste accesible — es común que la paleta de marca no pase AA
- Componentes web (formularios, tarjetas, navegación)

Todo eso se define en la Fase 2 **extendiendo** la marca, y se hace aprobar por el cliente.
Se documenta en `docs/04-ui-design-system.md`.

## Si no hay manual de marca formal

Cargá lo que exista: el logotipo, los colores que usa hoy el sitio, cualquier pieza gráfica
de la institución. En ese caso la Fase 2 incluye definir el sistema visual desde esos
elementos — es más trabajo y hay que avisarlo en la cotización.
