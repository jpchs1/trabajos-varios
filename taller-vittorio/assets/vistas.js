/* ==========================================================================
   Vistas: tablero, cotizaciones, clientes, vehículos, catálogo y ajustes.
   ========================================================================== */

/* ================================================================ TABLERO */
ruta(/^\/$/, () => {
  document.title = 'Tablero · Taller Vittorio';
  const cots = DB.cotizaciones.filter((c) => c.estado !== 'anulada');
  const mesActual = new Date().toISOString().slice(0, 7);
  const mesAnterior = sumarMeses(hoyISO(), -1).slice(0, 7);

  const delMes = (m) => cots.filter((c) => (c.fechaIngreso || c.creada || '').slice(0, 7) === m);
  const montoMes = (m) => delMes(m).reduce((a, c) => a + calc(c).total, 0);

  const actual = montoMes(mesActual);
  const previo = montoMes(mesAnterior);
  const variacion = previo ? Math.round(((actual - previo) / previo) * 100) : null;

  const enCurso = cots.filter((c) => c.estado === 'aprobada' || c.estado === 'taller');
  const porCobrar = cots.filter((c) => ['aprobada', 'taller', 'entregada'].includes(c.estado))
    .reduce((a, c) => a + Math.max(0, calc(c).saldo), 0);
  const seguir = necesitanSeguimiento();
  const mantenciones = mantencionesPendientes();

  setVista(`
    ${topbar({
      titulo: `Hola, ${h(primerNombreTaller())}`,
      sub: `${new Date().toLocaleDateString('es-CL', { weekday: 'long', day: 'numeric', month: 'long' })}`,
      acciones: `
        <button class="btn btn-soft btn-buscar" data-cmdk>${icon('search', 'i i-sm')} <span>Buscar</span> <kbd>⌘K</kbd></button>
        <button class="btn btn-primary" data-nueva>${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nueva cotización</span></button>`
    })}
    <div class="view">
      <div class="kpis">
        ${kpi('Cotizado este mes', actual, `${delMes(mesActual).length} cotizaciones`, 'chart', variacion)}
        ${kpi('Por cobrar', porCobrar, 'Aprobadas, en taller y entregadas', 'wallet')}
        ${kpi('En el taller', enCurso.length, 'Trabajos aprobados o en curso', 'wrench', null, String(enCurso.length))}
        ${kpi('Clientes', DB.clientes.length, `${DB.vehiculos.length} ${DB.vehiculos.length === 1 ? 'vehículo registrado' : 'vehículos registrados'}`, 'user', null, String(DB.clientes.length))}
      </div>

      <div class="cols-2">
        ${cardActividad(cots)}
        ${cardAgenda(enCurso)}
      </div>

      ${seguir.length ? cardSeguimiento(seguir) : ''}
      ${mantenciones.length ? cardMantenciones(mantenciones) : ''}

      <section class="card">
        <div class="card-head">
          <div>${icon('file')}</div>
          <div><h2>Últimas cotizaciones</h2><div class="sub">Lo más reciente primero</div></div>
          <div class="spacer"></div>
          <a class="btn btn-sm" href="#/cotizaciones">Ver todas ${icon('next', 'i i-sm')}</a>
        </div>
        <div class="card-body">
          ${DB.cotizaciones.length
            ? `<div class="cot-list">${[...DB.cotizaciones].sort(porActualizacion).slice(0, 5).map(tarjetaCot).join('')}</div>`
            : vacio('file', 'Todavía no hay cotizaciones',
                'Creá la primera: cargás el vehículo, elegís los trabajos del catálogo y el total se arma solo.',
                `<button class="btn btn-primary" data-nueva>${icon('plus', 'i i-sm')} Nueva cotización</button>`)}
        </div>
      </section>
    </div>`);

  // Los KPIs de plata cuentan hasta su valor al entrar a la vista.
  $$('[data-count]').forEach((el) => animarNumero(el, num(el.dataset.count), el.dataset.fmt === 'n' ? (v) => String(Math.round(v)) : money));
});

const porActualizacion = (a, b) => String(b.actualizada || '').localeCompare(String(a.actualizada || ''));

function primerNombreTaller() {
  const t = DB.ajustes.taller || 'Vittorio';
  return t.replace(/^taller\s+(automotriz\s+)?/i, '').split(' ')[0];
}

function kpi(label, valor, pie, ico, variacion = null, texto = null) {
  const esNumero = texto === null;
  return `
    <div class="kpi">
      <div class="k-icon">${icon(ico, 'i i-sm')}</div>
      <div class="k-label">${h(label)}</div>
      <div class="k-value" ${esNumero ? `data-count="${valor}"` : ''}>${texto !== null ? h(texto) : money(0)}</div>
      <div class="k-foot">
        ${variacion !== null ? `<span class="delta ${variacion >= 0 ? 'is-up' : 'is-down'}">${icon(variacion >= 0 ? 'up' : 'down', 'i i-sm')}${Math.abs(variacion)}%</span>` : ''}
        ${h(pie)}
      </div>
    </div>`;
}

/* --------------------------------------------- gráfico de los últimos meses */
function cardActividad(cots) {
  const meses = [];
  for (let i = 5; i >= 0; i--) {
    const iso = sumarMeses(hoyISO(), -i);
    const clave = iso.slice(0, 7);
    const monto = cots.filter((c) => (c.fechaIngreso || c.creada || '').slice(0, 7) === clave)
      .reduce((a, c) => a + calc(c).total, 0);
    const n = cots.filter((c) => (c.fechaIngreso || c.creada || '').slice(0, 7) === clave).length;
    meses.push({ clave, etiqueta: MESES[+clave.slice(5, 7) - 1], anio: clave.slice(0, 4), monto, n, actual: i === 0 });
  }
  const max = Math.max(...meses.map((m) => m.monto), 1);
  const total = meses.reduce((a, m) => a + m.monto, 0);

  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('chart')}</div>
        <div><h2>Cotizado por mes</h2><div class="sub">Últimos 6 meses · ${money(total)}</div></div>
      </div>
      <div class="card-body">
        <div class="chart" role="img" aria-label="Monto cotizado por mes: ${meses.map((m) => `${m.etiqueta} ${money(m.monto)}`).join(', ')}">
          ${meses.map((m) => `
            <div class="cbar ${m.actual ? 'is-actual' : ''}" tabindex="0"
                 data-tip="${h(m.etiqueta)} ${h(m.anio)} · ${h(money(m.monto))} · ${m.n} ${m.n === 1 ? 'cotización' : 'cotizaciones'}">
              <div class="cbar-track">
                <div class="cbar-fill" style="height:${m.monto ? Math.max(3, (m.monto / max) * 100) : 0}%"></div>
              </div>
              <span class="cbar-x">${h(m.etiqueta)}</span>
            </div>`).join('')}
        </div>
        <div class="chart-foot">
          <span class="faint">${h(meses[0].etiqueta)} — ${h(meses[5].etiqueta)}</span>
          <span class="faint"><span class="chart-key"></span> mes en curso</span>
          <span class="faint tr" style="margin-left:auto">Máximo ${montoCorto(max)}</span>
        </div>
        <table class="sr">
          <caption>Monto cotizado por mes</caption>
          <tbody>${meses.map((m) => `<tr><th>${m.etiqueta} ${m.anio}</th><td>${money(m.monto)}</td></tr>`).join('')}</tbody>
        </table>
      </div>
    </section>`;
}

function cardAgenda(enCurso) {
  const ordenadas = [...enCurso].sort((a, b) => String(a.fechaEntrega || '9999').localeCompare(String(b.fechaEntrega || '9999')));
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('wrench')}</div>
        <div><h2>En el taller</h2><div class="sub">Trabajos aprobados o en curso</div></div>
      </div>
      <div class="card-body ${ordenadas.length ? 'tight' : ''}">
        ${ordenadas.length
          ? `<div class="mini-list">${ordenadas.slice(0, 6).map((c) => {
              const atrasada = c.fechaEntrega && c.fechaEntrega < hoyISO();
              return `
              <button class="mini-row" data-abrir="${c.id}">
                <span class="mini-plate">${h(patente(c.vehiculo?.patente))}</span>
                <span class="mini-main">
                  <b>${h(c.vehiculo?.marcaModelo || c.folio)}</b>
                  <small>${h(c.cliente?.nombre || 'Sin cliente')} · ${badgeEstadoTexto(c.estado)}</small>
                </span>
                <span class="mini-right ${atrasada ? 'is-late' : ''}">
                  ${c.fechaEntrega ? (atrasada ? 'Atrasada' : fecha(c.fechaEntrega)) : 'Sin fecha'}
                </span>
              </button>`;
            }).join('')}</div>`
          : vacio('check', 'Nada en curso', 'Cuando apruebes una cotización, el trabajo aparece acá con su fecha de entrega.')}
      </div>
    </section>`;
}

