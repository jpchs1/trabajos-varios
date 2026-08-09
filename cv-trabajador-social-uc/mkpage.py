# -*- coding: utf-8 -*-
"""Genera la pagina HTML con las tres propuestas, desde cv_content.py.
Incluye edicion en linea (contenteditable), autoguardado en localStorage,
descarga de la plantilla original (Word/PDF) e impresion/exportacion de la
version editada."""
import sys, html
sys.path.insert(0, '.')
import cv_content as C

E = lambda s: html.escape(s, quote=True)
import json, os, base64

DOCX_MIME = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
FILES = {
  "minimal":  ("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_01_Minimal.docx",
               "fin_CV_Jeniffer_Mieres_01_Minimal.pdf",
               "CV Jeniffer Mieres - Minimal"),
  "banda":    ("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_02_Banda.docx",
               "fin_CV_Jeniffer_Mieres_02_Banda.pdf",
               "CV Jeniffer Mieres - Banda"),
  "sidebar":  ("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_03_Sidebar.docx",
               "fin_CV_Jeniffer_Mieres_03_Sidebar.pdf",
               "CV Jeniffer Mieres - Sidebar"),
}
def b64(path):
    return base64.b64encode(open(path, "rb").read()).decode()

FITS = json.load(open("fits.json")) if os.path.exists("fits.json") else {"minimal":1.0,"banda":1.0,"sidebar":1.0}
FONTS = open('fsub/fonts.css').read()

# ---------------------------------------------------------------- edicion en linea
def ED(extra_class=""):
    cls = ("ed " + extra_class).strip()
    return f'class="{cls}" contenteditable="true" spellcheck="false" autocorrect="off"'

def leaf(tag, cls, role, text):
    return f'<{tag} {ED(cls)} data-role="{role}">{E(text)}</{tag}>'

def bullets(job, glyph):
    out = []
    for pre, key, post in job["bullets"]:
        inner = f'{E(pre)}<b>{E(key)}</b>{E(post)}' if key else E(pre + post)
        out.append(f'<li data-role="bullet"><span class="bg">{glyph}</span>'
                    f'<span {ED()}>{inner}</span></li>')
    return "".join(out)

def tags(items, sep='<i class="sep">·</i>'):
    return sep.join(f'<span {ED()}>{E(t)}</span>' for t in items)

def s3_tags(items):
    return "".join(f'<li data-role="bullet"><span class="bg">▪</span><span {ED()}>{E(t)}</span></li>'
                   for t in items)

def claim_dot(cls):
    a = f'<span {ED()}>{E(C.TITULO)}</span>'
    b = f'<span {ED()}>{E(C.CLAIM)}</span>'
    return f'<p class="{cls}" data-role="claim">{a}<i class="csep">·</i>{b}</p>'

def claim_pipe(cls):
    a = f'<span {ED()}>{E(C.TITULO)}</span>'
    b = f'<span {ED()}>{E(C.CLAIM)}</span>'
    return f'<p class="{cls}" data-role="claim">{a}<i>|</i>{b}</p>'

def meta_grouped(cls):
    a = f'<span {ED()}>{E(C.FONO)}</span>'
    b = f'<span {ED()}>{E(C.MAIL)}</span>'
    c = f'<span {ED()}>{E(C.CIUDAD)}</span>'
    grp = f'<span class="grp">{a}<i class="msep">·</i>{b}</span>'
    return f'<p class="{cls}" data-role="meta">{grp}{c}</p>'

def meta_flat(cls):
    a = f'<span {ED()}>{E(C.FONO)}</span>'
    b = f'<span {ED()}>{E(C.MAIL)}</span>'
    c = f'<span {ED()}>{E(C.CIUDAD)}</span>'
    return f'<p class="{cls}" data-role="meta">{a}<i class="msep">·</i>{b}<i class="msep">·</i>{c}</p>'

def org_line(cls, job):
    name = f'<span {ED()}>{E(job["empresa"])}</span>'
    date = f'<span {ED()}>{E(job["fechas"])}</span>'
    return f'<p class="{cls}" data-role="org">{name}{date}</p>'

def edu_block(wrap_cls, title, inst, year):
    first = f'<p><b {ED()}>{E(title)}</b><span {ED()}>{E(year)}</span></p>'
    second = f'<p {ED("i")}>{E(inst)}</p>'
    return f'<div class="{wrap_cls}" data-role="edu">{first}{second}</div>'

def edu_block_s3(wrap_cls, title, inst, year):
    first = f'<p><b {ED()}>{E(title)}</b></p>'
    second = f'<p {ED("i")}>{E(inst)}  ·  {E(year)}</p>'
    return f'<div class="{wrap_cls}" data-role="edu">{first}{second}</div>'

