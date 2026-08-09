# -*- coding: utf-8 -*-
"""CV Jeniffer Mieres Contreras - dossier editorial ejecutivo."""
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_TAB_ALIGNMENT, WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

INK    = RGBColor(0x16, 0x18, 0x1A)
BODY   = RGBColor(0x33, 0x37, 0x3B)
META   = RGBColor(0x6C, 0x71, 0x76)
FAINT  = RGBColor(0xA8, 0xAD, 0xB2)
ACCENT = RGBColor(0x6E, 0x21, 0x30)
HAIR   = "6E2130"

SERIF, SANS = "Cambria", "Calibri"
RAIL, BUL   = Cm(2.9), Cm(0.34)

doc = Document()
s = doc.sections[0]
s.top_margin, s.bottom_margin = Cm(1.25), Cm(1.15)
s.left_margin, s.right_margin = Cm(1.8), Cm(1.6)
FULL = s.page_width - s.left_margin - s.right_margin

st = doc.styles["Normal"]
st.font.name = SERIF; st.font.size = Pt(9.5); st.font.color.rgb = BODY
rPr = st.element.get_or_add_rPr()
rPr.rFonts.set(qn("w:eastAsia"), SERIF)
lang = OxmlElement("w:lang"); lang.set(qn("w:val"), "es-CL"); rPr.append(lang)
st.paragraph_format.space_before = Pt(0)
st.paragraph_format.space_after  = Pt(0)
st.paragraph_format.line_spacing = 1.17

# --- particion de palabras: justificado sin rios ni huecos ---
sett = doc.settings.element
for tag, val in (("w:autoHyphenation", "1"), ("w:consecutiveHyphenLimit", "2"),
                 ("w:doNotHyphenateCaps", "1"), ("w:hyphenationZone", "510")):
    e = OxmlElement(tag); e.set(qn("w:val"), val); sett.append(e)

def P(before=0, after=0, line=1.17, keep=False, indent=None, hang=None, tabs=(), just=False):
    p = doc.add_paragraph(); pf = p.paragraph_format
    pf.space_before, pf.space_after, pf.line_spacing = Pt(before), Pt(after), line
    if keep: pf.keep_with_next = True
    if indent is not None: pf.left_indent = indent
    if hang   is not None: pf.first_line_indent = hang
    for pos, al in tabs: pf.tab_stops.add_tab_stop(pos, al)
    if just: p.alignment = WD_ALIGN_PARAGRAPH.JUSTIFY
    return p

def R(p, text, font=SERIF, size=9.5, bold=False, color=BODY, track=None, caps=False):
    r = p.add_run(text)
    r.font.name = font; r.font.size = Pt(size); r.bold = bold; r.font.color.rgb = color
    rp = r._element.get_or_add_rPr()
    rp.rFonts.set(qn("w:eastAsia"), font); rp.rFonts.set(qn("w:cs"), font)
    if caps:
        e = OxmlElement("w:caps"); e.set(qn("w:val"), "1"); rp.append(e)
    if track:
        e = OxmlElement("w:spacing"); e.set(qn("w:val"), str(track)); rp.append(e)
    return r

def hairline(p, sz=4, space=7):
    pPr = p._p.get_or_add_pPr()
    b = OxmlElement("w:pBdr"); bt = OxmlElement("w:bottom")
    bt.set(qn("w:val"), "single"); bt.set(qn("w:sz"), str(sz))
    bt.set(qn("w:space"), str(space)); bt.set(qn("w:color"), HAIR)
    b.append(bt); pPr.append(bt.getparent()); pPr.append(b)

def label_run(p, label):
    R(p, label, font=SANS, size=7.5, bold=True, color=ACCENT, track=20, caps=True)
    p.add_run("\t")

