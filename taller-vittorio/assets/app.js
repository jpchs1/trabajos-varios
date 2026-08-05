/* ==========================================================================
   Taller Automotriz Vittorio — sistema de cotización
   App de una sola página, sin build ni dependencias. Los datos viven en
   localStorage del navegador; se pueden exportar/importar como respaldo.
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

function num(v) {
  if (typeof v === 'number') return isFinite(v) ? v : 0;
  if (v === null || v === undefined) return 0;
  const s = String(v).trim().replace(/[^\d,.-]/g, '');
  if (!s) return 0;
  // Formato chileno: 1.234,5  →  1234.5
  const limpio = s.replace(/\./g, '').replace(',', '.');
  const n = parseFloat(limpio);
  return isFinite(n) ? n : 0;
}

const h = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
  { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
));

const uid = () => Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-3);
const hoyISO = () => new Date().toISOString().slice(0, 10);
const sum = (arr, fn) => (arr || []).reduce((a, x) => a + num(fn(x)), 0);

function fecha(iso, largo = false) {
  if (!iso) return '—';
  const [y, m, d] = String(iso).slice(0, 10).split('-');
  if (!y || !m || !d) return iso;
  const meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
  const mesL = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
  return largo
    ? `${+d} de ${mesL[+m - 1]} de ${y}`
    : `${d}-${meses[+m - 1]}-${y.slice(2)}`;
}

function desdeAhora(iso) {
  if (!iso) return '';
  const dias = Math.floor((Date.now() - new Date(iso).getTime()) / 86400000);
  if (dias <= 0) return 'hoy';
  if (dias === 1) return 'ayer';
  if (dias < 30) return `hace ${dias} días`;
  const m = Math.floor(dias / 30);
  return m === 1 ? 'hace un mes' : `hace ${m} meses`;
}

function patente(p) {
  const s = String(p || '').toUpperCase().replace(/[^A-Z0-9]/g, '');
  if (s.length === 6) return `${s.slice(0, 2)}·${s.slice(2, 4)}·${s.slice(4)}`;
  return s || '—';
}

function icon(id, cls = 'i') {
  return `<svg class="${cls}" aria-hidden="true"><use href="#ic-${id}"></use></svg>`;
}

function debounce(fn, ms) {
  let t;
  return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); };
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

function getPath(obj, path) {
  return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
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

let DB = null;

function dbNueva() {
  return {
    version: 1,
    seq: 2,
    ajustes: JSON.parse(JSON.stringify(AJUSTES_DEFAULT)),
    servicios: CATALOGO_SERVICIOS.map((s) => ({ id: uid(), ...s })),
    repuestos: CATALOGO_REPUESTOS.map((r) => ({ id: uid(), ...r })),
    cotizaciones: [cotizacionEjemplo()]
  };
}

function cargar() {
  try {
    const raw = localStorage.getItem(LS_KEY);
    if (!raw) return dbNueva();
    const db = JSON.parse(raw);
    db.ajustes = Object.assign({}, AJUSTES_DEFAULT, db.ajustes || {});
    db.servicios = db.servicios || [];
    db.repuestos = db.repuestos || [];
    db.cotizaciones = db.cotizaciones || [];
    db.seq = db.seq || db.cotizaciones.length + 1;
    return db;
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
      toast('No se pudo guardar: almacenamiento lleno', 'err');
    }
  };
  if (inmediato) escribir();
  else guardando = setTimeout(escribir, 350);
}

function marcarGuardado(estado) {
  const el = $('#saveState');
  if (!el) return;
  el.dataset.state = estado;
  el.querySelector('span').textContent =
    estado === 'saving' ? 'Guardando…' : estado === 'error' ? 'Error al guardar' : 'Guardado';
}

/* ------------------------------------------------------------- cotización */
function cotNueva() {
  const aj = DB.ajustes;
  const anio = new Date().getFullYear();
  const folio = `${aj.folioPrefijo || 'COT'}-${anio}-${String(DB.seq++).padStart(4, '0')}`;
  return {
    id: uid(),
    folio,
    estado: 'borrador',
    creada: new Date().toISOString(),
    actualizada: new Date().toISOString(),
    cliente: { nombre: '', telefono: '', email: '', rut: '' },
    vehiculo: { marcaModelo: '', patente: '', anio: '', km: '', color: '', vin: '' },
    fechaIngreso: hoyISO(),
    fechaEntrega: '',
    motivo: '',
    trabajos: [{ id: uid(), desc: '', obs: '', realizado: false, mo: 0 }],
    repuestos: [],
    abonos: [],
    gastosPct: num(aj.gastosPct),
    ivaPct: num(aj.ivaPct),
    aplicaIva: !!aj.aplicaIvaDefault,
    descuento: 0,
    notas: ''
  };
}

function getCot(id) {
  return DB.cotizaciones.find((c) => c.id === id);
}

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

/* ---------------------------------------------------------------- toasts  */
function toast(msg, tipo = 'ok') {
  const cont = $('#toasts');
  const el = document.createElement('div');
  el.className = 'toast ' + (tipo === 'err' ? 'is-err' : 'is-ok');
  el.innerHTML = `${icon(tipo === 'err' ? 'alert' : 'check', 'i i-sm')}<span>${h(msg)}</span>`;
  cont.appendChild(el);
  setTimeout(() => {
    el.style.transition = 'opacity .25s, transform .25s';
    el.style.opacity = '0';
    el.style.transform = 'translateY(6px)';
    setTimeout(() => el.remove(), 260);
  }, 2600);
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
  const foco = $('[data-autofocus]', ov);
  if (foco) foco.focus();
  if (opciones.onMount) opciones.onMount(ov);
  return ov;
}

function cerrarModal() {
  const ov = $('#overlay');
  if (ov) ov.remove();
}

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

