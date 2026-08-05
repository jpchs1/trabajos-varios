/* ==========================================================================
   Datos iniciales: ajustes del taller, catálogo de precios y la cotización
   de ejemplo (la misma planilla del Jaguar XF que se usaba en Excel).
   Todo esto es sólo la semilla: una vez cargado, se edita desde la app.
   ========================================================================== */

const AJUSTES_DEFAULT = {
  taller: 'Taller Automotriz Vittorio',
  lema: 'Mecánica general · Suspensión · Diagnóstico',
  rut: '',
  direccion: '',
  comuna: '',
  telefono: '',
  email: '',
  web: '',
  ivaPct: 19,
  aplicaIvaDefault: false,
  gastosPct: 10,
  validezDias: 15,
  seguimientoDias: 5,
  mantencionKm: 10000,
  mantencionMeses: 6,
  configurado: false,
  garantia: 'Garantía de 3 meses o 5.000 km (lo que ocurra primero) sobre la mano de obra realizada. Los repuestos mantienen la garantía del fabricante.',
  condiciones: [
    'Valores expresados en pesos chilenos.',
    'Cualquier trabajo adicional detectado durante la reparación se informa y se aprueba antes de ejecutarlo.',
    'Los repuestos se piden una vez aprobada la cotización.',
    'El vehículo se entrega contra pago total del saldo, salvo acuerdo previo.'
  ],
  folioPrefijo: 'COT'
};

/* Catálogo de mano de obra: lo que se cotiza una y otra vez. */
const CATALOGO_SERVICIOS = [
  { cat: 'Suspensión y dirección', desc: 'Cambio bandejas traseras, brazos suspensión trasera, bieletas reguladores ambos lados', obs: '', precio: 80000 },
  { cat: 'Suspensión y dirección', desc: 'Bandeja superior delantera, bandeja inferior, brazo suspensión delantera, terminales y axiales ambos lados', obs: '', precio: 90000 },
  { cat: 'Suspensión y dirección', desc: 'Cambio amortiguadores traseros', obs: '', precio: 60000 },
  { cat: 'Suspensión y dirección', desc: 'Cambio amortiguadores delanteros', obs: 'Incluye desmontaje de resortes', precio: 70000 },
  { cat: 'Suspensión y dirección', desc: 'Alineación completa', obs: 'Todos los ángulos', precio: 45000 },
  { cat: 'Suspensión y dirección', desc: 'Cambio bujes barra estabilizadora', obs: '', precio: 35000 },
  { cat: 'Frenos', desc: 'Cambio líquido de frenos, sangrado completo sistema', obs: '', precio: 15000 },
  { cat: 'Frenos', desc: 'Cambio pastillas de freno delanteras', obs: 'Incluye limpieza de mordazas', precio: 25000 },
  { cat: 'Frenos', desc: 'Cambio pastillas de freno traseras', obs: '', precio: 25000 },
  { cat: 'Frenos', desc: 'Rectificado de discos de freno (por eje)', obs: '', precio: 30000 },
  { cat: 'Mantención', desc: 'Cambio aceite motor y filtro', obs: 'Incluye revisión de niveles', precio: 25000 },
  { cat: 'Mantención', desc: 'Cambio filtro aire motor, limpieza sistema', obs: 'Limpieza flujómetro', precio: 15000 },
  { cat: 'Mantención', desc: 'Cambio filtro de habitáculo', obs: '', precio: 10000 },
  { cat: 'Mantención', desc: 'Cambio bujías, desmontaje admisión', obs: '', precio: 30000 },
  { cat: 'Mantención', desc: 'Cambio correa de distribución', obs: 'Incluye tensor y poleas', precio: 180000 },
  { cat: 'Diagnóstico', desc: 'Escáner completo, chequeo códigos y parámetros', obs: 'Revisión general de valores', precio: 15000 },
  { cat: 'Diagnóstico', desc: 'Diagnóstico eléctrico con multímetro', obs: '', precio: 25000 },
  { cat: 'Eléctrico', desc: 'Cambio óptico trasero', obs: 'Desmontaje bisel', precio: 20000 },
  { cat: 'Eléctrico', desc: 'Cambio batería y prueba de carga alternador', obs: '', precio: 12000 },
  { cat: 'Motor', desc: 'Cambio bomba de agua y refrigerante', obs: '', precio: 90000 },
  { cat: 'Motor', desc: 'Limpieza de inyectores por ultrasonido', obs: '', precio: 60000 },
  { cat: 'Otros', desc: 'Insumos + traslados + diagnóstico + niveles', obs: 'Lavado motor, lavado carrocería', precio: 15000 }
];

