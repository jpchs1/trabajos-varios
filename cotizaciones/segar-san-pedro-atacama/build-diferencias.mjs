#!/usr/bin/env node
// Comparativo entre el programa cotizado (COT-2026-0160) y el itinerario que
// mandó el cliente. Sale en español y en inglés, HTML y PDF.
//
//   node build-diferencias.mjs [--solo-html]
//
// Los montos se leen de contenido.mjs, así que si la cotización cambia, el
// impacto en el valor de este documento se recalcula solo.

import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { doc, cliente, programa } from './contenido.mjs';
import { estilos, estilosComparativo } from './estilos.mjs';
import { plata, fecha, mayus, esc, cabecera, piePagina, aPdf } from './comun.mjs';

const AQUI = dirname(fileURLToPath(import.meta.url));
const soloHtml = process.argv.includes('--solo-html');

// --- Impacto en el valor -----------------------------------------------------
// Qué pasa con cada uno de los nueve servicios cotizados bajo el itinerario
// nuevo. Los montos salen de contenido.mjs; acá sólo se clasifica.

const DESTINO = {
  'transfer-in': 'mantiene',     // mismo servicio, ahora con horario
  'puritama': 'sale',            // no aparece en el itinerario
  'luna-sur': 'recotiza',        // otro horario, circuito por confirmar
  'astronomico': 'recotiza',     // compartido → privado, y otro día
  'marte-sandboard': 'recotiza', // sandboard → cuadriciclos
  'cejar': 'sale',               // no aparece; el itinerario apunta a Baltinache
  'piedras-rojas': 'recotiza',   // otro alcance y dos horas más
  'arqueologico': 'mantiene',    // mismo servicio, cambia de día
  'transfer-out': 'sale',        // falta en el itinerario
};

const sub = (s) => s.valor + s.entradas;
const impacto = { mantiene: 0, recotiza: 0, sale: 0 };
const cuenta = { mantiene: 0, recotiza: 0, sale: 0 };
for (const s of programa) {
  impacto[DESTINO[s.id]] += sub(s);
  cuenta[DESTINO[s.id]] += 1;
}
const totalPack = programa.reduce((a, s) => a + sub(s), 0);
const pct = (n) => `${Math.round((n / totalPack) * 1000) / 10}`.replace('.', ',');
const pctEn = (n) => `${Math.round((n / totalPack) * 1000) / 10}`;

// --- Comparativo día a día ---------------------------------------------------

const ESTADOS = {
  ok:    { c: 'b-ok',  t: { es: 'Se mantiene', en: 'Unchanged' } },
  chg:   { c: 'b-chg', t: { es: 'Cambia', en: 'Changes' } },
  x:     { c: 'b-x',   t: { es: 'Hay que resolver', en: 'Needs resolving' } },
  out:   { c: 'b-out', t: { es: 'No aparece', en: 'Not in schedule' } },
};

const N = { es: '—', en: '—' };

