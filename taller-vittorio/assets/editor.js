/* ==========================================================================
   Editor de cotización: datos, líneas, fotos, firma y resumen.
   ========================================================================== */

ruta(/^\/cot\/(.+)$/, (id) => {
  const c = getCot(id);
  if (!c) { toast('Esa cotización ya no existe', 'err'); return navegar('#/cotizaciones'); }
  document.title = `${c.folio} · Taller Vittorio`;

  setVista(`
    ${topbar({
      titulo: h(c.vehiculo?.marcaModelo || 'Nueva cotización'),
      sub: `${h(c.folio)} · ${c.vehiculo?.patente ? h(patente(c.vehiculo.patente)) : 'sin patente'}`,
      atras: '#/cotizaciones',
      acciones: `
        <span class="save-state" id="saveState" data-state="saved">${icon('cloud', 'i i-sm')}<span>Guardado</span></span>
        <a class="btn ocultar-movil" href="#/doc/${c.id}">${icon('file', 'i i-sm')} Documento</a>
        <button class="btn btn-primary" data-compartir="${c.id}">${icon('share', 'i i-sm')} <span class="ocultar-movil">Compartir</span></button>`
    })}
    <div class="view">
      <div class="editor">
        <div class="editor-main">
          <div id="bannerVeh">${bannerVehiculo(c)}</div>
          ${cardDatos(c)}
          ${cardTrabajos(c)}
          ${cardRepuestos(c)}
          ${cardAbonos(c)}
          ${cardFotos(c)}
          ${cardNotas(c)}
        </div>
        <aside class="rail">${railResumen(c)}</aside>
      </div>
    </div>
    ${barraMovil(c)}`);
});

/* --------------------------------------------------------- vehículo conocido */
function bannerVehiculo(c) {
  const pat = normPatente(c.vehiculo?.patente);
  if (!pat) return '';
  const v = vehiculoPorPatente(pat);
  if (!v) return '';
  const previas = cotsDeVehiculo(v.id).filter((x) => x.id !== c.id);
  if (!previas.length) return '';
  const cli = getCliente(v.clienteId);
  const faltan = !c.vehiculo.marcaModelo || !c.cliente.nombre;
  return `
    <div class="aviso is-info">
      ${icon('history')}
      <div>
        <b>Este vehículo ya vino ${previas.length} ${previas.length === 1 ? 'vez' : 'veces'}.</b>
        ${h(v.marcaModelo || '')}${cli?.nombre ? ` · ${h(cli.nombre)}` : ''} · última atención ${h(fecha(previas[0].fechaIngreso))}${previas[0].vehiculo?.km ? ` con ${miles(previas[0].vehiculo.km)} km` : ''}.
      </div>
      ${faltan ? `<button class="btn btn-sm" data-usar-veh="${v.id}">Completar datos</button>` : ''}
      <a class="btn btn-sm" href="#/vehiculo/${v.id}">Ver ficha</a>
    </div>`;
}

