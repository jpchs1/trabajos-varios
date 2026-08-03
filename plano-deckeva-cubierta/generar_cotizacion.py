#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Genera la cotización formal (2 páginas A4) asociada al plano DK-CUB-01.

Neto $1.761.000 + IVA 19% (Chile, CLP).
"""

import io
import math
import os

from PIL import ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))

# ---------------------------------------------------------------- Datos -----
NUM_COT = "DK-COT-2026-001"
FECHA = "03 de agosto de 2026"
VALIDEZ = 30                      # días corridos
PLANO_REF = "DK-CUB-01, rev. 00"
NETO = 1_761_000
TASA_IVA = 0.19
IVA = round(NETO * TASA_IVA)
TOTAL = NETO + IVA
AREA_TOTAL = 9.440                # m² netos según plano
UNIT_M2 = NETO / AREA_TOTAL

# ---------------------------------------------------------------- Estilo ----
W, H = 210.0, 297.0
M = 15.0
CW = W - 2 * M

NAVY = "#16264B"
TEAL = "#189BB0"
INK = "#22303F"
GREY = "#7A8896"
LINE = "#C2CBD5"
SOFT = "#F2F6FA"
BAND = "#E4EBF2"
PAPER = "#FFFFFF"

FONT = "Liberation Sans, DejaVu Sans, Arial, sans-serif"
FONT_BOLD_TTF = "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"


# ------------------------------------------------------------- Utilidades ---
def clp(n):
    return "$" + f"{int(round(n)):,}".replace(",", ".")


UNI = ["", "uno", "dos", "tres", "cuatro", "cinco", "seis", "siete", "ocho",
       "nueve", "diez", "once", "doce", "trece", "catorce", "quince",
       "dieciséis", "diecisiete", "dieciocho", "diecinueve"]
DEC = ["", "", "veinte", "treinta", "cuarenta", "cincuenta", "sesenta",
       "setenta", "ochenta", "noventa"]
CEN = ["", "ciento", "doscientos", "trescientos", "cuatrocientos",
       "quinientos", "seiscientos", "setecientos", "ochocientos",
       "novecientos"]


def _centenas(n):
    """0 <= n < 1000 en palabras."""
    if n == 0:
        return ""
    if n == 100:
        return "cien"
    c, r = divmod(n, 100)
    out = CEN[c]
    if r:
        if r < 20:
            t = UNI[r]
        elif r < 30:
            t = "veinti" + UNI[r - 20]
        else:
            d, u = divmod(r, 10)
            t = DEC[d] + (" y " + UNI[u] if u else "")
        out = (out + " " + t).strip()
    return out


def en_palabras(n):
    """Entero a palabras en español (hasta millones)."""
    if n == 0:
        return "cero"
    partes = []
    millones, resto = divmod(n, 1_000_000)
    if millones:
        partes.append("un millón" if millones == 1
                      else _centenas(millones) + " millones")
    miles, uni = divmod(resto, 1000)
    if miles:
        partes.append("mil" if miles == 1 else _centenas(miles) + " mil")
    if uni:
        partes.append(_centenas(uni))
    return " ".join(partes)


# ------------------------------------------------------------ Primitivas ----
def esc(t):
    return t.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")


class Page:
    def __init__(self):
        self.o = []

    def add(self, s):
        self.o.append(s)

    def txt(self, x, y, s, size=3.2, fill=INK, anchor="start", weight="normal",
            ls=0.0, opacity=1.0):
        lsp = f' letter-spacing="{ls}"' if ls else ""
        op = f' opacity="{opacity}"' if opacity != 1.0 else ""
        self.add(f'<text x="{x:.2f}" y="{y:.2f}" font-family="{FONT}" '
                 f'font-size="{size}" font-weight="{weight}" fill="{fill}" '
                 f'text-anchor="{anchor}"{lsp}{op}>{esc(s)}</text>')

    def line(self, x1, y1, x2, y2, stroke=LINE, w=0.25, dash=None):
        d = f' stroke-dasharray="{dash}"' if dash else ""
        self.add(f'<line x1="{x1:.2f}" y1="{y1:.2f}" x2="{x2:.2f}" y2="{y2:.2f}" '
                 f'stroke="{stroke}" stroke-width="{w}"{d}/>')

    def rect(self, x, y, w, h, fill="none", stroke="none", sw=0.3, rx=0):
        self.add(f'<rect x="{x:.2f}" y="{y:.2f}" width="{w:.2f}" height="{h:.2f}" '
                 f'rx="{rx}" fill="{fill}" stroke="{stroke}" stroke-width="{sw}"/>')

    def poly(self, pts, fill, stroke="none", sw=0):
        p = " ".join(f"{a:.2f},{b:.2f}" for a, b in pts)
        self.add(f'<polygon points="{p}" fill="{fill}" stroke="{stroke}" '
                 f'stroke-width="{sw}"/>')

    def svg(self):
        head = (f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}mm" '
                f'height="{H}mm" viewBox="0 0 {W} {H}">'
                f'<rect width="{W}" height="{H}" fill="{PAPER}"/>')
        return head + "".join(self.o) + "</svg>"


_fc = {}


def _measure(text, px):
    k = round(px, 2)
    if k not in _fc:
        _fc[k] = ImageFont.truetype(FONT_BOLD_TTF, int(px * 8))
    return _fc[k].getlength(text) / 8.0


def logo(p, x, y, h):
    """Logotipo Deckeva. (x,y) esquina sup. izq., h alto de caja."""
    fs = h * 0.98
    base = y + h * 0.80
    w_dec, w_k = _measure("Dec", fs), _measure("k", fs)
    kx = x + w_dec
    x0, y0 = kx + w_k * 0.30, base - fs * 0.34
    x1, y1 = kx + w_k * 1.00, base
    tw = fs * 0.145
    p.poly([(x0, y0), (x0 + tw * 1.15, y0 - tw * 0.30),
            (x1 + tw * 0.10, y1), (x1 - tw * 1.05, y1)], TEAL)
    p.add(f'<text x="{x:.2f}" y="{base:.2f}" font-family="{FONT}" '
          f'font-size="{fs:.2f}" font-weight="bold" fill="{NAVY}" '
          f'letter-spacing="{-fs*0.012:.3f}">Deckeva</text>')
    p.line(x + fs * 0.02, base + fs * 0.085, x + w_dec * 0.72,
           base + fs * 0.085, NAVY, fs * 0.028)
    p.line(x + fs * 0.02, base + fs * 0.155, x + w_dec * 0.72,
           base + fs * 0.155, NAVY, fs * 0.028)


def parrafo(p, x, y, ancho, texto, size=3.1, lh=4.3, fill=INK, weight="normal"):
    """Texto justificado a la izquierda con salto automático."""
    palabras, linea, yy = texto.split(), "", y
    for w in palabras:
        prueba = (linea + " " + w).strip()
        if _measure(prueba, size) * 0.94 > ancho and linea:
            p.txt(x, yy, linea, size, fill, weight=weight)
            yy += lh
            linea = w
        else:
            linea = prueba
    if linea:
        p.txt(x, yy, linea, size, fill, weight=weight)
        yy += lh
    return yy


def pie(p, n, total):
    p.line(M, H - 16, W - M, H - 16, LINE, 0.3)
    p.txt(M, H - 12, f"Cotización {NUM_COT}  ·  {FECHA}", 2.6, GREY)
    p.txt(W - M, H - 12, f"Página {n} de {total}", 2.6, GREY, "end")
    p.txt(W / 2, H - 12, "Deckeva", 2.6, NAVY, "middle", "bold")


def cabecera(p, titulo, sub=None):
    logo(p, M, M, 12.5)
    p.txt(W - M, M + 5.5, titulo, 9.2, NAVY, "end", "bold", ls=0.5)
    if sub:
        p.txt(W - M, M + 11.0, sub, 3.2, GREY, "end")
    p.line(M, M + 16.5, W - M, M + 16.5, NAVY, 0.7)


# ============================================================================
# PÁGINA 1
# ============================================================================
p1 = Page()
cabecera(p1, "COTIZACIÓN", f"N° {NUM_COT}   ·   {FECHA}")

y = M + 24

# ------------------------------------------------- Emisor / Cliente ---------
bw = (CW - 6) / 2
for i, (tit, filas) in enumerate([
    ("EMISOR", [("Razón social", "Deckeva"),
                ("RUT", "—"),
                ("Dirección", "—"),
                ("Contacto", "—"),
                ("Correo / teléfono", "—")]),
    ("CLIENTE", [("Razón social", "—"),
                 ("RUT", "—"),
                 ("Embarcación", "—"),
                 ("Contacto", "—"),
                 ("Correo / teléfono", "—")]),
]):
    bx = M + i * (bw + 6)
    bh = 8 + len(filas) * 5.2 + 2
    p1.rect(bx, y, bw, bh, PAPER, NAVY, 0.5)
    p1.rect(bx, y, bw, 6.4, NAVY)
    p1.txt(bx + 3, y + 4.5, tit, 3.2, "#FFFFFF", weight="bold", ls=0.4)
    fy = y + 11.5
    for k, v in filas:
        p1.txt(bx + 3, fy, k, 2.7, GREY)
        p1.txt(bx + 30, fy, v, 3.0, INK, weight="bold")
        fy += 5.2
    bh_final = bh
y += bh_final + 7

# ------------------------------------------------------------ Referencia ---
p1.rect(M, y, CW, 13, SOFT, NAVY, 0.4)
p1.txt(M + 3, y + 5.2, "REFERENCIA", 2.7, GREY, weight="bold", ls=0.4)
p1.txt(M + 3, y + 10.2,
       f"Plano de despiece {PLANO_REF}  ·  9 piezas  ·  "
       f"{f'{AREA_TOTAL:.3f}'.replace('.', ',')} m² netos de superficie",
       3.2, NAVY, weight="bold")
y += 19

# --------------------------------------------------------- Descripción -----
p1.txt(M, y, "DESCRIPCIÓN DE LOS TRABAJOS", 3.6, NAVY, weight="bold", ls=0.4)
y += 5.5
y = parrafo(p1, M, y, CW,
            "Fabricación, suministro e instalación del revestimiento de cubierta de la "
            "embarcación, compuesto por nueve (9) piezas ejecutadas a medida según el plano "
            f"de despiece {PLANO_REF} adjunto a esta cotización: pasillo central, cubierta de "
            "la zona de escotillas, cubierta de proa, cuatro paneles laterales y dos escalones. "
            "Incluye plantillado en obra de las piezas de contorno irregular, corte, "
            "terminación de cantos, materiales, fijación y limpieza final de la zona de trabajo.")
y += 4

# ---------------------------------------------------------- Partidas -------
p1.txt(M, y, "DETALLE DE LA OFERTA", 3.6, NAVY, weight="bold", ls=0.4)
y += 4
cols = [0, 12, 100, 112, 126, 153, CW]   # ítem, desc., un., cant., unit., total
rh = 7.2
p1.rect(M, y, CW, rh, NAVY)
for i, h in enumerate(["ÍTEM", "DESCRIPCIÓN", "UN.", "CANT.", "P. UNITARIO", "TOTAL"]):
    if i == 1:
        p1.txt(M + cols[1] + 2.5, y + 4.8, h, 2.8, "#FFFFFF", weight="bold")
    elif i in (0, 2, 3):
        p1.txt(M + (cols[i] + cols[i + 1]) / 2, y + 4.8, h, 2.8, "#FFFFFF",
               "middle", "bold")
    else:
        p1.txt(M + cols[i + 1] - 2.5, y + 4.8, h, 2.8, "#FFFFFF", "end", "bold")
y += rh

fila_h = 15.0
p1.rect(M, y, CW, fila_h, PAPER, LINE, 0.25)
for c in (1, 2, 3, 4, 5):
    p1.line(M + cols[c], y, M + cols[c], y + fila_h, LINE, 0.25)
p1.txt(M + cols[1] / 2, y + 6.0, "1", 3.1, NAVY, "middle", "bold")
p1.txt(M + cols[1] + 2.5, y + 5.4,
       "Revestimiento de cubierta a medida, 9 piezas", 3.1, INK, weight="bold")
p1.txt(M + cols[1] + 2.5, y + 9.8,
       f"Según plano {PLANO_REF}. Suministro e instalación.", 2.8, GREY)
p1.txt(M + cols[1] + 2.5, y + 13.4,
       f"Superficie neta {f'{AREA_TOTAL:.3f}'.replace('.', ',')} m² "
       f"(valor referencial {clp(UNIT_M2)}/m²).", 2.8, GREY)
p1.txt(M + (cols[2] + cols[3]) / 2, y + 6.0, "GL", 3.0, INK, "middle")
p1.txt(M + (cols[3] + cols[4]) / 2, y + 6.0, "1", 3.0, INK, "middle")
p1.txt(M + cols[5] - 2.5, y + 6.0, clp(NETO), 3.1, INK, "end")
p1.txt(M + cols[6] - 2.5, y + 6.0, clp(NETO), 3.1, INK, "end", "bold")
y += fila_h + 3

# ------------------------------------------------------------- Totales -----
tw_ = 78.0
tx = W - M - tw_
for etq, val, fuerte in [("Neto", NETO, False),
                         (f"IVA {int(TASA_IVA*100)} %", IVA, False),
                         ("TOTAL", TOTAL, True)]:
    hh = 8.5 if fuerte else 6.6
    p1.rect(tx, y, tw_, hh, NAVY if fuerte else SOFT,
            NAVY if fuerte else LINE, 0.35)
    p1.txt(tx + 3, y + (5.9 if fuerte else 4.6), etq,
           3.5 if fuerte else 3.1, "#FFFFFF" if fuerte else GREY,
           weight="bold")
    p1.txt(tx + tw_ - 3, y + (5.9 if fuerte else 4.6), clp(val),
           4.2 if fuerte else 3.3, "#FFFFFF" if fuerte else INK, "end", "bold")
    y += hh + 1.2
y += 2.5

palabras = en_palabras(TOTAL)
p1.txt(M, y, "Son:", 2.9, GREY)
parrafo(p1, M + 9, y, CW - 9,
        palabras[0].upper() + palabras[1:] + " pesos chilenos, IVA incluido.",
        3.1, 4.2, INK, "bold")
y += 9

# ------------------------------------------------ Condiciones comerciales --
p1.txt(M, y, "CONDICIONES COMERCIALES", 3.6, NAVY, weight="bold", ls=0.4)
y += 5.5
cond = [
    ("Moneda", "Pesos chilenos (CLP). Valores expresados netos; el IVA se indica por separado."),
    ("Validez de la oferta", f"{VALIDEZ} días corridos contados desde la fecha de emisión."),
    ("Forma de pago", "50 % a la aceptación de la cotización y 50 % contra entrega conforme."),
    ("Plazo de ejecución", "A convenir con el cliente, contado desde la aceptación y "
                           "la confirmación de medidas en obra."),
    ("Garantía", "12 meses sobre fabricación e instalación, en condiciones normales de uso."),
    ("Medidas", "Los valores del plano provienen de un levantamiento en obra y deben "
                "verificarse antes del corte (ver tabla de verificación del plano). "
                "Diferencias relevantes pueden modificar el valor cotizado."),
    ("Alcance", "Cualquier trabajo no descrito expresamente en esta cotización se "
                "considera excluido y se cotizará por separado."),
]
for k, v in cond:
    p1.txt(M, y, k, 3.0, NAVY, weight="bold")
    ny = parrafo(p1, M + 38, y, CW - 38, v, 3.0, 4.1)
    y = max(y + 4.6, ny + 0.6)

pie(p1, 1, 2)

# ============================================================================
# PÁGINA 2
# ============================================================================
p2 = Page()
cabecera(p2, "COTIZACIÓN", f"N° {NUM_COT}   ·   Detalle y condiciones")
y = M + 24

# ----------------------------------------------------- Detalle de piezas ---
p2.txt(M, y, f"DETALLE DE PIEZAS SEGÚN PLANO {PLANO_REF}", 3.6, NAVY,
       weight="bold", ls=0.4)
y += 4
PIEZAS = [
    ("P-01", "Pasillo central de cubierta", "78 × 283", 1, 2.207),
    ("P-02", "Cubierta zona escotillas (acceso escalera)", "166/94 × 185", 1, 2.646),
    ("P-03", "Cubierta de proa — trapecio en V", "60/166 × 172", 1, 1.944),
    ("P-04", "Panel lateral A", "45 × 103", 1, 0.464),
    ("P-05", "Panel lateral B", "70 × 103", 1, 0.721),
    ("P-06", "Panel lateral C", "70 × 103", 1, 0.721),
    ("P-07", "Panel lateral D", "40 × 103", 1, 0.412),
    ("P-08", "Escalón superior", "58 × 28", 1, 0.162),
    ("P-09", "Escalón inferior", "58 × 28", 1, 0.162),
]
pc = [0, 16, 104, 140, 158, CW]
rh = 7.0
p2.rect(M, y, CW, rh, NAVY)
for i, h in enumerate(["REF.", "DESCRIPCIÓN", "MEDIDAS (cm)", "CANT.", "ÁREA (m²)"]):
    if i == 1:
        p2.txt(M + pc[1] + 2.5, y + 4.7, h, 2.8, "#FFFFFF", weight="bold")
    elif i == 4:
        p2.txt(M + pc[5] - 2.5, y + 4.7, h, 2.8, "#FFFFFF", "end", "bold")
    else:
        p2.txt(M + (pc[i] + pc[i + 1]) / 2, y + 4.7, h, 2.8, "#FFFFFF",
               "middle", "bold")
y += rh
for n, (ref, nom, med, cant, area) in enumerate(PIEZAS):
    p2.rect(M, y, CW, rh, SOFT if n % 2 else PAPER, LINE, 0.2)
    p2.txt(M + pc[1] / 2, y + 4.7, ref, 3.0, NAVY, "middle", "bold")
    p2.txt(M + pc[1] + 2.5, y + 4.7, nom, 3.0, INK)
    p2.txt(M + (pc[2] + pc[3]) / 2, y + 4.7, med, 3.0, INK, "middle", "bold")
    p2.txt(M + (pc[3] + pc[4]) / 2, y + 4.7, str(cant), 3.0, INK, "middle")
    p2.txt(M + pc[5] - 2.5, y + 4.7, f"{area:.3f}".replace(".", ","), 3.0, INK, "end")
    y += rh
p2.rect(M, y, CW, rh + 1, BAND, NAVY, 0.45)
p2.txt(M + pc[1] + 2.5, y + 5.2, "TOTAL", 3.2, NAVY, weight="bold")
p2.txt(M + (pc[3] + pc[4]) / 2, y + 5.2, "9", 3.1, NAVY, "middle", "bold")
p2.txt(M + pc[5] - 2.5, y + 5.2, f"{AREA_TOTAL:.3f}".replace(".", ","), 3.2,
       NAVY, "end", "bold")
y += rh + 5
p2.txt(M, y, "Superficie neta, sin solapes ni despunte. La merma de corte está "
       "considerada dentro del valor cotizado.", 2.8, GREY)
y += 9

# ------------------------------------------------------ Incluye / Excluye --
inc = ["Plantillado en obra de las piezas de contorno irregular (P-02 y P-03).",
       "Corte, mecanizado y terminación de cantos de las 9 piezas.",
       "Materiales de fabricación y elementos de fijación.",
       "Instalación a bordo y ajuste final de las piezas.",
       "Limpieza y retiro de residuos propios del trabajo.",
       "Traslados del equipo al lugar de la embarcación."]
exc = ["Retiro y desecho del revestimiento existente, salvo acuerdo previo.",
       "Reparación, nivelación o refuerzo estructural del sustrato.",
       "Trabajos de fibra, pintura, gelcoat o carpintería no descritos.",
       "Modificación de escotillas, herrajes, imbornales o pasacables.",
       "Piezas descartadas en el levantamiento (zonas marcadas en amarillo).",
       "Gastos de varadero, amarre, energía o agua en el lugar de trabajo."]
bw2 = (CW - 6) / 2
y0 = y
for i, (tit, items, col) in enumerate([("INCLUYE", inc, TEAL),
                                       ("NO INCLUYE", exc, GREY)]):
    bx = M + i * (bw2 + 6)
    yy = y0
    p2.rect(bx, yy, bw2, 6.4, NAVY)
    p2.txt(bx + 3, yy + 4.5, tit, 3.0, "#FFFFFF", weight="bold", ls=0.4)
    yy += 10.5
    for it in items:
        p2.rect(bx + 2.6, yy - 1.9, 1.6, 1.6, col)
        yy = parrafo(p2, bx + 6.5, yy, bw2 - 9.5, it, 2.9, 3.9) + 1.4
    p2.rect(bx, y0, bw2, yy - y0 - 1.4 + 3, "none", NAVY, 0.4)
    y = max(y, yy + 3)
y += 6

# ------------------------------------------------------------ Observaciones-
p2.txt(M, y, "OBSERVACIONES TÉCNICAS", 3.6, NAVY, weight="bold", ls=0.4)
y += 5.2
obs = [
    "Las cotas provienen del levantamiento fotográfico realizado en obra. En P-01 y en los "
    "paneles P-04 a P-09 el contorno se interpretó como rectangular, ya que solo se registraron "
    "dos medidas por pieza.",
    "En P-02 el alto total de 185 cm se descompone en 120 + 12 (chaflán) + 53 cm, y el "
    "retranqueo lateral registrado como 35 cm se resuelve con 36 cm por lado para cerrar los "
    "94 cm de la base.",
    "En P-03 la altura de 172 cm es calculada a partir de las bases de 60 y 166 cm con lados "
    "de 180 cm, no fue medida directamente.",
    "Se recomienda confirmar estas tres interpretaciones antes de la aceptación. Si alguna "
    "difiere, se emitirá una cotización rectificada sin costo.",
]
for i, o in enumerate(obs):
    p2.txt(M + 1, y, f"{i+1}.", 2.9, NAVY, weight="bold")
    y = parrafo(p2, M + 7, y, CW - 7, o, 2.9, 3.9) + 1.6
y += 6

# --------------------------------------------------------------- Aceptación-
ah = 34.0
ay = min(y, H - 22 - ah)
p2.rect(M, ay, CW, ah, SOFT, NAVY, 0.5)
p2.txt(M + 4, ay + 6.5, "ACEPTACIÓN DE LA COTIZACIÓN", 3.4, NAVY,
       weight="bold", ls=0.4)
p2.txt(M + 4, ay + 11.5,
       f"La firma de este documento constituye aceptación del alcance, el valor de "
       f"{clp(TOTAL)} IVA incluido y las condiciones aquí descritas.", 2.9, INK)
for i, (rot, sub) in enumerate([("Cliente", "Nombre, RUT y firma"),
                                ("Deckeva", "Nombre y firma")]):
    sx = M + 8 + i * (CW / 2)
    p2.line(sx, ay + 25, sx + CW / 2 - 20, ay + 25, NAVY, 0.4)
    p2.txt(sx, ay + 29.5, rot, 3.0, NAVY, weight="bold")
    p2.txt(sx + CW / 2 - 20, ay + 29.5, sub, 2.6, GREY, "end")

pie(p2, 2, 2)

# ============================================================================
# Exportación
# ============================================================================
import cairosvg          # noqa: E402
from pypdf import PdfWriter, PdfReader   # noqa: E402

paths = []
for i, pg in enumerate((p1, p2), 1):
    sp = os.path.join(HERE, f"_cotizacion_p{i}.svg")
    with open(sp, "w", encoding="utf-8") as f:
        f.write(pg.svg())
    paths.append(sp)

wr = PdfWriter()
for sp in paths:
    buf = io.BytesIO()
    cairosvg.svg2pdf(url=sp, write_to=buf)
    wr.append(PdfReader(io.BytesIO(buf.getvalue())))
pdf_out = os.path.join(HERE, f"cotizacion-{NUM_COT}.pdf")
with open(pdf_out, "wb") as f:
    wr.write(f)

cairosvg.svg2png(url=paths[0],
                 write_to=os.path.join(HERE, f"cotizacion-{NUM_COT}-p1.png"),
                 output_width=1600)
cairosvg.svg2png(url=paths[1],
                 write_to=os.path.join(HERE, f"cotizacion-{NUM_COT}-p2.png"),
                 output_width=1600)
for sp in paths:
    os.remove(sp)

print(f"Neto {clp(NETO)} | IVA {int(TASA_IVA*100)}% {clp(IVA)} | Total {clp(TOTAL)}")
print("Son:", en_palabras(TOTAL), "pesos")
print("PDF:", pdf_out)
