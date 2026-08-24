#!/usr/bin/env node
// Comparativo de días y horarios: la cotización COT-2026-0160 contra el
// itinerario cargado en el sistema Tourevo (el que ya se vio con Segar).
// Sale en español y en inglés, HTML y PDF.
//
//   node build-diferencias.mjs [--solo-html]
//
// Los montos se leen de contenido.mjs, así que si la cotización cambia, el
// desglose por categoría se recalcula solo.

import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { doc, cliente, programa } from './contenido.mjs';
import { estilos, estilosComparativo } from './estilos.mjs';
import { plata, fecha, mayus, esc, cabecera, piePagina, aPdf } from './comun.mjs';

const AQUI = dirname(fileURLToPath(import.meta.url));
const soloHtml = process.argv.includes('--solo-html');

// --- Comparación servicio por servicio ---------------------------------------
// Los nueve servicios cotizados contra lo que hoy está en el sistema. `e` es la
// diferencia en días y horarios, que es lo que se está revisando:
//   fija  → mismo día; la cotización no tenía hora y el sistema la fija
//   hora  → mismo día, distinto horario
//   dia   → corre de día
//   falta → no está en el sistema

const ESTADOS = {
  fija:  { c: 'b-ok',  t: { es: 'Se fija la hora', en: 'Time now set' } },
  hora:  { c: 'b-chg', t: { es: 'Cambia el horario', en: 'Time changes' } },
  dia:   { c: 'b-x',   t: { es: 'Corre de día', en: 'Moves day' } },
  falta: { c: 'b-out', t: { es: 'No está en el sistema', en: 'Not in the system' } },
};

