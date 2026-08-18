
/* ==================================================================
   Generador de PDF en el navegador, midiendo la maqueta real.
   Produce texto seleccionable (apto para ATS) con las tipografias
   incrustadas, no una imagen. No usa el dialogo de impresion.
   ================================================================== */
const PX2PT = 0.75;                 // 96 dpi -> 72 pt
const A4W_PT = 595.2756, A4H_PT = 841.8898;
const SHEET_PX = 793.7008;          // 210 mm a 96 dpi

/* WinAnsiEncoding: unicode -> byte */
const WINMAP = (()=>{
  const m = new Map();
  for(let i=32;i<127;i++) m.set(i,i);
  for(let i=160;i<256;i++) m.set(i,i);
  const hi = {0x20AC:0x80,0x201A:0x82,0x0192:0x83,0x201E:0x84,0x2026:0x85,0x2020:0x86,
              0x2021:0x87,0x02C6:0x88,0x2030:0x89,0x0160:0x8A,0x2039:0x8B,0x0152:0x8C,
              0x017D:0x8E,0x2018:0x91,0x2019:0x92,0x201C:0x93,0x201D:0x94,0x2022:0x95,
              0x2013:0x96,0x2014:0x97,0x02DC:0x98,0x2122:0x99,0x0161:0x9A,0x203A:0x9B,
              0x0153:0x9C,0x017E:0x9E,0x0178:0x9F};
  for(const k in hi) m.set(+k, hi[k]);
  return m;
})();

function faceKey(cs){
  const fam = (cs.fontFamily||'').toLowerCase();
  const serif = fam.indexOf('cvserif')>=0 || fam.indexOf('cambria')>=0 || fam.indexOf('georgia')>=0;
  const w = parseInt(cs.fontWeight,10) || 400;
  const ital = cs.fontStyle && cs.fontStyle !== 'normal';
  let k = serif ? 'serif' : 'sans';
  if(w >= 600) k += 'B'; else if(ital) k += 'I';
  return k;
}
function parseColor(v){
  if(!v) return null;
  const m = v.match(/rgba?\(([^)]+)\)/);
  if(!m) return null;
  const p = m[1].split(',').map(x=>parseFloat(x));
  const a = p.length > 3 ? p[3] : 1;
  if(!(a > 0.004)) return null;
  return {r:p[0]/255, g:p[1]/255, b:p[2]/255, a:a};
}
function effOpacity(el, root){
  let o = 1, n = el;
  while(n && n !== root.parentNode){
    const v = parseFloat(getComputedStyle(n).opacity);
    if(!isNaN(v)) o *= v;
    n = n.parentElement;
  }
  return o;
}
function applyTransform(txt, tt){
  if(tt === 'uppercase') return txt.toLocaleUpperCase('es');
  if(tt === 'lowercase') return txt.toLocaleLowerCase('es');
  if(tt === 'capitalize') return txt.replace(/(^|\s)(\S)/g,(m,a,b)=>a+b.toLocaleUpperCase('es'));
  return txt;
}

