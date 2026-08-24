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
| `Tourevo-COT-2026-0160-Segar-Ajustes-ES.pdf` · `-EN.pdf` | Comparativo contra el itinerario del cliente, 5 páginas |

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

## Comparativo contra el itinerario del cliente

El itinerario que mandó el cliente no coincide con el programa cotizado. El
documento de ajustes lo desglosa; el resumen es que **de los CLP 809.500 por
persona sólo se mantienen CLP 111.000** (traslado de llegada y tour
arqueológico): CLP 457.500 hay que recotizarlos y CLP 241.000 se caen del
itinerario.

Tres cosas bloquean y hay que resolverlas antes de reservar nada:

1. **24 y 25 de diciembre son feriado.** Los dos días más caros del programa. Disponibilidad y recargo, primero que nada.
2. **La aclimatación del 22 de 09:00 a 13:00 es imposible:** el vuelo llega 18:15. Probablemente va el 23.
3. **El Valle del Arcoíris no se combina con Piedras Rojas:** están en direcciones opuestas. Necesita su propia media jornada.

## Pendientes

1. **Condiciones de pago y cancelación.** No venían en el programa del operador y **no se inventaron**. Quedan fuera del documento a propósito: se piden al operador y recién ahí se agregan.
2. **Fotos de los vehículos.** No estaban disponibles al generar los PDF. La sección de flota se arma igual, con las fichas de los dos vehículos como tira compacta, y el texto avisa que las fotos van adjuntas. El camino con fotos está probado: dejarlas en `vehiculos/` (ver `LEEME.md`) y volver a correr el build. Con 4 pax la ficha de capacidad dejó de ser decorativa: la Tahoe entra cómoda con equipaje, la 4Runner queda al límite en el full day.
3. **Erratum del correo original.** El trekking de Vilama venía con "Altitud mínima: 2.940 / Altitud máxima: 2.850", con la mínima por sobre la máxima. Se publicó como rango `2.850 – 2.940 m`, que respeta los dos números. Chequear con el operador.
4. **Valle de la Luna Sur.** Hoy sin entrada, pero puede haberla a la fecha del viaje. Advertido en la ficha y en las condiciones.

## Cómo se regenera

```bash
cd cotizaciones/segar-san-pedro-atacama
node build.mjs               # cotización: HTML + PDF, ES y EN
node build-diferencias.mjs   # comparativo: HTML + PDF, ES y EN
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
| `build-diferencias.mjs` | Comparativo, con el itinerario recibido y los hallazgos |

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