# ---------------------------------------------------------------- botones
def printer_svg():
    return ('<svg viewBox="0 0 24 24" aria-hidden="true">'
            '<path d="M6 9V4h12v5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '<rect x="4" y="9" width="16" height="8" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.5"/>'
            '<path d="M7 14h10v6H7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '</svg>')

def word_svg():
    return ('<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h9l5 5v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 '
            '1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '<path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '<path d="M7.2 12.5l1.15 5h.05l1.1-5h1l1.1 5h.05l1.15-5" fill="none" stroke="currentColor" '
            'stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>')

def pdf_svg():
    return ('<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h9l5 5v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 '
            '1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '<path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>'
            '<path d="M7 17.2V12h1.55c.85 0 1.45.6 1.45 1.4s-.6 1.4-1.45 1.4H7m5.3 2.4V12h1.1c1.35 0 2.2 1 2.2 '
            '2.6s-.85 2.6-2.2 2.6h-1.1zm5.5 0V12h2.6M17.8 14.6h2" fill="none" stroke="currentColor" '
            'stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>')

def downloads(sid):
    docx_p, pdf_p, label = FILES[sid]
    docx_href = f"data:{DOCX_MIME};base64,{b64(docx_p)}"
    pdf_href  = f"data:application/pdf;base64,{b64(pdf_p)}"
    return f'''<div class="dl">
      <a class="dl-btn word" download="{E(label)}.docx" href="{docx_href}">
        {word_svg()}<span>Descargar Word<i>.docx</i></span></a>
      <a class="dl-btn pdf" download="{E(label)}.pdf" href="{pdf_href}">
        {pdf_svg()}<span>Descargar PDF<i>.pdf</i></span></a>
      <button type="button" class="dl-btn ghost print" data-variant="{sid}">
        {printer_svg()}<span>Imprimir esta versión<i>PDF</i></span></button>
      <button type="button" class="dl-btn edit export-word" data-variant="{sid}" hidden>
        {word_svg()}<span>Descargar edición<i>.docx</i></span></button>
    </div>
    <p class="dl-hint"><b>Word</b> y <b>PDF</b> descargan la plantilla original, verificada y lista para enviar.
      Haz clic en cualquier texto de una hoja para editarlo — se guarda solo en este navegador — y aparecerá
      la opción de descargar tu versión editada.</p>'''

# ================================================================ VARIANTE 1 (Minimal)
def v1_p1():
    return f"""
<div class="s1 pg">
  {leaf('h1','s1-name','name', C.NOMBRE)}
  {claim_dot('s1-claim')}
  {meta_grouped('s1-meta')}
  <div class="s1-hr"></div>
  {s1_head('Perfil')}
  {''.join(leaf('p','s1-p','p', x) for x in C.PERFIL)}
  {s1_head('Áreas de expertise')}
  <p class="s1-tags" data-role="tagline">{tags(C.EXPERTISE)}</p>
  {s1_head('Experiencia profesional')}
  {s1_job(C.JOBS[0], first=True)}
  {s1_job(C.JOBS[1])}
</div>"""

def v1_p2():
    edu = "".join(edu_block('s1-edu', t, i, y) for t, i, y in C.EDU)
    certs = "".join(f'<li data-role="bullet"><span class="bg">—</span>'
                     f'<span {ED()}><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""
<div class="s1 pg">
  {s1_job(C.JOBS[2], first=True)}
  {s1_job(C.JOBS[3])}
  {s1_head('Formación académica')}{edu}
  {s1_head('Cursos y certificaciones')}<ul class="s1-ul">{certs}</ul>
  {s1_head('Competencias')}
  <p class="s1-tags" data-role="tagline">{tags(C.COMPETENCIAS)}</p>
  {s1_head('Herramientas y disponibilidad')}
  <ul class="s1-ul">
    <li data-role="bullet"><span class="bg">—</span><span {ED()}><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li data-role="bullet"><span class="bg">—</span><span {ED()}><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul>
</div>"""

def s1_head(t):  return f'<div class="s1-rule"></div>{leaf("h2","s1-h","h",t)}'
def s1_job(j, first=False):
    return f"""<div class="s1-job{' first' if first else ''}">
  {org_line('s1-org', j)}
  {leaf('p','s1-role','role', j['cargo'])}
  <ul class="s1-ul">{bullets(j, '—')}</ul></div>"""

# ================================================================ VARIANTE 2 (Banda)
def band(cls, repeat=True):
    if not repeat: return ""
    return f"""<header class="{cls}-band">
  {leaf('p', cls + '-name', 'name', C.NOMBRE_T)}
  {claim_pipe(cls + '-claim')}
  {meta_flat(cls + '-meta')}
</header>"""

def s2_head(t): return f'{leaf("h2","s2-h","h",t)}<div class="s2-rule"></div>'
def s2_job(j):
    return f"""<div class="s2-job">
  {org_line('s2-org', j)}
  {leaf('p','s2-role','role', j['cargo'])}
  <ul class="s2-ul">{bullets(j, '▪')}</ul></div>"""

def v2_p1():
    return f"""<div class="s2 pg">{band('s2')}<div class="s2-body">
  {s2_head('Perfil profesional')}
  {''.join(leaf('p','s2-p','p', x) for x in C.PERFIL)}
  {s2_head('Áreas de expertise')}<p class="s2-tags" data-role="tagline">{tags(C.EXPERTISE)}</p>
  {s2_head('Experiencia profesional')}{s2_job(C.JOBS[0])}{s2_job(C.JOBS[1])}
</div></div>"""

def v2_p2():
    edu = "".join(edu_block('s2-edu', t, i, y) for t, i, y in C.EDU)
    certs = "".join(f'<li data-role="bullet"><span class="bg">▪</span>'
                     f'<span {ED()}><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""<div class="s2 pg"><div class="s2-body s2-cont">
  {s2_job(C.JOBS[2])}{s2_job(C.JOBS[3])}
  {s2_head('Formación académica')}{edu}
  {s2_head('Cursos y certificaciones')}<ul class="s2-ul">{certs}</ul>
  {s2_head('Competencias')}<p class="s2-tags" data-role="tagline">{tags(C.COMPETENCIAS)}</p>
  {s2_head('Herramientas y disponibilidad')}
  <ul class="s2-ul">
    <li data-role="bullet"><span class="bg">▪</span><span {ED()}><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li data-role="bullet"><span class="bg">▪</span><span {ED()}><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul></div></div>"""

# ================================================================ VARIANTE 3 (Sidebar)
def s3_head(t): return f'{leaf("h2","s3-h","h",t)}<div class="s3-rule"></div>'
def s3_job(j):
    return f"""<div class="s3-job">
  {leaf('p','s3-org','orgname', j['empresa'])}
  {leaf('p','s3-role','role', j['cargo'])}
  {leaf('p','s3-dates','dates', j['fechas'])}
  <ul class="s3-ul">{bullets(j, '▪')}</ul></div>"""

def v3_p1():
    return f"""<div class="s3 pg">{band('s3')}<div class="s3-cols">
  <aside class="s3-side">
    {s3_head('Áreas de expertise')}<ul class="s3-tl">{s3_tags(C.EXPERTISE)}</ul>
    {s3_head('Competencias')}<ul class="s3-tl">{s3_tags(C.COMPETENCIAS)}</ul>
  </aside>
  <main class="s3-main">
    {s3_head('Perfil profesional')}
    {''.join(leaf('p','s3-p','p', x) for x in C.PERFIL)}
    {s3_head('Experiencia profesional')}{s3_job(C.JOBS[0])}{s3_job(C.JOBS[1])}
  </main></div></div>"""

def v3_p2():
    edu = "".join(edu_block_s3('s3-edu', t, i, y) for t, i, y in C.EDU)
    return f"""<div class="s3 pg">{band('s3')}<div class="s3-cols">
  <aside class="s3-side">
    {s3_head('Herramientas')}<p class="s3-sp" {ED()} data-role="p">{E(C.HERRAMIENTAS)}</p>
    {s3_head('Disponibilidad')}<p class="s3-sp" {ED()} data-role="p">{E(C.DISPONIBILIDAD)}</p>
    {s3_head('Cursos y certificaciones')}
    <ul class="s3-tl">{''.join(f'<li data-role="bullet"><span class="bg">▪</span><span {ED()}>'
                                f'<b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)}</ul>
  </aside>
  <main class="s3-main">
    {s3_head('Experiencia profesional (cont.)')}{s3_job(C.JOBS[2])}{s3_job(C.JOBS[3])}
    {s3_head('Formación académica')}{edu}
  </main></div></div>"""

# ---------------------------------------------------------------- ficha tecnica
SPECS = [
 dict(id="minimal", n="01", name="Minimal", tag="Suizo · sin color", accent="0D0D0D", font="Calibri",
      note="Reglas cortas y gruesas, versales espaciadas, cero color. El lenguaje de las "
           "consultoras de búsqueda ejecutiva: nada decora, todo jerarquiza.",
      spec=[("Tipografía","Calibri"),("Cuerpo","9,1 pt"),("Acento","Ninguno"),
            ("Holgura pág. 1","2,86 cm"),("Riesgo ATS","Nulo")], rec=False,
      pages=[v1_p1(), v1_p2()]),
 dict(id="banda", n="02", name="Banda", tag="Bloque de color a sangre", accent="13323C", font="Cambria",
      note="Bloque sólido en el encabezado con el nombre en blanco y cuerpo limpio abajo. "
           "Serif para nombre y cargos contra sans en el texto. Máximo impacto sin riesgo técnico.",
      spec=[("Tipografía","Cambria + Calibri"),("Cuerpo","9,6 pt"),("Acento","Petróleo #13323C"),
            ("Holgura pág. 1","2,62 cm"),("Riesgo ATS","Nulo")], rec=True,
      pages=[v2_p1(), v2_p2()]),
 dict(id="sidebar", n="03", name="Sidebar", tag="Dos columnas", accent="1B3A4B", font="Calibri",
      note="Barra lateral tramada con expertise y competencias; columna principal con perfil y "
           "experiencia. Es el formato más usado hoy y el único con un matiz técnico.",
      spec=[("Tipografía","Calibri"),("Cuerpo","9,3 pt"),("Acento","Petróleo #1B3A4B"),
            ("Holgura pág. 1","3,31 cm"),("Riesgo ATS","Bajo–moderado")], rec=False,
      pages=[v3_p1(), v3_p2()]),
]

tabs = "".join(
  f'<button role="tab" id="t-{s["id"]}" aria-controls="p-{s["id"]}" '
  f'aria-selected="{"true" if i==1 else "false"}" tabindex="{0 if i==1 else -1}">'
  f'<i>{s["n"]}</i>{E(s["name"])}</button>' for i, s in enumerate(SPECS))

def figure(sheet_html, page_no, fit):
    return f'''<figure class="frame">
      <div class="sheet" style="--fit:{fit:.4f}">{sheet_html}</div>
      <figcaption><span>Página {page_no}</span>
        <span class="fig-tools"><button type="button" class="reset-btn">Restablecer</button>
        <span class="status" aria-live="polite"></span></span>
      </figcaption>
    </figure>'''

panels = ""
for i, s in enumerate(SPECS):
    rows = "".join(f'<div class="sp"><dt>{E(k)}</dt><dd>{E(v)}</dd></div>' for k, v in s["spec"])
    sheets = "".join(figure(p, j + 1, FITS[s["id"]]) for j, p in enumerate(s["pages"]))
    panels += f"""
<section role="tabpanel" id="p-{s['id']}" aria-labelledby="t-{s['id']}" {'hidden' if i!=1 else ''}>
  <div class="lede">
    <div class="lede-txt">
      <h2>{E(s['name'])}{' <em>Recomendado</em>' if s['rec'] else ''}</h2>
      <p class="kicker">{E(s['tag'])}</p>
      <p class="note">{E(s['note'])}</p>
      {downloads(s['id'])}
    </div>
    <dl class="specs">{rows}</dl>
  </div>
  <div class="sheets">{sheets}</div>
</section>"""

VARIANT_CFG = "{" + ",".join(
  f'"{s["id"]}":{{accent:"{s["accent"]}",font:"{s["font"]}"}}' for s in SPECS) + "}"

# ---------------------------------------------------------------- CSS
CSS = FONTS + r"""
:root{
  --ground:#EBEDEF; --raise:#F7F8F9; --ink:#14171A; --muted:#6A7075;
  --line:#D6D9DD; --line-soft:#E2E5E8; --accent:#1B3A4B; --accent-ink:#FFFFFF;
  --shadow:0 1px 2px rgba(16,22,26,.06), 0 12px 32px -12px rgba(16,22,26,.28);
  --dl-word:#2957A4; --dl-pdf:#B23A2E; --dl-edit:#1E8E5A;
  --ui:ui-sans-serif,"Segoe UI",Roboto,system-ui,-apple-system,"Helvetica Neue",Arial,sans-serif;
}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){
  --ground:#131619; --raise:#1B1F23; --ink:#E7E9EB; --muted:#979DA3;
  --line:#272C31; --line-soft:#22262A; --accent:#8AB6C7; --accent-ink:#0E1417;
  --shadow:0 1px 2px rgba(0,0,0,.5), 0 18px 44px -14px rgba(0,0,0,.72);
  --dl-word:#8FB4EE; --dl-pdf:#EE998B; --dl-edit:#6FCF9B;
}}
:root[data-theme="dark"]{
  --ground:#131619; --raise:#1B1F23; --ink:#E7E9EB; --muted:#979DA3;
  --line:#272C31; --line-soft:#22262A; --accent:#8AB6C7; --accent-ink:#0E1417;
  --shadow:0 1px 2px rgba(0,0,0,.5), 0 18px 44px -14px rgba(0,0,0,.72);
  --dl-word:#8FB4EE; --dl-pdf:#EE998B; --dl-edit:#6FCF9B;
}
*{box-sizing:border-box}
body{margin:0;background:var(--ground);color:var(--ink);font-family:var(--ui);
  font-size:16px;line-height:1.5;-webkit-font-smoothing:antialiased}
.wrap{max-width:1360px;margin:0 auto;padding:clamp(28px,5vw,64px) clamp(18px,4vw,44px) 96px}

/* ---------- encabezado de la pagina ---------- */
.top{display:flex;flex-wrap:wrap;gap:28px 48px;align-items:flex-end;justify-content:space-between;
  padding-bottom:26px;border-bottom:1px solid var(--line)}
.eyebrow{font-size:.7rem;letter-spacing:.19em;text-transform:uppercase;color:var(--accent);
  font-weight:650;margin:0 0 12px}
.title{font-size:clamp(1.9rem,4.4vw,3rem);line-height:1.02;letter-spacing:-.028em;
  font-weight:680;margin:0;text-wrap:balance}
.title span{display:block;color:var(--muted);font-weight:380;letter-spacing:-.02em}
.stand{font-family:'CVSerif',Georgia,serif;font-size:clamp(.98rem,1.5vw,1.1rem);line-height:1.5;
  color:var(--muted);max-width:44ch;margin:0}
.stand b{color:var(--ink);font-weight:700}

/* ---------- aviso de edicion ---------- */
.edit-hint{display:flex;align-items:center;gap:10px;margin:18px 0 0;padding:12px 15px;
  background:var(--raise);border:1px solid var(--line);border-radius:10px;
  font-size:.83rem;color:var(--muted)}
.edit-hint svg{width:17px;height:17px;flex:none;color:var(--accent)}
.edit-hint b{color:var(--ink)}

/* ---------- selector ---------- */
.tabs{position:sticky;top:0;z-index:20;display:flex;gap:6px;flex-wrap:wrap;
  margin:0 -6px;padding:14px 6px;background:color-mix(in srgb,var(--ground) 88%,transparent);
  backdrop-filter:blur(10px);border-bottom:1px solid var(--line)}
.tabs button{appearance:none;display:inline-flex;align-items:baseline;gap:9px;cursor:pointer;
  border:1px solid var(--line);background:var(--raise);color:var(--muted);
  font:inherit;font-size:.86rem;font-weight:560;letter-spacing:.005em;
  padding:9px 16px;border-radius:999px;transition:background .16s,color .16s,border-color .16s}
.tabs button i{font-style:normal;font-size:.68rem;letter-spacing:.1em;opacity:.62;
  font-variant-numeric:tabular-nums}
.tabs button:hover{color:var(--ink);border-color:var(--muted)}
.tabs button[aria-selected="true"]{background:var(--accent);color:var(--accent-ink);
  border-color:var(--accent)}
.tabs button[aria-selected="true"] i{opacity:.7}
:where(button,a):focus-visible{outline:2px solid var(--accent);outline-offset:3px;border-radius:6px}

/* ---------- ficha de cada propuesta ---------- */
.lede{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);gap:34px 56px;
  align-items:start;padding:38px 0 30px}
@media (max-width:820px){.lede{grid-template-columns:1fr;gap:26px}}
.lede h2{margin:0;font-size:clamp(1.5rem,2.6vw,2rem);letter-spacing:-.024em;font-weight:660;
  display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.lede h2 em{font-style:normal;font-size:.6rem;letter-spacing:.15em;text-transform:uppercase;
  font-weight:700;color:var(--accent-ink);background:var(--accent);padding:5px 10px;border-radius:999px}
.kicker{margin:7px 0 0;font-size:.74rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted)}
.note{margin:16px 0 0;color:var(--muted);max-width:56ch;font-size:.94rem}
.dl{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}
.dl-btn{display:inline-flex;align-items:center;gap:9px;appearance:none;text-decoration:none;
  cursor:pointer;border-radius:9px;padding:10px 15px 10px 12px;font:inherit;font-size:.85rem;
  font-weight:600;letter-spacing:.005em;border:1px solid var(--line);
  background:var(--raise);color:var(--ink);transition:background .16s,border-color .16s,transform .12s}
.dl-btn svg{width:19px;height:19px;flex:none}
.dl-btn i{font-style:normal;font-weight:500;color:var(--muted);margin-left:1px}
.dl-btn:hover{border-color:var(--accent);transform:translateY(-1px)}
.dl-btn:active{transform:translateY(0)}
.dl-btn.word{color:var(--dl-word)}
.dl-btn.word:hover{background:color-mix(in srgb,var(--dl-word) 10%,var(--raise))}
.dl-btn.pdf{color:var(--dl-pdf)}
.dl-btn.pdf:hover{background:color-mix(in srgb,var(--dl-pdf) 10%,var(--raise))}
.dl-btn.ghost{color:var(--muted)}
.dl-btn.ghost:hover{color:var(--ink);background:var(--raise)}
.dl-btn[hidden]{display:none!important}
.dl-btn.edit{color:var(--dl-edit);border-color:var(--dl-edit)}
.dl-btn.edit:hover{background:color-mix(in srgb,var(--dl-edit) 12%,var(--raise))}
.dl-hint{margin:12px 0 0;font-size:.78rem;color:var(--muted);max-width:54ch;line-height:1.5}
.dl-hint b{color:var(--ink);font-weight:600}

.specs{margin:0;display:grid;grid-template-columns:1fr;gap:0;
  border-top:1px solid var(--line)}
.sp{display:flex;justify-content:space-between;gap:20px;padding:9px 0;
  border-bottom:1px solid var(--line-soft)}
.sp dt{margin:0;font-size:.76rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
.sp dd{margin:0;font-size:.86rem;font-weight:600;font-variant-numeric:tabular-nums;text-align:right}

/* ---------- hojas ---------- */
.sheets{display:grid;grid-template-columns:1fr 1fr;gap:clamp(18px,3vw,40px)}
@media (max-width:900px){.sheets{grid-template-columns:1fr}}
.frame{margin:0;min-width:0}
.frame figcaption{display:flex;align-items:baseline;justify-content:space-between;gap:10px;
  margin-top:11px;font-size:.72rem;letter-spacing:.13em;text-transform:uppercase;color:var(--muted)}
.fig-tools{display:flex;align-items:center;gap:10px;text-transform:none;letter-spacing:normal}
.reset-btn{appearance:none;border:none;background:none;cursor:pointer;color:var(--muted);
  font:inherit;font-size:.68rem;text-decoration:underline;text-underline-offset:2px;padding:0}
.reset-btn:hover{color:var(--accent)}
.status{font-size:.68rem;color:var(--dl-edit);opacity:0;transition:opacity .25s;font-weight:600}
.status.show{opacity:1}
.sheet{container-type:inline-size;background:#fff;color:#33373B;border-radius:2px;
  box-shadow:var(--shadow);overflow:hidden}
.pg{aspect-ratio:210/297;overflow:hidden}
.sheet b{font-weight:700}
.sheet ul{list-style:none;margin:0;padding:0}
.sheet p{margin:0}
.sheet .bg{flex:none}
.sheet li{display:flex}

/* ---------- campos editables ---------- */
.sheet .ed{outline:none;border-radius:.2em;cursor:text;transition:background-color .12s}
.sheet .ed:hover{background:rgba(37,99,235,.09)}
.sheet .ed:focus{background:rgba(37,99,235,.15);box-shadow:0 0 0 2px rgba(37,99,235,.38)}
.sheet .csep,.sheet .msep{opacity:.5;padding:0 .45em}

/* ============ 01 MINIMAL ============ */
.s1{font-family:'CVSans',Calibri,sans-serif;font-size:calc(1.529cqw*var(--fit,1));line-height:1.34;
  padding:5.0cqw 6.90cqw 0 7.62cqw;color:#3A3E42}
.s1-name{font-size:2.75em;font-weight:700;letter-spacing:-.012em;color:#0D0D0D;line-height:1.06}
.s1-claim{font-size:.88em;letter-spacing:.19em;color:#767B80;margin-top:.5em!important;text-transform:uppercase}
.s1-meta{display:flex;justify-content:space-between;gap:1em;font-size:.99em;margin-top:.85em!important;
  padding-bottom:.5em;border-bottom:.11em solid #0D0D0D}
.s1-meta span:last-child{color:#767B80}
.s1-rule{width:14.5%;border-top:.19em solid #0D0D0D;margin-top:1.6em}
.s1-h{font-size:.88em;font-weight:700;letter-spacing:.21em;text-transform:uppercase;color:#0D0D0D;
  margin:.62em 0 .62em}
.s1-p{margin-bottom:.5em!important;text-align:justify;hyphens:auto}
.s1-p:last-of-type{margin-bottom:0!important}
.s1-tags{line-height:1.62;color:#3A3E42}
.s1-tags .sep{font-style:normal;color:#B0B4B8;padding:0 .55em}
.s1-job{margin-top:1.25em}.s1-job.first{margin-top:0}
.s1-org{display:flex;justify-content:space-between;align-items:baseline;gap:1em;
  font-size:1.21em;font-weight:700;color:#0D0D0D}
.s1-org span{font-size:.77em;font-weight:400;color:#767B80;letter-spacing:.055em;white-space:nowrap}
.s1-role{font-size:.99em;color:#767B80;letter-spacing:.03em;margin-top:.22em!important}
.s1-ul{margin-top:.5em}
.s1-ul li{gap:.62em;margin-bottom:.42em;text-align:justify;hyphens:auto}
.s1-ul .bg{color:#B0B4B8}
.s1-ul b{color:#0D0D0D}
.s1-edu{margin-top:.72em}
.s1-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s1-edu p:first-child b{color:#0D0D0D;font-size:1.04em}
.s1-edu p:first-child span{font-size:.93em;color:#767B80;letter-spacing:.055em}
.s1-edu .i{font-size:.99em;color:#767B80}

/* ============ 02 BANDA ============ */
.s2{font-family:'CVSans',Calibri,sans-serif;font-size:calc(1.613cqw*var(--fit,1));line-height:1.32;color:#353A3D}
.s2-band{background:#13323C;color:#C5D2D6;padding:1.5em 7.62cqw 1.15em}
.s2-name{font-family:'CVSerif',Cambria,Georgia,serif;font-size:2.08em;color:#fff;
  letter-spacing:.012em;line-height:1.12}
.s2-claim{font-size:.83em;font-weight:700;letter-spacing:.185em;margin-top:.5em!important;text-transform:uppercase}
.s2-claim i{font-style:normal;padding:0 .9em;opacity:.55}
.s2-meta{font-size:.885em;margin-top:.42em!important;opacity:.92}
.s2-body{padding:1.5em 6.90cqw 0 7.62cqw}
.s2-body.s2-cont{padding-top:5.2cqw}
.s2-h{font-size:.885em;font-weight:700;letter-spacing:.22em;text-transform:uppercase;
  color:#13323C;margin:1.55em 0 0}
.s2-body>.s2-h:first-child{margin-top:0}
.s2-rule{width:8%;border-bottom:.17em solid #13323C;margin:.5em 0 .78em}
.s2-p{margin-bottom:.52em!important;text-align:justify;hyphens:auto}
.s2-tags{line-height:1.58}
.s2-tags .sep{font-style:normal;color:#AEB3B6;padding:0 .55em}
.s2-job{margin-top:1.2em}
.s2-body .s2-rule + .s2-job{margin-top:0}
.s2-org{display:flex;justify-content:space-between;align-items:baseline;gap:1em;
  font-family:'CVSerif',Cambria,Georgia,serif;font-size:1.09em;font-weight:700;color:#151A1C}
.s2-org span{font-family:'CVSans',Calibri,sans-serif;font-size:.86em;font-weight:400;
  color:#6E7478;letter-spacing:.045em;white-space:nowrap}
.s2-role{font-size:.95em;color:#6E7478;letter-spacing:.02em;margin-top:.2em!important}
.s2-ul{margin-top:.48em}
.s2-ul li{gap:.6em;margin-bottom:.44em;text-align:justify;hyphens:auto}
.s2-ul .bg{color:#13323C;font-size:.7em;line-height:1.9}
.s2-ul b{color:#151A1C}
.s2-edu{margin-top:.7em}
.s2-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s2-edu p:first-child b{font-family:'CVSerif',Cambria,Georgia,serif;color:#151A1C;font-size:1.04em}
.s2-edu p:first-child span{font-size:.9em;color:#6E7478;letter-spacing:.045em}
.s2-edu .i{font-size:.95em;color:#6E7478}

/* ============ 03 SIDEBAR ============ */
.s3{font-family:'CVSans',Calibri,sans-serif;font-size:calc(1.562cqw*var(--fit,1));line-height:1.3;color:#353A3D}
.s3-band{background:#1B3A4B;color:#C7D4DA;padding:1.4em 7.14cqw 1.1em}
.s3-name{font-size:2.4em;font-weight:700;color:#fff;letter-spacing:.008em;line-height:1.1}
.s3-claim{font-size:.85em;font-weight:700;letter-spacing:.19em;margin-top:.42em!important;text-transform:uppercase}
.s3-claim i{font-style:normal;padding:0 .9em;opacity:.55}
.s3-meta{font-size:.9em;margin-top:.36em!important;opacity:.92}
.s3-cols{display:grid;grid-template-columns:25.7% 1fr;gap:0;align-items:start;
  padding:1.1em 6.67cqw 0 7.14cqw}
.s3-side{background:#F1F4F5;padding:1.15em 1.25em 1.6em;margin-left:-1.25em}
.s3-main{padding-left:1.9em}
.s3-h{font-size:.82em;font-weight:700;letter-spacing:.2em;text-transform:uppercase;
  color:#1B3A4B;margin:1.5em 0 0;text-wrap:balance}
.s3-side>.s3-h:first-child,.s3-main>.s3-h:first-child{margin-top:0}
.s3-rule{border-bottom:.115em solid #1B3A4B;margin:.42em 0 .7em}
.s3-side .s3-rule{width:82%}
.s3-p{margin-bottom:.5em!important;text-align:justify;hyphens:auto}
.s3-tl li{gap:.5em;margin-bottom:.38em;font-size:.94em;line-height:1.26}
.s3-tl .bg,.s3-ul .bg{color:#1B3A4B;font-size:.68em;line-height:1.95}
.s3-sp{font-size:.94em;line-height:1.28}
.s3-job{margin-top:1.15em}
.s3-main .s3-rule + .s3-job{margin-top:0}
.s3-org{font-size:1.12em;font-weight:700;color:#151A1C}
.s3-role{font-size:.95em;color:#6E7478;margin-top:.16em!important}
.s3-dates{font-size:.9em;font-weight:700;color:#1B3A4B;letter-spacing:.045em;margin-top:.1em!important}
.s3-ul{margin-top:.45em}
.s3-ul li{gap:.55em;margin-bottom:.42em;text-align:justify;hyphens:auto}
.s3-ul b{color:#151A1C}
.s3-edu{margin-top:.62em}
.s3-edu b{color:#151A1C}
.s3-edu .i{font-size:.94em;color:#6E7478}

/* ---------- pie ---------- */
.foot{margin-top:64px;padding-top:26px;border-top:1px solid var(--line);
  display:flex;flex-wrap:wrap;gap:14px 40px;justify-content:space-between;
  color:var(--muted);font-size:.84rem}
.foot b{color:var(--ink);font-weight:620}

/* ---------- impresion ---------- */
#print-root{display:none}
@media print{
  body>*:not(#print-root){display:none!important}
  #print-root{display:block!important}
  #print-root .pframe{break-after:page}
  #print-root .pframe:last-child{break-after:auto}
  #print-root .sheet{width:210mm;height:297mm;box-shadow:none;border-radius:0}
  #print-root .ed{background:none!important;box-shadow:none!important}
  @page{size:A4;margin:0}
}
@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
"""

# ---------------------------------------------------------------- JS
JS = r"""
/* ================= selector de propuestas ================= */
const tabs=[...document.querySelectorAll('[role=tab]')];
function show(i){tabs.forEach((t,k)=>{const on=k===i;
  t.setAttribute('aria-selected',on);t.tabIndex=on?0:-1;
  document.getElementById(t.getAttribute('aria-controls')).hidden=!on;});}
tabs.forEach((t,i)=>{
  t.addEventListener('click',()=>show(i));
  t.addEventListener('keydown',e=>{
    const d=e.key==='ArrowRight'?1:e.key==='ArrowLeft'?-1:0;
    if(!d)return;e.preventDefault();
    const n=(i+d+tabs.length)%tabs.length;show(n);tabs[n].focus();});
});

/* ================= edicion en linea + autoguardado ================= */
const originals = new Map();
function sheetKey(sh){
  const section = sh.closest('section');
  const idx = [...section.querySelectorAll('.sheet')].indexOf(sh);
  return 'cvedit:'+section.id+':'+idx;
}
function variantOf(sh){ return sh.closest('section').id.replace('p-',''); }
function isEdited(sid){
  return [...document.querySelectorAll('#p-'+sid+' .sheet')]
    .some(sh=> sh.innerHTML !== originals.get(sh));
}
function refreshEditedUI(sid){
  const btn = document.querySelector(`.dl-btn.export-word[data-variant="${sid}"]`);
  if(btn) btn.hidden = !isEdited(sid);
}
function setStatus(fig, text){
  const tag = fig.querySelector('.status');
  if(!tag) return;
  tag.textContent = text;
  tag.classList.add('show');
  clearTimeout(tag._t);
  tag._t = setTimeout(()=>tag.classList.remove('show'), 1800);
}

document.querySelectorAll('.sheet').forEach(sh=>{
  originals.set(sh, sh.innerHTML);
  const saved = localStorage.getItem(sheetKey(sh));
  if(saved) sh.innerHTML = saved;
});
['minimal','banda','sidebar'].forEach(refreshEditedUI);

const timers = new WeakMap();
document.addEventListener('input', e=>{
  const ed = e.target.closest('.ed');
  if(!ed) return;
  const sh = ed.closest('.sheet');
  const fig = sh.closest('figure');
  setStatus(fig, 'Editando…');
  clearTimeout(timers.get(sh));
  timers.set(sh, setTimeout(()=>{
    localStorage.setItem(sheetKey(sh), sh.innerHTML);
    setStatus(fig, 'Guardado');
    refreshEditedUI(variantOf(sh));
  }, 500));
});

document.addEventListener('keydown', e=>{
  if(e.key==='Enter' && e.target.closest('.ed')) e.preventDefault();
});
document.addEventListener('paste', e=>{
  if(!e.target.closest('.ed')) return;
  e.preventDefault();
  const text=(e.clipboardData||window.clipboardData).getData('text/plain');
  document.execCommand('insertText', false, text);
});

document.querySelectorAll('.reset-btn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const fig = btn.closest('figure');
    const sh = fig.querySelector('.sheet');
    sh.innerHTML = originals.get(sh);
    localStorage.removeItem(sheetKey(sh));
    setStatus(fig, 'Restablecido');
    refreshEditedUI(variantOf(sh));
  });
});

/* ================= imprimir / PDF de la version actual ================= */
function buildPrintRoot(sid){
  const root=document.getElementById('print-root');
  root.innerHTML='';
  document.querySelectorAll('#p-'+sid+' .sheet').forEach(sh=>{
    const wrap=document.createElement('div'); wrap.className='pframe';
    const clone=sh.cloneNode(true);
    clone.removeAttribute('style');
    clone.querySelectorAll('.ed').forEach(e=>e.removeAttribute('contenteditable'));
    wrap.appendChild(clone);
    root.appendChild(wrap);
  });
}
document.querySelectorAll('.dl-btn.print').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    buildPrintRoot(btn.dataset.variant);
    requestAnimationFrame(()=>requestAnimationFrame(()=>window.print()));
  });
});
window.addEventListener('afterprint', ()=>{
  const r=document.getElementById('print-root'); if(r) r.innerHTML='';
});

/* ================= exportar Word de la version editada ================= */
const VARIANTS = __VARIANT_CFG__;
const CRC_TABLE = (()=>{ let c, t=new Uint32Array(256);
  for(let n=0;n<256;n++){ c=n; for(let k=0;k<8;k++) c=(c&1)?(0xEDB88320^(c>>>1)):(c>>>1); t[n]=c>>>0; }
  return t; })();
function crc32(bytes){ let crc=0xFFFFFFFF;
  for(let i=0;i<bytes.length;i++) crc=CRC_TABLE[(crc^bytes[i])&0xFF]^(crc>>>8);
  return (crc^0xFFFFFFFF)>>>0; }

function zipStore(files){
  const enc=new TextEncoder(); let parts=[], centrals=[], offset=0;
  files.forEach(f=>{
    const nameBytes=enc.encode(f.name), crc=crc32(f.data), size=f.data.length;
    const lh=new Uint8Array(30+nameBytes.length), dv=new DataView(lh.buffer);
    dv.setUint32(0,0x04034b50,true); dv.setUint16(4,20,true); dv.setUint16(6,0,true);
    dv.setUint16(8,0,true); dv.setUint16(10,0,true); dv.setUint16(12,0,true);
    dv.setUint32(14,crc,true); dv.setUint32(18,size,true); dv.setUint32(22,size,true);
    dv.setUint16(26,nameBytes.length,true); dv.setUint16(28,0,true);
    lh.set(nameBytes,30);
    parts.push(lh, f.data);
    const ch=new Uint8Array(46+nameBytes.length), cdv=new DataView(ch.buffer);
    cdv.setUint32(0,0x02014b50,true); cdv.setUint16(4,20,true); cdv.setUint16(6,20,true);
    cdv.setUint16(8,0,true); cdv.setUint16(10,0,true); cdv.setUint16(12,0,true); cdv.setUint16(14,0,true);
    cdv.setUint32(16,crc,true); cdv.setUint32(20,size,true); cdv.setUint32(24,size,true);
    cdv.setUint16(28,nameBytes.length,true); cdv.setUint16(30,0,true); cdv.setUint16(32,0,true);
    cdv.setUint16(34,0,true); cdv.setUint16(36,0,true); cdv.setUint32(38,0,true);
    cdv.setUint32(42,offset,true);
    ch.set(nameBytes,46);
    centrals.push(ch);
    offset += lh.length + f.data.length;
  });
  const centralStart=offset;
  let centralSize=0; centrals.forEach(c=>centralSize+=c.length);
  const eocd=new Uint8Array(22), edv=new DataView(eocd.buffer);
  edv.setUint32(0,0x06054b50,true); edv.setUint16(4,0,true); edv.setUint16(6,0,true);
  edv.setUint16(8,files.length,true); edv.setUint16(10,files.length,true);
  edv.setUint32(12,centralSize,true); edv.setUint32(16,centralStart,true); edv.setUint16(20,0,true);
  return new Blob([...parts, ...centrals, eocd],
    {type:'application/vnd.openxmlformats-officedocument.wordprocessingml.document'});
}

function xesc(t){ return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function wr(text, o){
  o=o||{};
  if(text==='') return '';
  let p='<w:rPr><w:rFonts w:ascii="'+(o.font||'Calibri')+'" w:hAnsi="'+(o.font||'Calibri')+'" w:cs="'+(o.font||'Calibri')+'"/>';
  if(o.bold) p+='<w:b/>';
  if(o.italic) p+='<w:i/>';
  if(o.caps) p+='<w:caps/>';
  if(o.color) p+='<w:color w:val="'+o.color+'"/>';
  p+='<w:sz w:val="'+(o.size||20)+'"/><w:szCs w:val="'+(o.size||20)+'"/></w:rPr>';
  return '<w:r>'+p+'<w:t xml:space="preserve">'+xesc(text)+'</w:t></w:r>';
}
function wTab(){ return '<w:r><w:tab/></w:r>'; }
function wPara(runs, o){
  o=o||{};
  let pp='<w:pPr>';
  if(o.pageBreak) pp+='<w:pageBreakBefore/>';
  if(o.tab) pp+='<w:tabs><w:tab w:val="right" w:pos="'+o.tab+'"/></w:tabs>';
  pp+='<w:spacing w:before="'+(o.before||0)+'" w:after="'+(o.after||0)+'" w:line="264" w:lineRule="auto"/>';
  if(o.justify) pp+='<w:jc w:val="both"/>';
  pp+='</w:pPr>';
  return '<w:p>'+pp+runs+'</w:p>';
}
function runsFromNode(el, base){
  let xml='';
  el.childNodes.forEach(n=>{
    if(n.nodeType===Node.TEXT_NODE){ if(n.textContent) xml+=wr(n.textContent, base); }
    else if(n.nodeType===Node.ELEMENT_NODE){
      if(n.classList && n.classList.contains('bg')) return;
      const bold = base.bold || n.tagName==='B' || n.tagName==='STRONG';
      const italic = base.italic || n.tagName==='I' || n.tagName==='EM';
      xml += runsFromNode(n, Object.assign({}, base, {bold, italic}));
    }
  });
  return xml;
}
function textOf(el){ return el.textContent.replace(/\s+/g,' ').trim(); }

const PAGE_TAB = 9866;
function buildParagraphs(sheetEl, accentHex, font){
  const items = sheetEl.querySelectorAll('[data-role]');
  let out = '';
  items.forEach(el=>{
    const role = el.dataset.role;
    if(role==='name'){
      out += wPara(runsFromNode(el,{bold:true,size:44,font:font}), {after:60});
    } else if(role==='claim'){
      const spans = el.querySelectorAll('.ed');
      const a = spans[0]?textOf(spans[0]):''; const b = spans[1]?textOf(spans[1]):'';
      out += wPara(wr(a,{bold:true,size:21,caps:true,color:'595959',font:font}) +
                    wr('   |   ',{size:21,color:'A8ADB2',font:font}) +
                    wr(b,{bold:true,size:21,caps:true,color:'595959',font:font}), {after:60});
    } else if(role==='meta'){
      const spans=[...el.querySelectorAll('.ed')];
      const txt = spans.map(textOf).join('   ·   ');
      out += wPara(wr(txt,{size:18,color:'6E7478',font:font}), {after:200});
    } else if(role==='h'){
      out += wPara(wr(textOf(el).toUpperCase(),{bold:true,size:17,color:accentHex,font:font}), {before:220,after:90});
    } else if(role==='p'){
      out += wPara(runsFromNode(el,{size:19,font:font}), {after:70,justify:true});
    } else if(role==='tagline'){
      const items = [...el.querySelectorAll('.ed')].map(textOf);
      out += wPara(wr(items.join('   ·   '),{size:18,font:font}), {after:120,justify:true});
    } else if(role==='org'){
      const spans=[...el.querySelectorAll('.ed')];
      const name=spans[0]?textOf(spans[0]):''; const date=spans[1]?textOf(spans[1]):'';
      out += wPara(wr(name,{bold:true,size:21,font:font}) + wTab() + wr(date,{size:17,color:'6E7478',font:font}),
                    {before:160,tab:PAGE_TAB});
    } else if(role==='orgname'){
      out += wPara(runsFromNode(el,{bold:true,size:21,font:font}), {before:160});
    } else if(role==='role'){
      out += wPara(runsFromNode(el,{italic:true,size:19,color:'6E7478',font:font}), {after:70});
    } else if(role==='dates'){
      out += wPara(runsFromNode(el,{bold:true,size:18,color:accentHex,font:font}), {after:70});
    } else if(role==='bullet'){
      const span = el.querySelector('span:not(.bg)');
      const runs = span ? runsFromNode(span,{size:19,font:font}) : '';
      out += wPara(wr('•  ',{size:19,font:font}) + runs, {after:60,justify:true});
    } else if(role==='edu'){
      const ps = el.querySelectorAll('p');
      const first = ps[0], inst = ps[1];
      if(first){
        const kids = [...first.childNodes].filter(n=>n.nodeType===1);
        const title = kids[0] ? textOf(kids[0]) : textOf(first);
        const year = kids[1] ? textOf(kids[1]) : '';
        out += wPara(wr(title,{bold:true,size:19,font:font}) +
                      (year? wTab()+wr(year,{size:17,color:'6E7478',font:font}) : ''),
                      {before:110, tab:PAGE_TAB});
      }
      if(inst) out += wPara(wr(textOf(inst),{italic:true,size:18,color:'6E7478',font:font}), {after:130});
    }
  });
  return out;
}

function exportVariantDocx(sid){
  const cfg = VARIANTS[sid];
  const sheets = [...document.querySelectorAll('#p-'+sid+' .sheet')];
  let body = '';
  sheets.forEach((sh, i)=>{
    let pageXml = buildParagraphs(sh, cfg.accent, cfg.font);
    if(i>0) pageXml = pageXml.replace('<w:pPr>', '<w:pPr><w:pageBreakBefore/>');
    body += pageXml;
  });
  const doc = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
    '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' +
    '<w:body>' + body +
    '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>' +
    '<w:pgMar w:top="737" w:right="1020" w:bottom="680" w:left="1020" w:header="0" w:footer="0" w:gutter="0"/>' +
    '</w:sectPr></w:body></w:document>';
  const ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
    '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' +
    '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' +
    '<Default Extension="xml" ContentType="application/xml"/>' +
    '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' +
    '</Types>';
  const rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' +
    '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' +
    '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' +
    '</Relationships>';
  const enc = new TextEncoder();
  const blob = zipStore([
    {name:'[Content_Types].xml', data:enc.encode(ct)},
    {name:'_rels/.rels', data:enc.encode(rels)},
    {name:'word/document.xml', data:enc.encode(doc)},
  ]);
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'CV Jeniffer Mieres - ' + sid + ' (editado).docx';
  document.body.appendChild(a); a.click(); a.remove();
  setTimeout(()=>URL.revokeObjectURL(a.href), 4000);
}
document.querySelectorAll('.dl-btn.export-word').forEach(btn=>{
  btn.addEventListener('click', ()=> exportVariantDocx(btn.dataset.variant));
});
"""
JS = JS.replace("__VARIANT_CFG__", VARIANT_CFG)

PENCIL = ('<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20l1-4.2L15.6 5.2a1.5 1.5 0 0 1 2.1 0l1.1 1.1a1.5 '
          '1.5 0 0 1 0 2.1L8.2 19l-4.2 1z" fill="none" stroke="currentColor" stroke-width="1.5" '
          'stroke-linejoin="round"/><path d="M14 7l3 3" stroke="currentColor" stroke-width="1.5"/></svg>')

HTML = f"""<title>Propuestas de diseño de CV · Jeniffer Mieres Contreras</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>{CSS}</style>
<div class="wrap">
  <header class="top">
    <div>
      <p class="eyebrow">Tres propuestas · Agosto 2026</p>
      <h1 class="title">Jeniffer Mieres Contreras<span>Trabajadora Social</span></h1>
    </div>
    <p class="stand">Tres tratamientos visuales para la postulación a <b>Trabajador/a Social ·
      Subdirección de Bienestar y Compensaciones</b> de la Pontificia Universidad Católica de Chile.
      El contenido es idéntico en los tres; solo cambia el diseño.</p>
  </header>
  <p class="edit-hint">{PENCIL}<span>Haz clic en cualquier texto de una hoja para editarlo. Los cambios se
    guardan <b>automáticamente en este navegador</b> — nadie más los ve, y puedes restablecer el original
    en cualquier momento.</span></p>

  <div class="tabs" role="tablist" aria-label="Propuestas de diseño">{tabs}</div>
  {panels}

  <footer class="foot">
    <p>Las tres cierran en <b>2 páginas exactas</b>, sin ningún cargo partido entre páginas.</p>
    <p>Tipografías incrustadas con las métricas de <b>Calibri</b> y <b>Cambria</b>.</p>
  </footer>
</div>
<div id="print-root" aria-hidden="true"></div>
<script>{JS}</script>
"""
open("cv_propuestas.html", "w", encoding="utf-8").write(HTML)
print("HTML:", len(HTML)/1024, "KB")
