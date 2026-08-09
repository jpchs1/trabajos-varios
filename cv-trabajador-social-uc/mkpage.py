# -*- coding: utf-8 -*-
"""Genera la pagina HTML con las tres propuestas, desde cv_content.py."""
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

def downloads(sid):
    docx_p, pdf_p, label = FILES[sid]
    docx_href = f"data:{DOCX_MIME};base64,{b64(docx_p)}"
    pdf_href  = f"data:application/pdf;base64,{b64(pdf_p)}"
    return f'''<div class="dl">
      <a class="dl-btn word" download="{E(label)}.docx" href="{docx_href}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h9l5 5v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7.2 12.5l1.15 5h.05l1.1-5h1l1.1 5h.05l1.15-5" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Descargar Word<i>.docx</i></span></a>
      <a class="dl-btn pdf" download="{E(label)}.pdf" href="{pdf_href}">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h9l5 5v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M14 3v5h5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M7 17.2V12h1.55c.85 0 1.45.6 1.45 1.4s-.6 1.4-1.45 1.4H7m5.3 2.4V12h1.1c1.35 0 2.2 1 2.2 2.6s-.85 2.6-2.2 2.6h-1.1zm5.5 0V12h2.6M17.8 14.6h2" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Descargar PDF<i>.pdf</i></span></a>
    </div>'''
FITS = json.load(open("fits.json")) if os.path.exists("fits.json") else {"minimal":1.0,"banda":1.0,"sidebar":1.0}
FONTS = open('fsub/fonts.css').read()

# ---------------------------------------------------------------- helpers CV
def bullets(job, glyph, cls=""):
    out = []
    for pre, key, post in job["bullets"]:
        inner = f'{E(pre)}<b>{E(key)}</b>{E(post)}' if key else E(pre + post)
        out.append(f'<li class="{cls}"><span class="bg">{glyph}</span><span>{inner}</span></li>')
    return "".join(out)

def tags(items, sep='<i class="sep">·</i>'):
    return sep.join(f"<span>{E(t)}</span>" for t in items)

# ================================================================ VARIANTE 1
def v1_p1():
    return f"""
<div class="s1 pg">
  <h1 class="s1-name">{E(C.NOMBRE)}</h1>
  <p class="s1-claim">{E((C.TITULO + '  ·  ' + C.CLAIM).upper())}</p>
  <p class="s1-meta"><span>{E(C.FONO)}  ·  {E(C.MAIL)}</span><span>{E(C.CIUDAD)}</span></p>
  <div class="s1-hr"></div>
  {s1_head('Perfil')}
  {''.join(f'<p class="s1-p">{E(x)}</p>' for x in C.PERFIL)}
  {s1_head('Áreas de expertise')}
  <p class="s1-tags">{tags(C.EXPERTISE)}</p>
  {s1_head('Experiencia profesional')}
  {s1_job(C.JOBS[0], first=True)}
  {s1_job(C.JOBS[1])}
</div>"""