const COMPARACION = [
  { f: '2026-12-22', e: 'ok',
    cot: { t: { es: 'Traslado de llegada · El Loa → San Pedro', en: 'Arrival transfer · El Loa → San Pedro' }, h: { es: 'Horario a coordinar', en: 'Time to be confirmed' } },
    rec: { t: { es: 'Transfer Calama → San Pedro de Atacama', en: 'Transfer Calama → San Pedro de Atacama' }, h: { es: '18:15 – 19:35', en: '18:15 – 19:35' } },
    obs: { es: 'Mismo servicio. El itinerario fija el horario que la cotización dejaba abierto.', en: 'Same service. The schedule pins down the time the quotation had left open.' } },

  { f: '2026-12-22', e: 'x',
    cot: null,
    rec: { t: { es: 'Aclimatación · mañana tranquila en San Pedro', en: 'Acclimatization · calm morning in San Pedro' }, h: { es: '09:00 – 13:00', en: '09:00 – 13:00' } },
    obs: { es: 'Choca con la llegada de las 19:35 del mismo día — ver el punto 2. En esta línea el itinerario anota «Check Baltinache», que no es lo mismo que Cejar.', en: 'Clashes with the 19:35 arrival on the same day — see point 2. On this line the schedule notes “Check Baltinache”, which is not the same thing as Cejar.' } },

  { f: '2026-12-22', e: 'chg',
    cot: { t: { es: 'Tour astronómico · compartido', en: 'Astronomy tour · shared' }, h: { es: 'Mié 23 · 21:00 – 23:00', en: 'Wed 23 · 21:00 – 23:00' } },
    rec: { t: { es: 'Astronomía privada', en: 'Private astronomy' }, h: { es: '22:15 – 23:59', en: '22:15 – 23:59' } },
    obs: { es: 'Cambia de día, de horario y de modalidad. Lo cotizado es compartido, CLP 40.000 por persona, coordinado como cortesía. Privado es otro producto y hay que cotizarlo.', en: 'Changes day, time and format. What is quoted is the shared tour at CLP 40,000 per person, coordinated as a courtesy. Private is a different product and has to be quoted.' } },

  { f: '2026-12-23', e: 'out',
    cot: { t: { es: 'Termas de Puritama', en: 'Puritama Hot Springs' }, h: { es: '09:30 – 13:30', en: '09:30 – 13:30' } },
    rec: null,
    obs: { es: 'No está en el itinerario. Son CLP 75.000 más CLP 35.000 de entrada por persona.', en: 'Not in the schedule. It is CLP 75,000 plus CLP 35,000 in park fees per person.' } },

  { f: '2026-12-23', e: 'chg',
    cot: { t: { es: 'Valle de la Luna Sur', en: 'Valle de la Luna South' }, h: { es: '16:30 – 20:30', en: '16:30 – 20:30' } },
    rec: { t: { es: 'Valle de la Luna al atardecer', en: 'Valle de la Luna at sunset' }, h: { es: '17:30 – 20:45', en: '17:30 – 20:45' } },
    obs: { es: 'Una hora más tarde y quince minutos más largo. Hay que confirmar que sea el mismo circuito off circuit por el Vallecito y no el recorrido estándar, que es otro producto y con mucha más gente.', en: 'An hour later and fifteen minutes longer. Worth confirming it is the same off-circuit route through El Vallecito and not the standard circuit, which is a different product and far busier.' } },

  { f: '2026-12-23', e: 'chg',
    cot: { t: { es: 'Cotizado para el sábado 26', en: 'Quoted for Saturday 26' }, h: { es: '08:00 – 10:00', en: '08:00 – 10:00' } },
    rec: { t: { es: 'Pukará de Quitor', en: 'Pukará de Quitor' }, h: { es: 'Sin horario', en: 'No time given' } },
    obs: { es: 'Se adelanta del sábado 26 al miércoles 23 y queda sin hora. Además preguntan si guiado o autoguiado: la respuesta está en el punto 6.', en: 'Moves forward from Saturday 26 to Wednesday 23 and has no time. The schedule also asks whether it is self-guided: the answer is in point 6.' } },

  { f: '2026-12-24', e: 'chg',
    cot: { t: { es: 'Valle de Marte + Sandboard', en: 'Valle de Marte + Sandboarding' }, h: { es: '09:30 – 13:30', en: '09:30 – 13:30' } },
    rec: { t: { es: 'Death Valley · cuadriciclos', en: 'Death Valley · quad bikes' }, h: { es: '13:30 – 15:30', en: '13:30 – 15:30' } },
    obs: { es: 'Es el mismo valle — «Valle de la Muerte» viene de una mala transcripción de Valle de Marte — pero otro producto y otro operador. Dos horas en vez de cuatro, y en la franja de más calor de un día de diciembre. No está cotizado.', en: 'It is the same valley — “Valle de la Muerte” comes from a mistranscription of Valle de Marte — but a different product and a different operator. Two hours instead of four, and in the hottest part of a December day. Not quoted.' } },

  { f: '2026-12-24', e: 'out',
    cot: { t: { es: 'Laguna Cejar + Ojos de Tebenquiche + Laguna de Tebenquiche', en: 'Cejar Lagoon + Ojos de Tebenquiche + Tebenquiche Lagoon' }, h: { es: '15:30 – 19:30', en: '15:30 – 19:30' } },
    rec: null,
    obs: { es: 'No está en el itinerario. Son CLP 80.000 más CLP 21.000 de entrada por persona. Si la idea es reemplazarlo por Baltinache, es otra cotización: otro acceso, otro cupo y sin la flotación de Cejar.', en: 'Not in the schedule. It is CLP 80,000 plus CLP 21,000 in park fees per person. If the idea is to swap it for Baltinache, that is a separate quote: different access, different capacity, and without the floating at Cejar.' } },

  { f: '2026-12-25', e: 'x',
    cot: { t: { es: 'Piedras Rojas + Tuyajto + Lagunas Altiplánicas + Protoaldeas + Pueblos', en: 'Piedras Rojas + Tuyajto + Altiplanic Lagoons + Proto-villages + Andean towns' }, h: { es: '10:00 – 18:00', en: '10:00 – 18:00' } },
    rec: { t: { es: 'Altiplano · Chaxa, Toconao, Miscanti, Piedras Rojas y Valle del Arcoíris', en: 'Altiplano · Chaxa, Toconao, Miscanti, Piedras Rojas & Rainbow Valley' }, h: { es: '08:00 – 18:00', en: '08:00 – 18:00' } },
    obs: { es: 'Dos horas más y otro alcance: entra Chaxa, que tiene su propia entrada, y el Valle del Arcoíris; salen Tuyajto y las protoaldeas. El Arcoíris no se combina con Piedras Rojas — ver el punto 3.', en: 'Two hours longer and a different scope: Chaxa comes in, with its own entrance fee, and Rainbow Valley; Tuyajto and the proto-villages drop out. Rainbow Valley does not combine with Piedras Rojas — see point 3.' } },

  { f: '2026-12-26', e: 'chg',
    cot: { t: { es: 'Tour arqueológico · Pukará de Quitor', en: 'Archaeological tour · Pukará de Quitor' }, h: { es: '08:00 – 10:00', en: '08:00 – 10:00' } },
    rec: { t: { es: 'Movido al miércoles 23', en: 'Moved to Wednesday 23' }, h: { es: '—', en: '—' } },
    obs: { es: 'El servicio sigue siendo el mismo; cambia el día.', en: 'The service itself is unchanged; only the day moves.' } },

  { f: '2026-12-26', e: 'x',
    cot: { t: { es: 'Traslado de salida · San Pedro → El Loa', en: 'Departure transfer · San Pedro → El Loa' }, h: { es: 'Horario a confirmar', en: 'Time to be confirmed' } },
    rec: null,
    obs: { es: 'No está en el itinerario, y hace falta el vuelo de salida para fijarlo. Ver el punto 4.', en: 'Not in the schedule, and the outbound flight is needed to set it. See point 4.' } },
];

