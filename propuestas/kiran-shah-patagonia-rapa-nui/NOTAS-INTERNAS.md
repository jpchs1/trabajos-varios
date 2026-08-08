# Notas internas — COT-2026-0159 · Kiran P. Shah

> **No se publica.** El deploy FTP excluye `*.md` (`--exclude-glob '*.md'` en `scripts/deploy-ftp.sh`).
> Documento de revisión previa al envío. Acá está todo lo que cambiamos respecto del mail original.

- **Cliente:** Kiran P. Shah · kpshah3105@gmail.com
- **Mail original:** 5 ago 2026, 13:52 → hello@tourevo.cl
- **Pasajeros:** 2 adultos, ambos +65, vegetarianos, aceptan excursiones compartidas
- **Fecha inamovible:** deben estar en Santiago el **22 nov 2026**
- **Emitida:** 07 ago 2026 · vigencia 21 ago 2026

---

## Tabla de comparación

| # | Solicitud original del cliente | Propuesta Tourevo | Razón del cambio | Beneficio |
|---|---|---|---|---|
| 1 | "El tour puede empezar el 14 o 15 de nov. Por favor asesorar." + "Necesito estar en Santiago el 22 nov" | **Salida 14 nov.** Llegada a Santiago la **tarde/noche del 21**, no el 22 | FTE y USH son los dos aeropuertos más expuestos al clima de Argentina; noviembre es el mes más ventoso. Saliendo el 15, el único armado posible obliga a volar USH→AEP→SCL el propio 22 → una demora rompe la única fecha fija del viaje | Un día completo de colchón antes de la fecha fija + una noche de descanso en Santiago + **3 noches en Ushuaia en vez de 2** (el día extra queda dentro del viaje, no al final) |
| 2 | "El Calafate, El Chaltén, Ushuaia" con caminatas fáciles de 1-2 h | **2n El Calafate · 2n El Chaltén · 3n Ushuaia.** El Chaltén se duerme, no se hace en el día | El day trip estándar desde El Calafate son 13-14 h puerta a puerta, 6 h sentados, con las caminatas al mediodía (peor luz). Inaceptable para +65 | Sin madrugón, Fitz Roy al amanecer y al atardecer desde la ventana, caminatas al ritmo de ellos con opción de volverse sin perder el día |
| 3 | "El Perito Moreno, el paseo en barco" | Perito Moreno full day **+ Safari Náutico (1 h)**. NO el *Todo Glaciares* Upsala/Spegazzini (9 h) | Se venden dos productos bajo la misma frase. *Todo Glaciares* sale de Puerto Bandera y **no se combina con Perito Moreno el mismo día**: con 2 noches en Calafate, elegirlo significa perder el glaciar que vienen a ver | Reciben las dos mitades de la experiencia (pasarelas + agua al pie del frente) en un solo día cómodo, de vuelta al hotel a las 18:00 |
| 4 | "Vuelos domésticos incluidos" + estar en Santiago | **USH → Buenos Aires AEP → SCL, ambos tramos Aerolíneas, un solo ticket** | Los ruteos alternativos obligan a cambiar de aeropuerto en Buenos Aires (AEP→EZE, 45-70 min con equipaje) o parten el día entre dos aerolíneas sin protección si el primer tramo se atrasa | Sin cambio de aeropuerto, sin re-check de maletas, y con obligación de reacomodo de la aerolínea si el tramo desde Ushuaia se atrasa |
| 5 | "Isla Martillo para los pingüinos (si es posible)" | Sí, **versión con desembarco** vía Estancia Harberton, bloqueada apenas confirmen | Dos productos comparten el nombre: la mayoría de los catamaranes del Beagle solo pasan frente a la isla. El desembarco tiene concesión única y cupo diario tope por permiso; nov-dic se agota con meses. Además **ya hicieron el Beagle**, así que el pasar-de-largo no aporta nada | Caminan entre los pingüinos en vez de fotografiarlos a 50 m. Es la línea que se reserva primero y alrededor de la cual se arma el resto |
| 6 | Isla de Pascua: 4 noches, 3-7 dic, vuelta a Santiago el 7, casa el 8 | **Sin cambios.** Confirmado tal cual | 4 noches es la duración correcta (llega mediodía / sale media tarde = 3 días completos + 2 medios, uno por circuito). Y la noche del 7 en Santiago es el colchón que igual habríamos exigido: el retorno son ~4 h 45 sobre océano abierto sin alternativa | Nada que arreglar. Se le dice explícitamente que su estructura está bien y por qué — refuerza confianza en los cambios que sí hicimos |
| 7 | "Excursiones compartidas cubriendo los principales atractivos arqueológicos y culturales" | **3 circuitos, uno por día, nunca dos**: full day Este (Rano Raraku + Tongariki), medio día Sur (Rano Kau + Orongo), medio día Oeste (Puna Pau + Akivi + cuevas). Tardes libres tras los medios días | El ticket del Parque Nacional Rapa Nui admite **una sola entrada a Rano Raraku y una sola a Orongo**, y el guardaparques lo escanea. Los itinerarios que "pasan por ahí" más de un día queman una entrada que no se puede recomprar | Cero entradas desperdiciadas, tiempo real en la cantera (el sitio donde todos quisieron tener una hora más) y dos tardes libres en Hanga Roa |
| 8 | *(No mencionado)* | Se **detecta y se pregunta** por el hueco del **22 nov – 3 dic** (11 días) | El cliente no dijo qué hay ahí. No asumimos ni lo cotizamos: se señala y se ofrece Atacama / Lagos y Chiloé / Valparaíso-Casablanca / Torres del Paine como tercer segmento | Si están libres, es el mejor upsell del expediente. Si están comprometidos, no molesta |

