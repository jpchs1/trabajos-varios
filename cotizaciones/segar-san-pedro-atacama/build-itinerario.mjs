#!/usr/bin/env node
// Itinerario propuesto para confirmar con el operador. Toma el itinerario que
// el cliente ya revisó, lo deja igual donde se puede y mueve sólo lo que no se
// puede operar como está. Sale en español y en inglés, HTML y PDF.
//
//   node build-itinerario.mjs [--solo-html]

import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { doc, cliente } from './contenido.mjs';
import { estilos, estilosComparativo, estilosItinerario } from './estilos.mjs';
import { fecha, mayus, esc, cabecera, piePagina, aPdf } from './comun.mjs';

const AQUI = dirname(fileURLToPath(import.meta.url));
const soloHtml = process.argv.includes('--solo-html');

// tipo:  tourevo → servicio nuestro · tercero → vuelos, comidas, check-in · libre → tiempo sin programar
// e:     igual → tal cual lo vio el cliente · movido → cambia de hora · nuevo → no estaba
const E = {
  igual:  { c: 'b-ok',  t: { es: 'Sin cambio', en: 'Unchanged' } },
  movido: { c: 'b-chg', t: { es: 'Movido', en: 'Moved' } },
  nuevo:  { c: 'b-x',   t: { es: 'Nuevo', en: 'New' } },
};

