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

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