def rail(label, before=16, right_tab=False, just=True):
    tabs = [(RAIL, WD_TAB_ALIGNMENT.LEFT)]
    if right_tab: tabs.append((FULL, WD_TAB_ALIGNMENT.RIGHT))
    p = P(before=before, keep=True, indent=RAIL, hang=-RAIL, tabs=tabs, just=just)
    label_run(p, label)
    return p

def rail_bullet(label, before=16):
    p = P(before=before, indent=RAIL + BUL, hang=-(RAIL + BUL), just=True,
          tabs=[(RAIL, WD_TAB_ALIGNMENT.LEFT), (RAIL + BUL, WD_TAB_ALIGNMENT.LEFT)])
    label_run(p, label)
    R(p, "•", size=8.5, color=ACCENT); p.add_run("\t")
    p.paragraph_format.space_after = Pt(3.7)
    return p

def bullet(pre="", key="", post="", after=3.7):
    p = P(after=after, indent=RAIL + BUL, hang=-BUL, just=True)
    R(p, "•", size=8.5, color=ACCENT); p.add_run("\t")
    if pre:  R(p, pre)
    if key:  R(p, key, bold=True, color=INK)
    if post: R(p, post)
    return p

def no_hyphen(p):
    e = OxmlElement("w:suppressAutoHyphens"); e.set(qn("w:val"), "1")
    p._p.get_or_add_pPr().append(e)
    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
    return p

def keywords(p, text, size=8.5):
    no_hyphen(p)
    for i, part in enumerate(text.split("  ·  ")):
        if i: R(p, "  ·  ", size=size, color=FAINT)
        R(p, part, size=size)

def job(company, role, dates, first=False, label=None, before=10.5, brk=False):
    p = rail(label, before=(26 if label=="Formación" else 16), right_tab=True, just=False) if first else \
        P(before=before, keep=True, indent=RAIL, tabs=[(FULL, WD_TAB_ALIGNMENT.RIGHT)])
    if brk: p.paragraph_format.page_break_before = True
    R(p, company, size=10.5, bold=True, color=INK); p.add_run("\t")
    R(p, dates, font=SANS, size=8.5, color=META, track=14)
    q = P(before=2, after=4, keep=True, indent=RAIL)
    R(q, role, font=SANS, size=9, color=META, track=8)

def edu(title, inst, year, first=False, label=None, before=15):
    p = rail(label, before=(26 if label=="Formación" else 16), right_tab=True, just=False) if first else \
        P(before=before, keep=True, indent=RAIL, tabs=[(FULL, WD_TAB_ALIGNMENT.RIGHT)])
    R(p, title, size=9.5, bold=True, color=INK); p.add_run("\t")
    R(p, year, font=SANS, size=8.5, color=META, track=14)
    q = P(before=1.5, indent=RAIL); R(q, inst, font=SANS, size=8.5, color=META, track=8)

# ---------------------------- ENCABEZADO ----------------------------
p = P(after=3)
R(p, "JENIFFER MIERES CONTRERAS", size=21, color=INK, track=40)
p = P(after=4)
R(p, "Trabajadora Social", font=SANS, size=10.5, color=RGBColor(0x4A,0x4F,0x54), track=16)
R(p, "   |   ", font=SANS, size=10.5, color=ACCENT)
R(p, "Bienestar Laboral, Calidad de Vida y Gestión de Beneficios",
  font=SANS, size=10.5, color=RGBColor(0x4A,0x4F,0x54), track=16)
p = P()
for i, part in enumerate(["+56 9 4903 1682", "jeniffer.mieres@gmail.com",
                          "El Monte, Región Metropolitana"]):
    if i: R(p, "   ·   ", font=SANS, size=8.5, color=FAINT)
    R(p, part, font=SANS, size=8.5, color=META, track=12)
hairline(p)