const DIAS = [
  { f: '2026-12-22', n: { es: 'Llegada desde Puerto Natales', en: 'Arrival from Puerto Natales' }, blks: [
    { h: '06:35', f2: '07:05', tipo: 'tercero', e: 'igual', t: { es: 'Traslado al aeropuerto de Puerto Natales (PNT)', en: 'Transfer to Puerto Natales airport (PNT)' } },
    { h: '09:05', f2: '12:14', tipo: 'tercero', e: 'igual', t: { es: 'Vuelo Puerto Natales → Santiago · LATAM', en: 'Flight Puerto Natales → Santiago · LATAM' } },
    { h: '13:45', f2: '15:15', tipo: 'tercero', e: 'igual', t: { es: 'Conexión en Santiago · almuerzo en el aeropuerto', en: 'Connection in Santiago · lunch at the airport' } },
    { h: '15:33', f2: '17:41', tipo: 'tercero', e: 'igual', t: { es: 'Vuelo Santiago → Calama (CJC) · LATAM', en: 'Flight Santiago → Calama (CJC) · LATAM' } },
    { h: '18:15', f2: '19:15', tipo: 'tourevo', e: 'igual', t: { es: 'Traslado Calama → San Pedro de Atacama', en: 'Transfer Calama → San Pedro de Atacama' },
      n: { es: 'Servicio privado. Recogida en la salida del vuelo LATAM de las 17:41.', en: 'Private service. Pickup at the arrivals gate of the 17:41 LATAM flight.' } },
    { h: '19:35', f2: '20:30', tipo: 'tercero', e: 'igual', t: { es: 'Check-in en San Pedro y descanso', en: 'Check-in in San Pedro and rest' } },
  ]},

  { f: '2026-12-23', n: { es: 'Aclimatación, salar y atardecer', en: 'Acclimatization, salt flat and sunset' }, blks: [
    { h: '09:00', f2: '13:00', tipo: 'tercero', e: 'igual', t: { es: 'Aclimatación · mañana tranquila en San Pedro', en: 'Acclimatization · calm morning in San Pedro' },
      n: { es: 'Falta definir si la quieren guiada o como tiempo libre. Si es guiada, la cotizamos.', en: 'Still to define whether they want it guided or as free time. If guided, we quote it.' } },
    { h: '13:30', f2: '16:30', tipo: 'tourevo', e: 'nuevo', t: { es: 'Laguna Cejar + Ojos de Tebenquiche + Laguna de Tebenquiche', en: 'Cejar Lagoon + Ojos de Tebenquiche + Tebenquiche Lagoon' },
      n: { es: 'Versión de <b>3 horas, sin la espera del atardecer en Tebenquiche</b>: el atardecer lo tienen esa misma tarde en el Valle de la Luna. Es el único hueco del programa donde Cejar entra completo. Vuelta al hotel 16:30, una hora antes de la recogida siguiente.',
           en: 'A <b>three-hour version, without waiting for sunset at Tebenquiche</b>: they get sunset that same afternoon at Valle de la Luna. It is the only gap in the programme where Cejar fits whole. Back at the hotel 16:30, an hour before the next pickup.' } },
    { h: '17:30', f2: '20:45', tipo: 'tourevo', e: 'igual', t: { es: 'Valle de la Luna al atardecer', en: 'Valle de la Luna at sunset' },
      n: { es: 'Confirmar que a las 17:30 alcance el circuito <b>off circuit por el Vallecito</b>, que se hace caminando, y no el recorrido estándar.', en: 'Please confirm that a 17:30 start allows the <b>off-circuit route through El Vallecito</b>, which is walked, rather than the standard circuit.' } },
  ]},

  { f: '2026-12-24', n: { es: 'Puritama, Valle de la Muerte y Nochebuena', en: 'Puritama, Valle de la Muerte and Christmas Eve' }, blks: [
    { h: '09:30', f2: '13:30', tipo: 'tourevo', e: 'igual', t: { es: 'Termas de Puritama · 3.500 m', en: 'Puritama Hot Springs · 3,500 m' },
      n: { es: 'Se mantiene el 24 y no el 23: deja un día de aclimatación en San Pedro a 2.400 m antes de subir, con Miscanti sobre 4.000 m al día siguiente.', en: 'Kept on the 24th rather than the 23rd: it leaves a day acclimatizing in San Pedro at 2,400 m before climbing, with Miscanti above 4,000 m the next day.' } },
    { h: '13:30', f2: '15:15', tipo: 'libre', e: null, t: { es: 'Vuelta a San Pedro, almuerzo y descanso', en: 'Back to San Pedro, lunch and rest' },
      n: { es: 'Esta franja antes no existía: Puritama y el Valle de la Muerte estaban pegados a las 13:30, con las termas a unos 30 km.', en: 'This gap did not exist before: Puritama and Valle de la Muerte were back to back at 13:30, with the springs some 30 km away.' } },
    { h: '15:30', f2: '17:30', tipo: 'tourevo', e: 'movido', t: { es: 'Valle de la Muerte · sandboard', en: 'Valle de la Muerte · sandboarding' },
      n: { es: 'Estaba de 13:30 a 15:30, imposible pegado a Puritama. Pasa a la franja que el itinerario tenía como tarde libre, y de paso sale de la hora de más calor de un día de diciembre.', en: 'It was 13:30 to 15:30, impossible back to back with Puritama. It moves into the slot the itinerary had as a free afternoon, and in doing so leaves the hottest part of a December day.' } },
    { h: '17:30', f2: '20:00', tipo: 'libre', e: null, t: { es: 'Descanso antes de la cena', en: 'Rest before dinner' } },
    { h: '20:00', f2: '22:00', tipo: 'tercero', e: 'igual', t: { es: 'Cena de Nochebuena', en: 'Christmas Eve dinner' } },
    { h: '22:15', f2: '23:59', tipo: 'tourevo', e: 'igual', t: { es: 'Astronomía privada', en: 'Private astronomy' },
      n: { es: 'Sin cambio de hora. <b>Recogida en el restaurante a las 22:10</b>, no en el hotel: la cena termina 22:00. Ver la nota sobre la luna más abajo.', en: 'No change of time. <b>Pickup at the restaurant at 22:10</b>, not the hotel: dinner ends at 22:00. See the note on the moon below.' } },
  ]},

  { f: '2026-12-25', n: { es: 'Altiplano', en: 'Altiplano' }, blks: [
    { h: '08:00', f2: '18:00', tipo: 'tourevo', e: 'igual', t: { es: 'Altiplano · Chaxa, Toconao, Miscanti y Piedras Rojas', en: 'Altiplano · Chaxa, Toconao, Miscanti and Piedras Rojas' },
      n: { es: 'Confirmar el alcance: <b>entra Chaxa</b>, con su propia entrada, y <b>salen Tuyajto y las protoaldeas</b> que estaban en lo cotizado. Almuerzo en hábitat natural.', en: 'Please confirm the scope: <b>Chaxa comes in</b>, with its own entrance fee, and <b>Tuyajto and the proto-villages drop out</b> from what was quoted. Lunch in a natural setting.' } },
    { h: '20:00', f2: '21:30', tipo: 'tercero', e: 'igual', t: { es: 'Cena', en: 'Dinner' } },
  ]},

  { f: '2026-12-26', n: { es: 'Quitor y salida', en: 'Quitor and departure' }, blks: [
    { h: '07:45', f2: '08:15', tipo: 'tourevo', e: 'nuevo', t: { es: 'Check-out y carga del equipaje', en: 'Check-out and loading luggage' },
      n: { es: 'Bloque nuevo, y es el que destraba la mañana. Con el equipaje ya en el vehículo, el traslado sale <b>directo desde el pukará</b> y los 24 minutos entre Quitor y las 10:24 dejan de ser un problema.', en: 'A new block, and the one that unblocks the morning. With the luggage already in the vehicle, the transfer leaves <b>straight from the pukará</b> and the 24 minutes between Quitor and 10:24 stop being a problem.' } },
    { h: '08:30', f2: '10:00', tipo: 'tourevo', e: 'igual', t: { es: 'Pukará de Quitor · guiado', en: 'Pukará de Quitor · guided' },
      n: { es: 'Hora y media. El guiado está cotizado a dos horas: confirmar si el recorrido entra, o si prefieren arrancar 08:15.', en: 'Ninety minutes. The guided visit is quoted at two hours: please confirm the route fits, or whether you would rather start at 08:15.' } },
    { h: '10:24', f2: '11:44', tipo: 'tourevo', e: 'igual', t: { es: 'Traslado a Calama', en: 'Transfer to Calama' },
      n: { es: 'Sale desde el pukará, no desde el hotel. Llega 11:44 para el vuelo de las 13:44.', en: 'Leaves from the pukará, not the hotel. Arrives 11:44 for the 13:44 flight.' } },
    { h: '13:44', f2: '15:52', tipo: 'tercero', e: 'igual', t: { es: 'Vuelo Calama → Santiago · LATAM', en: 'Flight Calama → Santiago · LATAM' } },
    { h: '20:00', f2: '23:45', tipo: 'tercero', e: 'igual', t: { es: 'Check-in internacional en Santiago', en: 'International check-in in Santiago' } },
    { h: '23:45', f2: null, tipo: 'tercero', e: 'igual', t: { es: 'Vuelo Santiago → Dallas · AA940', en: 'Flight Santiago → Dallas · AA940' } },
  ]},
];