const COMPARACION = [
  { id: 'transfer-in', e: 'fija',
    cot: { d: '2026-12-22', h: { es: 'Horario a coordinar', en: 'Time to be confirmed' } },
    sis: { d: '2026-12-22', h: { es: '18:15 – 19:15', en: '18:15 – 19:15' } },
    obs: { es: 'Mismo día. El sistema le pone la hora que la cotización dejaba abierta: llegada a San Pedro 19:15 y check-in 19:35. El 22 no queda espacio para nada más, y el sistema tampoco lo tiene: es día completo de viaje desde Puerto Natales.',
           en: 'Same day. The system fills in the time the quotation had left open: arrival in San Pedro 19:15, check-in 19:35. Nothing else fits on the 22nd, and the system does not try: it is a full travel day from Puerto Natales.' } },

  { id: 'puritama', e: 'dia',
    cot: { d: '2026-12-23', h: { es: '09:30 – 13:30', en: '09:30 – 13:30' } },
    sis: { d: '2026-12-24', h: { es: '09:30 – 13:30', en: '09:30 – 13:30' } },
    obs: { es: 'La hora es idéntica; el día corre uno. El sistema anota Puritama a 3.500 m, y correrlo al 24 deja el 23 para aclimatar en San Pedro a 2.400 m antes de subir, con Miscanti por encima de 4.000 m el 25. Leído así el cambio es deliberado y está bien pensado — pero deja el 24 con cinco bloques. Ver el choque 1.',
           en: 'The time is identical; the day moves by one. The system notes Puritama at 3,500 m, and moving it to the 24th leaves the 23rd to acclimatize in San Pedro at 2,400 m before climbing, with Miscanti above 4,000 m on the 25th. Read that way the change is deliberate and well judged — but it leaves the 24th with five blocks. See conflict 1.' } },

  { id: 'luna-sur', e: 'hora',
    cot: { d: '2026-12-23', h: { es: '16:30 – 20:30', en: '16:30 – 20:30' } },
    sis: { d: '2026-12-23', h: { es: '17:30 – 20:45', en: '17:30 – 20:45' } },
    obs: { es: 'Mismo día, una hora más tarde y quince minutos más largo. Hay que confirmar que el circuito cotizado — el off circuit por el Vallecito, que se hace caminando — entre en esa ventana. El recorrido estándar sí entra, pero es otro producto y con mucha más gente.',
           en: 'Same day, an hour later and fifteen minutes longer. Worth confirming the quoted route — the off-circuit through El Vallecito, which is walked — fits that window. The standard circuit does, but it is a different product and far busier.' } },

  { id: 'astronomico', e: 'dia',
    cot: { d: '2026-12-23', h: { es: '21:00 – 23:00 · compartido', en: '21:00 – 23:00 · shared' } },
    sis: { d: '2026-12-24', h: { es: '22:15 – 23:59 · privado', en: '22:15 – 23:59 · private' } },
    obs: { es: 'Corre del 23 al 24, arranca una hora y cuarto más tarde y pasa de compartido a privado. Lo cotizado es el compartido a CLP 40.000 por persona, que coordinamos como cortesía; el privado es otro producto y hay que cotizarlo. Además queda pegado a la cena de Nochebuena. Ver el choque 2.',
           en: 'Moves from the 23rd to the 24th, starts an hour and a quarter later, and goes from shared to private. What is quoted is the shared tour at CLP 40,000 per person, which we coordinate as a courtesy; private is a different product and has to be quoted. It also lands right on top of the Christmas Eve dinner. See conflict 2.' } },

  { id: 'marte-sandboard', e: 'hora',
    cot: { d: '2026-12-24', h: { es: '09:30 – 13:30 · 4 h', en: '09:30 – 13:30 · 4 h' } },
    sis: { d: '2026-12-24', h: { es: '13:30 – 15:30 · 2 h', en: '13:30 – 15:30 · 2 h' } },
    obs: { es: 'Mismo día, pero pasa de la mañana a la tarde y de cuatro horas a dos. El sistema lo deja como «sandboard or quad bikes», sin decidir: el sandboard está cotizado, los cuadriciclos no y no los operamos nosotros. Y a las 13:30 choca con Puritama. Ver el choque 1.',
           en: 'Same day, but it moves from morning to afternoon and from four hours to two. The system leaves it as “sandboard or quad bikes”, undecided: sandboarding is quoted, quad bikes are not and we do not operate them. And at 13:30 it collides with Puritama. See conflict 1.' } },

  { id: 'cejar', e: 'falta',
    cot: { d: '2026-12-24', h: { es: '15:30 – 19:30', en: '15:30 – 19:30' } },
    sis: null,
    obs: { es: 'El único servicio cotizado que no está en el sistema. En su lugar el 24 tiene «Free afternoon» de 15:30 a 17:30 — exactamente donde arrancaba Cejar, pero dos horas en vez de cuatro. Y en la línea del Valle de la Muerte hay un mensaje sin responder: preguntaron si la caminata se puede combinar con una flotada. Esa flotada es Cejar.',
           en: 'The only quoted service missing from the system. In its place the 24th has a “Free afternoon” from 15:30 to 17:30 — exactly where Cejar started, but two hours instead of four. And on the Valle de la Muerte line there is an unanswered message: they asked whether the walk can be combined with a float. That float is Cejar.' } },

  { id: 'piedras-rojas', e: 'hora',
    cot: { d: '2026-12-25', h: { es: '10:00 – 18:00', en: '10:00 – 18:00' } },
    sis: { d: '2026-12-25', h: { es: '08:00 – 18:00', en: '08:00 – 18:00' } },
    obs: { es: 'Mismo día, dos horas más: arranca a las 08:00. El alcance también cambió — entra Chaxa, que tiene su propia entrada, y salen Tuyajto y las protoaldeas. El Valle del Arcoíris ya no aparece: eso quedó resuelto.',
           en: 'Same day, two hours longer: it starts at 08:00. The scope changed too — Chaxa comes in, with its own entrance fee, and Tuyajto and the proto-villages drop out. Rainbow Valley no longer appears: that one is settled.' } },

  { id: 'arqueologico', e: 'hora',
    cot: { d: '2026-12-26', h: { es: '08:00 – 10:00 · 2 h', en: '08:00 – 10:00 · 2 h' } },
    sis: { d: '2026-12-26', h: { es: '08:30 – 10:00 · 1 h 30', en: '08:30 – 10:00 · 1 h 30' } },
    obs: { es: 'Mismo día, media hora más tarde y media hora más corto. El guiado está cotizado a dos horas: en hora y media hay que recortar el recorrido. Y el traslado a Calama sale 24 minutos después de que termina. Ver el choque 3.',
           en: 'Same day, half an hour later and half an hour shorter. The guided visit is quoted at two hours: in ninety minutes the route has to be cut back. And the transfer to Calama leaves 24 minutes after it ends. See conflict 3.' } },

  { id: 'transfer-out', e: 'fija',
    cot: { d: '2026-12-26', h: { es: 'Horario a confirmar', en: 'Time to be confirmed' } },
    sis: { d: '2026-12-26', h: { es: '10:24 – 11:44', en: '10:24 – 11:44' } },
    obs: { es: 'Mismo día y ahora con hora. Llega a Calama 11:44 para el vuelo de las 13:44: dos horas de margen, bien para un doméstico. Lo apretado no es el vuelo, es salir de Quitor.',
           en: 'Same day and now with a time. It reaches Calama at 11:44 for the 13:44 flight: two hours of margin, fine for a domestic leg. The tight part is not the flight, it is getting away from Quitor.' } },
];

