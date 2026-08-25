// Fuente única de verdad de la cotización COT-2026-0160 · Segar.
//
// Los precios viven acá una sola vez y las dos versiones (español e inglés) se
// arman desde este mismo objeto: así los subtotales y el total del pack no
// pueden quedar distintos entre un idioma y el otro.
//
// Todos los montos están en pesos chilenos. `valor` y `entradas` son POR
// PERSONA salvo que el servicio traiga `base: 2`, que marca las tarifas
// mínimas por grupo de 2 pax de las alternativas.

export const doc = {
  numero: 'COT-2026-0160',
  emitida: '2026-08-24',
  vigencia: '2026-09-07',
  pax: 4,
  moneda: 'CLP',
};

export const cliente = {
  nombre: 'Segar',
};

// ---------------------------------------------------------------------------
// Programa confirmado
// ---------------------------------------------------------------------------

export const programa = [
  {
    id: 'transfer-in',
    fecha: '2026-12-22',
    franja: 'transfer',
    horario: { es: '18:15 – 20:00 hrs.', en: '18:15 – 20:00 hrs' },
    titulo: {
      es: 'Traslado de llegada · Aeropuerto El Loa → San Pedro',
      en: 'Arrival transfer · El Loa Airport → San Pedro',
    },
    texto: {
      es: 'Traslado en servicio privado desde el aeropuerto de Calama hasta el hotel en San Pedro de Atacama.',
      en: 'Private transfer from Calama airport to your hotel in San Pedro de Atacama.',
    },
    etiqueta: { es: 'Servicio privado', en: 'Private service' },
    valor: 30000,
    entradas: 0,
    entradasAnticipadas: true,
  },
  {
    id: 'puritama',
    fecha: '2026-12-24',
    franja: 'am',
    horario: { es: '09:30 – 13:30 hrs.', en: '09:30 – 13:30 hrs' },
    titulo: { es: 'Termas de Puritama', en: 'Puritama Hot Springs' },
    texto: {
      es: 'Este viaje invita a disfrutar y relajarse en los ocho pozones de las Termas de Puritama, famosas por sus saludables aguas tibias y su belleza natural.',
      en: 'An invitation to slow down and unwind in the eight natural pools of the Puritama Hot Springs, known for their therapeutic warm waters and the beauty of the canyon that holds them.',
    },
    incluye: { es: 'Pisco sour y snack', en: 'Pisco sour and snack' },
    valor: 75000,
    entradas: 35000,
    entradasAnticipadas: true,
  },
  {
    id: 'luna-sur',
    fecha: '2026-12-24',
    franja: 'pm',
    horario: { es: '17:00 – 21:00 hrs.', en: '17:00 – 21:00 hrs' },
    titulo: { es: 'Valle de la Luna Sur', en: 'Valle de la Luna South' },
    texto: {
      es: 'Nos adentraremos por la Cordillera de la Sal hasta llegar al Vallecito, la entrada off circuit del Valle de la Luna, donde conoceremos el fascinante Río de Sal y disfrutaremos de una vista panorámica desde las cornisas del área. Las recorreremos caminando entre antiguos piques mineros de sal para terminar en un bus abandonado en medio de la cordillera, testigo del ajetreado pasado minero de la zona.',
      en: 'We head into the Cordillera de la Sal as far as El Vallecito, the off-circuit entrance to the Valle de la Luna, where we discover the remarkable Salt River and take in the panoramic view from the ledges above. We walk them past old salt-mining shafts and finish at an abandoned bus stranded in the middle of the range, a witness to the area’s busy mining past.',
    },
    incluye: { es: 'Snack', en: 'Snack' },
    valor: 75000,
    entradas: 0,
    entradasAnticipadas: true,
    entradasNota: {
      es: 'Hoy este circuito no paga entrada a parques. Es posible que sí la cobren a la fecha del viaje: si ocurre, se informa antes y se cobra al valor vigente.',
      en: 'This circuit currently charges no park fee. One may be in force by your travel date: if so, we will tell you beforehand and charge it at the rate then published.',
    },
  },
  {
    id: 'astronomico',
    fecha: '2026-12-23',
    franja: 'noche',
    horario: { es: '21:00 – 23:00 hrs.', en: '21:00 – 23:00 hrs' },
    titulo: { es: 'Tour astronómico', en: 'Astronomy tour' },
    texto: {
      es: 'No realizamos directamente el tour astronómico, pero lo coordinamos a modo de cortesía para ustedes. Se opera en modalidad compartida.',
      en: 'We do not operate the astronomy tour ourselves, but we coordinate it for you as a courtesy. It runs on a shared basis.',
    },
    incluye: { es: 'Bebida caliente o fría y snack', en: 'Hot or cold drink and snack' },
    etiqueta: { es: 'Compartido · coordinado por nosotros', en: 'Shared · coordinated by us' },
    valor: 40000,
    entradas: 0,
    entradasAnticipadas: true,
  },
  {
    id: 'marte-sandboard',
    fecha: '2026-12-24',
    franja: 'pm',
    horario: { es: '14:30 – 16:30 hrs.', en: '14:30 – 16:30 hrs' },
    titulo: { es: 'Valle de Marte + Sandboard', en: 'Valle de Marte + Sandboarding' },
    texto: {
      es: 'Recorreremos los rincones y extraordinarios pasajes del Valle de Marte hasta encontrar sus dunas, en las que podrán deslizarse sobre tablas especialmente adecuadas para disfrutar de este deporte.',
      en: 'We explore the corners and extraordinary passages of the Valle de Marte until we reach its dunes, where you can ride down on boards made for exactly this.',
    },
    incluye: { es: 'Snack y equipo completo de sandboard', en: 'Snack and full sandboard equipment' },
    valor: 110000,
    entradas: 7500,
    entradasAnticipadas: true,
    reemplazable: true,
  },
  {
    id: 'cejar',
    fecha: '2026-12-23',
    franja: 'pm',
    horario: { es: '15:30 – 19:30 hrs.', en: '15:30 – 19:30 hrs' },
    titulo: {
      es: 'Laguna Cejar + Ojos de Tebenquiche + Laguna de Tebenquiche',
      en: 'Cejar Lagoon + Ojos de Tebenquiche + Tebenquiche Lagoon',
    },
    texto: {
      es: 'Este ya clásico del corazón del salar de Atacama ofrece un recorrido en que es posible disfrutar del refrescante baño en las saladas aguas esmeralda de la laguna Piedra (Cejar), donde se experimenta la flotación sin esfuerzo, para luego continuar a los Ojos de Tebenquiche y cerrar en la laguna de Tebenquiche y sus fascinantes rocas vivientes, colonizadas desde hace millones de años por extremófilos que escogieron estos increíbles ecosistemas para sobrevivir, donde hasta su descubrimiento la vida era impensada. Todo bajo la atenta mirada de los volcanes de la cordillera de Los Andes.',
      en: 'This classic of the heart of the Atacama salt flat opens with a refreshing swim in the emerald brine of Laguna Piedra (Cejar), where you float effortlessly, then continues to the Ojos de Tebenquiche and closes at Tebenquiche Lagoon and its extraordinary living rocks, colonised for millions of years by extremophiles that chose these improbable ecosystems to survive in, where until their discovery life was thought impossible. All of it under the steady gaze of the Andean volcanoes.',
    },
    incluye: { es: 'Pisco sour y snack', en: 'Pisco sour and snack' },
    valor: 80000,
    entradas: 21000,
    entradasAnticipadas: true,
  },
  {
    id: 'piedras-rojas',
    fecha: '2026-12-25',
    franja: 'fullday',
    horario: { es: '08:00 – 18:00 hrs.', en: '08:00 – 18:00 hrs' },
    titulo: {
      es: 'Altiplano · Laguna Chaxa, Toconao, Miscanti y Piedras Rojas',
      en: 'Altiplano · Laguna Chaxa, Toconao, Miscanti and Piedras Rojas',
    },
    texto: {
      es: 'En este tour se conoce el sureste del salar de Atacama por su borde este, incluyendo diferentes lagunas y salares altoandinos, entre ellos las lagunas de Miscanti y Miñiques, así como refugios milenarios que sirvieron a cazadores y recolectores: incansables nómades que, antes de lograr la domesticación, siguieron a los animales necesarios para su supervivencia. Además pasaremos por el interior de los pueblos de Socaire y Toconao.',
      en: 'This tour takes in the south-east of the Atacama salt flat along its eastern rim, including several high-Andean lagoons and salt flats — among them Miscanti and Miñiques — as well as millennia-old shelters used by hunter-gatherers: tireless nomads who, before domestication, followed the animals they depended on. We also drive through the villages of Socaire and Toconao.',
    },
    incluye: { es: 'Almuerzo en hábitat natural y snack', en: 'Lunch in a natural setting and snack' },
    valor: 270000,   // 25 dic con Chaxa, confirmado por el operador (era 210.000)
    entradas: 15000,
    entradasAnticipadas: true, // + la entrada de Chaxa, monto por confirmar
  },
  {
    id: 'arqueologico',
    fecha: '2026-12-23',
    franja: 'am',
    horario: { es: '09:30 – 11:30 hrs.', en: '09:30 – 11:30 hrs' },
    titulo: { es: 'Tour arqueológico · Pukará de Quitor', en: 'Archaeological tour · Pukará de Quitor' },
    texto: {
      es: 'Este tour permite conocer el clásico Pukará de Quitor, hito de la conquista española en la zona y vestigio histórico y cultural inconmensurable, destacando su importancia como la última resistencia atacameña contra la invasión hispánica.',
      en: 'A visit to the Pukará de Quitor, landmark of the Spanish conquest in the area and a historical and cultural site of immense value, remembered above all as the last Atacameño stand against the Spanish invasion.',
    },
    incluye: { es: 'Snack', en: 'Snack' },
    valor: 75000,
    entradas: 6000,
    entradasAnticipadas: true,
  },
  {
    id: 'transfer-out',
    fecha: '2026-12-26',
    franja: 'transfer',
    horario: { es: '10:00 – 11:45 hrs.', en: '10:00 – 11:45 hrs' },
    titulo: {
      es: 'Traslado de salida · San Pedro → Aeropuerto El Loa',
      en: 'Departure transfer · San Pedro → El Loa Airport',
    },
    texto: {
      es: 'Traslado en servicio privado desde el hotel en San Pedro de Atacama hasta el aeropuerto de Calama.',
      en: 'Private transfer from your hotel in San Pedro de Atacama to Calama airport.',
    },
    etiqueta: { es: 'Servicio privado', en: 'Private service' },
    valor: 30000,
    entradas: 0,
    entradasAnticipadas: true,
  },
];

