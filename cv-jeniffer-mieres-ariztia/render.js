// Genera el PDF del CV desde cv.html.
// Requisitos: npm install puppeteer-core  +  un Chromium instalado.
const puppeteer = require('puppeteer-core');
const path = require('path');

const HTML = 'file://' + path.join(__dirname, 'cv.html');
const OUT = path.join(__dirname, 'CV_Jeniffer_Mieres_Contreras.pdf');
const CHROME = process.env.CHROME_PATH || '/opt/pw-browsers/chromium-1194/chrome-linux/chrome';

(async () => {
  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: true,
    args: ['--no-sandbox', '--disable-gpu', '--font-render-hinting=none'],
  });
  const page = await browser.newPage();
  await page.goto(HTML, { waitUntil: 'networkidle0' });
  await page.evaluateHandle('document.fonts.ready');
  await new Promise(r => setTimeout(r, 300));
  await page.pdf({ path: OUT, printBackground: true, preferCSSPageSize: true });
  console.log('PDF generado:', OUT);
  await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
