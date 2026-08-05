/* ==========================================================================
   Taller Automotriz Vittorio — núcleo
   Utilidades, datos, cálculo, router y acciones globales.
   Sin dependencias: los archivos se cargan como scripts clásicos, en orden.
   ========================================================================== */

/* ------------------------------------------------------------- utilidades */
const $  = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

const CLP = new Intl.NumberFormat('es-CL', { maximumFractionDigits: 0 });
const NUM = new Intl.NumberFormat('es-CL', { maximumFractionDigits: 2 });

const money = (n) => '$' + CLP.format(Math.round(num(n)));
const miles = (n) => CLP.format(Math.round(num(n)));
/** Valor tal como se ve dentro de un campo editable: vacío si es cero. */
const campoMonto = (n) => (num(n) ? '$ ' + miles(n) : '');

/** Monto abreviado para espacios chicos: $1,2M / $585k */
function montoCorto(n) {
  const v = Math.round(num(n));
  if (Math.abs(v) >= 1000000) return '$' + (v / 1000000).toFixed(v % 1000000 === 0 ? 0 : 1).replace('.', ',') + 'M';
  if (Math.abs(v) >= 10000) return '$' + Math.round(v / 1000) + 'k';
  return money(v);
}

function num(v) {
  if (typeof v === 'number') return isFinite(v) ? v : 0;
  if (v === null || v === undefined) return 0;
  const s = String(v).trim().replace(/[^\d,.-]/g, '');
  if (!s) return 0;
  const limpio = s.replace(/\./g, '').replace(',', '.');   // 1.234,5 → 1234.5
  const n = parseFloat(limpio);
  return isFinite(n) ? n : 0;
}

const h = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
  { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

const uid = () => Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-3);
const hoyISO = () => new Date().toISOString().slice(0, 10);
const sum = (arr, fn) => (arr || []).reduce((a, x) => a + num(fn(x)), 0);

const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
const MESES_L = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

function fecha(iso, largo = false) {
  if (!iso) return '—';
  const [y, m, d] = String(iso).slice(0, 10).split('-');
  if (!y || !m || !d) return iso;
  return largo ? `${+d} de ${MESES_L[+m - 1]} de ${y}` : `${d}-${MESES[+m - 1]}-${y.slice(2)}`;
}

function desdeAhora(iso) {
  if (!iso) return '';
  const dias = diasDesde(iso);
  if (dias <= 0) return 'hoy';
  if (dias === 1) return 'ayer';
  if (dias < 30) return `hace ${dias} días`;
  const m = Math.floor(dias / 30);
  return m === 1 ? 'hace un mes' : m < 12 ? `hace ${m} meses` : 'hace más de un año';
}

function diasDesde(iso) {
  if (!iso) return Infinity;
  return Math.floor((Date.now() - new Date(iso).getTime()) / 86400000);
}

/** Suma meses a una fecha ISO y devuelve ISO. */
function sumarMeses(iso, meses) {
  const d = new Date(iso || hoyISO());
  d.setMonth(d.getMonth() + num(meses));
  return d.toISOString().slice(0, 10);
}

