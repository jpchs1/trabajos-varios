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

## Itinerario final — con lo conversado con Francisco y con Segar (24 ago)

**20 bloques, 9 servicios nuestros, los 9 ítems cotizados ubicados.**

| Día | Programa |
|---|---|
| Mar 22 | Vuelo 15:33 – 17:41 · **traslado 18:15 – 20:00** · llegada al hotel, noche libre |
| Mié 23 | **Quitor 09:30 – 11:30** · **Cejar 15:30 – 19:30** · **astronómico compartido 21:00 – 23:00** |
| Jue 24 | **Puritama 09:30 – 13:30** · **sandboard 14:30 – 16:30** · **Vallecito 17:00 – 21:00** · cena 21:30 |
| Vie 25 | **Altiplano con Chaxa 08:00 – 18:00** · cena 20:00 |
| Sáb 26 | Libre · **traslado 10:00 – 11:45** · vuelo 13:44 |

### Lo que impuso el operador

1. **Cejar sólo PM.** Las agencias no entran en la mañana, así que la salida de las 10:15 no existía. Va el 23, 15:30 – 19:30.
2. **El Vallecito sale 17:00 como máximo**, no 17:30. Queda 17:00 – 21:00 y por eso **ya no cabe el mismo día que Cejar**: se cruzan. Cejar se queda el 23 (deja hora y media antes del astronómico) y el Vallecito pasa al 24.
3. **Traslados de 1 h 45.** Cambia las dos puntas: llegada al hotel 20:00 el 22, salida 10:00 el 26.
4. **Quitor y sandboard son excursiones distintas** aunque vayan seguidas. Acá quedaron en días distintos igual.
5. **Altiplano con Chaxa: CLP 270.000 p/p el 25** (eran 210.000). El operador **no lo recomienda**, alarga mucho el día. Falta el monto de la entrada de Chaxa.
6. **Astronómico privado el 24: CLP 600.000 el grupo.** Se descarta: vuelve el compartido cotizado, CLP 40.000 p/p, y al 23, que es la noche que pidió Segar.
7. **Todas las entradas por adelantado**, incluidas Quitor y Marte, que las compra el operador.
8. Las alternativas con «Valor Mínimo» eran para grupos de menos de 3 pax: **no aplican con 4** y además quedaron fuera de la elección.

### Lo que definió Segar

- Astronomía el 23: sí.
- Cejar en PM: OK.
- Sandboard el 24: OK.
- **Cena sin traslado nuestro:** usan el shuttle del hotel.

### Lo único que queda por decidir

**La hora de la cena de Nochebuena.** El Vallecito termina 21:00 y la cena estaba a las 20:00. O se corre a las **21:30** —hora normal de Nochebuena en Chile— o el Valle de la Luna no puede ir el 24, y no queda otra tarde donde ponerlo.

### Valores

| | |
|---|---|
| Servicios | CLP 785.000 p/p (eran 725.000: +60.000 por Chaxa el 25) |
| Entradas | CLP 84.500 p/p, **más la entrada de Chaxa que falta** |
| **Pack por persona** | **CLP 869.500** |
| Pack para 4 pax | CLP 3.478.000 |

Faltan por confirmar los recargos de Nochebuena en Puritama, sandboard y
Vallecito. El del 25 ya está dentro de los 270.000.

## Documentos

`itinerario.mjs` es la fuente única del PDF y de la planilla; `contenido.mjs`, la
de los precios. El comparativo de días y horarios (`build-diferencias.mjs`) se
eliminó: comparaba la cotización original contra un estado del sistema que ya
cambió dos veces, y contradecía al itinerario vigente.

`build-excel.py` **falla si algún servicio cotizado no aparece en el itinerario**,
si aparece dos veces, o si un servicio nuestro se queda sin texto de
confirmación. Cada bloque declara en `cot` qué ítems cubre.

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
| `build-itinerario.mjs` | Itinerario propuesto para el operador, con las preguntas a confirmar |
| `itinerario.mjs` | Itinerario propuesto. Fuente única del PDF y de la planilla |
| `build-excel.py` | Planilla .xlsx para el operador. Lee itinerario y precios vía node, no duplica nada |

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