// Lo que el sistema tiene y la cotización no cubre.
const EXTRA = [
  { d: '2026-12-22', t: { es: 'El tramo completo desde Puerto Natales: traslado a PNT 06:35, vuelo PNT → Santiago 09:05, conexión y almuerzo en Santiago 13:45, vuelo Santiago → Calama 15:33, y el check-in en San Pedro 19:35. De todo eso, la cotización sólo cubre el traslado Calama → San Pedro.',
                          en: 'The whole leg down from Puerto Natales: transfer to PNT 06:35, PNT → Santiago 09:05, connection and lunch in Santiago 13:45, Santiago → Calama 15:33, and check-in in San Pedro 19:35. Of all that, the quotation only covers the Calama → San Pedro transfer.' } },
  { d: '2026-12-23', t: { es: 'Aclimatación · mañana tranquila en San Pedro, 09:00 – 13:00. Hay que definir si es servicio guiado o simplemente tiempo libre: si es guiado, hay que cotizarlo.',
                          en: 'Acclimatization · calm morning in San Pedro, 09:00 – 13:00. We need to settle whether it is a guided service or simply free time: if it is guided, it needs quoting.' } },
  { d: '2026-12-24', t: { es: 'Tarde libre 15:30 – 17:30 y cena de Nochebuena 20:00 – 22:00.',
                          en: 'Free afternoon 15:30 – 17:30 and Christmas Eve dinner 20:00 – 22:00.' } },
  { d: '2026-12-25', t: { es: 'Cena 20:00 – 21:30.', en: 'Dinner 20:00 – 21:30.' } },
  { d: '2026-12-26', t: { es: 'Vuelo Calama → Santiago 13:44, check-in internacional 20:00 y vuelo Santiago → Dallas AA940 23:45, ya comprado. Fuera del alcance de la cotización.',
                          en: 'Calama → Santiago flight 13:44, international check-in 20:00 and the Santiago → Dallas AA940 at 23:45, already purchased. Outside the scope of the quotation.' } },
];

// --- Choques de horario dentro del propio sistema ----------------------------