const badgeEstadoTexto = (e) => (ESTADOS[e] || ESTADOS.borrador).label;

function cardSeguimiento(lista) {
  return `
    <section class="card card-alerta">
      <div class="card-head">
        <div style="color:var(--warn)">${icon('bell')}</div>
        <div><h2>Necesitan seguimiento</h2><div class="sub">Enviadas hace ${h(DB.ajustes.seguimientoDias)} días o más, sin respuesta</div></div>
        <div class="spacer"></div>
        <span class="badge badge-taller">${lista.length}</span>
      </div>
      <div class="card-body tight">
        <div class="mini-list">
          ${lista.slice(0, 5).map((c) => `
            <div class="mini-row is-static">
              <span class="mini-plate">${h(patente(c.vehiculo?.patente))}</span>
              <span class="mini-main">
                <b>${h(c.cliente?.nombre || c.vehiculo?.marcaModelo || c.folio)}</b>
                <small>${h(c.folio)} · ${h(money(calc(c).total))} · ${h(desdeAhora(c.actualizada))}</small>
              </span>
              <span class="mini-acts">
                <button class="btn btn-sm" data-seguimiento="${c.id}" title="Recordatorio por WhatsApp">${icon('chat', 'i i-sm')}</button>
                <button class="btn btn-sm" data-abrir="${c.id}">Abrir</button>
              </span>
            </div>`).join('')}
        </div>
      </div>
    </section>`;
}

function mantencionesPendientes() {
  return DB.vehiculos
    .map((v) => ({ v, m: proximaMantencion(v) }))
    .filter((x) => x.m && x.m.vencida)
    .sort((a, b) => a.m.fecha.localeCompare(b.m.fecha));
}

