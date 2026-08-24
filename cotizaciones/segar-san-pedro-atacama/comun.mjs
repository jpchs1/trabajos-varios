// Helpers que comparten los dos documentos de la carpeta: la cotización y el
// comparativo de días y horarios.

import { join } from 'node:path';
import { createRequire } from 'node:module';

export const LOCALE = { es: 'es-CL', en: 'en-GB' };

export const plata = (n, l) =>
  `CLP ${new Intl.NumberFormat(l === 'es' ? 'es-CL' : 'en-US').format(n)}`;

export const fecha = (iso, l, opts) =>
  new Intl.DateTimeFormat(LOCALE[l], { timeZone: 'UTC', ...opts }).format(new Date(`${iso}T00:00:00Z`));

export const mayus = (s) => s.charAt(0).toUpperCase() + s.slice(1);

export const esc = (s) =>
  String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

// Cabecera de marca, idéntica en los dos documentos.
export const cabecera = ({ eyebrow, h1, sub, chips }) => `
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
        <div style="text-align:right"><div class="eyebrow">${eyebrow}</div></div>
      </div>

      <h1 class="doc-title">${esc(h1)}</h1>
      <p class="doc-sub">${esc(sub)}</p>

      <div class="meta">${chips.map(([k, v]) => `
        <span class="chip">${k} <b>${v}</b></span>`).join('')}
      </div>
    </header>`;

export const piePagina = (links = 'info@tourevo.cl · tourevo.cl') => `
    <footer class="foot">
      <div class="who"><b>Tourevo</b> <span>· Private experiences</span></div>
      <div class="links">${links}</div>
    </footer>`;

// Imprime cada salida a PDF con Chromium. `salidas` son { html, pdf }.
export async function aPdf(salidas, { aqui, pie }) {
  const require_ = createRequire(import.meta.url);
  let chromium;
  for (const c of ['playwright', '/opt/node22/lib/node_modules/playwright', 'playwright-core']) {
    try { ({ chromium } = require_(c)); break; } catch { /* siguiente candidato */ }
  }
  if (!chromium) {
    console.error('No se encontró Playwright. Corré el build con --solo-html o instalá playwright.');
    process.exit(1);
  }

  const plantillaPie = `<div style="width:100%;font-size:7.5pt;font-family:Helvetica,Arial,sans-serif;color:#8497A0;padding:0 12mm;display:flex;justify-content:space-between;">
  <span>${pie}</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>`;

  const navegador = await chromium.launch({ args: ['--no-sandbox'] });
  for (const s of salidas) {
    const p = await navegador.newPage();
    await p.emulateMedia({ colorScheme: 'light', media: 'print' });
    await p.goto(`file://${join(aqui, s.html)}`, { waitUntil: 'networkidle' });
    await p.pdf({
      path: join(aqui, s.pdf),
      format: 'A4',
      printBackground: true,
      displayHeaderFooter: true,
      headerTemplate: '<div></div>',
      footerTemplate: plantillaPie,
      margin: { top: '11mm', bottom: '15mm', left: '12mm', right: '12mm' },
    });
    await p.close();
    console.log(`✓ ${s.pdf}`);
  }
  await navegador.close();
}