// --- Lo que hay que resolver -------------------------------------------------
// Ordenado por lo que rompe el viaje si no se toca, no por fecha.

const HALLAZGOS = [
  { grave: true,
    tit: { es: 'El 24 y el 25 son Nochebuena y Navidad', en: '24 and 25 December are Christmas Eve and Christmas Day' },
    cuerpo: {
      es: ['Dos de los cuatro días de actividades caen en feriado: el <b>jueves 24</b> con los cuadriciclos y el <b>viernes 25</b> con el full day al altiplano, que además es el servicio más caro del programa y el más difícil de armar.',
           'En San Pedro esos dos días la operación se achica. Hay operadores que directamente no salen el 25, los guías y conductores que sí trabajan van con recargo, y los sitios administrados por CONAF pueden tener horario especial. Nada de esto es imposible, pero es lo primero que hay que confirmar y lo último que conviene dejar para el final.'],
      en: ['Two of the four activity days fall on public holidays: <b>Thursday 24</b> with the quad bikes and <b>Friday 25</b> with the full-day altiplano trip, which is also the most expensive service in the programme and the hardest to put together.',
           'Operations in San Pedro shrink on those two days. Some operators simply do not run on the 25th, the guides and drivers who do work carry a holiday supplement, and CONAF-administered sites may keep special hours. None of this is impossible, but it is the first thing to confirm and the last thing to leave until the end.'],
    },
    fix: { es: 'Pedir disponibilidad del 24 y del 25 antes que cualquier otra cosa, con el recargo de feriado por escrito. Si el 25 no sale, el full day se corre al miércoles 23 y el resto del programa se reordena alrededor.',
           en: 'Ask for availability on the 24th and 25th before anything else, with the holiday supplement in writing. If the 25th is not viable, the full day moves to Wednesday 23 and the rest of the programme reorders around it.' } },

  { grave: true,
    tit: { es: 'La mañana del 22 no existe: el vuelo llega a las 18:15', en: 'The morning of the 22nd does not exist: the flight lands at 18:15' },
    cuerpo: {
      es: ['El itinerario tiene una <b>aclimatación de 09:00 a 13:00 el martes 22</b> y, el mismo día, el traslado desde Calama que llega a San Pedro <b>a las 19:35</b>. No se puede tener una mañana tranquila en San Pedro nueve horas antes de llegar.',
           'Es además la única línea del itinerario que no dice <i>To be booked</i>, así que probablemente sea un marcador de posición y no un servicio pedido.'],
      en: ['The schedule has an <b>acclimatization block from 09:00 to 13:00 on Tuesday 22</b> and, on the same day, the transfer from Calama arriving in San Pedro <b>at 19:35</b>. You cannot have a calm morning in San Pedro nine hours before arriving there.',
           'It is also the only line in the schedule that does not say <i>To be booked</i>, so it is most likely a placeholder rather than a requested service.'],
    },
    fix: { es: 'Confirmar si la aclimatación va el <b>miércoles 23 por la mañana</b>. Si va ahí, hay que decidir qué pasa con el Pukará de Quitor, que también está el 23 y sin horario.',
           en: 'Confirm whether the acclimatization belongs on <b>Wednesday 23 in the morning</b>. If it does, a decision is needed on the Pukará de Quitor, which is also on the 23rd and has no time.' } },

  { grave: true,
    tit: { es: 'El Valle del Arcoíris no cabe en el día de Piedras Rojas', en: 'Rainbow Valley does not fit into the Piedras Rojas day' },
    cuerpo: {
      es: ['El viernes 25 junta <b>Chaxa, Toconao, Miscanti, Piedras Rojas y el Valle del Arcoíris</b> en una sola salida de 08:00 a 18:00.',
           'Los primeros cuatro están al <b>sureste</b> de San Pedro, subiendo por el borde este del salar hasta más de 4.000 metros. El Valle del Arcoíris está al <b>noroeste</b>, camino a Río Grande, en la dirección contraria. Meterlos en el mismo día son varios cientos de kilómetros extra y deja todo lo demás en pasadas de auto.'],
      en: ['Friday 25 puts <b>Chaxa, Toconao, Miscanti, Piedras Rojas and Rainbow Valley</b> into a single outing from 08:00 to 18:00.',
           'The first four lie <b>south-east</b> of San Pedro, climbing the eastern rim of the salt flat to over 4,000 metres. Rainbow Valley lies <b>north-west</b>, on the road to Río Grande, in the opposite direction. Putting them in one day adds several hundred kilometres of driving and reduces everything else to drive-past stops.'],
    },
    fix: { es: 'Sacar el Valle del Arcoíris del 25 y darle su propia media jornada. El <b>jueves 24 por la mañana</b> es el hueco natural, antes de los cuadriciclos de las 13:30 — sujeto a que haya operación en Nochebuena.',
           en: 'Take Rainbow Valley out of the 25th and give it its own half day. <b>Thursday 24 in the morning</b> is the natural gap, before the 13:30 quad bikes — subject to operators running on Christmas Eve.' } },

  { grave: false,
    tit: { es: 'Falta el traslado de salida', en: 'The departure transfer is missing' },
    cuerpo: {
      es: ['El itinerario tiene el traslado de llegada del 22, pero <b>no tiene el de vuelta</b> al aeropuerto de El Loa. La cotización sí lo incluye, el sábado 26, a CLP 30.000 por persona.'],
      en: ['The schedule has the arrival transfer on the 22nd but <b>not the return</b> to El Loa airport. The quotation does include it, on Saturday 26, at CLP 30,000 per person.'],
    },
    fix: { es: 'Mandar el vuelo de salida para fijar el horario. Y si el viaje termina antes del 26, decirlo: cambia el último día del programa.',
           en: 'Send the outbound flight so the time can be set. And if the trip ends before the 26th, say so: it changes the last day of the programme.' } },

  { grave: false,
    tit: { es: 'El miércoles 23 tiene dos actividades y sólo una tiene hora', en: 'Wednesday 23 has two activities and only one has a time' },
    cuerpo: {
      es: ['El 23 aparecen el <b>Valle de la Luna al atardecer, de 17:30 a 20:45</b>, y el <b>Pukará de Quitor sin horario</b>. En la cotización, Quitor estaba el sábado 26 de 08:00 a 10:00.',
           'Si además se mueve ahí la aclimatación de 09:00 a 13:00, el día queda con tres bloques y Quitor sin lugar donde entrar.'],
      en: ['The 23rd carries the <b>Valle de la Luna at sunset, 17:30 to 20:45</b>, and the <b>Pukará de Quitor with no time</b>. In the quotation, Quitor sat on Saturday 26 from 08:00 to 10:00.',
           'If the 09:00–13:00 acclimatization also moves there, the day ends up with three blocks and Quitor has nowhere to go.'],
    },
    fix: { es: 'Decidir el orden del 23. Quitor temprano y la aclimatación después funciona; los tres bloques juntos, no.',
           en: 'Decide the order of the 23rd. Quitor early with the acclimatization after it works; all three blocks together does not.' } },

  { grave: false,
    tit: { es: 'Quitor: se puede ir por cuenta propia, pero no conviene', en: 'Quitor: you can go on your own, but it is not worth it' },
    cuerpo: {
      es: ['Es la pregunta que trae el itinerario. Al Pukará se puede entrar por cuenta propia pagando la entrada a la comunidad: es un sitio abierto y con senderos marcados.',
           'Dicho eso, sin guía es una ladera con muros de piedra. Lo que hace que la visita valga son las dos horas de interpretación: qué era cada recinto, cómo cayó y por qué se lo recuerda como la última resistencia atacameña. La cotización lo lleva guiado, a CLP 75.000 por persona más CLP 6.000 de entrada.'],
      en: ['This is the question the schedule raises. You can visit the Pukará on your own by paying the community entrance: it is an open site with marked paths.',
           'That said, without a guide it is a hillside with stone walls. What makes the visit worth it is the two hours of interpretation: what each enclosure was, how it fell, and why it is remembered as the last Atacameño stand. The quotation carries it guided, at CLP 75,000 per person plus CLP 6,000 entrance.'],
    },
    fix: { es: 'Recomendamos guiado. Si prefieren autoguiado, se descuentan los CLP 75.000 del servicio y queda sólo la entrada.',
           en: 'We recommend the guided version. If you prefer self-guided, the CLP 75,000 service comes off and only the entrance fee remains.' } },
];