function cardMantenciones(lista) {
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('clock')}</div>
        <div><h2>Mantenciones que tocan</h2><div class="sub">Vehículos que no vuelven hace ${h(DB.ajustes.mantencionMeses)} meses</div></div>
        <div class="spacer"></div>
        <a class="btn btn-sm" href="#/vehiculos">Ver vehículos</a>
      </div>
      <div class="card-body tight">
        <div class="mini-list">
          ${lista.slice(0, 5).map(({ v, m }) => {
            const cli = getCliente(v.clienteId);
            return `
            <div class="mini-row is-static">
              <span class="mini-plate">${h(patente(v.patente))}</span>
              <span class="mini-main">
                <b>${h(v.marcaModelo || 'Vehículo')}</b>
                <small>${h(cli?.nombre || 'Sin cliente')} · última atención ${h(fecha(m.ultima.fechaIngreso))}</small>
              </span>
              <span class="mini-acts">
                ${cli?.telefono ? `<a class="btn btn-sm" href="https://wa.me/${telWa(cli.telefono)}?text=${encodeURIComponent(`Hola${cli.nombre ? ' ' + cli.nombre.split(' ')[0] : ''}, le escribimos de ${DB.ajustes.taller}. Su ${v.marcaModelo || 'vehículo'} (${patente(v.patente)}) ya cumple el plazo de mantención. ¿Le agendamos una revisión?`)}" target="_blank" rel="noopener" title="Avisar por WhatsApp">${icon('chat', 'i i-sm')}</a>` : ''}
                <a class="btn btn-sm" href="#/vehiculo/${v.id}">Ficha</a>
              </span>
            </div>`;
          }).join('')}
        </div>
      </div>
    </section>`;
}

/* =========================================================== COTIZACIONES */
const filtro = { q: '', estado: 'todas', orden: 'reciente' };

ruta(/^\/cotizaciones$/, () => {
  document.title = 'Cotizaciones · Taller Vittorio';
  const conteo = {};
  Object.keys(ESTADOS).forEach((k) => { conteo[k] = DB.cotizaciones.filter((c) => c.estado === k).length; });

  setVista(`
    ${topbar({
      titulo: 'Cotizaciones',
      sub: `${DB.cotizaciones.length} en total`,
      acciones: `
        <button class="btn btn-soft btn-buscar" data-cmdk>${icon('search', 'i i-sm')} <span>Buscar</span> <kbd>⌘K</kbd></button>
        <button class="btn btn-primary" data-nueva>${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nueva cotización</span></button>`
    })}
    <div class="view">
      <section class="card">
        <div class="card-head col-movil">
          <div class="search">
            ${icon('search')}
            <input class="input" id="q" placeholder="Buscar por patente, cliente, folio o vehículo…" value="${h(filtro.q)}" autocomplete="off">
          </div>
          <div class="chips">
            <button class="chip-btn" data-filtro="todas" aria-pressed="${filtro.estado === 'todas'}">Todas</button>
            ${Object.entries(ESTADOS).map(([k, v]) => `
              <button class="chip-btn" data-filtro="${k}" aria-pressed="${filtro.estado === k}">${v.label}${conteo[k] ? ` · ${conteo[k]}` : ''}</button>`).join('')}
          </div>
          <select class="select select-sm" id="orden" aria-label="Ordenar">
            <option value="reciente" ${filtro.orden === 'reciente' ? 'selected' : ''}>Más recientes</option>
            <option value="monto" ${filtro.orden === 'monto' ? 'selected' : ''}>Mayor monto</option>
            <option value="cliente" ${filtro.orden === 'cliente' ? 'selected' : ''}>Cliente A-Z</option>
          </select>
        </div>
        <div class="card-body" id="lista"></div>
      </section>
    </div>`);

  pintarLista();
});

function pintarLista() {
  const cont = $('#lista');
  if (!cont) return;
  const q = filtro.q.trim().toLowerCase();
  let cots = [...DB.cotizaciones];
  if (filtro.estado !== 'todas') cots = cots.filter((c) => c.estado === filtro.estado);
  if (q) {
    cots = cots.filter((c) => [c.folio, c.cliente?.nombre, c.cliente?.telefono, c.vehiculo?.patente, c.vehiculo?.marcaModelo, c.motivo]
      .join(' ').toLowerCase().includes(q));
  }
  cots.sort(filtro.orden === 'monto'
    ? (a, b) => calc(b).total - calc(a).total
    : filtro.orden === 'cliente'
      ? (a, b) => (a.cliente?.nombre || 'zzz').localeCompare(b.cliente?.nombre || 'zzz')
      : porActualizacion);

  cont.innerHTML = cots.length
    ? `<div class="cot-list">${cots.map(tarjetaCot).join('')}</div>`
    : vacio('search', 'Sin resultados', 'Probá con otra patente, cliente o estado.');
}

function tarjetaCot(c) {
  const t = calc(c);
  const hechos = (c.trabajos || []).filter((x) => x.realizado).length;
  const totalT = (c.trabajos || []).filter((x) => (x.desc || '').trim()).length;
  const avance = totalT ? Math.round((hechos / totalT) * 100) : 0;
  const seguimiento = c.estado === 'enviada' && diasDesde(c.actualizada) >= num(DB.ajustes.seguimientoDias);
  return `
    <button class="cot-card" data-abrir="${c.id}">
      <span class="plate mono">${h(patente(c.vehiculo?.patente))}</span>
      <span class="cot-main">
        <span class="t1">
          ${h(c.vehiculo?.marcaModelo || 'Vehículo sin identificar')}
          ${badgeEstado(c.estado)}
          ${seguimiento ? `<span class="badge badge-taller">${icon('bell', 'i i-sm')} sin respuesta</span>` : ''}
        </span>
        <span class="t2">
          <span>${h(c.folio)}</span>
          ${c.cliente?.nombre ? `<span>${h(c.cliente.nombre)}</span>` : ''}
          ${totalT ? `<span>${totalT} trabajos</span>` : ''}
          <span>${h(desdeAhora(c.actualizada))}</span>
        </span>
        ${avance && c.estado === 'taller' ? `<span class="bar"><span style="width:${avance}%"></span></span>` : ''}
      </span>
      <span class="amount">
        <b>${money(t.total)}</b>
        <small>${t.abonado ? `saldo ${money(t.saldo)}` : fecha(c.fechaIngreso)}</small>
      </span>
    </button>`;
}

/* =============================================================== CLIENTES */
const filtroCli = { q: '' };

ruta(/^\/clientes$/, () => {
  document.title = 'Clientes · Taller Vittorio';
  setVista(`
    ${topbar({
      titulo: 'Clientes',
      sub: `${DB.clientes.length} fichas`,
      acciones: `<button class="btn btn-primary" data-nuevo-cliente>${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nuevo cliente</span></button>`
    })}
    <div class="view">
      <section class="card">
        <div class="card-head">
          <div class="search">
            ${icon('search')}
            <input class="input" id="qcli" placeholder="Buscar por nombre o teléfono…" value="${h(filtroCli.q)}" autocomplete="off">
          </div>
        </div>
        <div class="card-body" id="listaCli"></div>
      </section>
    </div>`);
  pintarClientes();
});

function pintarClientes() {
  const cont = $('#listaCli');
  if (!cont) return;
  const q = normTexto(filtroCli.q);
  const lista = DB.clientes
    .filter((c) => !q || normTexto(c.nombre + ' ' + c.telefono + ' ' + c.email).includes(q))
    .map((c) => ({ c, r: resumenDe(cotsDeCliente(c.id)), v: vehiculosDeCliente(c.id) }))
    .sort((a, b) => String(b.r.ultima?.fechaIngreso || '').localeCompare(String(a.r.ultima?.fechaIngreso || '')));

  cont.innerHTML = lista.length
    ? `<div class="ficha-grid">${lista.map(({ c, r, v }) => `
        <a class="ficha-card" href="#/cliente/${c.id}">
          <span class="avatar">${h(iniciales(c.nombre))}</span>
          <span class="ficha-main">
            <b>${h(c.nombre || 'Sin nombre')}</b>
            <small>${h(c.telefono || 'Sin teléfono')}</small>
            <span class="ficha-tags">
              <span class="pill">${v.length} ${v.length === 1 ? 'vehículo' : 'vehículos'}</span>
              <span class="pill">${r.n} ${r.n === 1 ? 'atención' : 'atenciones'}</span>
              ${r.saldo > 0 ? `<span class="pill is-warn">debe ${montoCorto(r.saldo)}</span>` : ''}
            </span>
          </span>
          <span class="ficha-right">
            <b>${montoCorto(r.facturado)}</b>
            <small>${r.ultima ? h(desdeAhora(r.ultima.fechaIngreso)) : 'sin visitas'}</small>
          </span>
        </a>`).join('')}</div>`
    : vacio('user', filtroCli.q ? 'Sin resultados' : 'Todavía no hay clientes',
        filtroCli.q ? 'Probá con otro nombre o teléfono.' : 'Las fichas se crean solas cuando cargás el nombre del cliente en una cotización.',
        `<button class="btn btn-primary" data-nuevo-cliente>${icon('plus', 'i i-sm')} Nuevo cliente</button>`);
}

ruta(/^\/cliente\/(.+)$/, (id) => {
  const cli = getCliente(id);
  if (!cli) { toast('Esa ficha ya no existe', 'err'); return navegar('#/clientes'); }
  document.title = `${cli.nombre || 'Cliente'} · Taller Vittorio`;
  const cots = cotsDeCliente(id);
  const r = resumenDe(cots);
  const vehs = vehiculosDeCliente(id);

  setVista(`
    ${topbar({
      titulo: h(cli.nombre || 'Cliente sin nombre'),
      sub: `${cots.length} ${cots.length === 1 ? 'cotización' : 'cotizaciones'} · ${vehs.length} ${vehs.length === 1 ? 'vehículo' : 'vehículos'}`,
      atras: '#/clientes',
      acciones: `
        ${cli.telefono ? `<a class="btn" href="https://wa.me/${telWa(cli.telefono)}" target="_blank" rel="noopener">${icon('chat', 'i i-sm')} <span class="ocultar-movil">WhatsApp</span></a>` : ''}
        <button class="btn btn-primary" data-nueva-cli="${cli.id}">${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nueva cotización</span></button>`
    })}
    <div class="view">
      <div class="kpis">
        ${kpi(r.facturado ? 'Facturado' : 'Cotizado', r.facturado || r.cotizado,
          r.facturado ? 'Aprobadas, en taller y entregadas' : 'Todavía sin trabajos aprobados', 'wallet')}
        ${kpi('Saldo pendiente', r.saldo, r.saldo ? 'Por cobrar' : 'Al día', 'money')}
        ${kpi('Atenciones', r.n, r.ultima ? `Última ${desdeAhora(r.ultima.fechaIngreso)}` : 'Sin visitas', 'wrench', null, String(r.n))}
      </div>

      <div class="cols-2">
        <section class="card">
          <div class="card-head"><div>${icon('user')}</div><div><h2>Datos del cliente</h2><div class="sub">Se actualizan en sus cotizaciones</div></div></div>
          <div class="card-body grid">
            <div class="grid grid-2">
              <div class="field"><label for="c1">Nombre</label><input class="input" id="c1" data-cli="${cli.id}" data-cfld="nombre" value="${h(cli.nombre)}"></div>
              <div class="field"><label for="c2">Teléfono</label><input class="input" id="c2" data-cli="${cli.id}" data-cfld="telefono" value="${h(cli.telefono)}" inputmode="tel"></div>
            </div>
            <div class="grid grid-2">
              <div class="field"><label for="c3">Email</label><input class="input" id="c3" data-cli="${cli.id}" data-cfld="email" value="${h(cli.email)}" inputmode="email"></div>
              <div class="field"><label for="c4">RUT</label><input class="input mono" id="c4" data-cli="${cli.id}" data-cfld="rut" value="${h(cli.rut)}"></div>
            </div>
            <div class="field"><label for="c5">Notas internas</label><textarea class="input" id="c5" rows="2" data-cli="${cli.id}" data-cfld="notas" placeholder="Prefiere que lo llamen en la tarde, paga con transferencia…">${h(cli.notas)}</textarea></div>
            <div><button class="btn btn-danger btn-sm" data-del-cli="${cli.id}">${icon('trash', 'i i-sm')} Eliminar ficha</button></div>
          </div>
        </section>

        <section class="card">
          <div class="card-head"><div>${icon('car')}</div><div><h2>Vehículos</h2><div class="sub">${vehs.length} en la ficha</div></div></div>
          <div class="card-body ${vehs.length ? 'tight' : ''}">
            ${vehs.length ? `<div class="mini-list">${vehs.map((v) => {
              const m = proximaMantencion(v);
              return `
              <a class="mini-row" href="#/vehiculo/${v.id}">
                <span class="mini-plate">${h(patente(v.patente))}</span>
                <span class="mini-main">
                  <b>${h(v.marcaModelo || 'Sin modelo')}</b>
                  <small>${h(v.anio || '')}${v.km ? ` · ${miles(v.km)} km` : ''}</small>
                </span>
                <span class="mini-right ${m?.vencida ? 'is-late' : ''}">${m ? (m.vencida ? 'Toca mantención' : `Próxima ${fecha(m.fecha)}`) : ''}</span>
              </a>`;
            }).join('')}</div>` : vacio('car', 'Sin vehículos', 'Al cargar una patente en una cotización de este cliente, el auto queda acá.')}
          </div>
        </section>
      </div>

      <section class="card">
        <div class="card-head"><div>${icon('file')}</div><div><h2>Historial de cotizaciones</h2></div></div>
        <div class="card-body">
          ${cots.length ? `<div class="cot-list">${cots.map(tarjetaCot).join('')}</div>`
            : vacio('file', 'Sin cotizaciones', 'Creá la primera para este cliente.',
                `<button class="btn btn-primary" data-nueva-cli="${cli.id}">${icon('plus', 'i i-sm')} Nueva cotización</button>`)}
        </div>
      </section>
    </div>`);

  $$('[data-count]').forEach((el) => animarNumero(el, num(el.dataset.count)));
});

/* ============================================================== VEHÍCULOS */
const filtroVeh = { q: '' };

ruta(/^\/vehiculos$/, () => {
  document.title = 'Vehículos · Taller Vittorio';
  setVista(`
    ${topbar({ titulo: 'Vehículos', sub: `${DB.vehiculos.length} patentes registradas` })}
    <div class="view">
      <section class="card">
        <div class="card-head">
          <div class="search">
            ${icon('search')}
            <input class="input" id="qveh" placeholder="Buscar por patente o modelo…" value="${h(filtroVeh.q)}" autocomplete="off">
          </div>
        </div>
        <div class="card-body" id="listaVeh"></div>
      </section>
    </div>`);
  pintarVehiculos();
});

function pintarVehiculos() {
  const cont = $('#listaVeh');
  if (!cont) return;
  const q = normTexto(filtroVeh.q);
  const lista = DB.vehiculos
    .filter((v) => !q || normTexto(v.patente + ' ' + v.marcaModelo).includes(q))
    .map((v) => ({ v, r: resumenDe(cotsDeVehiculo(v.id)), m: proximaMantencion(v), cli: getCliente(v.clienteId) }))
    .sort((a, b) => String(b.r.ultima?.fechaIngreso || '').localeCompare(String(a.r.ultima?.fechaIngreso || '')));

  cont.innerHTML = lista.length
    ? `<div class="ficha-grid">${lista.map(({ v, r, m, cli }) => `
        <a class="ficha-card" href="#/vehiculo/${v.id}">
          <span class="plate-big mono">${h(patente(v.patente))}</span>
          <span class="ficha-main">
            <b>${h(v.marcaModelo || 'Sin modelo')}</b>
            <small>${h(cli?.nombre || 'Sin cliente')}${v.km ? ` · ${miles(v.km)} km` : ''}</small>
            <span class="ficha-tags">
              <span class="pill">${r.n} ${r.n === 1 ? 'atención' : 'atenciones'}</span>
              ${m?.vencida ? `<span class="pill is-warn">${icon('clock', 'i i-sm')} toca mantención</span>` : ''}
            </span>
          </span>
          <span class="ficha-right">
            <b>${montoCorto(r.facturado)}</b>
            <small>${r.ultima ? h(desdeAhora(r.ultima.fechaIngreso)) : 'sin visitas'}</small>
          </span>
        </a>`).join('')}</div>`
    : vacio('car', filtroVeh.q ? 'Sin resultados' : 'Todavía no hay vehículos',
        filtroVeh.q ? 'Probá con otra patente.' : 'Cada patente que cargues en una cotización queda con su ficha y su historial.');
}

ruta(/^\/vehiculo\/(.+)$/, (id) => {
  const v = getVehiculo(id);
  if (!v) { toast('Esa ficha ya no existe', 'err'); return navegar('#/vehiculos'); }
  document.title = `${patente(v.patente)} · Taller Vittorio`;
  const cots = cotsDeVehiculo(id);
  const r = resumenDe(cots);
  const m = proximaMantencion(v);
  const cli = getCliente(v.clienteId);
  const trabajosHechos = cots.flatMap((c) => (c.trabajos || [])
    .filter((t) => (t.desc || '').trim())
    .map((t) => ({ desc: t.desc, fecha: c.fechaIngreso, km: c.vehiculo?.km, folio: c.folio, cotId: c.id })));

  setVista(`
    ${topbar({
      titulo: `${h(v.marcaModelo || 'Vehículo')}`,
      sub: `${h(patente(v.patente))}${v.anio ? ` · ${h(v.anio)}` : ''}${cli ? ` · ${h(cli.nombre)}` : ''}`,
      atras: '#/vehiculos',
      acciones: `<button class="btn btn-primary" data-nueva-veh="${v.id}">${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nueva atención</span></button>`
    })}
    <div class="view">
      <div class="kpis">
        ${kpi(r.facturado ? 'Gastado en el taller' : 'Cotizado', r.facturado || r.cotizado,
          r.facturado ? `${r.n} ${r.n === 1 ? 'atención' : 'atenciones'}` : 'Todavía sin trabajos aprobados', 'wallet')}
        ${kpi('Kilometraje', num(v.km), r.ultima ? `Al ${fecha(r.ultima.fechaIngreso)}` : '—', 'gauge', null, v.km ? miles(v.km) + ' km' : '—')}
        ${kpi('Próxima mantención', 0, m ? (m.vencida ? 'Ya corresponde' : `o a los ${miles(m.km)} km`) : 'Sin atenciones previas', 'clock', null, m ? fecha(m.fecha) : '—')}
      </div>

      ${m?.vencida ? `
      <div class="aviso">
        ${icon('bell')}
        <div><b>Este vehículo ya cumple el plazo de mantención.</b> Última atención ${h(fecha(m.ultima.fechaIngreso))}${cli?.telefono ? '. Podés avisarle al cliente por WhatsApp.' : '.'}</div>
        ${cli?.telefono ? `<a class="btn btn-sm" target="_blank" rel="noopener" href="https://wa.me/${telWa(cli.telefono)}?text=${encodeURIComponent(`Hola${cli.nombre ? ' ' + cli.nombre.split(' ')[0] : ''}, le escribimos de ${DB.ajustes.taller}. Su ${v.marcaModelo || 'vehículo'} (${patente(v.patente)}) ya cumple el plazo de mantención. ¿Le agendamos una revisión?`)}">${icon('chat', 'i i-sm')} Avisar</a>` : ''}
      </div>` : ''}

      <div class="cols-2">
        <section class="card">
          <div class="card-head"><div>${icon('car')}</div><div><h2>Ficha del vehículo</h2></div></div>
          <div class="card-body grid">
            <div class="grid grid-2">
              <div class="field"><label for="v1">Patente</label><input class="input mono" id="v1" data-veh="${v.id}" data-vfld="patente" value="${h(v.patente)}" style="text-transform:uppercase"></div>
              <div class="field"><label for="v2">Marca y modelo</label><input class="input" id="v2" data-veh="${v.id}" data-vfld="marcaModelo" value="${h(v.marcaModelo)}"></div>
            </div>
            <div class="grid grid-3">
              <div class="field"><label for="v3">Año</label><input class="input" id="v3" data-veh="${v.id}" data-vfld="anio" value="${h(v.anio)}" inputmode="numeric"></div>
              <div class="field"><label for="v4">Color</label><input class="input" id="v4" data-veh="${v.id}" data-vfld="color" value="${h(v.color)}"></div>
              <div class="field"><label for="v5">Km actual</label><input class="input" id="v5" data-veh="${v.id}" data-vfld="km" value="${h(v.km)}" inputmode="numeric"></div>
            </div>
            <div class="field"><label for="v6">VIN / chasis</label><input class="input mono" id="v6" data-veh="${v.id}" data-vfld="vin" value="${h(v.vin)}"></div>
            <div class="field"><label for="v7">Notas del vehículo</label><textarea class="input" id="v7" rows="2" data-veh="${v.id}" data-vfld="notas" placeholder="Traía ruido en tren delantero, cliente pidió no tocar el equipo de música…">${h(v.notas)}</textarea></div>
            <div class="toolbar">
              ${cli ? `<a class="btn btn-sm" href="#/cliente/${cli.id}">${icon('user', 'i i-sm')} Ver cliente</a>` : ''}
              <button class="btn btn-danger btn-sm" data-del-veh="${v.id}">${icon('trash', 'i i-sm')} Eliminar ficha</button>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-head"><div>${icon('list')}</div><div><h2>Trabajos hechos</h2><div class="sub">Historial mecánico</div></div></div>
          <div class="card-body ${trabajosHechos.length ? 'tight' : ''}" style="max-height:420px;overflow:auto">
            ${trabajosHechos.length
              ? `<div class="hist">${trabajosHechos.slice(0, 30).map((t) => `
                  <div class="hist-row">
                    <span class="hist-dot"></span>
                    <div>
                      <b>${h(t.desc)}</b>
                      <small>${h(fecha(t.fecha))}${t.km ? ` · ${miles(t.km)} km` : ''} · ${h(t.folio)}</small>
                    </div>
                  </div>`).join('')}</div>`
              : vacio('list', 'Sin trabajos registrados', 'Van apareciendo a medida que cargás cotizaciones para esta patente.')}
          </div>
        </section>
      </div>

      <section class="card">
        <div class="card-head"><div>${icon('file')}</div><div><h2>Atenciones</h2></div></div>
        <div class="card-body">
          ${cots.length ? `<div class="cot-list">${cots.map(tarjetaCot).join('')}</div>`
            : vacio('file', 'Sin atenciones', 'Todavía no cotizaste nada para este vehículo.',
              `<button class="btn btn-primary" data-nueva-veh="${v.id}">${icon('plus', 'i i-sm')} Nueva atención</button>`)}
        </div>
      </section>
    </div>`);

  $$('[data-count]').forEach((el) => animarNumero(el, num(el.dataset.count)));
});

/* =============================================================== CATÁLOGO */
ruta(/^\/catalogo$/, () => {
  document.title = 'Catálogo · Taller Vittorio';
  setVista(`
    ${topbar({
      titulo: 'Catálogo de precios',
      sub: 'Lo que cargás acá aparece al cotizar',
      acciones: `<button class="btn btn-primary" data-cat-add="servicios">${icon('plus', 'i i-sm')} <span class="ocultar-movil">Nuevo servicio</span></button>`
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
          <th style="width:44px"><span class="sr">Acciones</span></th>
        </tr>
      </thead>
      <tbody>
        ${items.map((it) => `
          <tr data-cat="${tipo}" data-id="${it.id}">
            <td><input class="input input-flush" data-cfield="cat" value="${h(it.cat || '')}" placeholder="Categoría" aria-label="Categoría"></td>
            <td><input class="input input-flush" data-cfield="desc" value="${h(it.desc || '')}" placeholder="Descripción" aria-label="Descripción"></td>
            ${esServicio ? `<td><input class="input input-flush" data-cfield="obs" value="${h(it.obs || '')}" placeholder="—" aria-label="Observación"></td>` : ''}
            <td><input class="input input-flush input-money" data-cfield="precio" data-money value="${campoMonto(it.precio)}" inputmode="numeric" aria-label="Precio"></td>
            <td><button class="btn btn-ghost btn-icon" data-cat-del title="Eliminar">${icon('trash', 'i i-sm')}</button></td>
          </tr>`).join('')}
      </tbody>
    </table>`;
}

/* ================================================================ AJUSTES */
ruta(/^\/ajustes$/, () => {
  document.title = 'Ajustes · Taller Vittorio';
  const a = DB.ajustes;
  const kb = Math.round(pesoDatos() / 1024);
  const pct = Math.min(100, Math.round((kb / 5000) * 100));

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
            <div class="field"><label for="b1">IVA (%)</label><input class="input" id="b1" data-aj="ivaPct" value="${h(a.ivaPct)}" inputmode="decimal"></div>
            <div class="field"><label for="b2">Gastos (%)</label><input class="input" id="b2" data-aj="gastosPct" value="${h(a.gastosPct)}" inputmode="decimal"></div>
            <div class="field"><label for="b3">Validez (días)</label><input class="input" id="b3" data-aj="validezDias" value="${h(a.validezDias)}" inputmode="numeric"></div>
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
        <div class="card-head"><div>${icon('bell')}</div><div><h2>Seguimiento y mantenciones</h2><div class="sub">Los avisos del tablero</div></div></div>
        <div class="card-body grid grid-3">
          <div class="field">
            <label for="s1">Avisar sin respuesta (días)</label>
            <input class="input" id="s1" data-aj="seguimientoDias" value="${h(a.seguimientoDias)}" inputmode="numeric">
            <span class="hint">Marca las enviadas que llevan más de ese tiempo.</span>
          </div>
          <div class="field">
            <label for="s2">Mantención cada (meses)</label>
            <input class="input" id="s2" data-aj="mantencionMeses" value="${h(a.mantencionMeses)}" inputmode="numeric">
          </div>
          <div class="field">
            <label for="s3">Mantención cada (km)</label>
            <input class="input" id="s3" data-aj="mantencionKm" value="${h(a.mantencionKm)}" inputmode="numeric">
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head"><div>${icon('shield')}</div><div><h2>Tus datos</h2><div class="sub">Todo queda en este navegador</div></div></div>
        <div class="card-body grid">
          <div class="uso">
            <div class="uso-head"><span>Espacio usado</span><b>${kb} KB de ~5.000 KB</b></div>
            <div class="uso-bar"><span style="width:${Math.max(2, pct)}%" class="${pct > 80 ? 'is-alto' : ''}"></span></div>
            <p class="hint">${DB.cotizaciones.length} cotizaciones · ${DB.clientes.length} clientes · ${DB.vehiculos.length} vehículos · ${DB.cotizaciones.reduce((n, c) => n + (c.fotos || []).length, 0)} fotos.</p>
          </div>
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

/* ------------------------------------------- acciones de fichas maestras -- */
function nuevoCliente() {
  const cli = { id: uid(), nombre: '', telefono: '', email: '', rut: '', notas: '', creado: new Date().toISOString() };
  DB.clientes.push(cli);
  guardar(true);
  navegar(`#/cliente/${cli.id}`);
}

async function eliminarCliente(id) {
  const cli = getCliente(id);
  if (!cli) return;
  const n = cotsDeCliente(id).length;
  const ok = await confirmar('Eliminar ficha', `Se borra la ficha de ${cli.nombre || 'este cliente'}. Sus ${n} ${n === 1 ? 'cotización queda' : 'cotizaciones quedan'} intactas.`, 'Eliminar');
  if (!ok) return;
  DB.clientes = DB.clientes.filter((x) => x.id !== id);
  DB.cotizaciones.forEach((c) => { if (c.clienteId === id) c.clienteId = ''; });
  DB.vehiculos.forEach((v) => { if (v.clienteId === id) v.clienteId = ''; });
  guardar(true);
  navegar('#/clientes');
  toast('Ficha eliminada');
}

async function eliminarVehiculo(id) {
  const v = getVehiculo(id);
  if (!v) return;
  const n = cotsDeVehiculo(id).length;
  const ok = await confirmar('Eliminar ficha', `Se borra la ficha de ${patente(v.patente)}. Sus ${n} ${n === 1 ? 'atención queda' : 'atenciones quedan'} intactas.`, 'Eliminar');
  if (!ok) return;
  DB.vehiculos = DB.vehiculos.filter((x) => x.id !== id);
  DB.cotizaciones.forEach((c) => { if (c.vehiculoId === id) c.vehiculoId = ''; });
  guardar(true);
  navegar('#/vehiculos');
  toast('Ficha eliminada');
}

/** Los cambios en la ficha se copian a las cotizaciones del cliente/vehículo. */
function propagarCliente(cli) {
  DB.cotizaciones.filter((c) => c.clienteId === cli.id).forEach((c) => {
    c.cliente = { nombre: cli.nombre, telefono: cli.telefono, email: cli.email, rut: cli.rut };
  });
}

function propagarVehiculo(veh) {
  DB.cotizaciones.filter((c) => c.vehiculoId === veh.id).forEach((c) => {
    c.vehiculo = Object.assign({}, c.vehiculo, {
      patente: veh.patente, marcaModelo: veh.marcaModelo, anio: veh.anio, color: veh.color, vin: veh.vin
    });
  });
}

/* -------------------------------------------------- eventos de las vistas */
document.addEventListener('click', (e) => {
  const t = e.target;

  const filtroBtn = t.closest('[data-filtro]');
  if (filtroBtn) {
    filtro.estado = filtroBtn.dataset.filtro;
    $$('[data-filtro]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.filtro === filtro.estado)));
    pintarLista();
    return;
  }

  if (t.closest('[data-nuevo-cliente]')) { nuevoCliente(); return; }

  const delCli = t.closest('[data-del-cli]');
  if (delCli) { eliminarCliente(delCli.dataset.delCli); return; }

  const delVeh = t.closest('[data-del-veh]');
  if (delVeh) { eliminarVehiculo(delVeh.dataset.delVeh); return; }

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
    const item = DB[tipo].find((x) => x.id === tr.dataset.id);
    const indice = DB[tipo].indexOf(item);
    DB[tipo].splice(indice, 1);
    guardar();
    $(`#tbl-${tipo}`).innerHTML = tablaCatalogo(tipo);
    refrescarDatalists();
    toast('Ítem eliminado', 'ok', {
      texto: 'Deshacer',
      run: () => { DB[tipo].splice(indice, 0, item); guardar(); $(`#tbl-${tipo}`).innerHTML = tablaCatalogo(tipo); refrescarDatalists(); }
    });
  }
});

/** Entradas de texto de las vistas (buscadores, fichas, catálogo y ajustes). */
function manejarInputVista(el) {
  if (el.id === 'q') { filtro.q = el.value; pintarLista(); return true; }
  if (el.id === 'qcli') { filtroCli.q = el.value; pintarClientes(); return true; }
  if (el.id === 'qveh') { filtroVeh.q = el.value; pintarVehiculos(); return true; }
  if (el.id === 'orden') { filtro.orden = el.value; pintarLista(); return true; }

  if (el.dataset.cli) {
    const cli = getCliente(el.dataset.cli);
    if (cli) { cli[el.dataset.cfld] = el.value; propagarCliente(cli); guardar(); }
    return true;
  }

  if (el.dataset.veh) {
    const veh = getVehiculo(el.dataset.veh);
    if (veh) {
      veh[el.dataset.vfld] = el.dataset.vfld === 'patente' ? normPatente(el.value) : el.value;
      propagarVehiculo(veh);
      guardar();
    }
    return true;
  }

  const fila = el.closest('[data-cat]');
  if (fila && el.dataset.cfield) {
    const item = DB[fila.dataset.cat].find((x) => x.id === fila.dataset.id);
    if (item) {
      item[el.dataset.cfield] = el.dataset.money !== undefined ? num(el.value) : el.value;
      guardar();
      refrescarDatalists();
    }
    return true;
  }

  if (el.dataset.aj) {
    const k = el.dataset.aj;
    DB.ajustes[k] = el.type === 'checkbox' ? el.checked
      : ['ivaPct', 'gastosPct', 'validezDias', 'seguimientoDias', 'mantencionKm', 'mantencionMeses'].includes(k) ? num(el.value)
      : el.value;
    guardar();
    return true;
  }

  if (el.dataset.ajLista) {
    DB.ajustes[el.dataset.ajLista] = el.value.split('\n').map((x) => x.trim()).filter(Boolean);
    guardar();
    return true;
  }

  return false;
}
