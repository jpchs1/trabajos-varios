# Traspaso a producción — portal tourevo.cl

> **No se publica.** El deploy FTP de este repo excluye `*.md`.
> Contenido listo para pegar en el panel admin de `tourevo-cl`, campo por campo.

## Por qué este archivo existe

El documento HTML **no es el camino a un link público**. En `tourevo-cl`, la carpeta
`cotizaciones/` dice explícitamente que no se publica: el deploy web solo sincroniza
`website/dist/` al webroot. Esos HTML son para mandar por mail o exportar a PDF.

El link personalizado con botones de pago sale de otro lado: el **portal cliente** `c/`,
que es token-gated y se alimenta de una *cotización a medida* del panel admin.

```
https://www.tourevo.cl/c/Q-XXXXXX?t=<32 hex>
```

El token es `substr(hash_hmac('sha256', $id, $portalSecret), 0, 32)` — lo genera el
panel, no hace falta calcularlo a mano.

**No puedo crearla desde acá:** las cotizaciones viven como JSON plano en
`~/tourevo.cl/api/data/quotes/Q-XXXXXX.json`, en el servidor, fuera de git. El panel es
PHP en cPanel. Así que abajo va el contenido ya con el formato exacto de cada campo.

## El mapeo resultó exacto

Las tres cosas que armé corresponden una a una con los tres formularios de
`MedidaController`:

| Lo que armé | Campo del panel | Ruta |
| --- | --- | --- |
| Los 3 planes | `savePlanes` → `medida.plans` | `/medida/{id}#planes` |
| El costo en tierra | `savePresupuesto` → `medida.budget` | `/medida/{id}#presupuesto` |
| Las recomendaciones por día | `saveItinerario` → `medida.itinerary_meta` | `/medida/{id}#itinerario` |

El panel llama al presupuesto **"referencial"**, que es justo lo que corresponde al
modelo: el pasajero le paga a cada proveedor y nosotros cobramos el plan.

---

## 1 · Planes (`/medida/{id}#planes`)

Formato del textarea `items`: **una viñeta por línea**. Una línea que empieza con `-`
se guarda como `on:false` y sale como viñeta hueca (lo que en el documento son los
puntos vacíos de "no incluye"). El panel corta en 3 planes.

### Plan 1

- **name:** `The plan`
- **tagline:** `The thinking, written down. You take it from there and book everything yourself.`
- **fee:** `760`

```
This document, finished — the thirteen travelling days, the alternatives, the reasoning behind each one
Who to book with and who to avoid, by name, for every service in both segments
The booking order and the deadlines: the Isla Martillo landing permit, the Rapa Nui park entries, the El Calafate–Ushuaia flight, the Aerolíneas connection through Aeroparque
Weather windows, walking distances, the vegetarian briefing notes, and what to pack for a Patagonian November and a Rapa Nui December
Two rounds of revisions on the itinerary
- We do not contact any supplier on your behalf
- No assistance once you are travelling
```

### Plan 2

- **name:** `The plan, booked`
- **tagline:** `We make every reservation for you. You pay each supplier directly, in your name.`
- **fee:** `1440`

```
Everything in Plan 1
We book all of it — hotels, every flight in both segments, guides, the Safari Náutico, the penguin landing, the park entries, the transfers
You pay each supplier directly, at their price. We never hold your money
The paperwork handled: the Rapa Nui entry form, park tickets named to passports, the Isla Martillo permit and its daily quota
Re-booking and changes through the whole planning phase, unlimited
One document with every confirmation code, in order, on your phone
- No dedicated assistance during the trip beyond emergencies
```

### Plan 3

- **name:** `The plan, booked, and watched`
- **tagline:** `Everything above, plus a Tourevo host on the other end of WhatsApp from landing to departure.`
- **fee:** `2380`

```
Everything in Plan 2
A Tourevo host reachable on WhatsApp, 08:00 to midnight, every day you are travelling — in Argentina and on Rapa Nui alike
We watch the forecast and re-arrange days when the weather turns — before it costs you one
We stay in constant contact with your suppliers to confirm what you have booked, and to see that it happens on time and as agreed
If a supplier lets you down, we do everything we can to put it right. It is not entirely in our hands — it is a third party — but we will be there giving our best to find a solution, as far as one exists
We keep in touch as much as is useful and no more. We are not going to interrupt a good moment out there; the point is that you enjoy it
```

> **Ojo con el fee.** El último commit del repo es *"Segar: precio por grupo de 4"*, y el
> adjunto que me pasaste decía "for a group of 4". Estas tres cifras son **las mismas del
> adjunto**, sin escalar, porque me pediste cambiar solo que es para 2 personas. Si el
> criterio real es que el fee baja con el tamaño del grupo, hay que cambiarlas acá.

---

## 2 · Presupuesto referencial (`/medida/{id}#presupuesto`)

Moneda `USD`. **Todas las líneas van con `sourced` sin marcar** — el panel tiene ese flag
justo para esto, y estos precios son estimación de mercado, no tarifa de proveedor. A
medida que confirmes cada uno, marcás `sourced` y cargás el `source`.