const normPatente = (p) => String(p || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
const normTexto = (t) => String(t || '').trim().toLowerCase().replace(/\s+/g, ' ');
const normTel = (t) => String(t || '').replace(/\D/g, '').slice(-9);

function patente(p) {
  const s = normPatente(p);
  return s.length === 6 ? `${s.slice(0, 2)}·${s.slice(2, 4)}·${s.slice(4)}` : (s || '—');
}

/** Teléfono chileno listo para wa.me */
function telWa(t) {
  const d = String(t || '').replace(/\D/g, '');
  if (!d) return '';
  return d.length === 9 ? '56' + d : d.length === 8 ? '569' + d : d;
}

function icon(id, cls = 'i') {
  return `<svg class="${cls}" aria-hidden="true"><use href="#ic-${id}"></use></svg>`;
}

function iniciales(nombre) {
  const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
  if (!partes.length) return '—';
  return (partes[0][0] + (partes[1]?.[0] || '')).toUpperCase();
}

function setPath(obj, path, value) {
  const partes = path.split('.');
  let cur = obj;
  while (partes.length > 1) {
    const k = partes.shift();
    if (typeof cur[k] !== 'object' || cur[k] === null) cur[k] = {};
    cur = cur[k];
  }
  cur[partes[0]] = value;
}

function b64urlEncode(str) {
  const bytes = new TextEncoder().encode(str);
  let bin = '';
  bytes.forEach((b) => { bin += String.fromCharCode(b); });
  return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function b64urlDecode(str) {
  const s = str.replace(/-/g, '+').replace(/_/g, '/');
  const bin = atob(s + '='.repeat((4 - (s.length % 4)) % 4));
  return new TextDecoder().decode(Uint8Array.from(bin, (c) => c.charCodeAt(0)));
}

const sinMovimiento = () => matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Cuenta hasta el valor final; se usa al montar una vista, no al tipear. */
function animarNumero(el, hasta, formato = money) {
  if (!el) return;
  if (sinMovimiento()) { el.textContent = formato(hasta); return; }
  const desde = 0;
  const dur = 520;
  const t0 = performance.now();
  const paso = (t) => {
    const p = Math.min(1, (t - t0) / dur);
    const e = 1 - Math.pow(1 - p, 3);
    el.textContent = formato(desde + (hasta - desde) * e);
    if (p < 1) requestAnimationFrame(paso);
  };
  requestAnimationFrame(paso);
}

/* ------------------------------------------------------------------ datos */
const LS_KEY = 'vittorio.taller.v1';
const ESTADOS = {
  borrador:  { label: 'Borrador',  cls: 'badge-borrador' },
  enviada:   { label: 'Enviada',   cls: 'badge-enviada' },
  aprobada:  { label: 'Aprobada',  cls: 'badge-aprobada' },
  taller:    { label: 'En taller', cls: 'badge-taller' },
  entregada: { label: 'Entregada', cls: 'badge-entregada' },
  anulada:   { label: 'Anulada',   cls: 'badge-anulada' }
};
const ESTADOS_VIVOS = ['borrador', 'enviada', 'aprobada', 'taller'];

let DB = null;

function dbNueva() {
  const db = {
    version: 2,
    seq: 2,
    ajustes: JSON.parse(JSON.stringify(AJUSTES_DEFAULT)),
    servicios: CATALOGO_SERVICIOS.map((s) => ({ id: uid(), ...s })),
    repuestos: CATALOGO_REPUESTOS.map((r) => ({ id: uid(), ...r })),
    clientes: [],
    vehiculos: [],
    cotizaciones: [cotizacionEjemplo()]
  };
  // La cotización de ejemplo deja creada su ficha de cliente y de vehículo.
  db.cotizaciones.forEach((c) => vincularMaestros(c, db));
  return db;
}

/** Deja cualquier respaldo viejo con la forma que espera la versión actual. */
function migrar(db) {
  db.ajustes = Object.assign({}, AJUSTES_DEFAULT, db.ajustes || {});
  db.servicios = db.servicios || [];
  db.repuestos = db.repuestos || [];
  db.clientes = db.clientes || [];
  db.vehiculos = db.vehiculos || [];
  db.cotizaciones = (db.cotizaciones || []).map((c) => ({
    fotos: [], historial: [], firma: null, mostrarUnitarios: true,
    ...c,
    cliente: c.cliente || { nombre: '', telefono: '', email: '', rut: '' },
    vehiculo: c.vehiculo || { marcaModelo: '', patente: '', anio: '', km: '' }
  }));
  db.seq = db.seq || db.cotizaciones.length + 1;

  // Fichas de cliente y vehículo a partir de las cotizaciones que ya existían.
  if (db.version !== 2) {
    db.cotizaciones.forEach((c) => vincularMaestros(c, db));
    db.version = 2;
  }
  db.cotizaciones.forEach((c) => {
    if (!c.historial || !c.historial.length) {
      c.historial = [{ fecha: c.creada || new Date().toISOString(), texto: 'Cotización creada' }];
    }
  });
  return db;
}

function cargar() {
  try {
    const raw = localStorage.getItem(LS_KEY);
    return raw ? migrar(JSON.parse(raw)) : dbNueva();
  } catch (e) {
    console.warn('No se pudo leer el respaldo local, se parte de cero.', e);
    return dbNueva();
  }
}

let guardando = null;
function guardar(inmediato = false) {
  marcarGuardado('saving');
  clearTimeout(guardando);
  const escribir = () => {
    try {
      localStorage.setItem(LS_KEY, JSON.stringify(DB));
      marcarGuardado('saved');
    } catch (e) {
      marcarGuardado('error');
      toast('No se pudo guardar: el navegador está lleno. Descargá un respaldo y borrá fotos.', 'err');
    }
  };
  if (inmediato) escribir(); else guardando = setTimeout(escribir, 350);
}

function marcarGuardado(estado) {
  const el = $('#saveState');
  if (!el) return;
  el.dataset.state = estado;
  el.querySelector('span').textContent =
    estado === 'saving' ? 'Guardando…' : estado === 'error' ? 'Sin guardar' : 'Guardado';
}

function pesoDatos() {
  try { return new Blob([JSON.stringify(DB)]).size; } catch (e) { return 0; }
}

/* --------------------------------------------- clientes y vehículos ------ */
/** Crea o actualiza la ficha del cliente y la del vehículo de una cotización. */
function vincularMaestros(c, db = DB) {
  const nom = normTexto(c.cliente?.nombre);
  const tel = normTel(c.cliente?.telefono);
  if (nom || tel) {
    let cli = db.clientes.find((x) => (tel && normTel(x.telefono) === tel) || (nom && normTexto(x.nombre) === nom));
    if (!cli) {
      cli = { id: uid(), nombre: c.cliente.nombre || '', telefono: c.cliente.telefono || '', email: '', rut: '', notas: '', creado: new Date().toISOString() };
      db.clientes.push(cli);
    }
    ['nombre', 'telefono', 'email', 'rut'].forEach((k) => { if (c.cliente[k]) cli[k] = c.cliente[k]; });
    c.clienteId = cli.id;
  }

  const pat = normPatente(c.vehiculo?.patente);
  if (pat) {
    let veh = db.vehiculos.find((x) => normPatente(x.patente) === pat);
    if (!veh) {
      veh = { id: uid(), patente: pat, marcaModelo: '', anio: '', color: '', vin: '', km: '', notas: '', clienteId: c.clienteId || '', creado: new Date().toISOString() };
      db.vehiculos.push(veh);
    }
    ['marcaModelo', 'anio', 'color', 'vin'].forEach((k) => { if (c.vehiculo[k]) veh[k] = c.vehiculo[k]; });
    if (num(c.vehiculo.km) >= num(veh.km)) veh.km = c.vehiculo.km;
    if (c.clienteId) veh.clienteId = c.clienteId;
    c.vehiculoId = veh.id;
  }
}

const getCliente  = (id) => DB.clientes.find((x) => x.id === id);
const getVehiculo = (id) => DB.vehiculos.find((x) => x.id === id);
const vehiculoPorPatente = (p) => DB.vehiculos.find((x) => normPatente(x.patente) === normPatente(p));

const cotsDeCliente  = (id) => DB.cotizaciones.filter((c) => c.clienteId === id).sort(porFecha);
const cotsDeVehiculo = (id) => DB.cotizaciones.filter((c) => c.vehiculoId === id).sort(porFecha);
const vehiculosDeCliente = (id) => DB.vehiculos.filter((v) => v.clienteId === id);

const porFecha = (a, b) => String(b.fechaIngreso || b.creada || '').localeCompare(String(a.fechaIngreso || a.creada || ''));

/** Números de un cliente o de un vehículo: cuánto, cuándo y qué debe. */
function resumenDe(cots) {
  const validas = cots.filter((c) => c.estado !== 'anulada');
  const facturado = validas.filter((c) => ['aprobada', 'taller', 'entregada'].includes(c.estado)).reduce((a, c) => a + calc(c).total, 0);
  const saldo = validas.filter((c) => ['aprobada', 'taller', 'entregada'].includes(c.estado)).reduce((a, c) => a + Math.max(0, calc(c).saldo), 0);
  const cotizado = validas.reduce((a, c) => a + calc(c).total, 0);
  const ultima = validas[0];
  return { n: validas.length, facturado, cotizado, saldo, ultima, abiertas: validas.filter((c) => ESTADOS_VIVOS.includes(c.estado)).length };
}

/** Próxima mantención sugerida a partir de la última atención del vehículo. */
function proximaMantencion(veh) {
  const cots = cotsDeVehiculo(veh.id).filter((c) => c.estado !== 'anulada' && c.estado !== 'borrador');
  const ultima = cots[0];
  if (!ultima) return null;
  const base = ultima.fechaIngreso || (ultima.creada || '').slice(0, 10);
  const fechaProx = sumarMeses(base, DB.ajustes.mantencionMeses);
  const kmProx = num(ultima.vehiculo?.km) ? num(ultima.vehiculo.km) + num(DB.ajustes.mantencionKm) : 0;
  return { fecha: fechaProx, km: kmProx, vencida: new Date(fechaProx) <= new Date(), ultima };
}

/** Cotizaciones enviadas que llevan demasiados días sin respuesta. */
function necesitanSeguimiento() {
  const limite = num(DB.ajustes.seguimientoDias) || 5;
  return DB.cotizaciones
    .filter((c) => c.estado === 'enviada' && diasDesde(c.actualizada) >= limite)
    .sort((a, b) => diasDesde(b.actualizada) - diasDesde(a.actualizada));
}

/* ------------------------------------------------------------- cotización */
function cotNueva(base = {}) {
  const aj = DB.ajustes;
  const anio = new Date().getFullYear();
  const folio = `${aj.folioPrefijo || 'COT'}-${anio}-${String(DB.seq++).padStart(4, '0')}`;
  return {
    id: uid(),
    folio,
    estado: 'borrador',
    creada: new Date().toISOString(),
    actualizada: new Date().toISOString(),
    clienteId: base.clienteId || '',
    vehiculoId: base.vehiculoId || '',
    cliente: Object.assign({ nombre: '', telefono: '', email: '', rut: '' }, base.cliente),
    vehiculo: Object.assign({ marcaModelo: '', patente: '', anio: '', km: '', color: '', vin: '' }, base.vehiculo),
    fechaIngreso: hoyISO(),
    fechaEntrega: '',
    motivo: '',
    trabajos: [{ id: uid(), desc: '', obs: '', realizado: false, mo: 0 }],
    repuestos: [],
    abonos: [],
    fotos: [],
    firma: null,
    gastosPct: num(aj.gastosPct),
    ivaPct: num(aj.ivaPct),
    aplicaIva: !!aj.aplicaIvaDefault,
    descuento: 0,
    mostrarUnitarios: true,
    notas: '',
    historial: [{ fecha: new Date().toISOString(), texto: 'Cotización creada' }]
  };
}

const getCot = (id) => DB.cotizaciones.find((c) => c.id === id);

/** Réplica de la planilla: MO + (repuestos + gastos) − descuento + IVA − abonos. */
function calc(c) {
  const mo = sum(c.trabajos, (t) => t.mo);
  const repBase = sum(c.repuestos, (r) => num(r.cant) * num(r.unit));
  const gastos = Math.round((repBase * num(c.gastosPct)) / 100);
  const rep = repBase + gastos;
  const bruto = mo + rep;
  const descuento = Math.min(num(c.descuento), bruto);
  const neto = bruto - descuento;
  const ivaPct = c.aplicaIva ? num(c.ivaPct) : 0;
  const iva = Math.round((neto * ivaPct) / 100);
  const total = neto + iva;
  const abonado = sum(c.abonos, (a) => a.monto);
  return { mo, repBase, gastos, rep, bruto, descuento, neto, ivaPct, iva, total, abonado, saldo: total - abonado };
}

/** Marca la cotización como tocada, sincroniza fichas y guarda. */
function tocar(c, evento) {
  c.actualizada = new Date().toISOString();
  vincularMaestros(c);
  refrescarDatalists();
  if (evento) registrar(c, evento);
  guardar();
}

function registrar(c, texto) {
  c.historial = c.historial || [];
  const ultimo = c.historial[c.historial.length - 1];
  if (ultimo && ultimo.texto === texto && diasDesde(ultimo.fecha) === 0) return;
  c.historial.push({ fecha: new Date().toISOString(), texto });
}

/* ---------------------------------------------------------------- toasts  */
function toast(msg, tipo = 'ok', accion) {
  const cont = $('#toasts');
  const el = document.createElement('div');
  el.className = 'toast ' + (tipo === 'err' ? 'is-err' : 'is-ok');
  el.innerHTML = `${icon(tipo === 'err' ? 'alert' : 'check', 'i i-sm')}<span>${h(msg)}</span>`;
  if (accion) {
    const b = document.createElement('button');
    b.className = 'toast-act';
    b.textContent = accion.texto;
    b.addEventListener('click', () => { accion.run(); cerrar(); });
    el.appendChild(b);
  }
  cont.appendChild(el);
  const cerrar = () => {
    el.style.transition = 'opacity .25s, transform .25s';
    el.style.opacity = '0';
    el.style.transform = 'translateY(6px)';
    setTimeout(() => el.remove(), 260);
  };
  setTimeout(cerrar, accion ? 6000 : 2600);
}

/* ---------------------------------------------------------------- modales */
function abrirModal(html, opciones = {}) {
  cerrarModal();
  const ov = document.createElement('div');
  ov.className = 'overlay';
  ov.id = 'overlay';
  ov.innerHTML = html;
  ov.addEventListener('mousedown', (e) => { if (e.target === ov) cerrarModal(); });
  document.body.appendChild(ov);
  $('[data-autofocus]', ov)?.focus();
  if (opciones.onMount) opciones.onMount(ov);
  return ov;
}

function cerrarModal() { $('#overlay')?.remove(); }

function confirmar(titulo, texto, textoBoton = 'Confirmar') {
  return new Promise((resolve) => {
    const ov = abrirModal(`
      <div class="modal" style="max-width:440px" role="dialog" aria-modal="true">
        <div class="modal-head"><div><h3>${h(titulo)}</h3></div></div>
        <div class="modal-body"><p class="muted">${h(texto)}</p></div>
        <div class="modal-foot">
          <button class="btn" data-no>Cancelar</button>
          <button class="btn btn-danger" data-yes data-autofocus>${h(textoBoton)}</button>
        </div>
      </div>`);
    ov.addEventListener('click', (e) => {
      if (e.target.closest('[data-yes]')) { cerrarModal(); resolve(true); }
      if (e.target.closest('[data-no]')) { cerrarModal(); resolve(false); }
    });
  });
}

/* ----------------------------------------------------------------- router */
const rutas = [];
function ruta(re, fn) { rutas.push([re, fn]); }
function navegar(hash) { location.hash = hash; }

function pintarRuta() {
  const hash = location.hash.replace(/^#/, '') || '/';
  for (const [re, fn] of rutas) {
    const m = hash.match(re);
    if (m) {
      document.body.classList.remove('nav-open');
      if (!hash.startsWith('/ver/')) document.body.classList.remove('modo-publico');
      fn(...m.slice(1));
      window.scrollTo({ top: 0 });
      marcarNav(hash);
      return;
    }
  }
  navegar('#/');
}

function render() {
  if (document.startViewTransition && !sinMovimiento()) document.startViewTransition(() => pintarRuta());
  else pintarRuta();
}

function marcarNav(hash) {
  const activo = (href) => {
    if (href === '/') return hash === '/';
    if (href === '/cotizaciones') return /^\/(cotizaciones|cot\/|doc\/)/.test(hash);
    if (href === '/clientes') return hash.startsWith('/cliente');
    if (href === '/vehiculos') return hash.startsWith('/vehiculo');
    return hash.startsWith(href);
  };
  $$('.nav-item[href], .tab[href]').forEach((a) => {
    a.classList.toggle('is-active', activo(a.getAttribute('href').replace(/^#/, '')));
  });
  const pintaContador = (sel, n) => {
    const b = $(sel);
    if (!b) return;
    b.textContent = n || '';
    b.classList.toggle('hidden', !n);
  };
  pintaContador('#navCotsCount', DB.cotizaciones.filter((c) => ESTADOS_VIVOS.includes(c.estado)).length);
  pintaContador('#navSeguimiento', necesitanSeguimiento().length);
}

function setVista(html) { $('#view').innerHTML = html; }

function topbar({ titulo, sub, atras, acciones = '' }) {
  return `
    <header class="topbar">
      <button class="btn btn-ghost btn-icon nav-toggle" data-nav-toggle aria-label="Abrir menú">${icon('menu')}</button>
      ${atras ? `<a class="btn btn-ghost btn-icon" href="${atras}" aria-label="Volver">${icon('back')}</a>` : ''}
      <div style="min-width:0">
        <h1>${titulo}</h1>
        ${sub ? `<div class="sub">${sub}</div>` : ''}
      </div>
      <div class="topbar-actions">${acciones}</div>
    </header>`;
}

function vacio(ico, titulo, texto, accion = '') {
  return `
    <div class="empty">
      <div class="e-icon">${icon(ico, 'i i-lg')}</div>
      <h3>${h(titulo)}</h3>
      <p>${h(texto)}</p>
      ${accion}
    </div>`;
}

function vacioLinea(txt) {
  return `<div style="padding:22px 16px;text-align:center;color:var(--ink-3);font-size:13.5px">${h(txt)}</div>`;
}

const badgeEstado = (e) => `<span class="badge ${(ESTADOS[e] || ESTADOS.borrador).cls}">${(ESTADOS[e] || ESTADOS.borrador).label}</span>`;

/* ============================================================== COMPARTIR */
function enlaceCompartir(c) {
  const aj = DB.ajustes;
  // Las fotos no viajan en el enlace: harían una URL imposible de mandar.
  const liviana = { ...c, fotos: [] };
  const payload = b64urlEncode(JSON.stringify({
    c: liviana,
    a: {
      taller: aj.taller, lema: aj.lema, rut: aj.rut, direccion: aj.direccion, comuna: aj.comuna,
      telefono: aj.telefono, email: aj.email, web: aj.web, garantia: aj.garantia,
      condiciones: aj.condiciones, validezDias: aj.validezDias
    }
  }));
  return `${location.href.split('#')[0]}#/ver/${payload}`;
}

async function copiar(texto) {
  try {
    await navigator.clipboard.writeText(texto);
    return true;
  } catch (e) {
    const ta = document.createElement('textarea');
    ta.value = texto;
    ta.style.cssText = 'position:fixed;opacity:0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
    ta.remove();
    return ok;
  }
}

function textoResumen(c) {
  const t = calc(c);
  const veh = [c.vehiculo?.marcaModelo, c.vehiculo?.patente ? patente(c.vehiculo.patente) : ''].filter(Boolean).join(' · ');
  return `${DB.ajustes.taller}\n${c.folio}${veh ? ` · ${veh}` : ''}\n` +
    `Mano de obra: ${money(t.mo)}\nRepuestos: ${money(t.rep)}\n` +
    (t.iva ? `IVA: ${money(t.iva)}\n` : '') +
    `Total: ${money(t.total)}` + (t.abonado ? `\nA pagar: ${money(t.saldo)}` : '');
}

function abrirCompartir(c) {
  const url = enlaceCompartir(c);
  const ov = abrirModal(`
    <div class="modal" style="max-width:560px" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div>${icon('share')}</div>
        <div><h3>Compartir ${h(c.folio)}</h3><div class="sub">Enlace de sólo lectura, no hay que instalar nada</div></div>
      </div>
      <div class="modal-body grid">
        <div class="field">
          <label for="lnk">Enlace</label>
          <textarea class="input mono" id="lnk" rows="3" readonly style="font-size:11.5px">${h(url)}</textarea>
          <div class="hint">El documento viaja dentro del enlace: quien lo abra ve la cotización tal como está hoy.${(c.fotos || []).length ? ' Las fotos del vehículo no se incluyen.' : ''}</div>
        </div>
        <div class="toolbar">
          <button class="btn btn-primary" data-copiar>${icon('copy', 'i i-sm')} Copiar enlace</button>
          <button class="btn" data-wa>${icon('chat', 'i i-sm')} WhatsApp</button>
          <a class="btn" href="mailto:${encodeURIComponent(c.cliente?.email || '')}?subject=${encodeURIComponent(`${DB.ajustes.taller} · ${c.folio}`)}&body=${encodeURIComponent(textoResumen(c) + '\n\n' + url)}">${icon('mail', 'i i-sm')} Email</a>
          <a class="btn" href="#/doc/${c.id}" data-cerrar>${icon('print', 'i i-sm')} Imprimir</a>
        </div>
      </div>
      <div class="modal-foot"><button class="btn" data-cerrar>Cerrar</button></div>
    </div>`);

  ov.addEventListener('click', async (e) => {
    if (e.target.closest('[data-copiar]')) {
      const ok = await copiar(url);
      toast(ok ? 'Enlace copiado' : 'Copialo a mano desde el cuadro', ok ? 'ok' : 'err');
      if (ok && c.estado === 'borrador') { c.estado = 'enviada'; tocar(c, 'Enviada al cliente'); }
    }
    if (e.target.closest('[data-wa]')) abrirWhatsApp(c, url);
    if (e.target.closest('[data-cerrar]')) cerrarModal();
  });
}

function abrirWhatsApp(c, url) {
  const texto = `${textoResumen(c)}\n\nDetalle completo: ${url || enlaceCompartir(c)}`;
  const tel = telWa(c.cliente?.telefono);
  window.open(`https://wa.me/${tel}?text=${encodeURIComponent(texto)}`, '_blank', 'noopener');
  if (c.estado === 'borrador') { c.estado = 'enviada'; tocar(c, 'Enviada por WhatsApp'); }
}

/** Mensaje corto de seguimiento para una cotización sin respuesta. */
function seguimientoWhatsApp(c) {
  const veh = c.vehiculo?.marcaModelo || 'su vehículo';
  const texto = `Hola${c.cliente?.nombre ? ' ' + c.cliente.nombre.split(' ')[0] : ''}, le escribimos de ${DB.ajustes.taller}. ` +
    `¿Alcanzó a revisar la cotización ${c.folio} de ${veh}? Quedamos atentos.\n\n${enlaceCompartir(c)}`;
  window.open(`https://wa.me/${telWa(c.cliente?.telefono)}?text=${encodeURIComponent(texto)}`, '_blank', 'noopener');
  registrar(c, 'Seguimiento enviado');
  guardar();
}

/* ================================================================ acciones */
function nuevaCotizacion(base) {
  const c = cotNueva(base);
  DB.cotizaciones.unshift(c);
  vincularMaestros(c);
  guardar(true);
  navegar(`#/cot/${c.id}`);
  toast(`${c.folio} creada`);
}

function nuevaParaVehiculo(vehId) {
  const v = getVehiculo(vehId);
  if (!v) return;
  const cli = getCliente(v.clienteId);
  nuevaCotizacion({
    vehiculoId: v.id,
    clienteId: v.clienteId,
    vehiculo: { marcaModelo: v.marcaModelo, patente: v.patente, anio: v.anio, km: v.km, color: v.color, vin: v.vin },
    cliente: cli ? { nombre: cli.nombre, telefono: cli.telefono, email: cli.email, rut: cli.rut } : {}
  });
}

function nuevaParaCliente(cliId) {
  const cli = getCliente(cliId);
  if (!cli) return;
  const vs = vehiculosDeCliente(cliId);
  const v = vs.length === 1 ? vs[0] : null;
  nuevaCotizacion({
    clienteId: cli.id,
    cliente: { nombre: cli.nombre, telefono: cli.telefono, email: cli.email, rut: cli.rut },
    vehiculoId: v?.id || '',
    vehiculo: v ? { marcaModelo: v.marcaModelo, patente: v.patente, anio: v.anio, km: v.km } : {}
  });
}

function duplicar(id) {
  const orig = getCot(id);
  if (!orig) return;
  const copia = JSON.parse(JSON.stringify(orig));
  copia.id = uid();
  copia.folio = `${DB.ajustes.folioPrefijo || 'COT'}-${new Date().getFullYear()}-${String(DB.seq++).padStart(4, '0')}`;
  copia.estado = 'borrador';
  copia.creada = copia.actualizada = new Date().toISOString();
  copia.fechaIngreso = hoyISO();
  copia.fechaEntrega = '';
  copia.abonos = [];
  copia.fotos = [];
  copia.firma = null;
  copia.historial = [{ fecha: new Date().toISOString(), texto: `Duplicada de ${orig.folio}` }];
  copia.trabajos.forEach((t) => { t.id = uid(); t.realizado = false; });
  copia.repuestos.forEach((r) => { r.id = uid(); });
  DB.cotizaciones.unshift(copia);
  guardar(true);
  navegar(`#/cot/${copia.id}`);
  toast(`Duplicada como ${copia.folio}`);
}

async function eliminar(id) {
  const c = getCot(id);
  if (!c) return;
  const ok = await confirmar('Eliminar cotización', `Se borra ${c.folio}. Podés deshacerlo apenas se elimine.`, 'Eliminar');
  if (!ok) return;
  const indice = DB.cotizaciones.indexOf(c);
  DB.cotizaciones.splice(indice, 1);
  guardar(true);
  navegar('#/cotizaciones');
  toast('Cotización eliminada', 'ok', {
    texto: 'Deshacer',
    run: () => { DB.cotizaciones.splice(indice, 0, c); guardar(true); navegar(`#/cot/${c.id}`); }
  });
}

function cambiarEstado(c, estado) {
  c.estado = estado;
  tocar(c, `Estado: ${ESTADOS[estado].label}`);
  toast(`${c.folio} · ${ESTADOS[estado].label}`);
  render();
}

/* --------------------------------------------------------------- respaldo */
function descargar(nombre, contenido, tipo) {
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([contenido], { type: tipo }));
  a.download = nombre;
  a.click();
  URL.revokeObjectURL(a.href);
}

function exportarJSON() {
  descargar(`vittorio-respaldo-${hoyISO()}.json`, JSON.stringify(DB, null, 2), 'application/json');
  toast('Respaldo descargado');
}

function exportarCSV() {
  const filas = [['Folio', 'Estado', 'Ingreso', 'Entrega', 'Cliente', 'Teléfono', 'Vehículo', 'Patente', 'Km', 'Mano de obra', 'Repuestos', 'Neto', 'IVA', 'Total', 'Abonado', 'Saldo']];
  DB.cotizaciones.forEach((c) => {
    const t = calc(c);
    filas.push([c.folio, ESTADOS[c.estado]?.label || c.estado, c.fechaIngreso, c.fechaEntrega,
      c.cliente?.nombre, c.cliente?.telefono, c.vehiculo?.marcaModelo, c.vehiculo?.patente, c.vehiculo?.km,
      t.mo, t.rep, t.neto, t.iva, t.total, t.abonado, t.saldo]);
  });
  const csv = filas.map((f) => f.map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(';')).join('\n');
  descargar(`vittorio-cotizaciones-${hoyISO()}.csv`, '﻿' + csv, 'text/csv;charset=utf-8');
  toast('CSV exportado');
}

function importarJSON(file) {
  const fr = new FileReader();
  fr.onload = async () => {
    try {
      const datos = JSON.parse(fr.result);
      if (!datos || !Array.isArray(datos.cotizaciones)) throw new Error('formato');
      const ok = await confirmar('Restaurar respaldo', `Se reemplazan los datos actuales por ${datos.cotizaciones.length} cotizaciones del archivo.`, 'Restaurar');
      if (!ok) return;
      DB = migrar(datos);
      guardar(true);
      render();
      toast('Respaldo restaurado');
    } catch (e) {
      toast('El archivo no es un respaldo válido', 'err');
    }
  };
  fr.readAsText(file);
}

async function borrarTodo() {
  const ok = await confirmar('Borrar todo', 'Se eliminan cotizaciones, clientes, vehículos, catálogo y ajustes de este navegador.', 'Borrar todo');
  if (!ok) return;
  localStorage.removeItem(LS_KEY);
  DB = dbNueva();
  guardar(true);
  navegar('#/');
  render();
  toast('Datos borrados');
}

/* -------------------------------------------------------------------- tema */
function aplicarTema(t) {
  if (t === 'auto') document.documentElement.removeAttribute('data-theme');
  else document.documentElement.setAttribute('data-theme', t);
  localStorage.setItem('vittorio.tema', t);
  const btn = $('#temaBtn');
  if (btn) {
    btn.innerHTML = `${icon(t === 'dark' ? 'moon' : t === 'light' ? 'sun' : 'auto', 'i i-sm')}<span>${t === 'auto' ? 'Automático' : t === 'light' ? 'Claro' : 'Oscuro'}</span>`;
  }
}

function alternarTema() {
  const actual = localStorage.getItem('vittorio.tema') || 'auto';
  const siguiente = actual === 'auto' ? 'light' : actual === 'light' ? 'dark' : 'auto';
  aplicarTema(siguiente);
}

/* ------------------------------------------------------------- datalists  */
function refrescarDatalists() {
  const s = $('#dl-servicios');
  const r = $('#dl-repuestos');
  const cl = $('#dl-clientes');
  if (s) s.innerHTML = DB.servicios.map((x) => `<option value="${h(x.desc)}">`).join('');
  if (r) r.innerHTML = DB.repuestos.map((x) => `<option value="${h(x.desc)}">`).join('');
  if (cl) cl.innerHTML = DB.clientes.filter((x) => x.nombre).map((x) => `<option value="${h(x.nombre)}">`).join('');
}

/* ======================================================== paleta de comandos */
function abrirCmdK() {
  const acciones = [
    { t: 'Nueva cotización', s: 'Crear', ico: 'plus', run: () => nuevaCotizacion() },
    { t: 'Tablero', s: 'Ir a', ico: 'home', run: () => navegar('#/') },
    { t: 'Cotizaciones', s: 'Ir a', ico: 'file', run: () => navegar('#/cotizaciones') },
    { t: 'Clientes', s: 'Ir a', ico: 'user', run: () => navegar('#/clientes') },
    { t: 'Vehículos', s: 'Ir a', ico: 'car', run: () => navegar('#/vehiculos') },
    { t: 'Catálogo de precios', s: 'Ir a', ico: 'list', run: () => navegar('#/catalogo') },
    { t: 'Ajustes del taller', s: 'Ir a', ico: 'settings', run: () => navegar('#/ajustes') },
    { t: 'Cambiar tema claro / oscuro', s: 'Vista', ico: 'moon', run: () => alternarTema() },
    { t: 'Descargar respaldo', s: 'Datos', ico: 'download', run: () => exportarJSON() }
  ];

  let cursor = 0;
  const ov = abrirModal(`
    <div class="modal cmdk" role="dialog" aria-modal="true" aria-label="Buscador">
      <div class="picker-search">
        <input class="input" id="ck" placeholder="Buscá una patente, cliente, folio o una acción…" data-autofocus autocomplete="off">
      </div>
      <div class="modal-body flush"><div class="picker-list" id="cklist"></div></div>
      <div class="modal-foot cmdk-foot">
        <span><kbd>↑</kbd><kbd>↓</kbd> moverse</span><span><kbd>↵</kbd> abrir</span><span><kbd>esc</kbd> cerrar</span>
      </div>
    </div>`);

  const opciones = () => {
    const q = ($('#ck').value || '').toLowerCase().trim();
    const accs = acciones.filter((a) => (a.t + ' ' + a.s).toLowerCase().includes(q));
    if (!q) return accs;
    const cots = DB.cotizaciones
      .filter((c) => [c.folio, c.cliente?.nombre, c.vehiculo?.patente, c.vehiculo?.marcaModelo].join(' ').toLowerCase().includes(q))
      .slice(0, 5)
      .map((c) => ({ t: c.vehiculo?.marcaModelo || c.folio, s: `${c.folio} · ${patente(c.vehiculo?.patente)} · ${money(calc(c).total)}`, ico: 'file', run: () => navegar(`#/cot/${c.id}`) }));
    const clis = DB.clientes
      .filter((x) => (x.nombre + ' ' + x.telefono).toLowerCase().includes(q))
      .slice(0, 4)
      .map((x) => ({ t: x.nombre || 'Cliente', s: `Cliente · ${x.telefono || 'sin teléfono'}`, ico: 'user', run: () => navegar(`#/cliente/${x.id}`) }));
    const vehs = DB.vehiculos
      .filter((x) => (x.patente + ' ' + x.marcaModelo).toLowerCase().includes(q))
      .slice(0, 4)
      .map((x) => ({ t: x.marcaModelo || patente(x.patente), s: `Vehículo · ${patente(x.patente)}`, ico: 'car', run: () => navegar(`#/vehiculo/${x.id}`) }));
    return [...accs, ...clis, ...vehs, ...cots];
  };

  const pintar = () => {
    const list = opciones();
    cursor = Math.min(cursor, Math.max(0, list.length - 1));
    $('#cklist').innerHTML = list.length
      ? list.map((o, i) => `
          <button class="picker-item ${i === cursor ? 'is-cursor' : ''}" data-i="${i}">
            <div style="display:flex;align-items:center;gap:11px;min-width:0">
              <span style="color:var(--ink-3)">${icon(o.ico, 'i i-sm')}</span>
              <span style="min-width:0"><span class="p1">${h(o.t)}</span><span class="p2">${h(o.s)}</span></span>
            </div>
            <span class="faint" style="font-size:11px">↵</span>
          </button>`).join('')
      : `<div style="padding:26px;text-align:center;color:var(--ink-3)">Sin resultados</div>`;
  };

  pintar();
  const input = $('#ck');
  input.addEventListener('input', () => { cursor = 0; pintar(); });
  input.addEventListener('keydown', (e) => {
    const list = opciones();
    if (e.key === 'ArrowDown') { e.preventDefault(); cursor = Math.min(cursor + 1, list.length - 1); pintar(); }
    if (e.key === 'ArrowUp') { e.preventDefault(); cursor = Math.max(cursor - 1, 0); pintar(); }
    if (e.key === 'Enter') { e.preventDefault(); const o = list[cursor]; if (o) { cerrarModal(); o.run(); } }
  });
  ov.addEventListener('click', (e) => {
    const b = e.target.closest('[data-i]');
    if (b) { const o = opciones()[+b.dataset.i]; cerrarModal(); if (o) o.run(); }
  });
}

/* ============================================================ bienvenida == */
/** Primer uso: pide lo mínimo para que el documento salga con los datos del taller. */
function abrirBienvenida() {
  const a = DB.ajustes;
  const ov = abrirModal(`
    <div class="modal" style="max-width:560px" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div class="brand-mark" style="width:36px;height:36px;border-radius:11px">${icon('car', 'i')}</div>
        <div><h3>Bienvenido a tu taller digital</h3><div class="sub">Un minuto y queda listo para cotizar</div></div>
      </div>
      <div class="modal-body grid">
        <p class="muted" style="font-size:13.5px">Estos datos salen impresos en cada cotización que le mandes al cliente. Los podés cambiar cuando quieras en <b>Ajustes</b>.</p>
        <div class="grid grid-2">
          <div class="field"><label for="w1">Nombre del taller</label><input class="input" id="w1" data-aj="taller" value="${h(a.taller)}" data-autofocus></div>
          <div class="field"><label for="w2">Teléfono / WhatsApp</label><input class="input" id="w2" data-aj="telefono" value="${h(a.telefono)}" placeholder="+56 9 1234 5678"></div>
        </div>
        <div class="grid grid-2">
          <div class="field"><label for="w3">Dirección</label><input class="input" id="w3" data-aj="direccion" value="${h(a.direccion)}" placeholder="Av. Siempre Viva 742"></div>
          <div class="field"><label for="w4">RUT</label><input class="input mono" id="w4" data-aj="rut" value="${h(a.rut)}" placeholder="76.123.456-7"></div>
        </div>
        <label class="switch"><input type="checkbox" data-aj="aplicaIvaDefault" ${a.aplicaIvaDefault ? 'checked' : ''}><span class="track"></span><span>Cotizo con IVA por defecto</span></label>
      </div>
      <div class="modal-foot">
        <span class="faint" style="margin-right:auto;font-size:12px">Ya te dejamos cargado un catálogo de precios y una cotización de ejemplo.</span>
        <button class="btn btn-primary" data-cerrar-bienvenida>Empezar</button>
      </div>
    </div>`);
  ov.addEventListener('click', (e) => {
    if (e.target.closest('[data-cerrar-bienvenida]')) {
      DB.ajustes.configurado = true;
      guardar(true);
      cerrarModal();
      render();
      toast('Todo listo. Creá tu primera cotización con ⌘K');
    }
  });
}

/* ============================================================== eventos === */
function cotActual() {
  const m = location.hash.match(/^#\/cot\/(.+)$/);
  return m ? getCot(m[1]) : null;
}

document.addEventListener('click', (e) => {
  const t = e.target;

  if (t.closest('[data-nav-toggle]')) { document.body.classList.toggle('nav-open'); return; }
  if (t.closest('#temaBtn')) { alternarTema(); return; }
  if (t.closest('[data-cmdk]')) { abrirCmdK(); return; }
  if (t.closest('[data-imprimir]')) { window.print(); return; }

  const nueva = t.closest('[data-nueva]');
  if (nueva) { nuevaCotizacion(); return; }

  const nuevaVeh = t.closest('[data-nueva-veh]');
  if (nuevaVeh) { nuevaParaVehiculo(nuevaVeh.dataset.nuevaVeh); return; }

  const nuevaCli = t.closest('[data-nueva-cli]');
  if (nuevaCli) { nuevaParaCliente(nuevaCli.dataset.nuevaCli); return; }

  const abrir = t.closest('[data-abrir]');
  if (abrir) { navegar(`#/cot/${abrir.dataset.abrir}`); return; }

  const comp = t.closest('[data-compartir]');
  if (comp) { const c = getCot(comp.dataset.compartir); if (c) abrirCompartir(c); return; }

  const wa = t.closest('[data-whatsapp]');
  if (wa) { const c = getCot(wa.dataset.whatsapp); if (c) abrirWhatsApp(c); return; }

  const seg = t.closest('[data-seguimiento]');
  if (seg) { const c = getCot(seg.dataset.seguimiento); if (c) seguimientoWhatsApp(c); return; }

  const est = t.closest('[data-estado]');
  if (est) { const c = getCot(est.dataset.cot); if (c) cambiarEstado(c, est.dataset.estado); return; }

  const dup = t.closest('[data-duplicar]');
  if (dup) { duplicar(dup.dataset.duplicar); return; }

  const del = t.closest('[data-eliminar]');
  if (del) { eliminar(del.dataset.eliminar); return; }

  if (t.closest('[data-export]')) { exportarJSON(); return; }
  if (t.closest('[data-csv]')) { exportarCSV(); return; }
  if (t.closest('[data-import]')) { $('#fileImport').click(); return; }
  if (t.closest('[data-reset]')) { borrarTodo(); return; }
});

/* Toda la escritura pasa por acá: primero el editor, después el resto. */
document.addEventListener('input', (e) => {
  const el = e.target;
  if (el.id === 'fileImport' || el.id === 'pq' || el.id === 'ck') return;
  if (typeof manejarInputEditor === 'function' && manejarInputEditor(el)) return;
  if (typeof manejarInputVista === 'function') manejarInputVista(el);
});

document.addEventListener('change', (e) => {
  if (e.target.id === 'fileImport' && e.target.files?.[0]) {
    importarJSON(e.target.files[0]);
    e.target.value = '';
  }
});

/* Los campos de dinero se muestran con separador de miles al salir. */
document.addEventListener('focusout', (e) => {
  const el = e.target;
  if (el.dataset && el.dataset.money !== undefined) el.value = campoMonto(num(el.value));
}, true);

document.addEventListener('keydown', (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); abrirCmdK(); return; }
  if (e.key === 'Escape') { cerrarModal(); document.body.classList.remove('nav-open'); }
});

window.addEventListener('hashchange', render);

/* ---------------------------------------------------------------- arranque */
function iniciar() {
  DB = cargar();
  guardar(true);
  aplicarTema(localStorage.getItem('vittorio.tema') || 'auto');
  refrescarDatalists();
  render();
  if (!DB.ajustes.configurado) setTimeout(abrirBienvenida, 400);

  if ('serviceWorker' in navigator && location.protocol.startsWith('http')) {
    navigator.serviceWorker.register('sw.js').catch(() => { /* sin modo offline, no pasa nada */ });
  }
}

document.addEventListener('DOMContentLoaded', iniciar);