/* ---------------- medicion de una hoja ---------------- */
function measureSheet(sheetEl){
  const base = sheetEl.getBoundingClientRect();
  const ox = base.left, oy = base.top;
  const ops = [];
  const push = (o)=>{ ops.push(o); };

  /* 1. fondos y filetes, en orden de documento (padres antes que hijos) */
  const els = [sheetEl, ...sheetEl.querySelectorAll('*')];
  els.forEach(el=>{
    const cs = getComputedStyle(el);
    if(cs.display === 'none' || cs.visibility === 'hidden') return;
    const r = el.getBoundingClientRect();
    if(r.width <= 0 || r.height <= 0) {
      // los filetes puros (div solo con border-top) miden alto = grosor
      if(r.width <= 0) return;
    }
    const al = effOpacity(el, sheetEl);
    const bg = parseColor(cs.backgroundColor);
    if(bg && r.width > 0 && r.height > 0)
      push({t:'rect', x:r.left-ox, y:r.top-oy, w:r.width, h:r.height, c:bg, a:al*bg.a});
    const sides = [
      ['Top',    r.left-ox,  r.top-oy,  r.width, 0],
      ['Right',  0,          r.top-oy,  0,       r.height],
      ['Bottom', r.left-ox,  0,         r.width, 0],
      ['Left',   r.left-ox,  r.top-oy,  0,       r.height],
    ];
    sides.forEach(([side, x, y, w, h])=>{
      const bw = parseFloat(cs['border'+side+'Width']) || 0;
      if(bw <= 0) return;
      const st = cs['border'+side+'Style'];
      if(st === 'none' || st === 'hidden') return;
      const c = parseColor(cs['border'+side+'Color']);
      if(!c) return;
      let X=x, Y=y, W=w, H=h;
      if(side === 'Top'){ H = bw; }
      else if(side === 'Bottom'){ Y = r.bottom-oy-bw; H = bw; }
      else if(side === 'Left'){ W = bw; }
      else { X = r.right-ox-bw; W = bw; }
      if(W <= 0 || H <= 0) return;
      push({t:'rect', x:X, y:Y, w:W, h:H, c:c, a:al*c.a});
    });
  });

  /* 2. texto, nodo por nodo, midiendo cada caracter */
  const walker = document.createTreeWalker(sheetEl, NodeFilter.SHOW_TEXT, null);
  const rg = document.createRange();
  let node;
  while((node = walker.nextNode())){
    const raw = node.data;
    if(!raw || !raw.trim()) continue;
    const par = node.parentElement;
    if(!par) continue;
    const cs = getComputedStyle(par);
    if(cs.display === 'none' || cs.visibility === 'hidden') continue;
    const col = parseColor(cs.color);
    if(!col) continue;
    const size = parseFloat(cs.fontSize) || 0;
    if(size <= 0) continue;
    const face = faceKey(cs);
    const fm = PDFFONTS[face];
    const ascFrac = fm.asc / (fm.asc - fm.desc);
    const lsRaw = cs.letterSpacing;
    const ls = (!lsRaw || lsRaw === 'normal') ? 0 : (parseFloat(lsRaw) || 0);
    const al = effOpacity(par, sheetEl);
    const txt = applyTransform(raw, cs.textTransform);

    let words = [], cur = null;
    const flush = (hyph)=>{ if(cur){ cur.hyph = !!hyph; words.push(cur); cur = null; } };
    for(let i=0;i<raw.length;i++){
      if(/\s/.test(raw[i])){ flush(false); continue; }
      rg.setStart(node, i); rg.setEnd(node, i+1);
      const r = rg.getBoundingClientRect();
      if(r.height <= 0){ continue; }
      if(cur && Math.abs(r.top - cur.top) > 0.9) flush(true);   // corte de linea dentro de palabra
      if(!cur) cur = {top:r.top, h:r.height, chars:[]};
      cur.chars.push({ch: txt[i] || raw[i], l:r.left, r:r.right, w:r.width});
    }
    flush(false);

    words.forEach(w=>{
      const baseY = w.top - oy + w.h * ascFrac;
      let runTxt = '', runX = null;
      const flushRun = ()=>{
        if(runTxt){
          push({t:'text', x:runX, y:baseY, s:runTxt, size:size, ls:ls,
                face:face, c:col, a:al*col.a});
        }
        runTxt = ''; runX = null;
      };
      w.chars.forEach(c=>{
        const cp = c.ch.codePointAt(0);
        if(WINMAP.has(cp)){
          if(runX === null) runX = c.l - ox;
          runTxt += c.ch;
        } else {
          flushRun();
          if(c.ch === '▪' || c.ch === '■' || c.ch === '•'){
            const side = size * (c.ch === '•' ? 0.26 : 0.30);
            const cx = (c.l + c.r)/2 - ox, cy = baseY - size*0.28;
            push({t:'rect', x:cx-side/2, y:cy-side/2, w:side, h:side, c:col, a:al*col.a});
          }
        }
      });
      if(w.hyph){
        const last = w.chars[w.chars.length-1];
        if(last && runX !== null) runTxt += '-';
        else if(last) { runX = last.r - ox; runTxt = '-'; }
      }
      flushRun();
    });
  }
  return ops;
}