const CHOQUES = [
  { grave: true,
    tit: { es: 'El 24: Puritama termina y el Valle de la Muerte empieza a la misma hora',
           en: 'The 24th: Puritama ends and Valle de la Muerte begins at the same time' },
    cuerpo: {
      es: ['Puritama va de <b>09:30 a 13:30</b> y el Valle de la Muerte de <b>13:30 a 15:30</b>. Las termas están a unos 30 km de San Pedro, cerca de una hora de camino: no se puede terminar allá a las 13:30 y estar en el valle a las 13:30.',
           'Aparte del choque, serían dos horas de sandboard o cuadriciclos en la franja de más calor de un día de diciembre, justo después de cuatro horas de termas.'],
      en: ['Puritama runs <b>09:30 to 13:30</b> and Valle de la Muerte <b>13:30 to 15:30</b>. The hot springs are some 30 km from San Pedro, close to an hour on the road: you cannot finish there at 13:30 and be in the valley at 13:30.',
           'Beyond the clash, it would be two hours of sandboarding or quad bikes in the hottest part of a December day, straight after four hours in hot springs.'],
    },
    fix: { es: 'Correr el Valle de la Muerte a la franja de la tarde libre, <b>15:30 a 17:30</b>. Resuelve el choque, deja margen real después de Puritama y saca los quads de la peor hora.',
           en: 'Move Valle de la Muerte into the free-afternoon slot, <b>15:30 to 17:30</b>. It resolves the clash, leaves real margin after Puritama and takes the quads out of the worst hour.' } },

  { grave: true,
    tit: { es: 'Cejar y el Valle de la Muerte no caben los dos el 24',
           en: 'Cejar and Valle de la Muerte do not both fit on the 24th' },
    cuerpo: {
      es: ['Cejar está cotizado de <b>15:30 a 19:30</b>, cuatro horas. Si el Valle de la Muerte se corre a esa franja para resolver el choque anterior, Cejar no entra. Y si Cejar se queda donde estaba cotizado, el Valle de la Muerte tiene que volver a las 13:30 y el choque vuelve.',
           'La tarde del <b>miércoles 23</b> es el único hueco donde Cejar entra completo: la aclimatación termina 13:00 y el Valle de la Luna arranca 17:30. Son cuatro horas y media, justas — habría que salir apenas termine la aclimatación. A favor: Cejar está a nivel del salar, así que no rompe la aclimatación del día.'],
      en: ['Cejar is quoted <b>15:30 to 19:30</b>, four hours. If Valle de la Muerte moves into that slot to fix the previous clash, Cejar does not fit. And if Cejar stays where it was quoted, Valle de la Muerte has to go back to 13:30 and the clash returns.',
           'The afternoon of <b>Wednesday 23</b> is the only gap where Cejar fits whole: the acclimatization ends at 13:00 and Valle de la Luna starts at 17:30. That is four and a half hours, just enough — you would have to leave as soon as the acclimatization ends. In its favour: Cejar sits at salt-flat level, so it does not undo the day’s acclimatization.'],
    },
    fix: { es: 'Decidir primero si Cejar entra. Si entra, la tarde del 23; si no, el 24 se ordena solo con el Valle de la Muerte a las 15:30. Antes de recotizarlo conviene mirar los <b>11 servicios quitados</b> que el sistema marca como restaurables: puede que Cejar esté ahí.',
           en: 'Decide first whether Cejar is in. If it is, the afternoon of the 23rd; if not, the 24th sorts itself out with Valle de la Muerte at 15:30. Before requoting it, check the <b>11 removed services</b> the system flags as restorable: Cejar may be among them.' } },

  { grave: false,
    tit: { es: 'El 24: quince minutos entre la cena de Nochebuena y la astronomía',
           en: 'The 24th: fifteen minutes between the Christmas Eve dinner and the astronomy' },
    cuerpo: {
      es: ['La cena va de <b>20:00 a 22:00</b> y la astronomía privada arranca <b>22:15</b>. Quince minutos para levantarse de la mesa y salir. Se puede, pero el operador tiene que saberlo de antemano.'],
      en: ['Dinner runs <b>20:00 to 22:00</b> and the private astronomy starts at <b>22:15</b>. Fifteen minutes to get up from the table and go. It is doable, but the operator has to know in advance.'],
    },
    fix: { es: 'Fijar el punto de recogida en el restaurante y no en el hotel, o cerrar la cena a las 21:30. Y confirmar que haya operación astronómica la noche del 24.',
           en: 'Set the pickup at the restaurant rather than the hotel, or close dinner at 21:30. And confirm the astronomy operates on the night of the 24th.' } },

  { grave: false,
    tit: { es: 'El 26: veinticuatro minutos entre Quitor y el traslado',
           en: 'The 26th: twenty-four minutes between Quitor and the transfer' },
    cuerpo: {
      es: ['Quitor termina <b>10:00</b> y el traslado a Calama sale <b>10:24</b>. En esos 24 minutos hay que volver del pukará, pasar por el hotel, hacer el check-out y cargar el equipaje.'],
      en: ['Quitor ends at <b>10:00</b> and the transfer to Calama leaves at <b>10:24</b>. In those 24 minutes you have to come back from the pukará, stop at the hotel, check out and load the luggage.'],
    },
    fix: { es: 'Hacer el check-out <b>antes</b> de salir a Quitor y llevar el equipaje en el vehículo. Así el traslado sale directo desde el pukará y los 24 minutos sobran.',
           en: 'Check out <b>before</b> heading to Quitor and carry the luggage in the vehicle. The transfer then leaves straight from the pukará and 24 minutes is more than enough.' } },

  { grave: false,
    tit: { es: 'El 23 tiene dos bloques y el 24 tiene cinco',
           en: 'The 23rd has two blocks and the 24th has five' },
    cuerpo: {
      es: ['El 23 queda con la aclimatación de la mañana y el Valle de la Luna al atardecer. El 24, que es Nochebuena, acumula Puritama, el Valle de la Muerte, la tarde libre, la cena y la astronomía.',
           'La progresión de altura justifica que Puritama esté el 24, así que el desbalance no se arregla devolviéndolo al 23. Se arregla con lo que se mueva a la tarde del 23.'],
      en: ['The 23rd is left with the morning acclimatization and Valle de la Luna at sunset. The 24th, which is Christmas Eve, piles up Puritama, Valle de la Muerte, the free afternoon, dinner and the astronomy.',
           'The altitude progression justifies Puritama sitting on the 24th, so the imbalance is not fixed by moving it back. It is fixed by whatever moves into the afternoon of the 23rd.'],
    },
    fix: { es: 'La tarde del 23 es el espacio libre que queda en todo el programa. Cejar es el candidato natural.',
           en: 'The afternoon of the 23rd is the only free space left in the whole programme. Cejar is the natural candidate.' } },
];

