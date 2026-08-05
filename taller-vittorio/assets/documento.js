/* ==========================================================================
   Documento imprimible: cotización para el cliente y orden de trabajo.
   La misma función arma la vista compartida por enlace.
   ========================================================================== */

ruta(/^\/doc\/([^/]+)(?:\/(ot))?$/, (id, modo) => {
  const c = getCot(id);
  if (!c) { toast('Esa cotización ya no existe', 'err'); return navegar('#/cotizaciones'); }
  const esOT = modo === 'ot';
  document.title = `${c.folio} · ${esOT ? 'Orden de trabajo' : 'Cotización'}`;
  setVista(`
    <div class="doc-bar no-print">
      <a class="btn btn-ghost btn-icon" href="#/cot/${c.id}" aria-label="Volver">${icon('back')}</a>
      <div class="seg" role="group" aria-label="Tipo de documento">
        <button data-doc-modo="" aria-pressed="${!esOT}">Cotización</button>
        <button data-doc-modo="ot" aria-pressed="${esOT}">Orden de trabajo</button>
      </div>
      <label class="switch ocultar-movil" style="margin-left:8px">
        <input type="checkbox" data-doc-unit ${c.mostrarUnitarios !== false ? 'checked' : ''}>
        <span class="track"></span><span>Mostrar valores unitarios</span>
      </label>
      <div class="spacer"></div>
      <button class="btn" data-firmar="${c.id}">${icon('pen', 'i i-sm')} <span class="ocultar-movil">${c.firma ? 'Firma' : 'Firmar'}</span></button>
      <button class="btn" data-compartir="${c.id}">${icon('share', 'i i-sm')} <span class="ocultar-movil">Compartir</span></button>
      <button class="btn" data-whatsapp="${c.id}">${icon('chat', 'i i-sm')} <span class="ocultar-movil">WhatsApp</span></button>
      <button class="btn btn-primary" data-imprimir>${icon('print', 'i i-sm')} <span class="ocultar-movil">Imprimir / PDF</span></button>
    </div>
    <div class="doc-stage doc-scope" id="docStage">${documentoHTML(c, DB.ajustes, esOT)}</div>`);
});