# ---------------------------- PERFIL ----------------------------
p = rail("Perfil", before=14)
p.paragraph_format.line_spacing = 1.19
R(p, "Trabajadora Social titulada con distinción y más de 9 años de experiencia en bienestar laboral, gestión de beneficios, evaluación socioeconómica y acompañamiento social.")
for txt in (
  "Creé desde cero el Departamento de Bienestar Social de una organización con más de 200 colaboradores, administrando el Seguro Complementario de Salud, las licencias médicas y las cargas familiares. Luego lideré la gestión de personas y bienestar en la consultora minera Bmining, a cargo de programas, convenios, capacitación y clima laboral, y de la matriz de riesgo y salud en el trabajo con seguimiento por KPI.",
  "Hoy, en la Universidad de Santiago de Chile, realizo evaluaciones socioeconómicas, gestiono beneficios ministeriales e internos y acompaño casos que requieren articular áreas internas con redes de apoyo. Sumo además una mirada de proceso: fui auditora interna del Sistema de Gestión Integrado (ISO 9001, 14001 y 45001)."):
    q = P(before=6, line=1.19, indent=RAIL, just=True); R(q, txt)

# ---------------------------- EXPERTISE ----------------------------
p = rail("Expertise", before=13)
p.paragraph_format.line_spacing = 1.34
keywords(p,
 "Creación, implementación y evaluación de programas de bienestar  ·  Gestión de beneficios internos y externos  ·  "
 "Evaluación socioeconómica e informes sociales  ·  Seguro Complementario de Salud  ·  "
 "Licencias médicas y cargas familiares  ·  Convenios institucionales y gestión de proveedores  ·  "
 "Gestión y seguimiento de casos sociales  ·  Orientación en salud y vivienda  ·  "
 "Coordinación de actividades y eventos  ·  Diseño y facilitación de talleres y capacitaciones  ·  "
 "Articulación con redes de apoyo  ·  Diversidad e inclusión  ·  Calidad de servicio")

# ---------------------------- EXPERIENCIA ----------------------------
job("Universidad de Santiago de Chile", "Trabajadora Social · Departamento de Beneficios Estudiantiles",
    "Agosto 2023 – Actualidad", first=True, label="Experiencia")
bullet("Realizo la ", "evaluación socioeconómica",
       " de cada caso, revisando antecedentes y acreditaciones en los sistemas del Ministerio de Educación para "
       "definir con autonomía qué apoyo corresponde.")
bullet("Gestiono ", "beneficios ministeriales e internos",
       " de principio a fin: verificación de requisitos, preselección, seguimiento y registro documental que "
       "permite auditar el caso posteriormente.")
bullet("Atiendo al estudiantado de manera permanente y entrego ", "orientación social personalizada",
       ", acompañándolo durante todo el proceso de postulación.")
bullet("", "Coordino casos complejos",
       " con áreas internas de la universidad y con redes de apoyo externas cuando la necesidad excede el "
       "ámbito académico.")
bullet("Elaboro ", "informes sociales",
       " y mantengo la trazabilidad administrativa de la cartera de casos a mi cargo.")

job("Bmining · Desarrollo e Innovación para la Minería SpA",
    "Líder en Gestión de Personas · Consultora para la industria minera", "Octubre 2018 – Febrero 2023")
bullet("Estuve a cargo de los ", "programas de bienestar y beneficios",
       " de los colaboradores, desde la definición de la oferta hasta su implementación y seguimiento, con foco "
       "en el clima laboral y en el día a día de la empresa.")
bullet("Gestioné ", "convenios y beneficios orientados a la calidad de vida laboral",
       ", administrando la relación con instituciones y proveedores externos.")
bullet("Organicé ", "actividades corporativas de bienestar e integración",
       ", articulando a las distintas áreas para asegurar convocatoria y buena ejecución.")
bullet("Tuve a cargo la ", "matriz de riesgo y salud en el trabajo",
       " junto al prevencionista externo, con seguimiento por KPI para dar cumplimiento a la certificación.")