const PREGUNTAS = [
  { q: { es: '¿Cejar entra o queda fuera?', en: 'Is Cejar in or out?' },
    d: { es: 'Es el único servicio cotizado que no está en el sistema, y hay un mensaje del cliente preguntando por una flotada. Si entra, la tarde del 23.', en: 'It is the only quoted service missing from the system, and there is a client message asking about a float. If it is in, the afternoon of the 23rd.' } },
  { q: { es: '¿Sandboard o cuadriciclos en el Valle de la Muerte?', en: 'Sandboarding or quad bikes in the Valle de la Muerte?' },
    d: { es: 'El sistema lo deja sin decidir. El sandboard está cotizado; los cuadriciclos no, y no los operamos nosotros.', en: 'The system leaves it undecided. Sandboarding is quoted; quad bikes are not, and we do not operate them.' } },
  { q: { es: '¿Movemos el Valle de la Muerte a las 15:30?', en: 'Do we move Valle de la Muerte to 15:30?' },
    d: { es: 'Es lo que destraba el 24. Depende de qué se decida con Cejar.', en: 'It is what unblocks the 24th. It depends on what is decided about Cejar.' } },
  { q: { es: '¿La astronomía privada va confirmada?', en: 'Is the private astronomy confirmed?' },
    d: { es: 'Cambia el valor respecto del compartido que está cotizado, y hay que confirmar que opere la noche del 24.', en: 'It changes the price against the shared tour that is quoted, and we need to confirm it runs on the night of the 24th.' } },
  { q: { es: '¿El Valle de la Luna del 23 a las 17:30 es el circuito cotizado?', en: 'Is the 17:30 Valle de la Luna on the 23rd the quoted route?' },
    d: { es: 'Lo cotizado es el off circuit por el Vallecito, que se hace caminando. El estándar entra igual en la ventana, pero es otro producto.', en: 'What is quoted is the off-circuit through El Vallecito, which is walked. The standard one also fits the window, but it is a different product.' } },
  { q: { es: '¿Quitor en hora y media o volvemos a las dos horas cotizadas?', en: 'Quitor in ninety minutes, or back to the two hours quoted?' },
    d: { es: 'En 1 h 30 hay que recortar el recorrido. Y conviene hacer el check-out antes de salir.', en: 'In 1 h 30 the route has to be cut back. And it is worth checking out before leaving.' } },
  { q: { es: '¿Chaxa entra al full day del 25?', en: 'Does Chaxa go into the full day on the 25th?' },
    d: { es: 'No estaba en lo cotizado y suma su propia entrada. Salen Tuyajto y las protoaldeas.', en: 'It was not in the quotation and adds its own entrance fee. Tuyajto and the proto-villages drop out.' } },
  { q: { es: '¿La aclimatación del 23 es servicio guiado o tiempo libre?', en: 'Is the acclimatization on the 23rd a guided service or free time?' },
    d: { es: 'Si es guiado hay que cotizarlo; si es tiempo libre, no cuesta nada.', en: 'If it is guided it needs quoting; if it is free time, it costs nothing.' } },
  { q: { es: 'Disponibilidad y recargo del 24 y 25.', en: 'Availability and holiday supplement for the 24th and 25th.' },
    d: { es: 'Nochebuena y Navidad, con el full day más caro del programa el 25. Es lo primero que hay que cerrar.', en: 'Christmas Eve and Christmas Day, with the programme’s most expensive full day on the 25th. This is the first thing to close.' } },
  { q: { es: '¿Son 4 pasajeros?', en: 'Is it 4 travellers?' },
    d: { es: 'El itinerario dice 4 en todas las líneas, pero la cena del 25 está rotulada «Segar & Shreya». La cotización va sobre 4; conviene confirmarlo antes de pedir las tarifas de grupo.', en: 'The schedule says 4 on every line, but the dinner on the 25th is labelled “Segar & Shreya”. The quotation is built on 4; worth confirming before requesting group rates.' } },
];

