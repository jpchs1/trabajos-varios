#!/usr/bin/env node
// Itinerario propuesto para confirmar con el operador. Toma el itinerario que
// el cliente ya revisó, lo deja igual donde se puede y mueve sólo lo que no se
// puede operar como está. Sale en español y en inglés, HTML y PDF.
//
//   node build-itinerario.mjs [--solo-html]

import { writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { doc, cliente } from './contenido.mjs';
import { DIAS, CAMBIOS, PREGUNTAS_GENERALES } from './itinerario.mjs';
import { estilos, estilosComparativo, estilosItinerario } from './estilos.mjs';
import { fecha, mayus, esc, cabecera, piePagina, aPdf } from './comun.mjs';

const AQUI = dirname(fileURLToPath(import.meta.url));
const soloHtml = process.argv.includes('--solo-html');

// tipo:  tourevo → servicio nuestro · tercero → vuelos, comidas, check-in · libre → tiempo sin programar
// e:     igual → tal cual lo vio el cliente · movido → cambia de hora · nuevo → no estaba
const E = {
  igual:  { c: 'b-ok',  t: { es: 'Sin cambio', en: 'Unchanged' } },
  movido: { c: 'b-chg', t: { es: 'Movido', en: 'Moved' } },
  nuevo:  { c: 'b-x',   t: { es: 'Nuevo', en: 'New' } },
};

// --- Textos ------------------------------------------------------------------

const PREGUNTAS = [
  ...PREGUNTAS_GENERALES,
  ...DIAS.flatMap((d) => d.blks.filter((b) => b.conf).map((b) => ({
    q: { es: `${fecha(d.f, 'es', { day: 'numeric', month: 'short' })} · ${b.t.es}`,
         en: `${fecha(d.f, 'en', { day: 'numeric', month: 'short' })} · ${b.t.en}` },
    d: b.conf,
  }))),
];

const cuentaOurs = DIAS.flatMap((d) => d.blks).filter((b) => b.tipo === 'tourevo').length;
const cuentaCambios = DIAS.flatMap((d) => d.blks).filter((b) => b.e === 'movido' || b.e === 'nuevo').length;

const T = {
  es: {
    titulo: `Tourevo · Itinerario propuesto — ${cliente.nombre}`,
    eyebrow: 'Itinerario propuesto',
    h1: 'Horarios que necesitamos confirmar',
    sub: 'Itinerario que proponemos operar del 22 al 26 de diciembre, ya con los ajustes conversados: Cejar pasa a la mañana del 23 como aclimatación, Quitor y el Valle de la Muerte van juntos la tarde del 24, y el 26 queda libre. Ningún día lleva más de dos salidas.',
    chips: [['Ref.', doc.numero], ['Programa', '22 – 26 dic 2026'], ['Pasajeros', String(doc.pax)]],
    lLeyenda: 'El día completo, hora por hora',
    ledeLeyenda: `Los ${cuentaOurs} bloques marcados son los servicios que operamos nosotros. Los demás — vuelos, comidas, check-in y tiempo libre — van en gris, sólo para que se vea el día entero y se entienda de dónde sale cada horario.`,
    swTourevo: 'Servicio nuestro', swTercero: 'Vuelos, comidas y check-in', swLibre: 'Tiempo libre',
    lCambios: `Los ${CAMBIOS.length} ajustes`,
    ledeCambios: 'Todo lo demás queda en el día y la hora que el cliente ya vio en el sistema.',
    lQs: 'Lo que necesitamos que confirmen',
    ledeQs: 'Las primeras cuatro son transversales; las que siguen van línea por línea del itinerario.',
    cierre: '¿Es factible operar el programa en estos horarios? Quedamos atentos para cerrarlo y bloquear los cupos.',
    legend: `Itinerario propuesto sobre la cotización ${doc.numero} · Tourevo`,
  },
  en: {
    titulo: `Tourevo · Proposed itinerary — ${cliente.nombre}`,
    eyebrow: 'Proposed itinerary',
    h1: 'Times we need confirmed',
    sub: 'The itinerary we propose to run from 22 to 26 December, with the agreed adjustments: Cejar moves to the morning of the 23rd as the acclimatization, Quitor and Valle de la Muerte go together on the afternoon of the 24th, and the 26th is left free. No day carries more than two outings.',
    chips: [['Ref.', doc.numero], ['Programme', '22 – 26 Dec 2026'], ['Travellers', String(doc.pax)]],
    lLeyenda: 'The full day, hour by hour',
    ledeLeyenda: `The ${cuentaOurs} highlighted blocks are the services we operate. The rest — flights, meals, check-in and free time — are in grey, purely so the whole day is visible and every time makes sense in context.`,
    swTourevo: 'Our service', swTercero: 'Flights, meals and check-in', swLibre: 'Free time',
    lCambios: `The ${CAMBIOS.length} adjustments`,
    ledeCambios: 'Everything else stays on the day and time the client has already seen in the system.',
    lQs: 'What we need you to confirm',
    ledeQs: 'The first four are cross-cutting; the rest follow the itinerary line by line.',
    cierre: 'Is the programme feasible at these times? We look forward to closing it and holding the spaces.',
    legend: `Proposed itinerary against quotation ${doc.numero} · Tourevo`,
  },
};

// --- Render ------------------------------------------------------------------

const bloque = (b, l) => `
          <div class="blk ${b.tipo}">
            <div class="hh">${b.h}${b.f2 ? `<i>${b.f2}</i>` : ''}</div>
            <div>
              <div class="tt">${esc(b.t[l])}</div>
              ${b.n ? `<span class="nn">${b.n[l]}</span>` : ''}
            </div>
            ${b.e ? `<span class="badge ${E[b.e].c}">${E[b.e].t[l]}</span>` : '<span></span>'}
          </div>`;

const dia = (d, l) => {
  const nuestros = d.blks.filter((b) => b.tipo === 'tourevo').length;
  return `
      <article class="day">
        <div class="day-head">
          <span class="day-date">${esc(mayus(fecha(d.f, l, { weekday: 'long', day: 'numeric', month: 'long' })))}</span>
          <span class="day-name">${esc(d.n[l])}</span>
          <span class="day-sum">${nuestros} ${l === 'es' ? (nuestros === 1 ? 'servicio nuestro' : 'servicios nuestros') : (nuestros === 1 ? 'service of ours' : 'services of ours')}</span>
        </div>
        <div class="tl">
${d.blks.map((b) => bloque(b, l)).join('\n')}
        </div>
      </article>`;
};

const cambio = (c, i, l) => `
        <article class="find">
          <div class="find-top">
            <span class="find-n">${i + 1}</span>
            <h4>${esc(c.tit[l])}</h4>
          </div>
          ${c.cuerpo[l].map((p) => `<p>${p}</p>`).join('\n          ')}
        </article>`;

function pagina(l) {
  const t = T[l];
  return `<!-- Generado por build-itinerario.mjs — no editar a mano. -->
<title>${esc(t.titulo)}</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>${estilos}${estilosComparativo}${estilosItinerario}</style>

<div class="wrap">
  <article class="paper">
${cabecera({ eyebrow: t.eyebrow, h1: t.h1, sub: t.sub, chips: t.chips })}

    <div class="body">

      <section>
        <p class="label">${t.lCambios}</p>
        <p class="lede">${t.ledeCambios}</p>
        <div class="finds">
${CAMBIOS.map((c, i) => cambio(c, i, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lLeyenda}</p>
        <p class="lede">${t.ledeLeyenda}</p>
        <p class="leyenda">
          <span><i class="sw tourevo"></i>${t.swTourevo}</span>
          <span><i class="sw tercero"></i>${t.swTercero}</span>
          <span><i class="sw libre"></i>${t.swLibre}</span>
        </p>
        <div class="days">
${DIAS.map((d) => dia(d, l)).join('\n')}
        </div>
      </section>

      <section>
        <p class="label">${t.lQs}</p>
        <p class="lede">${t.ledeQs}</p>
        <ol class="qs">
${PREGUNTAS.map((q) => `          <li><span style="display:block"><b>${esc(q.q[l])}</b><span>${esc(q.d[l])}</span></span></li>`).join('\n')}
        </ol>
      </section>

      <div class="cta">
        <p class="lead">${esc(t.cierre)}</p>
      </div>

    </div>
${piePagina()}
  </article>
</div>
<p class="legend">${esc(t.legend)}</p>
`;
}

const SALIDAS = [
  { l: 'es', html: 'itinerario.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Itinerario-propuesto-ES.pdf` },
  { l: 'en', html: 'itinerario-en.html', pdf: `Tourevo-${doc.numero}-${cliente.nombre}-Itinerario-propuesto-EN.pdf` },
];

for (const s of SALIDAS) {
  writeFileSync(join(AQUI, s.html), pagina(s.l));
  console.log(`✓ ${s.html}`);
}
console.log(`\n  ${DIAS.flatMap((d) => d.blks).length} bloques · ${cuentaOurs} servicios nuestros · ${cuentaCambios} movidos o nuevos\n`);

if (!soloHtml) await aPdf(SALIDAS, { aqui: AQUI, pie: `Tourevo · ${doc.numero} · ${cliente.nombre} · itinerario propuesto` });