---

## Estructura final

**Segmento 1 — Patagonia Argentina · 14 → 21 nov 2026 (7 noches)**

| Día | Fecha | Programa | Noche |
|---|---|---|---|
| 1 | Sáb 14 nov | Llegada FTE, transfer, tarde libre (opcional Laguna Nimez) | El Calafate |
| 2 | Dom 15 nov | Perito Moreno full day + Safari Náutico | El Calafate |
| 3 | Lun 16 nov | Traslado a El Chaltén c/ paradas + Mirador de los Cóndores (1 h) | El Chaltén |
| 4 | Mar 17 nov | Lago del Desierto (alternativa liviana: Chorrillo del Salto + día libre) | El Chaltén |
| 5 | Mié 18 nov | Chaltén → FTE (3 h 15) → vuelo FTE-USH | Ushuaia |
| 6 | Jue 19 nov | PN Tierra del Fuego + Tren del Fin del Mundo | Ushuaia |
| 7 | Vie 20 nov | Isla Martillo (desembarco) + Estancia Harberton | Ushuaia |
| 8 | Sáb 21 nov | USH → AEP → SCL, ambos tramos Aerolíneas | Santiago (opcional) |

**Segmento 2 — Isla de Pascua · 3 → 7 dic 2026 (4 noches)**

| Día | Fecha | Programa | Noche |
|---|---|---|---|
| 1 | Jue 3 dic | SCL → IPC, transfer, atardecer en Ahu Tahai | Hanga Roa |
| 2 | Vie 4 dic | Full day Este: Rano Raraku, Tongariki, Te Pito Kura, Papa Vaka, Anakena | Hanga Roa |
| 3 | Sáb 5 dic | Medio día Sur: Rano Kau, Orongo, Ana Kai Tangata · tarde libre | Hanga Roa |
| 4 | Dom 6 dic | Medio día Oeste: Puna Pau, Ahu Akivi, Ana Te Pahu, Vinapu · tarde libre | Hanga Roa |
| 5 | Lun 7 dic | Mañana libre, IPC → SCL | Santiago (opcional) |

---

## Precios cargados en la propuesta

**⚠️ SON INDICATIVOS. Reemplazar por tarifas reales de proveedor antes de enviar.**

### Cotización 1 — Patagonia Argentina (2 pax)

| Concepto | USD |
|---|---:|
| Alojamiento 7 noches 4★ dbl BB (Calafate 2 / Chaltén 2 / Ushuaia 3) | 1.610 |
| Vuelos FTE-USH · USH-AEP · AEP-SCL (2 × 620) | 1.240 |
| Traslados privados (FTE in, Calafate↔Chaltén, Chaltén→FTE, USH in/out) | 450 |
| Perito Moreno FD + Safari Náutico (2 × 150) | 300 |
| Lago del Desierto FD (2 × 130) | 260 |
| PN Tierra del Fuego + Tren (2 × 150) | 300 |
| Isla Martillo c/ desembarco + Harberton (2 × 170) | 340 |
| **Total** | **4.500** |
| Por persona | 2.250 |

