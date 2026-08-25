# Notas internas — COT-2026-0160 · Segar

> **No se publica.** Documento de trabajo: qué se tomó del programa del
> operador, qué se agregó, qué se corrigió y qué sigue pendiente.

- **Cliente:** Segar · **4 pasajeros**
- **Programa:** San Pedro de Atacama, martes 22 a sábado 26 de **diciembre** de 2026
- **Emitida:** 24 ago 2026 · vigencia 07 sep 2026

## Entregables

| Archivo | Qué es |
|---|---|
| `Tourevo-COT-2026-0160-Segar-ES.pdf` · `-EN.pdf` | Cotización, 7 páginas |
| `Tourevo-COT-2026-0160-Segar-Dias-y-horarios-ES.pdf` · `-EN.pdf` | Comparativo contra el itinerario del sistema Tourevo, 5 páginas |
| `Tourevo-COT-2026-0160-Segar-Itinerario-propuesto-ES.pdf` · `-EN.pdf` | Itinerario propuesto para mandarle al operador, 4 páginas |
| `Tourevo-COT-2026-0160-Segar-Itinerario-para-operador.xlsx` | Planilla para mandarle al operador, con dos columnas en blanco para que responda. 3 hojas |

---

## Qué se agregó respecto del programa recibido

| # | En el correo del operador | En la cotización | Por qué |
|---|---|---|---|
| 1 | Cada tour con su "Valor por persona" y, aparte, "Entradas a parques" | **Subtotal por tour** = servicio + entradas, en la ficha de cada excursión y en la tabla resumen | Era el pedido explícito. Además es el número que el pasajero necesita para decidir tour por tour |
| 2 | "Total del programa por persona CLP 725.000 / Valores no incluyen entradas" | **CLP 809.500 por persona** (725.000 + 84.500 de entradas) y **CLP 3.238.000 para 4 pax** | El total de 725.000 dejaba fuera 84.500 de entradas obligatorias: un 11,7% del gasto real quedaba invisible. Se muestran los tres números, no se reemplaza ninguno |
| 3 | Subtotal por día: no había | Cada día del itinerario muestra su subtotal por persona | Es la unidad con la que el pasajero razona |
| 4 | Alternativas sueltas, sin comparación | Cada alternativa muestra su subtotal y el **efecto sobre el total del pack** | Sin el delta hay que hacer la resta a mano para poder elegir |
| 5 | Un solo texto en español | Dos versiones completas, ES y EN, del mismo `contenido.mjs` | Los montos se calculan una vez y se renderizan dos: no pueden discrepar |

## Correcciones sobre la primera versión

1. **Las fechas son de diciembre, no de septiembre.** La primera versión las fechó en septiembre 2026 porque los días de la semana del correo (martes 22 … sábado 26) calzaban con ese mes. Diciembre 2026 tiene exactamente el mismo patrón, y el itinerario del cliente lo confirma: el viaje es del **22 al 26 de diciembre**. Los nombres de los días no cambian; sí cambian la temporada (alta) y los feriados (24 y 25).
2. **4 pasajeros, no 2.** Los tours van por persona, así que el valor por persona no se mueve. Lo que sí: las alternativas 1 y 2 estaban cotizadas como **tarifa mínima de grupo sobre base 2 pax** y no se pueden dividir entre 4. El documento las muestra como referencia sobre base 2, marcadas para recotizar, y no inventa un efecto sobre el total.
3. **Nochebuena y Navidad** quedaron advertidas en las condiciones.

## Aritmética verificada

| Concepto | Monto |
|---|---|
| Servicios (9 ítems) | CLP 725.000 — **coincide con el total del correo** |
| Entradas (Puritama 35.000 + Marte 7.500 + Cejar 21.000 + Piedras Rojas 15.000 + Quitor 6.000) | CLP 84.500 |
| **Total del pack por persona** | **CLP 809.500** |
| Total para 4 pax | CLP 3.238.000 |

Efecto de cada alternativa sobre el pack (base jueves AM: CLP 117.500 p/p):

| Alternativa | Subtotal | Efecto |
|---|---|---|
| 1 · Trekking Cascadas de Vilama | CLP 210.000 mín. 2 pax (105.000 p/p de referencia) | A recotizar para 4 pax |
| 2 · Trekking Cornisas + Valle de Marte | CLP 270.000 mín. 2 pax (141.000 p/p de referencia) | A recotizar para 4 pax |
| 3 · Bike Valle de Catarpe | CLP 105.000 p/p | −12.500 → pack 797.000 |

## Comparativo contra el itinerario del sistema Tourevo

La primera versión de este comparativo se hizo contra un export suelto y quedó
mal en tres puntos: decía que Puritama faltaba (está el 24), que faltaba el
traslado de salida (está el 26 a las 10:24) y marcaba dos bloqueos que el
sistema ya tenía resueltos. El comparativo actual se hace contra el **itinerario
cargado en sistema, que ya se vio con Segar**, y es el que vale.