// --- Lo único que se movió ---------------------------------------------------

const CAMBIOS = [
  { tit: { es: 'El Valle de la Muerte pasa de 13:30 a 15:30', en: 'Valle de la Muerte moves from 13:30 to 15:30' },
    cuerpo: {
      es: ['El itinerario lo tenía de 13:30 a 15:30, justo cuando Puritama termina. Las termas están a unos 30 km de San Pedro: no se puede cerrar allá a las 13:30 y estar en el valle a las 13:30.',
           'La tarde del 24 ya tenía una franja libre de 15:30 a 17:30, así que la excursión entra ahí sin tocar nada más del día. Además saca el sandboard de la hora de más calor y deja dos horas y media de descanso antes de la cena de Nochebuena.'],
      en: ['The itinerary had it from 13:30 to 15:30, exactly when Puritama ends. The springs are some 30 km from San Pedro: you cannot finish there at 13:30 and be in the valley at 13:30.',
           'The afternoon of the 24th already had a free slot from 15:30 to 17:30, so the excursion fits there without touching anything else in the day. It also takes the sandboarding out of the hottest hour and leaves two and a half hours of rest before the Christmas Eve dinner.'],
    } },
  { tit: { es: 'Cejar entra la tarde del 23', en: 'Cejar goes into the afternoon of the 23rd' },
    cuerpo: {
      es: ['Cejar estaba cotizado y no aparecía en el itinerario, y el cliente preguntó justamente si la caminata se podía combinar con una flotada. La flotada es esta.',
           'El 24 no cabe: si Cejar toma la tarde, el Valle de la Muerte se queda sin franja y el choque de las 13:30 vuelve. La tarde del 23 es el único hueco donde entra completo, entre la aclimatación que termina a las 13:00 y el Valle de la Luna que arranca a las 17:30. A favor: Cejar está a nivel del salar, así que no rompe la aclimatación del día.'],
      en: ['Cejar was quoted and did not appear in the itinerary, and the client asked precisely whether the walk could be combined with a float. This is the float.',
           'It does not fit on the 24th: if Cejar takes the afternoon, Valle de la Muerte has no slot and the 13:30 clash comes back. The afternoon of the 23rd is the only gap where it fits whole, between the acclimatization ending at 13:00 and Valle de la Luna starting at 17:30. In its favour: Cejar sits at salt-flat level, so it does not undo the day’s acclimatization.'],
    } },
  { tit: { es: 'El check-out del 26 se hace antes de salir a Quitor', en: 'Check-out on the 26th happens before leaving for Quitor' },
    cuerpo: {
      es: ['No es un cambio de horario, es un bloque nuevo de media hora a primera hora. Sin él, entre que Quitor termina a las 10:00 y el traslado sale a las 10:24 hay que volver del pukará, pasar por el hotel, hacer el check-out y cargar maletas en 24 minutos.',
           'Con el equipaje ya en el vehículo, el traslado sale directo desde el pukará y la mañana deja de estar al filo.'],
      en: ['This is not a time change, it is a new half-hour block first thing. Without it, between Quitor ending at 10:00 and the transfer leaving at 10:24 you have to come back from the pukará, stop at the hotel, check out and load luggage in 24 minutes.',
           'With the luggage already in the vehicle, the transfer leaves straight from the pukará and the morning stops being on a knife edge.'],
    } },
];

