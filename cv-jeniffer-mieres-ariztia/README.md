# CV · Jeniffer Mieres Contreras

CV en formato profesional y sobrio, redactado con voz natural (que no parezca
generado por IA) para postular en el área de bienestar / personas / equidad e
inclusión (p. ej. el cargo de Coordinador(a) de Equidad e Inclusión de Ariztía,
publicado en su portal de empleos Hiring Room).

## Archivos
- `CV_Jeniffer_Mieres_Contreras.pdf` — **entregable** (2 páginas A4, con capa de texto real).
- `cv.html` — fuente editable.
- `render.js` — genera el PDF desde el HTML.

## Nota técnica (importante para portales tipo Hiring Room / ATS)
El PDF usa la tipografía **Lato** instalada en el sistema (`.ttf`), referenciada por
nombre (sin `@font-face`). Esto es clave: al imprimir con Chromium, las fuentes web
`@font-face` (woff2) y las OpenType `.otf`/variables se incrustan como **Type 3**
(glifos dibujados), y muchos importadores las rechazan con "Error de Lectura: sube un
.pdf con texto (no escaneado/foto)". Con una fuente TrueType del sistema, Chromium
incrusta **CID TrueType** con Unicode y el PDF queda 100% legible como texto.

Verificación rápida: `pdffonts CV_Jeniffer_Mieres_Contreras.pdf` no debe mostrar
ningún `Type 3`; y `pdftotext` debe extraer todo el contenido.

## Enfoque de redacción
La versión final evita los rasgos que suelen "delatar" un texto de IA:
- Sin tríadas ni enumeraciones de tres forzadas; frases de largo variado.
- Sin buzzwords vacíos ("excelencia", "trazabilidad", "sinergia"...).
- Sin guiones largos (—) ni middots (·) como separadores; puntuación española normal.
- Resumen en primera persona, con voz propia y registro chileno.
- Nombres de sección estándar (Perfil, Experiencia laboral, Formación, etc.), sin módulos marketineros ("En cifras", "Mapa de competencias", citas motivacionales).
- Ajuste natural al área de inclusión/bienestar, sin incrustar el nombre de la empresa.

Todo el contenido es real: solo se reordenó y reformuló la información del CV
original. No se agregaron cifras, logros ni datos no verificables.

## Regenerar el PDF
```bash
npm install puppeteer-core
node render.js
```
El HTML define página A4 con márgenes; se imprime con `printBackground: true` y
`preferCSSPageSize: true`.