/* ------------------------------------------------------------------ datos */
function cardDatos(c) {
  return `
    <section class="card">
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
          <div class="field span-2">
            <label for="f-mm">Marca y modelo</label>
            <input class="input" id="f-mm" data-bind="vehiculo.marcaModelo" value="${h(c.vehiculo.marcaModelo)}" placeholder="Jaguar XF 30" autocomplete="off">
          </div>
          <div class="field">
            <label for="f-anio">Año</label>
            <input class="input" id="f-anio" data-bind="vehiculo.anio" value="${h(c.vehiculo.anio)}" placeholder="2012" inputmode="numeric" autocomplete="off">
          </div>
        </div>
        <div class="grid grid-4">
          <div class="field">
            <label for="f-km">Kilometraje</label>
            <input class="input" id="f-km" data-bind="vehiculo.km" value="${h(c.vehiculo.km)}" placeholder="144000" inputmode="numeric" autocomplete="off">
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
            <input class="input" id="f-cli" data-bind="cliente.nombre" value="${h(c.cliente.nombre)}" placeholder="Nombre y apellido" autocomplete="off" list="dl-clientes">
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

/* -------------------------------------------------------------- trabajos  */
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
        <button class="row-del" data-del title="Eliminar línea" aria-label="Eliminar línea">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

/* ------------------------------------------------------------- repuestos  */
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
            <input class="input" data-bind="gastosPct" aria-label="Porcentaje de gastos" value="${h(c.gastosPct)}" inputmode="decimal"
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
        <button class="row-del" data-del title="Eliminar línea" aria-label="Eliminar línea">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

/* ---------------------------------------------------------------- abonos  */
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
        <button class="row-del" data-del title="Eliminar abono" aria-label="Eliminar abono">${icon('trash', 'i i-sm')}</button>
      </div>`).join('')}`;
}

/* ----------------------------------------------------------------- fotos  */
function cardFotos(c) {
  const fotos = c.fotos || [];
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('camera')}</div>
        <div><h2>Fotos del vehículo</h2><div class="sub">Estado de recepción, daños, repuestos cambiados</div></div>
        <div class="spacer"></div>
        <button class="btn btn-sm" data-foto-add>${icon('plus', 'i i-sm')} Agregar fotos</button>
      </div>
      <div class="card-body">
        <div class="fotos" id="fotos">${galeriaFotos(c)}</div>
        ${fotos.length ? `
          <label class="switch" style="margin-top:14px">
            <input type="checkbox" data-bind="incluirFotos" ${c.incluirFotos !== false ? 'checked' : ''}>
            <span class="track"></span><span>Incluir las fotos en el documento impreso</span>
          </label>` : ''}
        <input type="file" id="fotoInput" accept="image/*" capture="environment" multiple class="hidden">
      </div>
    </section>`;
}

function galeriaFotos(c) {
  const fotos = c.fotos || [];
  if (!fotos.length) {
    return `<button class="foto-drop" data-foto-add>
        ${icon('camera', 'i i-lg')}
        <b>Sacá o subí fotos</b>
        <small>Quedan guardadas con la cotización. Sirven para dejar registro de cómo llegó el auto.</small>
      </button>`;
  }
  return fotos.map((f) => `
    <figure class="foto" data-foto="${f.id}">
      <img src="${f.img}" alt="${h(f.nota || 'Foto del vehículo')}" loading="lazy" data-foto-ver="${f.id}">
      <button class="foto-del" data-foto-del="${f.id}" title="Eliminar foto" aria-label="Eliminar foto">${icon('x', 'i i-sm')}</button>
      <figcaption><input class="input input-flush" data-foto-nota="${f.id}" value="${h(f.nota || '')}" placeholder="Nota…" aria-label="Nota de la foto"></figcaption>
    </figure>`).join('');
}

/** Reduce la imagen antes de guardarla: el navegador tiene poco espacio. */
async function comprimirImagen(file, max = 1280, calidad = 0.72) {
  const bitmap = await createImageBitmap(file);
  const escala = Math.min(1, max / Math.max(bitmap.width, bitmap.height));
  const w = Math.round(bitmap.width * escala);
  const alto = Math.round(bitmap.height * escala);
  const cv = document.createElement('canvas');
  cv.width = w;
  cv.height = alto;
  cv.getContext('2d').drawImage(bitmap, 0, 0, w, alto);
  bitmap.close?.();
  return cv.toDataURL('image/jpeg', calidad);
}

async function agregarFotos(c, files) {
  const restantes = 12 - (c.fotos || []).length;
  const lista = Array.from(files).slice(0, Math.max(0, restantes));
  if (!lista.length) { toast('Máximo 12 fotos por cotización', 'err'); return; }
  toast(`Procesando ${lista.length} ${lista.length === 1 ? 'foto' : 'fotos'}…`);
  for (const file of lista) {
    try {
      const img = await comprimirImagen(file);
      c.fotos.push({ id: uid(), img, nota: '', fecha: new Date().toISOString() });
    } catch (e) {
      toast('No se pudo leer una de las imágenes', 'err');
    }
  }
  tocar(c, 'Fotos agregadas');
  $('#fotos').innerHTML = galeriaFotos(c);
}

function verFoto(c, fotoId) {
  const f = (c.fotos || []).find((x) => x.id === fotoId);
  if (!f) return;
  const ov = abrirModal(`
    <div class="lightbox" role="dialog" aria-modal="true">
      <img src="${f.img}" alt="${h(f.nota || 'Foto del vehículo')}">
      <div class="lightbox-bar">
        <span>${h(f.nota || 'Sin nota')} · ${h(fecha(f.fecha?.slice(0, 10)))}</span>
        <button class="btn btn-sm" data-cerrar>Cerrar</button>
      </div>
    </div>`);
  ov.addEventListener('click', (e) => { if (e.target.closest('[data-cerrar]')) cerrarModal(); });
}

