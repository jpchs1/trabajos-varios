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

## Clave de acceso

La cotización quedó pública en `tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui/`
apenas se subió, así que se le agregó Basic Auth vía `.htaccess`.

**Los 3 planes quedan visibles a propósito** — el cliente tiene que poder elegirlos y
pagar sin desenterrar la clave primero. Viven en una **carpeta hermana sin `.htaccess`**:
`propuestas/kiran-shah-patagonia-rapa-nui-planes/index.html`, publicada en
`tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui-planes/`.

Al principio esa página vivía adentro de la carpeta protegida, como `planes.html`, con una
excepción `<Files "planes.html"> Require all granted </Files>` dentro del mismo
`.htaccess`. En teoría un bloque `<Files>` más específico pisa el `Require valid-user` de
más arriba — es el patrón estándar de Apache 2.4 para dejar un archivo público dentro de
una carpeta protegida. **En este cPanel puntual no funcionó**: `curl` sin credenciales dio
`401` también para `planes.html`, probablemente por cómo ese servidor mergea autorización
entre el nivel de directorio y el de `<Files>` (`AuthMerging`, o una config de EasyApache
que no se comporta como el Apache estándar). En vez de perseguir la causa exacta con
sintaxis de `Require expr`, se movió el archivo a una carpeta separada sin `.htaccess` —
determinístico, no depende de cómo esté configurado el merging en este servidor.

El `.htaccess` sí está en el repo. El `.htpasswd` **no**: este repo es público, y el hash
de la clave real no debería quedar en el historial de git aunque `apr1` sea razonablemente
resistente. Se genera directo en el servidor:

```bash
cd ~/tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui

# si el servidor tiene htpasswd (Apache/httpd-tools):
htpasswd -bc .htpasswd kiran 'Kiran321#'

# si no lo tiene, con openssl (viene en cualquier cPanel):
printf 'kiran:%s\n' "$(openssl passwd -apr1 'Kiran321#')" > .htpasswd

chmod 644 .htpasswd
```

Después probar sin sesión (curl sin credenciales debe dar 401, con ellas 200):

```bash
curl -sI https://tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui/ | head -1
curl -sI -u kiran:'Kiran321#' https://tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui/ | head -1
curl -sI https://tourevo.cl/propuestas/kiran-shah-patagonia-rapa-nui-planes/ | head -1   # 200 sin clave
```

Si el 401 no aparece, es que el cPanel tiene `AllowOverride None` para `AuthConfig` en esa
zona y el `.htaccess` no alcanza — en ese caso hay que activar la protección desde
*cPanel → Privacidad de directorios* en vez de por archivo.

**La clave viaja en texto plano por Basic Auth** (va con TLS porque el sitio es HTTPS, pero
sin más cifrado que eso) y por email si se la mandan a Kiran por ese medio. Para una
cotización no es un problema serio, pero no es el nivel de un login real — es una cortina,
no una caja fuerte.

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
