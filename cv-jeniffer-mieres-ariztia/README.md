# CV · Jeniffer Mieres Contreras — Ariztía (Equidad e Inclusión)

CV en formato profesional para postular al cargo **Coordinador(a) de Equidad e
Inclusión** de Ariztía (Región Metropolitana · jornada completa · híbrido),
publicado en el portal de empleos de la empresa (Hiring Room).

## Archivos
- `CV_Jeniffer_Mieres_Contreras_Ariztia_Equidad_Inclusion.pdf` — **entregable** (2 páginas A4, texto seleccionable / apto para lectores ATS).
- `cv.html` — fuente editable del CV.
- `assets/fonts.css` + `assets/fonts/` — tipografías locales (Fraunces + Inter) para render reproducible.

## Regenerar el PDF
Con Node y un Chromium disponible:

```bash
npm install puppeteer-core
node render.js   # ver script de render usado en la sesión
```

El HTML define dos páginas A4 (`@page { size:A4; margin:0 }`); se imprime con
`printBackground: true` y `preferCSSPageSize: true`.

## Contenido
El CV mantiene la información real del CV original y reordena/enfoca la
presentación hacia Equidad e Inclusión (diplomado en Diversidad e Inclusión
USACH, certificación Gatekeepers, evaluación socioeconómica, bienestar
organizacional y gestión de redes). No se agregó información no verificable.
