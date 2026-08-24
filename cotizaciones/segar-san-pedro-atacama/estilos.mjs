// Hoja de estilos compartida por las dos versiones. Es la misma paleta y la
// misma tipografía de las propuestas anteriores de Tourevo: el documento tiene
// que leerse como parte de la misma casa, no como un archivo suelto.

export const estilos = `
  :root {
    --ground: #EEF3F3;
    --paper: #FFFFFF;
    --glacier: #E7F0F0;
    --glacier-2: #F3F8F8;
    --ink: #16232B;
    --ink-soft: #566A72;
    --ink-faint: #8497A0;
    --line: #DBE6E6;
    --line-soft: #EAF1F1;
    --accent: #0F766E;
    --accent-deep: #0B5B54;
    --summit: #2F6E8F;
    --warn: #B4632A;
    --warn-wash: #FBF1E8;
    --warn-line: #E9CFB6;
    --shadow: 24px 40px 80px -40px rgba(16, 45, 50, .35);
    --font-serif: "Iowan Old Style", "Palatino Linotype", "Palatino", "Georgia", serif;
    --font-sans: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }
  @media (prefers-color-scheme: dark) {
    :root {
      --ground: #0A0F13; --paper: #121a20; --glacier: #172129; --glacier-2: #141d24;
      --ink: #E7EDEE; --ink-soft: #9EACB2; --ink-faint: #6C7B82; --line: #26313A;
      --line-soft: #1E2831; --accent: #2DD4BF; --accent-deep: #5EEAD4; --summit: #8FBAD3;
      --warn: #E0955C; --warn-wash: #241A12; --warn-line: #4A3524;
      --shadow: 24px 40px 90px -40px rgba(0, 0, 0, .7);
    }
  }

  * { box-sizing: border-box; }
  body {
    margin: 0;
    background: radial-gradient(120% 60% at 50% -10%, var(--glacier) 0%, transparent 60%), var(--ground);
    color: var(--ink);
    font-family: var(--font-sans);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    padding: clamp(16px, 4vw, 56px) 16px;
  }
  .wrap { max-width: 820px; margin: 0 auto; }
  .paper { background: var(--paper); border: 1px solid var(--line); border-radius: 16px; box-shadow: var(--shadow); overflow: hidden; }

  /* ---- Encabezado ---- */
  .head { position: relative; padding: clamp(26px, 5vw, 44px) clamp(22px, 5vw, 48px) 26px; border-bottom: 1px solid var(--line); background: linear-gradient(180deg, var(--glacier-2), var(--paper)); overflow: hidden; }
  .ridge { position: absolute; left: 0; right: 0; bottom: -1px; height: 46px; width: 100%; display: block; color: var(--accent); opacity: .10; }
  .brandrow { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; flex-wrap: wrap; position: relative; z-index: 1; }
  .brand { display: flex; align-items: center; gap: 12px; }
  .mark { width: 42px; height: 42px; border-radius: 12px; flex: none; background: linear-gradient(140deg, var(--accent), var(--summit)); display: grid; place-items: center; color: #fff; box-shadow: 0 6px 16px -6px color-mix(in srgb, var(--accent) 70%, transparent); }
  .wordmark { font-weight: 700; font-size: 20px; letter-spacing: -.01em; }
  .wordmark small { display: block; font-size: 10.5px; font-weight: 600; letter-spacing: .18em; text-transform: uppercase; color: var(--ink-faint); margin-top: 2px; }
  .eyebrow { font-size: 11px; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; color: var(--accent); }
  .doc-title { font-family: var(--font-serif); font-weight: 600; font-size: clamp(26px, 5.2vw, 36px); line-height: 1.05; margin: 14px 0 0; letter-spacing: -.01em; text-wrap: balance; position: relative; z-index: 1; }
  .doc-sub { margin: 10px 0 0; font-size: 15px; color: var(--ink-soft); max-width: 56ch; position: relative; z-index: 1; }
  .meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; position: relative; z-index: 1; }
  .chip { font-size: 12px; font-weight: 600; color: var(--ink-soft); background: var(--glacier); border: 1px solid var(--line); padding: 5px 11px; border-radius: 999px; font-variant-numeric: tabular-nums; }
  .chip b { color: var(--ink); font-weight: 700; }

  /* ---- Secciones ---- */
  .body { padding: clamp(22px, 4.5vw, 40px) clamp(22px, 5vw, 48px) clamp(28px, 5vw, 44px); }
  section + section { margin-top: clamp(26px, 4vw, 36px); }
  .label { font-size: 11px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--ink-faint); margin: 0 0 12px; display: flex; align-items: center; gap: 10px; }
  .label::after { content: ""; flex: 1; height: 1px; background: var(--line); }
  .lede { margin: 0 0 14px; font-size: 14.5px; color: var(--ink-soft); max-width: 68ch; }

  .client { display: flex; align-items: baseline; gap: 10px 18px; flex-wrap: wrap; }
  .client .name { font-size: 19px; font-weight: 700; }

  .facts { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1px; background: var(--line); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
  .fact { background: var(--paper); padding: 14px 16px; }
  .fact dt { font-size: 11px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--ink-faint); margin: 0 0 4px; }
  .fact dd { margin: 0; font-size: 15px; font-weight: 650; }

  /* ---- Nota de bienvenida ---- */
  .welcome { display: flex; gap: 16px; align-items: flex-start; padding: 20px 22px; background: linear-gradient(135deg, var(--glacier), var(--glacier-2)); border: 1px solid var(--line); border-left: 3px solid var(--accent); border-radius: 4px 14px 14px 4px; }
  .welcome .monogram { width: 40px; height: 40px; border-radius: 50%; flex: none; background: linear-gradient(140deg, var(--accent), var(--summit)); color: #fff; display: grid; place-items: center; font-weight: 700; font-size: 15px; margin-top: 2px; }
  .welcome .msg { font-family: var(--font-serif); font-size: 15.5px; line-height: 1.6; color: var(--ink); font-style: italic; margin: 0; }
  .welcome .msg b { font-style: normal; font-weight: 600; }
  .welcome .sig { display: block; margin-top: 10px; font-family: var(--font-sans); font-style: normal; font-size: 12.5px; font-weight: 600; color: var(--ink-soft); }
  .welcome .sig b { color: var(--accent-deep); font-weight: 700; }

  /* ---- Itinerario ---- */
  .days { display: grid; gap: 14px; }
  .day { border: 1px solid var(--line); border-radius: 13px; overflow: hidden; background: var(--paper); }
  .day-head { display: flex; align-items: baseline; gap: 13px; padding: 12px 18px; background: var(--glacier-2); border-bottom: 1px solid var(--line); flex-wrap: wrap; }
  .day-date { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--accent); font-variant-numeric: tabular-nums; }
  .day-name { font-family: var(--font-serif); font-size: 16.5px; font-weight: 600; letter-spacing: -.01em; flex: 1; min-width: 160px; }
  .day-sum { font-size: 12px; font-weight: 700; color: var(--ink-soft); background: var(--paper); border: 1px solid var(--line); padding: 4px 10px; border-radius: 999px; font-variant-numeric: tabular-nums; }

  .svc { padding: 15px 18px; border-bottom: 1px solid var(--line-soft); }
  .svc:last-child { border-bottom: 0; }
  .svc-top { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 6px; }
  .svc-t { font-size: 12.5px; font-weight: 700; color: var(--summit); font-variant-numeric: tabular-nums; }
  .svc-h { font-size: 15.5px; font-weight: 750; letter-spacing: -.005em; flex: 1; min-width: 200px; }
  .tag { font-size: 10.5px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--accent); background: color-mix(in srgb, var(--accent) 10%, var(--paper)); border: 1px solid color-mix(in srgb, var(--accent) 28%, var(--line)); padding: 3px 9px; border-radius: 999px; }
  .svc p { margin: 0; font-size: 13.8px; line-height: 1.5; color: var(--ink-soft); }
  .svc .inc { margin-top: 8px; font-size: 13px; color: var(--ink); }
  .svc .inc b { font-weight: 700; }
  .svc .flag { margin-top: 9px; font-size: 12.5px; line-height: 1.45; color: var(--warn); background: var(--warn-wash); border: 1px solid var(--warn-line); border-radius: 9px; padding: 9px 12px; }

  /* ---- Franja de subtotales por tour ---- */
  .pstrip { display: flex; flex-wrap: wrap; gap: 1px; margin-top: 12px; background: var(--line); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
  .pstrip > div { background: var(--glacier-2); padding: 9px 14px; flex: 1 1 120px; }
  .pstrip i { display: block; font-style: normal; font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-faint); margin-bottom: 3px; }
  .pstrip b { font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .pstrip .sub { background: color-mix(in srgb, var(--accent) 9%, var(--paper)); flex: 1 1 150px; }
  .pstrip .sub i { color: var(--accent-deep); }
  .pstrip .sub b { color: var(--accent-deep); font-size: 15px; }
  .pstrip .nil b { color: var(--ink-faint); font-weight: 650; }

  /* ---- Tabla de precios ---- */
  .tbl-wrap { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
  table { border-collapse: collapse; width: 100%; font-size: 13.5px; }
  thead th { font-size: 10px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ink-faint); background: var(--glacier-2); text-align: left; padding: 10px 14px; border-bottom: 1px solid var(--line); }
  th.r, td.r { text-align: right; }
  tbody td { padding: 10px 14px; border-bottom: 1px solid var(--line-soft); vertical-align: top; line-height: 1.4; }
  tbody td.r { font-variant-numeric: tabular-nums; font-weight: 650; white-space: nowrap; }
  tbody td.sub { font-weight: 750; color: var(--accent-deep); background: color-mix(in srgb, var(--accent) 5%, var(--paper)); }
  tbody td em { font-style: normal; display: block; font-size: 12px; color: var(--ink-faint); margin-top: 2px; }
  tbody td .n { display: inline-block; min-width: 17px; color: var(--ink-faint); font-weight: 700; font-variant-numeric: tabular-nums; }
  tfoot td.r { white-space: nowrap; }
  tfoot td { padding: 12px 14px; border-top: 2px solid var(--line); background: var(--glacier-2); font-weight: 750; font-size: 13.5px; font-variant-numeric: tabular-nums; }
  tfoot td.sub { color: var(--accent-deep); background: color-mix(in srgb, var(--accent) 10%, var(--paper)); font-size: 15px; }

  /* ---- Total del pack ---- */
  .total { display: grid; grid-template-columns: 1fr auto; gap: 10px 24px; align-items: end; background: linear-gradient(135deg, var(--accent-deep), var(--summit)); color: #fff; border-radius: 14px; padding: 22px 24px; margin-top: 14px; }
  .total .tlabel { font-size: 11px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; opacity: .82; }
  .total .calc { font-size: 13.5px; font-weight: 600; opacity: .92; font-variant-numeric: tabular-nums; margin-top: 6px; }
  .total .amount { font-family: var(--font-serif); font-size: clamp(30px, 6vw, 40px); font-weight: 600; line-height: 1; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .total .grp { grid-column: 1 / -1; display: flex; justify-content: space-between; align-items: baseline; gap: 16px; flex-wrap: wrap; border-top: 1px solid rgba(255,255,255,.28); padding-top: 12px; margin-top: 4px; font-size: 13.5px; font-weight: 650; font-variant-numeric: tabular-nums; opacity: .95; }
  .total .grp b { font-size: 19px; font-weight: 700; font-family: var(--font-serif); }

  /* ---- Alternativas ---- */
  .alts { display: grid; gap: 14px; }
  .alt { border: 1px solid var(--line); border-radius: 13px; overflow: hidden; }
  .alt-head { display: flex; align-items: baseline; gap: 11px; padding: 12px 18px; background: var(--glacier-2); border-bottom: 1px solid var(--line); flex-wrap: wrap; }
  .alt-rank { width: 22px; height: 22px; border-radius: 50%; flex: none; background: var(--accent); color: #fff; display: grid; place-items: center; font-size: 11.5px; font-weight: 700; align-self: center; }
  .alt-h { font-family: var(--font-serif); font-size: 16.5px; font-weight: 600; flex: 1; min-width: 200px; }
  .alt-t { font-size: 12.5px; font-weight: 700; color: var(--summit); font-variant-numeric: tabular-nums; }
  .alt-body { padding: 14px 18px 16px; }
  .alt-body p { margin: 0; font-size: 13.8px; line-height: 1.5; color: var(--ink-soft); }
  .alt-body .inc { margin-top: 8px; font-size: 13px; color: var(--ink); }
  .specs { display: flex; flex-wrap: wrap; gap: 7px; margin-top: 10px; }
  .spec { font-size: 11.5px; font-weight: 650; color: var(--ink-soft); background: var(--glacier-2); border: 1px solid var(--line); padding: 4px 10px; border-radius: 8px; font-variant-numeric: tabular-nums; }
  .delta { margin-top: 11px; font-size: 12.8px; font-weight: 650; color: var(--ink-soft); border-top: 1px dashed var(--line); padding-top: 10px; font-variant-numeric: tabular-nums; }
  .delta b { color: var(--ink); font-weight: 750; }
  .delta .up { color: var(--warn); font-weight: 750; }
  .delta .down { color: var(--accent); font-weight: 750; }

  /* ---- Flota ---- */
  .fleet { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .veh { border: 1px solid var(--line); border-radius: 13px; overflow: hidden; background: var(--glacier-2); }
  .veh img { display: block; width: 100%; height: 190px; object-fit: cover; background: var(--glacier); }
  .veh .cap { padding: 12px 15px; }
  .veh.sinfoto { display: flex; align-items: center; }
  .veh.sinfoto .cap { padding: 13px 16px; }
  .veh .cap h4 { margin: 0 0 3px; font-size: 14.5px; font-weight: 750; }
  .veh .cap p { margin: 0; font-size: 12.5px; color: var(--ink-soft); }

  /* ---- Condiciones ---- */
  .cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .card { border: 1px solid var(--line); border-radius: 12px; padding: 17px 18px 15px; background: var(--glacier-2); }
  .card h4 { margin: 0 0 10px; font-size: 12.8px; font-weight: 750; }
  .card ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 9px; }
  .card li { font-size: 13.2px; color: var(--ink-soft); padding-left: 16px; position: relative; line-height: 1.45; }
  .card li::before { content: ""; position: absolute; left: 2px; top: 8px; width: 5px; height: 5px; border-radius: 50%; background: var(--accent); }
  .card.warn li::before { background: var(--warn); }
  .card b { color: var(--ink); font-weight: 700; }

  /* ---- Cierre ---- */
  .cta { margin-top: clamp(24px, 4vw, 34px); text-align: center; padding: clamp(20px, 4vw, 28px); border: 1px dashed color-mix(in srgb, var(--accent) 45%, var(--line)); border-radius: 14px; background: var(--glacier-2); }
  .cta .lead { margin: 0; font-family: var(--font-serif); font-size: 16px; font-style: italic; color: var(--ink); }
  .cta .after { margin: 10px 0 0; font-size: 12.5px; color: var(--ink-soft); }
  .foot { border-top: 1px solid var(--line); padding: 18px clamp(22px, 5vw, 48px); display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; background: var(--glacier-2); }
  .foot .who { font-size: 13px; }
  .foot .who span { color: var(--ink-soft); }
  .foot .links { font-size: 12.5px; color: var(--ink-soft); }
  .legend { max-width: 820px; margin: clamp(16px, 3vw, 22px) auto 0; font-size: 12px; color: var(--ink-faint); text-align: center; }

  @media (max-width: 640px) {
    .fleet, .cards { grid-template-columns: 1fr; }
    .total { grid-template-columns: 1fr; }
  }

  @media print {
    /* El PDF es un documento, no una pantalla: fijamos la paleta clara pase lo
       que pase con el sistema del lector, y le pedimos al motor que no
       descarte los fondos. */
    :root {
      --ground: #FFFFFF; --paper: #FFFFFF; --glacier: #E7F0F0; --glacier-2: #F5F9F9;
      --ink: #16232B; --ink-soft: #4E626B; --ink-faint: #788B94; --line: #D3E0E0;
      --line-soft: #E8F0F0; --accent: #0F766E; --accent-deep: #0B5B54; --summit: #2F6E8F;
      --warn: #A0561F; --warn-wash: #FBF1E8; --warn-line: #E9CFB6;
      --shadow: none;
    }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

    body { background: #fff; padding: 0; font-size: 9.5pt; line-height: 1.42; orphans: 3; widows: 3; }
    .wrap { max-width: none; }
    .paper { border: 0; border-radius: 0; box-shadow: none; }
    .head { padding: 0 0 14px; background: none; border-radius: 0; }
    .ridge { display: none; }
    .body { padding: 16px 0 0; }
    .foot { padding: 12px 0 0; background: none; border-radius: 0; }
    .legend { margin-top: 8px; }
    section + section { margin-top: 18px; }

    /* Chromium no fragmenta un contenedor grid entre páginas, así que las
       pilas altas de una sola columna salen del grid para paginar como
       bloques normales. */
    .days, .alts { display: block; }
    .day, .alt { margin-bottom: 10px; }
    .day:last-child, .alt:last-child { margin-bottom: 0; }

    /* Un día de itinerario puede pasar de media página: dejamos que se parta,
       pero nunca justo después de su propio encabezado ni dentro de un
       servicio. */
    .day, .alt, .tbl-wrap, table { break-inside: auto; }
    .day-head, .alt-head, .label, thead, .svc-top { break-after: avoid; }
    .svc, .pstrip, tbody tr, tfoot tr, .card, .veh, .welcome, .total,
    .facts, .cta, .alt-body, .delta { break-inside: avoid; }
    thead { display: table-header-group; }
    /* table-footer-group repite el pie en TODAS las páginas: el total del
       programa aparecía a media tabla como si cerrara sólo esas filas. */
    tfoot { display: table-row-group; }
    .facts { grid-template-columns: repeat(3, 1fr); }

    /* Ritmo más apretado en papel: la página es un presupuesto fijo. */
    .day-head, .alt-head { padding: 8px 13px; }
    .svc { padding: 11px 13px; }
    .alt-body { padding: 11px 13px 12px; }
    .pstrip > div { padding: 7px 11px; }
    .card { padding: 13px; }
    .card ul { gap: 7px; }
    .welcome { padding: 15px 17px; }
    tbody td { padding: 7px 11px; }
    thead th { padding: 7px 11px; }
    tfoot td { padding: 9px 11px; }
    .total { padding: 16px 18px; margin-top: 11px; }
    .veh img { height: 150px; }

    /* El cierre tiene que caer entero en la última página: una hoja con sólo
       el pie parece un PDF cortado. */
    .cta { margin-top: 12px; padding: 15px; }
    .foot, .legend { break-before: avoid; }
  }
`;