/* ----------------------------------------------------------------- notas  */
function cardNotas(c) {
  return `
    <section class="card">
      <div class="card-head">
        <div>${icon('note')}</div>
        <div><h2>Notas para el cliente</h2><div class="sub">Se imprimen en el documento</div></div>
      </div>
      <div class="card-body">
        <textarea class="input" data-bind="notas" rows="3" aria-label="Notas para el cliente" placeholder="Ej: se recomienda revisar bujes de barra estabilizadora en la próxima mantención.">${h(c.notas)}</textarea>
      </div>
    </section>`;
}

/* ------------------------------------------------------------------ rail  */
function railResumen(c) {
  const t = calc(c);
  const proximo = siguienteEstado(c.estado);
  return `
    <section class="card">
      <div class="card-head"><div><h2>Resumen</h2><div class="sub">Se recalcula solo</div></div></div>
      <div class="card-body">
        <div class="field" style="margin-bottom:14px">
          <label for="f-estado">Estado</label>
          <select class="select" id="f-estado" data-bind="estado">
            ${Object.entries(ESTADOS).map(([k, v]) => `<option value="${k}" ${c.estado === k ? 'selected' : ''}>${v.label}</option>`).join('')}
          </select>
        </div>
        ${proximo ? `<button class="btn btn-block btn-soft" style="margin-bottom:14px" data-cot="${c.id}" data-estado="${proximo.k}">${icon(proximo.ico, 'i i-sm')} ${proximo.txt}</button>` : ''}
        <div class="sum" id="sum" aria-live="polite">${sumRows(t)}</div>
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
        <button class="btn btn-block" data-firmar="${c.id}">${icon('pen', 'i i-sm')} ${c.firma ? 'Ver firma del cliente' : 'Firmar aprobación'}</button>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:9px">
          <button class="btn" data-duplicar="${c.id}">${icon('copy', 'i i-sm')} Duplicar</button>
          <button class="btn btn-danger" data-eliminar="${c.id}">${icon('trash', 'i i-sm')} Eliminar</button>
        </div>
      </div>
    </section>

    <section class="card">
      <div class="card-head"><div>${icon('history')}</div><div><h2>Actividad</h2></div></div>
      <div class="card-body tight">
        <div class="hist">
          ${[...(c.historial || [])].reverse().slice(0, 6).map((x) => `
            <div class="hist-row">
              <span class="hist-dot"></span>
              <div><b>${h(x.texto)}</b><small>${h(fecha(x.fecha.slice(0, 10)))} · ${h(desdeAhora(x.fecha))}</small></div>
            </div>`).join('')}
        </div>
      </div>
    </section>`;
}

