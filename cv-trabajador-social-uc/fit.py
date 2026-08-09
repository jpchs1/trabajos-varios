import json, os, subprocess, sys
from playwright.sync_api import sync_playwright
D = "/tmp/claude-0/-home-user-trabajos-varios/08e63ebe-9bab-5b5e-bcb3-d86e109b9bf1/scratchpad/cvwork"
TARGET_P1 = 0.915          # mismo llenado que el .docx en la pagina 1
def measure():
    with sync_playwright() as p:
        b = p.chromium.launch(executable_path='/opt/pw-browsers/chromium-1194/chrome-linux/chrome', args=['--no-sandbox'])
        pg = b.new_context(viewport={'width':1440,'height':1200}).new_page()
        pg.goto(f"file://{D}/cv_propuestas.html"); pg.wait_for_timeout(600)
        res={}
        for tab in ("minimal","banda","sidebar"):
            pg.click(f"#t-{tab}"); pg.wait_for_timeout(220)
            res[tab]=pg.evaluate("""()=>[...document.querySelectorAll('section:not([hidden]) .pg')].map(el=>{
              const box=el.getBoundingClientRect(); let last=0;
              el.querySelectorAll('*').forEach(n=>{const b=n.getBoundingClientRect();
                if(b.height&&b.bottom>last) last=b.bottom;});
              return +((last-box.top)/box.height).toFixed(4);})""")
        b.close(); return res
fits = json.load(open(f"{D}/fits.json")) if os.path.exists(f"{D}/fits.json") else {"minimal":1.0,"banda":1.0,"sidebar":1.0}
for it in range(5):
    subprocess.run([sys.executable, f"{D}/mkpage.py"], cwd=D, capture_output=True)
    r = measure()
    print(f"iter {it}: " + "  ".join(f"{k} p1={v[0]:.3f} p2={v[1]:.3f}" for k,v in r.items()))
    done=True
    for k,(a,bq) in r.items():
        if not (0.882 <= a <= 0.945):
            fits[k] = round(fits[k]*0.915/a, 4); done=False
        if bq > 0.965:                      # nunca desbordar la pagina 2
            fits[k] = round(fits[k]*0.96/bq, 4); done=False
    json.dump(fits, open(f"{D}/fits.json","w"))
    if done: break
print("FITS =", fits)
