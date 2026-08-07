# -*- coding: utf-8 -*-
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

NAVY  = RGBColor(0x1F, 0x35, 0x5E)
GRAY  = RGBColor(0x44, 0x44, 0x44)
MID   = RGBColor(0x5A, 0x5A, 0x5A)
BLACK = RGBColor(0x1A, 0x1A, 0x1A)
FONT  = "Calibri"
LINE  = 1.06

doc = Document()
s = doc.sections[0]
s.top_margin = Cm(1.4); s.bottom_margin = Cm(1.3)
s.left_margin = Cm(1.7); s.right_margin = Cm(1.7)
CONTENT_W = s.page_width - s.left_margin - s.right_margin

st = doc.styles["Normal"]
st.font.name = FONT; st.font.size = Pt(10); st.font.color.rgb = BLACK
st.element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
st.paragraph_format.space_before = Pt(0)
st.paragraph_format.space_after = Pt(0)
st.paragraph_format.line_spacing = LINE

def sp(p, before=0, after=0, line=LINE, keep=False):
    p.paragraph_format.space_before = Pt(before)
    p.paragraph_format.space_after = Pt(after)
    p.paragraph_format.line_spacing = line
    if keep: p.paragraph_format.keep_with_next = True

def run(p, text, size=10, bold=False, italic=False, color=BLACK, spacing=None):
    r = p.add_run(text)
    r.font.name = FONT; r.font.size = Pt(size); r.bold = bold; r.italic = italic
    r.font.color.rgb = color
    r._element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
    if spacing:
        el = OxmlElement("w:spacing"); el.set(qn("w:val"), str(spacing))
        r._element.rPr.append(el)
    return r

def rule(p, color="1F355E", size=6, space=2):
    pPr = p._p.get_or_add_pPr()
    pbdr = OxmlElement("w:pBdr"); b = OxmlElement("w:bottom")
    b.set(qn("w:val"), "single"); b.set(qn("w:sz"), str(size))
    b.set(qn("w:space"), str(space)); b.set(qn("w:color"), color)
    pbdr.append(b); pPr.append(pbdr)

def heading(text, first=False):
    p = doc.add_paragraph(); sp(p, before=0 if first else 11, after=5, keep=True)
    run(p, text.upper(), size=10.5, bold=True, color=NAVY, spacing=30)
    rule(p)

def bullet(pre="", key="", post="", keep=False):
    p = doc.add_paragraph(style="List Bullet")
    p.paragraph_format.left_indent = Cm(0.42)
    p.paragraph_format.first_line_indent = Cm(-0.42)
    sp(p, before=0, after=2.5, keep=keep)
    if pre:  run(p, pre, size=10)
    if key:  run(p, key, size=10, bold=True)
    if post: run(p, post, size=10)

def job(org, place, period, title):
    p = doc.add_paragraph(); sp(p, before=8, after=0, keep=True)
    p.paragraph_format.tab_stops.add_tab_stop(CONTENT_W, WD_TAB_ALIGNMENT.RIGHT)
    run(p, org, size=10.5, bold=True, color=NAVY)
    if place: run(p, "  |  " + place, size=9.5, color=MID)
    run(p, "\t" + period, size=9.5, bold=True, color=MID)
    q = doc.add_paragraph(); sp(q, before=1, after=3, keep=True)
    run(q, title, size=10, italic=True, color=GRAY)

# ---------------- ENCABEZADO ----------------
p = doc.add_paragraph(); sp(p, after=1)
run(p, "[NOMBRE Y APELLIDOS]", size=21, bold=True, color=NAVY, spacing=6)
p = doc.add_paragraph(); sp(p, before=1, after=3)
run(p, "Trabajadora Social  |  Bienestar Laboral, Calidad de Vida y Gestión de Beneficios",
    size=11, bold=True, color=GRAY)
p = doc.add_paragraph(); sp(p, before=0, after=2)
run(p, "[+56 9 XXXX XXXX]  ·  [correo@correo.cl]  ·  [Comuna], Región Metropolitana  ·  [linkedin.com/in/...]",
    size=9.5, color=MID)
rule(p, color="C9CEDB", size=6, space=4)