def v1_p2():
    edu = "".join(f'<div class="s1-edu"><p><b>{E(t)}</b><span>{E(y)}</span></p><p class="i">{E(i)}</p></div>'
                  for t, i, y in C.EDU)
    certs = "".join(f'<li><span class="bg">—</span><span><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""
<div class="s1 pg">
  {s1_job(C.JOBS[2], first=True)}
  {s1_job(C.JOBS[3])}
  {s1_head('Formación académica')}{edu}
  {s1_head('Cursos y certificaciones')}<ul class="s1-ul">{certs}</ul>
  {s1_head('Competencias')}
  <p class="s1-tags">{tags(C.COMPETENCIAS)}</p>
  {s1_head('Herramientas y disponibilidad')}
  <ul class="s1-ul">
    <li><span class="bg">—</span><span><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li><span class="bg">—</span><span><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul>
</div>"""

def s1_head(t):  return f'<div class="s1-rule"></div><h2 class="s1-h">{E(t)}</h2>'
def s1_job(j, first=False):
    return f"""<div class="s1-job{' first' if first else ''}">
  <p class="s1-org">{E(j['empresa'])}<span>{E(j['fechas'])}</span></p>
  <p class="s1-role">{E(j['cargo'])}</p>
  <ul class="s1-ul">{bullets(j, '—')}</ul></div>"""

# ================================================================ VARIANTE 2
def band(cls):
    return f"""<header class="{cls}-band">
  <p class="{cls}-name">{E(C.NOMBRE_T)}</p>
  <p class="{cls}-claim">{E(C.TITULO.upper())}<i>|</i>{E(C.CLAIM.upper())}</p>
  <p class="{cls}-meta">{E(C.FONO)}  ·  {E(C.MAIL)}  ·  {E(C.CIUDAD)}</p>
</header>"""

def s2_head(t): return f'<h2 class="s2-h">{E(t)}</h2><div class="s2-rule"></div>'
def s2_job(j):
    return f"""<div class="s2-job">
  <p class="s2-org">{E(j['empresa'])}<span>{E(j['fechas'])}</span></p>
  <p class="s2-role">{E(j['cargo'])}</p>
  <ul class="s2-ul">{bullets(j, '▪')}</ul></div>"""

def v2_p1():
    return f"""<div class="s2 pg">{band('s2')}<div class="s2-body">
  {s2_head('Perfil profesional')}
  {''.join(f'<p class="s2-p">{E(x)}</p>' for x in C.PERFIL)}
  {s2_head('Áreas de expertise')}<p class="s2-tags">{tags(C.EXPERTISE)}</p>
  {s2_head('Experiencia profesional')}{s2_job(C.JOBS[0])}{s2_job(C.JOBS[1])}
</div></div>"""

def v2_p2():
    edu = "".join(f'<div class="s2-edu"><p><b>{E(t)}</b><span>{E(y)}</span></p><p class="i">{E(i)}</p></div>'
                  for t, i, y in C.EDU)
    certs = "".join(f'<li><span class="bg">▪</span><span><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""<div class="s2 pg"><div class="s2-body s2-cont">
  {s2_job(C.JOBS[2])}{s2_job(C.JOBS[3])}
  {s2_head('Formación académica')}{edu}
  {s2_head('Cursos y certificaciones')}<ul class="s2-ul">{certs}</ul>
  {s2_head('Competencias')}<p class="s2-tags">{tags(C.COMPETENCIAS)}</p>
  {s2_head('Herramientas y disponibilidad')}
  <ul class="s2-ul">
    <li><span class="bg">▪</span><span><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li><span class="bg">▪</span><span><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul></div></div>"""

# ================================================================ VARIANTE 3
def s3_head(t): return f'<h2 class="s3-h">{E(t)}</h2><div class="s3-rule"></div>'
def s3_job(j):
    return f"""<div class="s3-job">
  <p class="s3-org">{E(j['empresa'])}</p>
  <p class="s3-role">{E(j['cargo'])}</p>
  <p class="s3-dates">{E(j['fechas'])}</p>
  <ul class="s3-ul">{bullets(j, '▪')}</ul></div>"""

def s3_tags(items):
    return "".join(f'<li><span class="bg">▪</span><span>{E(t)}</span></li>' for t in items)

def v3_p1():
    return f"""<div class="s3 pg">{band('s3')}<div class="s3-cols">
  <aside class="s3-side">
    {s3_head('Áreas de expertise')}<ul class="s3-tl">{s3_tags(C.EXPERTISE)}</ul>
    {s3_head('Competencias')}<ul class="s3-tl">{s3_tags(C.COMPETENCIAS)}</ul>
  </aside>
  <main class="s3-main">
    {s3_head('Perfil profesional')}
    {''.join(f'<p class="s3-p">{E(x)}</p>' for x in C.PERFIL)}
    {s3_head('Experiencia profesional')}{s3_job(C.JOBS[0])}{s3_job(C.JOBS[1])}
  </main></div></div>"""

def v3_p2():
    edu = "".join(f'<div class="s3-edu"><p><b>{E(t)}</b></p><p class="i">{E(i)}  ·  {E(y)}</p></div>'
                  for t, i, y in C.EDU)
    return f"""<div class="s3 pg">{band('s3')}<div class="s3-cols">
  <aside class="s3-side">
    {s3_head('Herramientas')}<p class="s3-sp">{E(C.HERRAMIENTAS)}</p>
    {s3_head('Disponibilidad')}<p class="s3-sp">{E(C.DISPONIBILIDAD)}</p>
    {s3_head('Cursos y certificaciones')}
    <ul class="s3-tl">{''.join(f'<li><span class="bg">▪</span><span>{E(k + r)}</span></li>' for k, r in C.CERTS)}</ul>
  </aside>
  <main class="s3-main">
    {s3_head('Experiencia profesional (cont.)')}{s3_job(C.JOBS[2])}{s3_job(C.JOBS[3])}
    {s3_head('Formación académica')}{edu}
  </main></div></div>"""

# ---------------------------------------------------------------- ficha tecnica
SPECS = [
 dict(id="minimal", n="01", name="Minimal", tag="Suizo · sin color",
      note="Reglas cortas y gruesas, versales espaciadas, cero color. El lenguaje de las "
           "consultoras de búsqueda ejecutiva: nada decora, todo jerarquiza.",
      spec=[("Tipografía","Calibri"),("Cuerpo","9,1 pt"),("Acento","Ninguno"),
            ("Holgura pág. 1","2,86 cm"),("Riesgo ATS","Nulo")], rec=False,
      pages=[v1_p1(), v1_p2()]),
 dict(id="banda", n="02", name="Banda", tag="Bloque de color a sangre",
      note="Bloque sólido en el encabezado con el nombre en blanco y cuerpo limpio abajo. "
           "Serif para nombre y cargos contra sans en el texto. Máximo impacto sin riesgo técnico.",
      spec=[("Tipografía","Cambria + Calibri"),("Cuerpo","9,6 pt"),("Acento","Petróleo #13323C"),
            ("Holgura pág. 1","2,62 cm"),("Riesgo ATS","Nulo")], rec=True,
      pages=[v2_p1(), v2_p2()]),
 dict(id="sidebar", n="03", name="Sidebar", tag="Dos columnas",
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

panels = ""
for i, s in enumerate(SPECS):
    rows = "".join(f'<div class="sp"><dt>{E(k)}</dt><dd>{E(v)}</dd></div>' for k, v in s["spec"])
    sheets = "".join(f'<figure class="frame"><div class="sheet" style="--fit:{FITS[s["id"]]:.4f}">{p}</div>'
                     f'<figcaption>Página {j+1}</figcaption></figure>'
                     for j, p in enumerate(s["pages"]))
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

# ---------------------------------------------------------------- CSS
CSS = FONTS + r"""
:root{
  --ground:#EBEDEF; --raise:#F7F8F9; --ink:#14171A; --muted:#6A7075;
  --line:#D6D9DD; --line-soft:#E2E5E8; --accent:#1B3A4B; --accent-ink:#FFFFFF;
  --shadow:0 1px 2px rgba(16,22,26,.06), 0 12px 32px -12px rgba(16,22,26,.28);
  --dl-word:#2957A4; --dl-pdf:#B23A2E;
  --ui:ui-sans-serif,"Segoe UI",Roboto,system-ui,-apple-system,"Helvetica Neue",Arial,sans-serif;
}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){
  --ground:#131619; --raise:#1B1F23; --ink:#E7E9EB; --muted:#979DA3;
  --line:#272C31; --line-soft:#22262A; --accent:#8AB6C7; --accent-ink:#0E1417;
  --shadow:0 1px 2px rgba(0,0,0,.5), 0 18px 44px -14px rgba(0,0,0,.72);
  --dl-word:#8FB4EE; --dl-pdf:#EE998B;
}}
:root[data-theme="dark"]{
  --ground:#131619; --raise:#1B1F23; --ink:#E7E9EB; --muted:#979DA3;
  --line:#272C31; --line-soft:#22262A; --accent:#8AB6C7; --accent-ink:#0E1417;
  --shadow:0 1px 2px rgba(0,0,0,.5), 0 18px 44px -14px rgba(0,0,0,.72);
  --dl-word:#8FB4EE; --dl-pdf:#EE998B;
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
.frame figcaption{margin-top:11px;font-size:.72rem;letter-spacing:.13em;text-transform:uppercase;
  color:var(--muted)}
.sheet{container-type:inline-size;background:#fff;color:#33373B;border-radius:2px;
  box-shadow:var(--shadow);overflow:hidden}
.pg{aspect-ratio:210/297;overflow:hidden}
.sheet b{font-weight:700}
.sheet ul{list-style:none;margin:0;padding:0}
.sheet p{margin:0}
.sheet .bg{flex:none}
.sheet li{display:flex}

/* ============ 01 MINIMAL ============ */
.s1{font-family:'CVSans',Calibri,sans-serif;font-size:calc(1.529cqw*var(--fit,1));line-height:1.34;
  padding:5.0cqw 6.90cqw 0 7.62cqw;color:#3A3E42}
.s1-name{font-size:2.75em;font-weight:700;letter-spacing:-.012em;color:#0D0D0D;line-height:1.06}
.s1-claim{font-size:.88em;letter-spacing:.19em;color:#767B80;margin-top:.5em!important}
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
.s2-claim{font-size:.83em;font-weight:700;letter-spacing:.185em;margin-top:.5em!important}
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
.s3-claim{font-size:.85em;font-weight:700;letter-spacing:.19em;margin-top:.42em!important}
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
@media (prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
"""

JS = r"""
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
"""

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

  <div class="tabs" role="tablist" aria-label="Propuestas de diseño">{tabs}</div>
  {panels}

  <footer class="foot">
    <p>Las tres cierran en <b>2 páginas exactas</b>, sin ningún cargo partido entre páginas.</p>
    <p>Tipografías incrustadas con las métricas de <b>Calibri</b> y <b>Cambria</b>.</p>
  </footer>
</div>
<script>{JS}</script>
"""
open("cv_propuestas.html", "w", encoding="utf-8").write(HTML)
print("HTML:", len(HTML)/1024, "KB")
