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
  "editorial":("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_04_Editorial.docx",
               "fin_CV_Jeniffer_Mieres_04_Editorial.pdf",
               "CV Jeniffer Mieres - Editorial"),
  "clasico":  ("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_05_Clasico.docx",
               "fin_CV_Jeniffer_Mieres_05_Clasico.pdf",
               "CV Jeniffer Mieres - Clasico"),
  "impacto":  ("/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_06_Impacto.docx",
               "fin_CV_Jeniffer_Mieres_06_Impacto.pdf",
               "CV Jeniffer Mieres - Impacto"),
}
def b64(path):
    return base64.b64encode(open(path, "rb").read()).decode()

FITS = json.load(open("fits.json")) if os.path.exists("fits.json") else {}
for _k in ("minimal","banda","sidebar","editorial","clasico","impacto"): FITS.setdefault(_k, 1.0)
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
      <div class="dlgrp">
        <span class="dlg-l">Plantilla original</span>
        <div class="dlg-b">
          <a class="dl-btn word" download="{E(label)}.docx" href="{docx_href}">
            {word_svg()}<span>Word<i>.docx</i></span></a>
          <a class="dl-btn pdf" download="{E(label)}.pdf" href="{pdf_href}">
            {pdf_svg()}<span>PDF<i>.pdf</i></span></a>
        </div>
      </div>
      <div class="dlgrp grp-edit" data-variant="{sid}" hidden>
        <span class="dlg-l">Tu versión editada
          <em class="saved" data-variant="{sid}" aria-live="polite">guardada ✓</em></span>
        <div class="dlg-b">
          <button type="button" class="dl-btn edit export-word" data-variant="{sid}">
            {word_svg()}<span>Word con tu edición<i>mismo diseño</i></span></button>
          <button type="button" class="dl-btn edit export-pdf" data-variant="{sid}">
            {pdf_svg()}<span>PDF con tu edición<i>imprimir</i></span></button>
          <button type="button" class="dl-btn ghost reset-variant" data-variant="{sid}">
            <span>Restablecer</span></button>
        </div>
      </div>
    </div>
    <p class="dl-hint">Haz clic en cualquier texto de las hojas para editarlo: se guarda solo y aparece el
      grupo <b>Tu versión editada</b>. El Word editado conserva el diseño exacto de esta plantilla.</p>'''

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


# ================================================================ VARIANTE 4 (Editorial · rail)
def s4_row(label, inner, cls=""):
    if label:
        lab = f'<span {ED("s4-lab")} data-role="h">{E(label)}</span>'
    else:
        lab = '<span class="s4-lab" aria-hidden="true"></span>'
    return f'<div class="s4-row {cls}">{lab}<div class="s4-bd">{inner}</div></div>'

def s4_job(j):
    return (f'{org_line("s4-org", j)}'
            f'{leaf("p","s4-role","role", j["cargo"])}'
            f'<ul class="s4-ul">{bullets(j, "•")}</ul>')

def v4_p1():
    perfil = ''.join(leaf('p','s4-p','p', x) for x in C.PERFIL)
    return f"""<div class="s4 pg">
  {leaf('h1','s4-name','name', C.NOMBRE)}
  {claim_pipe('s4-claim')}
  {meta_flat('s4-meta')}
  {s4_row('Perfil', perfil)}
  {s4_row('Expertise', f'<p class="s4-tags" data-role="tagline">{tags(C.EXPERTISE)}</p>')}
  {s4_row('Experiencia', s4_job(C.JOBS[0]) + s4_job(C.JOBS[1]), cls='exp')}
</div>"""

def v4_p2():
    edu = "".join(edu_block('s4-edu', t, i, y) for t, i, y in C.EDU)
    k0, r0 = C.CERTS[0]
    certs = (f'<p class="s4-cert" {ED()} data-role="p"><b>{E(k0)}</b>{E(r0)}</p>'
             f'<ul class="s4-ul">' +
             "".join(f'<li data-role="bullet"><span class="bg">•</span>'
                     f'<span {ED()}><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS[1:]) +
             '</ul>')
    return f"""<div class="s4 pg">
  {s4_row('', s4_job(C.JOBS[2]), cls='exp first')}
  {s4_row('', s4_job(C.JOBS[3]), cls='exp')}
  {s4_row('Formación', edu)}
  {s4_row('Certificaciones', certs)}
  {s4_row('Competencias', f'<p class="s4-tags" data-role="tagline">{tags(C.COMPETENCIAS)}</p>')}
  {s4_row('Herramientas', f'<p class="s4-sp" {ED()} data-role="p">{E(C.HERRAMIENTAS)}</p>')}
  {s4_row('Disponibilidad', f'<p class="s4-sp" {ED()} data-role="p">{E(C.DISPONIBILIDAD)}</p>')}
</div>"""

# ================================================================ VARIANTE 5 (Clasico · centrado)
def s5_head(t): return f'{leaf("h2","s5-h","h",t)}<div class="s5-rule"></div>'
def s5_job(j):
    return f"""<div class="s5-job">
  {org_line('s5-org', j)}
  {leaf('p','s5-role','role', j['cargo'])}
  <ul class="s5-ul">{bullets(j, '·')}</ul></div>"""

def v5_p1():
    return f"""<div class="s5 pg">
  {leaf('h1','s5-name','name', C.NOMBRE)}
  {claim_dot('s5-claim')}
  {meta_flat('s5-meta')}
  <div class="s5-double"></div>
  {s5_head('Perfil profesional')}
  {''.join(leaf('p','s5-p','p', x) for x in C.PERFIL)}
  {s5_head('Áreas de expertise')}
  <p class="s5-tags" data-role="tagline">{tags(C.EXPERTISE)}</p>
  {s5_head('Experiencia profesional')}
  {s5_job(C.JOBS[0])}{s5_job(C.JOBS[1])}
</div>"""

def v5_p2():
    edu = "".join(edu_block('s5-edu', t, i, y) for t, i, y in C.EDU)
    certs = "".join(f'<li data-role="bullet"><span class="bg">·</span>'
                     f'<span {ED()}><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""<div class="s5 pg top2">
  {s5_job(C.JOBS[2])}{s5_job(C.JOBS[3])}
  {s5_head('Formación académica')}{edu}
  {s5_head('Cursos y certificaciones')}<ul class="s5-ul">{certs}</ul>
  {s5_head('Competencias')}
  <p class="s5-tags" data-role="tagline">{tags(C.COMPETENCIAS)}</p>
  {s5_head('Herramientas y disponibilidad')}
  <ul class="s5-ul">
    <li data-role="bullet"><span class="bg">·</span><span {ED()}><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li data-role="bullet"><span class="bg">·</span><span {ED()}><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul>
</div>"""

# ================================================================ VARIANTE 6 (Impacto · franjas)
def s6_head(t): return leaf('h2','s6-h','h', t)
def s6_job(j):
    return f"""<div class="s6-job">
  {org_line('s6-org', j)}
  {leaf('p','s6-role','role', j['cargo'])}
  <ul class="s6-ul">{bullets(j, '▪')}</ul></div>"""

def v6_p1():
    return f"""<div class="s6 pg">
  {leaf('h1','s6-name','name', C.NOMBRE_T)}
  {claim_pipe('s6-claim')}
  {meta_flat('s6-meta')}
  {s6_head('Perfil profesional')}
  {''.join(leaf('p','s6-p','p', x) for x in C.PERFIL)}
  {s6_head('Áreas de expertise')}
  <p class="s6-tags" data-role="tagline">{tags(C.EXPERTISE)}</p>
  {s6_head('Experiencia profesional')}
  {s6_job(C.JOBS[0])}{s6_job(C.JOBS[1])}
</div>"""

def v6_p2():
    edu = "".join(edu_block('s6-edu', t, i, y) for t, i, y in C.EDU)
    certs = "".join(f'<li data-role="bullet"><span class="bg">▪</span>'
                     f'<span {ED()}><b>{E(k)}</b>{E(r)}</span></li>' for k, r in C.CERTS)
    return f"""<div class="s6 pg top2">
  {s6_job(C.JOBS[2])}{s6_job(C.JOBS[3])}
  {s6_head('Formación académica')}{edu}
  {s6_head('Cursos y certificaciones')}<ul class="s6-ul">{certs}</ul>
  {s6_head('Competencias')}
  <p class="s6-tags" data-role="tagline">{tags(C.COMPETENCIAS)}</p>
  {s6_head('Herramientas y disponibilidad')}
  <ul class="s6-ul">
    <li data-role="bullet"><span class="bg">▪</span><span {ED()}><b>Herramientas: </b>{E(C.HERRAMIENTAS)}</span></li>
    <li data-role="bullet"><span class="bg">▪</span><span {ED()}><b>Disponibilidad: </b>{E(C.DISPONIBILIDAD)}</span></li>
  </ul>
</div>"""

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
            ("Holgura pág. 1","2,67 cm"),("Riesgo ATS","Nulo")], rec=True,
      pages=[v2_p1(), v2_p2()]),
 dict(id="sidebar", n="03", name="Sidebar", tag="Dos columnas", accent="1B3A4B", font="Calibri",
      note="Barra lateral tramada con expertise y competencias; columna principal con perfil y "
           "experiencia. Es el formato más usado hoy y el único con un matiz técnico.",
      spec=[("Tipografía","Calibri"),("Cuerpo","9,3 pt"),("Acento","Petróleo #1B3A4B"),
            ("Holgura pág. 1","3,35 cm"),("Riesgo ATS","Bajo–moderado")], rec=False,
      pages=[v3_p1(), v3_p2()]),
 dict(id="editorial", n="04", name="Editorial", tag="Raíl de etiquetas · serif", accent="6E2130", font="Cambria",
      note="Las etiquetas de sección viven en una columna propia y el contenido cuelga de una espina "
           "vertical. Serif editorial con acento vino: el lenguaje de un dossier de executive search.",
      spec=[("Tipografía","Cambria + Calibri"),("Cuerpo","9,5 pt"),("Acento","Vino #6E2130"),
            ("Holgura pág. 1","3,23 cm"),("Riesgo ATS","Nulo")], rec=False, new=True,
      pages=[v4_p1(), v4_p2()]),
 dict(id="clasico", n="05", name="Clásico", tag="Centrado · doble filete", accent="7A5C2E", font="Cambria",
      note="Encabezado centrado con versales espaciadas y doble filete, títulos de sección centrados "
           "con regla corta. Serif en todo el cuerpo y acento bronce: sobriedad académica.",
      spec=[("Tipografía","Cambria"),("Cuerpo","9,5 pt"),("Acento","Bronce #7A5C2E"),
            ("Holgura pág. 1","2,55 cm"),("Riesgo ATS","Nulo")], rec=False, new=True,
      pages=[v5_p1(), v5_p2()]),
 dict(id="impacto", n="06", name="Impacto", tag="Franjas de sección en color", accent="24435C", font="Calibri",
      note="Nombre a gran tamaño y cada sección abre con una franja sólida de color con el título en "
           "blanco. El más contemporáneo de los seis, manteniendo párrafos puros aptos para ATS.",
      spec=[("Tipografía","Calibri"),("Cuerpo","9,5 pt"),("Acento","Pizarra #24435C"),
            ("Holgura pág. 1","2,55 cm"),("Riesgo ATS","Nulo")], rec=False, new=True,
      pages=[v6_p1(), v6_p2()]),
]

tabs = "".join(
  f'<button role="tab" id="t-{s["id"]}" aria-controls="p-{s["id"]}" '
  f'aria-selected="{"true" if i==1 else "false"}" tabindex="{0 if i==1 else -1}">'
  f'<i>{s["n"]}</i>{E(s["name"])}</button>' for i, s in enumerate(SPECS))

def figure(sheet_html, page_no, fit):
    return f'''<figure class="frame">
      <div class="sheet" style="--fit:{fit:.4f}">{sheet_html}</div>
      <figcaption><span>Página {page_no}</span></figcaption>
    </figure>'''

panels = ""
for i, s in enumerate(SPECS):
    rows = "".join(f'<div class="sp"><dt>{E(k)}</dt><dd>{E(v)}</dd></div>' for k, v in s["spec"])
    sheets = "".join(figure(p, j + 1, FITS[s["id"]]) for j, p in enumerate(s["pages"]))
    panels += f"""
<section role="tabpanel" id="p-{s['id']}" aria-labelledby="t-{s['id']}" {'hidden' if i!=1 else ''}>
  <div class="lede">
    <div class="lede-txt">
      <h2>{E(s['name'])}{' <em>Recomendado</em>' if s['rec'] else (' <em class="alt">Nuevo</em>' if s.get('new') else '')}</h2>
      <p class="kicker">{E(s['tag'])}</p>
      <p class="note">{E(s['note'])}</p>
      {downloads(s['id'])}
    </div>
    <dl class="specs">{rows}</dl>
  </div>
  <div class="sheets">{sheets}</div>
</section>"""

VARIANT_CFG = "{" + ",".join(
  f'"{s["id"]}":{{accent:"{s["accent"]}",font:"{s["font"]}",label:"{s["name"]}"}}' for s in SPECS) + "}"

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
.lede h2 em.alt{background:transparent;color:var(--accent);border:1px solid var(--accent);padding:4px 9px}
.kicker{margin:7px 0 0;font-size:.74rem;letter-spacing:.15em;text-transform:uppercase;color:var(--muted)}
.note{margin:16px 0 0;color:var(--muted);max-width:56ch;font-size:.94rem}
.dl{display:flex;flex-direction:column;gap:14px;margin-top:22px}
.dlgrp{display:flex;flex-direction:column;gap:8px}
.dlg-l{font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--muted);font-weight:650;
  display:flex;align-items:center;gap:10px}
.dlg-l .saved{font-style:normal;text-transform:none;letter-spacing:.01em;font-size:.72rem;font-weight:600;
  color:var(--dl-edit)}
.dlg-b{display:flex;flex-wrap:wrap;gap:10px}
.grp-edit{padding:12px 14px;border:1px dashed var(--dl-edit);border-radius:12px;
  background:color-mix(in srgb,var(--dl-edit) 6%,transparent)}
.tabs button.edited::after{content:"";display:inline-block;width:7px;height:7px;border-radius:50%;
  background:var(--dl-edit);margin-left:7px;vertical-align:middle}
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
.s1-org span{font-size:.78em;font-weight:400;color:#767B80;letter-spacing:.22em;text-transform:uppercase;white-space:nowrap}
.s1-role{font-size:.99em;font-style:italic;color:#767B80;letter-spacing:.03em;margin-top:.22em!important}
.s1-ul{margin-top:.5em}
.s1-ul li{gap:.62em;margin-bottom:.42em;text-align:justify;hyphens:auto}
.s1-ul .bg{color:#B0B4B8}
.s1-ul b{color:#0D0D0D}
.s1-edu{margin-top:.72em}
.s1-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s1-edu p:first-child b{color:#0D0D0D;font-size:1.04em}
.s1-edu p:first-child span{font-size:.78em;color:#767B80;letter-spacing:.22em;text-transform:uppercase}
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
.s2-org span{font-family:'CVSans',Calibri,sans-serif;font-size:.76em;font-weight:400;
  color:#6E7478;letter-spacing:.2em;text-transform:uppercase;white-space:nowrap}
.s2-role{font-family:'CVSerif',Cambria,Georgia,serif;font-style:italic;font-size:.98em;color:#6E7478;margin-top:.2em!important}
.s2-ul{margin-top:.48em}
.s2-ul li{gap:.6em;margin-bottom:.44em;text-align:justify;hyphens:auto}
.s2-ul .bg{color:#13323C;font-size:.7em;line-height:1.9}
.s2-ul b{color:#151A1C}
.s2-edu{margin-top:.7em}
.s2-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s2-edu p:first-child b{font-family:'CVSerif',Cambria,Georgia,serif;color:#151A1C;font-size:1.04em}
.s2-edu p:first-child span{font-size:.76em;color:#6E7478;letter-spacing:.2em;text-transform:uppercase}
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
.s3-side{background:#EFF3F5;border-left:.34em solid #1B3A4B;padding:1.15em 1.25em 1.6em;margin-left:-1.25em}
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
.s3-dates{font-size:.8em;font-weight:700;color:#1B3A4B;letter-spacing:.18em;text-transform:uppercase;margin-top:.12em!important}
.s3-ul{margin-top:.45em}
.s3-ul li{gap:.55em;margin-bottom:.42em;text-align:justify;hyphens:auto}
.s3-ul b{color:#151A1C}
.s3-edu{margin-top:.62em}
.s3-edu b{color:#151A1C}
.s3-edu .i{font-size:.94em;color:#6E7478}

/* ============ 04 EDITORIAL ============ */
.s4{font-family:'CVSerif',Cambria,Georgia,serif;font-size:calc(1.575cqw*var(--fit,1));line-height:1.42;
  padding:4.6cqw 7.14cqw 0 8.10cqw;color:#33373B}
.s4-name{font-size:2.08em;font-weight:400;color:#16181A;letter-spacing:.09em}
.s4-claim{font-family:'CVSans',Calibri,sans-serif;font-size:1.03em;color:#4A4F54;
  letter-spacing:.045em;margin-top:.32em!important}
.s4-claim i{font-style:normal;color:#6E2130;padding:0 .8em}
.s4-meta{font-family:'CVSans',Calibri,sans-serif;font-size:.88em;color:#6C7176;letter-spacing:.03em;
  margin-top:.5em!important;padding-bottom:.65em;border-bottom:1px solid #6E2130}
.s4-row{display:grid;grid-template-columns:16.3% 1fr;margin-top:1.55em}
.s4-row.exp{margin-top:1.45em}
.s4-row.exp.first{margin-top:1.05em}
.s4-row.exp + .s4-row.exp{margin-top:1.25em}
.s4-lab{font-family:'CVSans',Calibri,sans-serif;font-size:.77em;font-weight:700;color:#6E2130;
  letter-spacing:.18em;text-transform:uppercase;padding-top:.28em}
.s4-bd{min-width:0}
.s4-p{margin-bottom:.55em!important;text-align:justify;hyphens:auto}
.s4-p:last-child{margin-bottom:0!important}
.s4-tags{line-height:1.55;font-size:.9em}
.s4-tags .sep{font-style:normal;color:#A8ADB2;padding:0 .5em}
.s4-org{display:flex;justify-content:space-between;align-items:baseline;gap:1em;
  font-size:1.1em;font-weight:700;color:#16181A}
.s4-org span:last-child{font-family:'CVSans',Calibri,sans-serif;font-size:.71em;font-weight:400;
  color:#6C7176;letter-spacing:.2em;text-transform:uppercase;white-space:nowrap}
.s4-role{font-family:'CVSans',Calibri,sans-serif;font-style:italic;font-size:.93em;color:#6C7176;
  margin-top:.18em!important}
.s4-ul{margin-top:.45em}
.s4-bd > .s4-org ~ .s4-org{margin-top:1.25em}
.s4-ul li{gap:.55em;margin-bottom:.5em;text-align:justify;hyphens:auto}
.s4-ul .bg{color:#6E2130;font-size:.85em;line-height:1.65}
.s4-ul b{color:#16181A}
.s4-cert{margin-bottom:.5em!important;text-align:justify;hyphens:auto}
.s4-cert b,.s4-edu b{color:#16181A}
.s4-edu{margin-top:.65em}
.s4-edu:first-child{margin-top:0}
.s4-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s4-edu p:first-child span{font-family:'CVSans',Calibri,sans-serif;font-size:.76em;color:#6C7176;
  letter-spacing:.2em;text-transform:uppercase}
.s4-edu .i{font-family:'CVSans',Calibri,sans-serif;font-style:italic;font-size:.9em;color:#6C7176}
.s4-sp{text-align:justify;hyphens:auto;font-size:.95em}

/* ============ 05 CLASICO ============ */
.s5{font-family:'CVSerif',Cambria,Georgia,serif;font-size:calc(1.60cqw*var(--fit,1));line-height:1.42;
  padding:4.9cqw 9.05cqw 0 9.05cqw;color:#35383B}
.s5.top2{padding-top:5.4cqw}
.s5-name{font-size:1.98em;font-weight:400;color:#1A1A1A;letter-spacing:.26em;text-align:center;
  text-transform:uppercase}
.s5-claim{font-family:'CVSans',Calibri,sans-serif;font-size:.83em;font-weight:700;color:#70747A;
  letter-spacing:.3em;text-transform:uppercase;text-align:center;margin-top:.55em!important}
.s5-claim .csep{font-style:normal;color:#7A5C2E;padding:0 .8em}
.s5-meta{font-family:'CVSans',Calibri,sans-serif;font-size:.89em;color:#70747A;letter-spacing:.05em;
  text-align:center;margin-top:.5em!important}
.s5-double{border-bottom:4px double #7A5C2E;margin-top:.75em}
.s5-h{font-family:'CVSans',Calibri,sans-serif;font-size:.84em;font-weight:700;color:#7A5C2E;
  letter-spacing:.4em;text-transform:uppercase;text-align:center;margin-top:1.6em}
.s5 > .s5-h:first-of-type{margin-top:1.35em}
.s5.top2 > .s5-h:first-of-type{margin-top:1.6em}
.s5-rule{width:12.5%;margin:.42em auto .75em;border-bottom:2px solid #7A5C2E}
.s5-p{margin-bottom:.55em!important;text-align:justify;hyphens:auto}
.s5-tags{line-height:1.55;font-size:.9em;text-align:center}
.s5-tags .sep{font-style:normal;color:#7A5C2E;padding:0 .5em}
.s5-job{margin-top:1.3em}
.s5-rule + .s5-job{margin-top:0}
.s5-org{display:flex;justify-content:space-between;align-items:baseline;gap:1em;
  font-size:1.1em;font-weight:700;color:#1A1A1A}
.s5-org span:last-child{font-family:'CVSans',Calibri,sans-serif;font-size:.71em;font-weight:700;
  color:#7A5C2E;letter-spacing:.18em;text-transform:uppercase;white-space:nowrap}
.s5-role{font-style:italic;font-size:.97em;color:#70747A;margin-top:.18em!important}
.s5-ul{margin-top:.45em}
.s5-ul li{gap:.55em;margin-bottom:.48em;text-align:justify;hyphens:auto}
.s5-ul .bg{color:#7A5C2E;font-weight:700}
.s5-ul b{color:#1A1A1A}
.s5-edu{margin-top:.62em}
.s5-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s5-edu p:first-child b{color:#1A1A1A}
.s5-edu p:first-child span{font-family:'CVSans',Calibri,sans-serif;font-size:.71em;font-weight:700;
  color:#7A5C2E;letter-spacing:.18em;text-transform:uppercase}
.s5-edu .i{font-style:italic;font-size:.92em;color:#70747A}

/* ============ 06 IMPACTO ============ */
.s6{font-family:'CVSans',Calibri,sans-serif;font-size:calc(1.575cqw*var(--fit,1));line-height:1.38;
  padding:4.6cqw 7.14cqw 0 7.62cqw;color:#33373B}
.s6.top2{padding-top:5.1cqw}
.s6-name{font-size:2.72em;font-weight:700;color:#171A1E;letter-spacing:-.005em;line-height:1.04}
.s6-claim{font-size:.88em;font-weight:700;color:#24435C;letter-spacing:.26em;text-transform:uppercase;
  margin-top:.45em!important}
.s6-claim i{font-style:normal;opacity:.5;padding:0 .8em}
.s6-meta{font-size:.94em;color:#6E7378;margin-top:.45em!important;padding-bottom:.6em;
  border-bottom:.3em solid #24435C}
.s6-h{background:#24435C;color:#FFFFFF;font-size:.88em;font-weight:700;letter-spacing:.28em;
  text-transform:uppercase;padding:.3em .7em;margin-top:1.45em}
.s6 > .s6-h:first-of-type{margin-top:1.25em}
.s6.top2 > .s6-h:first-of-type{margin-top:1.45em}
.s6-h + .s6-p,.s6-h + .s6-tags,.s6-h + .s6-job,.s6-h + .s6-ul,.s6-h + .s6-edu{margin-top:.65em}
.s6-p{margin-bottom:.55em!important;text-align:justify;hyphens:auto}
.s6-tags{line-height:1.5;font-size:.94em}
.s6-tags .sep{font-style:normal;color:#AEB3B6;padding:0 .5em}
.s6-job{margin-top:1.15em}
.s6-org{display:flex;justify-content:space-between;align-items:baseline;gap:1em;
  font-size:1.1em;font-weight:700;color:#171A1E}
.s6-org span:last-child{font-size:.73em;font-weight:700;color:#24435C;letter-spacing:.18em;
  text-transform:uppercase;white-space:nowrap}
.s6-role{font-style:italic;font-size:.94em;color:#6E7378;margin-top:.16em!important}
.s6-ul{margin-top:.45em}
.s6-ul li{gap:.55em;margin-bottom:.5em;text-align:justify;hyphens:auto}
.s6-ul .bg{color:#24435C;font-size:.68em;line-height:2}
.s6-ul b{color:#171A1E}
.s6-edu{margin-top:.62em}
.s6-edu p:first-child{display:flex;justify-content:space-between;gap:1em;align-items:baseline}
.s6-edu p:first-child b{color:#171A1E}
.s6-edu p:first-child span{font-size:.73em;font-weight:700;color:#24435C;letter-spacing:.18em;
  text-transform:uppercase}
.s6-edu .i{font-style:italic;font-size:.92em;color:#6E7378}

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
  #print-root *{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  @page{size:A4;margin:0}
}
#toast{position:fixed;left:50%;bottom:26px;transform:translate(-50%,14px);z-index:99;
  max-width:min(92vw,480px);padding:11px 18px;border-radius:10px;font-size:.86rem;font-weight:560;
  background:var(--ink);color:var(--ground);box-shadow:var(--shadow);opacity:0;pointer-events:none;
  transition:opacity .22s,transform .22s;text-align:center}
#toast.show{opacity:1;transform:translate(-50%,0)}
#changes-btn{margin-left:auto;flex:none;appearance:none;cursor:pointer;font:inherit;font-size:.8rem;
  font-weight:620;color:var(--accent);background:transparent;border:1px solid var(--accent);
  border-radius:999px;padding:7px 14px;transition:background .16s}
#changes-btn:hover{background:color-mix(in srgb,var(--accent) 12%,transparent)}
#changes-dlg{border:1px solid var(--line);border-radius:14px;background:var(--raise);color:var(--ink);
  padding:22px 24px;max-width:min(92vw,760px);width:100%;box-shadow:var(--shadow)}
#changes-dlg::backdrop{background:rgba(10,14,17,.55);backdrop-filter:blur(2px)}
#changes-dlg h3{margin:0 0 6px;font-size:1.05rem;letter-spacing:-.01em}
#changes-dlg p{margin:0 0 14px;font-size:.85rem;color:var(--muted)}
#changes-txt{width:100%;height:min(46vh,340px);resize:vertical;font:12px/1.55 ui-monospace,Consolas,monospace;
  color:var(--ink);background:var(--ground);border:1px solid var(--line);border-radius:8px;padding:12px}
.dlg-row{display:flex;gap:10px;justify-content:flex-end;margin-top:14px}
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
const VARIANTS = __VARIANT_CFG__;
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
  const on = isEdited(sid);
  const grp = document.querySelector(`.grp-edit[data-variant="${sid}"]`);
  if(grp) grp.hidden = !on;
  const tab = document.getElementById('t-'+sid);
  if(tab) tab.classList.toggle('edited', on);
}
function setSaved(sid, txt){
  const chip = document.querySelector(`.saved[data-variant="${sid}"]`);
  if(chip){ chip.textContent = txt; }
}

document.querySelectorAll('.sheet').forEach(sh=>{
  originals.set(sh, sh.innerHTML);
  const saved = localStorage.getItem(sheetKey(sh));
  if(saved) sh.innerHTML = saved;
});
Object.keys(VARIANTS).forEach(refreshEditedUI);
refreshChangesBtn();

const timers = new WeakMap();
document.addEventListener('input', e=>{
  const ed = e.target.closest('.ed');
  if(!ed) return;
  const sh = ed.closest('.sheet');
  const sid = variantOf(sh);
  refreshEditedUI(sid); setSaved(sid, 'guardando…');
  clearTimeout(timers.get(sh));
  timers.set(sh, setTimeout(()=>{
    localStorage.setItem(sheetKey(sh), sh.innerHTML);
    setSaved(sid, 'guardada ✓');
    refreshEditedUI(sid);
    refreshChangesBtn();
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

document.querySelectorAll('.reset-variant').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const sid = btn.dataset.variant;
    if(!confirm('¿Volver esta propuesta al original? Se pierden tus ediciones de esta variante.')) return;
    document.querySelectorAll('#p-'+sid+' .sheet').forEach(sh=>{
      sh.innerHTML = originals.get(sh);
      localStorage.removeItem(sheetKey(sh));
    });
    refreshEditedUI(sid);
    refreshChangesBtn();
    toast('Propuesta restablecida al original.');
  });
});

/* ================= texto de ayuda segun entorno ================= */
if(!(window.claude && window.claude.downloads)){
  document.querySelectorAll('.dl-hint').forEach(h=>{
    h.innerHTML = '<b>Word</b> y <b>PDF</b> descargan directo la plantilla original, verificada y lista ' +
      'para enviar. Si editas cualquier texto de una hoja, aparecen dos botones verdes: <b>Word</b> ' +
      'de tu versi\u00f3n editada directo, y <b>PDF</b> mediante el di\u00e1logo de impresi\u00f3n.';
  });
}

/* ================= reporte de cambios del usuario ================= */
function normTxt(t){ return t.replace(/\s+/g,' ').trim(); }
function collectChanges(){
  const lines = [];
  document.querySelectorAll('section[role=tabpanel]').forEach(sec=>{
    const variant = sec.querySelector('h2').firstChild.textContent.trim();
    sec.querySelectorAll('.sheet').forEach((sh, pi)=>{
      const orig = originals.get(sh);
      if(sh.innerHTML === orig) return;
      const tmp = document.createElement('div'); tmp.innerHTML = orig;
      const oldEds = tmp.querySelectorAll('.ed');
      const newEds = sh.querySelectorAll('.ed');
      if(oldEds.length !== newEds.length){
        lines.push(`[${variant} · página ${pi+1}] estructura modificada — revisar manualmente`);
        return;
      }
      for(let i=0;i<oldEds.length;i++){
        const o = normTxt(oldEds[i].textContent), n = normTxt(newEds[i].textContent);
        if(o !== n){
          lines.push(`[${variant} · página ${pi+1}]`);
          lines.push(`  ANTES : ${o}`);
          lines.push(`  AHORA : ${n}`);
          lines.push('');
        }
      }
    });
  });
  return lines.length ? lines.join('\n') : 'Sin diferencias respecto del original.';
}
function refreshChangesBtn(){
  const any = [...document.querySelectorAll('.sheet')].some(sh => sh.innerHTML !== originals.get(sh));
  const btn = document.getElementById('changes-btn');
  if(btn) btn.hidden = !any;
}
document.getElementById('changes-btn').addEventListener('click', ()=>{
  document.getElementById('changes-txt').value = collectChanges();
  document.getElementById('changes-dlg').showModal();
});
document.getElementById('changes-close').addEventListener('click',
  ()=> document.getElementById('changes-dlg').close());
document.getElementById('changes-dl').addEventListener('click', ()=>{
  const txt = document.getElementById('changes-txt').value;
  deliver('cambios-cv-jeniffer.txt', new TextEncoder().encode(txt));
});

/* ================= entrega de archivos (visor claude.ai o navegador) ================= */
function toast(msg){
  let t = document.getElementById('toast');
  if(!t){
    t = document.createElement('div'); t.id='toast'; t.setAttribute('role','status');
    document.body.appendChild(t);
  }
  t.textContent = msg; t.classList.add('show');
  clearTimeout(t._t); t._t = setTimeout(()=>t.classList.remove('show'), 4200);
}
function anchorDownload(filename, bytes){
  let bin=''; const CH=0x8000;
  for(let i=0;i<bytes.length;i+=CH) bin+=String.fromCharCode.apply(null, bytes.subarray(i,i+CH));
  const a=document.createElement('a');
  a.href='data:application/octet-stream;base64,'+btoa(bin);
  a.download=filename;
  document.body.appendChild(a); a.click(); a.remove();
}
async function deliver(filename, bytes){
  if(window.claude && window.claude.downloads){
    try{
      await window.claude.downloads.save({filename, data: bytes});
      toast('Descarga confirmada: ' + filename);
    }catch(e){
      const code = e && e.code;
      if(code === 'declined') return;
      if(code === 'rate_limited'){ toast('Hay una descarga pendiente de confirmar. Espera un momento y reintenta.'); return; }
      if(code === 'extension_not_enabled' || code === 'rejected_extension'){
        toast('Este visor no permite guardar este tipo de archivo. Usa los archivos enviados en el chat.');
        return;
      }
      toast('No se pudo guardar: ' + ((e && e.message) || 'sin detalle'));
    }
    return;
  }
  anchorDownload(filename, bytes);
}
function b64ToBytes(b64){
  const bin = atob(b64), out = new Uint8Array(bin.length);
  for(let i=0;i<bin.length;i++) out[i] = bin.charCodeAt(i);
  return out;
}
/* Botones preestablecidos: interceptar el ancla y entregar via la API del visor */
document.querySelectorAll('a.dl-btn.word').forEach(aEl=>{
  aEl.addEventListener('click', e=>{
    e.preventDefault();
    const b64 = aEl.getAttribute('href').split('base64,')[1];
    deliver(aEl.getAttribute('download'), b64ToBytes(b64));
  });
});
document.querySelectorAll('a.dl-btn.pdf').forEach(aEl=>{
  aEl.addEventListener('click', async e=>{
    if(!(window.claude && window.claude.downloads)) return;   // fuera del visor: ancla normal
    e.preventDefault();
    const name = aEl.getAttribute('download');
    // Intento de descarga directa primero: si el visor algun dia permite
    // .pdf, el boton descargara sin pasos extra.
    try{
      const b64 = aEl.getAttribute('href').split('base64,')[1];
      await window.claude.downloads.save({filename: name, data: b64ToBytes(b64)});
      toast('Descarga confirmada: ' + name);
      return;
    }catch(err){
      const code = err && err.code;
      if(code === 'declined') return;
      if(code === 'rate_limited'){ toast('Hay una descarga pendiente de confirmar. Reintenta en unos segundos.'); return; }
      // rejected_extension / extension_not_enabled / resto: caer a impresion
    }
    const sid = aEl.closest('section').id.replace('p-','');
    buildPrintRootPristine(sid);
    toast('claude.ai no permite bajar .pdf directo. En el diálogo elige «Guardar como PDF» — un solo clic más.');
    requestAnimationFrame(()=>requestAnimationFrame(()=>window.print()));
  });
});

/* ================= imprimir / PDF de la version actual ================= */
function buildPrintRoot(sid, pristine){
  const root=document.getElementById('print-root');
  root.innerHTML='';
  document.querySelectorAll('#p-'+sid+' .sheet').forEach(sh=>{
    const wrap=document.createElement('div'); wrap.className='pframe';
    const clone=sh.cloneNode(pristine ? false : true);
    if(pristine) clone.innerHTML = originals.get(sh);
    clone.removeAttribute('style');
    clone.querySelectorAll('.ed').forEach(e=>e.removeAttribute('contenteditable'));
    wrap.appendChild(clone);
    root.appendChild(wrap);
  });
}
function buildPrintRootPristine(sid){ buildPrintRoot(sid, true); }
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
  const chunks=[...parts, ...centrals, eocd];
  let total=0; chunks.forEach(c=>total+=c.length);
  const out=new Uint8Array(total); let off=0;
  chunks.forEach(c=>{ out.set(c, off); off+=c.length; });
  return out;
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

/* ======== exportacion con el diseno de la plantilla (parche de docx) ======== */
async function inflateRaw(u8){
  const ds = new DecompressionStream('deflate-raw');
  const st = new Blob([u8]).stream().pipeThrough(ds);
  return new Uint8Array(await new Response(st).arrayBuffer());
}
async function unzipDocx(bytes){
  const dv = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
  let e = bytes.length - 22;
  while(e >= 0 && dv.getUint32(e, true) !== 0x06054b50) e--;
  if(e < 0) throw new Error('ZIP invalido');
  const n = dv.getUint16(e+10, true), cdOff = dv.getUint32(e+16, true);
  const files = []; let p = cdOff;
  const td = new TextDecoder();
  for(let i=0;i<n;i++){
    const method = dv.getUint16(p+10,true), csize = dv.getUint32(p+20,true);
    const nameLen = dv.getUint16(p+28,true), extraLen = dv.getUint16(p+30,true), cmtLen = dv.getUint16(p+32,true);
    const lho = dv.getUint32(p+42,true);
    const name = td.decode(bytes.subarray(p+46, p+46+nameLen));
    const lnl = dv.getUint16(lho+26,true), lel = dv.getUint16(lho+28,true);
    const dataOff = lho+30+lnl+lel;
    files.push({name, method, raw: bytes.subarray(dataOff, dataOff+csize)});
    p += 46+nameLen+extraLen+cmtLen;
  }
  for(const f of files){
    f.data = f.method === 8 ? await inflateRaw(f.raw) : new Uint8Array(f.raw);
    delete f.raw;
  }
  return files;
}
const W_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
function escRx(txt){ return txt.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
function flexRegex(txt){ return new RegExp(escRx(txt).replace(/\s+/g, '\\s+')); }
function fullRegex(txt){ return new RegExp('^' + escRx(txt).replace(/\s+/g, '\\s+') + '$'); }
function collectFieldChanges(sid){
  const out = [];
  document.querySelectorAll('#p-'+sid+' .sheet').forEach(sh=>{
    const tmp = document.createElement('div'); tmp.innerHTML = originals.get(sh);
    const o = tmp.querySelectorAll('.ed'), c = sh.querySelectorAll('.ed');
    if(o.length !== c.length) throw Object.assign(new Error('estructura'), {code:'struct'});
    for(let i=0;i<o.length;i++){
      const a = normTxt(o[i].textContent), b = normTxt(c[i].textContent);
      if(a !== b) out.push({old: a, node: c[i]});
    }
  });
  return out;
}
function runText(r){
  let t=''; const ts = r.getElementsByTagNameNS(W_NS,'t');
  for(const x of ts) t += x.textContent;
  return t;
}
function makeRun(xdoc, templateRun, text, bold){
  const r = xdoc.createElementNS(W_NS,'w:r');
  const tpr = templateRun && templateRun.getElementsByTagNameNS(W_NS,'rPr')[0];
  if(tpr){
    const pr = tpr.cloneNode(true);
    const bs = [...pr.getElementsByTagNameNS(W_NS,'b')];
    bs.forEach(x=>pr.removeChild(x));
    if(bold) pr.appendChild(xdoc.createElementNS(W_NS,'w:b'));
    r.appendChild(pr);
  } else if(bold){
    const pr = xdoc.createElementNS(W_NS,'w:rPr');
    pr.appendChild(xdoc.createElementNS(W_NS,'w:b'));
    r.appendChild(pr);
  }
  const t = xdoc.createElementNS(W_NS,'w:t');
  t.setAttribute('xml:space','preserve');
  t.textContent = text;
  r.appendChild(t);
  return r;
}
function applyChange(xdoc, ch){
  const rx  = flexRegex(ch.old);
  const rxU = flexRegex(ch.old.toUpperCase());
  const newText = normTxt(ch.node.textContent);
  // Caso A: el texto completo vive dentro de un solo w:t
  for(const t of xdoc.getElementsByTagNameNS(W_NS,'t')){
    if(rx.test(t.textContent)){
      t.textContent = t.textContent.replace(rx, newText);
      t.setAttribute('xml:space','preserve');
      return true;
    }
    if(rxU.test(t.textContent)){
      t.textContent = t.textContent.replace(rxU, newText.toUpperCase());
      t.setAttribute('xml:space','preserve');
      return true;
    }
  }
  // Caso B: el texto abarca la cola de runs de un parrafo (vinietas con negrita)
  for(const par of xdoc.getElementsByTagNameNS(W_NS,'p')){
    const rs = [...par.getElementsByTagNameNS(W_NS,'r')].filter(r => r.parentNode === par);
    for(let k=0;k<rs.length;k++){
      let concat = '';
      for(let j=k;j<rs.length;j++) concat += runText(rs[j]);
      if(!fullRegex(ch.old).test(normTxt(concat))) continue;
      const isBold = r => r.getElementsByTagNameNS(W_NS,'b').length > 0;
      const tail = rs.slice(k);
      const baseTpl = tail.find(r=>!isBold(r)) || tail[0];
      const boldTpl = tail.find(isBold) || baseTpl;
      tail.forEach(r=>par.removeChild(r));
      (function emit(node){
        for(const nd of node.childNodes){
          if(nd.nodeType === 3){
            if(nd.textContent) par.appendChild(makeRun(xdoc, baseTpl, nd.textContent, false));
          } else if(nd.nodeType === 1){
            if(nd.tagName === 'B' || nd.tagName === 'STRONG')
              par.appendChild(makeRun(xdoc, boldTpl, nd.textContent, true));
            else if(!nd.classList.contains('bg')) emit(nd);
          }
        }
      })(ch.node);
      return true;
    }
  }
  return false;
}
async function exportPatchedDocx(sid){
  const cfg = VARIANTS[sid];
  const srcA = document.querySelector('#p-' + sid + ' a.dl-btn.word');
  const bytes = b64ToBytes(srcA.getAttribute('href').split('base64,')[1]);
  const files = await unzipDocx(bytes);
  const docF = files.find(f=>f.name === 'word/document.xml');
  const xml = new TextDecoder().decode(docF.data);
  const xdoc = new DOMParser().parseFromString(xml, 'application/xml');
  if(xdoc.getElementsByTagName('parsererror').length) throw new Error('XML de plantilla ilegible');
  const changes = collectFieldChanges(sid);
  const missed = [];
  for(const ch of changes){
    if(!applyChange(xdoc, ch)) missed.push(ch.old.slice(0, 40));
  }
  docF.data = new TextEncoder().encode(new XMLSerializer().serializeToString(xdoc));
  const out = zipStore(files.map(f=>({name: f.name, data: f.data})));
  await deliver('CV Jeniffer Mieres - ' + cfg.label + ' (editado).docx', out);
  if(missed.length) toast('Ojo: ' + missed.length + ' cambio(s) no se pudieron aplicar automaticamente.');
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
  const bytes = zipStore([
    {name:'[Content_Types].xml', data:enc.encode(ct)},
    {name:'_rels/.rels', data:enc.encode(rels)},
    {name:'word/document.xml', data:enc.encode(doc)},
  ]);
  deliver('CV Jeniffer Mieres - ' + sid + ' (editado).docx', bytes);
}
document.querySelectorAll('.dl-btn.export-word').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    const sid = btn.dataset.variant;
    try{
      if(!window.DecompressionStream) throw Object.assign(new Error('sin soporte'), {code:'nods'});
      await exportPatchedDocx(sid);
    }catch(e){
      if(e && e.code === 'struct'){
        toast('La estructura de una hoja cambio demasiado. Usa Restablecer y reaplica tus cambios.');
        return;
      }
      try{ exportVariantDocx(sid); toast('Descargado en formato simplificado (respaldo).'); }
      catch(e2){ toast('No se pudo generar el Word editado: ' + e2.message); }
    }
  });
});
document.querySelectorAll('.dl-btn.export-pdf').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    buildPrintRoot(btn.dataset.variant);
    requestAnimationFrame(()=>requestAnimationFrame(()=>window.print()));
  });
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
      <p class="eyebrow">Seis propuestas · Agosto 2026</p>
      <h1 class="title">Jeniffer Mieres Contreras<span>Trabajadora Social</span></h1>
    </div>
    <p class="stand">Seis tratamientos visuales para la postulación a <b>Trabajador/a Social ·
      Subdirección de Bienestar y Compensaciones</b> de la Pontificia Universidad Católica de Chile.
      El contenido es idéntico en los seis; solo cambia el diseño.</p>
  </header>
  <p class="edit-hint">{PENCIL}<span>Haz clic en cualquier texto de una hoja para editarlo. Los cambios se
    guardan <b>automáticamente en este navegador</b> — nadie más los ve, y puedes restablecer el original
    en cualquier momento.</span>
    <button type="button" id="changes-btn" hidden>Ver mis cambios</button></p>
  <dialog id="changes-dlg">
    <h3>Tus cambios respecto del original</h3>
    <p>Copia este texto y pégaselo a Claude en el chat para replicar los cambios en los seis diseños,
      o descárgalo como archivo.</p>
    <textarea id="changes-txt" readonly spellcheck="false"></textarea>
    <div class="dlg-row">
      <button type="button" id="changes-dl" class="dl-btn edit">Descargar .txt</button>
      <button type="button" id="changes-close" class="dl-btn ghost">Cerrar</button>
    </div>
  </dialog>

  <div class="tabs" role="tablist" aria-label="Propuestas de diseño">{tabs}</div>
  {panels}

  <footer class="foot">
    <p>Las seis cierran en <b>2 páginas exactas</b>, sin ningún cargo partido entre páginas.</p>
    <p>Tipografías incrustadas con las métricas de <b>Calibri</b> y <b>Cambria</b>.</p>
  </footer>
</div>
<div id="print-root" aria-hidden="true"></div>
<script>{JS}</script>
"""
open("cv_propuestas.html", "w", encoding="utf-8").write(HTML)
print("HTML:", len(HTML)/1024, "KB")