# ---------------- PERFIL ----------------
heading("Perfil profesional")
p = doc.add_paragraph(); sp(p, after=0, line=1.08)
run(p,
 "Trabajadora Social [titulada de la Universidad XXXX] con más de 8 años de experiencia en bienestar laboral, "
 "gestión de beneficios y atención social directa, desarrollada en instituciones de educación superior y en "
 "empresas privadas. Actualmente me desempeño en la Universidad de Santiago de Chile, donde realizo evaluaciones "
 "socioeconómicas, administro beneficios institucionales y ministeriales y acompaño casos sociales complejos "
 "articulando unidades internas y redes externas. Antes lideré la gestión de personas y bienestar en Bmining, "
 "coordinando programas, convenios y actividades de calidad de vida; y con anterioridad tuve a cargo el área de "
 "bienestar y el Seguro Complementario de Salud de una dotación superior a 200 colaboradores. A la atención "
 "directa sumo una mirada de "
 "proceso: fui auditora interna del Sistema de Gestión Integrado (ISO 9001, 14001 y 45001), lo que me permite "
 "implementar y evaluar programas con estándares verificables. Diplomado en Bienestar Organizacional y "
 "Estrategias de Diversidad e Inclusión.", size=10)

# ---------------- ÁREAS DE EXPERTISE ----------------
heading("Áreas de expertise")
p = doc.add_paragraph(); sp(p, after=0, line=1.1)
run(p, "Diseño, implementación y evaluación de programas de bienestar  ·  Gestión de beneficios internos y externos  ·  "
       "Evaluación socioeconómica e informes sociales  ·  Seguro Complementario de Salud  ·  "
       "Convenios institucionales y gestión de proveedores  ·  Gestión y seguimiento de casos sociales  ·  "
       "Orientación y atención de usuarios  ·  Coordinación de actividades y eventos  ·  "
       "Diseño y facilitación de talleres y capacitaciones  ·  Articulación con redes de apoyo  ·  "
       "Calidad de servicio  ·  Aplicación de normativa y gestión documental", size=9.5)

# ---------------- EXPERIENCIA ----------------
heading("Experiencia profesional")

job("Universidad de Santiago de Chile", "Santiago", "Agosto 2023 – Presente",
    "Trabajadora Social · Departamento de Beneficios Estudiantiles")
bullet("Realizo ", "evaluaciones socioeconómicas",
       " que determinan el acceso a beneficios institucionales y ministeriales, resolviendo cada caso con "
       "autonomía y aplicando la normativa vigente.")
bullet("Gestiono ", "beneficios internos y externos",
       " de principio a fin: verificación de requisitos, asignación, seguimiento y cierre documental.")
bullet("Atiendo público de manera permanente y entrego ", "orientación social personalizada",
       ", sosteniendo el acompañamiento del usuario durante todo el proceso de atención.")
bullet("", "Coordino casos sociales complejos",
       " con distintas unidades internas de la universidad y con redes de apoyo externas, gestionando las "
       "derivaciones que cada situación requiere.")
bullet("Elaboro ", "informes sociales",
       " y mantengo la trazabilidad administrativa y documental de la cartera de casos a mi cargo.")

job("Bmining · Desarrollo e Innovación para la Minería SpA", None, "Octubre 2018 – Febrero 2023",
    "Líder en Gestión de Personas")
bullet("Coordiné los ", "programas de bienestar y beneficios",
       " dirigidos a los colaboradores, desde la definición de la oferta hasta su implementación y seguimiento.")
bullet("Gestioné ", "convenios y beneficios orientados a la calidad de vida laboral",
       ", administrando la relación con instituciones y proveedores externos.")
bullet("Organicé ", "actividades corporativas de bienestar e integración",
       ", articulando a las distintas áreas para asegurar convocatoria y buena ejecución.")
bullet("Fui ", "contraparte directa de los trabajadores",
       " en la resolución de sus necesidades laborales, con autonomía para canalizar y resolver cada requerimiento.")
bullet("Participé en los procesos de ", "selección, inducción y capacitación",
       ", incorporando la mirada de bienestar desde el ingreso de cada persona.")
bullet("Asumí durante un año el ", "Sistema de Gestión Integrado como auditora interna",
       " en las certificaciones ISO 9001:2015 (Calidad), ISO 14001:2015 (Medio Ambiente) e ISO 45001:2018 "
       "(Seguridad y Salud en el Trabajo).")

