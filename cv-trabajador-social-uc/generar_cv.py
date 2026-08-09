# -*- coding: utf-8 -*-
from docx import Document
from docx.shared import Pt, Cm, RGBColor
from docx.enum.text import WD_TAB_ALIGNMENT
from docx.oxml.ns import qn
from docx.oxml import OxmlElement

NAVY  = RGBColor(0x1F, 0x35, 0x5E)
GRAY  = RGBColor(0x44, 0x44, 0x44)
MID   = RGBColor(0x5A, 0x5A, 0x5A)
BLACK = RGBColor(0x1A, 0x1A, 0x1A)
FONT  = "Calibri"
LINE  = 1.03

doc = Document()
s = doc.sections[0]
s.top_margin = Cm(1.15); s.bottom_margin = Cm(1.1)
s.left_margin = Cm(1.6); s.right_margin = Cm(1.6)
W = s.page_width - s.left_margin - s.right_margin

st = doc.styles["Normal"]
st.font.name = FONT; st.font.size = Pt(9.5); st.font.color.rgb = BLACK
st.element.rPr.rFonts.set(qn("w:eastAsia"), FONT)
st.paragraph_format.space_before = Pt(0)
st.paragraph_format.space_after = Pt(0)
st.paragraph_format.line_spacing = LINE

def sp(p, before=0, after=0, line=LINE, keep=False):
    p.paragraph_format.space_before = Pt(before)
    p.paragraph_format.space_after = Pt(after)
    p.paragraph_format.line_spacing = line
    if keep: p.paragraph_format.keep_with_next = True

def run(p, text, size=9.5, bold=False, italic=False, color=BLACK, spacing=None):
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

def heading(text):
    p = doc.add_paragraph(); sp(p, before=9, after=4, keep=True)
    run(p, text.upper(), size=10, bold=True, color=NAVY, spacing=30)
    rule(p)

def bullet(pre="", key="", post=""):
    p = doc.add_paragraph(style="List Bullet")
    p.paragraph_format.left_indent = Cm(0.40)
    p.paragraph_format.first_line_indent = Cm(-0.40)
    sp(p, before=0, after=1.5)
    if pre:  run(p, pre)
    if key:  run(p, key, bold=True)
    if post: run(p, post)

def job(org, period, title):
    p = doc.add_paragraph(); sp(p, before=6.5, after=0, keep=True)
    p.paragraph_format.tab_stops.add_tab_stop(W, WD_TAB_ALIGNMENT.RIGHT)
    run(p, org, size=10, bold=True, color=NAVY)
    run(p, "\t" + period, size=9, bold=True, color=MID)
    q = doc.add_paragraph(); sp(q, before=1, after=2.5, keep=True)
    run(q, title, size=9.5, italic=True, color=GRAY)

def entry(title, inst, year):
    p = doc.add_paragraph(); sp(p, before=3, after=0, keep=True)
    p.paragraph_format.tab_stops.add_tab_stop(W, WD_TAB_ALIGNMENT.RIGHT)
    run(p, title, bold=True)
    run(p, "\t" + year, size=9, bold=True, color=MID)
    q = doc.add_paragraph(); sp(q, before=0.5, after=0)
    run(q, inst, size=9, italic=True, color=GRAY)

# ---------------- ENCABEZADO ----------------
p = doc.add_paragraph(); sp(p, after=1)
run(p, "JENIFFER MIERES CONTRERAS", size=19, bold=True, color=NAVY, spacing=8)
p = doc.add_paragraph(); sp(p, before=1, after=2.5)
run(p, "Trabajadora Social  |  Bienestar Laboral, Calidad de Vida y Gestión de Beneficios",
    size=10.5, bold=True, color=GRAY)
p = doc.add_paragraph(); sp(p, before=0, after=2)
run(p, "+56 9 4903 1682  ·  jeniffer.mieres@gmail.com  ·  El Monte, Región Metropolitana", size=9, color=MID)
rule(p, color="C9CEDB", size=6, space=4)

