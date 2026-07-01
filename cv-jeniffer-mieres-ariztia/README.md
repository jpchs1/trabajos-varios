# CV · Jeniffer Mieres Contreras

CV en formato profesional y sobrio, redactado con voz natural (que no parezca
generado por IA) para postular en el área de bienestar / personas / equidad e
inclusión (p. ej. el cargo de Coordinador(a) de Equidad e Inclusión de Ariztía,
publicado en su portal de empleos Hiring Room).

## Archivos
- `CV_Jeniffer_Mieres_Contreras.pdf` — **entregable** (2 páginas A4, texto seleccionable / apto para lectores ATS).
- `cv.html` — fuente editable.
- `assets/fonts.css` + `assets/fonts/` — tipografías locales (Fraunces + Inter) para render reproducible.
- `render.js` — genera el PDF desde el HTML.

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
