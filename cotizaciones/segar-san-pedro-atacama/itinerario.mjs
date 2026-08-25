// Itinerario propuesto, fuente única del PDF (ES/EN) y de la planilla.
//
// Esta versión incorpora lo conversado el 24 de agosto con Francisco Renard
// (operador en San Pedro) y con Segar. Las restricciones que mandan:
//   · Cejar sólo se puede operar PM — las agencias no entran en la mañana.
//   · El Vallecito necesita salir 17:00 como máximo para hacerse bien.
//   · Los traslados al aeropuerto se calculan en 1 h 45, no en 1 h 20.
//   · Quitor y el sandboard son dos excursiones distintas aunque vayan seguidas.
//   · Todas las entradas se compran por adelantado.
//
// tipo:  tourevo → servicio nuestro · cliente → vuelos y comidas · libre → sin programar
// e:     igual → como estaba · movido → cambia de hora o día · nuevo → no estaba
//        decidir → necesita que Segar defina
// cot:   qué ítems de la cotización cubre el bloque (el build valida la cobertura)

export const DIAS = [
  { f: '2026-12-22', n: { es: 'Llegada a San Pedro', en: 'Arrival in San Pedro' }, blks: [
    { h: '15:33', f2: '17:41', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Santiago → Calama (CJC) · LATAM', en: 'Flight Santiago → Calama (CJC) · LATAM' } },
    { h: '18:15', f2: '20:00', tipo: 'tourevo', e: 'movido', cot: ['transfer-in'],
      t: { es: 'Traslado Calama → San Pedro de Atacama', en: 'Transfer Calama → San Pedro de Atacama' },
      n: { es: 'El operador calcula el traslado en <b>1 h 45</b>, no en la hora que teníamos: se llega al hotel <b>20:00</b> y no 19:15. Salida del aeropuerto 18:15, con 35 minutos desde que aterriza el vuelo.',
           en: 'The operator calculates the transfer at <b>1 h 45</b>, not the hour we had: you reach the hotel at <b>20:00</b>, not 19:15. Departure from the airport at 18:15, 35 minutes after the flight lands.' },
      conf: { es: '¿35 minutos entre que aterriza el vuelo de las 17:41 y sale el traslado alcanzan, o conviene dejar más?',
              en: 'Are 35 minutes between the 17:41 landing and the transfer departure enough, or should we leave more?' } },
    { h: '20:00', f2: '21:00', tipo: 'libre', e: 'igual',
      t: { es: 'Llegada y check-in en el hotel · resto de la noche libre', en: 'Arrival and check-in at the hotel · rest of the evening free' } },
  ]},

  { f: '2026-12-23', n: { es: 'Quitor, salar y estrellas', en: 'Quitor, salt flat and stars' }, blks: [
    { h: '09:30', f2: '11:30', tipo: 'tourevo', e: 'movido', cot: ['arqueologico'],
      t: { es: 'Pukará de Quitor · guiado', en: 'Pukará de Quitor · guided' },
      n: { es: 'Con el 26 libre y Cejar tomando la tarde, la mañana del 23 es el único hueco que queda para Quitor. Son dos horas suaves a pasos del pueblo, así que no rompe el día de llegada.',
           en: 'With the 26th free and Cejar taking the afternoon, the morning of the 23rd is the only gap left for Quitor. Two gentle hours a step from the village, so it does not spoil the settling-in day.' },
      conf: { es: 'El operador aclara que Quitor y el sandboard son dos excursiones distintas aunque vayan el mismo medio día. Acá van en días distintos, así que se cobran separadas igual. ¿Confirman 09:30 – 11:30 el 23?',
              en: 'The operator notes Quitor and the sandboarding are two separate excursions even within one half day. Here they fall on different days, so they bill separately anyway. Do you confirm 09:30 – 11:30 on the 23rd?' } },
    { h: '11:30', f2: '15:15', tipo: 'libre', e: 'igual',
      t: { es: 'Almuerzo y descanso en el hotel', en: 'Lunch and rest at the hotel' } },
    { h: '15:30', f2: '19:30', tipo: 'tourevo', e: 'movido', cot: ['cejar'],
      t: { es: 'Laguna Cejar · flotación en Laguna Piedra + Ojos de Tebenquiche + Laguna de Tebenquiche',
           en: 'Laguna Cejar · floating at Laguna Piedra + Ojos de Tebenquiche + Tebenquiche Lagoon' },
      n: { es: 'Va por la tarde porque <b>las agencias no pueden operar Cejar en la mañana</b>. El horario es el que propuso el operador. La idea de la mañana relajada se cumple igual: el 23 arranca tarde y con sólo dos horas de Quitor antes.',
           en: 'It runs in the afternoon because <b>agencies cannot operate Cejar in the morning</b>. The time is the one the operator proposed. The relaxed-morning idea still holds: the 23rd starts late with only two hours of Quitor before it.' },
      conf: { es: '¿Confirmado 15:30 – 19:30 el miércoles 23?', en: 'Confirmed 15:30 – 19:30 on Wednesday the 23rd?' } },
    { h: '19:30', f2: '21:00', tipo: 'libre', e: 'nuevo',
      t: { es: 'Vuelta al hotel, ducha y descanso', en: 'Back to the hotel, shower and rest' },
      n: { es: 'Hora y media entre salir del agua salada y la salida al cielo.', en: 'An hour and a half between leaving the salt water and heading out under the sky.' } },
    { h: '21:00', f2: '23:00', tipo: 'tourevo', e: 'movido', cot: ['astronomico'],
      t: { es: 'Tour astronómico · compartido', en: 'Astronomy tour · shared' },
      n: { es: 'Vuelve al 23 y a modalidad compartida, que es lo que estaba cotizado: <b>CLP 40.000 por persona</b> contra los <b>CLP 600.000 por el grupo</b> que sale el privado el 24. Es además la noche que preguntó Segar.',
           en: 'Back to the 23rd and to the shared format, which is what was quoted: <b>CLP 40,000 per person</b> against the <b>CLP 600,000 for the group</b> the private tour costs on the 24th. It is also the night Segar asked about.' },
      conf: { es: '¿Opera el compartido la noche del 23 y se mantiene CLP 40.000 por persona? Aviso: esa noche la luna va al 99,9% de iluminación — el 24 está igual, así que cambiar de noche no mejora nada. Conviene que el guía lo oriente a luna y planetas.',
              en: 'Does the shared tour run on the night of the 23rd and does CLP 40,000 per person hold? Note: that night the moon is 99.9% illuminated — the 24th is the same, so switching nights changes nothing. Better to have the guide aim it at the moon and planets.' } },
  ]},

  { f: '2026-12-24', n: { es: 'Puritama, dunas y Valle de la Luna', en: 'Puritama, dunes and Valle de la Luna' }, blks: [
    { h: '09:30', f2: '13:30', tipo: 'tourevo', e: 'igual', cot: ['puritama'],
      t: { es: 'Termas de Puritama · 3.500 m', en: 'Puritama Hot Springs · 3,500 m' },
      conf: { es: '¿Hay recargo por Nochebuena en Puritama? ¿Y se alcanza a estar de vuelta en San Pedro a las 13:30, con la salida siguiente a las 14:30?',
              en: 'Is there a Christmas Eve supplement on Puritama? And is it feasible to be back in San Pedro by 13:30, with the next departure at 14:30?' } },
    { h: '13:30', f2: '14:30', tipo: 'libre', e: 'igual',
      t: { es: 'Almuerzo y descanso', en: 'Lunch and rest' } },
    { h: '14:30', f2: '16:30', tipo: 'tourevo', e: 'igual', cot: ['marte-sandboard'],
      t: { es: 'Valle de la Muerte · sandboard', en: 'Valle de la Muerte · sandboarding' },
      n: { es: 'Es lo que ya se le confirmó a Segar para el 24. Queda encadenado con el Valle de la Luna, que está al lado, con media hora entre uno y otro.',
           en: 'This is what has already been confirmed to Segar for the 24th. It chains into Valle de la Luna, which sits right next door, with half an hour in between.' },
      conf: { es: '¿Pueden encadenar sandboard 14:30 – 16:30 y Vallecito 17:00 – 21:00 el mismo día, con media hora entre medio? Están en la misma zona, pero el operador pidió dejar aire y queremos que alcance.',
              en: 'Can you chain sandboarding 14:30 – 16:30 into Vallecito 17:00 – 21:00 on the same day, with half an hour between? They are in the same area, but the operator asked for buffer and we want it to hold.' } },
    { h: '17:00', f2: '21:00', tipo: 'tourevo', e: 'movido', cot: ['luna-sur'],
      t: { es: 'Valle de la Luna Sur · Vallecito al atardecer', en: 'Valle de la Luna South · Vallecito at sunset' },
      n: { es: 'Sale a las <b>17:00</b> porque el operador avisa que a las 17:30 <b>ya no se alcanza a hacer bien</b>. Y pasa al 24 porque el 23 lo toma Cejar: los dos son de tarde y no caben el mismo día.',
           en: 'It leaves at <b>17:00</b> because the operator warns that at 17:30 <b>there is no longer time to do it properly</b>. And it moves to the 24th because Cejar takes the 23rd: both are afternoon excursions and cannot share a day.' },
      conf: { es: '¿Confirmado 17:00 – 21:00 el 24? ¿Hay recargo de Nochebuena?', en: 'Confirmed 17:00 – 21:00 on the 24th? Is there a Christmas Eve supplement?' } },
    { h: '21:30', f2: null, tipo: 'cliente', e: 'decidir',
      t: { es: 'Cena de Nochebuena', en: 'Christmas Eve dinner' },
      n: { es: '<b>Esto lo tiene que confirmar Segar.</b> Estaba a las 20:00, pero el Vallecito termina 21:00. O la cena se corre a las 21:30 — que para Nochebuena en Chile es la hora normal — o el Valle de la Luna no puede ir el 24. Sin traslado nuestro: van en el shuttle del hotel.',
           en: '<b>Segar has to confirm this.</b> It was at 20:00, but Vallecito ends at 21:00. Either dinner moves to 21:30 — which for Christmas Eve in Chile is the normal hour — or Valle de la Luna cannot go on the 24th. No transfer from us: they take the hotel shuttle.' } },
  ]},

  { f: '2026-12-25', n: { es: 'Altiplano', en: 'Altiplano' }, blks: [
    { h: '08:00', f2: '18:00', tipo: 'tourevo', e: 'igual', cot: ['piedras-rojas'],
      t: { es: 'Altiplano · Laguna Chaxa, Toconao, Miscanti y Piedras Rojas', en: 'Altiplano · Laguna Chaxa, Toconao, Miscanti and Piedras Rojas' },
      n: { es: 'Con Chaxa adentro el operador lo opera de 08:00 a 18:00 y el valor del 25 de diciembre queda en <b>CLP 270.000 por persona</b>, contra los 210.000 cotizados. Nos avisa que <b>no lo recomienda</b>: hace el día muy largo. Si se saca Chaxa, vuelve a ser más corto y más barato.',
           en: 'With Chaxa in, the operator runs it 08:00 to 18:00 and the 25 December rate lands at <b>CLP 270,000 per person</b>, against the 210,000 quoted. They tell us they <b>do not recommend it</b>: it makes for a very long day. Drop Chaxa and it gets shorter and cheaper.' },
      conf: { es: '¿Cuánto es la entrada de Chaxa, para sumarla a las que se compran por adelantado? Y si se saca Chaxa, ¿en cuánto queda el valor y el horario?',
              en: 'How much is the Chaxa entrance fee, to add it to the ones bought in advance? And if Chaxa comes out, what do the price and timing become?' } },
    { h: '20:00', f2: '21:30', tipo: 'cliente', e: 'igual',
      t: { es: 'Cena', en: 'Dinner' } },
  ]},

  { f: '2026-12-26', n: { es: 'Día libre y salida', en: 'Free day and departure' }, blks: [
    { h: '08:00', f2: '09:45', tipo: 'libre', e: 'igual',
      t: { es: 'Mañana libre en San Pedro · desayuno y check-out', en: 'Free morning in San Pedro · breakfast and check-out' },
      n: { es: 'El último día queda sin excursiones.', en: 'The last day carries no excursions.' } },
    { h: '10:00', f2: '11:45', tipo: 'tourevo', e: 'movido', cot: ['transfer-out'],
      t: { es: 'Traslado San Pedro → Calama', en: 'Transfer San Pedro → Calama' },
      n: { es: 'Se adelanta a las <b>10:00</b>: con el traslado en 1 h 45, salir 10:24 dejaba menos de dos horas antes del vuelo. Llega 11:45 para el vuelo de las 13:44.',
           en: 'Brought forward to <b>10:00</b>: at 1 h 45 for the transfer, leaving at 10:24 gave under two hours before the flight. It arrives 11:45 for the 13:44 flight.' },
      conf: { es: '¿Confirman recogida 10:00 en el hotel para llegar 11:45 a Calama?', en: 'Do you confirm 10:00 hotel pickup to reach Calama at 11:45?' } },
    { h: '13:44', f2: '15:52', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Calama → Santiago · LATAM', en: 'Flight Calama → Santiago · LATAM' } },
    { h: '20:00', f2: '23:45', tipo: 'cliente', e: 'igual',
      t: { es: 'Check-in internacional en Santiago', en: 'International check-in in Santiago' } },
    { h: '23:45', f2: '23:59', tipo: 'cliente', e: 'igual',
      t: { es: 'Vuelo Santiago → Dallas · AA940', en: 'Flight Santiago → Dallas · AA940' } },
  ]},
];

export const CAMBIOS = [
  { tit: { es: 'Cejar sólo se puede operar por la tarde', en: 'Cejar can only be operated in the afternoon' },
    cuerpo: {
      es: ['Las agencias no pueden entrar a Cejar en la mañana, así que la salida de las 10:15 que habíamos armado no existe. Va el <b>miércoles 23 de 15:30 a 19:30</b>, que es el horario que propuso el operador.',
           'La idea de que el 23 fuera un día tranquilo se mantiene igual: la mañana lleva sólo dos horas de Quitor y después hay casi cuatro horas libres antes de salir al salar.'],
      en: ['Agencies cannot enter Cejar in the morning, so the 10:15 departure we had built does not exist. It runs <b>Wednesday 23 from 15:30 to 19:30</b>, the slot the operator proposed.',
           'The idea of the 23rd being a quiet day still holds: the morning carries only two hours at Quitor and there are almost four free hours before heading out to the salt flat.'],
    } },
  { tit: { es: 'El Vallecito sale 17:00 y por eso se va al 24', en: 'Vallecito leaves at 17:00, and so it moves to the 24th' },
    cuerpo: {
      es: ['El operador avisa que saliendo 17:30 <b>no se alcanza a hacer bien</b>: el máximo es 17:00, y la excursión queda de <b>17:00 a 21:00</b>.',
           'Con eso, Cejar (15:30 – 19:30) y el Vallecito (17:00 – 21:00) se cruzan y ya no pueden ir el mismo día. Cejar se queda el 23 —así la astronomía de las 21:00 tiene hora y media de aire— y el Vallecito pasa al 24.'],
      en: ['The operator warns that leaving at 17:30 <b>does not allow doing it properly</b>: the latest is 17:00, and the excursion runs <b>17:00 to 21:00</b>.',
           'With that, Cejar (15:30 – 19:30) and Vallecito (17:00 – 21:00) overlap and can no longer share a day. Cejar stays on the 23rd — that way the 21:00 astronomy has an hour and a half of buffer — and Vallecito moves to the 24th.'],
    } },
  { tit: { es: 'La astronomía vuelve a compartida y al 23', en: 'The astronomy goes back to shared and to the 23rd' },
    cuerpo: {
      es: ['El privado del 24 sale <b>CLP 600.000 por el grupo</b>. El compartido que ya estaba cotizado son <b>CLP 40.000 por persona</b>, y es la noche por la que preguntó Segar: queda el <b>23 de 21:00 a 23:00</b>.',
           'Un dato que conviene que sepan antes: esa noche la luna va al <b>99,9% de iluminación</b>, y el 24 está igual. Cambiar de noche no mejora nada, así que lo que corresponde es que el guía lo oriente a luna y planetas.'],
      en: ['The private tour on the 24th costs <b>CLP 600,000 for the group</b>. The shared one already quoted is <b>CLP 40,000 per person</b>, and it is the night Segar asked about: it lands on the <b>23rd, 21:00 to 23:00</b>.',
           'Something worth knowing beforehand: that night the moon is <b>99.9% illuminated</b>, and the 24th is the same. Switching nights changes nothing, so the right move is for the guide to aim it at the moon and planets.'],
    } },
  { tit: { es: 'Quitor va la mañana del 23, y se cobra aparte', en: 'Quitor goes on the morning of the 23rd, and bills separately' },
    cuerpo: {
      es: ['El operador aclara que <b>Quitor y el sandboard son dos excursiones distintas</b>, aunque se hagan en el mismo medio día. Como el 26 tiene que quedar libre y las dos tardes se las llevan Cejar y el Vallecito, el único hueco que queda es la mañana del 23.',
           'Son dos horas a pasos del pueblo, así que no pesan en el día de llegada.'],
      en: ['The operator makes clear that <b>Quitor and the sandboarding are two separate excursions</b>, even done within the same half day. Since the 26th has to stay free and both afternoons go to Cejar and Vallecito, the only gap left is the morning of the 23rd.',
           'Two hours a step from the village, so it does not weigh on the settling-in day.'],
    } },
  { tit: { es: 'Los traslados son de 1 h 45', en: 'The transfers take 1 h 45' },
    cuerpo: {
      es: ['No de una hora larga como teníamos. Cambia las dos puntas del viaje: el 22 se llega al hotel a las <b>20:00</b> y no a las 19:15, y el 26 el traslado se adelanta a las <b>10:00</b> para llegar a Calama 11:45, con dos horas antes del vuelo de las 13:44.'],
      en: ['Not the long hour we had. It changes both ends of the trip: on the 22nd you reach the hotel at <b>20:00</b> rather than 19:15, and on the 26th the transfer moves up to <b>10:00</b> to reach Calama at 11:45, two hours before the 13:44 flight.'],
    } },
  { tit: { es: 'Lo que tiene que decidir Segar: la hora de la cena de Nochebuena', en: 'What Segar has to decide: the time of the Christmas Eve dinner' },
    cuerpo: {
      es: ['El Vallecito termina a las 21:00 y la cena estaba a las 20:00. O la cena se corre a las <b>21:30</b> —que para Nochebuena en Chile es la hora normal— o el Valle de la Luna no puede ir el 24, y entonces habría que sacarlo del programa: no queda otra tarde donde ponerlo.',
           'El traslado a la cena ya no hace falta: Segar confirmó que usan el shuttle del hotel.'],
      en: ['Vallecito ends at 21:00 and dinner was set for 20:00. Either dinner moves to <b>21:30</b> — which for Christmas Eve in Chile is the normal hour — or Valle de la Luna cannot go on the 24th, and it would have to come out of the programme: there is no other afternoon for it.',
           'The transfer to dinner is no longer needed: Segar confirmed they will use the hotel shuttle.'],
    } },
];

export const PREGUNTAS_GENERALES = [
  { q: { es: 'Valor final del programa para 4 pasajeros', en: 'Final programme price for 4 travellers' },
    d: { es: 'Quedamos en que la cotización traía algunas alternativas con Valor Mínimo, que aplica bajo 3 pax y no es el caso. Esas alternativas además quedaron fuera de la elección. Necesitamos el valor por persona de los nueve servicios elegidos, con los recargos del 24 y 25 ya incluidos.',
         en: 'We agreed the quotation carried some alternatives at a Minimum Rate, which applies under 3 travellers and is not the case here. Those alternatives are also out of the final selection. We need the per-person price for the nine chosen services, with the 24th and 25th supplements already included.' } },
  { q: { es: 'Recargos de Nochebuena y Navidad, servicio por servicio', en: 'Christmas Eve and Christmas Day supplements, service by service' },
    d: { es: 'Del 25 ya sabemos el valor con Chaxa (CLP 270.000 por pax). Falta saber si Puritama, el sandboard y el Vallecito del 24 llevan recargo.',
         en: 'For the 25th we already have the figure with Chaxa (CLP 270,000 per traveller). We still need to know whether Puritama, the sandboarding and Vallecito on the 24th carry a supplement.' } },
  { q: { es: 'Entrada de Laguna Chaxa', en: 'Laguna Chaxa entrance fee' },
    d: { es: 'Falta el monto para sumarlo al resto de las entradas, que van todas por adelantado. Y si finalmente sacamos Chaxa, en cuánto queda el full day y con qué horario.',
         en: 'We need the amount to add to the rest of the entrance fees, which all go in advance. And if we end up dropping Chaxa, what the full day becomes in price and timing.' } },
  { q: { es: 'Aire entre excursiones', en: 'Buffer between excursions' },
    d: { es: 'Nos pediste dejar margen por tránsito y accesos. En el cuadro dejamos una hora después de Puritama y media hora entre el sandboard y el Vallecito. Decinos si en algún punto queda corto.',
         en: 'You asked us to leave margin for traffic and access controls. In the table we left an hour after Puritama and half an hour between the sandboarding and Vallecito. Tell us if any of it falls short.' } },
];
