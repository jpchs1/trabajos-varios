#!/usr/bin/env node
// Arma las dos versiones de la cotización (español e inglés) y las imprime a
// PDF con Chromium.
//
//   node build.mjs            → escribe los .html y los .pdf
//   node build.mjs --solo-html → sólo los .html (no necesita Chromium)
//
// Las fotos de la flota se leen de ./vehiculos/ y se incrustan como data URI:
// el PDF queda autocontenido. Si las fotos no están, la sección se arma igual
// con las fichas de los vehículos y se omiten las imágenes.

import { readFileSync, existsSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createRequire } from 'node:module';

import { doc, cliente, programa, alternativas, flota } from './contenido.mjs';
import { estilos } from './estilos.mjs';

const AQUI = dirname(fileURLToPath(import.meta.url));
const soloHtml = process.argv.includes('--solo-html');

// --- Formato -----------------------------------------------------------------

const LOCALE = { es: 'es-CL', en: 'en-GB' };

const plata = (n, l) => `CLP ${new Intl.NumberFormat(l === 'es' ? 'es-CL' : 'en-US').format(n)}`;

const fecha = (iso, l, opts) =>
  new Intl.DateTimeFormat(LOCALE[l], { timeZone: 'UTC', ...opts }).format(new Date(`${iso}T00:00:00Z`));

const mayus = (s) => s.charAt(0).toUpperCase() + s.slice(1);

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

// --- Cálculos ----------------------------------------------------------------
// Un solo lugar hace las sumas. Los dos idiomas leen de acá, así que no pueden
// discrepar.

const sub = (s) => (s.valor + s.entradas) / (s.base ?? 1);

const totales = {
  servicios: programa.reduce((a, s) => a + s.valor, 0),
  entradas: programa.reduce((a, s) => a + s.entradas, 0),
};
totales.pack = totales.servicios + totales.entradas;
totales.grupo = totales.pack * doc.pax;

const dias = [...new Set(programa.map((s) => s.fecha))].map((f) => ({
  fecha: f,
  servicios: programa.filter((s) => s.fecha === f),
  subtotal: programa.filter((s) => s.fecha === f).reduce((a, s) => a + sub(s), 0),
}));

const baseJueves = sub(programa.find((s) => s.reemplazable));

// --- Fotos de la flota -------------------------------------------------------

