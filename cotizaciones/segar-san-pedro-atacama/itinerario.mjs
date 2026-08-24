// Itinerario propuesto, fuente única. Lo usan build-itinerario.mjs (PDF, ES/EN)
// y build-excel.py (planilla para el operador), así que los horarios no pueden
// quedar distintos entre un documento y otro.
//
// tipo:  tourevo → servicio nuestro · cliente → vuelos y comidas · libre → sin programar
// e:     igual → como lo vio el cliente · movido → cambia de hora o día · nuevo → no estaba
// conf:  qué necesitamos que el operador confirme de esa línea

export const DIAS = [
  { f: '2026-12-22', n: { es: 'Llegada a San Pedro', en: 'Arrival in San Pedro' }, blks: [
    { h: '15:33', f2: '17:41', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Santiago → Calama (CJC) · LATAM', en: 'Flight Santiago → Calama (CJC) · LATAM' } },
    { h: '18:15', f2: '19:15', tipo: 'tourevo', e: 'igual',
      t: { es: 'Traslado Calama → San Pedro de Atacama', en: 'Transfer Calama → San Pedro de Atacama' },
      n: { es: 'Servicio privado. Recogida en la llegada del vuelo de las 17:41.', en: 'Private service. Pickup at the arrival of the 17:41 flight.' },
      conf: { es: 'Recogida en el aeropuerto de Calama a la llegada del vuelo LATAM de las 17:41.', en: 'Pickup at Calama airport on arrival of the 17:41 LATAM flight.' } },
    { h: '19:15', f2: '20:00', tipo: 'libre', e: 'igual',
      t: { es: 'Llegada y check-in en el hotel · resto de la noche libre para aclimatación', en: 'Arrival and check-in at the hotel · rest of the evening free to acclimatize' } },
  ]},

  { f: '2026-12-23', n: { es: 'Salar y atardecer', en: 'Salt flat and sunset' }, blks: [
    { h: '10:15', f2: '14:15', tipo: 'tourevo', e: 'movido',
      t: { es: 'Complejo Laguna Cejar · flotación en Laguna Piedra + Ojos de Tebenquiche + Laguna de Tebenquiche',
           en: 'Laguna Cejar complex · floating at Laguna Piedra + Ojos de Tebenquiche + Tebenquiche Lagoon' },
      n: { es: 'Salida del hotel <b>10:15</b> para estar en el complejo a las <b>10:50</b>. Reemplaza el bloque de aclimatación: es un día de agua y salar, a nivel del salar (2.300 m), justo lo que corresponde antes de subir. El complejo <b>cierra los martes</b>; el 23 es miércoles.',
           en: 'Depart the hotel at <b>10:15</b> to be at the complex by <b>10:50</b>. It replaces the acclimatization block: a day of water and salt flat at 2,300 m, exactly right before climbing. The complex <b>closes on Tuesdays</b>; the 23rd is a Wednesday.' },
      conf: { es: 'Salida 10:15 del hotel para llegar 10:50 al complejo: ¿es correcto el tiempo de camino? ¿El circuito completo (Piedra, Ojos y Tebenquiche) cierra a las 14:15? Y confirmar que el complejo abre el miércoles 23.',
              en: 'Departure 10:15 from the hotel to arrive 10:50 at the complex: is that the right drive time? Does the full circuit (Piedra, Ojos and Tebenquiche) close at 14:15? And please confirm the complex opens on Wednesday the 23rd.' } },
    { h: '14:15', f2: '17:15', tipo: 'libre', e: 'nuevo',
      t: { es: 'Almuerzo y tarde libre en el hotel', en: 'Lunch and free afternoon at the hotel' },
      n: { es: 'Tres horas de margen entre Cejar y el Valle de la Luna. El día queda con dos salidas y no tres.', en: 'Three hours of margin between Cejar and Valle de la Luna. The day carries two outings, not three.' } },
    { h: '17:30', f2: '20:45', tipo: 'tourevo', e: 'igual',
      t: { es: 'Valle de la Luna al atardecer', en: 'Valle de la Luna at sunset' },
      conf: { es: '¿Alcanza a las 17:30 para el circuito off circuit por el Vallecito, que se hace caminando? Es el que está cotizado.',
              en: 'Is a 17:30 start enough for the off-circuit route through El Vallecito, which is walked? That is the one quoted.' } },
  ]},

  { f: '2026-12-24', n: { es: 'Puritama, Quitor y Nochebuena', en: 'Puritama, Quitor and Christmas Eve' }, blks: [
    { h: '09:30', f2: '13:30', tipo: 'tourevo', e: 'igual',
      t: { es: 'Termas de Puritama · 3.500 m', en: 'Puritama Hot Springs · 3,500 m' },
      n: { es: 'Primera subida del viaje, después del día a nivel del salar. Miscanti, sobre 4.000 m, queda para el 25.', en: 'The first climb of the trip, after a day at salt-flat level. Miscanti, above 4,000 m, comes on the 25th.' } },
    { h: '13:30', f2: '14:30', tipo: 'libre', e: 'nuevo',
      t: { es: 'Almuerzo y descanso', en: 'Lunch and rest' } },
    { h: '14:30', f2: '18:30', tipo: 'tourevo', e: 'movido',
      t: { es: 'Pukará de Quitor + Valle de la Muerte con sandboard', en: 'Pukará de Quitor + Valle de la Muerte with sandboarding' },
      n: { es: 'Medio día combinado, privado: <b>Quitor 14:45 – 16:15</b>, veinte minutos de traslado, <b>Valle de la Muerte y sandboard 16:35 – 18:00</b>, regreso 18:30. Están a quince minutos uno del otro y es como el cliente ya los quería, juntos. El sandboard cae al final, con la luz y la temperatura de la tarde.',
           en: 'A combined private half day: <b>Quitor 14:45 – 16:15</b>, twenty minutes on the road, <b>Valle de la Muerte and sandboarding 16:35 – 18:00</b>, back by 18:30. They sit fifteen minutes apart and this is how the client already wanted them, together. Sandboarding lands at the end, in afternoon light and temperature.' },
      conf: { es: '¿Lo operan como medio día combinado en ese orden y horario? Quitor está cotizado guiado a 2 h y acá va en 1 h 30. El sandboard está cotizado; los cuadriciclos no y no los operamos nosotros.',
              en: 'Do you run it as a combined half day in that order and time frame? Quitor is quoted guided at 2 h and here it runs in 1 h 30. Sandboarding is quoted; quad bikes are not and we do not operate them.' } },
    { h: '18:30', f2: '20:00', tipo: 'libre', e: 'igual',
      t: { es: 'Descanso antes de la cena', en: 'Rest before dinner' } },
    { h: '20:00', f2: '22:00', tipo: 'cliente', e: 'igual',
      t: { es: 'Cena de Nochebuena', en: 'Christmas Eve dinner' } },
    { h: '22:15', f2: '23:59', tipo: 'tourevo', e: 'igual',
      t: { es: 'Astronomía privada', en: 'Private astronomy' },
      n: { es: 'Recogida <b>en el restaurante a las 22:10</b>, no en el hotel: la cena termina 22:00.', en: 'Pickup <b>at the restaurant at 22:10</b>, not the hotel: dinner ends at 22:00.' },
      conf: { es: 'La luna esa noche está al 99% iluminada y ninguna otra noche del viaje es mejor. ¿Cómo lo manejan en un privado, orientándolo a luna y planetas? Y confirmar la recogida en el restaurante a las 22:10. Lo cotizado es el tour compartido: el privado hay que cotizarlo.',
              en: 'The moon that night is 99% illuminated and no other night of the trip is better. How do you handle that on a private tour, aimed at the moon and planets? And please confirm the 22:10 pickup at the restaurant. What is quoted is the shared tour: the private one needs quoting.' } },
  ]},

  { f: '2026-12-25', n: { es: 'Altiplano', en: 'Altiplano' }, blks: [
    { h: '08:00', f2: '18:00', tipo: 'tourevo', e: 'igual',
      t: { es: 'Altiplano · Chaxa, Toconao, Miscanti y Piedras Rojas', en: 'Altiplano · Chaxa, Toconao, Miscanti and Piedras Rojas' },
      n: { es: 'Almuerzo en hábitat natural.', en: 'Lunch in a natural setting.' },
      conf: { es: 'Confirmar el alcance: entra Chaxa, con su propia entrada, y salen Tuyajto y las protoaldeas que estaban cotizadas. ¿Cómo queda el valor y qué entradas se compran por adelantado?',
              en: 'Please confirm the scope: Chaxa comes in, with its own entrance fee, and Tuyajto and the proto-villages drop out from the quote. How does the price land and which tickets are bought in advance?' } },
    { h: '20:00', f2: '21:30', tipo: 'cliente', e: 'igual',
      t: { es: 'Cena', en: 'Dinner' } },
  ]},

  { f: '2026-12-26', n: { es: 'Día libre y salida', en: 'Free day and departure' }, blks: [
    { h: '08:00', f2: '10:00', tipo: 'libre', e: 'nuevo',
      t: { es: 'Mañana libre en San Pedro · desayuno y check-out', en: 'Free morning in San Pedro · breakfast and check-out' },
      n: { es: 'El último día queda sin excursiones. Quitor se movió al 24.', en: 'The last day carries no excursions. Quitor moved to the 24th.' } },
    { h: '10:24', f2: '11:44', tipo: 'tourevo', e: 'igual',
      t: { es: 'Traslado San Pedro → Calama', en: 'Transfer San Pedro → Calama' },
      n: { es: 'Llega 11:44 para el vuelo de las 13:44: dos horas de margen.', en: 'Arrives 11:44 for the 13:44 flight: two hours of margin.' } },
    { h: '13:44', f2: '15:52', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Calama → Santiago · LATAM', en: 'Flight Calama → Santiago · LATAM' } },
    { h: '20:00', f2: '23:45', tipo: 'cliente', e: 'igual',
      t: { es: 'Check-in internacional en Santiago', en: 'International check-in in Santiago' } },
    { h: '23:45', f2: '23:59', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Santiago → Dallas · AA940', en: 'Flight Santiago → Dallas · AA940' } },
  ]},
];

export const CAMBIOS = [
  { tit: { es: 'Cejar pasa a la mañana del 23 y es la aclimatación', en: 'Cejar moves to the morning of the 23rd and becomes the acclimatization' },
    cuerpo: {
      es: ['Salida del hotel <b>10:15</b> para estar en el complejo a las <b>10:50</b>, y vuelta 14:15. El bloque que decía «aclimatación» pasa a ser esto: flotar en Laguna Piedra es exactamente la forma de pasar el primer día completo sin esforzarse.',
           'Además ordena la altura: el 23 a nivel del salar (2.300 m), el 24 en Puritama (3.500 m) y el 25 en Miscanti (sobre 4.000 m). El complejo <b>cierra los martes</b>; el único martes del viaje es el 22, que es día de llegada y no tiene excursiones.'],
      en: ['Depart the hotel at <b>10:15</b> to be at the complex by <b>10:50</b>, back at 14:15. The block that said “acclimatization” becomes this: floating at Laguna Piedra is exactly how you spend a first full day without exerting yourself.',
           'It also orders the altitude: the 23rd at salt-flat level (2,300 m), the 24th at Puritama (3,500 m) and the 25th at Miscanti (above 4,000 m). The complex <b>closes on Tuesdays</b>; the only Tuesday of the trip is the 22nd, the arrival day, which carries no excursions.'],
    } },
  { tit: { es: 'Quitor y el Valle de la Muerte van juntos la tarde del 24', en: 'Quitor and Valle de la Muerte go together on the afternoon of the 24th' },
    cuerpo: {
      es: ['Un solo medio día privado de <b>14:30 a 18:30</b>: Quitor primero, veinte minutos de traslado, y el sandboard en el Valle de la Muerte al final. Están a quince minutos uno del otro, así que es un vehículo y un guía en vez de dos salidas.',
           'Es también lo que el cliente ya había decidido —quedarse con Quitor y el Valle de la Muerte juntos— y es lo que libera el 26.'],
      en: ['A single private half day from <b>14:30 to 18:30</b>: Quitor first, twenty minutes on the road, and sandboarding at Valle de la Muerte at the end. They are fifteen minutes apart, so it is one vehicle and one guide instead of two outings.',
           'It is also what the client had already decided — keeping Quitor and Valle de la Muerte together — and it is what frees up the 26th.'],
    } },
  { tit: { es: 'El 26 queda libre', en: 'The 26th is left free' },
    cuerpo: {
      es: ['Sin excursiones. Mañana libre, check-out, y a las 10:24 el traslado a Calama para el vuelo de las 13:44.'],
      en: ['No excursions. Free morning, check-out, and at 10:24 the transfer to Calama for the 13:44 flight.'],
    } },
  { tit: { es: 'El 23 por la tarde y el 22 por la noche quedan libres', en: 'The afternoon of the 23rd and the evening of the 22nd are left free' },
    cuerpo: {
      es: ['El 22 se llega al hotel 19:15 y no se programa nada más. El 23 hay tres horas entre Cejar y el Valle de la Luna. Ningún día del programa lleva más de dos salidas.'],
      en: ['On the 22nd you reach the hotel at 19:15 and nothing else is scheduled. On the 23rd there are three hours between Cejar and Valle de la Luna. No day in the programme carries more than two outings.'],
    } },
];

// Preguntas que no cuelgan de una línea del itinerario. Las que sí, viven en el
// campo `conf` de cada bloque, para que no haya dos listas que mantener.
export const PREGUNTAS_GENERALES = [
  { q: { es: 'Disponibilidad y recargo del 24 y 25 de diciembre', en: 'Availability and holiday supplement for 24 and 25 December' },
    d: { es: 'Nochebuena y Navidad, con el full day del altiplano el 25. Es lo primero que hay que cerrar: si el 25 no hay operación, se rearma el programa.',
         en: 'Christmas Eve and Christmas Day, with the altiplano full day on the 25th. First thing to close: if the 25th cannot operate, the programme gets rebuilt.' } },
  { q: { es: 'Tarifas para 4 pasajeros', en: 'Rates for 4 travellers' },
    d: { es: 'La cotización original iba sobre 2 en las alternativas de trekking, con tarifa mínima de grupo. Necesitamos el valor para 4.',
         en: 'The original quote was built on 2 for the trekking alternatives, at a minimum group rate. We need the figure for 4.' } },
  { q: { es: 'Vehículo del full day', en: 'Vehicle for the full day' },
    d: { es: 'Con 4 pasajeros más guía-conductor y equipaje en una salida de diez horas al altiplano, ¿qué vehículo asignan?',
         en: 'With 4 travellers plus guide-driver and luggage on a ten-hour run up to the altiplano, which vehicle would you assign?' } },
  { q: { es: 'Valle del Arcoíris: hoy no cabe', en: 'Rainbow Valley: it does not fit today' },
    d: { es: 'El cliente lo había pedido. Con el 26 libre y el resto de los días completos, no queda espacio sin sacar otra cosa. ¿Lo operan como media jornada propia y en qué horario? Así el cliente decide con el dato a la vista.',
         en: 'The client had asked for it. With the 26th free and every other day full, there is no space without dropping something else. Do you run it as a half day of its own, and at what time? That way the client decides with the facts in hand.' } },
];