De los nueve servicios cotizados, ocho están en el sistema y **ninguno coincide
en día y hora a la vez**:

| Diferencia | Servicios | Valor |
|---|---|---|
| Se fija la hora que la cotización dejaba abierta | 2 (los dos traslados) | CLP 60.000 |
| Mismo día, cambia el horario | 4 (Valle de la Luna, Valle de la Muerte, full day, Quitor) | CLP 498.500 |
| Corre de día | 2 (Puritama 23→24, astronómico 23→24) | CLP 150.000 |
| No está en el sistema | 1 (Cejar + Tebenquiche) | CLP 101.000 |

Ya resuelto en el sistema, no hay que volver a tocarlo: la aclimatación quedó el
23 y no el 22, y el Valle del Arcoíris salió del día de Piedras Rojas.

Dos choques de horario del propio sistema hay que destrabarlos para que el 24
sea operable:

1. **Puritama termina 13:30 y el Valle de la Muerte empieza 13:30.** Las termas
   están a ~30 km. Propuesta: correr el Valle de la Muerte a la tarde libre,
   15:30–17:30 — resuelve el choque y saca los quads de la hora de más calor.
2. **Cejar y el Valle de la Muerte no caben los dos el 24.** Si Cejar entra, la
   tarde del 23 es el único hueco donde cabe completo. Antes de recotizarlo,
   revisar los 11 servicios quitados que el sistema marca como restaurables.

Menores: 15 minutos entre la cena de Nochebuena y la astronomía del 24, y 24
minutos entre Quitor y el traslado del 26 (se arregla haciendo el check-out
antes de salir a Quitor).

**El movimiento de Puritama al 24 parece deliberado y bien pensado**: deja el 23
para aclimatar a 2.400 m antes de subir a 3.500 m, con Miscanti sobre 4.000 m el
25. No conviene devolverlo al 23 sólo para descomprimir.

## Itinerario propuesto para el operador

Documento para pedirle al operador que confirme horarios. Respeta el itinerario
que el cliente ya vio: de 23 bloques del día, **sólo se tocan 3**.

| Cambio | Detalle |
|---|---|
| Valle de la Muerte, 24 dic | De 13:30–15:30 a **15:30–17:30**, ocupando la tarde libre que el itinerario ya tenía. Resuelve el choque con Puritama y saca el sandboard de la hora de más calor |
| Cejar, 23 dic | **Nuevo, 13:30–16:30**, versión de 3 h sin la espera del atardecer en Tebenquiche. Es el único hueco donde entra completo |
| Check-out, 26 dic | **Nuevo bloque 07:45–08:15**. Con el equipaje cargado, el traslado sale directo desde el pukará y los 24 minutos entre Quitor y las 10:24 dejan de importar |

Todo lo demás — traslados, Puritama, Valle de la Luna, astronomía, el full day,
Quitor, vuelos y comidas — queda en el día y la hora exactos que vio Segar.

**Luna llena toda la estadía.** Calculado: del 22 al 26 de diciembre la luna va
entre 92% y 100% iluminada, y la noche del 24 está en 99,4%. Ninguna noche del
viaje es mejor, así que no se arregla moviéndola. Va como pregunta al operador:
en un tour astronómico privado con esa luna, o se reorienta a luna y planetas o
no vale la pena. Es el tipo de cosa que conviene que el cliente sepa antes y no
después.

## Itinerario final propuesto

`itinerario.mjs` es la fuente única: la usan el PDF (ES/EN) y la planilla, así
que los horarios no pueden quedar distintos entre documentos. **19 bloques, 8
servicios nuestros, ningún día con más de dos salidas.**

| Día | Programa |
|---|---|
| Mar 22 | Vuelo Santiago → Calama 15:33 · traslado 18:15 – 19:15 · llegada al hotel 19:15, noche libre |
| Mié 23 | **Cejar 10:15 – 14:15** · tarde libre · Valle de la Luna 17:30 – 20:45 |
| Jue 24 | Puritama 09:30 – 13:30 · **Quitor + Valle de la Muerte 14:30 – 18:30** · cena 20:00 · astronomía 22:15 |
| Vie 25 | Altiplano 08:00 – 18:00 · cena 20:00 |
| Sáb 26 | **Libre.** Check-out, traslado 10:24 – 11:44, vuelo 13:44 |

Las cuatro decisiones que hubo que tomar:

1. **Cejar reemplaza la aclimatación del 23.** Salida 10:15 para estar a las 10:50
   en el complejo, como se pidió. Ordena además la altura: 2.300 m el 23,
   3.500 m el 24 en Puritama, sobre 4.000 m el 25 en Miscanti. El complejo cierra
   los martes y el único martes del viaje es el 22, día de llegada sin excursiones.
2. **Quitor y el Valle de la Muerte van juntos la tarde del 24.** Están a quince
   minutos uno del otro, es un vehículo en vez de dos salidas, y es lo que el
   cliente ya había decidido. El sandboard queda al final, 16:35 – 18:00, con la
   luz y la temperatura de la tarde.