const MIME = { jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png', webp: 'image/webp' };

function foto(archivo) {
  const base = archivo.replace(/\.[^.]+$/, '');
  for (const ext of ['jpg', 'jpeg', 'png', 'webp']) {
    const ruta = join(AQUI, 'vehiculos', `${base}.${ext}`);
    if (existsSync(ruta)) return `data:${MIME[ext]};base64,${readFileSync(ruta).toString('base64')}`;
  }
  return null;
}

const fotos = Object.fromEntries(flota.map((v) => [v.archivo, foto(v.archivo)]));
const hayFotos = Object.values(fotos).some(Boolean);

// --- Textos ------------------------------------------------------------------

const T = {
  es: {
    lang: 'es',
    titulo: `Tourevo · San Pedro de Atacama — ${cliente.nombre}`,
    eyebrow: 'Cotización Atacama',
    h1: 'San Pedro de Atacama · cinco días en el desierto',
    sub: 'El programa ajustado según lo conversado, con el valor de cada excursión desglosado — servicio, entradas a parques y subtotal — y el total del pack al final. Incluye tres alternativas para el jueves por la mañana y los vehículos con los que operamos.',
    chipNo: 'Nº',
    chipEmitida: 'Emitida',
    chipVigencia: 'Vigente hasta',
    lPara: 'Preparado para',
    lResumen: 'El programa de un vistazo',
    lItinerario: 'Día a día, con subtotal por tour',
    lTabla: 'Resumen de valores',
    lAlt: 'Alternativas al jueves 24 por la mañana',
    lFlota: 'Los vehículos con los que operamos',
    lCond: 'Condiciones y consideraciones',
    saludo: `${cliente.nombre}, gracias por confiarnos este viaje. Acá está el programa ajustado según lo conversado, con <b>cada excursión desglosada en servicio, entradas y subtotal por persona</b>, y el total del pack cerrado al final — para que no haya sorpresas al llegar. Al final dejamos tres alternativas al jueves por la mañana, en orden de preferencia, que nos parecen más entretenidas que el sandboard según las edades de los pasajeros.`,
    firma: 'Un abrazo,',
    firmante: 'Juan Pablo',
    cargo: 'Travel Manager, Tourevo',
    facts: [
      ['Fechas', `22 – 26 sep 2026`],
      ['Días', '5'],
      ['Base', 'San Pedro de Atacama'],
      ['Servicios', String(programa.length)],
      ['Traslados', 'Privados'],
      ['Pasajeros', `${doc.pax} · base de cálculo`],
    ],
    ledeTabla: 'Todos los valores están en pesos chilenos y son por persona. La columna de subtotal ya suma el servicio y sus entradas a parques.',
    thN: '#',
    thServicio: 'Servicio',
    thFecha: 'Fecha',
    thValor: 'Servicio',
    thEntradas: 'Entradas',
    thSub: 'Subtotal',
    tfTotal: 'Total del programa',
    strip: { valor: 'Servicio', entradas: 'Entradas a parques', sub: 'Subtotal por persona' },
    sinEntradas: 'Sin entradas',
    incluye: 'Incluye',
    anticipada: 'Entrada que se compra por adelantado.',
    totalLabel: 'Total del pack, por persona',
    totalCalc: (s, e) => `${plata(s, 'es')} en servicios + ${plata(e, 'es')} en entradas a parques`,
    grupoLabel: `Total del pack para ${doc.pax} pasajeros`,
    ledeAlt: 'Las tres reemplazan a <b>Valle de Marte + Sandboard</b> del jueves 24 por la mañana, y van en orden de preferencia. Debajo de cada una está el efecto exacto sobre el total del pack.',
    minGrupo: (n) => `Tarifa mínima por grupo de ${n} pax`,
    porPersona: 'por persona',
    efecto: 'Efecto en el pack',
    reemplaza: (d, nuevo) => `Reemplaza los ${plata(baseJueves, 'es')} del jueves AM. El total del pack por persona queda en <b>${plata(nuevo, 'es')}</b>`,
    sube: 'sube',
    baja: 'baja',
    specAlt: (a, b) => `Altitud ${new Intl.NumberFormat('es-CL').format(a)} – ${new Intl.NumberFormat('es-CL').format(b)} m`,
    specDist: (d) => `Distancia ${String(d).replace('.', ',')} km`,
    ledeFlota: hayFotos
      ? 'Estos son los vehículos que solemos utilizar en nuestros programas. Todos los traslados y excursiones privadas de esta cotización se operan en este tipo de unidades.'
      : 'Estos son los vehículos que solemos utilizar en nuestros programas. Todos los traslados y excursiones privadas de esta cotización se operan en este tipo de unidades. Las fotografías van adjuntas por separado.',
    condA: 'Qué incluye el valor',
    condAItems: [
      'Todas las excursiones del programa con guía, con lo que cada ficha indica en <b>Incluye</b>.',
      'Los dos <b>traslados de aeropuerto en servicio privado</b>, El Loa – San Pedro y vuelta.',
      'La coordinación del <b>tour astronómico</b>, que opera un tercero en modalidad compartida y que gestionamos a modo de cortesía.',
      'Las <b>entradas a parques</b> están detalladas aparte en cada tour y sumadas en el total del pack. El valor de servicios por sí solo no las incluye.',
    ],
    condB: 'A tener presente',
    condBItems: [
      'Las entradas de <b>Cejar y Tebenquiche, Piedras Rojas y el tour arqueológico</b> se compran por adelantado.',
      '<b>Valle de la Luna Sur</b> hoy no paga entrada, pero es posible que la cobren a la fecha del viaje.',
      'Los horarios de los <b>traslados</b> quedan por coordinar y confirmar según los vuelos.',
      `Las dos primeras alternativas se cotizan como <b>tarifa mínima de grupo sobre base ${doc.pax} pax</b>: con otra cantidad de pasajeros el valor por persona cambia.`,
      'Todo queda sujeto a disponibilidad al momento de confirmar.',
    ],
    ctaLead: 'Quedamos atentos a sus noticias para bloquear los cupos.',
    ctaAfter: `Esta cotización tiene validez hasta el ${fecha(doc.vigencia, 'es', { day: 'numeric', month: 'long', year: 'numeric' })}.`,
    footWho: '<b>Tourevo</b> <span>· Private experiences</span>',
    legend: `Cotización ${doc.numero} · Tourevo · Valores en pesos chilenos`,
  },

  en: {
    lang: 'en',
    titulo: `Tourevo · San Pedro de Atacama — ${cliente.nombre}`,
    eyebrow: 'Atacama quotation',
    h1: 'San Pedro de Atacama · five days in the desert',
    sub: 'The programme adjusted to what we discussed, with every excursion broken down — service, park fees and subtotal — and the full pack total at the end. It also carries three alternatives for Thursday morning and the vehicles we run.',
    chipNo: 'No.',
    chipEmitida: 'Issued',
    chipVigencia: 'Valid until',
    lPara: 'Prepared for',
    lResumen: 'The programme at a glance',
    lItinerario: 'Day by day, with a subtotal per tour',
    lTabla: 'Price summary',
    lAlt: 'Alternatives for Thursday 24, morning',
    lFlota: 'The vehicles we run',
    lCond: 'Conditions and things to keep in mind',
    saludo: `${cliente.nombre}, thank you for trusting us with this trip. Here is the programme adjusted to what we discussed, with <b>every excursion broken down into service, park fees and a per-person subtotal</b>, and the pack total closed out at the end — so nothing is a surprise on arrival. At the end we have left three alternatives to Thursday morning, in order of preference, which we think are more fun than the sandboarding depending on the ages of the travellers.`,
    firma: 'Warmly,',
    firmante: 'Juan Pablo',
    cargo: 'Travel Manager, Tourevo',
    facts: [
      ['Dates', '22 – 26 Sep 2026'],
      ['Days', '5'],
      ['Base', 'San Pedro de Atacama'],
      ['Services', String(programa.length)],
      ['Transfers', 'Private'],
      ['Travellers', `${doc.pax} · basis of quote`],
    ],
    ledeTabla: 'All amounts are in Chilean pesos and are per person. The subtotal column already adds the service and its park fees together.',
    thN: '#',
    thServicio: 'Service',
    thFecha: 'Date',
    thValor: 'Service',
    thEntradas: 'Park fees',
    thSub: 'Subtotal',
    tfTotal: 'Programme total',
    strip: { valor: 'Service', entradas: 'Park fees', sub: 'Subtotal per person' },
    sinEntradas: 'No park fee',
    incluye: 'Includes',
    anticipada: 'This ticket has to be bought in advance.',
    totalLabel: 'Pack total, per person',
    totalCalc: (s, e) => `${plata(s, 'en')} in services + ${plata(e, 'en')} in park fees`,
    grupoLabel: `Pack total for ${doc.pax} travellers`,
    ledeAlt: 'All three replace <b>Valle de Marte + Sandboarding</b> on Thursday 24 in the morning, and are listed in order of preference. Under each one is its exact effect on the pack total.',
    minGrupo: (n) => `Minimum group rate for ${n} travellers`,
    porPersona: 'per person',
    efecto: 'Effect on the pack',
    reemplaza: (d, nuevo) => `Replaces the ${plata(baseJueves, 'en')} of Thursday AM. The pack total per person becomes <b>${plata(nuevo, 'en')}</b>`,
    sube: 'up',
    baja: 'down',
    specAlt: (a, b) => `Altitude ${new Intl.NumberFormat('en-US').format(a)} – ${new Intl.NumberFormat('en-US').format(b)} m`,
    specDist: (d) => `Distance ${d} km`,
    ledeFlota: hayFotos
      ? 'These are the vehicles we normally use on our programmes. Every private transfer and excursion in this quotation runs on this type of unit.'
      : 'These are the vehicles we normally use on our programmes. Every private transfer and excursion in this quotation runs on this type of unit. Photographs are attached separately.',
    condA: 'What the price covers',
    condAItems: [
      'Every excursion in the programme with a guide, plus whatever each card lists under <b>Includes</b>.',
      'Both <b>airport transfers in private service</b>, El Loa – San Pedro and back.',
      'Coordination of the <b>astronomy tour</b>, which a third party operates on a shared basis and which we arrange for you as a courtesy.',
      '<b>Park fees</b> are itemised separately on each tour and added into the pack total. The service figure on its own does not include them.',
    ],
    condB: 'Worth knowing',
    condBItems: [
      'Tickets for <b>Cejar and Tebenquiche, Piedras Rojas and the archaeological tour</b> are bought in advance.',
      '<b>Valle de la Luna South</b> currently charges no park fee, but one may be in force by your travel date.',
      '<b>Transfer</b> times are still to be coordinated and confirmed against your flights.',
      `The first two alternatives are quoted as a <b>minimum group rate on a ${doc.pax}-traveller basis</b>: with a different party size the per-person figure changes.`,
      'Everything remains subject to availability at the time of confirmation.',
    ],
    ctaLead: 'We look forward to hearing from you so we can hold the spaces.',
    ctaAfter: `This quotation is valid until ${fecha(doc.vigencia, 'en', { day: 'numeric', month: 'long', year: 'numeric' })}.`,
    footWho: '<b>Tourevo</b> <span>· Private experiences</span>',
    legend: `Quotation ${doc.numero} · Tourevo · Amounts in Chilean pesos`,
  },
};

const NOMBRE_DIA = {
  '2026-09-22': { es: 'Llegada a San Pedro', en: 'Arrival in San Pedro' },
  '2026-09-23': { es: 'Puritama, Valle de la Luna y el cielo', en: 'Puritama, Valle de la Luna and the night sky' },
  '2026-09-24': { es: 'Valle de Marte y el corazón del salar', en: 'Valle de Marte and the heart of the salt flat' },
  '2026-09-25': { es: 'Altiplano: Piedras Rojas y las lagunas', en: 'Altiplano: Piedras Rojas and the lagoons' },
  '2026-09-26': { es: 'Quitor y salida', en: 'Quitor and departure' },
};

// --- Render ------------------------------------------------------------------

function franjaPrecios(s, t, l) {
  const entradas = s.entradas === 0
    ? `<div class="nil"><i>${t.strip.entradas}</i><b>${t.sinEntradas}</b></div>`
    : `<div><i>${t.strip.entradas}</i><b>${plata(s.entradas / (s.base ?? 1), l)}</b></div>`;
  return `
          <div class="pstrip">
            <div><i>${t.strip.valor}</i><b>${plata(s.valor / (s.base ?? 1), l)}</b></div>
            ${entradas}
            <div class="sub"><i>${t.strip.sub}</i><b>${plata(sub(s), l)}</b></div>
          </div>`;
}

function servicio(s, t, l) {
  const tag = s.etiqueta ? `<span class="tag">${esc(s.etiqueta[l])}</span>` : '';
  const inc = s.incluye ? `<p class="inc"><b>${t.incluye}:</b> ${esc(s.incluye[l])}</p>` : '';
  const flags = [];
  if (s.entradasNota) flags.push(esc(s.entradasNota[l]));
  if (s.entradasAnticipadas) flags.push(t.anticipada);
  const flag = flags.length ? `<p class="flag">${flags.join(' ')}</p>` : '';
  return `
        <div class="svc">
          <div class="svc-top">
            <span class="svc-t">${esc(s.horario[l])}</span>
            <span class="svc-h">${esc(s.titulo[l])}</span>
            ${tag}
          </div>
          <p>${esc(s.texto[l])}</p>
          ${inc}
          ${flag}
${franjaPrecios(s, t, l)}
        </div>`;
}

function dia(d, t, l) {
  const cab = mayus(fecha(d.fecha, l, { weekday: 'long', day: 'numeric', month: 'long' }));
  return `
      <article class="day">
        <div class="day-head">
          <span class="day-date">${esc(cab)}</span>
          <span class="day-name">${esc(NOMBRE_DIA[d.fecha][l])}</span>
          <span class="day-sum">${t.strip.sub}: ${plata(d.subtotal, l)}</span>
        </div>
${d.servicios.map((s) => servicio(s, t, l)).join('\n')}
      </article>`;
}

function tabla(t, l) {
  const filas = programa.map((s, i) => `
          <tr>
            <td><span class="n">${i + 1}</span></td>
            <td><b>${esc(s.titulo[l])}</b><em>${esc(fecha(s.fecha, l, { weekday: 'short', day: 'numeric', month: 'short' }))} · ${esc(s.horario[l])}</em></td>
            <td class="r">${plata(s.valor, l)}</td>
            <td class="r">${s.entradas === 0 ? '—' : plata(s.entradas, l)}</td>
            <td class="r sub">${plata(sub(s), l)}</td>
          </tr>`).join('');
  return `
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr>
              <th>${t.thN}</th>
              <th>${t.thServicio}</th>
              <th class="r">${t.thValor}</th>
              <th class="r">${t.thEntradas}</th>
              <th class="r">${t.thSub}</th>
            </tr>
          </thead>
          <tbody>${filas}
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2">${t.tfTotal}</td>
              <td class="r">${plata(totales.servicios, l)}</td>
              <td class="r">${plata(totales.entradas, l)}</td>
              <td class="r sub">${plata(totales.pack, l)}</td>
            </tr>
          </tfoot>
        </table>
      </div>`;
}

function total(t, l) {
  return `
      <div class="total">
        <div>
          <div class="tlabel">${t.totalLabel}</div>
          <div class="calc">${t.totalCalc(totales.servicios, totales.entradas)}</div>
        </div>
        <div class="amount">${plata(totales.pack, l)}</div>
        <div class="grp">
          <span>${t.grupoLabel}</span>
          <b>${plata(totales.grupo, l)}</b>
        </div>
      </div>`;
}

function alternativa(a, t, l) {
  const pp = sub(a);
  const nuevo = totales.pack - baseJueves + pp;
  const delta = pp - baseJueves;
  const signo = delta === 0
    ? ''
    : ` (<span class="${delta > 0 ? 'up' : 'down'}">${delta > 0 ? t.sube : t.baja} ${plata(Math.abs(delta), l)}</span>)`;

  const precio = a.base > 1
    ? `<div><i>${t.minGrupo(a.base)}</i><b>${plata(a.valor, l)}</b></div>
            ${a.entradas ? `<div><i>${t.strip.entradas}</i><b>${plata(a.entradas, l)} / ${a.base} pax</b></div>` : `<div class="nil"><i>${t.strip.entradas}</i><b>${t.sinEntradas}</b></div>`}
            <div class="sub"><i>${t.strip.sub}</i><b>${plata(pp, l)}</b></div>`
    : `<div><i>${t.strip.valor}</i><b>${plata(a.valor, l)}</b></div>
            ${a.entradas ? `<div><i>${t.strip.entradas}</i><b>${plata(a.entradas, l)}</b></div>` : `<div class="nil"><i>${t.strip.entradas}</i><b>${t.sinEntradas}</b></div>`}
            <div class="sub"><i>${t.strip.sub}</i><b>${plata(pp, l)}</b></div>`;

  const specs = a.ficha
    ? `<div class="specs">
            <span class="spec">${t.specAlt(a.ficha.altMin, a.ficha.altMax)}</span>
            <span class="spec">${t.specDist(a.ficha.distancia)}</span>
          </div>`
    : '';

  return `
      <article class="alt">
        <div class="alt-head">
          <span class="alt-rank">${a.orden}</span>
          <span class="alt-h">${esc(a.titulo[l])}</span>
          <span class="alt-t">${esc(a.horario[l])}</span>
        </div>
        <div class="alt-body">
          <p>${esc(a.texto[l])}</p>
          <p class="inc"><b>${t.incluye}:</b> ${esc(a.incluye[l])}</p>
          ${specs}
          <div class="pstrip">
            ${precio}
          </div>
          <p class="delta"><b>${t.efecto}:</b> ${t.reemplaza(delta, nuevo)}${signo}.</p>
        </div>
      </article>`;
}

function vehiculo(v, t, l) {
  const src = fotos[v.archivo];
  const img = src ? `<img src="${src}" alt="${esc(v.modelo)}">` : '';
  return `
        <figure class="veh${src ? '' : ' sinfoto'}">
          ${img}
          <figcaption class="cap">
            <h4>${esc(v.modelo)}</h4>
            <p>${esc(v.detalle[l])}</p>
          </figcaption>
        </figure>`;
}

function pagina(l) {
  const t = T[l];
  return `<!-- Generado por build.mjs — no editar a mano. Los precios viven en contenido.mjs. -->
<title>${esc(t.titulo)}</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${estilos}</style>

<div class="wrap">
  <article class="paper">

    <header class="head">
      <svg class="ridge" viewBox="0 0 800 46" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 46 L120 14 L210 34 L330 4 L430 30 L540 10 L650 32 L740 16 L800 30 L800 46 Z" fill="currentColor"/>
      </svg>
      <div class="brandrow">
        <div class="brand">
          <div class="mark" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 20 L9 8 L13 15 L16 10 L21 20 Z"/></svg>
          </div>
          <div class="wordmark">Tourevo<small>Private experiences</small></div>
        </div>
        <div style="text-align:right"><div class="eyebrow">${t.eyebrow}</div></div>
      </div>

      <h1 class="doc-title">${esc(t.h1)}</h1>
      <p class="doc-sub">${esc(t.sub)}</p>

      <div class="meta">
        <span class="chip">${t.chipNo} <b>${doc.numero}</b></span>
        <span class="chip">${t.chipEmitida} <b>${fecha(doc.emitida, l, { day: '2-digit', month: 'short', year: 'numeric' })}</b></span>
        <span class="chip">${t.chipVigencia} <b>${fecha(doc.vigencia, l, { day: '2-digit', month: 'short', year: 'numeric' })}</b></span>
      </div>
    </header>

    <div class="body">

      <section>
        <div class="welcome">
          <div class="monogram" aria-hidden="true">JP</div>
          <p class="msg">
            ${t.saludo}
            <span class="sig">${t.firma} <b>${t.firmante}</b> · ${t.cargo}</span>
          </p>
        </div>
      </section>

      <section>
        <p class="label">${t.lPara}</p>
        <div class="client"><span class="name">${esc(cliente.nombre)}</span></div>
      </section>

      <section>
        <p class="label">${t.lResumen}</p>
        <dl class="facts">
${t.facts.map(([k, v]) => `          <div class="fact"><dt>${esc(k)}</dt><dd>${esc(v)}</dd></div>`).join('\n')}
        </dl>
      </section>

      <section>
        <p class="label">${t.lItinerario}</p>
        <div class="days">
${dias.map((d) => dia(d, t, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lTabla}</p>
        <p class="lede">${t.ledeTabla}</p>
${tabla(t, l)}
${total(t, l)}
      </section>

      <section>
        <p class="label">${t.lAlt}</p>
        <p class="lede">${t.ledeAlt}</p>
        <div class="alts">
${alternativas.map((a) => alternativa(a, t, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lFlota}</p>
        <p class="lede">${t.ledeFlota}</p>
        <div class="fleet">
${flota.map((v) => vehiculo(v, t, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lCond}</p>
        <div class="cards">
          <div class="card">
            <h4>${t.condA}</h4>
            <ul>${t.condAItems.map((i) => `\n              <li>${i}</li>`).join('')}
            </ul>
          </div>
          <div class="card warn">
            <h4>${t.condB}</h4>
            <ul>${t.condBItems.map((i) => `\n              <li>${i}</li>`).join('')}
            </ul>
          </div>
        </div>
      </section>

      <div class="cta">
        <p class="lead">${t.ctaLead}</p>
        <p class="after">${t.ctaAfter}</p>
      </div>

    </div>

    <footer class="foot">
      <div class="who">${t.footWho}</div>
      <div class="links">info@tourevo.cl · tourevo.cl</div>
    </footer>

  </article>
</div>
<p class="legend">${esc(t.legend)}</p>
`;
}

// --- Salida ------------------------------------------------------------------

const SALIDAS = [
  { l: 'es', html: 'index.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-ES.pdf` },
  { l: 'en', html: 'index-en.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-EN.pdf` },
];

for (const s of SALIDAS) {
  writeFileSync(join(AQUI, s.html), pagina(s.l));
  console.log(`✓ ${s.html}`);
}

console.log(
  `\n  servicios ${plata(totales.servicios, 'es')}` +
  `\n  entradas  ${plata(totales.entradas, 'es')}` +
  `\n  pack p/p  ${plata(totales.pack, 'es')}` +
  `\n  ${doc.pax} pax     ${plata(totales.grupo, 'es')}` +
  `\n  fotos     ${hayFotos ? Object.values(fotos).filter(Boolean).length + '/' + flota.length : 'ninguna (se omiten)'}\n`,
);

if (soloHtml) process.exit(0);

// --- PDF ---------------------------------------------------------------------

const require_ = createRequire(import.meta.url);
let chromium;
for (const c of ['playwright', '/opt/node22/lib/node_modules/playwright', 'playwright-core']) {
  try { ({ chromium } = require_(c)); break; } catch { /* siguiente candidato */ }
}
if (!chromium) {
  console.error('No se encontró Playwright. Corré `node build.mjs --solo-html` o instalá playwright.');
  process.exit(1);
}

const pie = (tx) => `<div style="width:100%;font-size:7.5pt;font-family:Helvetica,Arial,sans-serif;color:#8497A0;padding:0 12mm;display:flex;justify-content:space-between;">
  <span>${tx}</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>`;

const navegador = await chromium.launch({ args: ['--no-sandbox'] });
for (const s of SALIDAS) {
  const p = await navegador.newPage();
  await p.emulateMedia({ colorScheme: 'light', media: 'print' });
  await p.goto(`file://${join(AQUI, s.html)}`, { waitUntil: 'networkidle' });
  await p.pdf({
    path: join(AQUI, s.pdf),
    format: 'A4',
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: '<div></div>',
    footerTemplate: pie(`Tourevo · ${doc.numero} · ${cliente.nombre}`),
    margin: { top: '11mm', bottom: '15mm', left: '12mm', right: '12mm' },
  });
  await p.close();
  console.log(`✓ ${s.pdf}`);
}
await navegador.close();