### Cotización 2 — Isla de Pascua (2 pax)

| Concepto | USD |
|---|---:|
| Vuelos SCL ⇄ IPC LATAM (2 × 850) | 1.700 |
| Alojamiento 4 noches 4★ dbl BB | 1.200 |
| Transfers aeropuerto in/out | 60 |
| Ahu Tahai atardecer | incluido |
| Full day Este (2 × 120) | 240 |
| Medio día Sur (2 × 70) | 140 |
| Medio día Oeste (2 × 70) | 140 |
| **Total** | **3.480** |
| Por persona | 1.740 |

**Total ambas: USD 7.980 · USD 3.990 p/p** (+ tickets de parques por fuera)

### Opcionales cotizados

| Opcional | USD desde |
|---|---:|
| Noche Santiago 21-22 nov 4★ BB + transfer | 240 |
| Noche Santiago 7-8 dic 4★ BB + 2 transfers | 240 |
| 3ª noche El Calafate + *Todo Glaciares* (mueve la salida al 13 nov) | 640 |
| Noche en Buenos Aires 20 nov en vez de la 3ª de Ushuaia | 190 |
| Cena y show cultural Rapa Nui, menú vegetariano | 190 |
| Privado en vez de compartido, cualquier excursión | a pedido |

---

## Modelo de cobro — los 3 planes

Se incorporaron los 3 planes de servicio. **Esto cambió el modelo de precio de toda la propuesta**, porque
el Plan 2 dice explícitamente *"you pay each supplier directly… we never hold your money"*, lo que es
incompatible con el 30/70 sobre el total del viaje que tenía la versión anterior.

Ahora la propuesta muestra **dos precios distintos y bien separados**:

| | Qué es | Quién lo cobra | Cuánto |
|---|---|---|---|
| **Costo en tierra** | Hoteles, vuelos, guías, botes, traslados | Cada proveedor, directo, a nombre del pasajero | USD 7.980 (2 pax, ambos segmentos) |
| **Fee Tourevo** | El plan que contraten | Tourevo | USD 760 / 1.440 / 2.380 |

Cambios que esto obligó a hacer en el documento:

- Nota de apertura: se agrega la aclaración de los dos precios antes de que el lector llegue a las cifras.
- Bajada nueva sobre la tabla de la Cotización 1 explicando que son precios de proveedor y que no tomamos
  comisión sobre ellos.
- Los tres totales pasan de "what it costs" a **"what the ground costs"**, con "paid direct to suppliers"
  en el subtítulo y "Tourevo's fee is separate" en el gran total.
- Ficha "Included in both quotations" → **"What the ground cost covers"**. La viñeta de *One point of
  contact* se reemplazó, porque eso ahora lo define el plan, no la cotización.
- **Condiciones comerciales reescritas por completo**: se elimina el 30/70 sobre el viaje. Ahora: qué nos
  pagan a nosotros / qué le pagan a los proveedores / fee del plan 50-50 / cancelación separada entre fee
  y proveedores.
- CTA: ahora pide dos cosas, aceptar/rechazar recomendaciones **y** elegir plan.

### Decisiones que tomé y conviene que revises

1. **Precio para 2 personas.** El adjunto decía "for a group of 4". Mantuve las tres cifras tal cual
   (760 / 1.440 / 2.380) y solo cambié la etiqueta a **"for the two of you"**, leyendo tu "solo debes
   cambiar que es para 2 personas" al pie de la letra. El fee es fijo por trabajo, no por pasajero, y el
   trabajo de este viaje no baja por ser 2 en vez de 4 — de hecho son dos países y dos segmentos. **Si
   querés otra cifra, es cambiar tres números y regenerar.**
2. **Adaptación del copy del Plan 3.** El original decía *"every day you are in Chile"*. Este viaje es
   mayormente **Argentina**, así que quedó *"every day you are travelling — in Argentina and on Rapa Nui
   alike"*. Si no cubrimos Argentina con host, hay que corregirlo, porque tal como está lo estamos
   prometiendo.