/* Catálogo de repuestos e insumos. */
const CATALOGO_REPUESTOS = [
  { cat: 'Lubricantes', desc: 'Aceite 5W30 100% sintético (litro)', precio: 11500 },
  { cat: 'Lubricantes', desc: 'Aceite 5W40 100% sintético (litro)', precio: 12000 },
  { cat: 'Lubricantes', desc: 'Líquido de frenos DOT 4', precio: 14000 },
  { cat: 'Lubricantes', desc: 'Refrigerante concentrado (litro)', precio: 9000 },
  { cat: 'Filtros', desc: 'Filtro aceite motor', precio: 8500 },
  { cat: 'Filtros', desc: 'Filtro aire motor', precio: 22000 },
  { cat: 'Filtros', desc: 'Filtro de combustible', precio: 18000 },
  { cat: 'Filtros', desc: 'Filtro de habitáculo', precio: 12000 },
  { cat: 'Encendido', desc: 'Bujía', precio: 12500 },
  { cat: 'Encendido', desc: 'Bobina de encendido', precio: 38000 },
  { cat: 'Suspensión', desc: 'Amortiguador trasero', precio: 95000 },
  { cat: 'Suspensión', desc: 'Amortiguador delantero', precio: 120000 },
  { cat: 'Suspensión', desc: 'Bandeja de suspensión', precio: 75000 },
  { cat: 'Suspensión', desc: 'Bieleta barra estabilizadora', precio: 18000 },
  { cat: 'Suspensión', desc: 'Terminal de dirección', precio: 22000 },
  { cat: 'Frenos', desc: 'Juego pastillas de freno', precio: 45000 },
  { cat: 'Frenos', desc: 'Disco de freno', precio: 42000 }
];

/* Cotización de ejemplo: réplica exacta de la planilla adjunta. */
function cotizacionEjemplo() {
  return {
    id: 'demo-jaguar-xf',
    folio: 'COT-2026-0001',
    estado: 'enviada',
    creada: '2026-08-05T12:00:00.000Z',
    actualizada: '2026-08-05T12:00:00.000Z',
    cliente: { nombre: 'Rodrigo Sepúlveda', telefono: '+56 9 8765 4321', email: '', rut: '' },
    vehiculo: { marcaModelo: 'Jaguar XF 30', patente: 'DJBV53', anio: '2012', km: '144000', color: '', vin: '' },
    fechaIngreso: '2026-08-05',
    fotos: [],
    firma: null,
    mostrarUnitarios: true,
    historial: [{ fecha: '2026-08-05T12:00:00.000Z', texto: 'Cotización creada' }],
    fechaEntrega: '',
    motivo: 'Suspensión, mantención',
    trabajos: [
      { id: 't1', desc: 'Cambio bandejas traseras, brazos suspensión trasera, bieletas reguladores ambos lados', obs: '', realizado: false, mo: 80000 },
      { id: 't2', desc: 'Bandeja superior delantera, bandeja inferior, brazo suspensión delantera, terminales y axiales ambos lados', obs: '', realizado: false, mo: 90000 },
      { id: 't3', desc: 'Cambio amortiguadores traseros', obs: '', realizado: false, mo: 60000 },
      { id: 't4', desc: 'Cambio líquido de frenos, sangrado completo sistema', obs: '', realizado: false, mo: 15000 },
      { id: 't5', desc: 'Cambio filtro aire motor, limpieza sistema', obs: 'Limpieza flujómetro', realizado: false, mo: 15000 },
      { id: 't6', desc: 'Alineación completa', obs: 'Todos los ángulos', realizado: false, mo: 45000 },
      { id: 't7', desc: 'Insumos + traslados + diagnóstico + niveles', obs: 'Lavado motor, lavado carrocería', realizado: false, mo: 15000 },
      { id: 't8', desc: 'Escáner completo, chequeo códigos y parámetros', obs: 'Revisión general de valores', realizado: false, mo: 15000 },
      { id: 't9', desc: 'Cambio bujías, desmontaje admisión', obs: '', realizado: false, mo: 30000 },
      { id: 't10', desc: 'Cambio óptico trasero', obs: 'Desmontaje bisel', realizado: false, mo: 0 }
    ],
    repuestos: [
      { id: 'r1', cant: 1, desc: 'Líquido de frenos DOT 4', unit: 14000 },
      { id: 'r2', cant: 1, desc: 'Filtro aire motor', unit: 22000 },
      { id: 'r3', cant: 6, desc: 'Bujías', unit: 12500 },
      { id: 'r4', cant: 7, desc: 'Aceite 5W30 100% sintético', unit: 11500 },
      { id: 'r5', cant: 1, desc: 'Filtro aceite motor', unit: 8500 }
    ],
    abonos: [],
    gastosPct: 10,
    ivaPct: 19,
    aplicaIva: false,
    descuento: 0,
    notas: ''
  };
}