`flat` marcado = no escala por persona (hotel por habitación, traslado por vehículo).

| category | day | title | type | flat | qty | pp_low | pp_high |
| --- | --- | --- | --- | :-: | --: | --: | --: |
| Hoteles | 14–21 nov | Alojamiento 7 noches 4★ dbl BB · Calafate 2 / Chaltén 2 / Ushuaia 3 | hotel | ✔ | 1 | 1610 | 1610 |
| Vuelos | 18–21 nov | FTE→USH · USH→AEP · AEP→SCL, economy c/ equipaje | flight | | 2 | 620 | 620 |
| Traslados | 14–18 nov | Traslados privados: FTE in, Calafate↔Chaltén c/ paradas, Chaltén→FTE, Ushuaia in/out | private | ✔ | 1 | 450 | 450 |
| Excursiones | 15 nov | Perito Moreno full day + Safari Náutico | shared | | 2 | 150 | 150 |
| Excursiones | 17 nov | Lago del Desierto full day | shared | | 2 | 130 | 130 |
| Excursiones | 19 nov | PN Tierra del Fuego + Tren del Fin del Mundo | shared | | 2 | 150 | 150 |
| Excursiones | 20 nov | Isla Martillo c/ desembarco + Estancia Harberton | shared | | 2 | 170 | 170 |
| Vuelos | 3 / 7 dic | SCL ⇄ IPC LATAM, economy c/ equipaje | flight | | 2 | 850 | 850 |
| Hoteles | 3–7 dic | Alojamiento 4 noches 4★ dbl BB · Hanga Roa | hotel | ✔ | 1 | 1200 | 1200 |
| Traslados | 3 / 7 dic | Transfers aeropuerto Mataveri in/out | private | ✔ | 1 | 60 | 60 |
| Excursiones | 4 dic | Full day Este: Rano Raraku, Tongariki, Te Pito Kura, Anakena | shared | | 2 | 120 | 120 |
| Excursiones | 5 dic | Medio día Sur: Rano Kau, Orongo, Ana Kai Tangata | shared | | 2 | 70 | 70 |
| Excursiones | 6 dic | Medio día Oeste: Puna Pau, Ahu Akivi, Ana Te Pahu, Vinapu | shared | | 2 | 70 | 70 |

Control: Patagonia 4.500 + Rapa Nui 3.480 = **7.980**.

Fuera del presupuesto, a pagar en destino: ticket PN Rapa Nui ~USD 80 p/p, entradas
Los Glaciares y Tierra del Fuego ~USD 30 p/p por parque.

---

## 3 · Itinerario (`/medida/{id}#itinerario`)

`itinerary_meta` es por día, con `badge`, `tag` (`private` | `shared`), `why` y
`alternatives`. Los `why` salen de las recomendaciones y los `alternatives` de los
trade-offs que ya están escritos. Los días y su contenido están en el documento y en el
PDF; acá van los cuatro que llevan `why` fuerte:

- **16–17 nov (Chaltén, 2 noches):** `why` → el day trip desde Calafate son 13-14 h puerta
  a puerta con 6 h sentados. `alternatives` → si prefieren menos movimiento, Chorrillo del
  Salto plano de 1 h y tarde libre.
- **15 nov (Perito Moreno):** `why` → el Safari Náutico de 1 h se combina el mismo día; el
  *Todo Glaciares* de 9 h no. `alternatives` → 3ª noche en Calafate + Upsala/Spegazzini.
- **20 nov (Isla Martillo):** `why` → concesión única con cupo diario por permiso, y ya
  hicieron el Beagle. `alternatives` → ninguna que agregue algo nuevo.
- **21 nov (salida):** `why` → los dos tramos en Aerolíneas por Aeroparque evitan cruzar
  Buenos Aires. `alternatives` → noche en Buenos Aires el 20 y Ushuaia baja a 2 noches.

---

## Pendientes antes de mandar el link

1. **Cargar precios reales** y marcar `sourced` línea por línea.
2. **Decidir el fee para 2 personas** (ver la nota arriba).
3. **Confirmar el copy del Plan 3.** Dice "every day you are travelling — in Argentina and
   on Rapa Nui alike". Si el host no cubre Argentina, corregir: tal como está lo estamos
   prometiendo.
4. **Confirmar el 50/50 del fee** y el "subir de plan pagando la diferencia" — los redacté
   yo, el adjunto no dice nada de cobranza.
5. **Bloquear Isla Martillo** apenas confirmen el plan.

## Lo que queda en este repo

El HTML y el PDF siguen acá y siguen sirviendo para mandar por mail y para el adjunto.
Si se quieren mover a `tourevo-cl/cotizaciones/kiran-shah-patagonia-rapa-nui/`, va por PR
a `setup/initial-structure` — pero conviene restilarlos antes al navy/esmeralda con
Fraunces + Inter que usan las otras 23, porque hoy están en el tema claro de la propuesta
de Crystal Low.
