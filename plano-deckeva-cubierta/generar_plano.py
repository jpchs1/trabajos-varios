#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Genera el plano de despiece de paneles de cubierta (Deckeva) en SVG,
y lo exporta a PDF y PNG.

Formato de hoja : A1 apaisado (841 x 594 mm)
Escala          : 1:10  -> 1 cm real = 1 mm de papel
Unidades        : centimetros
"""

import base64
import io
import math
import os

from PIL import Image, ImageFont

HERE = os.path.dirname(os.path.abspath(__file__))

# ----------------------------------------------------------------------------
# Hoja y estilo
# ----------------------------------------------------------------------------
SHEET_W, SHEET_H = 841.0, 594.0          # A1 apaisado, en mm
ESCALA = 10.0                            # 1:10  -> cm -> mm es 1:1

NAVY = "#16264B"
TEAL = "#189BB0"
INK = "#22303F"
GREY = "#7A8896"
LINE = "#9AA7B4"
FILL = "#EFF3F7"
FILL2 = "#E4EBF2"
DIMC = "#B4472C"          # cotas
PAPER = "#FFFFFF"

FONT = "Liberation Sans, DejaVu Sans, Arial, sans-serif"
FONT_BOLD_TTF = "/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf"

out = []


def add(s):
    out.append(s)


def esc(t):
    return (t.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;"))


def num(v, dec=3):
    """Formato numerico espanol (coma decimal)."""
    return f"{v:.{dec}f}".replace(".", ",")


def foto_box(path, bx, by, bw, bh, maxpx=760):
    """Inserta una foto ajustada dentro de la caja (bx,by,bw,bh) manteniendo
    proporcion, incrustada en base64."""
    im = Image.open(path).convert("RGB")
    im.thumbnail((maxpx, maxpx), Image.LANCZOS)
    buf = io.BytesIO()
    im.save(buf, "JPEG", quality=72, optimize=True)
    b64 = base64.b64encode(buf.getvalue()).decode("ascii")
    ar = im.width / im.height
    w, h = bw, bw / ar
    if h > bh:
        h, w = bh, bh * ar
    x, y = bx + (bw - w) / 2, by + (bh - h) / 2
    add(f'<image x="{x:.2f}" y="{y:.2f}" width="{w:.2f}" height="{h:.2f}" '
        f'preserveAspectRatio="none" xlink:href="data:image/jpeg;base64,{b64}" '
        f'xmlns:xlink="http://www.w3.org/1999/xlink"/>')
    add(f'<rect x="{x:.2f}" y="{y:.2f}" width="{w:.2f}" height="{h:.2f}" '
        f'fill="none" stroke="{NAVY}" stroke-width="0.35"/>')


def txt(x, y, s, size=3.4, fill=INK, anchor="start", weight="normal",
        rotate=None, ls=0.0, family=FONT, opacity=1.0):
    tr = f' transform="rotate({rotate} {x:.2f} {y:.2f})"' if rotate else ""
    lsp = f' letter-spacing="{ls}"' if ls else ""
    op = f' opacity="{opacity}"' if opacity != 1.0 else ""
    add(f'<text x="{x:.2f}" y="{y:.2f}" font-family="{family}" font-size="{size}" '
        f'font-weight="{weight}" fill="{fill}" text-anchor="{anchor}"{lsp}{tr}{op}>{esc(s)}</text>')


def line(x1, y1, x2, y2, stroke=LINE, w=0.25, dash=None, cap="butt", opacity=1.0):
    d = f' stroke-dasharray="{dash}"' if dash else ""
    add(f'<line x1="{x1:.2f}" y1="{y1:.2f}" x2="{x2:.2f}" y2="{y2:.2f}" '
        f'stroke="{stroke}" stroke-width="{w}" stroke-linecap="{cap}"{d} opacity="{opacity}"/>')


def rect(x, y, w, h, fill="none", stroke=NAVY, sw=0.3, rx=0):
    add(f'<rect x="{x:.2f}" y="{y:.2f}" width="{w:.2f}" height="{h:.2f}" rx="{rx}" '
        f'fill="{fill}" stroke="{stroke}" stroke-width="{sw}"/>')


def poly(pts, fill="none", stroke=NAVY, sw=0.6, extra=""):
    p = " ".join(f"{x:.2f},{y:.2f}" for x, y in pts)
    add(f'<polygon points="{p}" fill="{fill}" stroke="{stroke}" stroke-width="{sw}" '
        f'stroke-linejoin="round"{extra}/>')


def arrow(x, y, ang, size=2.0, fill=DIMC):
    """Punta de flecha en (x,y) apuntando segun ang (grados)."""
    a = math.radians(ang)
    bx, by = x - size * math.cos(a), y - size * math.sin(a)
    px, py = -math.sin(a) * size * 0.30, math.cos(a) * size * 0.30
    pts = [(x, y), (bx + px, by + py), (bx - px, by - py)]
    add('<polygon points="' + " ".join(f"{a1:.2f},{b1:.2f}" for a1, b1 in pts) +
        f'" fill="{fill}"/>')


# ----------------------------------------------------------------------------
# Cotas
# ----------------------------------------------------------------------------
def dim_h(x1, x2, y_dim, y_geom, label, size=3.4, above=True):
    """Cota horizontal entre x1 y x2, linea de cota en y_dim."""
    for xx in (x1, x2):
        line(xx, y_geom, xx, y_dim + (1.6 if y_dim < y_geom else -1.6), DIMC, 0.18)
    line(x1, y_dim, x2, y_dim, DIMC, 0.25)
    arrow(x1, y_dim, 180)
    arrow(x2, y_dim, 0)
    ty = y_dim - 1.5 if above else y_dim + 3.4
    add(f'<rect x="{(x1+x2)/2 - len(label)*size*0.33:.2f}" y="{ty - size*0.82:.2f}" '
        f'width="{len(label)*size*0.66:.2f}" height="{size*1.05:.2f}" fill="{PAPER}"/>')
    txt((x1 + x2) / 2, ty, label, size, DIMC, "middle", "bold")


def dim_v(y1, y2, x_dim, x_geom, label, size=3.4, right=True):
    """Cota vertical entre y1 e y2, linea de cota en x_dim."""
    for yy in (y1, y2):
        line(x_geom, yy, x_dim + (1.6 if x_dim < x_geom else -1.6), yy, DIMC, 0.18)
    line(x_dim, y1, x_dim, y2, DIMC, 0.25)
    arrow(x_dim, y1, -90)
    arrow(x_dim, y2, 90)
    tx = x_dim + (3.3 if right else -1.6)
    ym = (y1 + y2) / 2
    add(f'<rect x="{tx - size*0.85:.2f}" y="{ym - len(label)*size*0.33:.2f}" '
        f'width="{size*1.05:.2f}" height="{len(label)*size*0.66:.2f}" fill="{PAPER}"/>')
    txt(tx, ym, label, size, DIMC, "middle", "bold", rotate=-90)


def dim_incl(p1, p2, label, off=4.2, size=3.2):
    """Cota sobre un lado inclinado: linea paralela desplazada + texto girado."""
    (x1, y1), (x2, y2) = p1, p2
    ang = math.degrees(math.atan2(y2 - y1, x2 - x1))
    nx, ny = math.sin(math.radians(ang)), -math.cos(math.radians(ang))
    ax1, ay1 = x1 + nx * off, y1 + ny * off
    ax2, ay2 = x2 + nx * off, y2 + ny * off
    line(x1, y1, ax1, ay1, DIMC, 0.18)
    line(x2, y2, ax2, ay2, DIMC, 0.18)
    line(ax1, ay1, ax2, ay2, DIMC, 0.25)
    arrow(ax1, ay1, ang + 180)
    arrow(ax2, ay2, ang)
    mx, my = (ax1 + ax2) / 2, (ay1 + ay2) / 2
    ta = ang if -90 <= ang <= 90 else ang + 180
    add(f'<g transform="rotate({ta:.2f} {mx:.2f} {my:.2f})">'
        f'<rect x="{mx - len(label)*size*0.33:.2f}" y="{my - size*0.85:.2f}" '
        f'width="{len(label)*size*0.66:.2f}" height="{size*1.05:.2f}" fill="{PAPER}"/></g>')
    txt(mx, my - 0.1, label, size, DIMC, "middle", "bold", rotate=f"{ta:.2f}")


# ----------------------------------------------------------------------------
# Logo Deckeva (reconstruccion vectorial del logotipo)
# ----------------------------------------------------------------------------
_f_cache = {}


def _measure(text, px):
    key = round(px, 2)
    if key not in _f_cache:
        _f_cache[key] = ImageFont.truetype(FONT_BOLD_TTF, int(px * 8))
    f = _f_cache[key]
    return f.getlength(text) / 8.0


def logo(x, y, h, mono=False):
    """Logotipo 'Deckeva'. (x,y) = esquina sup. izq.; h = alto de caja."""
    fs = h * 0.98
    base = y + h * 0.80
    navy = NAVY if not mono else NAVY
    teal = TEAL if not mono else TEAL
    w_dec = _measure("Dec", fs)
    w_k = _measure("k", fs)
    kx = x + w_dec
    # cuna turquesa sobre la pata inferior de la "k"
    x0 = kx + w_k * 0.30
    y0 = base - fs * 0.34
    x1 = kx + w_k * 1.00
    y1 = base
    tw = fs * 0.145
    poly([(x0, y0), (x0 + tw * 1.15, y0 - tw * 0.30),
          (x1 + tw * 0.10, y1), (x1 - tw * 1.05, y1)],
         fill=teal, stroke="none", sw=0)
    add(f'<text x="{x:.2f}" y="{base:.2f}" font-family="{FONT}" font-size="{fs:.2f}" '
        f'font-weight="bold" fill="{navy}" letter-spacing="{-fs*0.012:.3f}">Deckeva</text>')
    # doble filete bajo la "D"
    line(x + fs * 0.02, base + fs * 0.085, x + w_dec * 0.72, base + fs * 0.085, navy, fs * 0.028)
    line(x + fs * 0.02, base + fs * 0.155, x + w_dec * 0.72, base + fs * 0.155, navy, fs * 0.028)
    return _measure("Deckeva", fs)


# ============================================================================
# PIEZAS  (medidas reales en cm)
# ============================================================================
PIEZAS = [
    dict(ref="P-01", nom="Pasillo central de cubierta",
         med="78 x 283", cant=1, area=0.7800 * 2.8300, foto="Foto 1"),
    dict(ref="P-02", nom="Cubierta zona escotillas (acceso escalera)",
         med="166/94 x 185", cant=1, area=2.6462, foto="Foto 2"),
    dict(ref="P-03", nom="Cubierta de proa - trapecio en V",
         med="60/166 x 172", cant=1, area=1.9436, foto="Foto 3"),
    dict(ref="P-04", nom="Panel lateral A - banda babor",
         med="45 x 103", cant=1, area=0.45 * 1.03, foto="Foto 4"),
    dict(ref="P-05", nom="Panel lateral B - banda babor",
         med="70 x 103", cant=1, area=0.70 * 1.03, foto="Foto 4"),
    dict(ref="P-06", nom="Panel lateral C - banda estribor",
         med="70 x 103", cant=1, area=0.70 * 1.03, foto="Foto 5"),
    dict(ref="P-07", nom="Panel lateral D - banda estribor",
         med="40 x 103", cant=1, area=0.40 * 1.03, foto="Foto 5"),
    dict(ref="P-08", nom="Escalon superior",
         med="58 x 28", cant=1, area=0.58 * 0.28, foto="Foto 6"),
    dict(ref="P-09", nom="Escalon inferior",
         med="58 x 28", cant=1, area=0.58 * 0.28, foto="Foto 6"),
]
AREA_TOTAL = sum(p["area"] * p["cant"] for p in PIEZAS)

# ============================================================================
# CABECERA SVG
# ============================================================================
add(f'<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '
    f'width="{SHEET_W}mm" height="{SHEET_H}mm" viewBox="0 0 {SHEET_W} {SHEET_H}">')
add('<defs>')
add(f'<pattern id="slatH" width="6" height="5.5" patternUnits="userSpaceOnUse">'
    f'<rect width="6" height="5.5" fill="{FILL}"/>'
    f'<line x1="0" y1="5.5" x2="6" y2="5.5" stroke="{LINE}" stroke-width="0.22" opacity="1"/></pattern>')
add(f'<pattern id="slatV" width="5.5" height="6" patternUnits="userSpaceOnUse">'
    f'<rect width="5.5" height="6" fill="{FILL}"/>'
    f'<line x1="5.5" y1="0" x2="5.5" y2="6" stroke="{LINE}" stroke-width="0.22" opacity="1"/></pattern>')
add('</defs>')
add(f'<rect width="{SHEET_W}" height="{SHEET_H}" fill="{PAPER}"/>')

# Marco
rect(8, 8, SHEET_W - 16, SHEET_H - 16, "none", NAVY, 0.9)
rect(11, 11, SHEET_W - 22, SHEET_H - 22, "none", NAVY, 0.25)

# ---------------------------------------------------------------- Cabecera --
logo(22, 18, 21)
line(22, 47.5, SHEET_W - 22, 47.5, NAVY, 0.6)
txt(SHEET_W - 22, 27, "PLANO DE DESPIECE - PANELES DE CUBIERTA", 8.6, NAVY, "end", "bold", ls=0.35)
txt(SHEET_W - 22, 34.5, "Levantamiento de medidas en obra  ·  9 piezas  ·  superficie total "
    f"{num(AREA_TOTAL, 2)} m²", 4.0, GREY, "end")
txt(SHEET_W - 22, 42.0, "ESCALA 1:10  (A1 841 x 594 mm)   ·   COTAS EN CENTIMETROS",
    4.0, TEAL, "end", "bold", ls=0.3)

# ============================================================================
# ZONA DE DIBUJO
# ============================================================================
COLX = 566.0          # separacion columna derecha
line(COLX - 8, 54, COLX - 8, SHEET_H - 14, LINE, 0.3, dash="3 2")


def etiqueta(cx, y, ref, nombre, medida, foto):
    """Cajetin de identificacion de pieza."""
    w = 15.0
    rect(cx - w / 2, y, w, 6.4, NAVY, NAVY, 0.3, rx=1.0)
    txt(cx, y + 4.55, ref, 4.0, "#FFFFFF", "middle", "bold")
    txt(cx, y + 11.4, nombre, 3.3, INK, "middle", "bold")
    txt(cx, y + 15.9, f"{medida} cm   |   {foto}", 3.0, GREY, "middle")


# ---------------------------------------------------------------- P-02 -----
# 166 arriba, laterales 120, chaflan 36h x 12v, laterales 53, base 94, alto 185
ox, oy = 34.0, 74.0
P2 = [(0, 0), (166, 0), (166, 120), (130, 132), (130, 185), (36, 185), (36, 132), (0, 120)]
poly([(ox + a, oy + b) for a, b in P2], fill="url(#slatH)", stroke=NAVY, sw=0.7)
dim_h(ox, ox + 166, oy - 9, oy, "166")
dim_h(ox + 36, ox + 130, oy + 185 + 9, oy + 185, "94", above=False)
dim_v(oy, oy + 120, ox - 9, ox, "120", right=False)
dim_v(oy + 132, oy + 185, ox - 9, ox + 36, "53", right=False)
dim_v(oy, oy + 185, ox + 166 + 12, ox + 166, "185")
dim_h(ox + 130, ox + 166, oy + 138, oy + 132, "35", size=3.0, above=False)
line(ox + 130, oy + 132, ox + 166, oy + 132, LINE, 0.18, dash="2 1.5")
etiqueta(ox + 83, oy + 196, "P-02", "Cubierta zona escotillas", "166/94 x 185", "Foto 2")

# ---------------------------------------------------------------- P-03 -----
# trapecio isosceles: 60 arriba, 166 abajo, lados 180, altura 172
ox3, oy3 = 245.0, 74.0
P3 = [(53, 0), (113, 0), (166, 172), (0, 172)]
poly([(ox3 + a, oy3 + b) for a, b in P3], fill="url(#slatH)", stroke=NAVY, sw=0.7)
dim_h(ox3 + 53, ox3 + 113, oy3 - 9, oy3, "60")
dim_h(ox3, ox3 + 166, oy3 + 172 + 9, oy3 + 172, "166", above=False)
dim_incl((ox3 + 53, oy3), (ox3, oy3 + 172), "180", off=-6.5)
dim_incl((ox3 + 113, oy3), (ox3 + 166, oy3 + 172), "180", off=6.5)
dim_v(oy3, oy3 + 172, ox3 + 166 + 15, ox3 + 166, "172*")
txt(ox3 + 83, oy3 + 60, "PROA", 5.0, LINE, "middle", "bold", ls=1.2)
line(ox3 + 83, oy3 + 55, ox3 + 83, oy3 + 34, LINE, 0.5)
arrow(ox3 + 83, oy3 + 31, -90, 2.6, LINE)
etiqueta(ox3 + 83, oy3 + 196, "P-03", "Cubierta de proa (trapecio en V)", "60/166 x 172", "Foto 3")

# ---------------------------------------------------------------- P-01 -----
ox1, oy1 = 446.0, 74.0
poly([(ox1, oy1), (ox1 + 78, oy1), (ox1 + 78, oy1 + 283), (ox1, oy1 + 283)],
     fill="url(#slatV)", stroke=NAVY, sw=0.7)
dim_h(ox1, ox1 + 78, oy1 - 9, oy1, "78")
dim_v(oy1, oy1 + 283, ox1 + 78 + 12, ox1 + 78, "283")
# flecha sentido de listones
line(ox1 + 39, oy1 + 118, ox1 + 39, oy1 + 168, TEAL, 0.5)
arrow(ox1 + 39, oy1 + 116, -90, 2.4, TEAL)
arrow(ox1 + 39, oy1 + 170, 90, 2.4, TEAL)
txt(ox1 + 35, oy1 + 143, "sentido de listones", 2.9, TEAL, "middle", "normal", rotate=-90)
etiqueta(ox1 + 39, oy1 + 294, "P-01", "Pasillo central de cubierta", "78 x 283", "Foto 1")

# --------------------------------------------------- P-04 .. P-07 (paneles) -
oy4 = 320.0
panels = [("P-04", 45, "Panel lateral A", "Foto 4"),
          ("P-05", 70, "Panel lateral B", "Foto 4"),
          ("P-06", 70, "Panel lateral C", "Foto 5"),
          ("P-07", 40, "Panel lateral D", "Foto 5")]
px = 34.0
for ref, w, nom, foto in panels:
    poly([(px, oy4), (px + w, oy4), (px + w, oy4 + 103), (px, oy4 + 103)],
         fill="url(#slatH)", stroke=NAVY, sw=0.7)
    dim_h(px, px + w, oy4 - 8, oy4, str(w), size=3.2)
    dim_v(oy4, oy4 + 103, px + w + 10, px + w, "103", size=3.2)
    etiqueta(px + w / 2, oy4 + 112, ref, nom, f"{w} x 103", foto)
    px += w + 44

# --------------------------------------------------- P-08 / P-09 (escalones)-
oy8 = 470.0
px8 = 34.0
for ref, nom in (("P-08", "Escalon superior"), ("P-09", "Escalon inferior")):
    poly([(px8, oy8), (px8 + 58, oy8), (px8 + 58, oy8 + 28), (px8, oy8 + 28)],
         fill="url(#slatH)", stroke=NAVY, sw=0.7)
    dim_h(px8, px8 + 58, oy8 - 8, oy8, "58", size=3.2)
    dim_v(oy8, oy8 + 28, px8 + 58 + 10, px8 + 58, "28", size=3.2)
    etiqueta(px8 + 29, oy8 + 37, ref, nom, "58 x 28", "Foto 6")
    px8 += 100

# ------------------------------------ Tabla de verificacion de medidas ------
VX, VY, VW = 232.0, 462.0, 328.0
txt(VX, VY, "VERIFICACION DE MEDIDAS EN OBRA (a rellenar antes del corte)",
    4.0, NAVY, "start", "bold", ls=0.3)
vcols = [0, 30, 118, 228, VW]
vh = 8.6
vy = VY + 4
rect(VX, vy, VW, vh, NAVY, NAVY, 0.3)
for i, h in enumerate(["REF.", "MEDIDA DE PLANO (cm)", "MEDIDA VERIFICADA (cm)", "CONFORME"]):
    txt(VX + (vcols[i] + vcols[i + 1]) / 2, vy + 5.7, h, 2.9, "#FFFFFF", "middle", "bold")
vy += vh
for n, p in enumerate(PIEZAS):
    rect(VX, vy, VW, vh, "#F6F9FC" if n % 2 else "#FFFFFF", LINE, 0.2)
    for c in (1, 2, 3):
        line(VX + vcols[c], vy, VX + vcols[c], vy + vh, LINE, 0.2)
    txt(VX + vcols[1] / 2, vy + 5.7, p["ref"], 3.2, NAVY, "middle", "bold")
    txt(VX + (vcols[1] + vcols[2]) / 2, vy + 5.7, p["med"], 3.2, INK, "middle")
    rect(VX + vcols[3] + 12, vy + 2.2, 4.2, 4.2, "#FFFFFF", GREY, 0.3)
    txt(VX + vcols[3] + 20, vy + 5.7, "SI", 2.9, GREY, "start")
    rect(VX + vcols[3] + 40, vy + 2.2, 4.2, 4.2, "#FFFFFF", GREY, 0.3)
    txt(VX + vcols[3] + 48, vy + 5.7, "NO", 2.9, GREY, "start")
    vy += vh
rect(VX, VY + 4, VW, vy - VY - 4, "none", NAVY, 0.5)

# ============================================================================
# COLUMNA DERECHA
# ============================================================================
TX = 576.0
TW = SHEET_W - 22 - TX          # 243

# ---------------------------------------------------------- Tabla de piezas -
ty = 62.0
txt(TX, ty, "RELACION DE PIEZAS", 4.6, NAVY, "start", "bold", ls=0.4)
ty += 4.0
cols = [0, 18, 128, 186, 204, TW]
heads = ["REF.", "DESCRIPCION", "MEDIDAS (cm)", "CANT.", "AREA (m²)"]
rh = 9.6
rect(TX, ty, TW, rh, NAVY, NAVY, 0.3)
for i, h in enumerate(heads):
    a = "start" if i < 2 else ("middle" if i in (2, 3) else "end")
    xx = TX + cols[i] + 2.5 if a == "start" else (
        TX + (cols[i] + cols[i + 1]) / 2 if a == "middle" else TX + cols[i + 1] - 2.5)
    txt(xx, ty + 6.3, h, 3.1, "#FFFFFF", a, "bold")
ty += rh
for n, p in enumerate(PIEZAS):
    rect(TX, ty, TW, rh, "#F6F9FC" if n % 2 else "#FFFFFF", LINE, 0.2)
    txt(TX + 2.5, ty + 6.3, p["ref"], 3.3, NAVY, "start", "bold")
    txt(TX + cols[1] + 2.5, ty + 6.3, p["nom"], 3.2, INK)
    txt(TX + (cols[2] + cols[3]) / 2, ty + 6.3, p["med"], 3.2, INK, "middle", "bold")
    txt(TX + (cols[3] + cols[4]) / 2, ty + 6.3, str(p["cant"]), 3.2, INK, "middle")
    txt(TX + cols[5] - 2.5, ty + 6.3, num(p["area"] * p["cant"]), 3.2, INK, "end")
    ty += rh
rect(TX, ty, TW, rh, FILL2, NAVY, 0.4)
txt(TX + 2.5, ty + 6.3, "TOTAL", 3.4, NAVY, "start", "bold")
txt(TX + (cols[3] + cols[4]) / 2, ty + 6.3, str(sum(p["cant"] for p in PIEZAS)),
    3.3, NAVY, "middle", "bold")
txt(TX + cols[5] - 2.5, ty + 6.3, num(AREA_TOTAL), 3.4, NAVY, "end", "bold")
ty += rh + 3
txt(TX, ty + 3, "Superficie neta, sin solapes ni despunte. Prever ~10-15 % de merma de corte.",
    2.9, GREY)

# ------------------------------------------------------------------- Notas --
ny = ty + 14
txt(TX, ny, "NOTAS", 4.6, NAVY, "start", "bold", ls=0.4)
ny += 6.5
notas = [
    "1.  Todas las cotas en centimetros, tomadas en obra sobre la embarcacion.",
    "     Verificar in situ antes de cortar; plantillar las piezas con contornos",
    "     irregulares (P-02 y P-03) sobre el propio soporte.",
    "2.  Solo se representan las piezas marcadas en VERDE en las fotos. Las zonas",
    "     marcadas en amarillo o tachadas quedan expresamente excluidas.",
    "3.  P-01 y P-04 a P-09: en las fotos el contorno aparece trapecial por efecto",
    "     de perspectiva; se interpretan como rectangulos segun las dos cotas",
    "     anotadas en cada uno.",
    "4.  P-02: el alto total de 185 se descompone en 120 (tramo recto) + 12",
    "     (chaflan) + 53 (tramo recto). El retranqueo lateral anotado de 35 se",
    "     resuelve con 36 por lado para cerrar 166 - 72 = 94 en la base.",
    "5.  (*) P-03: la altura 172 es calculada a partir de las bases 60 y 166 y los",
    "     lados de 180 (no fue medida directamente en obra).",
    "6.  P-08 y P-09 son dos escalones identicos de 58 x 28.",
    "7.  Holgura recomendada de montaje: 3 a 5 mm en todo el perimetro.",
    "8.  Sentido del listonado: longitudinal a la eslora salvo indicacion contraria.",
]
for l in notas:
    txt(TX, ny, l, 3.05, INK if not l.startswith("     ") else GREY)
    ny += 4.5

# --------------------------------------- Escala grafica + leyenda (dcha.) ---
sby = ny + 8
txt(TX, sby, "ESCALA GRAFICA  1:10", 3.6, NAVY, "start", "bold", ls=0.3)
sby += 3.5
for i in range(5):
    rect(TX + i * 20, sby, 20, 3.6, NAVY if i % 2 == 0 else "#FFFFFF", NAVY, 0.3)
for i in range(6):
    txt(TX + i * 20, sby + 8.6, str(i * 20), 2.8, GREY, "middle")
txt(TX + 104, sby + 3.1, "cm", 3.0, GREY, "start")

lgy = sby + 15
txt(TX, lgy, "LEYENDA", 3.6, NAVY, "start", "bold", ls=0.3)
rect(TX, lgy + 3.4, 7.5, 4.6, "url(#slatH)", NAVY, 0.6)
txt(TX + 11, lgy + 7.2, "Pieza a fabricar (marcada en VERDE en las fotos)", 3.0, INK)
rect(TX, lgy + 10.4, 7.5, 4.6, "#FFFFFF", LINE, 0.4)
line(TX, lgy + 10.4, TX + 7.5, lgy + 15.0, LINE, 0.4)
line(TX, lgy + 15.0, TX + 7.5, lgy + 10.4, LINE, 0.4)
txt(TX + 11, lgy + 14.2, "Zona descartada (AMARILLO / tachada) - NO se fabrica", 3.0, INK)
line(TX + 0.8, lgy + 20.0, TX + 6.7, lgy + 20.0, DIMC, 0.3)
arrow(TX + 0.8, lgy + 20.0, 180)
arrow(TX + 6.7, lgy + 20.0, 0)
txt(TX + 11, lgy + 21.2, "Cota tomada en obra (cm)", 3.0, INK)
line(TX + 3.7, lgy + 24.5, TX + 3.7, lgy + 29.0, TEAL, 0.5)
arrow(TX + 3.7, lgy + 24.2, -90, 2.0, TEAL)
txt(TX + 11, lgy + 28.2, "Sentido sugerido del listonado", 3.0, INK)

# --------------------------------------------- Indice fotografico de obra ---
fy = lgy + 36
txt(TX, fy, "FOTOS DE REFERENCIA (levantamiento en obra)", 3.6, NAVY, "start", "bold", ls=0.3)
fy += 3.5
FOTOS = [("foto-1.jpg", "FOTO 1", "P-01"),
         ("foto-2.jpg", "FOTO 2", "P-02"),
         ("foto-3.jpg", "FOTO 3", "P-03"),
         ("foto-4.jpg", "FOTO 4", "P-04 / P-05"),
         ("foto-5.jpg", "FOTO 5", "P-06 / P-07"),
         ("foto-6.jpg", "FOTO 6", "P-08 / P-09")]
cw = (TW - 2 * 5) / 3.0
ch = 62.0
for i, (fn, tag, refs) in enumerate(FOTOS):
    cx = TX + (i % 3) * (cw + 5)
    cy = fy + (i // 3) * (ch + 12)
    p = os.path.join(HERE, "fotos", fn)
    if os.path.exists(p):
        foto_box(p, cx, cy, cw, ch)
    else:
        rect(cx, cy, cw, ch, FILL2, LINE, 0.3)
    txt(cx + cw / 2, cy + ch + 4.2, f"{tag}  ·  {refs}", 2.9, NAVY, "middle", "bold")

# ------------------------------------------------------------------ Rotulo --
RH = 76.0
RY = SHEET_H - 14 - RH
rect(TX, RY, TW, RH, "#FFFFFF", NAVY, 0.7)
line(TX, RY + 26, TX + TW, RY + 26, NAVY, 0.4)
logo(TX + 5, RY + 5.5, 15)
txt(TX + TW - 4, RY + 12.5, "PLANO Nº", 3.0, GREY, "end")
txt(TX + TW - 4, RY + 20.5, "DK-CUB-01", 5.4, NAVY, "end", "bold", ls=0.3)

campos = [
    ("CLIENTE", "—"),
    ("EMBARCACION / OBRA", "—"),
    ("CONTENIDO", "Despiece de paneles de cubierta"),
    ("ESCALA", "1:10  (A1)"),
    ("UNIDADES", "centimetros"),
    ("FECHA", "03 / 08 / 2026"),
    ("REV.", "00"),
    ("HOJA", "1 de 1"),
]
cy = RY + 26
for i, (k, v) in enumerate(campos):
    h = 6.2
    line(TX, cy + h, TX + TW, cy + h, LINE, 0.2)
    txt(TX + 3, cy + 4.5, k, 2.7, GREY)
    txt(TX + 74, cy + 4.5, v, 3.2, INK, "start", "bold")
    cy += h

add('</svg>')

svg = "\n".join(out)
svg_path = os.path.join(HERE, "plano-cubierta-deckeva.svg")
with open(svg_path, "w", encoding="utf-8") as f:
    f.write(svg)
print("SVG:", svg_path)

import cairosvg  # noqa: E402
cairosvg.svg2pdf(url=svg_path, write_to=os.path.join(HERE, "plano-cubierta-deckeva.pdf"))
cairosvg.svg2png(url=svg_path, write_to=os.path.join(HERE, "plano-cubierta-deckeva.png"),
                 output_width=2600)
print("PDF y PNG generados.")