// --- Desglose por tipo de diferencia -----------------------------------------

const sub = (s) => s.valor + s.entradas;
const porId = Object.fromEntries(programa.map((s) => [s.id, s]));
const CAT = ['fija', 'hora', 'dia', 'falta'];
const monto = Object.fromEntries(CAT.map((c) => [c, 0]));
const cuenta = Object.fromEntries(CAT.map((c) => [c, 0]));
for (const c of COMPARACION) {
  monto[c.e] += sub(porId[c.id]);
  cuenta[c.e] += 1;
}
const totalPack = programa.reduce((a, s) => a + sub(s), 0);

// --- Textos ------------------------------------------------------------------

const T = {
  es: {
    titulo: `Tourevo · Días y horarios — ${cliente.nombre}`,
    eyebrow: 'Revisión de días y horarios',
    h1: 'La cotización contra el itinerario en sistema',
    sub: 'Los nueve servicios cotizados en COT-2026-0160, uno por uno, contra lo que hoy está cargado en el sistema Tourevo y ya visto con Segar. Qué coincide, qué corre de día, qué cambia de hora y qué no está.',
    chips: [['Ref.', doc.numero], ['Programa', '22 – 26 dic 2026'], ['Pasajeros', String(doc.pax)]],
    lResumen: 'Los nueve servicios, por tipo de diferencia',
    ledeResumen: 'Ocho de los nueve están en el sistema. Ninguno coincide con la cotización en día y hora a la vez: dos corren de día, cuatro cambian de horario, dos recién ahora tienen hora y uno no aparece.',
    catFija: 'Se fija la hora', catHora: 'Cambia el horario', catDia: 'Corre de día', catFalta: 'No está',
    svc: (n) => `${n} de ${programa.length} servicios`,
    lYaOk: 'Lo que ya quedó resuelto',
    ledeYaOk: 'Dos cosas que marqué la vez pasada sobre el export y que en el sistema ya están arregladas: la <b>aclimatación</b> quedó el miércoles 23 y no el 22, así que ya no choca con la llegada de las 19:15; y el <b>Valle del Arcoíris</b> salió del día de Piedras Rojas, que era la combinación que no se podía operar. Puritama tampoco faltaba: está el 24. Nada de eso hay que volver a tocarlo.',
    lCmp: 'Servicio por servicio',
    thSvc: 'Servicio',
    thCot: 'Cotizado',
    thSis: 'Sistema Tourevo',
    thDif: 'Diferencia',
    lChoques: 'Choques de horario dentro del propio sistema',
    ledeChoques: 'Cinco puntos que no dependen de la cotización sino del itinerario tal como está armado hoy. Los dos primeros hay que resolverlos para que el 24 sea operable.',
    queHacemos: 'Qué proponemos',
    lExtra: 'Lo que el sistema tiene y la cotización no cubre',
    lQs: 'Lo que necesito confirmar',
    nota: 'El sistema marca además <b>cuatro mensajes sin responder</b> — uno en el Valle de la Luna, uno en la cena de Nochebuena y dos en el Valle de la Muerte — y <b>11 servicios quitados</b> que se pueden restaurar.',
    cierre: 'Con esas respuestas cierro el programa y mando la cotización alineada con el sistema.',
    legend: `Días y horarios sobre la cotización ${doc.numero} · Tourevo`,
  },

  en: {
    titulo: `Tourevo · Days and times — ${cliente.nombre}`,
    eyebrow: 'Day and time review',
    h1: 'The quotation against the itinerary in the system',
    sub: 'The nine services quoted in COT-2026-0160, one by one, against what is loaded today in the Tourevo system and already reviewed with Segar. What matches, what moves day, what changes time and what is missing.',
    chips: [['Ref.', doc.numero], ['Programme', '22 – 26 Dec 2026'], ['Travellers', String(doc.pax)]],
    lResumen: 'The nine services, by type of difference',
    ledeResumen: 'Eight of the nine are in the system. None matches the quotation on both day and time: two move day, four change time, two only now have a time, and one is missing.',
    catFija: 'Time now set', catHora: 'Time changes', catDia: 'Moves day', catFalta: 'Missing',
    svc: (n) => `${n} of ${programa.length} services`,
    lYaOk: 'What is already settled',
    ledeYaOk: 'Two things I flagged last time against the export are already fixed in the system: the <b>acclimatization</b> now sits on Wednesday 23 rather than the 22nd, so it no longer clashes with the 19:15 arrival; and <b>Rainbow Valley</b> has come out of the Piedras Rojas day, which was the combination that could not be operated. Puritama was not missing either: it is on the 24th. None of that needs touching again.',
    lCmp: 'Service by service',
    thSvc: 'Service',
    thCot: 'Quoted',
    thSis: 'Tourevo system',
    thDif: 'Difference',
    lChoques: 'Timing clashes inside the system itself',
    ledeChoques: 'Five points that do not depend on the quotation but on the itinerary as it stands today. The first two have to be resolved for the 24th to be operable.',
    queHacemos: 'What we propose',
    lExtra: 'What the system has that the quotation does not cover',
    lQs: 'What I need confirmed',
    nota: 'The system also flags <b>four unanswered messages</b> — one on Valle de la Luna, one on the Christmas Eve dinner and two on Valle de la Muerte — and <b>11 removed services</b> that can be restored.',
    cierre: 'With those answers I will close the programme and send the quotation aligned to the system.',
    legend: `Days and times against quotation ${doc.numero} · Tourevo`,
  },
};