/* ---------------- ensamblado del PDF ---------------- */
function PdfBuf(){
  this.parts = []; this.len = 0;
}
PdfBuf.prototype.s = function(str){
  const b = new Uint8Array(str.length);
  for(let i=0;i<str.length;i++) b[i] = str.charCodeAt(i) & 255;
  this.parts.push(b); this.len += b.length;
};
PdfBuf.prototype.b = function(u8){ this.parts.push(u8); this.len += u8.length; };
PdfBuf.prototype.out = function(){
  const o = new Uint8Array(this.len); let k = 0;
  for(const p of this.parts){ o.set(p, k); k += p.length; }
  return o;
};
function pdfStr(s){
  let o = '';
  for(const ch of s){
    const cp = ch.codePointAt(0), b = WINMAP.get(cp);
    if(b === undefined) continue;
    if(b === 0x28 || b === 0x29 || b === 0x5C) o += '\\';
    o += String.fromCharCode(b);
  }
  return '(' + o + ')';
}
const f3 = v => (Math.round(v*1000)/1000).toString();

const FACE_META = {
  sans:  {name:'AAAAAA+Carlito',        flags:32,  ital:0},
  sansB: {name:'AAAAAB+Carlito-Bold',   flags:32,  ital:0},
  sansI: {name:'AAAAAC+Carlito-Italic', flags:96,  ital:-12},
  serif: {name:'AAAAAD+Caladea',        flags:34,  ital:0},
  serifB:{name:'AAAAAE+Caladea-Bold',   flags:34,  ital:0},
  serifI:{name:'AAAAAF+Caladea-Italic', flags:98,  ital:-12},
};