3. **Viñetas del Plan 1 reescritas para este viaje.** El adjunto mencionaba "los once días", "the Grey
   ice, the Serrano navigation" — eso es de un programa de Torres del Paine. Acá quedaron los trece días,
   el permiso de Isla Martillo, las entradas de Rapa Nui, el vuelo FTE–USH y la conexión por Aeroparque.
4. **Fee del plan 50% / 50%** (al empezar y antes del primer deadline de reserva). **Lo inventé yo**: el
   adjunto no dice nada de cómo se cobra el fee. Confirmalo o cambialo.
5. **"Podés subir de plan durante la planificación pagando solo la diferencia"** — también lo agregué yo.
   Es una promesa comercial; si no la querés sostener, sacala de la letra chica.

---

## Clave de acceso — versión 3: todo en una sola página

Se probaron tres enfoques distintos para esto. Los primeros dos se descartaron; este es el
que quedó en producción.

**v1 — Basic Auth (`.htaccess`) sobre toda la carpeta.** Protección real a nivel de
servidor: sin clave, un `401` sin body. Problema: bloqueaba absolutamente todo, incluidos
los 3 planes, y el pedido era justo lo contrario — que el cliente pudiera elegir y pagar un
plan sin tener la clave a mano.

**v2 — Basic Auth + una página separada `kiran-shah-patagonia-rapa-nui-planes/` sin
`.htaccess`**, solo con los 3 planes, con un preview borroso del itinerario arriba a modo
de gancho. Funcionaba, pero eran **dos URLs distintas** para la misma cotización, y el
cliente lo rechazó explícitamente: *"tiene que ser la misma página... pero todo en una
página"*. Esa carpeta y el `.htaccess` se eliminaron del repo.

**v3 — la actual: una sola página, todo con CSS + JavaScript, sin Basic Auth.** El bloque
`.gate-wrap` envuelve todo el documento excepto el header, los 3 planes y las condiciones
comerciales: nota de bienvenida, "prepared for", recomendaciones, los dos itinerarios día a
día, hoteles, tablas de precio y el sello Tourevo. Por defecto está `filter: blur(7px)` +
`user-select:none` + `pointer-events:none`. Un formulario (`#gateForm`) calcula el
SHA-256 del texto ingresado (Web Crypto, `crypto.subtle.digest`) y lo compara contra un
hash fijo en el `<script>` al final del archivo. Si coincide, se agrega la clase
`unlocked` a `<html>`, que por CSS saca el blur, y se guarda en `localStorage` para que no
haya que volver a tipear la clave en visitas siguientes desde el mismo navegador.

```
HASH sha256("Kiran321#") = 8360c9f5136ff61a6fdf492a762d863b3b2efbb99155044319b640163278087e
```

Si cambia la clave, recalcular con: `python3 -c "import hashlib; print(hashlib.sha256('LA-NUEVA-CLAVE'.encode()).hexdigest())"`
y reemplazar el valor de `HASH` en el `<script>` de `index.html`.

**⚠️ Esto es una cortina, no una caja fuerte — y menos que la v1.** El HTML completo
(itinerario real, nombre del hotel, precios exactos) se descarga al navegador del visitante
apenas carga la página, tenga o no la clave. El blur es puramente visual: cualquiera que
abra las herramientas de desarrollador, mire "ver código fuente" o simplemente desactive
CSS lee todo sin escribir nada. El hash SHA-256 tampoco es una protección real — evita que
la clave aparezca como texto plano en el código fuente, pero no impide que alguien se salte
el gate sin conocerla. Es la decisión correcta para el objetivo que se pidió (mostrar algo
"bien hecho" que tiente, sin trabar a nadie con un popup feo del navegador), pero **no
sirve si en algún momento se necesita protección real** — para eso hay que volver a algo
del lado del servidor (Basic Auth, o mejor, el portal `c/` de tourevo-cl con token HMAC,
ver `TRASPASO-PORTAL.md`).

**La impresión/PDF siempre muestra todo sin blur**, sin importar si la página está
desbloqueada en pantalla — hay una excepción específica dentro de `@media print` para que
`Ctrl+P` desde cualquier estado del gate produzca el documento completo.

Ya no hace falta generar `.htpasswd` en el servidor ni tocar `.htaccess` — de hecho, si
quedó un `.htaccess` viejo de la v1 en `~/tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui/`,
**hay que borrarlo**, porque si no la página nunca va a llegar a mostrar el gate: seguiría
devolviendo `401` antes de que el navegador vea una sola línea de HTML.