bullet("Lideré el ", "plan anual de capacitaciones internas", " de la compañía.")
bullet("Asumí durante un año el ", "Sistema de Gestión Integrado como auditora interna",
       " en las certificaciones ISO 9001:2015, ISO 14001:2015 e ISO 45001:2018 (Seguridad y Salud en el Trabajo).")

job("Fundación Fondo Esperanza", "Monitora · Grupo Emprendedores", "Octubre 2017 – Septiembre 2018", before=16, brk=True)
bullet("Diseñé y dicté ", "capacitaciones",
       " para emprendedores, con metodologías prácticas y grupales, y acompañé la gestión de sus proyectos "
       "revisando con cada participante qué reforzar.")
bullet("Orienté a los emprendedores en materia de ", "vivienda",
       ", articulando con las redes de la comuna, y los vinculé con recursos disponibles que muchas veces no "
       "sabían que tenían a su alcance.")

job("Ferretería San Francisco", "Encargada · Departamento de Bienestar Social",
    "Diciembre 2016 – Septiembre 2017", before=18)
bullet("", "Creé el departamento desde cero",
       " para una dotación de más de 200 colaboradores, definiendo la planificación de actividades y beneficios.")
bullet("Administré el ", "Seguro Complementario de Salud",
       " de la dotación, apoyando a los trabajadores en el acceso, uso y tramitación de reembolsos.")
bullet("Gestioné ", "licencias médicas ante la Caja de Compensación Los Andes",
       " y la incorporación de cargas familiares de los trabajadores.")
bullet("Realicé ", "visitas semanales a la segunda sucursal",
       ", atendiendo en terreno las necesidades de beneficios internos y externos, principalmente en "
       "vivienda y salud.")
bullet("Gestioné ", "convenios con instituciones externas", " para mejorar la calidad de vida en el trabajo.")

# ---------------------------- FORMACIÓN ----------------------------
edu("Diplomado en Bienestar Organizacional y Estrategias de Diversidad e Inclusión",
    "Universidad de Santiago de Chile", "En curso", first=True, label="Formación")
edu("Postítulo en Trabajo Social en Niñez, Adolescencia y Familia en el Contexto Judicial",
    "Universidad Andrés Bello", "2016")
edu("Trabajadora Social · Título profesional con distinción",
    "Instituto Profesional AIEP", "2012")

# ---------------------------- CERTIFICACIONES ----------------------------
p = rail_bullet("Certificaciones", before=26)
R(p, "Certificación Gatekeepers", bold=True, color=INK)
R(p, " — agentes comunitarios en prevención del suicidio (2026).")
bullet("", "Interpretación auditor interno", " — TÜV Rheinland (2019).")
bullet("", "Ley 21.327, Modernización de la Dirección del Trabajo", " — Bicentenario OTEC (2021).")
bullet("", "Cálculo de finiquito", " — Consultores y Asesores en Capacitación Chile Ltda. (2022).")

# ---------------------------- COMPETENCIAS ----------------------------
p = rail("Competencias", before=26)
p.paragraph_format.line_spacing = 1.34
keywords(p,
 "Vocación y orientación al servicio  ·  Excelente trato al usuario y altos estándares de calidad de atención  ·  "
 "Comunicación efectiva  ·  Trabajo en equipo y coordinación transversal con múltiples unidades  ·  "
 "Autonomía y resolución de situaciones  ·  Flexibilidad y adaptación al cambio  ·  "
 "Proactividad y aporte al clima laboral")

p = rail("Herramientas", before=22)
R(p, "Microsoft Office (Word, Excel y PowerPoint) nivel intermedio; conocimiento básico de BUK, SAP y Talana; "
     "plataformas institucionales del Ministerio de Educación y gestión documental.")

p = rail("Disponibilidad", before=18)
R(p, "Movilización propia y licencia clase B; disponibilidad para desempeñarse en Campus San Joaquín y en "
     "otros campus de la Región Metropolitana.")

out = "/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_Contreras_Trabajadora_Social_UC.docx"
doc.save(out)
print("OK ->", out)
