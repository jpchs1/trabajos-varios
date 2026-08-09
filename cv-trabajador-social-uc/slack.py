import re, subprocess, sys
def slack(pdf):
    xml = subprocess.run(['pdftotext','-bbox',pdf,'-'],capture_output=True,text=True).stdout
    pages = re.findall(r'<page width="([\d.]+)" height="([\d.]+)">(.*?)</page>', xml, re.S)
    out=[]
    for w,h,body in pages:
        ys = [float(m) for m in re.findall(r'yMax="([\d.]+)"', body)]
        if not ys: continue
        # puntos -> cm
        out.append(((float(h)-max(ys))/72*2.54, float(h)/72*2.54))
    return out
for name,f in (("01 Minimal","fin_CV_Jeniffer_Mieres_01_Minimal.pdf"),
               ("02 Banda","fin_CV_Jeniffer_Mieres_02_Banda.pdf"),
               ("03 Sidebar","fin_CV_Jeniffer_Mieres_03_Sidebar.pdf")):
    s = slack(f)
    print(f"{name:11s} " + " | ".join(f"p{i+1}: {v:.2f} cm libres" for i,(v,_) in enumerate(s)))