job("Fundación Fondo Esperanza", None, "Octubre 2017 – Octubre 2018",
    "Monitora de Grupos de Emprendedores")
bullet("Acompañé a ", "grupos de emprendedores",
       " en el desarrollo y fortalecimiento de sus iniciativas, con orientación sostenida en el tiempo.")
bullet("Diseñé y facilité ", "capacitaciones grupales",
       " orientadas al desarrollo de habilidades para la gestión de negocios, con metodologías prácticas "
       "y participativas.")
bullet("", "Vinculé a los participantes con redes y recursos",
       " disponibles, ampliando su acceso a oportunidades de desarrollo.")

job("Ferretería San Francisco", None, "[Mes Año – Mes Año]",
    "[Cargo por confirmar] · Área de Bienestar y Recursos Humanos")
bullet("Coordiné el ", "área de bienestar", " para una dotación superior a 200 colaboradores.")
bullet("Gestioné el ", "Seguro Complementario de Salud",
       " de la dotación, apoyando a los trabajadores en el acceso y uso del beneficio.")
bullet("Administré ", "beneficios internos y convenios con instituciones externas", ".")
bullet("Organicé ", "actividades de bienestar, integración y calidad de vida", ".")
bullet("Administré vacaciones, carpetas personales y documentación laboral, y apoyé los ",
       "procesos administrativos de Recursos Humanos", ".")

# ---------------- FORMACIÓN ----------------
heading("Formación académica y especialización")
def edu(title, inst, year):
    p = doc.add_paragraph(); sp(p, before=4, after=0, keep=True)
    p.paragraph_format.tab_stops.add_tab_stop(CONTENT_W, WD_TAB_ALIGNMENT.RIGHT)
    run(p, title, size=10, bold=True)
    run(p, "\t" + year, size=9.5, bold=True, color=MID)
    q = doc.add_paragraph(); sp(q, before=0.5, after=0)
    run(q, inst, size=9.5, italic=True, color=GRAY)

edu("[Título profesional: Trabajadora Social / Asistente Social]",
    "[Universidad] · [Titulada / Licenciada en Trabajo Social]", "[Año]")
edu("Diplomado en Bienestar Organizacional y Estrategias de Diversidad e Inclusión",
    "[Institución]", "[Año]")

# ---------------- CONOCIMIENTOS ----------------
heading("Conocimientos técnicos")
bullet("", "Intervención social: ",
       "evaluación socioeconómica, elaboración de informes sociales y seguimiento de casos.")
bullet("", "Bienestar y beneficios: ",
       "administración de beneficios internos y externos, convenios institucionales y Seguro "
       "Complementario de Salud.")
bullet("", "Gestión y calidad: ",
       "auditoría interna de Sistema de Gestión Integrado — ISO 9001:2015, ISO 14001:2015 e ISO 45001:2018.")
bullet("", "Herramientas: ",
       "Microsoft Office (Word, Excel y PowerPoint) nivel intermedio; plataformas institucionales de "
       "registro y gestión documental.")

# ---------------- COMPETENCIAS ----------------
heading("Competencias")
p = doc.add_paragraph(); sp(p, after=0, line=1.1)
run(p, "Vocación y orientación al servicio  ·  Excelente trato al usuario y altos estándares de calidad de atención  ·  "
       "Comunicación efectiva  ·  Trabajo en equipo y coordinación transversal con múltiples unidades  ·  "
       "Autonomía y resolución de situaciones  ·  Flexibilidad y adaptación al cambio  ·  Manejo de conflictos  ·  "
       "Proactividad y aporte al clima laboral  ·  Rigurosidad administrativa y normativa", size=9.5)

# ---------------- COMPLEMENTARIOS ----------------
heading("Antecedentes complementarios")
bullet("", "Disponibilidad ",
       "para desempeñarse en Campus San Joaquín y en otros campus de la Región Metropolitana.")
bullet("", "Licencia de conducir y movilización propia: ", "[por confirmar].")
bullet("", "Referencias laborales: ", "disponibles a solicitud.")

out = "/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Trabajadora_Social_UC_Bienestar.docx"
doc.save(out)
print("OK ->", out)