### Recuperación paga (USD $50) — ⚠️ pendiente el link real

Se agregó una segunda opción en `.gate-cta`, debajo del campo de clave existente (que
sigue funcionando igual que antes, sin tocarse): *"Lost the password? Pay USD $50 and
we'll send the username and password to your email"* → botón **Pay USD $50 for access**.

**Es para Kiran específicamente**, como vía de recuperación si perdió o no encuentra la
clave que le mandamos — no es un producto abierto a cualquiera. El botón hoy apunta a:

```
mailto:info@tourevo.cl?subject=Recover access · Quotation COT-2026-0159&body=...
```

Es decir: **entrega manual**. Clickearlo abre un borrador de mail pidiendo pagar, alguien
de Tourevo tiene que responder con instrucciones de pago, cobrar, y después mandar el
usuario y la clave a mano. Funciona siempre (nunca es un link muerto), pero no es lo que
se pidió — se pidió que fuera automático apenas se confirma el pago.

**Para que sea automático de verdad, sin backend propio**, la opción más rápida es un
servicio de venta de "producto digital" cuyo contenido es texto fijo (usuario+clave) que
se manda solo al pagar — **Gumroad, Payhip o Lemon Squeezy** son ejemplos, cualquiera
sirve. Se carga el producto con el usuario y la clave como contenido digital, y el link de
pago que da esa plataforma reemplaza el `mailto:` de arriba en `.gate-recover-pay`. Un
link de PayPal.me o de MercadoPago simple **no alcanza** para esto — cobran, pero no
tienen forma nativa de disparar un mail con un texto custom al confirmarse el pago; para
usar uno de esos habría que conectarlos por webhook a algo que mande el mail (Zapier/Make,
o un backend propio en el `api/` de tourevo-cl), que es un trabajo bastante más grande.

**Antes de reemplazar el link:** decidir también si el precio real es USD $50 o si conviene
otro número — no vino de ningún dato del cliente, se pidió tal cual.

---

## Puntos a verificar antes de enviar

1. **Tarifas.** Todas las cifras son estimación de mercado, no cotización de proveedor. Cargar reales.
2. **Vuelo FTE–USH directo.** Opera estacionalmente; confirmar que está en itinerario para el 18 nov 2026. Si no está, el ruteo pasa por Buenos Aires y se pierde el día → habría que dar vuelta el orden (Ushuaia primero). Es el único supuesto que puede obligar a rediseñar.
3. **AEP–SCL en Aerolíneas** el 21 nov: confirmar frecuencia y que el conector con USH–AEP dé mínimo 2 h 30 de conexión.
4. **Isla Martillo (desembarco):** confirmar cupo para el 20 nov apenas haya luz verde. Es la línea crítica.
5. **Hoteles:** Xelena / Destino Sur / Albatros / Puku Vai están puestos como "or similar". Confirmar disponibilidad y categoría antes de nombrarlos en firme.
6. **Ticket PN Rapa Nui:** verificar el valor vigente a nov 2026 (en la propuesta va como "aprox. USD 80 p/p").
7. **Cómo llegan a El Calafate el 14 nov.** El mail dice "el tour puede empezar desde El Calafate", así que asumimos que el vuelo de entrada a FTE lo ven ellos o se cotiza aparte. **Está declarado como no incluido y ofrecido a cotizar** — conviene confirmarlo en el mail de respuesta.
8. **Vuelo internacional de vuelta el 8 dic:** confirmar horario. Si sale de madrugada, revisar la noche del 7 en Santiago.
9. **Links del pie y del CTA** (`href="#"`) — cargar el Magic Link y las URLs reales.
10. **Acuse de recibo:** el cliente lo pidió explícitamente. Ya está resuelto en la nota de apertura de la propuesta, pero conviene repetirlo en el cuerpo del mail.

## Detalle vegetariano

Cargado como sección propia. Los dos servicios de menú fijo que **hay que avisar al reservar, no el día**: el almuerzo de Lago del Desierto y la cena cultural en Rapa Nui. Falta preguntarle al cliente por **huevo, lácteos y cebolla/ajo** — la propuesta se lo pregunta explícitamente en vez de asumir.
