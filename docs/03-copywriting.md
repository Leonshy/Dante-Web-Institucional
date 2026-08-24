# 03 — Copywriting (Fase 2)

Estado: **completo**, con reservas explícitas — ver nota de alcance abajo.

> **Nota de alcance y método.** Este documento se escribió a partir de: `docs/01-analisis-descubrimiento.md`
> (§B tono/marca, §C.2 inventario de 39 piezas con título/palabras/URL, §C.6 incidente de
> seguridad), `docs/02-ux-arquitectura-informacion.md` (mapa del sitio, tipos de contenido,
> wireframes) y `docs/redirecciones-301.csv`. La redacción se hizo en dos pasadas: una primera
> (subagente `copywriter-institucional`, sin acceso a intérprete de comandos) con los **hechos ya
> confirmados y citados en `docs/01`/`docs/02`** (129° aniversario, visita de Sergio Mattarella en
> 2024, afiliación a la Società Dante Alighieri, certificación PLIDA, bilingüismo
> español-italiano, inscripción presencial en papel), y una segunda pasada (con acceso de
> solo lectura a `dante_wp_legacy` vía `mysql`) que reemplazó los bloques `[COMPLETAR CON
> CONTENIDO MIGRADO]` por el texto real de las páginas Autoridades, Administración, Biblioteca,
> Enlaces de interés y el FAQ de Admisiones (§2.1.1–§2.1.4, §2.2.1) — limpiado de marcado Divi y
> reescrito en el tono de §1, **sin inventar ningún dato**. Los montos de aranceles del año 2024
> que trae el WordPress viejo se excluyeron a propósito (ver pregunta abierta #21) — publicar una
> cifra vieja como vigente sería un error. Las siete preguntas sin responder del cliente
> (`docs/01` §E #14 a #20) siguen marcadas como `[PENDIENTE: pregunta #N]`, más la nueva #21.
>
> **Multiidioma (ADR-002):** todo el copy de este documento está escrito para existir en
> español **e italiano**. La traducción en sí es trabajo de otra etapa (no de Fase 2), pero cada
> texto se redactó pensando en que tiene que sobrevivir la traducción sin perder tono: frases
> cortas, sin juegos de palabras intraducibles, sin dobles sentidos. Cuando el italiano esté
> habilitado, el tono de voz (§1) se traslada tal cual — "usted" en español equivale a "Lei" en
> italiano formal.

---

## 1. Tono de voz

El manual de marca (`docs/01-analisis-descubrimiento.md` §B) no define tono de voz — es
puramente de identidad visual (logo + color). Se define acá desde cero, fundamentado en el
posicionamiento real del proyecto (`PRODUCT.md`): colegio bilingüe con 129 años de historia,
afiliación oficial a la Società Dante Alighieri (Roma), certificación internacional PLIDA y una
visita de Estado (presidente de Italia, 2024) — son hechos que sostienen autoridad por sí solos,
no necesitan adjetivos que los inflen.

| Dimensión | Definición para Dante |
|---|---|
| Formalidad | Alta-media. Es una institución educativa con 129 años y una afiliación diplomática real — no es una escuelita de barrio ni una corporación. El registro es formal pero legible, sin jerga administrativa |
| Cercanía | El lector es un padre o madre con poco tiempo, comparando colegios desde el celular. Se le habla directo, sin rodeos, con información útil primero. Cercano en el servicio, no en la familiaridad |
| Autoridad | Se construye con datos verificables (años, afiliación, certificación, hitos), nunca con adjetivos autoelogiosos |
| Tratamiento | **Usted**, en todo el sitio — formularios, botones, textos legales, microcopy. Es el registro esperado de una institución educativa seria en Paraguay y evita la fricción de decidir "vos" para un padre y "usted" para un trámite legal |
| Persona | Hablamos como "el colegio" / "Dante", en tercera persona o primera del plural moderada ("contamos con", "ofrecemos"). Nunca en primera persona del singular, nunca "nosotros" reiterado en cada frase |
| Extensión de frase | Corta. Una idea por oración, una idea por párrafo. Los párrafos largos se cortan con subtítulos o listas |

**Sí decimos:**

- "129 años de historia" / "afiliados a la Società Dante Alighieri, Roma" / "certificación PLIDA".
- Verbos de acción en los CTA: "Complete la pre-inscripción", "Descargue el documento",
  "Escríbanos".
- El nombre completo la primera vez en cada página ("Colegio Dante Alighieri"), abreviado
  después ("Dante") — ver convención en §6.
- Fechas y plazos concretos cuando se conocen ("le respondemos en 48 horas").

**No decimos:**

- "Bienvenidos a nuestro sitio web."
- "En un mundo cada vez más globalizado..."
- "Somos líderes en educación bilingüe" / "excelencia académica" / "formamos personas íntegras"
  sin un dato detrás.
- Signos de exclamación en títulos institucionales.
- "Vos" en ningún punto del sitio (ni siquiera en microcopy informal de error o vacío).

**Ejemplo de la misma idea, bien y mal:**

> ✅ "Dante es un colegio bilingüe español-italiano en Asunción, afiliado a la Società Dante
> Alighieri de Roma desde hace 129 años. En 2024 recibió la visita del presidente de Italia,
> Sergio Mattarella."
>
> ❌ "En Dante creemos en una educación de excelencia que forma líderes del mañana en un ambiente
> de calidez y compromiso con los más altos estándares internacionales."

> ✅ "Complete la pre-inscripción. La secretaría lo contacta para coordinar la entrega de
> documentación en el colegio."
>
> ❌ "¡Inscribite ya y asegurá el futuro de tu hijo!"

---

## 2. Textos por página

Una entrada por plantilla/página del mapa del sitio de la Fase 1 (`docs/02` §4 y §9). Las
páginas institucionales que comparten plantilla ("página institucional con bloques") se agrupan
bajo un solo bloque de tabla cuando el texto estructural es idéntico, para no repetir 9 veces la
misma fila de microcopy.

### Inicio

- **URL:** `/`
- **Audiencia principal:** padres que están evaluando el colegio
- **Objetivo de la página:** que en los primeros 5 segundos el visitante entienda qué es Dante y
  encuentre el camino a Admisiones
- **CTA principal:** "Quiero inscribir a mi hijo/a" → `/admisiones`

| Elemento | Texto |
|---|---|
| `h1` | Colegio Dante Alighieri — educación bilingüe español-italiano en Asunción |
| Bajada (hero) | Afiliados a la Società Dante Alighieri de Roma. Educación bilingüe desde el nivel inicial, con certificación internacional PLIDA. |
| CTA hero | Quiero inscribir a mi hijo/a |
| Bloque "Oferta educativa" — título | Nuestra propuesta educativa |
| Bloque "Oferta educativa" — cuerpo | Instituto de Lengua y Cultura, Cursos de Italiano y certificación internacional PLIDA, obligatoria en ciertos grados. Conozca cómo se enseña el italiano en cada nivel. |
| Bloque "Hitos institucionales" — título | 129 años de historia |
| Bloque "Hitos institucionales" — cuerpo | 129° aniversario de la Scuola Dante Alighieri. Visita del presidente de Italia, Sergio Mattarella, en 2024. Afiliación oficial a la Società Dante Alighieri, Roma. |
| Bloque "Últimas noticias" — título | Noticias del colegio |
| CTA bloque noticias | Ver todas las noticias |
| CTA final (destacado) | ¿Quiere conocer el colegio? Complete la pre-inscripción y lo contactamos. |
| SEO título (≤ 60 car.) | Colegio Dante Alighieri — bilingüe español-italiano, Asunción |
| SEO descripción (≤ 155 car.) | Colegio bilingüe afiliado a la Società Dante Alighieri, con 129 años de historia y certificación internacional PLIDA. Conozca la oferta educativa. |

---

### Plantilla — Página institucional (aplica a las 9 páginas de "Institución")

Estructura común (hero corto + migas de pan + bloques de texto enriquecido + navegación
contextual a las páginas hermanas, según wireframe `pagina-institucional.html`). Se completa el
`h1`, la bajada y el objetivo de cada una; el cuerpo detallado de cada página requiere el texto
migrado real (ver nota de alcance).

| Página | URL | `h1` | Bajada | SEO título (≤60) | SEO descripción (≤155) |
|---|---|---|---|---|---|
| Quiénes somos | `/institucion/quienes-somos` | Quiénes somos | Un colegio bilingüe con 129 años de historia, afiliado a la Società Dante Alighieri de Roma. | Quiénes somos — Colegio Dante Alighieri | Conozca la identidad del Colegio Dante Alighieri: bilingüe, afiliado a la Società Dante Alighieri y con 129 años de trayectoria en Asunción. |
| Historia | `/institucion/historia` | Historia | 129 años de trayectoria educativa en Asunción, desde la fundación hasta la visita del presidente de Italia en 2024. | Historia del Colegio Dante Alighieri | La historia del Colegio Dante Alighieri: 129 años de trayectoria y la visita del presidente de Italia, Sergio Mattarella, en 2024. |
| Misión, visión y valores | `/institucion/mision-vision-valores` | Misión, visión y valores | Qué guía la educación que ofrece Dante. | Misión, visión y valores — Dante | Misión, visión y valores del Colegio Dante Alighieri, colegio bilingüe español-italiano en Asunción. |
| Autoridades | `/institucion/autoridades` | Autoridades | Quiénes integran la Comisión Directiva y la conducción de las dos sedes del colegio. | Autoridades — Colegio Dante Alighieri | Conozca las autoridades del Colegio Dante Alighieri, colegio bilingüe afiliado a la Società Dante Alighieri de Roma. |
| Acerca de la Società Dante Alighieri | `/institucion/sociedad-dante-alighieri` | Acerca de la Società Dante Alighieri | Qué es la Società Dante Alighieri de Roma y qué significa estar afiliados. | Società Dante Alighieri — afiliación del colegio | El Colegio Dante Alighieri está afiliado a la Società Dante Alighieri de Roma. Conozca qué implica esa afiliación. |
| Certificación internacional | `/institucion/certificacion-internacional` | Certificación internacional | Certificación PLIDA (Proyecto Lengua Italiana Dante Alighieri), obligatoria en ciertos grados. | Certificación PLIDA — Colegio Dante Alighieri | El Colegio Dante Alighieri ofrece la certificación internacional PLIDA. Conozca en qué grados es obligatoria y cómo se rinde. |
| Estatutos sociales | `/institucion/estatutos-sociales` | Estatutos sociales | El documento que rige la organización del colegio, disponible para descarga. | Estatutos sociales — Colegio Dante Alighieri | Descargue los estatutos sociales del Colegio Dante Alighieri en formato PDF. |
| Administración | `/institucion/administracion` | Administración | Aranceles, cuotas y condiciones de pago del año lectivo. | Administración — Colegio Dante Alighieri | Aranceles, cuotas y condiciones de pago del Colegio Dante Alighieri, colegio bilingüe en Asunción. |
| Convenio con Ex Alumnos | `/institucion/convenio-ex-alumnos` | Convenio con la Asociación de Ex Alumnos | `[PENDIENTE: pregunta #17 — vigencia del convenio]` | Convenio con Ex Alumnos — Dante | `[PENDIENTE: pregunta #17]` |

CTA de cierre común a las 9 páginas: **"Ver todas las páginas de Institución"** (vuelve a la
landing `/institucion`).

#### 2.1.1 Cuerpo — Autoridades

> Fuente: contenido real de la página actual (`/autoridades/`, ID 358 en `dante_wp_legacy`),
> reescrito en el tono definido en §1. **Los nombres corresponden al período 2023** — antes de
> publicar, confirmar con el cliente si la Comisión Directiva vigente es la misma (agregar como
> pregunta abierta si cambió).

**Comisión Directiva**

| Cargo | Nombre |
|---|---|
| Presidenta | Abog. Viviana Ruggero Laurino |
| Vicepresidenta | Sra. Esther González Palumbo de Noguera |
| Tesorera | María Guadalupe Segovia Volta |
| Vicetesorero | Lic. Alejandro Bellón |
| Secretaria | Prof. Higinia Giosa |
| Vocales | Dra. Irma Riella de Hirschkorn · Sr. Enrico Sachero Feliu · Danilo Bellón · Lic. Roberto Segovia |
| Suplentes | Eduardo Martínez Ruggero · Fabrizio Boffi |
| Comisión fiscalizadora (titulares) | Mtr. Graciela Acevedo Yrigoyen · Arq. Pedro Calabrese |
| Tribunal de conducta | Teresa Cardozo Vda. de Ughelli · Teresa Masi |
| Tribunal electoral | TSE Alejandro Noguera · José Carlos Melgarejo (suplente: Fátima Judith Fariña) |

**Dirección de las sedes**

| Sede | Director/a |
|---|---|
| Colegio Dante Alighieri — sede Asunción | Angelo Pigliacelli |
| Colegio Dante Alighieri — sede Fernando de la Mora | Ninfa Esther Cantero de Duarte |
| Instituto Dante Alighieri (idioma y cultura) | — |

**Administración general:** Andrés Amarilla Ruiz Díaz (general) · Sandra Romero (sede Fernando
de la Mora).

*Bajada de la página:* "La Comisión Directiva de la Sociedad Dante Alighieri y la dirección de
ambas sedes del colegio."

#### 2.1.2 Cuerpo — Administración

> Fuente: contenido real de la página actual (`/administracion/`, ID 285). La página describe
> **condiciones de pago**, no una estructura administrativa — se mantiene el enfoque real. Los
> montos en guaraníes que trae el WordPress corresponden al año lectivo 2024 y **quedan
> explícitamente fuera de este documento** — publicar una cifra vieja como si fuera la vigente
> sería un error, no una omisión menor. Se agrega como pregunta abierta nueva:

**Pregunta abierta #21 (nueva):** ¿Cuáles son los aranceles y cuotas vigentes para el año
lectivo actual, por nivel? — necesario para publicar la página de Administración con datos
correctos, no los de 2024 que trae el sitio viejo.

**Copy de la página (sin montos, estructura lista para cargar los valores vigentes):**

> **Aranceles y condiciones de pago**
>
> El costo de matrícula y cuota varía según el nivel educativo. Consulte los valores vigentes
> para el año lectivo en la Administración del colegio. `[PENDIENTE: pregunta #21 — tabla de
> aranceles por nivel]`
>
> **Condición para la inscripción de alumnos que ya cursan en la institución:** estar al día
> con las cuotas y aranceles del año lectivo anterior. No se formalizan inscripciones con pagos
> pendientes.
>
> **Formas de pago:** Pronet, Pago Móvil, o en la Administración del colegio con tarjeta de
> crédito o débito.
>
> **Vencimiento:** la cuota mensual se abona entre el día 1 y el 10 de cada mes, con 5 días de
> gracia sin recargo.
>
> **Pago anual:** abonar la anualidad completa en un solo pago (antes del plazo que fije la
> Administración) tiene un descuento — consultar el porcentaje y la fecha límite vigentes.
>
> **Descuento por hermanos:** el colegio ofrece descuentos escalonados para familias con más de
> un hijo inscripto en el mismo año lectivo — consultar condiciones vigentes en la
> Administración.

#### 2.1.3 Cuerpo — Biblioteca "Irene Borello de Amodei"

> Fuente: contenido real de la página actual (`/biblioteca-irene-borello-de-amodei/`, ID 488).

> Creada en 1981 bajo la dirección del Prof. Fiorello Ficorilli, la biblioteca "Irene Borello
> de Amodei" ocupa 157 m² en el edificio del colegio y está catalogada como una de las más
> completas del país en su especialidad: educación, humanidades y ciencias sociales.
>
> Comenzó con 6.500 volúmenes donados por el Gobierno de Italia y la Società Dante Alighieri de
> Roma. Entre sus fondos más valiosos se encuentra una donación del Ing. Enzo Debernardi sobre
> "Il Codice Atlantico" de Leonardo da Vinci — pieza única en Sudamérica.
>
> **Servicios disponibles:** hemeroteca (revistas nacionales e italianas de educación),
> ludoteca, mediateca, mapoteca, enciclopedias, diccionarios y computadoras con acceso a
> internet. Se dictan clases-taller de Lectura, Química, Metodología de la Investigación,
> Física, Ciencias Sociales, Comunicación e Italiano.
>
> La biblioteca es de acceso público — reciben consultas de investigadores, alumnos de otras
> instituciones y restauradores de obras pictóricas. Los alumnos de Educación Escolar Básica
> cuentan con su propio rincón de lectura en español e italiano, y el "Club de Pequeños Grandes
> Lectores" permite retirar libros a domicilio desde primer grado. Desde 2010 el colegio
> organiza el Maratón de Lectura de la Dante Alighieri sede Asunción.
>
> **Horario de atención:** de 13:00 a 17:00, de lunes a viernes.

*Bajada de la página:* "Más de 40 años de historia, una de las bibliotecas educativas más
completas del país y acceso público."

#### 2.1.4 Cuerpo — Enlaces de interés

> Fuente: contenido real de la página actual (`/enlaces-de-interes/`, ID 341). Se agrupan por
> tipo en vez de listar sin criterio, siguiendo la regla de "menos es más" del tono de voz.

> **Instituciones y cultura italiana**
> Società Dante Alighieri (sede central, Roma) · Embajada de Italia en Paraguay · Ministero
> degli Esteri italiano · Agenzia Nazionale del Turismo
>
> **Comunidad ítalo-paraguaya**
> Círculo Deportivo Italo Paraguayo · Unión Siciliana del Paraguay · Cámara de Comercio
> Italo-Paraguaya · Asociación de los Emiliano-Romañolos del Paraguay · Asociación Adiveneto Py
>
> **Estudio del idioma italiano**
> Italia para Todos (sitio de inmigrantes y descendientes) · EDULINGUA — Laboratorio di lingua
> e cultura italiana · Universidad para Extranjeros "Dante Alighieri" de Reggio Calabria ·
> Scuola di Italiano Dante Alighieri
>
> **Regiones de Italia**
> Enlaces oficiales de las regiones Abruzzo, Basilicata, Calabria, Campania, Emilia-Romaña,
> Friuli-Venezia Giulia, Liguria, Piamonte, Sicilia, Toscana, Véneto y la Provincia de Trento.

*Nota de implementación:* verificar que cada enlace externo resuelva antes de publicar (varios
son de hace años) — tarea de QA en Fase 9, no de esta fase.

---

### Landing — Institución

- **URL:** `/institucion`
- **Audiencia principal:** prensa, instituciones, padres evaluando (segundo nivel de interés)
- **Objetivo:** ordenar el acceso a las 9 páginas institucionales
- **CTA principal:** tarjetas hacia cada sub-página

| Elemento | Texto |
|---|---|
| `h1` | Institución |
| Bajada | 129 años de historia, afiliación a la Società Dante Alighieri de Roma y una comunidad educativa bilingüe en Asunción. |
| Tarjetas | Quiénes somos · Historia · Misión, visión y valores · Autoridades · Società Dante Alighieri · Certificación internacional · Estatutos sociales · Administración |
| SEO título | Institución — Colegio Dante Alighieri |
| SEO descripción | Historia, autoridades, misión y certificación internacional del Colegio Dante Alighieri, colegio bilingüe afiliado a la Società Dante Alighieri. |

---

### Landing — Oferta educativa

- **URL:** `/oferta-educativa`
- **Audiencia principal:** padres evaluando
- **Objetivo:** que entiendan en qué consiste la propuesta bilingüe antes de pasar a Admisiones
- **CTA principal:** "Ver requisitos de admisión" → `/admisiones`

| Elemento | Texto |
|---|---|
| `h1` | Oferta educativa |
| Bajada | Educación bilingüe español-italiano, desde el nivel inicial hasta la certificación internacional. |
| Tarjeta 1 | Instituto de Lengua y Cultura — cursos de italiano para todas las edades |
| Tarjeta 2 | Cursos de italiano — programas con certificación PLIDA |
| CTA final | Ver cómo inscribirse |
| SEO título | Oferta educativa — Colegio Dante Alighieri |
| SEO descripción | Educación bilingüe español-italiano con certificación PLIDA. Conozca el Instituto de Lengua y Cultura y los cursos de italiano de Dante. |

| Página hija | URL | `h1` | Bajada | SEO título | SEO descripción |
|---|---|---|---|---|---|
| Instituto de Lengua y Cultura | `/oferta-educativa/instituto-de-lengua-y-cultura` | Instituto de Lengua y Cultura | Cursos de italiano para niños, jóvenes y adultos, dentro y fuera del colegio. | Instituto de Lengua y Cultura — Dante | El Instituto de Lengua y Cultura del Colegio Dante Alighieri ofrece cursos de italiano para todas las edades en Asunción. |
| Cursos de Italiano | `/oferta-educativa/cursos-de-italiano` | Cursos de Italiano | Niveles y certificación PLIDA (Proyecto Lengua Italiana Dante Alighieri). | Cursos de Italiano — Colegio Dante Alighieri | Cursos de italiano con certificación internacional PLIDA en el Colegio Dante Alighieri, Asunción. |

---

### Landing — Admisiones

- **URL:** `/admisiones`
- **Audiencia principal:** padres evaluando — tarea de mayor prioridad comercial del sitio (`docs/02` §2, tarea #2)
- **Objetivo:** que sepan qué necesitan y a qué formulario ir, sin fricción
- **CTA principal:** "Complete la pre-inscripción"

| Elemento | Texto |
|---|---|
| `h1` | Admisiones |
| Bajada | La inscripción se completa en el colegio, en forma presencial. Acá encuentra los requisitos y el formulario de pre-inscripción. |
| Bloque "Cómo es el proceso" — título | Cómo es el proceso |
| Bloque "Cómo es el proceso" — cuerpo | Complete la pre-inscripción en línea. La secretaría lo contacta para coordinar la entrega de la documentación (cédula, certificado de nacimiento, boletín y fotos) en el colegio. La inscripción se formaliza de manera presencial. |
| Acordeón/FAQ | Preguntas frecuentes sobre requisitos, fechas y aranceles. Contenido real en §2.2.1 (fuente: circular "Inscripciones 2026", ID 283). |
| CTA sede Asunción | Pre-inscripción — sede Asunción |
| CTA sede Fernando de la Mora | `[PENDIENTE: pregunta #14 — vigencia de la sede]` |
| SEO título | Admisiones — Colegio Dante Alighieri |
| SEO descripción | Cómo inscribir a su hijo en el Colegio Dante Alighieri. Requisitos, formulario de pre-inscripción y próximos pasos. |

| Página hija | URL | `h1` | Microcopy clave |
|---|---|---|---|
| Pre-inscripción (Asunción) | `/admisiones/pre-inscripcion` | Pre-inscripción | Ver formulario en §3 (microcopy de formularios) |
| Pre-inscripción Fernando de la Mora | `/admisiones/pre-inscripcion-fernando-de-la-mora` | `[PENDIENTE: pregunta #14]` | `[PENDIENTE: pregunta #14]` |

**Confirmación tras enviar el formulario:** "Recibimos su pre-inscripción. La secretaría lo
contacta para coordinar la entrega de documentación en el colegio." — el destino real del envío
(mail propio o de terceros) depende de `[PENDIENTE: pregunta #20]`, pero el texto de confirmación
no cambia según esa respuesta.

#### 2.2.1 FAQ de Admisiones — contenido real

> Fuente: circular "Inscripciones 2026" (`/inscripciones-2/`, ID 283), la pieza más reciente y
> voluminosa del inventario (§C.2 de `docs/01`). Reducida a preguntas y respuestas breves —
> el original es una circular de 5200 palabras, no apta para un acordeón web tal cual. Los
> montos de aranceles no están en esta pieza (están en Administración, ver §2.1.2, pendiente
> de valores vigentes).

**¿Cuál es el horario de clases?**
Pre-Escolar, Jardín y Pre-Jardín: de 7:00 a 12:15, de lunes a viernes. 1º y 2º ciclo: de 7:00 a
12:30 (3 días) y de 7:00 a 15:30 (2 días). 3º ciclo y Nivel Medio: de 7:00 a 15:30, de lunes a
viernes.

**¿Qué documentos necesito para inscribir a un alumno que ya cursa en el colegio?**
Boletín de calificaciones, fotocopia de cédula de identidad (en caso de renovación) y
certificado de inspección médica para Educación Física, antes del 27 de febrero.

**¿Qué documentos necesito para inscribir a un alumno nuevo (hasta 2º curso)?**
Informe descriptivo de la institución anterior (Nivel Inicial), boletín de calificaciones con
promedio mínimo 4, certificado de nacimiento original, 4 fotos carnet, 4 fotocopias de cédula
del alumno y 2 de cada padre/madre o encargado, y una entrevista previa con el departamento de
Orientación.

**¿Se inscriben alumnos nuevos en el último curso de la Educación Media?**
No — el colegio no recibe inscripciones nuevas para 3º curso de Nivel Medio.

**¿Es obligatorio el examen PLIDA?**
Sí, para los alumnos de 8º grado, 1º y 3º curso — es parte de la malla curricular y se rinde el
tercer sábado de octubre.

**¿Cómo se abonan las cuotas?**
Por Pronet o Pago Móvil, entre el día 1 y el 10 de cada mes. El costo total es una anualidad
dividida en 10 cuotas (de febrero a noviembre). Ver valores vigentes en Administración.

**¿Puedo inscribir a mi hijo/a por teléfono o por un tercero?**
No — la inscripción es personal, la realiza el padre, la madre o el encargado, con toda la
documentación requerida. El colegio no admite "alumnos oyentes".

---

### Landing — Vida escolar

- **URL:** `/vida-escolar`
- **Audiencia principal:** padres de alumnos actuales
- **Objetivo:** centralizar calendario, comunicados, eventos, galería, biblioteca y enlaces
- **CTA principal:** "Ver calendario académico"

| Elemento | Texto |
|---|---|
| `h1` | Vida escolar |
| Bajada | Calendario, comunicados, eventos y actividades del colegio. |
| Tarjetas | Calendario académico · Comunicados · Eventos · Galería · Biblioteca "Irene Borello de Amodei" · Enlaces de interés |
| SEO título | Vida escolar — Colegio Dante Alighieri |
| SEO descripción | Calendario académico, comunicados, eventos y galería del Colegio Dante Alighieri. |

| Página hija | URL | `h1` | Bajada | SEO título | SEO descripción |
|---|---|---|---|---|---|
| Calendario académico | `/vida-escolar/calendario` | Calendario académico | Fechas importantes del año lectivo, por nivel. `[COMPLETAR — contenido nuevo, lo carga el editor académico]` | Calendario académico — Dante | Consulte el calendario académico del Colegio Dante Alighieri: fechas por nivel y próximos eventos. |
| Comunicados | `/vida-escolar/comunicados` | Comunicados | Avisos oficiales del colegio para las familias. | Comunicados — Colegio Dante Alighieri | Comunicados oficiales del Colegio Dante Alighieri para las familias de la comunidad educativa. |
| Eventos | `/vida-escolar/eventos` | Eventos | Actos, festejos y actividades del calendario escolar. | Eventos — Colegio Dante Alighieri | Próximos eventos y actividades del Colegio Dante Alighieri en Asunción. |
| Galería | `/vida-escolar/galeria` | Galería | Fotos de actos y actividades del colegio. | Galería de fotos — Colegio Dante Alighieri | Fotos de eventos y actividades del Colegio Dante Alighieri. |
| Biblioteca "Irene Borello de Amodei" | `/vida-escolar/biblioteca` | Biblioteca "Irene Borello de Amodei" | Más de 40 años de historia y una de las bibliotecas educativas más completas del país, de acceso público. Cuerpo completo en §2.1.3. | Biblioteca Irene Borello de Amodei — Dante | Conozca la biblioteca "Irene Borello de Amodei" del Colegio Dante Alighieri. |
| Enlaces de interés | `/vida-escolar/enlaces-de-interes` | Enlaces de interés | Instituciones, comunidad ítalo-paraguaya y recursos para el estudio del italiano. Cuerpo completo en §2.1.4. | Enlaces de interés — Colegio Dante Alighieri | Enlaces y recursos externos de interés para las familias del Colegio Dante Alighieri. |

**Nota:** "Galería" y "Documentos" fusionan las versiones "sede Asunción" y "sede Fernando de la
Mora" del sitio viejo; si aparece un filtro por sede en el panel, depende de
`[PENDIENTE: pregunta #14]`.

---

### Plantilla — Listado de noticias

- **URL:** `/noticias`
- **Audiencia principal:** padres actuales y evaluando, prensa
- **Objetivo:** encontrar la noticia más reciente o buscar una anterior

| Elemento | Texto |
|---|---|
| `h1` | Noticias |
| Bajada | Novedades y noticias del Colegio Dante Alighieri. |
| Filtro por categoría | Institucional · Cultural · Deportivo |
| Estado vacío del listado | Todavía no hay noticias publicadas en esta categoría. |
| SEO título | Noticias — Colegio Dante Alighieri |
| SEO descripción | Noticias y novedades del Colegio Dante Alighieri: actos, visitas y actividades institucionales. |

### Plantilla — Detalle de noticia

Aplicada a las 3 noticias con mayor relevancia institucional del inventario real (§3 tiene el
resto de las 13 piezas con destino/estado):

| Noticia | URL nueva | `h1` | Bajada / excerpt | SEO título (≤60) | SEO descripción (≤155) |
|---|---|---|---|---|---|
| La Scuola Dante Alighieri celebra su 129° aniversario con música y arte | `/noticias/129-aniversario-musica-y-arte` | La Scuola Dante Alighieri celebra su 129° aniversario | El colegio festejó 129 años de historia con un acto de música y arte. | 129° aniversario de la Scuola Dante Alighieri | El Colegio Dante Alighieri celebró su 129° aniversario con un acto de música y arte en Asunción. |
| Histórico: presidente de Italia, Sergio Mattarella, visita el Colegio Dante Alighieri | `/noticias/presidente-de-italia-visita-el-colegio` | El presidente de Italia visitó el Colegio Dante Alighieri | Sergio Mattarella visitó el colegio en una jornada histórica para la comunidad educativa. | Mattarella visita el Colegio Dante Alighieri | El presidente de Italia, Sergio Mattarella, visitó el Colegio Dante Alighieri en una jornada histórica. |
| Mercado navideño a la italiana en Asunción | `/noticias/mercado-navideno-a-la-italiana` | Mercado navideño a la italiana en Asunción | El colegio organizó un mercado navideño con productos y tradiciones de Italia. | Mercado navideño a la italiana — Dante | El Colegio Dante Alighieri organizó un mercado navideño a la italiana en Asunción. |

Estructura común de detalle: `h1` = título, fecha visible, imagen destacada, cuerpo, enlace
"Volver a Noticias" + 2-3 noticias relacionadas por categoría.

---

### Plantilla — Listado de documentos

- **URL:** `/documentos`
- **Audiencia principal:** padres actuales
- **Objetivo:** encontrar y descargar un documento sin usar el buscador

| Elemento | Texto |
|---|---|
| `h1` | Documentos |
| Bajada | Estatutos, circulares y formularios del colegio, para descargar. |
| Filtro | Por categoría (Estatutos, Circulares, Formularios) `[y por sede, condicionado a pregunta #14]` |
| Ítem de la lista | Nombre del documento + fecha + tipo de archivo (ej. "Estatutos sociales — PDF, 12/12/2023") |
| Estado vacío | No hay documentos publicados en esta categoría todavía. |
| SEO título | Documentos — Colegio Dante Alighieri |
| SEO descripción | Descargue estatutos, circulares y formularios del Colegio Dante Alighieri. |

---

### Contacto

- **URL:** `/contacto`
- **Audiencia principal:** todas — tarea #3 y #8 de `docs/02` §2
- **Objetivo:** que encuentren el dato de contacto directo o completen el formulario, sin
  scrollear por contenido de otras secciones
- **CTA principal:** "Enviar mensaje"

> Nota heredada del wireframe: la página actual tiene 4661 palabras — muy por encima de lo
> típico. El copy de esta página se acota a datos de contacto, mapa y formulario; qué otro
> contenido migrado (si lo hay) se reubica en otra sección depende de
> `[PENDIENTE: pregunta #19]`.

| Elemento | Texto |
|---|---|
| `h1` | Contacto |
| Bajada | Escríbanos o comuníquese directamente con la secretaría. |
| Etiqueta "Dirección" | `[COMPLETAR CON DATO REAL DE MIGRACIÓN]` |
| Etiqueta "Teléfono" | `[COMPLETAR CON DATO REAL DE MIGRACIÓN]` |
| Etiqueta "Email" | `[COMPLETAR CON DATO REAL DE MIGRACIÓN]` |
| Etiqueta "Horario de atención" | `[COMPLETAR CON DATO REAL DE MIGRACIÓN]` |
| Título del formulario | Escríbanos |
| SEO título | Contacto — Colegio Dante Alighieri |
| SEO descripción | Dirección, teléfono y formulario de contacto del Colegio Dante Alighieri en Asunción. |

---

### Búsqueda

- **URL:** `/buscar`
- **Objetivo:** encontrar página, noticia, documento o comunicado por palabra clave

| Elemento | Texto |
|---|---|
| `h1` | Resultados de búsqueda |
| Placeholder del campo | Buscar en el sitio |
| Texto con resultados | "{n} resultados para «{consulta}»" |
| Filtro por tipo | Todo · Páginas · Noticias · Documentos · Comunicados |
| SEO título | Buscar — Colegio Dante Alighieri |
| SEO descripción | Busque páginas, noticias, documentos y comunicados del Colegio Dante Alighieri. |

---

### 404

| Elemento | Texto |
|---|---|
| `h1` | Esta página no existe |
| Cuerpo | La dirección que buscó no está disponible. Puede haber cambiado de nombre o ya no existir. |
| CTA 1 | Ir al inicio |
| CTA 2 | Buscar en el sitio |
| SEO título | Página no encontrada — Colegio Dante Alighieri |
| SEO descripción | (con `noindex`, no se envía a buscadores) |

---

## 3. SEO — títulos y meta descripciones de las 39 piezas reales

A partir del inventario de `docs/01-analisis-descubrimiento.md` §C.2. URL nueva alineada con
`docs/redirecciones-301.csv` (fuente de verdad del mapa 301 — si hay una diferencia entre esa
tabla y esta, gana el CSV). Las filas marcadas con estado "archivado" o "revisar" llevan su
`[PENDIENTE: pregunta #N]` correspondiente en vez de un SEO definitivo, porque publicar un SEO
para contenido que puede terminar dado de baja es trabajo que se puede tirar.

**Páginas (26):**

| URL vieja | URL nueva | SEO título (≤60) | SEO descripción (≤155) |
|---|---|---|---|
| /acerca-de-la-sociedad/ | /institucion/sociedad-dante-alighieri | Società Dante Alighieri — afiliación del colegio | El Colegio Dante Alighieri está afiliado a la Società Dante Alighieri de Roma. Conozca qué implica esa afiliación. |
| /administracion/ | /institucion/administracion | Administración — Colegio Dante Alighieri | Conozca la administración del Colegio Dante Alighieri, colegio bilingüe en Asunción. |
| /autoridades/ | /institucion/autoridades | Autoridades — Colegio Dante Alighieri | Conozca las autoridades del Colegio Dante Alighieri, colegio bilingüe afiliado a la Società Dante Alighieri. |
| /biblioteca-irene-borello-de-amodei/ | /vida-escolar/biblioteca | Biblioteca Irene Borello de Amodei — Dante | Conozca la biblioteca "Irene Borello de Amodei" del Colegio Dante Alighieri en Asunción. |
| /certificacion-internacional/ | /institucion/certificacion-internacional | Certificación PLIDA — Colegio Dante Alighieri | El Colegio Dante Alighieri ofrece la certificación internacional PLIDA de italiano. |
| /contacto/ | /contacto | Contacto — Colegio Dante Alighieri | `[PENDIENTE: pregunta #19]` |
| /cursos-de-italiano/ | /oferta-educativa/cursos-de-italiano | Cursos de Italiano — Colegio Dante Alighieri | Cursos de italiano con certificación internacional PLIDA en el Colegio Dante Alighieri. |
| /descarga-de-documentos/ | /documentos | Documentos — Colegio Dante Alighieri | Descargue estatutos, circulares y formularios del Colegio Dante Alighieri. |
| /descarga-documentos-fndo-de-la-mora/ | /documentos | Documentos — Colegio Dante Alighieri | `[PENDIENTE: pregunta #14]` |
| /enlaces-de-interes/ | /vida-escolar/enlaces-de-interes | Enlaces de interés — Colegio Dante Alighieri | Enlaces y recursos externos de interés para las familias del Colegio Dante Alighieri. |
| /estatutos-sociales/ | /institucion/estatutos-sociales | Estatutos sociales — Colegio Dante Alighieri | Descargue los estatutos sociales del Colegio Dante Alighieri en formato PDF. |
| /eventos/ | /vida-escolar/eventos | Eventos — Colegio Dante Alighieri | Próximos eventos y actividades del Colegio Dante Alighieri en Asunción. |
| /formulacion-de-pre-inscripcion/ | /admisiones/pre-inscripcion | Pre-inscripción — Colegio Dante Alighieri | Complete la pre-inscripción de su hijo/a en el Colegio Dante Alighieri, sede Asunción. |
| /formulario-de-pre-inscripcion-sede-fernando-de-la-mora/ | /admisiones/pre-inscripcion-fernando-de-la-mora | `[PENDIENTE: pregunta #14]` | `[PENDIENTE: pregunta #14]` |
| /galeria/ | /vida-escolar/galeria | Galería de fotos — Colegio Dante Alighieri | Fotos de eventos y actividades del Colegio Dante Alighieri. |
| /galeria-sede-fndo/ | /vida-escolar/galeria | Galería de fotos — Colegio Dante Alighieri | `[PENDIENTE: pregunta #14]` |
| /historia/ | /institucion/historia | Historia del Colegio Dante Alighieri | La historia del Colegio Dante Alighieri: 129 años de trayectoria y la visita del presidente de Italia en 2024. |
| /inicio/ | / (redirección a home) | — | — (redirige, no indexa contenido propio) |
| /inscripciones-2/ | /admisiones | Admisiones — Colegio Dante Alighieri | Cómo inscribir a su hijo en el Colegio Dante Alighieri. Requisitos y formulario de pre-inscripción. |
| /instituto-de-lengua-y-cultura/ | /oferta-educativa/instituto-de-lengua-y-cultura | Instituto de Lengua y Cultura — Dante | El Instituto de Lengua y Cultura del Colegio Dante Alighieri ofrece cursos de italiano para todas las edades. |
| /invitaciones/ | archivado, URL propia (fuera de menú) | `[PENDIENTE: pregunta #16]` | `[PENDIENTE: pregunta #16]` |
| /mision-vision-objetivos-y-valores/ | /institucion/mision-vision-valores | Misión, visión y valores — Dante | Misión, visión y valores del Colegio Dante Alighieri, colegio bilingüe en Asunción. |
| /noticias/ | /noticias | Noticias — Colegio Dante Alighieri | Noticias y novedades del Colegio Dante Alighieri: actos, visitas y actividades institucionales. |
| /novedades/ | /noticias (fusionado) | Noticias — Colegio Dante Alighieri | Noticias y novedades del Colegio Dante Alighieri: actos, visitas y actividades institucionales. |
| /pagina-ejemplo/ | / (redirección a home) | `[PENDIENTE: pregunta #15]` | `[PENDIENTE: pregunta #15]` |
| /quienes-somos/ | /institucion/quienes-somos | Quiénes somos — Colegio Dante Alighieri | Conozca la identidad del Colegio Dante Alighieri: bilingüe, afiliado a la Società Dante Alighieri, 129 años de trayectoria. |

**Entradas / noticias (13):**

| URL vieja | URL nueva | SEO título (≤60) | SEO descripción (≤155) |
|---|---|---|---|
| /la-scuola-dante-alighieri-celebra-su-129-aniversario-con-musica-y-arte/ | /noticias/129-aniversario-musica-y-arte | 129° aniversario de la Scuola Dante Alighieri | El Colegio Dante Alighieri celebró su 129° aniversario con un acto de música y arte en Asunción. |
| /mercado-navideno-a-la-italiana-en-asuncion/ | /noticias/mercado-navideno-a-la-italiana | Mercado navideño a la italiana — Dante | El Colegio Dante Alighieri organizó un mercado navideño a la italiana en Asunción. |
| /historico-presidente-de-italia-sergio-mattarella-visita-colegio-dante-alighieri/ | /noticias/presidente-de-italia-visita-el-colegio | Mattarella visita el Colegio Dante Alighieri | El presidente de Italia, Sergio Mattarella, visitó el Colegio Dante Alighieri en una jornada histórica. |
| /comunicado-05052020/ | /vida-escolar/comunicados/comunicado-05-05-2020 | `[PENDIENTE: pregunta #18]` | `[PENDIENTE: pregunta #18]` |
| /domenica-di-sapori-italiani-feria-familiar-de-la-gastronomia-italiana/ | archivado, URL propia | Domenica di Sapori Italiani (archivo) — Dante | Archivo: feria de gastronomía italiana organizada por el Colegio Dante Alighieri en 2017. |
| /concierto-de-piano-de-mateo-servian-sforza/ | archivado, URL propia | Concierto de piano — Dante (archivo) | Archivo: concierto de piano organizado por el Colegio Dante Alighieri en 2017. |
| /convenio-con-la-asociacion-de-ex-alumnos-del-colegio-dante-alighieri/ | /institucion/convenio-ex-alumnos | `[PENDIENTE: pregunta #17]` | `[PENDIENTE: pregunta #17]` |
| /semana-de-la-juventud-y-fiesta-de-la-primavera-tematica-hippiechic/ | archivado, URL propia | `[PENDIENTE: pregunta #16]` | `[PENDIENTE: pregunta #16]` |
| /invitacion/ | archivado, URL propia | `[PENDIENTE: pregunta #16]` | `[PENDIENTE: pregunta #16]` |
| /festejo-del-dia-del-folklore-nivel-inicial/ | archivado, URL propia | `[PENDIENTE: pregunta #16]` | `[PENDIENTE: pregunta #16]` |
| /noticia-de-prueba-3/ | / (redirección a home) | `[PENDIENTE: pregunta #15]` | `[PENDIENTE: pregunta #15]` |
| /noticia-de-prueba-2/ | / (redirección a home) | `[PENDIENTE: pregunta #15]` | `[PENDIENTE: pregunta #15]` |
| /noticia-de-prueba-1/ | / (redirección a home) | `[PENDIENTE: pregunta #15]` | `[PENDIENTE: pregunta #15]` |

---

## 4. Microcopy

| Contexto | Texto |
|---|---|
| Botón de envío de formulario (contacto) | Enviar mensaje |
| Botón de envío de formulario (pre-inscripción) | Enviar pre-inscripción |
| Formulario enviado con éxito (contacto) | Recibimos su mensaje. Le respondemos a la brevedad. |
| Formulario enviado con éxito (pre-inscripción) | Recibimos su pre-inscripción. La secretaría lo contacta para coordinar la entrega de documentación en el colegio. |
| Error de validación genérico | Revise los datos marcados antes de enviar el formulario. |
| Campo obligatorio vacío | Este campo es obligatorio. |
| Email inválido | Escriba un email válido, por ejemplo nombre@dominio.com. |
| Teléfono inválido | Escriba un número de teléfono válido. |
| Captcha fallido | No pudimos confirmar que usted no es un robot. Vuelva a intentar. |
| Error de envío (falla del servidor) | No pudimos enviar el formulario. Intente de nuevo en unos minutos o escríbanos a `[email institucional]`. |
| Buscador: placeholder | Buscar en el sitio |
| Buscador: sin resultados | No encontramos resultados para «{consulta}». Pruebe con otra palabra. |
| Listado vacío (noticias) | Todavía no hay noticias publicadas en esta categoría. |
| Listado vacío (documentos) | No hay documentos publicados en esta categoría todavía. |
| Listado vacío (comunicados) | No hay comunicados vigentes por el momento. |
| Listado vacío (eventos/calendario) | No hay eventos cargados para este mes. |
| Listado vacío (galería) | Todavía no hay fotos publicadas en esta sección. |
| Cargando | Cargando… |
| 404 — título y texto | "Esta página no existe" / "La dirección que buscó no está disponible. Puede haber cambiado de nombre o ya no existir." |
| 500 — título y texto | "Algo salió mal de nuestro lado" / "Estamos trabajando para solucionarlo. Intente de nuevo en unos minutos." |
| Banner de cookies | Usamos cookies para mejorar su experiencia y medir el uso del sitio. Puede aceptarlas o configurarlas. |
| Banner de cookies — botones | Aceptar todas · Configurar · Rechazar no esenciales |
| Enlace "saltar al contenido" | Saltar al contenido principal |
| Aria-label del menú móvil (cerrado) | Abrir menú de navegación |
| Aria-label del menú móvil (abierto) | Cerrar menú de navegación |
| Aria-label del selector de idioma | Cambiar idioma del sitio |
| Aria-label del ícono de búsqueda | Buscar en el sitio |
| Aria-label de las migas de pan | Ruta de navegación |
| Alt de imagen decorativa (sin valor informativo) | `alt=""` (vacío, explícito) |
| Placeholder de campo "Nombre completo" | (sin placeholder — la etiqueta visible ya dice qué va ahí, ver §1 reglas de estilo) |
| Descarga de documento — enlace | Descargar (PDF) |
| Documento vencido/archivado | Este documento ya no está vigente. |

---

## 5. Textos legales

> ⚠️ **Ninguno de los tres textos de abajo se publica sin revisión de un abogado y aprobación
> explícita del cliente.** Son un borrador de trabajo con la estructura y el tono correctos, no
> un documento legal listo para producción — Dante trata datos personales de contacto de adultos
> (padres) y eso ya alcanza para requerir una política de privacidad seria, aunque no haya datos
> de menores online.

### 5.1 Sobre el alcance real (para que el borrador no invente ni omita)

- El sitio actual **no procesa datos personales de menores en línea**: la inscripción es 100 %
  presencial en papel (cédula, certificado de nacimiento, boletín, fotos) — confirmado en
  `docs/01-analisis-descubrimiento.md` §E pregunta #3. El único dato que el sitio recoge online
  son los datos de contacto de un adulto (nombre, teléfono, email) a través de los formularios de
  contacto y de pre-inscripción.
- El sitio anterior tuvo un incidente de seguridad confirmado (`docs/01` §C.6: webshell activa,
  usuarios administradores falsos). El aviso de privacidad **no necesita relatar el incidente**
  (no es una obligación legal automática sin asesoría que confirme si aplica una notificación de
  brecha bajo la ley paraguaya de protección de datos), pero si hubo datos de contacto expuestos
  en ese período, **eso es una decisión que tiene que tomar el cliente con asesoría legal**, no
  el copywriting. Se deja marcado acá para que no se pierda.
- El destino actual de los formularios de pre-inscripción es un mail de una agencia externa
  (`comunica@pressencia.com.py`), no de Dante — `[PENDIENTE: pregunta #20]`. La política de
  privacidad final tiene que nombrar correctamente a quién se envían los datos, así que este
  texto no se cierra hasta que se resuelva esa pregunta.

### 5.2 Política de privacidad (borrador — pendiente de revisión legal y del cliente)

**Qué datos recogemos**

Cuando usted completa un formulario en este sitio (contacto o pre-inscripción), recogemos su
nombre, teléfono y email. No pedimos datos del alumno a través del sitio: la inscripción se
completa en forma presencial en el colegio, con la documentación correspondiente.

**Para qué los usamos**

Para responder su consulta o coordinar la pre-inscripción presencial. No usamos estos datos con
fines distintos a los que motivaron el contacto, salvo que usted dé su consentimiento explícito
para otro uso (por ejemplo, recibir novedades por email).

**Cuánto tiempo los conservamos**

`[PENDIENTE: definir con el cliente — plazo de conservación de los envíos de formularios]`

**Con quién los compartimos**

`[PENDIENTE: pregunta #20 — depende de si el destino de los formularios sigue siendo la agencia externa o pasa a ser propio del colegio]`

**Cómo ejercer sus derechos**

Puede pedir acceder, corregir o eliminar sus datos escribiendo a `[email institucional a
confirmar]`.

**Cookies y analítica**

Ver política de cookies (§5.3).

### 5.3 Política de cookies (borrador — pendiente de revisión legal y del cliente)

Este sitio usa cookies propias, necesarias para su funcionamiento (por ejemplo, recordar el
idioma elegido), y cookies de terceros para medir el uso del sitio y mostrar publicidad relevante
(Google Analytics, Meta Pixel — a implementar en Fase 6). Usted puede aceptar todas las cookies,
configurarlas por categoría o rechazar las no esenciales desde el banner que aparece en su
primera visita, o en cualquier momento desde `[enlace "Configurar cookies" en el pie de página]`.

| Categoría | Para qué | ¿Se puede rechazar? |
|---|---|---|
| Necesarias | Idioma elegido, sesión del formulario, protección contra bots | No — son indispensables para que el sitio funcione |
| Analítica | Medir visitas y páginas más vistas (Google Analytics) | Sí |
| Publicidad | Medir campañas (Meta Pixel) | Sí |

### 5.4 Aviso legal / términos de uso (borrador — pendiente de revisión legal y del cliente)

Este sitio es propiedad del Colegio Dante Alighieri, `[razón social exacta a confirmar]`. El
contenido publicado tiene fines informativos. El uso del logotipo y la marca "Dante" está sujeto
a la titularidad de la Società Dante Alighieri (Roma), según el convenio de afiliación vigente
(`docs/01` §B.2). Queda prohibida la reproducción del contenido de este sitio sin autorización.

### 5.5 Aviso específico sobre datos de menores

No aplica hoy: **el sitio no recoge datos personales de menores en línea** — la inscripción es
100 % presencial en papel (`docs/01` §E pregunta #3). Este apartado se activa recién si en el
futuro se habilita algún formulario que pida datos del alumno (fuera de alcance de esta primera
entrega, ver `CLAUDE.md` §1) — en ese momento sí hace falta un aviso específico redactado con
asesoría legal, no antes.

---

## 6. Alt text

No hubo acceso visual a las imágenes reales de `_insumos/03-wordpress-actual/uploads/` en esta
sesión (128 medios: 104 JPEG, 22 PNG, 1 GIF, 1 PDF — `docs/01` §C.1). El alt text de cada imagen
concreta se redacta **cuando se cargue en el panel** (Fase 3/5), siguiendo el criterio de esta
tabla — no se inventa una descripción visual que no se puede verificar.

| Contexto de la imagen | Criterio de alt text |
|---|---|
| Logo en el header | `Colegio Dante Alighieri` (nunca "logo" ni "imagen de") |
| Logo en el pie de página (versión completa, con banderas) | `Colegio Dante Alighieri — afiliado a la Società Dante Alighieri` |
| Imagen de portada de una noticia | Describir la escena real de la foto en relación al título de la noticia (ej. para "129° aniversario": "Acto por el 129° aniversario del Colegio Dante Alighieri" — se completa al cargar la foto real, no antes) |
| Fotos de galería de eventos | `{Nombre del evento} — {fecha}`, ej. "Mercado navideño a la italiana — diciembre 2023" |
| Imagen decorativa (fondos, texturas, formas de marca sin información) | `alt=""` explícito, nunca se omite el atributo |
| Documento descargable (ícono de PDF) | `Descargar {nombre del documento} en PDF` |
| Fotos de autoridades/personal | `{Nombre y cargo}` si el cliente autoriza publicar nombres — si no, `Autoridad del Colegio Dante Alighieri` |
| Mapa embebido de Contacto | `Ubicación del Colegio Dante Alighieri en el mapa` |
| Imagen hero de la home | Describir la escena real (fachada, acto institucional) una vez cargada la foto definitiva — hoy no hay foto elegida (`docs/01` §E pregunta #9: no hay material fotográfico nuevo, se usa el existente) |

**Regla general:** el alt text describe qué se ve y para qué sirve en el contexto de esa página,
en máximo una oración. Nunca empieza con "Imagen de" ni "Foto de" (es redundante con la etiqueta
`<img>`). Nunca se deja vacío salvo que la imagen sea decorativa.

---

## 7. Reglas de estilo del contenido

- Frases cortas. Un párrafo, una idea.
- Los títulos dicen qué hay abajo, no son ingeniosos.
- Nada de "Bienvenidos a nuestro sitio web" ni fórmulas de apertura genéricas.
- Los CTA empiezan con verbo y dicen qué pasa al hacer clic ("Complete la pre-inscripción",
  no "Conocer más").
- Las cifras se escriben con números (129 años, 2024), salvo al inicio de una oración.
- Nada de mayúsculas sostenidas fuera del logotipo.
- Tratamiento: **usted**, siempre — nunca "vos" (ver §1).
- El nombre de la institución: **"Colegio Dante Alighieri"** en la primera mención de cada
  página, **"Dante"** en las menciones siguientes de la misma página. **"Società Dante
  Alighieri"** se reserva para nombrar a la entidad de Roma cuando se habla específicamente de la
  afiliación — nunca como sinónimo del colegio local. No se usa "Scuola Dante Alighieri" en copy
  nuevo (aparece solo en contenido migrado citado textualmente, como el título de la noticia del
  129° aniversario).
- Fechas en el formato "12 de marzo de 2026" en el cuerpo del texto; `dd/mm/aaaa` solo en tablas,
  fichas de documentos y metadatos donde el espacio es limitado.
- Nada de adjetivos sin respaldo ("excelencia académica", "educación de calidad") — si no hay un
  dato detrás, no se afirma.
- Nada de Lorem ipsum, nunca, ni siquiera en maquetas de trabajo interno.

---

## 8. Fuente del contenido

| Página | ¿De dónde sale el texto? | Responsable | Estado |
|---|---|---|---|
| Inicio | nuevo (plantilla propia por bloques, `docs/02` §3 nota de arquitectura) | Copywriting (este documento) | Redactado |
| Institución (landing) | nuevo (agregador) | Copywriting | Redactado |
| Quiénes somos | migrado (`/quienes-somos/`) | Copywriting + carga desde `dante_wp_legacy` en Fase 5 | Estructura redactada, cuerpo pendiente de carga |
| Historia | migrado (`/historia/`, 3277 palabras) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Misión, visión y valores | migrado (`/mision-vision-objetivos-y-valores/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Autoridades | migrado (`/autoridades/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Società Dante Alighieri | migrado (`/acerca-de-la-sociedad/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Certificación internacional | migrado (`/certificacion-internacional/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Estatutos sociales | migrado (`/estatutos-sociales/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Administración | migrado (`/administracion/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Convenio con Ex Alumnos | migrado (`/convenio-con-la-asociacion-de-ex-alumnos.../`), sujeto a #17 | ídem | Bloqueado por `[PENDIENTE: pregunta #17]` |
| Oferta educativa (landing) | nuevo (agregador) | Copywriting | Redactado |
| Instituto de Lengua y Cultura | migrado (`/instituto-de-lengua-y-cultura/`) | Copywriting + Fase 5 | Estructura redactada, cuerpo pendiente de carga |
| Cursos de Italiano | migrado (`/cursos-de-italiano/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Admisiones (landing) | migrado (`/inscripciones-2/`, 5206 palabras, contenido activo) + nuevo (agregador) | Copywriting + Fase 5 | Estructura redactada, cuerpo pendiente de carga |
| Pre-inscripción | migrado (`/formulacion-de-pre-inscripcion/`) | ídem | Estructura + microcopy redactados |
| Pre-inscripción Fernando de la Mora | migrado, sujeto a #14 | ídem | Bloqueado por `[PENDIENTE: pregunta #14]` |
| Vida escolar (landing) | nuevo (agregador) | Copywriting | Redactado |
| Calendario académico | nuevo (contenido nuevo, lo carga el editor académico) | Cliente (editor académico) | Estructura redactada, contenido lo provee el cliente |
| Comunicados | nuevo (tipo de contenido) + migrado (comunicado de 2020, sujeto a #18) | Cliente / Copywriting | Estructura redactada |
| Eventos | migrado (`/eventos/`) | Copywriting + Fase 5 | Estructura redactada, cuerpo pendiente de carga |
| Galería | migrado (`/galeria/` + `/galeria-sede-fndo/`, fusión sujeta a #14) | ídem | Estructura redactada |
| Biblioteca "Irene Borello de Amodei" | migrado (`/biblioteca-irene-borello-de-amodei/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Enlaces de interés | migrado (`/enlaces-de-interes/`) | ídem | Estructura redactada, cuerpo pendiente de carga |
| Noticias (listado) | migrado (fusión `/noticias/` + `/novedades/`) | Copywriting | Redactado |
| 3 noticias destacadas | migrado (ver §2/§3) | Copywriting | Redactado (excerpt); cuerpo completo pendiente de carga |
| Documentos (listado) | migrado (fusión `/descarga-de-documentos/` + sede Fdo. de la Mora, sujeta a #14) | Copywriting | Redactado |
| Contacto | migrado (`/contacto/`, 4661 palabras, sujeto a revisión) | Copywriting, bloqueado por #19 | Estructura redactada, datos de contacto y alcance final pendientes de `[PENDIENTE: pregunta #19]` |
| Búsqueda | nuevo (plantilla funcional) | Copywriting | Redactado |
| 404 | nuevo (plantilla funcional) | Copywriting | Redactado |
| Textos legales | nuevo, con base en el alcance real del sitio | Copywriting, **revisión legal obligatoria antes de publicar** | Borrador — no publicable sin revisión |
| Alt text | criterio, no texto final (sin acceso visual a las imágenes) | Se completa al cargar cada medio en el panel | Criterio definido |

---

## 9. Resumen de placeholders pendientes

Todo lo que este documento no pudo cerrar, en un solo lugar:

- **`[PENDIENTE: pregunta #14]`** — vigencia de la sede Fernando de la Mora. Afecta: Documentos,
  Galería, Pre-inscripción sede Fernando de la Mora, Comunicados "dirigido a".
- **`[PENDIENTE: pregunta #15]`** — baja de contenido de instalación/prueba de WordPress (Página
  de ejemplo, 3 noticias de prueba).
- **`[PENDIENTE: pregunta #16]`** — destino de 4 piezas de eventos puntuales de bajo contenido
  (2016/2023).
- **`[PENDIENTE: pregunta #17]`** — vigencia del convenio con la Asociación de Ex Alumnos.
- **`[PENDIENTE: pregunta #18]`** — destino del comunicado de la pandemia (05/05/2020).
- **`[PENDIENTE: pregunta #19]`** — contenido real de Contacto (4661 palabras, mezcla probable).
- **`[PENDIENTE: pregunta #20]`** — destino real de los envíos de pre-inscripción (agencia
  externa vs. propio).
- **`[COMPLETAR CON CONTENIDO MIGRADO]`** — no es una pregunta al cliente: es el cuerpo definitivo
  de las páginas institucionales (Autoridades, Administración, Historia, Biblioteca, Enlaces de
  interés, Institución en general) que solo existe en el HTML de `dante_wp_legacy` y no se pudo
  consultar en esta sesión por falta de acceso a un intérprete de comandos. Se resuelve en la
  Fase 5 (migración), reescribiendo ese texto real con el tono de §1, no publicando el HTML de
  Divi tal cual.
