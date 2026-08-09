import base64, io, sys, subprocess, os
sys.path.insert(0,'.')
import cv_content as C
from fontTools.subset import main as subset_main

# universo de caracteres realmente usado por el CV + chrome de la pagina
chars = set(" ·•▪—–|()[]{}/:;,.«»\"'%&+-…")
def eat(x):
    if isinstance(x, str): chars.update(x)
    elif isinstance(x, (list, tuple)): [eat(i) for i in x]
    elif isinstance(x, dict): [eat(v) for v in x.values()]
for name in dir(C):
    if not name.startswith("_"): eat(getattr(C, name))
chars.update("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789")
chars.update("ÁÉÍÓÚÜÑáéíóúüñ¿¡ÀÈÌÒÙçÇ")
chars.update("Propuestas de diseño · Formato Ficha técnica Recomendado Página")

FONTS = {
 "cv-sans":      ("/usr/share/fonts/truetype/crosextra/Carlito-Regular.ttf", 400, "normal"),
 "cv-sans-b":    ("/usr/share/fonts/truetype/crosextra/Carlito-Bold.ttf",    700, "normal"),
 "cv-sans-i":    ("/usr/share/fonts/truetype/crosextra/Carlito-Italic.ttf",  400, "italic"),
 "cv-serif":     ("/usr/share/fonts/truetype/crosextra/Caladea-Regular.ttf", 400, "normal"),
 "cv-serif-b":   ("/usr/share/fonts/truetype/crosextra/Caladea-Bold.ttf",    700, "normal"),
}
FAMILY = {"cv-sans":"CVSans","cv-sans-b":"CVSans","cv-sans-i":"CVSans",
          "cv-serif":"CVSerif","cv-serif-b":"CVSerif"}

os.makedirs("fsub", exist_ok=True)
text = "".join(sorted(chars))
open("fsub/chars.txt","w").write(text)
css = []
total = 0
for key,(path,weight,style) in FONTS.items():
    out = f"fsub/{key}.woff2"
    subset_main([path, f"--text-file=fsub/chars.txt", "--flavor=woff2",
                 "--layout-features=kern,liga", f"--output-file={out}",
                 "--no-hinting", "--desubroutinize"])
    b = open(out,"rb").read(); total += len(b)
    b64 = base64.b64encode(b).decode()
    css.append(f"""@font-face{{font-family:'{FAMILY[key]}';font-style:{style};font-weight:{weight};
font-display:swap;src:url(data:font/woff2;base64,{b64}) format('woff2');}}""")
open("fsub/fonts.css","w").write("\n".join(css))
print(f"woff2 total: {total/1024:.0f} KB  |  base64 aprox {total*1.34/1024:.0f} KB  |  glifos: {len(chars)}")