# ---------------- PERFIL ----------------
heading("Perfil profesional")
p = doc.add_paragraph(); sp(p, after=0, line=1.05)
run(p,
 "Trabajadora Social titulada con distinción y más de 9 años de experiencia en bienestar laboral, gestión de "
 "beneficios y atención social directa, desarrollada en instituciones de educación superior y en empresa privada. "
 "Creé desde cero el Departamento de Bienestar Social de una organización con más de 200 colaboradores, "
 "administrando el Seguro Complementario de Salud, las licencias médicas y las cargas familiares de la dotación. "
 "Luego lideré la gestión de personas y bienestar en la consultora minera Bmining, a cargo de los programas de "
 "bienestar, convenios, plan anual de capacitación, clima laboral y de la matriz de riesgo y salud en el trabajo "
 "con seguimiento por KPI. Hoy me desempeño en la Universidad de Santiago de Chile, donde realizo evaluaciones "
 "socioeconómicas en los sistemas del Ministerio de Educación, gestiono beneficios ministeriales e internos y "
 "acompaño casos que requieren articular áreas internas con redes de apoyo. A la atención directa sumo una mirada "
 "de proceso: fui auditora interna del Sistema de Gestión Integrado (ISO 9001, 14001 y 45001). Cursando el "
 "Diplomado en Bienestar Organizacional y Estrategias de Diversidad e Inclusión (USACH) y certificada como agente "
 "Gatekeeper en prevención del suicidio.")

# ---------------- ÁREAS DE EXPERTISE ----------------
heading("Áreas de expertise")
p = doc.add_paragraph(); sp(p, after=0, line=1.07)
run(p, "Creación, implementación y evaluación de programas de bienestar  ·  Gestión de beneficios internos y externos  ·  "
       "Evaluación socioeconómica e informes sociales  ·  Seguro Complementario de Salud  ·  "
       "Licencias médicas y cargas familiares  ·  Convenios institucionales y gestión de proveedores  ·  "
       "Gestión y seguimiento de casos sociales  ·  Orientación en salud y vivienda  ·  "
       "Coordinación de actividades y eventos  ·  Diseño y facilitación de talleres y capacitaciones  ·  "
       "Articulación con redes de apoyo  ·  Diversidad e inclusión  ·  Calidad de servicio  ·  "
       "Normativa laboral y gestión documental", size=9)

# ---------------- EXPERIENCIA ----------------
heading("Experiencia profesional")

job("Universidad de Santiago de Chile", "Agosto 2023 – Actualidad",
    "Trabajadora Social · Departamento de Beneficios Estudiantiles")
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

job("Bmining · Desarrollo e Innovación para la Minería SpA", "Octubre 2018 – Febrero 2023",
    "Líder en Gestión de Personas · Consultora para la industria minera")
bullet("Estuve a cargo de los ", "programas de bienestar y beneficios",
       " de los colaboradores, desde la definición de la oferta hasta su implementación y seguimiento, con foco "
       "en el clima laboral y en el día a día de la empresa.")
bullet("Gestioné ", "convenios y beneficios orientados a la calidad de vida laboral",
       ", administrando la relación con instituciones y proveedores externos.")
bullet("Estuve a cargo del área de Recursos Humanos en ", "selección e inducción",
       ", y lideré el plan anual de capacitaciones internas.")
bullet("Tuve a cargo la ", "matriz de riesgo y salud en el trabajo",
       " junto al prevencionista externo, con seguimiento por KPI para dar cumplimiento a la certificación.")
bullet("Impulsé el ", "programa de cuidado del medio ambiente",
       " con actividades semanales y seguimiento, con participación de toda la organización desde la gerencia "
       "general en adelante.")
bullet("Organicé ", "actividades corporativas de bienestar e integración",
       ", articulando a las distintas áreas para asegurar convocatoria y buena ejecución.")
bullet("Asumí durante un año el ", "Sistema de Gestión Integrado como auditora interna",
       " en las certificaciones ISO 9001:2015 (Calidad), ISO 14001:2015 (Medio Ambiente) e ISO 45001:2018 "
       "(Seguridad y Salud en el Trabajo).")

job("Fundación Fondo Esperanza", "Octubre 2017 – Septiembre 2018",
    "Monitora · Grupo Emprendedores")