// ---------------------------------------------------------------------------
// Alternativas al jueves 24 AM — en orden de preferencia
//
// Las tres reemplazan a "Valle de Marte + Sandboard". Las dos primeras se
// cotizan como tarifa MÍNIMA de grupo sobre base 2 pax (`base: 2`); el
// renderizador divide por esa base para poder compararlas contra el resto del
// programa, que va por persona.
// ---------------------------------------------------------------------------

export const alternativas = [
  {
    id: 'vilama',
    orden: 1,
    horario: { es: '10:00 – 13:30 hrs.', en: '10:00 – 13:30 hrs' },
    titulo: { es: 'Trekking Cascadas de Vilama', en: 'Vilama Waterfalls trek' },
    texto: {
      es: 'Un trekking corto que recorre una quebrada por el cauce de un río, entremedio de cactus gigantes y refugios de antiguos cazadores recolectores, con refrescantes baños en cascadas de aguas puras de la cordillera.',
      en: 'A short trek up a ravine along a riverbed, between giant cacti and the shelters of ancient hunter-gatherers, with refreshing swims in waterfalls fed by pure mountain water.',
    },
    incluye: {
      es: 'Ración de marcha, equipo de seguridad, bastones, primeros auxilios, comunicación local y satelital',
      en: 'Trail ration, safety equipment, trekking poles, first aid, local and satellite communications',
    },
    valor: 210000,
    entradas: 0,
    entradasAnticipadas: true,
    base: 2,
    ficha: { altMin: 2850, altMax: 2940, distancia: 3 },
  },
  {
    id: 'cornisas',
    orden: 2,
    horario: { es: '09:30 – 13:30 hrs.', en: '09:30 – 13:30 hrs' },
    titulo: {
      es: 'Trekking Cornisas (Túnel) + Valle de Marte',
      en: 'Cornisas (Tunnel) trek + Valle de Marte',
    },
    texto: {
      es: 'Nos internaremos en el Llano de la Paciencia para tomar una antigua ruta caravanera donde luego se construiría el túnel que hace 90 años unía Calama con San Pedro, para después recorrer las cornisas del Valle de Catarpe hasta llegar al Valle de Marte, parte del corazón de la Cordillera de la Sal, que cruzaremos a través de sus grandes dunas antes de tomar el camino de vuelta a San Pedro de Atacama.',
      en: 'We head into the Llano de la Paciencia to pick up an old caravan route, later the line of the tunnel that ninety years ago linked Calama with San Pedro, then follow the ledges of the Catarpe Valley to the Valle de Marte — part of the heart of the Cordillera de la Sal — crossing its great dunes before taking the road back to San Pedro de Atacama.',
    },
    incluye: {
      es: 'Ración de marcha, equipo de seguridad, bastones, primeros auxilios, comunicación local y satelital',
      en: 'Trail ration, safety equipment, trekking poles, first aid, local and satellite communications',
    },
    valor: 270000,
    entradas: 12000,
    entradasAnticipadas: true,
    base: 2,
    ficha: { altMin: 2570, altMax: 2730, distancia: 7.5 },
  },
  {
    id: 'bike-catarpe',
    orden: 3,
    horario: { es: '09:30 – 13:30 hrs.', en: '09:30 – 13:30 hrs' },
    titulo: {
      es: 'Bike en Valle de Catarpe y Garganta del Diablo',
      en: 'Biking in the Catarpe Valley and Garganta del Diablo',
    },
    texto: {
      es: 'En este viaje conoceremos la cuenca del río San Pedro y recorreremos los intrincados pasajes y columnas entre las impresionantes formaciones de la Cordillera de la Sal. ¡Una experiencia imperdible!',
      en: 'On this ride we follow the basin of the San Pedro river and thread the intricate passages and columns between the striking formations of the Cordillera de la Sal. An experience not to be missed.',
    },
    incluye: {
      es: 'Ración de marcha, bicicleta y equipo de seguridad',
      en: 'Trail ration, bicycle and safety equipment',
    },
    valor: 100000,
    entradas: 5000,
    entradasAnticipadas: true,
    base: 1,
  },
];

// ---------------------------------------------------------------------------
// Flota. Las fotos se leen desde ./vehiculos/ y se incrustan en el HTML como
// data URI para que el PDF viaje solo, sin archivos sueltos al lado.
// Si un archivo no está, la ficha se arma igual y sólo se omite la foto.
// ---------------------------------------------------------------------------

export const flota = [
  {
    archivo: 'chevrolet-tahoe.jpg',
    modelo: 'Chevrolet Tahoe',
    detalle: {
      es: '4×4 · hasta 5 pasajeros con equipaje',
      en: '4×4 · up to 5 passengers with luggage',
    },
  },
  {
    archivo: 'toyota-4runner.jpg',
    modelo: 'Toyota 4Runner SR5',
    detalle: {
      es: '4×4 · hasta 4 pasajeros con equipaje',
      en: '4×4 · up to 4 passengers with luggage',
    },
  },
];