// --- Render ------------------------------------------------------------------

const dLargo = (iso, l) => mayus(fecha(iso, l, { weekday: 'long', day: 'numeric', month: 'long' }));
const dCorto = (iso, l) => mayus(fecha(iso, l, { weekday: 'short', day: 'numeric', month: 'short' }));

const choque = (h, i, l) => `
        <article class="find${h.grave ? ' stop' : ''}">
          <div class="find-top">
            <span class="find-n">${i + 1}</span>
            <h4>${esc(h.tit[l])}</h4>
          </div>
          ${h.cuerpo[l].map((p) => `<p>${p}</p>`).join('\n          ')}
          <p class="fix"><b>${T[l].queHacemos}:</b> ${h.fix[l]}</p>
        </article>`;

function comparativo(t, l) {
  const celda = (x, clase) => x
    ? `<td class="${clase}">${esc(dCorto(x.d, l))}<span class="hr">${esc(x.h[l])}</span></td>`
    : `<td class="${clase}"><span class="nada">—</span></td>`;

  const filas = COMPARACION.map((c) => {
    const e = ESTADOS[c.e];
    return `
          <tbody class="cg">
            <tr>
              <td class="svcname"><b>${esc(porId[c.id].titulo[l])}</b></td>
              ${celda(c.cot, 'was')}
              ${celda(c.sis, 'now')}
              <td><span class="badge ${e.c}">${e.t[l]}</span></td>
            </tr>
            <tr><td colspan="4" class="obsrow"><span class="obs">${c.obs[l]}</span></td></tr>
          </tbody>`;
  }).join('');

  return `
      <div class="tbl-wrap">
        <table class="cmp">
          <thead>
            <tr><th>${t.thSvc}</th><th>${t.thCot}</th><th>${t.thSis}</th><th>${t.thDif}</th></tr>
          </thead>${filas}
        </table>
      </div>`;
}