const PREGUNTAS = [
  { q: { es: 'Disponibilidad y recargo del 24 y 25 de diciembre.', en: 'Availability and holiday supplement for 24 and 25 December.' },
    d: { es: 'Nochebuena y Navidad, con el full day del altiplano el 25. Es lo primero que necesitamos cerrar: si el 25 no hay operación, hay que rearmar el viaje entero.', en: 'Christmas Eve and Christmas Day, with the altiplano full day on the 25th. This is the first thing we need to close: if the 25th cannot operate, the whole trip has to be rebuilt.' } },
  { q: { es: '¿Pueden operar Cejar el 23 de 13:30 a 16:30, en versión de 3 horas?', en: 'Can you run Cejar on the 23rd from 13:30 to 16:30, in a three-hour version?' },
    d: { es: 'Sin la espera del atardecer en Tebenquiche. Si necesitan las 4 horas cotizadas, avísennos: la única forma de darlas es correr el Valle de la Luna de ese día, que ya está visto con el cliente.', en: 'Without waiting for sunset at Tebenquiche. If you need the four hours quoted, tell us: the only way to give them is to shift that day’s Valle de la Luna, which the client has already signed off.' } },
  { q: { es: '¿Pueden correr el Valle de la Muerte al 24 de 15:30 a 17:30?', en: 'Can you move Valle de la Muerte to 15:30–17:30 on the 24th?' },
    d: { es: '¿El sandboard funciona a esa hora o recomiendan otra franja?', en: 'Does sandboarding work at that hour, or would you recommend another slot?' } },
  { q: { es: 'Cuadriciclos: ¿los operan ustedes?', en: 'Quad bikes: do you operate them?' },
    d: { es: 'El cliente los pidió como alternativa al sandboard. El sandboard está cotizado; los cuadriciclos no. Si no los operan, ¿con quién se coordinan?', en: 'The client asked for them as an alternative to sandboarding. Sandboarding is quoted; quad bikes are not. If you do not run them, who do you coordinate with?' } },
  { q: { es: 'Astronomía del 24: la luna está llena.', en: 'Astronomy on the 24th: the moon is full.' },
    d: { es: 'La noche del 24 la luna va cerca del 99% iluminada, y ninguna otra noche del viaje es mejor — del 22 al 26 va entre 92% y 100%. ¿Cómo lo manejan en un privado? ¿Vale la pena igual, orientándolo a luna y planetas?', en: 'On the night of the 24th the moon is close to 99% illuminated, and no other night of the trip is better — from the 22nd to the 26th it runs between 92% and 100%. How do you handle that on a private tour? Is it still worth it, aimed at the moon and planets?' } },
  { q: { es: 'Recogida de la astronomía en el restaurante a las 22:10.', en: 'Astronomy pickup at the restaurant at 22:10.' },
    d: { es: 'La cena de Nochebuena termina a las 22:00 y el tour arranca 22:15. Nos confirman el punto exacto.', en: 'The Christmas Eve dinner ends at 22:00 and the tour starts at 22:15. Please confirm the exact pickup point.' } },
  { q: { es: 'Valle de la Luna del 23 arrancando a las 17:30.', en: 'Valle de la Luna on the 23rd starting at 17:30.' },
    d: { es: '¿Alcanza para el circuito off circuit por el Vallecito, que se hace caminando? Es el que está cotizado.', en: 'Is that enough for the off-circuit route through El Vallecito, which is walked? That is the one quoted.' } },
  { q: { es: 'Full day del 25: alcance y entradas.', en: 'Full day on the 25th: scope and entrance fees.' },
    d: { es: 'Entra Chaxa y salen Tuyajto y las protoaldeas respecto de lo cotizado. ¿Cómo queda el valor y qué entradas hay que comprar por adelantado?', en: 'Chaxa comes in and Tuyajto and the proto-villages drop out compared with the quote. How does the price land, and which tickets have to be bought in advance?' } },
  { q: { es: 'Quitor el 26 en hora y media.', en: 'Quitor on the 26th in ninety minutes.' },
    d: { es: 'Está cotizado a dos horas. ¿Entra el recorrido guiado en 08:30–10:00, o mejor arrancamos 08:15?', en: 'It is quoted at two hours. Does the guided route fit 08:30–10:00, or should we start at 08:15?' } },
  { q: { es: 'Tarifas para 4 pasajeros y vehículo del full day.', en: 'Rates for 4 travellers and the full-day vehicle.' },
    d: { es: 'Las alternativas de trekking venían cotizadas como mínimo de grupo para 2. Y para el full day al altiplano, con 4 pasajeros más guía-conductor y equipaje, ¿qué vehículo asignan?', en: 'The trekking alternatives were quoted as a minimum group rate for 2. And for the altiplano full day, with 4 travellers plus guide-driver and luggage, which vehicle would you assign?' } },
];