const PREGUNTAS = [
  { q: { es: '¿La aclimatación del 22 es en realidad el 23?', en: 'Is the acclimatization on the 22nd actually meant to be the 23rd?' },
    d: { es: '¿Y es un servicio guiado o simplemente tiempo libre en San Pedro? Si es guiado, hay que cotizarlo.', en: 'And is it a guided service or simply free time in San Pedro? If it is guided, it needs quoting.' } },
  { q: { es: '¿Cuál es el vuelo de salida?', en: 'What is the outbound flight?' },
    d: { es: 'Sin eso no se puede fijar el traslado a El Loa ni cerrar el último día.', en: 'Without it the transfer to El Loa cannot be scheduled and the last day cannot be closed.' } },
  { q: { es: '¿El Valle del Arcoíris se saca o se le da su propia media jornada?', en: 'Does Rainbow Valley come out, or does it get its own half day?' },
    d: { es: 'Si se queda, el jueves 24 por la mañana es el hueco natural.', en: 'If it stays, Thursday 24 in the morning is the natural gap.' } },
  { q: { es: '¿Baltinache en vez de Cejar, o ninguno de los dos?', en: 'Baltinache instead of Cejar, or neither?' },
    d: { es: 'El itinerario lo deja anotado como pendiente de revisar, y son productos distintos.', en: 'The schedule leaves it flagged for review, and they are different products.' } },
  { q: { es: '¿Puritama queda fuera del viaje?', en: 'Is Puritama out of the trip?' },
    d: { es: 'Estaba en el programa cotizado y no aparece en el itinerario.', en: 'It was in the quoted programme and does not appear in the schedule.' } },
  { q: { es: '¿Quitor guiado o autoguiado, y a qué hora?', en: 'Quitor guided or self-guided, and at what time?' },
    d: { es: 'Nuestra recomendación está en el punto 6.', en: 'Our recommendation is in point 6.' } },
  { q: { es: '¿Los cuadriciclos reemplazan al sandboard?', en: 'Do the quad bikes replace the sandboarding?' },
    d: { es: 'No los operamos nosotros: hay que buscar quién y cotizarlo aparte.', en: 'We do not operate them: we need to find who does and quote it separately.' } },
  { q: { es: '¿La astronomía privada va confirmada?', en: 'Is the private astronomy confirmed?' },
    d: { es: 'Cambia el valor respecto de la compartida que está cotizada, y cambia de noche.', en: 'It changes the price against the shared tour that is quoted, and it changes night.' } },
  { q: { es: 'Disponibilidad y recargo del 24 y 25 de diciembre.', en: 'Availability and holiday supplement for 24 and 25 December.' },
    d: { es: 'Es lo primero que hay que cerrar: condiciona los dos días más caros del programa.', en: 'This is the first thing to close: it governs the two most expensive days of the programme.' } },
  { q: { es: 'Qué vehículo va al full day con 4 pasajeros.', en: 'Which vehicle takes the full day with 4 travellers.' },
    d: { es: 'Con 4 pax más guía-conductor la Tahoe entra cómoda con equipaje; la 4Runner queda al límite en una salida de diez horas al altiplano. Conviene fijarlo ahora.', en: 'With 4 travellers plus a guide-driver the Tahoe is comfortable with luggage; the 4Runner is at its limit on a ten-hour run up to the altiplano. Better settled now.' } },
];

