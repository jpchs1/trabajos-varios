import sys, os, subprocess
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import variantes as V

# Escalera que recorta ESPACIADO primero y tipografia solo al final.
PRESETS = [
  dict(body= 0.0, line= 0.00, bul= 0.0, sec= 0.0,  job= 0.0, par= 0.0, mar= 0.00),
  dict(body= 0.0, line=-0.02, bul=-0.5, sec=-3.0,  job=-1.5, par=-0.5, mar=-0.15),
  dict(body= 0.0, line=-0.03, bul=-1.0, sec=-5.0,  job=-2.5, par=-1.0, mar=-0.28),
  dict(body= 0.0, line=-0.04, bul=-1.5, sec=-7.0,  job=-3.5, par=-1.5, mar=-0.38),
  dict(body= 0.0, line=-0.05, bul=-1.9, sec=-8.5,  job=-4.5, par=-2.0, mar=-0.45),
  dict(body=-0.2, line=-0.06, bul=-2.2, sec=-9.5,  job=-5.0, par=-2.3, mar=-0.50),
  dict(body=-0.4, line=-0.07, bul=-2.5, sec=-10.5, job=-5.5, par=-2.6, mar=-0.55),
  dict(body=-0.6, line=-0.08, bul=-2.8, sec=-11.5, job=-6.0, par=-2.9, mar=-0.60),
]

def pages(pdf):
    out = subprocess.run(['pdfinfo', pdf], capture_output=True, text=True).stdout
    for l in out.splitlines():
        if l.startswith('Pages:'): return int(l.split()[1])
    return 99

def build(name, preset):
    V.K.update(preset)
    f = V.BUILDERS[name]()
    base = os.path.basename(f).replace('.docx', '')
    subprocess.run(['cp', f, f'tune_{base}.docx'], check=True)
    subprocess.run(['soffice','--headless','-env:UserInstallation=file:///tmp/lo_profile',
                    '--convert-to','pdf',f'tune_{base}.docx','--outdir','.'], capture_output=True)
    return f, f'tune_{base}.pdf'

for name in ():
    for i, pr in enumerate(PRESETS):
        f, pdf = build(name, pr)
        n = pages(pdf)
        if n == 2:
            p1 = subprocess.run(['pdftotext','-f','1','-l','1',pdf,'-'],capture_output=True,text=True).stdout
            p2 = subprocess.run(['pdftotext','-f','2','-l','2',pdf,'-'],capture_output=True,text=True).stdout
            print(f"{name:8s} preset {i}: 2 pags | body {V.kb(9.5):.1f}pt line {V.kl(1.18):.2f}")
            print(f"          p1 fin   ...{' '.join(p1.split())[-58:]}")
            print(f"          p2 inicio {' '.join(p2.split())[:58]}...\n")
            break
        print(f"{name:8s} preset {i}: {n} pags")