function siguienteEstado(estado) {
  const mapa = {
    borrador:  { k: 'enviada',   txt: 'Marcar como enviada', ico: 'send' },
    enviada:   { k: 'aprobada',  txt: 'Marcar como aprobada', ico: 'check' },
    aprobada:  { k: 'taller',    txt: 'Ingresar al taller', ico: 'wrench' },
    taller:    { k: 'entregada', txt: 'Marcar como entregada', ico: 'flag' }
  };
  return mapa[estado] || null;
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
    ${t.abonado ? `<div class="sum-row" style="margin-top:8px"><span class="lbl-t">Abonado</span><span class="val" style="color:var(--ok)">−${money(t.abonado)}</span></div>` : ''}
    <div class="sum-pay"><span class="lbl-t">A pagar</span><span class="val">${money(t.saldo)}</span></div>`;
}

function barraMovil(c) {
  const t = calc(c);
  return `
    <div class="barra-movil no-print">
      <div class="bm-total">
        <small>A pagar</small>
        <b data-out="bm">${money(t.saldo)}</b>
      </div>
      <a class="btn" href="#/doc/${c.id}">${icon('file', 'i i-sm')} Documento</a>
      <button class="btn btn-primary" data-compartir="${c.id}">${icon('share', 'i i-sm')} Compartir</button>
    </div>`;
}

/** Actualiza sólo los números, sin volver a dibujar los campos (no roba el foco). */
function refrescarTotales(c) {
  const t = calc(c);
  const sumEl = $('#sum');
  if (sumEl) sumEl.innerHTML = sumRows(t);
  const set = (k, v) => { const el = $(`[data-out="${k}"]`); if (el) el.textContent = v; };
  set('mo', money(t.mo));
  set('rep', money(t.rep));
  set('gastos', money(t.gastos));
  set('abonado', money(t.abonado));
  set('saldo-pill', money(t.saldo));
  set('bm', money(t.saldo));
  $$('[data-linea="repuestos"]').forEach((row) => {
    const r = (c.repuestos || []).find((x) => x.id === row.dataset.id);
    const cel = $('[data-total]', row);
    if (r && cel) cel.textContent = money(num(r.cant) * num(r.unit));
  });
}

function repintarLineas(c, tipo) {
  const cont = $(`#lines-${tipo}`);
  if (!cont) return;
  cont.innerHTML = tipo === 'trabajos' ? lineasTrabajos(c) : tipo === 'repuestos' ? lineasRepuestos(c) : lineasAbonos(c);
  refrescarTotales(c);
}

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
          <div class="sub">Enter agrega y podés seguir eligiendo</div>
        </div>
        <div style="margin-left:auto"></div>
        <button class="btn btn-ghost btn-icon" data-cerrar aria-label="Cerrar">${icon('x')}</button>
      </div>
      <div class="picker-search">
        <input class="input" id="pq" placeholder="Buscar en el catálogo…" data-autofocus autocomplete="off">
      </div>
      <div class="modal-body flush"><div class="picker-list" id="plist"></div></div>
      <div class="modal-foot">
        <span class="faint" style="margin-right:auto;font-size:12px">${items.length} ítems · se editan en Catálogo</span>
        <button class="btn btn-primary" data-cerrar>Listo</button>
      </div>
    </div>`);

  const pintar = () => {
    const q = normTexto($('#pq').value);
    const filtrados = items.filter((it) => normTexto(it.cat + ' ' + it.desc).includes(q));
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
          <div style="min-width:0">
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
    if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
      e.preventDefault();
      pickerCursor = e.key === 'ArrowDown' ? Math.min(pickerCursor + 1, botones.length - 1) : Math.max(pickerCursor - 1, 0);
      pintar();
      $$('[data-pick]', ov)[pickerCursor]?.scrollIntoView({ block: 'nearest' });
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      const b = botones[pickerCursor];
      if (b) agregarDesdeCatalogo(tipo, b.dataset.pick, cotId);
      else if ($('#pq').value.trim()) crearItemCatalogo(tipo, $('#pq').value.trim(), cotId);
    }
  });

  ov.addEventListener('click', (e) => {
    const pick = e.target.closest('[data-pick]');
    if (pick) { agregarDesdeCatalogo(tipo, pick.dataset.pick, cotId); return; }
    if (e.target.closest('[data-crear-item]')) { crearItemCatalogo(tipo, $('#pq').value.trim(), cotId); return; }
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
  refrescarDatalists();
  agregarDesdeCatalogo(tipo, it.id, cotId);
  cerrarModal();
  toast('Ítem creado en el catálogo');
}

/* ==================================================== firma del cliente === */
function abrirFirma(c) {
  const ov = abrirModal(`
    <div class="modal" style="max-width:560px" role="dialog" aria-modal="true">
      <div class="modal-head">
        <div>${icon('pen')}</div>
        <div><h3>Firma del cliente</h3><div class="sub">Aprobación de la cotización ${h(c.folio)}</div></div>
      </div>
      <div class="modal-body grid">
        ${c.firma ? `
          <div class="firma-vista"><img src="${c.firma.img}" alt="Firma del cliente"></div>
          <p class="muted" style="font-size:13px">Firmada por ${h(c.firma.nombre || c.cliente?.nombre || 'el cliente')} el ${h(fecha(c.firma.fecha?.slice(0, 10), true))}.</p>
        ` : `
          <p class="muted" style="font-size:13.5px">Pasale el celular al cliente y que firme con el dedo. La firma queda impresa en la orden de trabajo.</p>
          <canvas id="firmaCanvas" class="firma-canvas" width="900" height="320" aria-label="Área para firmar"></canvas>
          <div class="field"><label for="firmaNombre">Nombre de quien firma</label><input class="input" id="firmaNombre" value="${h(c.cliente?.nombre || '')}"></div>
        `}
      </div>
      <div class="modal-foot">
        ${c.firma
          ? `<button class="btn btn-danger" data-firma-borrar>Borrar firma</button><button class="btn" data-cerrar>Cerrar</button>`
          : `<button class="btn" data-firma-limpiar>Limpiar</button><button class="btn" data-cerrar>Cancelar</button><button class="btn btn-primary" data-firma-guardar>Guardar firma</button>`}
      </div>
    </div>`);

  const cv = $('#firmaCanvas', ov);
  let trazos = false;
  if (cv) {
    const ctx = cv.getContext('2d');
    ctx.lineWidth = 4;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#101418';
    let dibujando = false;
    const punto = (e) => {
      const r = cv.getBoundingClientRect();
      return { x: (e.clientX - r.left) * (cv.width / r.width), y: (e.clientY - r.top) * (cv.height / r.height) };
    };
    cv.addEventListener('pointerdown', (e) => {
      dibujando = true;
      trazos = true;
      cv.setPointerCapture(e.pointerId);
      const p = punto(e);
      ctx.beginPath();
      ctx.moveTo(p.x, p.y);
    });
    cv.addEventListener('pointermove', (e) => {
      if (!dibujando) return;
      e.preventDefault();
      const p = punto(e);
      ctx.lineTo(p.x, p.y);
      ctx.stroke();
    });
    ['pointerup', 'pointerleave', 'pointercancel'].forEach((ev) => cv.addEventListener(ev, () => { dibujando = false; }));
  }

  ov.addEventListener('click', (e) => {
    if (e.target.closest('[data-firma-limpiar]')) {
      cv.getContext('2d').clearRect(0, 0, cv.width, cv.height);
      trazos = false;
    }
    if (e.target.closest('[data-firma-guardar]')) {
      if (!trazos) { toast('Todavía no hay ninguna firma', 'err'); return; }
      c.firma = { img: cv.toDataURL('image/png'), nombre: $('#firmaNombre', ov).value, fecha: new Date().toISOString() };
      tocar(c, 'Firmada por el cliente');
      cerrarModal();
      render();
      toast('Firma guardada');
    }
    if (e.target.closest('[data-firma-borrar]')) {
      c.firma = null;
      tocar(c, 'Firma eliminada');
      cerrarModal();
      render();
    }
    if (e.target.closest('[data-cerrar]')) cerrarModal();
  });
}

/* ------------------------------------------------------ eventos del editor */
document.addEventListener('click', (e) => {
  const t = e.target;
  const c = cotActual();

  const picker = t.closest('[data-picker]');
  if (picker) { if (c) abrirPicker(picker.dataset.picker, c.id); return; }

  const firmar = t.closest('[data-firmar]');
  if (firmar) { const cot = getCot(firmar.dataset.firmar); if (cot) abrirFirma(cot); return; }

  const usar = t.closest('[data-usar-veh]');
  if (usar && c) {
    const v = getVehiculo(usar.dataset.usarVeh);
    const cli = getCliente(v?.clienteId);
    if (v) {
      c.vehiculo = Object.assign({}, c.vehiculo, { marcaModelo: v.marcaModelo, anio: v.anio, color: v.color, vin: v.vin });
      if (!num(c.vehiculo.km)) c.vehiculo.km = v.km;
      if (cli && !c.cliente.nombre) c.cliente = { nombre: cli.nombre, telefono: cli.telefono, email: cli.email, rut: cli.rut };
      tocar(c, 'Datos tomados de la ficha del vehículo');
      render();
      toast('Datos completados desde la ficha');
    }
    return;
  }

  const add = t.closest('[data-add]');
  if (add && c) {
    const tipo = add.dataset.add;
    if (tipo === 'trabajos') c.trabajos.push({ id: uid(), desc: '', obs: '', realizado: false, mo: 0 });
    if (tipo === 'repuestos') c.repuestos.push({ id: uid(), cant: 1, desc: '', unit: 0 });
    if (tipo === 'abonos') c.abonos.push({ id: uid(), fecha: hoyISO(), detalle: '', monto: 0 });
    repintarLineas(c, tipo);
    tocar(c);
    const filas = $$(`#lines-${tipo} .line`);
    $('input:not([type=checkbox])', filas[filas.length - 1])?.focus();
    return;
  }

  const rowDel = t.closest('[data-del]');
  if (rowDel && c) {
    const linea = rowDel.closest('[data-linea]');
    if (!linea) return;
    const tipo = linea.dataset.linea;
    const item = c[tipo].find((x) => x.id === linea.dataset.id);
    const indice = c[tipo].indexOf(item);
    c[tipo].splice(indice, 1);
    repintarLineas(c, tipo);
    tocar(c);
    toast('Línea eliminada', 'ok', {
      texto: 'Deshacer',
      run: () => { c[tipo].splice(indice, 0, item); repintarLineas(c, tipo); tocar(c); }
    });
    return;
  }

  if (t.closest('[data-foto-add]')) { $('#fotoInput')?.click(); return; }

  const fotoVer = t.closest('[data-foto-ver]');
  if (fotoVer && c) { verFoto(c, fotoVer.dataset.fotoVer); return; }

  const fotoDel = t.closest('[data-foto-del]');
  if (fotoDel && c) {
    const id = fotoDel.dataset.fotoDel;
    const foto = c.fotos.find((x) => x.id === id);
    const indice = c.fotos.indexOf(foto);
    c.fotos.splice(indice, 1);
    tocar(c);
    $('#fotos').innerHTML = galeriaFotos(c);
    toast('Foto eliminada', 'ok', {
      texto: 'Deshacer',
      run: () => { c.fotos.splice(indice, 0, foto); tocar(c); $('#fotos').innerHTML = galeriaFotos(c); }
    });
  }
});