bullet("Diseñé y dicté ", "capacitaciones",
       " para emprendedores que estaban partiendo o buscando hacer crecer su negocio, con metodologías "
       "prácticas y grupales.")
bullet("", "Acompañé la gestión de sus proyectos",
       ", revisando con cada participante qué reforzar para alcanzar sus objetivos.")
bullet("Orienté a los emprendedores en materia de ", "vivienda",
       ", articulando con las redes de la comuna, y los vinculé con recursos disponibles que muchas veces no "
       "sabían que tenían a su alcance.")

job("Ferretería San Francisco", "Diciembre 2016 – Septiembre 2017",
    "Encargada · Departamento de Bienestar Social")
bullet("", "Creé desde cero el área de Bienestar Social",
       " para una dotación de más de 200 colaboradores, definiendo la planificación de actividades y beneficios.")
bullet("Administré el ", "Seguro Complementario de Salud",
       " de la dotación, apoyando a los trabajadores en el acceso, uso y tramitación de reembolsos.")
bullet("Gestioné ", "licencias médicas ante la Caja de Compensación Los Andes",
       " y la incorporación de cargas familiares de los trabajadores.")
bullet("Realicé ", "visitas semanales a la segunda sucursal",
       ", atendiendo en terreno las necesidades de beneficios internos y externos, principalmente en "
       "vivienda y salud.")
bullet("Gestioné ", "convenios con instituciones externas", " para mejorar la calidad de vida en el trabajo.")
bullet("Administré vacaciones, carpetas personales y documentación laboral, y apoyé los ",
       "procesos administrativos de Recursos Humanos", ".")

# ---------------- FORMACIÓN ----------------
heading("Formación académica")
entry("Diplomado en Bienestar Organizacional y Estrategias de Diversidad e Inclusión",
      "Universidad de Santiago de Chile", "En curso")
entry("Postítulo en Trabajo Social en Niñez, Adolescencia y Familia en el Contexto Judicial",
      "Universidad Andrés Bello", "2016")
entry("Trabajadora Social · Título profesional con distinción",
      "Instituto Profesional AIEP", "2012")

# ---------------- CERTIFICACIONES ----------------
heading("Cursos y certificaciones")
bullet("", "Certificación Gatekeepers", " — agentes comunitarios en prevención del suicidio (2026).")
bullet("", "Interpretación auditor interno",
       " — TÜV Rheinland (2019). Respalda la auditoría del Sistema de Gestión Integrado ISO 9001 / 14001 / 45001.")
bullet("", "Ley 21.327, Modernización de la Dirección del Trabajo", " — Bicentenario OTEC (2021).")
bullet("", "Cálculo de finiquito", " — Consultores y Asesores en Capacitación Chile Ltda. (2022).")

# ---------------- COMPETENCIAS ----------------
heading("Competencias y herramientas")
p = doc.add_paragraph(); sp(p, after=2.5, line=1.07)
run(p, "Vocación y orientación al servicio  ·  Excelente trato al usuario y altos estándares de calidad de atención  ·  "
       "Comunicación efectiva  ·  Trabajo en equipo y coordinación transversal con múltiples unidades  ·  "
       "Autonomía y resolución de situaciones  ·  Flexibilidad y adaptación al cambio  ·  Manejo de conflictos  ·  "
       "Proactividad y aporte al clima laboral  ·  Rigurosidad administrativa y normativa", size=9)
bullet("", "Herramientas: ",
       "Microsoft Office (Word, Excel y PowerPoint) nivel intermedio; conocimiento básico de BUK, SAP y Talana; "
       "plataformas institucionales del Ministerio de Educación y gestión documental.")
bullet("", "Disponibilidad: ",
       "movilización propia y licencia clase B; disponibilidad para desempeñarse en Campus San Joaquín y en "
       "otros campus de la Región Metropolitana.")

out = "/home/user/trabajos-varios/cv-trabajador-social-uc/CV_Jeniffer_Mieres_Contreras_Trabajadora_Social_UC.docx"
doc.save(out)
print("OK ->", out)
