# Notas internas — COT-2026-0160 · Segar

> **No se publica.** Estado actual del expediente: qué se acordó, con quién, qué
> falta y cómo se regenera todo.

- **Cliente:** Segar · **4 pasajeros**
- **Programa:** San Pedro de Atacama, martes 22 a sábado 26 de **diciembre** de 2026
- **Operador:** Francisco Renard, agencia en San Pedro
- **Última actualización:** 24 ago 2026, con lo conversado ese día con Francisco y con Segar

## Entregables

| Archivo | Qué es |
|---|---|
| `Tourevo-COT-2026-0160-Segar-ES.pdf` · `-EN.pdf` | Cotización, con subtotal por tour y total del pack |
| `Tourevo-COT-2026-0160-Segar-Itinerario-propuesto-ES.pdf` · `-EN.pdf` | Itinerario día a día |
| `Tourevo-COT-2026-0160-Segar-Itinerario-para-operador.xlsx` | Planilla con el itinerario en español y en inglés, más las preguntas al operador |

---

## Itinerario vigente

**20 bloques, 9 servicios nuestros, los 9 ítems cotizados ubicados.**

| Día | Programa |
|---|---|
| Mar 22 | Vuelo 15:33 – 17:41 · **traslado 18:15 – 20:00** · llegada al hotel, noche libre |
| Mié 23 | **Quitor 09:30 – 11:30** · **Cejar 15:30 – 19:30** · **astronómico compartido 21:00 – 23:00** |
| Jue 24 | **Puritama 09:30 – 13:30** · **sandboard 14:30 – 16:30** · **Vallecito 17:00 – 21:00** · cena 21:30 |
| Vie 25 | **Altiplano con Chaxa 08:00 – 18:00** · cena 20:00 |
| Sáb 26 | Libre · **traslado 10:00 – 11:45** · vuelo 13:44 |

### Lo que impuso el operador

1. **Cejar sólo PM.** Las agencias no entran en la mañana, así que la salida de las 10:15 que habíamos armado no existía. Va el 23, 15:30 – 19:30.
2. **El Vallecito sale 17:00 como máximo**, no 17:30, y queda 17:00 – 21:00. Con eso **se cruza con Cejar y ya no caben el mismo día**. Cejar se queda el 23 —deja hora y media antes del astronómico— y el Vallecito pasa al 24.
3. **Traslados de 1 h 45.** Cambia las dos puntas: llegada al hotel 20:00 el 22, salida 10:00 el 26.
4. **Quitor y sandboard son excursiones distintas** aunque vayan seguidas. Acá quedaron en días distintos igual.
5. **Altiplano con Chaxa: CLP 270.000 p/p el 25** (eran 210.000). El operador **no lo recomienda**: alarga mucho el día. Falta el monto de la entrada de Chaxa.
6. **Astronómico privado el 24: CLP 600.000 el grupo.** Descartado: vuelve el compartido cotizado, CLP 40.000 p/p, y al 23, que es la noche que pidió Segar.
7. **Todas las entradas por adelantado**, incluidas Quitor y Marte, que las compra el operador.
8. Las alternativas con «Valor Mínimo» eran para grupos de menos de 3 pax: **no aplican con 4** y quedaron fuera de la elección.
9. Pidió dejar **aire entre excursiones** por tránsito y accesos.

### Lo que definió Segar

- Astronomía el 23: sí.
- Cejar en PM: OK.
- Sandboard el 24: OK.
- **Cena sin traslado nuestro:** usan el shuttle del hotel.

### Lo único que queda por decidir

**La hora de la cena de Nochebuena.** El Vallecito termina 21:00 y la cena estaba
a las 20:00. O se corre a las **21:30** —hora normal de Nochebuena en Chile— o el
Valle de la Luna no puede ir el 24, y no queda otra tarde donde ponerlo. Va
marcado en ámbar en los dos documentos.

## Valores

| Concepto | Monto |
|---|---|
| Servicios | CLP 785.000 p/p (eran 725.000: +60.000 por Chaxa el 25) |
| Entradas | CLP 84.500 p/p, **más la entrada de Chaxa que falta** |
| **Pack por persona** | **CLP 869.500** |
| Pack para 4 pax | CLP 3.478.000 |

