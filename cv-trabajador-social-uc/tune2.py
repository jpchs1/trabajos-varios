import sys, os, re, subprocess
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import variantes as V
from tune import PRESETS

MIN_SLACK = 2.4   # cm libres al pie de la pagina 1 (colchon para Word)

def measure(pdf):
    xml = subprocess.run(['pdftotext','-bbox',pdf,'-'],capture_output=True,text=True).stdout
    pages = re.findall(r'<page width="[\d.]+" height="([\d.]+)">(.*?)</page>', xml, re.S)
    res=[]
    for h, body in pages:
        ys=[float(m) for m in re.findall(r'yMax="([\d.]+)"', body)]
        res.append((float(h)-max(ys))/72*2.54 if ys else 99)
    return res

def build(name, preset):
    V.K.update(preset); f = V.BUILDERS[name]()
    base = os.path.basename(f).replace('.docx','')
    subprocess.run(['cp', f, f'sf_{base}.docx'], check=True)
    subprocess.run(['soffice','--headless','-env:UserInstallation=file:///tmp/lo_profile',
                    '--convert-to','pdf',f'sf_{base}.docx','--outdir','.'], capture_output=True)
    return f, f'sf_{base}.pdf'

CHOSEN={}
for name in ('swiss','banda','sidebar','editorial','clasico','impacto'):
    for i, pr in enumerate(PRESETS):
        f, pdf = build(name, pr)
        sl = measure(pdf)
        ok = len(sl) == 2 and sl[0] >= MIN_SLACK
        print(f"{name:8s} preset {i}: {len(sl)} pags | holgura p1 {sl[0]:6.2f} cm {'OK' if ok else ''}")
        if ok:
            p1=subprocess.run(['pdftotext','-f','1','-l','1',pdf,'-'],capture_output=True,text=True).stdout
            p2=subprocess.run(['pdftotext','-f','2','-l','2',pdf,'-'],capture_output=True,text=True).stdout
            print(f"          p1 fin  ...{' '.join(p1.split())[-55:]}")
            print(f"          p2 ini  {' '.join(p2.split())[:55]}...")
            CHOSEN[name]=i; break
    else:
        print(f"{name}: sin preset que cumpla holgura\n")
print("\nCHOSEN =", CHOSEN)
