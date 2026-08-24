# Notas internas — COT-2026-0160 · Segar

> **No se publica.** Documento de revisión previa al envío: qué se tomó tal cual
> del programa del operador, qué se agregó y qué queda pendiente de confirmar
> antes de mandarlo.

- **Cliente:** Segar
- **Programa:** San Pedro de Atacama, martes 22 a sábado 26 de septiembre de 2026
- **Emitida:** 24 ago 2026 · vigencia 07 sep 2026
- **Base de cálculo:** 2 pax
- **Entregables:** `Tourevo-COT-2026-0160-Segar-ES.pdf` y `-EN.pdf` (7 páginas cada uno)

---

## Qué se agregó respecto del programa recibido

| # | En el correo del operador | En la cotización | Por qué |
|---|---|---|---|
| 1 | Cada tour con su "Valor por persona" y, aparte, "Entradas a parques" | **Subtotal por tour** = servicio + entradas, en la ficha de cada excursión y en la tabla resumen | Era el pedido explícito. Además es el número que el pasajero necesita para decidir tour por tour: el valor del servicio solo no es lo que va a gastar ese día |
| 2 | "Total del programa por persona CLP 725.000 / Valores no incluyen entradas a los parques" | **Total del pack CLP 809.500 por persona** (725.000 en servicios + 84.500 en entradas) y **CLP 1.619.000 para 2 pax** | El total de 725.000 deja fuera 84.500 de entradas obligatorias: un 11,7% del gasto real quedaba invisible. Se muestran los tres números, no se reemplaza ninguno |
| 3 | Subtotal por día: no había | Cada día del itinerario muestra su subtotal por persona en el encabezado | Barato de agregar y es la unidad con la que el pasajero razona ("¿cuánto me sale el miércoles?") |
| 4 | Alternativas sueltas, sin comparación | Cada alternativa muestra su subtotal y el **efecto exacto sobre el total del pack** | Las tres reemplazan al jueves AM (CLP 117.500 p/p). Sin el delta hay que hacer la resta a mano para poder elegir |
| 5 | Sin fecha de año | Fechas completas: 22–26 **septiembre 2026** | Los días de la semana del correo (martes 22 … sábado 26) sólo calzan con septiembre de 2026. Verificado antes de escribirlo |
| 6 | Un solo texto en español | Dos versiones completas, ES y EN, generadas del mismo `contenido.mjs` | Se pidieron las dos. Los montos se calculan una vez y se renderizan dos, así no pueden discrepar |

## Aritmética verificada

| Concepto | Monto |
|---|---|
| Servicios (9 ítems) | CLP 725.000 — **coincide con el total del correo** |
| Entradas a parques (Puritama 35.000 + Marte 7.500 + Cejar 21.000 + Piedras Rojas 15.000 + Quitor 6.000) | CLP 84.500 |
| **Total del pack por persona** | **CLP 809.500** |
| Total para 2 pax | CLP 1.619.000 |

Efecto de cada alternativa sobre el pack, por persona (base jueves AM: CLP 117.500):

| Alternativa | Subtotal p/p | Delta | Pack resultante |
|---|---|---|---|
| 1 · Trekking Cascadas de Vilama | 105.000 | −12.500 | 797.000 |
| 2 · Trekking Cornisas + Valle de Marte | 141.000 | +23.500 | 833.000 |
| 3 · Bike Valle de Catarpe | 105.000 | −12.500 | 797.000 |

## Supuestos que hay que confirmar antes de enviar

1. **Cantidad de pasajeros: se asumieron 2.** Los tours del programa están cotizados por persona, así que el total por persona no cambia. Lo que sí cambia es el valor de las alternativas 1 y 2, que el operador cotizó como **tarifa mínima de grupo sobre base 2 pax** (CLP 210.000 y CLP 270.000). Con otra cantidad de pasajeros hay que repedirlas. El documento lo dice en las condiciones.
2. **Fotos de los vehículos.** No estaban disponibles al generar los PDF; la sección se armó sin imágenes y el texto avisa que van adjuntas. Para incorporarlas, ver `vehiculos/LEEME.md` y volver a correr `node build.mjs`.
3. **Condiciones de pago y política de cancelación.** No venían en el programa del operador y **no se inventaron**. Hay que agregarlas antes de mandar la cotización.
4. **Erratum del correo original.** El trekking de Vilama venía con "Altitud mínima: 2.940 / Altitud máxima: 2.850", con la mínima por sobre la máxima. Se publicó como rango `2.850 – 2.940 m`, que respeta los dos números. Vale la pena chequearlo con el operador.
5. **Valle de la Luna Sur.** Hoy sin entrada, pero el operador avisa que puede haberla a la fecha del viaje. Queda advertido en la ficha del tour y en las condiciones.

## Cómo se regenera

```bash
cd cotizaciones/segar-san-pedro-atacama
node build.mjs              # HTML + PDF (necesita Playwright con Chromium)
node build.mjs --solo-html  # sólo HTML
```

Los precios viven **una sola vez**, en `contenido.mjs`. Tocá ese archivo y volvé
a correr el build: los subtotales, los totales y los deltas de las alternativas
se recalculan solos en los dos idiomas. Los `.html` son generados — no editarlos
a mano.

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