document.addEventListener('change', (e) => {
  if (e.target.id === 'fotoInput' && e.target.files?.length) {
    const c = cotActual();
    if (c) agregarFotos(c, e.target.files);
    e.target.value = '';
  }
});

/** Entradas de texto del editor. Devuelve true si manejó el evento. */
function manejarInputEditor(el) {
  const c = cotActual();
  if (!c) return false;

  const notaFoto = el.dataset.fotoNota;
  if (notaFoto) {
    const f = c.fotos.find((x) => x.id === notaFoto);
    if (f) { f.nota = el.value; tocar(c); }
    return true;
  }

  const bind = el.dataset.bind;
  if (bind) {
    let valor = el.type === 'checkbox' ? el.checked : el.value;
    if (el.dataset.money !== undefined || ['gastosPct', 'descuento'].includes(bind)) valor = num(valor);
    if (bind === 'vehiculo.patente') valor = normPatente(valor);
    setPath(c, bind, valor);
    tocar(c, bind === 'estado' ? `Estado: ${ESTADOS[valor]?.label || valor}` : null);
    refrescarTotales(c);
    if (bind === 'vehiculo.patente') {
      const banner = $('#bannerVeh');
      if (banner) banner.innerHTML = bannerVehiculo(c);
    }
    return true;
  }

  const linea = el.closest('[data-linea]');
  if (linea && el.dataset.field) {
    const item = (c[linea.dataset.linea] || []).find((x) => x.id === linea.dataset.id);
    if (!item) return true;
    const campo = el.dataset.field;
    item[campo] = el.type === 'checkbox' ? el.checked
      : (el.dataset.money !== undefined || campo === 'cant') ? num(el.value)
      : el.value;

    // Si el texto coincide con el catálogo y no hay precio, se completa solo.
    if (campo === 'desc') {
      const fuente = linea.dataset.linea === 'trabajos' ? DB.servicios : DB.repuestos;
      const match = fuente.find((x) => normTexto(x.desc) === normTexto(el.value));
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
    return true;
  }

  return false;
}

/* Enter en la última línea agrega otra: carga rápida sin soltar el teclado. */
document.addEventListener('keydown', (e) => {
  if (e.key !== 'Enter' || e.shiftKey) return;
  const linea = e.target.closest?.('[data-linea]');
  if (!linea || e.target.tagName !== 'INPUT' || e.target.type === 'checkbox') return;
  const filas = $$('.line', linea.parentElement);
  if (filas[filas.length - 1] === linea) {
    e.preventDefault();
    $(`[data-add="${linea.dataset.linea}"]`)?.click();
  }
});
