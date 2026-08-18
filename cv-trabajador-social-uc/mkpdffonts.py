# -*- coding: utf-8 -*-
"""Subconjunto Latin-1 de Carlito/Caladea en TTF + metricas, para incrustar en PDF."""
import base64, json, io
from fontTools.ttLib import TTFont
from fontTools.subset import Subsetter, Options

SRC = {
 "sans":   "/usr/share/fonts/truetype/crosextra/Carlito-Regular.ttf",
 "sansB":  "/usr/share/fonts/truetype/crosextra/Carlito-Bold.ttf",
 "sansI":  "/usr/share/fonts/truetype/crosextra/Carlito-Italic.ttf",
 "serif":  "/usr/share/fonts/truetype/crosextra/Caladea-Regular.ttf",
 "serifB": "/usr/share/fonts/truetype/crosextra/Caladea-Bold.ttf",
 "serifI": "/usr/share/fonts/truetype/crosextra/Caladea-Italic.ttf",
}
# WinAnsiEncoding: byte -> unicode (solo lo que difiere de latin-1)
WIN = {i: i for i in range(32, 256)}
WIN.update({0x80:0x20AC,0x82:0x201A,0x83:0x0192,0x84:0x201E,0x85:0x2026,0x86:0x2020,
            0x87:0x2021,0x88:0x02C6,0x89:0x2030,0x8A:0x0160,0x8B:0x2039,0x8C:0x0152,
            0x8E:0x017D,0x91:0x2018,0x92:0x2019,0x93:0x201C,0x94:0x201D,0x95:0x2022,
            0x96:0x2013,0x97:0x2014,0x98:0x02DC,0x99:0x2122,0x9A:0x0161,0x9B:0x203A,
            0x9C:0x0153,0x9E:0x017E,0x9F:0x0178})
CODES = sorted(WIN)
out = {}
for key, path in SRC.items():
    f = TTFont(path)
    ss = Subsetter(Options(notdef_outline=True, layout_features=[], hinting=False))
    ss.populate(unicodes=set(WIN.values()))
    ss.subset(f)
    upm = f['head'].unitsPerEm
    cmap = f.getBestCmap()
    hmtx = f['hmtx']
    widths = []
    for c in CODES:
        u = WIN[c]
        gn = cmap.get(u)
        widths.append(round(hmtx[gn][0] * 1000 / upm) if gn else 0)
    buf = io.BytesIO(); f.save(buf); data = buf.getvalue()
    hhea = f['hhea']; os2 = f['OS/2']; head = f['head']
    out[key] = dict(
        ttf = base64.b64encode(data).decode(),
        len1 = len(data),
        widths = widths,
        asc  = round(hhea.ascender * 1000 / upm),
        desc = round(hhea.descender * 1000 / upm),
        cap  = round(getattr(os2, 'sCapHeight', hhea.ascender) * 1000 / upm),
        bbox = [round(v * 1000 / upm) for v in (head.xMin, head.yMin, head.xMax, head.yMax)],
        italic = 1 if 'I' in key else 0,
        ascRatio = round(hhea.ascender / upm, 4),
    )
    print(f"{key:7s} {len(data)/1024:6.1f} KB  asc={out[key]['asc']}")
js = "const PDFFONTS=" + json.dumps(out, separators=(',',':')) + ";const PDFCODES=" + json.dumps(CODES) + ";"
open('pdffonts.js','w').write(js)
print("total js:", len(js)/1024, "KB")