function buildPdf(pageOps){
  /* recursos usados */
  const faces = [], alphas = [];
  pageOps.forEach(ops=>ops.forEach(o=>{
    if(o.t === 'text' && faces.indexOf(o.face) < 0) faces.push(o.face);
    const a = Math.round(Math.min(1, Math.max(0, o.a))*100)/100;
    if(a < 1 && alphas.indexOf(a) < 0) alphas.push(a);
  }));

  const objs = [];                       // objs[i] corresponde al objeto i+1
  const alloc = ()=>{ objs.push(null); return objs.length; };
  const put = (num, val)=>{ objs[num-1] = val; };

  const catalogN = alloc(), pagesN = alloc();
  const fontN = {}, gsN = {};
  faces.forEach(f=>{
    const ffN = alloc(), fdN = alloc(), ftN = alloc();
    const m = PDFFONTS[f], meta = FACE_META[f];
    const bytes = b64ToBytes(m.ttf);
    put(ffN, {pre:'<</Length '+bytes.length+'/Length1 '+m.len1+'>>\nstream\n',
              bin:bytes, post:'\nendstream'});
    put(fdN, '<</Type/FontDescriptor/FontName/'+meta.name+'/Flags '+meta.flags+
             '/FontBBox['+m.bbox.join(' ')+']/ItalicAngle '+meta.ital+
             '/Ascent '+m.asc+'/Descent '+m.desc+'/CapHeight '+m.cap+
             '/StemV 80/FontFile2 '+ffN+' 0 R>>');
    put(ftN, '<</Type/Font/Subtype/TrueType/BaseFont/'+meta.name+
             '/FirstChar 32/LastChar 255/Widths['+m.widths.join(' ')+']'+
             '/Encoding/WinAnsiEncoding/FontDescriptor '+fdN+' 0 R>>');
    fontN[f] = ftN;
  });
  alphas.forEach(a=>{
    const n = alloc();
    put(n, '<</Type/ExtGState/ca '+f3(a)+'/CA '+f3(a)+'>>');
    gsN[a] = n;
  });

  let res = '<</Font<<';
  faces.forEach(f=> res += '/F_'+f+' '+fontN[f]+' 0 R');
  res += '>>';
  if(alphas.length){
    res += '/ExtGState<<';
    alphas.forEach(a=> res += '/GS'+String(a).replace('.','_')+' '+gsN[a]+' 0 R');
    res += '>>';
  }
  res += '>>';

  const kids = [];
  pageOps.forEach(ops=>{
    const pN = alloc(), cN = alloc();
    kids.push(pN);
    let st = '', curA = null, curC = null;
    // No existe un ExtGState "opaco": para volver a alfa 1 se restaura el estado.
    const setAlpha = (a)=>{
      const v = Math.round(Math.min(1,Math.max(0,a))*100)/100;
      if(v === curA) return;
      if(v < 1){ st += '/GS'+String(v).replace('.','_')+' gs\n'; }
      else if(curA !== null){ st += 'Q q\n'; curC = null; }
      curA = v;
    };
    st += 'q\n';
    ops.forEach(o=>{
      setAlpha(o.a);
      const c = o.c;
      if(o.t === 'rect'){
        const key = 'f'+f3(c.r)+f3(c.g)+f3(c.b);
        if(key !== curC){ st += f3(c.r)+' '+f3(c.g)+' '+f3(c.b)+' rg\n'; curC = key; }
        st += f3(o.x*PX2PT)+' '+f3(A4H_PT-(o.y+o.h)*PX2PT)+' '+
              f3(o.w*PX2PT)+' '+f3(o.h*PX2PT)+' re f\n';
      } else {
        const key = 'f'+f3(c.r)+f3(c.g)+f3(c.b);
        if(key !== curC){ st += f3(c.r)+' '+f3(c.g)+' '+f3(c.b)+' rg\n'; curC = key; }
        // Tc forma parte del estado de texto y persiste tras ET: se fija siempre.
        st += 'BT /F_'+o.face+' '+f3(o.size*PX2PT)+' Tf '+f3((o.ls||0)*PX2PT)+' Tc '+
              f3(o.x*PX2PT)+' '+f3(A4H_PT-o.y*PX2PT)+' Td '+pdfStr(o.s)+' Tj ET\n';
      }
    });
    st += 'Q\n';
    put(cN, {pre:'<</Length '+st.length+'>>\nstream\n', bin:new TextEncoder().encode(''), post:'', raw:st});
    put(pN, '<</Type/Page/Parent '+pagesN+' 0 R/MediaBox[0 0 '+f3(A4W_PT)+' '+f3(A4H_PT)+']'+
            '/Resources '+res+'/Contents '+cN+' 0 R>>');
  });

  put(catalogN, '<</Type/Catalog/Pages '+pagesN+' 0 R>>');
  put(pagesN, '<</Type/Pages/Kids['+kids.map(k=>k+' 0 R').join(' ')+']/Count '+kids.length+'>>');

  const buf = new PdfBuf();
  buf.s('%PDF-1.4\n%\xE2\xE3\xCF\xD3\n');
  const offs = new Array(objs.length + 1).fill(0);
  objs.forEach((val, i)=>{
    const num = i + 1;
    offs[num] = buf.len;
    buf.s(num + ' 0 obj\n');
    if(typeof val === 'string'){ buf.s(val); }
    else if(val && val.raw !== undefined){
      buf.s(val.pre); buf.s(val.raw); buf.s('\nendstream');
    } else {
      buf.s(val.pre); buf.b(val.bin); buf.s(val.post);
    }
    buf.s('\nendobj\n');
  });
  const xref = buf.len;
  buf.s('xref\n0 ' + (objs.length + 1) + '\n0000000000 65535 f \n');
  for(let i=1;i<=objs.length;i++)
    buf.s(String(offs[i]).padStart(10,'0') + ' 00000 n \n');
  buf.s('trailer\n<</Size ' + (objs.length + 1) + '/Root ' + catalogN + ' 0 R>>\n' +
        'startxref\n' + xref + '\n%%EOF\n');
  return buf.out();
}

/* ---------------- puesta en escena y salida ---------------- */
async function generatePdf(sid, pristine){
  if(document.fonts && document.fonts.ready) await document.fonts.ready;
  const stage = document.createElement('div');
  stage.id = 'pdf-stage';
  stage.setAttribute('aria-hidden','true');
  stage.style.cssText = 'position:fixed;left:-4000px;top:0;width:' + SHEET_PX +
    'px;z-index:-1;pointer-events:none;background:#fff';
  const src = [...document.querySelectorAll('#p-' + sid + ' .sheet')];
  src.forEach(sh=>{
    const clone = sh.cloneNode(true);
    if(pristine) clone.innerHTML = originals.get(sh);
    clone.style.width = SHEET_PX + 'px';
    clone.style.boxShadow = 'none';
    clone.style.borderRadius = '0';
    clone.querySelectorAll('.ed').forEach(e=>e.removeAttribute('contenteditable'));
    stage.appendChild(clone);
  });
  document.body.appendChild(stage);
  try{
    const pages = [...stage.children].map(measureSheet);
    return buildPdf(pages);
  } finally {
    stage.remove();
  }
}