// --- Textos ------------------------------------------------------------------

const T = {
  es: {
    titulo: `Tourevo · Ajustes de días y horarios — ${cliente.nombre}`,
    eyebrow: 'Ajustes al programa',
    h1: 'Diferencias entre lo cotizado y el itinerario recibido',
    sub: 'Qué coincide, qué cambia de día u horario, qué falta y qué hay que resolver antes de reservar. Comparación línea por línea entre la cotización COT-2026-0160 y el itinerario que nos enviaron.',
    chips: [['Ref.', doc.numero], ['Programa', '22 – 26 dic 2026'], ['Pasajeros', String(doc.pax)]],
    lHall: 'Lo que hay que resolver primero',
    ledeHall: 'Seis puntos, en orden de lo que rompe el viaje si no se toca. Los tres primeros bloquean: hoy el itinerario, tal como está, no se puede operar.',
    lCmp: 'Día a día: lo cotizado contra lo recibido',
    ledeCmp: 'Once líneas. A la izquierda el programa cotizado, a la derecha el itinerario que llegó.',
    thCot: 'Programa cotizado',
    thRec: 'Itinerario recibido',
    thEst: 'Estado',
    lImp: 'Qué pasa con el valor cotizado',
    impKeep: 'Se mantiene', impReq: 'Hay que recotizar', impGone: 'Sale del itinerario',
    impSvc: (n) => `${n} de ${programa.length} servicios`,
    ledeImp1: `Sobre los <b>${plata(totalPack, 'es')} por persona</b> de la cotización, hoy sólo pasan sin cambios el traslado de llegada y el tour arqueológico. Todo lo demás cambia de alcance, de modalidad o se cae.`,
    ledeImp2: `A eso se le suman dos cosas que mueven el valor por su cuenta y que no dependen del itinerario: el grupo pasó de 2 a <b>${doc.pax} pasajeros</b> — las dos alternativas de trekking estaban cotizadas como tarifa mínima para 2 y hay que volver a pedirlas — y el viaje es del <b>22 al 26 de diciembre</b>, temporada alta y con dos feriados adentro. En la práctica, la cotización hay que rehacerla casi entera.`,
    lNuevo: 'Además, en el itinerario hay cinco cosas que no están cotizadas',
    nuevos: [
      'La <b>aclimatación</b> del primer día, si es servicio guiado y no tiempo libre.',
      'La <b>astronomía privada</b>, en vez de la compartida que está cotizada.',
      'Los <b>cuadriciclos</b> en el Valle de la Muerte.',
      'La <b>laguna Chaxa</b>, agregada al día de altiplano, con su propia entrada.',
      'El <b>Valle del Arcoíris</b>, que necesita su propia media jornada.',
    ],
    lQs: 'Lo que necesito confirmar',
    queHacemos: 'Qué hacemos',
    cierre: 'Con esas respuestas rearmo el programa y mando la cotización corregida para 4 pasajeros y fechas de diciembre.',
    legend: `Comparativo sobre la cotización ${doc.numero} · Tourevo`,
  },

  en: {
    titulo: `Tourevo · Day and time adjustments — ${cliente.nombre}`,
    eyebrow: 'Programme adjustments',
    h1: 'Differences between the quotation and the schedule received',
    sub: 'What matches, what changes day or time, what is missing and what has to be resolved before booking. A line-by-line comparison between quotation COT-2026-0160 and the schedule sent to us.',
    chips: [['Ref.', doc.numero], ['Programme', '22 – 26 Dec 2026'], ['Travellers', String(doc.pax)]],
    lHall: 'What has to be resolved first',
    ledeHall: 'Six points, ordered by what breaks the trip if left alone. The first three are blocking: as it stands today, the schedule cannot be operated.',
    lCmp: 'Day by day: quoted against received',
    ledeCmp: 'Eleven lines. On the left the quoted programme, on the right the schedule that arrived.',
    thCot: 'Quoted programme',
    thRec: 'Schedule received',
    thEst: 'Status',
    lImp: 'What happens to the quoted price',
    impKeep: 'Holds', impReq: 'Needs requoting', impGone: 'Out of the schedule',
    impSvc: (n) => `${n} of ${programa.length} services`,
    ledeImp1: `Of the <b>${plata(totalPack, 'en')} per person</b> in the quotation, only the arrival transfer and the archaeological tour carry over unchanged today. Everything else changes scope, changes format, or drops out.`,
    ledeImp2: `On top of that, two things move the price on their own and have nothing to do with the schedule: the party went from 2 to <b>${doc.pax} travellers</b> — both trekking alternatives were quoted as a minimum rate for 2 and have to be requested again — and the trip runs <b>22 to 26 December</b>, high season with two public holidays inside it. In practice the quotation has to be rebuilt almost entirely.`,
    lNuevo: 'The schedule also carries five things that are not quoted',
    nuevos: [
      'The <b>acclimatization</b> on day one, if it is a guided service and not free time.',
      'The <b>private astronomy</b> tour, instead of the shared one that is quoted.',
      'The <b>quad bikes</b> in the Valle de la Muerte.',
      '<b>Laguna Chaxa</b>, added to the altiplano day, with its own entrance fee.',
      '<b>Rainbow Valley</b>, which needs a half day of its own.',
    ],
    lQs: 'What I need confirmed',
    queHacemos: 'What we do',
    cierre: 'With those answers I will rebuild the programme and send the corrected quotation for 4 travellers on December dates.',
    legend: `Comparison against quotation ${doc.numero} · Tourevo`,
  },
};

