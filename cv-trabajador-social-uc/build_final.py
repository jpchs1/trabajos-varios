import sys, os, subprocess
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
import variantes as V
from tune import PRESETS
CHOSEN = dict(swiss=6, banda=6, sidebar=5)
for name, idx in CHOSEN.items():
    V.K.update(PRESETS[idx])
    f = V.BUILDERS[name]()
    base = os.path.basename(f).replace('.docx','')
    subprocess.run(['cp', f, f'fin_{base}.docx'], check=True)
    subprocess.run(['soffice','--headless','-env:UserInstallation=file:///tmp/lo_profile',
                    '--convert-to','pdf',f'fin_{base}.docx','--outdir','.'], capture_output=True)
    pg = subprocess.run(['pdfinfo', f'fin_{base}.pdf'],capture_output=True,text=True).stdout
    n = [l.split()[1] for l in pg.splitlines() if l.startswith('Pages:')][0]
    subprocess.run(['pdftoppm','-png','-r','100',f'fin_{base}.pdf',f'fin_{base}'])
    base_pt = 10.0 if name=="banda" else 9.5
    print(f"{name:8s} -> {n} pags | cuerpo {V.kb(base_pt):.1f}pt | {os.path.basename(f)}")