function render() {
  const hash = location.hash.replace(/^#/, '') || '/';
  for (const [re, fn] of rutas) {
    const m = hash.match(re);
    if (m) {
      document.body.classList.remove('nav-open');
      fn(...m.slice(1));
      window.scrollTo({ top: 0 });
      marcarNav(hash);
      return;
    }
  }
  navegar('#/');
}

function marcarNav(hash) {
  $$('.nav-item[href]').forEach((a) => {
    const href = a.getAttribute('href').replace(/^#/, '');
    const activo = href === '/' ? hash === '/' || hash.startsWith('/cot') || hash.startsWith('/doc') : hash.startsWith(href);
    a.classList.toggle('is-active', activo);
  });
  const pend = DB.cotizaciones.filter((c) => c.estado === 'borrador' || c.estado === 'enviada').length;
  const b = $('#navCount');
  if (b) { b.textContent = pend || ''; b.classList.toggle('hidden', !pend); }
}

function setVista(html) { $('#view').innerHTML = html; }

function topbar({ titulo, sub, atras, acciones = '' }) {
  return `
    <header class="topbar">
      <button class="btn btn-ghost btn-icon nav-toggle" data-nav-toggle aria-label="Menú">${icon('menu')}</button>
      ${atras ? `<a class="btn btn-ghost btn-icon" href="${atras}" aria-label="Volver">${icon('back')}</a>` : ''}
      <div>
        <h1>${titulo}</h1>
        ${sub ? `<div class="sub">${sub}</div>` : ''}
      </div>
      <div class="topbar-actions">${acciones}</div>
    </header>`;
}

/* ================================================================ TABLERO */
const filtro = { q: '', estado: 'todas' };

ruta(/^\/$/, () => {
  document.title = 'Tablero · Taller Vittorio';
  const cots = [...DB.cotizaciones].sort((a, b) => (b.actualizada || '').localeCompare(a.actualizada || ''));

  const mes = new Date().toISOString().slice(0, 7);
  const delMes = cots.filter((c) => (c.creada || '').slice(0, 7) === mes && c.estado !== 'anulada');
  const activas = cots.filter((c) => c.estado === 'aprobada' || c.estado === 'taller');
  const porCobrar = cots
    .filter((c) => ['aprobada', 'taller', 'entregada'].includes(c.estado))
    .reduce((a, c) => a + Math.max(0, calc(c).saldo), 0);
  const montoMes = delMes.reduce((a, c) => a + calc(c).total, 0);
  const ticket = delMes.length ? montoMes / delMes.length : 0;

  const conteo = {};
  Object.keys(ESTADOS).forEach((k) => { conteo[k] = cots.filter((c) => c.estado === k).length; });

  setVista(`
    ${topbar({
      titulo: 'Tablero',
      sub: `${DB.cotizaciones.length} cotizaciones · ${activas.length} en curso`,
      acciones: `
        <button class="btn btn-soft" data-cmdk>${icon('search', 'i i-sm')} Buscar <kbd>⌘K</kbd></button>
        <button class="btn btn-primary" data-nueva>${icon('plus', 'i i-sm')} Nueva cotización</button>`
    })}
    <div class="view">
      <div class="kpis">
        ${kpi('Cotizado este mes', money(montoMes), `${delMes.length} cotizaciones`, 'chart')}
        ${kpi('Por cobrar', money(porCobrar), 'Aprobadas, en taller y entregadas', 'wallet')}
        ${kpi('En curso', String(activas.length), 'Aprobadas o en el taller', 'wrench')}
        ${kpi('Ticket promedio', money(ticket), 'Del mes en curso', 'trend')}
      </div>

      <div class="card">
        <div class="card-head">
          <div class="toolbar" style="flex:1">
            <div class="search">
              ${icon('search')}
              <input class="input" id="q" placeholder="Buscar por patente, cliente, folio o vehículo…" value="${h(filtro.q)}" autocomplete="off">
            </div>
            <div class="chips">
              <button class="chip-btn" data-filtro="todas" aria-pressed="${filtro.estado === 'todas'}">Todas</button>
              ${Object.entries(ESTADOS).map(([k, v]) => `
                <button class="chip-btn" data-filtro="${k}" aria-pressed="${filtro.estado === k}">${v.label}${conteo[k] ? ` · ${conteo[k]}` : ''}</button>`).join('')}
            </div>
          </div>
        </div>
        <div class="card-body" id="lista"></div>
      </div>
    </div>`);

  pintarLista();
});

function kpi(label, valor, pie, ico) {
  return `
    <div class="kpi">
      <div class="k-icon">${icon(ico, 'i i-sm')}</div>
      <div class="k-label">${h(label)}</div>
      <div class="k-value">${h(valor)}</div>
      <div class="k-foot">${h(pie)}</div>
    </div>`;
}

function pintarLista() {
  const cont = $('#lista');
  if (!cont) return;
  const q = filtro.q.trim().toLowerCase();
  let cots = [...DB.cotizaciones].sort((a, b) => (b.actualizada || '').localeCompare(a.actualizada || ''));
  if (filtro.estado !== 'todas') cots = cots.filter((c) => c.estado === filtro.estado);
  if (q) {
    cots = cots.filter((c) => [
      c.folio, c.cliente?.nombre, c.cliente?.telefono, c.vehiculo?.patente,
      c.vehiculo?.marcaModelo, c.motivo
    ].join(' ').toLowerCase().includes(q));
  }

  if (!cots.length) {
    cont.innerHTML = `
      <div class="empty">
        <div class="e-icon">${icon('file', 'i i-lg')}</div>
        <h3>${q || filtro.estado !== 'todas' ? 'Sin resultados' : 'Todavía no hay cotizaciones'}</h3>
        <p>${q || filtro.estado !== 'todas'
          ? 'Probá con otra patente, cliente o estado.'
          : 'Creá la primera: cargás el vehículo, elegís los trabajos del catálogo y el total se arma solo.'}</p>
        <button class="btn btn-primary" data-nueva>${icon('plus', 'i i-sm')} Nueva cotización</button>
      </div>`;
    return;
  }

  cont.innerHTML = `<div class="cot-list">${cots.map(tarjetaCot).join('')}</div>`;
}

function tarjetaCot(c) {
  const t = calc(c);
  const est = ESTADOS[c.estado] || ESTADOS.borrador;
  const hechos = (c.trabajos || []).filter((x) => x.realizado).length;
  const totalT = (c.trabajos || []).filter((x) => (x.desc || '').trim()).length;
  return `
    <button class="cot-card" data-abrir="${c.id}">
      <div class="plate">${icon('car')}</div>
      <div>
        <div class="t1">
          ${h(c.vehiculo?.marcaModelo || 'Vehículo sin identificar')}
          <span class="badge ${est.cls}">${est.label}</span>
          ${c.vehiculo?.patente ? `<span class="pill mono">${h(patente(c.vehiculo.patente))}</span>` : ''}
        </div>
        <div class="t2">
          <span>${h(c.folio)}</span>
          ${c.cliente?.nombre ? `<span>${h(c.cliente.nombre)}</span>` : ''}
          ${totalT ? `<span>${totalT} trabajos${hechos ? ` · ${hechos} listos` : ''}</span>` : ''}
          <span>${h(desdeAhora(c.actualizada))}</span>
        </div>
      </div>
      <div class="amount">
        <b>${money(t.total)}</b>
        <small>${t.abonado ? `saldo ${money(t.saldo)}` : fecha(c.fechaIngreso)}</small>
      </div>
    </button>`;
}

/* ================================================================= EDITOR */
ruta(/^\/cot\/(.+)$/, (id) => {
  const c = getCot(id);
  if (!c) { toast('Esa cotización ya no existe', 'err'); return navegar('#/'); }
  document.title = `${c.folio} · Taller Vittorio`;

  setVista(`
    ${topbar({
      titulo: c.vehiculo?.marcaModelo || 'Nueva cotización',
      sub: `${h(c.folio)} · ${c.vehiculo?.patente ? patente(c.vehiculo.patente) : 'sin patente'}`,
      atras: '#/',
      acciones: `
        <span class="save-state" id="saveState" data-state="saved">${icon('cloud', 'i i-sm')}<span>Guardado</span></span>
        <a class="btn" href="#/doc/${c.id}">${icon('file', 'i i-sm')} Documento</a>
        <button class="btn btn-primary" data-compartir="${c.id}">${icon('share', 'i i-sm')} Compartir</button>`
    })}
    <div class="view">
      <div class="editor">
        <div class="editor-main">
          ${cardDatos(c)}
          ${cardTrabajos(c)}
          ${cardRepuestos(c)}
          ${cardAbonos(c)}
          ${cardNotas(c)}
        </div>
        <aside class="rail">${railResumen(c)}</aside>
      </div>
    </div>`);
});

function cardDatos(c) {
  return `
    <section class="card" data-cot="${c.id}">
      <div class="card-head">
        <div>${icon('car')}</div>
        <div><h2>Datos de atención</h2><div class="sub">Vehículo, cliente y fechas</div></div>
      </div>
      <div class="card-body grid">
        <div class="grid grid-4">
          <div class="field">
            <label for="f-pat">Patente</label>
            <input class="input mono" id="f-pat" data-bind="vehiculo.patente" value="${h(c.vehiculo.patente)}" placeholder="DJBV53" autocomplete="off" style="text-transform:uppercase">
          </div>
          <div class="field span-2" style="grid-column:span 2">
            <label for="f-mm">Marca y modelo</label>
            <input class="input" id="f-mm" data-bind="vehiculo.marcaModelo" value="${h(c.vehiculo.marcaModelo)}" placeholder="Jaguar XF 30" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-anio">Año</label>
            <input class="input num" id="f-anio" data-bind="vehiculo.anio" value="${h(c.vehiculo.anio)}" placeholder="2012" inputmode="numeric" autocomplete="off">
          </div>
        </div>
        <div class="grid grid-4">
          <div class="field">
            <label for="f-km">Kilometraje</label>
            <input class="input num" id="f-km" data-bind="vehiculo.km" value="${h(c.vehiculo.km)}" placeholder="144000" inputmode="numeric" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-in">Fecha ingreso</label>
            <input class="input" type="date" id="f-in" data-bind="fechaIngreso" value="${h(c.fechaIngreso)}">
          </div>
          <div class="field">
            <label for="f-out">Fecha entrega</label>
            <input class="input" type="date" id="f-out" data-bind="fechaEntrega" value="${h(c.fechaEntrega)}">
          </div>
          <div class="field">
            <label for="f-mot">Motivo de ingreso</label>
            <input class="input" id="f-mot" data-bind="motivo" value="${h(c.motivo)}" placeholder="Suspensión, mantención" autocomplete="off">
          </div>
        </div>
        <div class="grid grid-4">
          <div class="field">
            <label for="f-cli">Cliente</label>
            <input class="input" id="f-cli" data-bind="cliente.nombre" value="${h(c.cliente.nombre)}" placeholder="Nombre y apellido" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-tel">Teléfono</label>
            <input class="input" id="f-tel" data-bind="cliente.telefono" value="${h(c.cliente.telefono)}" placeholder="+56 9 …" inputmode="tel" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-mail">Email</label>
            <input class="input" id="f-mail" data-bind="cliente.email" value="${h(c.cliente.email)}" placeholder="cliente@correo.cl" inputmode="email" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-rut">RUT</label>
            <input class="input mono" id="f-rut" data-bind="cliente.rut" value="${h(c.cliente.rut)}" placeholder="12.345.678-9" autocomplete="off">
          </div>
        </div>
      </div>
    </section>`;
}

function cardTrabajos(c) {
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('wrench')}</div>
        <div><h2>Detalle de atención</h2><div class="sub">Mano de obra</div></div>
        <div class="spacer"></div>
        <button class="btn btn-sm" data-picker="servicios">${icon('list', 'i i-sm')} Del catálogo</button>
      </div>
      <div class="card-body flush">
        <div class="lines lines-mo" id="lines-trabajos">${lineasTrabajos(c)}</div>
        <div class="lines-foot">
          <button class="btn btn-sm btn-soft" data-add="trabajos">${icon('plus', 'i i-sm')} Agregar línea</button>
          <button class="btn btn-sm btn-ghost" data-picker="servicios">Buscar en el catálogo</button>
          <div class="total"><small>Total mano de obra</small><span data-out="mo">${money(calc(c).mo)}</span></div>
        </div>
      </div>
    </section>`;
}

function lineasTrabajos(c) {
  if (!c.trabajos.length) return vacioLinea('Sin trabajos cargados todavía.');
  return `
    <div class="line-head">
      <div>Trabajo</div><div>Observaciones</div><div class="tc">Realizado</div><div class="tr">M.O. ($)</div><div></div>
    </div>
    ${c.trabajos.map((t) => `
      <div class="line" data-linea="trabajos" data-id="${t.id}">
        <input class="input input-flush cell-first" data-field="desc" aria-label="Trabajo" value="${h(t.desc)}" title="${h(t.desc)}" placeholder="Describí el trabajo…" autocomplete="off" list="dl-servicios">
        <input class="input input-flush" data-field="obs" aria-label="Observaciones" value="${h(t.obs)}" title="${h(t.obs)}" placeholder="Observaciones" autocomplete="off">
        <label class="check cell-check tc" title="Trabajo realizado">
          <input type="checkbox" data-field="realizado" ${t.realizado ? 'checked' : ''}>
          <span class="box">${icon('check', 'i')}</span>
          <span class="check-txt">Realizado</span>
        </label>
        <input class="input input-flush input-money" data-field="mo" aria-label="Valor mano de obra" data-money value="${campoMonto(t.mo)}" placeholder="0" inputmode="numeric">
        <button class="row-del" data-del title="Eliminar línea">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

function cardRepuestos(c) {
  const t = calc(c);
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('box')}</div>
        <div><h2>Repuestos e insumos</h2><div class="sub">Con recargo de gestión y traslado</div></div>
        <div class="spacer"></div>
        <button class="btn btn-sm" data-picker="repuestos">${icon('list', 'i i-sm')} Del catálogo</button>
      </div>
      <div class="card-body flush">
        <div class="lines lines-rep" id="lines-repuestos">${lineasRepuestos(c)}</div>
        <div class="lines-foot">
          <button class="btn btn-sm btn-soft" data-add="repuestos">${icon('plus', 'i i-sm')} Agregar repuesto</button>
          <label class="pill" title="Recargo sobre el valor de los repuestos">
            Gastos
            <input class="input" data-bind="gastosPct" value="${h(c.gastosPct)}" inputmode="decimal"
                   style="width:52px;padding:2px 6px;text-align:right;background:transparent;border-color:transparent">
            %
            <b class="mono" data-out="gastos">${money(t.gastos)}</b>
          </label>
          <div class="total"><small>Total repuestos</small><span data-out="rep">${money(t.rep)}</span></div>
        </div>
      </div>
    </section>`;
}

function lineasRepuestos(c) {
  if (!c.repuestos.length) return vacioLinea('Sin repuestos cargados. Agregalos desde el catálogo o a mano.');
  return `
    <div class="line-head">
      <div class="tc">Cant.</div><div>Descripción</div><div class="tr">Valor unitario</div><div class="tr">Total</div><div></div>
    </div>
    ${c.repuestos.map((r) => `
      <div class="line" data-linea="repuestos" data-id="${r.id}">
        <input class="input input-flush tc" data-field="cant" aria-label="Cantidad" value="${h(r.cant)}" inputmode="decimal" style="text-align:center">
        <input class="input input-flush cell-first" data-field="desc" aria-label="Repuesto" value="${h(r.desc)}" title="${h(r.desc)}" placeholder="Repuesto o insumo…" autocomplete="off" list="dl-repuestos">
        <input class="input input-flush input-money" data-field="unit" aria-label="Valor unitario" data-money value="${campoMonto(r.unit)}" placeholder="0" inputmode="numeric">
        <div class="line-total" data-total>${money(num(r.cant) * num(r.unit))}</div>
        <button class="row-del" data-del title="Eliminar línea">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

function cardAbonos(c) {
  const t = calc(c);
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('wallet')}</div>
        <div><h2>Abonos y pagos</h2><div class="sub">Adelantos recibidos</div></div>
        <div class="spacer"></div>
        <span class="pill">Saldo <b class="mono" data-out="saldo-pill">${money(t.saldo)}</b></span>
      </div>
      <div class="card-body flush">
        <div class="lines lines-ab" id="lines-abonos">${lineasAbonos(c)}</div>
        <div class="lines-foot">
          <button class="btn btn-sm btn-soft" data-add="abonos">${icon('plus', 'i i-sm')} Registrar abono</button>
          <div class="total"><small>Total abonado</small><span data-out="abonado">${money(t.abonado)}</span></div>
        </div>
      </div>
    </section>`;
}

function lineasAbonos(c) {
  if (!c.abonos.length) return vacioLinea('Sin abonos registrados.');
  return `
    <div class="line-head"><div>Fecha</div><div>Detalle</div><div class="tr">Monto</div><div></div></div>
    ${c.abonos.map((a) => `
      <div class="line" data-linea="abonos" data-id="${a.id}">
        <input class="input input-flush cell-first" type="date" data-field="fecha" aria-label="Fecha del abono" value="${h(a.fecha)}">
        <input class="input input-flush" data-field="detalle" aria-label="Detalle del abono" value="${h(a.detalle)}" placeholder="Adelanto, transferencia, efectivo…" autocomplete="off">
        <input class="input input-flush input-money" data-field="monto" aria-label="Monto del abono" data-money value="${campoMonto(a.monto)}" placeholder="0" inputmode="numeric">
        <button class="row-del" data-del title="Eliminar abono">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

function cardNotas(c) {
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('note')}</div>
        <div><h2>Notas para el cliente</h2><div class="sub">Se imprimen en el documento</div></div>
      </div>
      <div class="card-body">
        <textarea class="input" data-bind="notas" rows="3" placeholder="Ej: se recomienda revisar bujes de barra estabilizadora en la próxima mantención.">${h(c.notas)}</textarea>
      </div>
    </section>`;
}

function vacioLinea(txt) {
  return `<div style="padding:22px 16px;text-align:center;color:var(--ink-3);font-size:13.5px">${h(txt)}</div>`;
}

function railResumen(c) {
  const t = calc(c);
  const est = ESTADOS[c.estado] || ESTADOS.borrador;
  return `
    <section class="card">
      <div class="card-head"><div><h2>Resumen</h2><div class="sub">Se recalcula solo</div></div></div>
      <div class="card-body">
        <div class="field" style="margin-bottom:16px">
          <label for="f-estado">Estado</label>
          <select class="select" id="f-estado" data-bind="estado">
            ${Object.entries(ESTADOS).map(([k, v]) => `<option value="${k}" ${c.estado === k ? 'selected' : ''}>${v.label}</option>`).join('')}
          </select>
        </div>
        <div class="sum" id="sum">${sumRows(t)}</div>
      </div>
      <div class="card-body" style="border-top:1px solid var(--line-2);display:grid;gap:12px">
        <label class="switch">
          <input type="checkbox" data-bind="aplicaIva" ${c.aplicaIva ? 'checked' : ''}>
          <span class="track"></span>
          <span>Agregar IVA (${h(c.ivaPct)}%)</span>
        </label>
        <div class="field">
          <label for="f-desc">Descuento ($)</label>
          <input class="input input-money" id="f-desc" data-bind="descuento" data-money value="${campoMonto(c.descuento)}" placeholder="0" inputmode="numeric">
        </div>
      </div>
    </section>

    <section class="card">
      <div class="card-body" style="display:grid;gap:9px">
        <a class="btn btn-primary btn-block" href="#/doc/${c.id}">${icon('file', 'i i-sm')} Ver documento / PDF</a>
        <button class="btn btn-block" data-compartir="${c.id}">${icon('share', 'i i-sm')} Compartir con el cliente</button>
        <button class="btn btn-block" data-whatsapp="${c.id}">${icon('chat', 'i i-sm')} Enviar por WhatsApp</button>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:9px">
          <button class="btn" data-duplicar="${c.id}">${icon('copy', 'i i-sm')} Duplicar</button>
          <button class="btn btn-danger" data-eliminar="${c.id}">${icon('trash', 'i i-sm')} Eliminar</button>
        </div>
        <p class="faint" style="font-size:11.5px;margin-top:2px">Creada ${h(fecha(c.creada?.slice(0, 10)))} · <span class="badge ${est.cls}">${est.label}</span></p>
      </div>
    </section>`;
}

function sumRows(t) {
  return `
    <div class="sum-row"><span class="lbl-t">Mano de obra</span><span class="val">${money(t.mo)}</span></div>
    <div class="sum-row"><span class="lbl-t">Repuestos</span><span class="val">${money(t.repBase)}</span></div>
    ${t.gastos ? `<div class="sum-row"><span class="lbl-t faint">Gastos y traslados</span><span class="val faint">${money(t.gastos)}</span></div>` : ''}
    ${t.descuento ? `<div class="sum-row"><span class="lbl-t" style="color:var(--ok)">Descuento</span><span class="val" style="color:var(--ok)">−${money(t.descuento)}</span></div>` : ''}
    <div class="sum-sep"></div>
    <div class="sum-row"><span class="lbl-t">Neto</span><span class="val">${money(t.neto)}</span></div>
    ${t.iva ? `<div class="sum-row"><span class="lbl-t">IVA ${t.ivaPct}%</span><span class="val">${money(t.iva)}</span></div>` : ''}
    <div class="sum-total"><span class="lbl-t">Total</span><span class="val">${money(t.total)}</span></div>
    ${t.abonado ? `
      <div class="sum-row" style="margin-top:8px"><span class="lbl-t">Abonado</span><span class="val" style="color:var(--ok)">−${money(t.abonado)}</span></div>` : ''}
    <div class="sum-pay"><span class="lbl-t">A pagar</span><span class="val">${money(t.saldo)}</span></div>`;
}

/** Actualiza sólo los números, sin volver a dibujar los campos (no roba el foco). */
function refrescarTotales(c) {
  const t = calc(c);
  const sum = $('#sum');
  if (sum) sum.innerHTML = sumRows(t);
  const set = (k, v) => { const el = $(`[data-out="${k}"]`); if (el) el.textContent = v; };
  set('mo', money(t.mo));
  set('rep', money(t.rep));
  set('gastos', money(t.gastos));
  set('abonado', money(t.abonado));
  set('saldo-pill', money(t.saldo));
  $$('[data-linea="repuestos"]').forEach((row) => {
    const r = (c.repuestos || []).find((x) => x.id === row.dataset.id);
    const cel = $('[data-total]', row);
    if (r && cel) cel.textContent = money(num(r.cant) * num(r.unit));
  });
}

/* ============================================================== DOCUMENTO */
ruta(/^\/doc\/([^/]+)(?:\/(ot))?$/, (id, modo) => {
  const c = getCot(id);
  if (!c) { toast('Esa cotización ya no existe', 'err'); return navegar('#/'); }
  const esOT = modo === 'ot';
  document.title = `${c.folio} · ${esOT ? 'Orden de trabajo' : 'Cotización'}`;
  setVista(`
    <div class="doc-bar no-print">
      <a class="btn btn-ghost btn-icon" href="#/cot/${c.id}" aria-label="Volver">${icon('back')}</a>
      <div class="seg" role="group" aria-label="Tipo de documento">
        <button data-doc-modo="" aria-pressed="${!esOT}">Cotización</button>
        <button data-doc-modo="ot" aria-pressed="${esOT}">Orden de trabajo</button>
      </div>
      <div class="spacer"></div>
      <button class="btn" data-compartir="${c.id}">${icon('share', 'i i-sm')} Compartir</button>
      <button class="btn" data-whatsapp="${c.id}">${icon('chat', 'i i-sm')} WhatsApp</button>
      <button class="btn btn-primary" data-imprimir>${icon('print', 'i i-sm')} Imprimir / PDF</button>
    </div>
    <div class="doc-stage doc-scope">${documentoHTML(c, DB.ajustes, esOT)}</div>`);
});

/* Vista pública: se abre desde el enlace compartido, sin tocar los datos locales. */
ruta(/^\/ver\/(.+)$/, (payload) => {
  let datos;
  try {
    datos = JSON.parse(b64urlDecode(payload));
  } catch (e) {
    setVista(`${topbar({ titulo: 'Enlace inválido' })}
      <div class="view"><div class="card"><div class="empty">
        <div class="e-icon">${icon('alert', 'i i-lg')}</div>
        <h3>No pudimos leer este enlace</h3>
        <p>Puede que se haya cortado al copiarlo. Pedí al taller que te lo envíe de nuevo.</p>
      </div></div></div>`);
    return;
  }
  const c = datos.c;
  const aj = Object.assign({}, AJUSTES_DEFAULT, datos.a || {});
  document.title = `${c.folio} · ${aj.taller}`;
  setVista(`
    <div class="doc-bar no-print">
      <div class="pill">${icon('eye', 'i i-sm')} Documento compartido</div>
      <div class="spacer"></div>
      ${aj.telefono ? `<a class="btn" href="https://wa.me/${String(aj.telefono).replace(/\D/g, '')}" target="_blank" rel="noopener">${icon('chat', 'i i-sm')} Contactar al taller</a>` : ''}
      <button class="btn btn-primary" data-imprimir>${icon('print', 'i i-sm')} Imprimir / PDF</button>
    </div>
    <div class="doc-stage doc-scope">${documentoHTML(c, aj, false)}</div>`);
});

function documentoHTML(c, aj, esOT) {
  const t = calc(c);
  const est = ESTADOS[c.estado] || ESTADOS.borrador;
  const trabajos = (c.trabajos || []).filter((x) => (x.desc || '').trim() || num(x.mo));
  const repuestos = (c.repuestos || []).filter((x) => (x.desc || '').trim() || num(x.unit));
  const condiciones = Array.isArray(aj.condiciones) ? aj.condiciones : String(aj.condiciones || '').split('\n').filter(Boolean);
  const vence = c.fechaIngreso && aj.validezDias
    ? new Date(new Date(c.fechaIngreso).getTime() + num(aj.validezDias) * 86400000).toISOString().slice(0, 10)
    : '';

  return `
  <article class="doc">
    <header class="doc-head">
      <div class="doc-brand">
        <div class="mk"><svg viewBox="0 0 24 24"><path d="M3 13.5 5 8a3 3 0 0 1 2.8-2h8.4A3 3 0 0 1 19 8l2 5.5"/><path d="M3 13.5h18V18a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><circle cx="7.5" cy="16" r=".6"/><circle cx="16.5" cy="16" r=".6"/></svg></div>
        <div>
          <div class="n1">${h(aj.taller)}</div>
          <div class="n2">${h(aj.lema || '')}</div>
          <div class="n2">${[aj.direccion, aj.comuna].filter(Boolean).map(h).join(', ')}${aj.telefono ? ` · ${h(aj.telefono)}` : ''}${aj.email ? ` · ${h(aj.email)}` : ''}</div>
        </div>
      </div>
      <div class="doc-title-box">
        <div class="k">${esOT ? 'Orden de trabajo' : 'Cotización'}</div>
        <div class="f">${h(c.folio)}</div>
        <div class="d">${h(fecha(c.fechaIngreso, true))}</div>
        ${!esOT && vence ? `<div class="d">Válida hasta ${h(fecha(vence))}</div>` : ''}
      </div>
    </header>

    <div class="doc-body">
      <div class="doc-facts">
        <div class="doc-panel">
          <h4>Vehículo</h4>
          <dl class="doc-dl">
            <dt>Marca / modelo</dt><dd>${h(c.vehiculo?.marcaModelo || '—')}</dd>
            <dt>Patente</dt><dd class="mono">${h(patente(c.vehiculo?.patente))}</dd>
            <dt>Año</dt><dd>${h(c.vehiculo?.anio || '—')}</dd>
            <dt>Kilometraje</dt><dd>${c.vehiculo?.km ? miles(c.vehiculo.km) + ' km' : '—'}</dd>
            ${c.motivo ? `<dt>Motivo</dt><dd>${h(c.motivo)}</dd>` : ''}
          </dl>
        </div>
        <div class="doc-panel">
          <h4>Cliente y fechas</h4>
          <dl class="doc-dl">
            <dt>Cliente</dt><dd>${h(c.cliente?.nombre || '—')}</dd>
            ${c.cliente?.telefono ? `<dt>Teléfono</dt><dd>${h(c.cliente.telefono)}</dd>` : ''}
            ${c.cliente?.rut ? `<dt>RUT</dt><dd class="mono">${h(c.cliente.rut)}</dd>` : ''}
            <dt>Ingreso</dt><dd>${h(fecha(c.fechaIngreso))}</dd>
            <dt>Entrega</dt><dd>${c.fechaEntrega ? h(fecha(c.fechaEntrega)) : 'Por confirmar'}</dd>
            <dt>Estado</dt><dd>${est.label}</dd>
          </dl>
        </div>
      </div>

      ${trabajos.length ? `
      <section>
        <h3 class="doc-sec">Detalle de atención · mano de obra</h3>
        <table class="doc-tbl">
          <thead>
            <tr>
              ${esOT ? '<th style="width:34px">OK</th>' : ''}
              <th>Trabajo</th>
              <th class="c-num" style="width:110px">M.O.</th>
            </tr>
          </thead>
          <tbody>
            ${trabajos.map((x) => `
              <tr>
                ${esOT ? `<td class="c-mid"><span class="ot-box ${x.realizado ? 'is-on' : ''}"></span></td>` : ''}
                <td>${h(x.desc)}${x.obs ? `<span class="obs">${h(x.obs)}</span>` : ''}</td>
                <td class="c-num">${money(x.mo)}</td>
              </tr>`).join('')}
          </tbody>
          <tfoot>
            <tr>
              ${esOT ? '<td></td>' : ''}
              <td class="c-num">Total mano de obra</td>
              <td class="c-num">${money(t.mo)}</td>
            </tr>
          </tfoot>
        </table>
      </section>` : ''}

      ${repuestos.length ? `
      <section>
        <h3 class="doc-sec">Repuestos e insumos</h3>
        <table class="doc-tbl">
          <thead>
            <tr>
              <th class="c-mid" style="width:56px">Cant.</th>
              <th>Descripción</th>
              <th class="c-num" style="width:110px">Valor unit.</th>
              <th class="c-num" style="width:110px">Total</th>
            </tr>
          </thead>
          <tbody>
            ${repuestos.map((r) => `
              <tr>
                <td class="c-mid">${h(NUM.format(num(r.cant)))}</td>
                <td>${h(r.desc)}</td>
                <td class="c-num">${money(r.unit)}</td>
                <td class="c-num">${money(num(r.cant) * num(r.unit))}</td>
              </tr>`).join('')}
          </tbody>
          <tfoot>
            ${t.gastos ? `<tr><td colspan="3" class="c-num">Gastos, gestión y traslados (${h(c.gastosPct)}%)</td><td class="c-num">${money(t.gastos)}</td></tr>` : ''}
            <tr><td colspan="3" class="c-num">Total repuestos</td><td class="c-num">${money(t.rep)}</td></tr>
          </tfoot>
        </table>
      </section>` : ''}

      <section class="doc-totals">
        <div class="doc-note">
          ${aj.garantia ? `<b>Garantía.</b> ${h(aj.garantia)}<br><br>` : ''}
          ${condiciones.length ? `<b>Condiciones</b><ul>${condiciones.map((x) => `<li>${h(x)}</li>`).join('')}</ul>` : ''}
          ${c.notas ? `<br><b>Notas.</b> ${h(c.notas)}` : ''}
        </div>
        <div>
          <div class="doc-total-rows">
            <div class="r"><span>Mano de obra</span><span class="v">${money(t.mo)}</span></div>
            <div class="r"><span>Repuestos e insumos</span><span class="v">${money(t.rep)}</span></div>
            ${t.descuento ? `<div class="r"><span>Descuento</span><span class="v">−${money(t.descuento)}</span></div>` : ''}
            <div class="r is-sep"><span>Neto</span><span class="v">${money(t.neto)}</span></div>
            ${t.iva ? `<div class="r"><span>IVA ${t.ivaPct}%</span><span class="v">${money(t.iva)}</span></div>` : ''}
            <div class="r is-sep is-big"><span>Total</span><span class="v">${money(t.total)}</span></div>
            ${t.abonado ? `<div class="r"><span>Abonos recibidos</span><span class="v">−${money(t.abonado)}</span></div>` : ''}
          </div>
          <div class="doc-pay"><span class="k">A pagar</span><span class="v">${money(t.saldo)}</span></div>
        </div>
      </section>

      ${(c.abonos || []).length ? `
      <section>
        <h3 class="doc-sec">Abonos registrados</h3>
        <table class="doc-tbl">
          <thead><tr><th style="width:120px">Fecha</th><th>Detalle</th><th class="c-num" style="width:120px">Monto</th></tr></thead>
          <tbody>
            ${c.abonos.map((a) => `<tr><td>${h(fecha(a.fecha))}</td><td>${h(a.detalle || 'Abono')}</td><td class="c-num">${money(a.monto)}</td></tr>`).join('')}
          </tbody>
          <tfoot><tr><td colspan="2" class="c-num">Total abonado</td><td class="c-num">${money(t.abonado)}</td></tr></tfoot>
        </table>
      </section>` : ''}

      ${esOT ? `
      <div class="doc-sign">
        <div>Recepción · taller</div>
        <div>Conformidad del cliente</div>
      </div>` : ''}
    </div>

    <footer class="doc-foot">
      <span>${h(aj.taller)}${aj.rut ? ` · RUT ${h(aj.rut)}` : ''}</span>
      <span>${h(c.folio)} · Documento generado el ${h(fecha(hoyISO()))}</span>
    </footer>
  </article>`;
}

/* =============================================================== CATÁLOGO */
ruta(/^\/catalogo$/, () => {
  document.title = 'Catálogo · Taller Vittorio';
  setVista(`
    ${topbar({
      titulo: 'Catálogo de precios',
      sub: 'Lo que cargás acá aparece al cotizar',
      acciones: `<button class="btn btn-primary" data-cat-add="servicios">${icon('plus', 'i i-sm')} Nuevo servicio</button>`
    })}
    <div class="view">
      <section class="card">
        <div class="card-head">
          <div>${icon('wrench')}</div>
          <div><h2>Mano de obra</h2><div class="sub">${DB.servicios.length} servicios</div></div>
          <div class="spacer"></div>
          <button class="btn btn-sm" data-cat-add="servicios">${icon('plus', 'i i-sm')} Agregar</button>
        </div>
        <div class="card-body flush"><div class="table-wrap" id="tbl-servicios">${tablaCatalogo('servicios')}</div></div>
      </section>

      <section class="card">
        <div class="card-head">
          <div>${icon('box')}</div>
          <div><h2>Repuestos e insumos</h2><div class="sub">${DB.repuestos.length} ítems</div></div>
          <div class="spacer"></div>
          <button class="btn btn-sm" data-cat-add="repuestos">${icon('plus', 'i i-sm')} Agregar</button>
        </div>
        <div class="card-body flush"><div class="table-wrap" id="tbl-repuestos">${tablaCatalogo('repuestos')}</div></div>
      </section>
    </div>`);
});

function tablaCatalogo(tipo) {
  const items = DB[tipo];
  if (!items.length) return vacioLinea('Sin ítems. Agregá el primero.');
  const esServicio = tipo === 'servicios';
  return `
    <table class="tbl">
      <thead>
        <tr>
          <th style="width:170px">Categoría</th>
          <th>Descripción</th>
          ${esServicio ? '<th style="width:200px">Observación por defecto</th>' : ''}
          <th class="tr" style="width:130px">Precio</th>
          <th style="width:44px"></th>
        </tr>
      </thead>
      <tbody>
        ${items.map((it) => `
          <tr data-cat="${tipo}" data-id="${it.id}">
            <td><input class="input input-flush" data-cfield="cat" value="${h(it.cat || '')}" placeholder="Categoría"></td>
            <td><input class="input input-flush" data-cfield="desc" value="${h(it.desc || '')}" placeholder="Descripción"></td>
            ${esServicio ? `<td><input class="input input-flush" data-cfield="obs" value="${h(it.obs || '')}" placeholder="—"></td>` : ''}
            <td><input class="input input-flush input-money" data-cfield="precio" data-money value="${campoMonto(it.precio)}" inputmode="numeric"></td>
            <td><button class="btn btn-ghost btn-icon" data-cat-del title="Eliminar">${icon('trash', 'i i-sm')}</button></td>
          </tr>`).join('')}
      </tbody>
    </table>`;
}

/* ================================================================ AJUSTES */
ruta(/^\/ajustes$/, () => {
  document.title = 'Ajustes · Taller Vittorio';
  const a = DB.ajustes;
  setVista(`
    ${topbar({ titulo: 'Ajustes', sub: 'Datos del taller y valores por defecto' })}
    <div class="view view-narrow">
      <section class="card">
        <div class="card-head"><div>${icon('shop')}</div><div><h2>Datos del taller</h2><div class="sub">Aparecen en cada documento</div></div></div>
        <div class="card-body grid">
          <div class="grid grid-2">
            <div class="field"><label for="a1">Nombre</label><input class="input" id="a1" data-aj="taller" value="${h(a.taller)}"></div>
            <div class="field"><label for="a2">Bajada / rubro</label><input class="input" id="a2" data-aj="lema" value="${h(a.lema)}"></div>
          </div>
          <div class="grid grid-3">
            <div class="field"><label for="a3">RUT</label><input class="input mono" id="a3" data-aj="rut" value="${h(a.rut)}" placeholder="76.123.456-7"></div>
            <div class="field"><label for="a4">Teléfono</label><input class="input" id="a4" data-aj="telefono" value="${h(a.telefono)}" placeholder="+56 9 1234 5678"></div>
            <div class="field"><label for="a5">Email</label><input class="input" id="a5" data-aj="email" value="${h(a.email)}"></div>
          </div>
          <div class="grid grid-3">
            <div class="field"><label for="a6">Dirección</label><input class="input" id="a6" data-aj="direccion" value="${h(a.direccion)}"></div>
            <div class="field"><label for="a7">Comuna</label><input class="input" id="a7" data-aj="comuna" value="${h(a.comuna)}"></div>
            <div class="field"><label for="a8">Sitio web</label><input class="input" id="a8" data-aj="web" value="${h(a.web)}"></div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head"><div>${icon('calc')}</div><div><h2>Valores por defecto</h2><div class="sub">Se aplican a cada cotización nueva</div></div></div>
        <div class="card-body grid">
          <div class="grid grid-4">
            <div class="field"><label for="b1">IVA (%)</label><input class="input num" id="b1" data-aj="ivaPct" value="${h(a.ivaPct)}" inputmode="decimal"></div>
            <div class="field"><label for="b2">Gastos (%)</label><input class="input num" id="b2" data-aj="gastosPct" value="${h(a.gastosPct)}" inputmode="decimal"></div>
            <div class="field"><label for="b3">Validez (días)</label><input class="input num" id="b3" data-aj="validezDias" value="${h(a.validezDias)}" inputmode="numeric"></div>
            <div class="field"><label for="b4">Prefijo de folio</label><input class="input mono" id="b4" data-aj="folioPrefijo" value="${h(a.folioPrefijo)}"></div>
          </div>
          <label class="switch"><input type="checkbox" data-aj="aplicaIvaDefault" ${a.aplicaIvaDefault ? 'checked' : ''}><span class="track"></span><span>Cotizar con IVA por defecto</span></label>
          <div class="field"><label for="b5">Texto de garantía</label><textarea class="input" id="b5" data-aj="garantia" rows="2">${h(a.garantia)}</textarea></div>
          <div class="field">
            <label for="b6">Condiciones (una por línea)</label>
            <textarea class="input" id="b6" data-aj-lista="condiciones" rows="4">${h((a.condiciones || []).join('\n'))}</textarea>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head"><div>${icon('shield')}</div><div><h2>Tus datos</h2><div class="sub">Todo queda en este navegador</div></div></div>
        <div class="card-body grid">
          <p class="muted" style="font-size:13.5px">
            El sistema funciona sin servidor: las cotizaciones se guardan en este dispositivo. Descargá un respaldo cada
            tanto o para pasarlas a otro computador.
          </p>
          <div class="toolbar">
            <button class="btn" data-export>${icon('download', 'i i-sm')} Descargar respaldo</button>
            <button class="btn" data-import>${icon('upload', 'i i-sm')} Restaurar respaldo</button>
            <button class="btn" data-csv>${icon('table', 'i i-sm')} Exportar CSV</button>
            <button class="btn btn-danger" data-reset>${icon('trash', 'i i-sm')} Borrar todo</button>
          </div>
          <input type="file" id="fileImport" accept="application/json" class="hidden">
        </div>
      </section>
    </div>`);
});

/* ================================================== catálogo: selector ==== */
let pickerCursor = 0;

function abrirPicker(tipo, cotId) {
  const items = DB[tipo];
  const esServicio = tipo === 'servicios';
  pickerCursor = 0;
  const ov = abrirModal(`
    <div class="modal" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div>${icon(esServicio ? 'wrench' : 'box')}</div>
        <div>
          <h3>${esServicio ? 'Agregar trabajos' : 'Agregar repuestos'}</h3>
          <div class="sub">Enter agrega · se puede seguir eligiendo</div>
        </div>
        <div class="spacer" style="margin-left:auto"></div>
        <button class="btn btn-ghost btn-icon" data-cerrar aria-label="Cerrar">${icon('x')}</button>
      </div>
      <div class="picker-search">
        <input class="input" id="pq" placeholder="Buscar en el catálogo…" data-autofocus autocomplete="off">
      </div>
      <div class="modal-body flush"><div class="picker-list" id="plist"></div></div>
      <div class="modal-foot">
        <span class="faint" style="margin-right:auto;font-size:12px">${items.length} ítems · editables en Catálogo</span>
        <button class="btn btn-primary" data-cerrar>Listo</button>
      </div>
    </div>`);

  const pintar = () => {
    const q = ($('#pq').value || '').toLowerCase().trim();
    const filtrados = items.filter((it) => (it.cat + ' ' + it.desc).toLowerCase().includes(q));
    const cont = $('#plist');
    if (!filtrados.length) {
      cont.innerHTML = `<div style="padding:26px;text-align:center;color:var(--ink-3)">
          Nada con “${h(q)}”.<br><button class="btn btn-sm" style="margin-top:10px" data-crear-item>${icon('plus', 'i i-sm')} Crear “${h(q)}” en el catálogo</button>
        </div>`;
      return;
    }
    let cat = '';
    cont.innerHTML = filtrados.map((it, i) => {
      const head = it.cat && it.cat !== cat ? `<div class="picker-cat">${h(it.cat)}</div>` : '';
      cat = it.cat || cat;
      return `${head}
        <button class="picker-item ${i === pickerCursor ? 'is-cursor' : ''}" data-pick="${it.id}">
          <div>
            <div class="p1">${h(it.desc)}</div>
            ${it.obs ? `<div class="p2">${h(it.obs)}</div>` : ''}
          </div>
          <div class="p-price">${money(it.precio)}</div>
        </button>`;
    }).join('');
  };

  pintar();

  $('#pq').addEventListener('input', () => { pickerCursor = 0; pintar(); });
  $('#pq').addEventListener('keydown', (e) => {
    const botones = $$('[data-pick]', ov);
    if (e.key === 'ArrowDown') { e.preventDefault(); pickerCursor = Math.min(pickerCursor + 1, botones.length - 1); pintar(); $$('[data-pick]', ov)[pickerCursor]?.scrollIntoView({ block: 'nearest' }); }
    if (e.key === 'ArrowUp') { e.preventDefault(); pickerCursor = Math.max(pickerCursor - 1, 0); pintar(); $$('[data-pick]', ov)[pickerCursor]?.scrollIntoView({ block: 'nearest' }); }
    if (e.key === 'Enter') {
      e.preventDefault();
      const b = botones[pickerCursor];
      if (b) agregarDesdeCatalogo(tipo, b.dataset.pick, cotId);
      else if (($('#pq').value || '').trim()) crearItemCatalogo(tipo, $('#pq').value.trim(), cotId);
    }
  });

  ov.addEventListener('click', (e) => {
    const pick = e.target.closest('[data-pick]');
    if (pick) { agregarDesdeCatalogo(tipo, pick.dataset.pick, cotId); return; }
    if (e.target.closest('[data-crear-item]')) { crearItemCatalogo(tipo, $('#pq').value.trim(), cotId); pintar(); return; }
    if (e.target.closest('[data-cerrar]')) cerrarModal();
  });
}

function agregarDesdeCatalogo(tipo, itemId, cotId) {
  const c = getCot(cotId);
  const it = DB[tipo].find((x) => x.id === itemId);
  if (!c || !it) return;
  if (tipo === 'servicios') {
    c.trabajos.push({ id: uid(), desc: it.desc, obs: it.obs || '', realizado: false, mo: num(it.precio) });
    repintarLineas(c, 'trabajos');
  } else {
    c.repuestos.push({ id: uid(), cant: 1, desc: it.desc, unit: num(it.precio) });
    repintarLineas(c, 'repuestos');
  }
  tocar(c);
  toast(`${it.desc.slice(0, 42)}${it.desc.length > 42 ? '…' : ''} agregado`);
}

function crearItemCatalogo(tipo, desc, cotId) {
  if (!desc) return;
  const it = { id: uid(), cat: 'Sin categoría', desc, obs: '', precio: 0 };
  DB[tipo].push(it);
  guardar();
  agregarDesdeCatalogo(tipo, it.id, cotId);
  cerrarModal();
  toast('Ítem creado en el catálogo');
}

function repintarLineas(c, tipo) {
  const cont = $(`#lines-${tipo}`);
  if (!cont) return;
  cont.innerHTML = tipo === 'trabajos' ? lineasTrabajos(c) : tipo === 'repuestos' ? lineasRepuestos(c) : lineasAbonos(c);
  refrescarTotales(c);
}

function tocar(c) {
  c.actualizada = new Date().toISOString();
  guardar();
}

/* ============================================================== COMPARTIR */
function enlaceCompartir(c) {
  const aj = DB.ajustes;
  const payload = b64urlEncode(JSON.stringify({
    c,
    a: { taller: aj.taller, lema: aj.lema, rut: aj.rut, direccion: aj.direccion, comuna: aj.comuna, telefono: aj.telefono, email: aj.email, web: aj.web, garantia: aj.garantia, condiciones: aj.condiciones, validezDias: aj.validezDias }
  }));
  const base = location.href.split('#')[0];
  return `${base}#/ver/${payload}`;
}

async function copiar(texto) {
  try {
    await navigator.clipboard.writeText(texto);
    return true;
  } catch (e) {
    const ta = document.createElement('textarea');
    ta.value = texto;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    let ok = false;
    try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
    ta.remove();
    return ok;
  }
}

function abrirCompartir(c) {
  const url = enlaceCompartir(c);
  const t = calc(c);
  const ov = abrirModal(`
    <div class="modal" style="max-width:560px" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div>${icon('share')}</div>
        <div><h3>Compartir ${h(c.folio)}</h3><div class="sub">Enlace de sólo lectura, no necesita instalar nada</div></div>
      </div>
      <div class="modal-body grid">
        <div class="field">
          <label for="lnk">Enlace</label>
          <textarea class="input mono" id="lnk" rows="3" readonly style="font-size:11.5px">${h(url)}</textarea>
          <div class="hint">El documento viaja dentro del enlace: quien lo abra ve la cotización tal como está hoy.</div>
        </div>
        <div class="toolbar">
          <button class="btn btn-primary" data-copiar>${icon('copy', 'i i-sm')} Copiar enlace</button>
          <button class="btn" data-wa>${icon('chat', 'i i-sm')} WhatsApp</button>
          <a class="btn" data-mail href="mailto:${encodeURIComponent(c.cliente?.email || '')}?subject=${encodeURIComponent(`${DB.ajustes.taller} · ${c.folio}`)}&body=${encodeURIComponent(textoResumen(c, t) + '\n\n' + url)}">${icon('mail', 'i i-sm')} Email</a>
          <a class="btn" href="#/doc/${c.id}" data-cerrar>${icon('print', 'i i-sm')} Imprimir</a>
        </div>
      </div>
      <div class="modal-foot"><button class="btn" data-cerrar>Cerrar</button></div>
    </div>`);

  ov.addEventListener('click', async (e) => {
    if (e.target.closest('[data-copiar]')) {
      const ok = await copiar(url);
      toast(ok ? 'Enlace copiado' : 'Copialo a mano desde el cuadro', ok ? 'ok' : 'err');
    }
    if (e.target.closest('[data-wa]')) { abrirWhatsApp(c, url); }
    if (e.target.closest('[data-cerrar]')) cerrarModal();
  });
}

function textoResumen(c, t) {
  const veh = [c.vehiculo?.marcaModelo, c.vehiculo?.patente ? patente(c.vehiculo.patente) : ''].filter(Boolean).join(' · ');
  return `${DB.ajustes.taller}\n${c.folio}${veh ? ` · ${veh}` : ''}\n` +
    `Mano de obra: ${money(t.mo)}\nRepuestos: ${money(t.rep)}\n` +
    (t.iva ? `IVA: ${money(t.iva)}\n` : '') +
    `Total: ${money(t.total)}` + (t.abonado ? `\nA pagar: ${money(t.saldo)}` : '');
}

function abrirWhatsApp(c, url) {
  const t = calc(c);
  const tel = String(c.cliente?.telefono || '').replace(/\D/g, '');
  const texto = `${textoResumen(c, t)}\n\nDetalle completo: ${url || enlaceCompartir(c)}`;
  const destino = tel ? `https://wa.me/${tel.length === 9 ? '56' + tel : tel}` : 'https://wa.me/';
  window.open(`${destino}?text=${encodeURIComponent(texto)}`, '_blank', 'noopener');
}

/* ======================================================== paleta de comandos */
function abrirCmdK() {
  const acciones = [
    { t: 'Nueva cotización', s: 'Crear', ico: 'plus', run: () => nuevaCotizacion() },
    { t: 'Tablero', s: 'Ir a', ico: 'home', run: () => navegar('#/') },
    { t: 'Catálogo de precios', s: 'Ir a', ico: 'list', run: () => navegar('#/catalogo') },
    { t: 'Ajustes del taller', s: 'Ir a', ico: 'settings', run: () => navegar('#/ajustes') },
    { t: 'Cambiar tema claro / oscuro', s: 'Vista', ico: 'moon', run: () => alternarTema() },
    { t: 'Descargar respaldo', s: 'Datos', ico: 'download', run: () => exportarJSON() }
  ];

  let cursor = 0;
  const ov = abrirModal(`
    <div class="modal cmdk" role="dialog" aria-modal="true">
      <div class="picker-search">
        <input class="input" id="ck" placeholder="Buscá una cotización o escribí una acción…" data-autofocus autocomplete="off">
      </div>
      <div class="modal-body flush"><div class="picker-list" id="cklist"></div></div>
    </div>`);

  const opciones = () => {
    const q = ($('#ck').value || '').toLowerCase().trim();
    const accs = acciones
      .filter((a) => (a.t + ' ' + a.s).toLowerCase().includes(q))
      .map((a) => ({ ...a, tipo: 'accion' }));
    const cots = DB.cotizaciones
      .filter((c) => !q || [c.folio, c.cliente?.nombre, c.vehiculo?.patente, c.vehiculo?.marcaModelo].join(' ').toLowerCase().includes(q))
      .slice(0, 6)
      .map((c) => ({
        t: c.vehiculo?.marcaModelo || c.folio,
        s: `${c.folio} · ${patente(c.vehiculo?.patente)}`,
        ico: 'car', tipo: 'cot', run: () => navegar(`#/cot/${c.id}`)
      }));
    return [...accs, ...cots];
  };

  const pintar = () => {
    const list = opciones();
    cursor = Math.min(cursor, Math.max(0, list.length - 1));
    $('#cklist').innerHTML = list.length
      ? list.map((o, i) => `
          <button class="picker-item ${i === cursor ? 'is-cursor' : ''}" data-i="${i}">
            <div style="display:flex;align-items:center;gap:11px">
              <span style="color:var(--ink-3)">${icon(o.ico, 'i i-sm')}</span>
              <span><span class="p1">${h(o.t)}</span><span class="p2">${h(o.s)}</span></span>
            </div>
            <span class="faint" style="font-size:11px">${o.tipo === 'cot' ? 'Abrir' : '↵'}</span>
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

/* ================================================================ acciones */
function nuevaCotizacion() {
  const c = cotNueva();
  DB.cotizaciones.unshift(c);
  guardar(true);
  navegar(`#/cot/${c.id}`);
  toast(`${c.folio} creada`);
}

function duplicar(id) {
  const orig = getCot(id);
  if (!orig) return;
  const copia = JSON.parse(JSON.stringify(orig));
  const anio = new Date().getFullYear();
  copia.id = uid();
  copia.folio = `${DB.ajustes.folioPrefijo || 'COT'}-${anio}-${String(DB.seq++).padStart(4, '0')}`;
  copia.estado = 'borrador';
  copia.creada = copia.actualizada = new Date().toISOString();
  copia.fechaIngreso = hoyISO();
  copia.fechaEntrega = '';
  copia.abonos = [];
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
  const ok = await confirmar('Eliminar cotización', `Se borra ${c.folio} y no se puede deshacer.`, 'Eliminar');
  if (!ok) return;
  DB.cotizaciones = DB.cotizaciones.filter((x) => x.id !== id);
  guardar(true);
  navegar('#/');
  toast('Cotización eliminada');
}

function exportarJSON() {
  const blob = new Blob([JSON.stringify(DB, null, 2)], { type: 'application/json' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `vittorio-respaldo-${hoyISO()}.json`;
  a.click();
  URL.revokeObjectURL(a.href);
  toast('Respaldo descargado');
}

function exportarCSV() {
  const filas = [['Folio', 'Estado', 'Ingreso', 'Entrega', 'Cliente', 'Teléfono', 'Vehículo', 'Patente', 'Km', 'Mano de obra', 'Repuestos', 'Neto', 'IVA', 'Total', 'Abonado', 'Saldo']];
  DB.cotizaciones.forEach((c) => {
    const t = calc(c);
    filas.push([
      c.folio, ESTADOS[c.estado]?.label || c.estado, c.fechaIngreso, c.fechaEntrega,
      c.cliente?.nombre, c.cliente?.telefono, c.vehiculo?.marcaModelo, c.vehiculo?.patente, c.vehiculo?.km,
      t.mo, t.rep, t.neto, t.iva, t.total, t.abonado, t.saldo
    ]);
  });
  const csv = filas.map((f) => f.map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`).join(';')).join('\n');
  const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = `vittorio-cotizaciones-${hoyISO()}.csv`;
  a.click();
  URL.revokeObjectURL(a.href);
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
      DB = datos;
      DB.ajustes = Object.assign({}, AJUSTES_DEFAULT, DB.ajustes || {});
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
  const ok = await confirmar('Borrar todo', 'Se eliminan cotizaciones, catálogo y ajustes de este navegador.', 'Borrar todo');
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
  if (btn) btn.innerHTML = icon(t === 'dark' ? 'moon' : t === 'light' ? 'sun' : 'auto', 'i i-sm');
}

function alternarTema() {
  const actual = localStorage.getItem('vittorio.tema') || 'auto';
  const siguiente = actual === 'auto' ? 'light' : actual === 'light' ? 'dark' : 'auto';
  aplicarTema(siguiente);
  toast(`Tema: ${siguiente === 'auto' ? 'automático' : siguiente === 'light' ? 'claro' : 'oscuro'}`);
}

/* ------------------------------------------------------------- datalists  */
function refrescarDatalists() {
  const s = $('#dl-servicios');
  const r = $('#dl-repuestos');
  if (s) s.innerHTML = DB.servicios.map((x) => `<option value="${h(x.desc)}">`).join('');
  if (r) r.innerHTML = DB.repuestos.map((x) => `<option value="${h(x.desc)}">`).join('');
}

/* ============================================================== eventos ==== */
function cotActual() {
  const m = location.hash.match(/^#\/cot\/(.+)$/);
  return m ? getCot(m[1]) : null;
}

document.addEventListener('click', (e) => {
  const t = e.target;

  if (t.closest('[data-nav-toggle]')) { document.body.classList.toggle('nav-open'); return; }
  if (t.closest('#temaBtn')) { alternarTema(); return; }
  if (t.closest('[data-cmdk]')) { abrirCmdK(); return; }
  if (t.closest('[data-nueva]')) { nuevaCotizacion(); return; }
  if (t.closest('[data-imprimir]')) { window.print(); return; }

  const abrir = t.closest('[data-abrir]');
  if (abrir) { navegar(`#/cot/${abrir.dataset.abrir}`); return; }

  const filtroBtn = t.closest('[data-filtro]');
  if (filtroBtn) {
    filtro.estado = filtroBtn.dataset.filtro;
    $$('[data-filtro]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.filtro === filtro.estado)));
    pintarLista();
    return;
  }

  const comp = t.closest('[data-compartir]');
  if (comp) { const c = getCot(comp.dataset.compartir); if (c) abrirCompartir(c); return; }

  const wa = t.closest('[data-whatsapp]');
  if (wa) { const c = getCot(wa.dataset.whatsapp); if (c) abrirWhatsApp(c); return; }

  const dup = t.closest('[data-duplicar]');
  if (dup) { duplicar(dup.dataset.duplicar); return; }

  const del = t.closest('[data-eliminar]');
  if (del) { eliminar(del.dataset.eliminar); return; }

  const modo = t.closest('[data-doc-modo]');
  if (modo) {
    const c = getCot(location.hash.match(/^#\/doc\/([^/]+)/)?.[1]);
    if (c) navegar(`#/doc/${c.id}${modo.dataset.docModo === 'ot' ? '/ot' : ''}`);
    return;
  }

  const picker = t.closest('[data-picker]');
  if (picker) { const c = cotActual(); if (c) abrirPicker(picker.dataset.picker, c.id); return; }

  const add = t.closest('[data-add]');
  if (add) {
    const c = cotActual();
    if (!c) return;
    const tipo = add.dataset.add;
    if (tipo === 'trabajos') c.trabajos.push({ id: uid(), desc: '', obs: '', realizado: false, mo: 0 });
    if (tipo === 'repuestos') c.repuestos.push({ id: uid(), cant: 1, desc: '', unit: 0 });
    if (tipo === 'abonos') c.abonos.push({ id: uid(), fecha: hoyISO(), detalle: '', monto: 0 });
    repintarLineas(c, tipo);
    tocar(c);
    const filas = $$(`#lines-${tipo} .line`);
    const ultima = filas[filas.length - 1];
    if (ultima) $('input:not([type=checkbox])', ultima)?.focus();
    return;
  }

  const rowDel = t.closest('[data-del]');
  if (rowDel) {
    const c = cotActual();
    const linea = rowDel.closest('[data-linea]');
    if (!c || !linea) return;
    const tipo = linea.dataset.linea;
    c[tipo] = c[tipo].filter((x) => x.id !== linea.dataset.id);
    repintarLineas(c, tipo);
    tocar(c);
    return;
  }

  const catAdd = t.closest('[data-cat-add]');
  if (catAdd) {
    const tipo = catAdd.dataset.catAdd;
    DB[tipo].push({ id: uid(), cat: '', desc: '', obs: '', precio: 0 });
    guardar();
    $(`#tbl-${tipo}`).innerHTML = tablaCatalogo(tipo);
    const filas = $$(`#tbl-${tipo} tbody tr`);
    $('input', filas[filas.length - 1])?.focus();
    refrescarDatalists();
    return;
  }

  const catDel = t.closest('[data-cat-del]');
  if (catDel) {
    const tr = catDel.closest('[data-cat]');
    const tipo = tr.dataset.cat;
    DB[tipo] = DB[tipo].filter((x) => x.id !== tr.dataset.id);
    guardar();
    $(`#tbl-${tipo}`).innerHTML = tablaCatalogo(tipo);
    refrescarDatalists();
    return;
  }

  if (t.closest('[data-export]')) { exportarJSON(); return; }
  if (t.closest('[data-csv]')) { exportarCSV(); return; }
  if (t.closest('[data-import]')) { $('#fileImport').click(); return; }
  if (t.closest('[data-reset]')) { borrarTodo(); return; }
});

/* Entrada de texto: campos ligados, líneas, catálogo y ajustes. */
document.addEventListener('input', (e) => {
  const el = e.target;

  if (el.id === 'q') { filtro.q = el.value; pintarLista(); return; }
  if (el.id === 'fileImport') return;

  // Campos simples de la cotización
  const bind = el.dataset.bind;
  if (bind) {
    const c = cotActual();
    if (!c) return;
    let valor = el.type === 'checkbox' ? el.checked : el.value;
    if (el.dataset.money !== undefined || ['gastosPct', 'descuento'].includes(bind)) valor = num(valor);
    if (bind === 'vehiculo.patente') valor = String(valor).toUpperCase();
    setPath(c, bind, valor);
    tocar(c);
    refrescarTotales(c);
    return;
  }

  // Líneas de trabajos / repuestos / abonos
  const linea = el.closest('[data-linea]');
  if (linea && el.dataset.field) {
    const c = cotActual();
    if (!c) return;
    const item = (c[linea.dataset.linea] || []).find((x) => x.id === linea.dataset.id);
    if (!item) return;
    const campo = el.dataset.field;
    item[campo] = el.type === 'checkbox' ? el.checked
      : (el.dataset.money !== undefined || campo === 'cant') ? num(el.value)
      : el.value;

    // Si el texto coincide con el catálogo y no hay precio, se completa solo.
    if (campo === 'desc') {
      const fuente = linea.dataset.linea === 'trabajos' ? DB.servicios : DB.repuestos;
      const match = fuente.find((x) => x.desc.toLowerCase() === String(el.value).toLowerCase());
      if (match) {
        const precioCampo = linea.dataset.linea === 'trabajos' ? 'mo' : 'unit';
        if (!num(item[precioCampo])) {
          item[precioCampo] = num(match.precio);
          const inp = $('[data-money]', linea);
          if (inp) inp.value = campoMonto(match.precio);
        }
        if (linea.dataset.linea === 'trabajos' && match.obs && !item.obs) {
          item.obs = match.obs;
          const obsInp = $('[data-field="obs"]', linea);
          if (obsInp) obsInp.value = match.obs;
        }
      }
    }
    tocar(c);
    refrescarTotales(c);
    return;
  }

  // Catálogo
  const fila = el.closest('[data-cat]');
  if (fila && el.dataset.cfield) {
    const item = DB[fila.dataset.cat].find((x) => x.id === fila.dataset.id);
    if (!item) return;
    item[el.dataset.cfield] = el.dataset.money !== undefined ? num(el.value) : el.value;
    guardar();
    refrescarDatalists();
    return;
  }

  // Ajustes
  if (el.dataset.aj) {
    const k = el.dataset.aj;
    DB.ajustes[k] = el.type === 'checkbox' ? el.checked
      : ['ivaPct', 'gastosPct', 'validezDias'].includes(k) ? num(el.value)
      : el.value;
    guardar();
    return;
  }
  if (el.dataset.ajLista) {
    DB.ajustes[el.dataset.ajLista] = el.value.split('\n').map((x) => x.trim()).filter(Boolean);
    guardar();
  }
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
  if (el.dataset && el.dataset.money !== undefined) {
    const v = num(el.value);
    el.value = campoMonto(v);
  }
}, true);

document.addEventListener('keydown', (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); abrirCmdK(); return; }
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'p' && location.hash.startsWith('#/doc')) return; // imprimir nativo
  if (e.key === 'Escape') { cerrarModal(); document.body.classList.remove('nav-open'); return; }
  // Enter en la última línea agrega otra (flujo rápido de carga)
  if (e.key === 'Enter' && !e.shiftKey) {
    const linea = e.target.closest?.('[data-linea]');
    if (linea && e.target.tagName === 'INPUT' && e.target.type !== 'checkbox') {
      const cont = linea.parentElement;
      const filas = $$('.line', cont);
      if (filas[filas.length - 1] === linea) {
        e.preventDefault();
        $(`[data-add="${linea.dataset.linea}"]`)?.click();
      }
    }
  }
});

window.addEventListener('hashchange', render);

/* ---------------------------------------------------------------- arranque */
function iniciar() {
  DB = cargar();
  guardar(true);
  aplicarTema(localStorage.getItem('vittorio.tema') || 'auto');
  refrescarDatalists();
  render();

  if ('serviceWorker' in navigator && location.protocol.startsWith('http')) {
    navigator.serviceWorker.register('sw.js').catch(() => { /* sin conexión offline, no pasa nada */ });
  }
}

document.addEventListener('DOMContentLoaded', iniciar);