// --- Render ------------------------------------------------------------------

const hallazgo = (h, i, l) => `
        <article class="find${h.grave ? ' stop' : ''}">
          <div class="find-top">
            <span class="find-n">${i + 1}</span>
            <h4>${esc(h.tit[l])}</h4>
          </div>
          ${h.cuerpo[l].map((p) => `<p>${p}</p>`).join('\n          ')}
          <p class="fix"><b>${T[l].queHacemos}:</b> ${h.fix[l]}</p>
        </article>`;

function comparativo(t, l) {
  let html = '';
  let dia = null;
  for (const c of COMPARACION) {
    if (c.f !== dia) {
      dia = c.f;
      html += `
          <tbody class="dg"><tr class="daybar"><td colspan="3">${esc(mayus(fecha(c.f, l, { weekday: 'long', day: 'numeric', month: 'long' })))}</td></tr></tbody>`;
    }
    const celda = (x, clase) => x
      ? `<td class="${clase}">${esc(x.t[l])}<span class="hr">${esc(x.h[l])}</span></td>`
      : `<td class="${clase}"><span class="nada">${N[l]}</span></td>`;
    const e = ESTADOS[c.e];
    html += `
          <tbody class="cg">
            <tr>
              ${celda(c.cot, 'was')}
              ${celda(c.rec, 'now')}
              <td><span class="badge ${e.c}">${e.t[l]}</span></td>
            </tr>
            <tr><td colspan="3" class="obsrow"><span class="obs">${c.obs[l]}</span></td></tr>
          </tbody>`;
  }
  return `
      <div class="tbl-wrap">
        <table class="cmp">
          <thead>
            <tr><th>${t.thCot}</th><th>${t.thRec}</th><th>${t.thEst}</th></tr>
          </thead>
${html}
        </table>
      </div>`;
}