Faltan los recargos de Nochebuena en Puritama, sandboard y Vallecito. El del 25
ya está dentro de los CLP 270.000.

## Pendientes

1. **Condiciones de pago y cancelación.** No venían en el programa y **no se inventaron**. Se piden al operador y recién ahí se agregan.
2. **Fotos de los vehículos.** No estaban al generar los PDF. La sección de flota se arma igual con las fichas de los dos vehículos y el texto avisa que van adjuntas. Para incorporarlas, ver `vehiculos/LEEME.md` y volver a correr `node build.mjs`. Con 4 pax la capacidad importa: la Tahoe entra cómoda con equipaje, la 4Runner queda al límite en el full day.
3. **Valle del Arcoíris.** Segar lo había pedido. Con el 26 libre y el resto de los días completos no queda espacio sin sacar otra cosa. No se descartó en silencio: está como pregunta.
4. **Erratum del correo original.** El trekking de Vilama venía con «Altitud mínima: 2.940 / Altitud máxima: 2.850», con la mínima por sobre la máxima. Quedó como rango `2.850 – 2.940 m`. Chequear con el operador si esas alternativas vuelven a entrar.

---

## Cómo se regenera

```bash
cd cotizaciones/segar-san-pedro-atacama
node build.mjs               # cotización: HTML + PDF, ES y EN
node build-itinerario.mjs    # itinerario: HTML + PDF, ES y EN
python3 build-excel.py       # planilla, con el itinerario en los dos idiomas
# --solo-html en cualquiera de los dos .mjs si no hay Chromium a mano
```

| Archivo | Qué tiene |
|---|---|
| `contenido.mjs` | Programa, precios, alternativas, flota. La fuente de los montos |
| `itinerario.mjs` | Itinerario propuesto. La fuente de los horarios |
| `estilos.mjs` | Hoja compartida por los documentos, incluida la de impresión |
| `comun.mjs` | Formato de moneda y fecha, cabecera, pie e impresión a PDF |
| `build.mjs` | Cotización |
| `build-itinerario.mjs` | Itinerario en PDF |
| `build-excel.py` | Planilla. Lee itinerario y precios vía node, no duplica nada |

Los precios viven **una sola vez** y los horarios **una sola vez**. Tocá esos dos
archivos y volvé a correr los tres builds: subtotales, totales y horarios se
recalculan solos en los dos idiomas. Los `.html` son generados — no editarlos.

### Cuatro hojas en la planilla

- **Itinerario** e **Itinerary** — la misma tabla en español y en inglés, para mandar una u otra según a quién. Los textos vienen bilingües de `itinerario.mjs`, así que las dos hojas no pueden decir cosas distintas. La columna **Nº** numera en teal los nueve servicios que operamos: las filas sin número son vuelos, comidas o tiempo libre, y las ámbar cambiaron o necesitan decisión. Las dos columnas amarillas quedan en blanco para que el operador responda **¿Factible?** y **Comentarios**.
- **Preguntas** — las transversales, con su columna de respuesta. En español, va para Francisco.
- **Referencia** — qué cambió, los nueve ítems cotizados con **dónde cae cada uno**, y de dónde sale cada dato. Interna.

`build-excel.py` **falla si algún servicio cotizado no aparece en el itinerario**,
si aparece dos veces, o si un servicio nuestro se queda sin texto de
confirmación. Se agregó después de que Puritama quedara con la columna de
confirmación vacía y pareciera que no estaba en la planilla. Cada bloque de
`itinerario.mjs` declara en `cot` qué ítems cubre, y el build cruza esa lista
contra `contenido.mjs`.

**Nota de entorno:** el contenedor trae `libreoffice-core` pero no
`libreoffice-calc`, así que ninguna planilla se puede abrir ni recalcular. Hay
que instalarlo (`apt-get install -y libreoffice-calc`) antes de correr
`scripts/recalc.py` de la skill de xlsx.

Esta carpeta va en `cotizaciones/`, no en `propuestas/`: ese árbol se movió a
`tourevo-cl/website/propuestas/` y el workflow de FTP publica cualquier cosa que
aparezca ahí. Acá el deploy no la toca.