// --- Textos ------------------------------------------------------------------

const cuentaOurs = DIAS.flatMap((d) => d.blks).filter((b) => b.tipo === 'tourevo').length;
const cuentaCambios = DIAS.flatMap((d) => d.blks).filter((b) => b.e === 'movido' || b.e === 'nuevo').length;

const T = {
  es: {
    titulo: `Tourevo · Itinerario propuesto — ${cliente.nombre}`,
    eyebrow: 'Itinerario propuesto',
    h1: 'Horarios que necesitamos confirmar',
    sub: 'Este es el itinerario que proponemos operar del 22 al 26 de diciembre. Está armado sobre el que el cliente ya revisó, así que casi todo queda tal cual: se mueve una excursión, se agrega la que faltaba y se suma un bloque de check-out.',
    chips: [['Ref.', doc.numero], ['Programa', '22 – 26 dic 2026'], ['Pasajeros', String(doc.pax)]],
    lLeyenda: 'El día completo, hora por hora',
    ledeLeyenda: `Los ${cuentaOurs} bloques marcados son los servicios que operamos nosotros. Los demás — vuelos, comidas, check-in y tiempo libre — van en gris, sólo para que se vea el día entero y se entienda de dónde sale cada horario.`,
    swTourevo: 'Servicio nuestro', swTercero: 'Vuelos, comidas y check-in', swLibre: 'Tiempo libre',
    lCambios: `Lo único que se movió: ${cuentaCambios} bloques`,
    ledeCambios: 'Todo lo demás queda exactamente en el día y la hora que el cliente ya vio.',
    lQs: 'Lo que necesitamos que confirmen',
    ledeQs: 'En orden de urgencia. Las dos primeras condicionan el armado completo.',
    cierre: 'Quedamos atentos para cerrar horarios y bloquear los cupos.',
    legend: `Itinerario propuesto sobre la cotización ${doc.numero} · Tourevo`,
  },
  en: {
    titulo: `Tourevo · Proposed itinerary — ${cliente.nombre}`,
    eyebrow: 'Proposed itinerary',
    h1: 'Times we need confirmed',
    sub: 'This is the itinerary we propose to run from 22 to 26 December. It is built on the one the client has already reviewed, so almost everything stays as it is: one excursion moves, the missing one is added, and a check-out block is inserted.',
    chips: [['Ref.', doc.numero], ['Programme', '22 – 26 Dec 2026'], ['Travellers', String(doc.pax)]],
    lLeyenda: 'The full day, hour by hour',
    ledeLeyenda: `The ${cuentaOurs} highlighted blocks are the services we operate. The rest — flights, meals, check-in and free time — are in grey, purely so the whole day is visible and every time makes sense in context.`,
    swTourevo: 'Our service', swTercero: 'Flights, meals and check-in', swLibre: 'Free time',
    lCambios: `The only things that moved: ${cuentaCambios} blocks`,
    ledeCambios: 'Everything else stays on exactly the day and time the client has already seen.',
    lQs: 'What we need you to confirm',
    ledeQs: 'In order of urgency. The first two govern how the whole thing is built.',
    cierre: 'We look forward to closing the times and holding the spaces.',
    legend: `Proposed itinerary against quotation ${doc.numero} · Tourevo`,
  },
};