function pagina(l) {
  const t = T[l];
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
        <p class="label">${t.lResumen}</p>
        <p class="lede">${t.ledeResumen}</p>
        <div class="impact cuatro">
          <div class="imp keep"><div class="k">${t.catFija}</div><div class="v">${plata(monto.fija, l)}</div><div class="p">${t.svc(cuenta.fija)}</div></div>
          <div class="imp req"><div class="k">${t.catHora}</div><div class="v">${plata(monto.hora, l)}</div><div class="p">${t.svc(cuenta.hora)}</div></div>
          <div class="imp move"><div class="k">${t.catDia}</div><div class="v">${plata(monto.dia, l)}</div><div class="p">${t.svc(cuenta.dia)}</div></div>
          <div class="imp gone"><div class="k">${t.catFalta}</div><div class="v">${plata(monto.falta, l)}</div><div class="p">${t.svc(cuenta.falta)}</div></div>
        </div>
      </section>

      <section>
        <p class="label">${t.lYaOk}</p>
        <p class="lede">${t.ledeYaOk}</p>
      </section>

      <section>
        <p class="label">${t.lCmp}</p>
${comparativo(t, l)}
      </section>

      <section>
        <p class="label">${t.lChoques}</p>
        <p class="lede">${t.ledeChoques}</p>
        <div class="finds">
${CHOQUES.map((h, i) => choque(h, i, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lExtra}</p>
        <div class="card">
          <ul>${EXTRA.map((x) => `\n            <li><b>${esc(dCorto(x.d, l))}.</b> ${esc(x.t[l])}</li>`).join('')}
          </ul>
        </div>
        <p class="lede" style="margin-top:12px">${t.nota}</p>
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
  { l: 'es', html: 'diferencias.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Dias-y-horarios-ES.pdf` },
  { l: 'en', html: 'diferencias-en.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Dias-y-horarios-EN.pdf` },
];

for (const s of SALIDAS) {
  writeFileSync(join(AQUI, s.html), pagina(s.l));
  console.log(`✓ ${s.html}`);
}
console.log(
  CAT.map((c) => `  ${c.padEnd(6)} ${String(cuenta[c])} serv · ${plata(monto[c], 'es')}`).join('\n') +
  `\n  control      ${plata(CAT.reduce((a, c) => a + monto[c], 0), 'es')} = pack ${plata(totalPack, 'es')}\n`);

if (!soloHtml) await aPdf(SALIDAS, { aqui: AQUI, pie: `Tourevo · ${doc.numero} · ${cliente.nombre} · días y horarios` });