3. **El 26 queda libre**, que era el pedido. Sacar Quitor de ahí es justamente lo
   que lo permite.
4. **El Valle de la Luna del 23 no se toca.** Con Cejar por la mañana, la tarde
   queda con tres horas de margen y el atardecer sigue en su hora.

El Valle del Arcoíris no cabe en ninguna parte con el 26 libre. Queda como
pregunta al operador para que el cliente decida con el dato a la vista, no como
algo que se descartó en silencio.

## Planilla para el operador

`build-excel.py` genera el .xlsx leyendo el itinerario de `itinerario.mjs` y los
precios de `contenido.mjs`. Tres hojas:

- **Itinerario** — una línea por bloque. La columna **Nº** numera en teal los
  ocho servicios que operamos nosotros, así que se cuentan de un vistazo y no
  hay forma de saltarse uno; las filas sin número son vuelos, comidas o tiempo
  libre. Después, «Qué necesitamos confirmar» y dos columnas en amarillo para
  que el operador responda: **¿Factible?** y **Comentarios**. Trae una fila de
  ejemplo que hay que borrar.
- **Preguntas** — las cuatro transversales, con su columna de respuesta.
- **Referencia** — qué cambió, los nueve ítems cotizados con **dónde cae cada
  uno en el itinerario**, y de dónde sale cada dato.

`build-excel.py` **falla si algún servicio cotizado no aparece en el itinerario**,
si aparece dos veces, o si un servicio nuestro se queda sin texto de
confirmación. Se agregó después de que Puritama quedara con la columna de
confirmación vacía y pareciera que no estaba en la planilla: estaba, pero sin
nada que la señalara. Cada bloque de `itinerario.mjs` declara en `cot` qué ítems
de la cotización cubre, y el build cruza esa lista contra `contenido.mjs`.

El comparativo de días y horarios (`build-diferencias.mjs`) sigue siendo el
registro de cómo se llegó acá, pero **el plan vigente es este itinerario**.

## Pendientes

0. **La cotización todavía refleja los días del operador, no los del sistema.** Se dejó así a propósito: el comparativo es el puente. Una vez resueltos Cejar, sandboard-vs-quads y el horario del Valle de la Muerte, se realinea `contenido.mjs` y se regenera.
1. **Condiciones de pago y cancelación.** No venían en el programa del operador y **no se inventaron**. Quedan fuera del documento a propósito: se piden al operador y recién ahí se agregan.
2. **Fotos de los vehículos.** No estaban disponibles al generar los PDF. La sección de flota se arma igual, con las fichas de los dos vehículos como tira compacta, y el texto avisa que las fotos van adjuntas. El camino con fotos está probado: dejarlas en `vehiculos/` (ver `LEEME.md`) y volver a correr el build. Con 4 pax la ficha de capacidad dejó de ser decorativa: la Tahoe entra cómoda con equipaje, la 4Runner queda al límite en el full day.
3. **Erratum del correo original.** El trekking de Vilama venía con "Altitud mínima: 2.940 / Altitud máxima: 2.850", con la mínima por sobre la máxima. Se publicó como rango `2.850 – 2.940 m`, que respeta los dos números. Chequear con el operador.
4. **Valle de la Luna Sur.** Hoy sin entrada, pero puede haberla a la fecha del viaje. Advertido en la ficha y en las condiciones.

## Cómo se regenera

```bash
cd cotizaciones/segar-san-pedro-atacama
node build.mjs               # cotización: HTML + PDF, ES y EN
node build-diferencias.mjs   # días y horarios: HTML + PDF, ES y EN
node build-itinerario.mjs    # itinerario propuesto: HTML + PDF, ES y EN
python3 build-excel.py       # planilla en el formato del export del cliente
# --solo-html en cualquiera de los dos si no hay Chromium a mano
```

Los precios viven **una sola vez**, en `contenido.mjs`. Tocá ese archivo y volvé
a correr los dos builds: los subtotales, los totales, los deltas de las
alternativas y el impacto del comparativo se recalculan solos en los dos
idiomas. Los `.html` son generados — no editarlos a mano.

| Archivo | Qué tiene |
|---|---|
| `contenido.mjs` | Programa, precios, alternativas, flota. La fuente de verdad |
| `estilos.mjs` | Hoja compartida por los dos documentos, incluida la de impresión |
| `comun.mjs` | Formato de moneda y fecha, cabecera, pie e impresión a PDF |
| `build.mjs` | Cotización |
| `build-diferencias.mjs` | Comparativo de días y horarios contra el sistema, con los choques |
| `build-itinerario.mjs` | Itinerario propuesto para el operador, con las preguntas a confirmar |
| `itinerario.mjs` | Itinerario propuesto. Fuente única del PDF y de la planilla |
| `build-excel.py` | Planilla .xlsx para el operador. Lee itinerario y precios vía node, no duplica nada |

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