// --- Render ------------------------------------------------------------------

const bloque = (b, l) => `
          <div class="blk ${b.tipo}">
            <div class="hh">${b.h}${b.f2 ? `<i>${b.f2}</i>` : ''}</div>
            <div>
              <div class="tt">${esc(b.t[l])}</div>
              ${b.n ? `<span class="nn">${b.n[l]}</span>` : ''}
            </div>
            ${b.e ? `<span class="badge ${E[b.e].c}">${E[b.e].t[l]}</span>` : '<span></span>'}
          </div>`;

const dia = (d, l) => {
  const nuestros = d.blks.filter((b) => b.tipo === 'tourevo').length;
  return `
      <article class="day">
        <div class="day-head">
          <span class="day-date">${esc(mayus(fecha(d.f, l, { weekday: 'long', day: 'numeric', month: 'long' })))}</span>
          <span class="day-name">${esc(d.n[l])}</span>
          <span class="day-sum">${nuestros} ${l === 'es' ? (nuestros === 1 ? 'servicio nuestro' : 'servicios nuestros') : (nuestros === 1 ? 'service of ours' : 'services of ours')}</span>
        </div>
        <div class="tl">
${d.blks.map((b) => bloque(b, l)).join('\n')}
        </div>
      </article>`;
};

const cambio = (c, i, l) => `
        <article class="find">
          <div class="find-top">
            <span class="find-n">${i + 1}</span>
            <h4>${esc(c.tit[l])}</h4>
          </div>
          ${c.cuerpo[l].map((p) => `<p>${p}</p>`).join('\n          ')}
        </article>`;

function pagina(l) {
  const t = T[l];
  return `<!-- Generado por build-itinerario.mjs — no editar a mano. -->
<title>${esc(t.titulo)}</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${estilos}${estilosComparativo}${estilosItinerario}</style>

<div class="wrap">
  <article class="paper">
${cabecera({ eyebrow: t.eyebrow, h1: t.h1, sub: t.sub, chips: t.chips })}

    <div class="body">

      <section>
        <p class="label">${t.lCambios}</p>
        <p class="lede">${t.ledeCambios}</p>
        <div class="finds">
${CAMBIOS.map((c, i) => cambio(c, i, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lLeyenda}</p>
        <p class="lede">${t.ledeLeyenda}</p>
        <p class="leyenda">
          <span><i class="sw tourevo"></i>${t.swTourevo}</span>
          <span><i class="sw tercero"></i>${t.swTercero}</span>
          <span><i class="sw libre"></i>${t.swLibre}</span>
        </p>
        <div class="days">
${DIAS.map((d) => dia(d, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lQs}</p>
        <p class="lede">${t.ledeQs}</p>
        <ol class="qs">
${PREGUNTAS.map((q) => `          <li><span style="display:block"><b>${esc(q.q[l])}</b><span>${esc(q.d[l])}</span></span></li>`).join('\n')}
        </ol>
      </section>

      <div class="cta">
        <p class="lead">${esc(t.cierre)}</p>
      </div>

    </div>
${piePagina()}
  </article>
</div>
<p class="legend">${esc(t.legend)}</p>
`;
}

const SALIDAS = [
  { l: 'es', html: 'itinerario.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Itinerario-propuesto-ES.pdf` },
  { l: 'en', html: 'itinerario-en.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Itinerario-propuesto-EN.pdf` },
];

for (const s of SALIDAS) {
  writeFileSync(join(AQUI, s.html), pagina(s.l));
  console.log(`✓ ${s.html}`);
}
console.log(`\n  ${DIAS.flatMap((d) => d.blks).length} bloques · ${cuentaOurs} servicios nuestros · ${cuentaCambios} movidos o nuevos\n`);

if (!soloHtml) await aPdf(SALIDAS, { aqui: AQUI, pie: `Tourevo · ${doc.numero} · ${cliente.nombre} · itinerario propuesto` });