function pagina(l) {
  const t = T[l];
  const p = l === 'es' ? pct : pctEn;
  return `<!-- Generado por build-diferencias.mjs — no editar a mano. -->
<title>${esc(t.titulo)}</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${estilos}${estilosComparativo}</style>

<div class="wrap">
  <article class="paper">
${cabecera({ eyebrow: t.eyebrow, h1: t.h1, sub: t.sub, chips: t.chips })}

    <div class="body">

      <section>
        <p class="label">${t.lHall}</p>
        <p class="lede">${t.ledeHall}</p>
        <div class="finds">
${HALLAZGOS.map((h, i) => hallazgo(h, i, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lCmp}</p>
        <p class="lede">${t.ledeCmp}</p>
${comparativo(t, l)}
      </section>

      <section>
        <p class="label">${t.lImp}</p>
        <div class="impact">
          <div class="imp keep"><div class="k">${t.impKeep}</div><div class="v">${plata(impacto.mantiene, l)}</div><div class="p">${t.impSvc(cuenta.mantiene)} · ${p(impacto.mantiene)}%</div></div>
          <div class="imp req"><div class="k">${t.impReq}</div><div class="v">${plata(impacto.recotiza, l)}</div><div class="p">${t.impSvc(cuenta.recotiza)} · ${p(impacto.recotiza)}%</div></div>
          <div class="imp gone"><div class="k">${t.impGone}</div><div class="v">${plata(impacto.sale, l)}</div><div class="p">${t.impSvc(cuenta.sale)} · ${p(impacto.sale)}%</div></div>
        </div>
        <p class="lede" style="margin-top:14px">${t.ledeImp1}</p>
        <p class="lede">${t.ledeImp2}</p>
        <div class="card" style="margin-top:4px">
          <h4>${t.lNuevo}</h4>
          <ul>${t.nuevos.map((i) => `\n            <li>${i}</li>`).join('')}
          </ul>
        </div>
      </section>

      <section>
        <p class="label">${t.lQs}</p>
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

// --- Salida ------------------------------------------------------------------

const SALIDAS = [
  { l: 'es', html: 'diferencias.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Ajustes-ES.pdf` },
  { l: 'en', html: 'diferencias-en.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Ajustes-EN.pdf` },
];

for (const s of SALIDAS) {
  writeFileSync(join(AQUI, s.html), pagina(s.l));
  console.log(`✓ ${s.html}`);
}
console.log(
  `\n  se mantiene  ${plata(impacto.mantiene, 'es')} (${cuenta.mantiene} servicios, ${pct(impacto.mantiene)}%)` +
  `\n  recotizar    ${plata(impacto.recotiza, 'es')} (${cuenta.recotiza} servicios, ${pct(impacto.recotiza)}%)` +
  `\n  sale         ${plata(impacto.sale, 'es')} (${cuenta.sale} servicios, ${pct(impacto.sale)}%)` +
  `\n  control      ${plata(impacto.mantiene + impacto.recotiza + impacto.sale, 'es')} = pack ${plata(totalPack, 'es')}\n`);

if (!soloHtml) await aPdf(SALIDAS, { aqui: AQUI, pie: `Tourevo · ${doc.numero} · ${cliente.nombre} · ajustes` });