/* Vista pública: se abre desde el enlace compartido, sin tocar los datos locales. */
ruta(/^\/ver\/(.+)$/, (payload) => {
  let datos;
  try {
    datos = JSON.parse(b64urlDecode(payload));
  } catch (e) {
    setVista(`${topbar({ titulo: 'Enlace inválido' })}
      <div class="view"><div class="card">${vacio('alert', 'No pudimos leer este enlace',
        'Puede que se haya cortado al copiarlo. Pedile al taller que te lo mande de nuevo.')}</div></div>`);
    return;
  }
  const c = datos.c;
  const aj = Object.assign({}, AJUSTES_DEFAULT, datos.a || {});
  document.title = `${c.folio} · ${aj.taller}`;
  document.body.classList.add('modo-publico');
  setVista(`
    <div class="doc-bar no-print">
      <div class="pill">${icon('eye', 'i i-sm')} Documento de ${h(aj.taller)}</div>
      <div class="spacer"></div>
      ${aj.telefono ? `<a class="btn" href="https://wa.me/${telWa(aj.telefono)}?text=${encodeURIComponent(`Hola, le escribo por la cotización ${c.folio}.`)}" target="_blank" rel="noopener">${icon('chat', 'i i-sm')} Contactar al taller</a>` : ''}
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
  const unitarios = c.mostrarUnitarios !== false;
  const fotos = (c.incluirFotos !== false ? (c.fotos || []) : []).slice(0, 6);
  const vence = c.fechaIngreso && aj.validezDias ? sumarMeses(c.fechaIngreso, 0) : '';
  const validaHasta = c.fechaIngreso && aj.validezDias
    ? new Date(new Date(c.fechaIngreso).getTime() + num(aj.validezDias) * 86400000).toISOString().slice(0, 10)
    : '';

  return `
  <article class="doc ${c.estado === 'borrador' ? 'es-borrador' : ''}">
    <header class="doc-head">
      <div class="doc-brand">
        <div class="mk"><svg viewBox="0 0 24 24"><path d="M3 13.5 5 8a3 3 0 0 1 2.8-2h8.4A3 3 0 0 1 19 8l2 5.5"/><path d="M3 13.5h18V18a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-1H7v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M7 16.2h.01M17 16.2h.01"/></svg></div>
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
        ${!esOT && validaHasta ? `<div class="d">Válida hasta ${h(fecha(validaHasta))}</div>` : ''}
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
              ${unitarios ? '<th class="c-num" style="width:110px">Valor unit.</th>' : ''}
              <th class="c-num" style="width:110px">Total</th>
            </tr>
          </thead>
          <tbody>
            ${repuestos.map((r) => `
              <tr>
                <td class="c-mid">${h(NUM.format(num(r.cant)))}</td>
                <td>${h(r.desc)}</td>
                ${unitarios ? `<td class="c-num">${money(r.unit)}</td>` : ''}
                <td class="c-num">${money(num(r.cant) * num(r.unit))}</td>
              </tr>`).join('')}
          </tbody>
          <tfoot>
            ${t.gastos ? `<tr><td colspan="${unitarios ? 3 : 2}" class="c-num">Gastos, gestión y traslados (${h(c.gastosPct)}%)</td><td class="c-num">${money(t.gastos)}</td></tr>` : ''}
            <tr><td colspan="${unitarios ? 3 : 2}" class="c-num">Total repuestos</td><td class="c-num">${money(t.rep)}</td></tr>
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

      ${fotos.length ? `
      <section>
        <h3 class="doc-sec">Registro fotográfico</h3>
        <div class="doc-fotos">
          ${fotos.map((f) => `<figure><img src="${f.img}" alt="${h(f.nota || 'Foto del vehículo')}">${f.nota ? `<figcaption>${h(f.nota)}</figcaption>` : ''}</figure>`).join('')}
        </div>
      </section>` : ''}

      ${esOT || c.firma ? `
      <div class="doc-sign">
        <div class="sign-box">
          <span class="sign-line"></span>
          Recepción · taller
        </div>
        <div class="sign-box">
          ${c.firma ? `<img class="sign-img" src="${c.firma.img}" alt="Firma del cliente">` : '<span class="sign-line"></span>'}
          ${c.firma ? `${h(c.firma.nombre || c.cliente?.nombre || 'Cliente')} · ${h(fecha(c.firma.fecha?.slice(0, 10)))}` : 'Conformidad del cliente'}
        </div>
      </div>` : ''}
    </div>

    <footer class="doc-foot">
      <span>${h(aj.taller)}${aj.rut ? ` · RUT ${h(aj.rut)}` : ''}</span>
      <span>${h(c.folio)} · Documento generado el ${h(fecha(hoyISO()))}</span>
    </footer>
  </article>`;
}

/* --------------------------------------------------- eventos del documento */
document.addEventListener('click', (e) => {
  const modo = e.target.closest('[data-doc-modo]');
  if (modo) {
    const id = location.hash.match(/^#\/doc\/([^/]+)/)?.[1];
    if (id) navegar(`#/doc/${id}${modo.dataset.docModo === 'ot' ? '/ot' : ''}`);
  }
});

document.addEventListener('change', (e) => {
  if (e.target.dataset.docUnit !== undefined) {
    const id = location.hash.match(/^#\/doc\/([^/]+)/)?.[1];
    const c = getCot(id);
    if (!c) return;
    c.mostrarUnitarios = e.target.checked;
    guardar();
    const esOT = /\/ot$/.test(location.hash);
    $('#docStage').innerHTML = documentoHTML(c, DB.ajustes, esOT);
  }
});
