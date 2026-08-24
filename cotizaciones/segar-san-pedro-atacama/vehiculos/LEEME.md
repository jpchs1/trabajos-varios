# Fotos de la flota

`build.mjs` busca acá los archivos que declara `contenido.mjs` y los incrusta en
el HTML como data URI, así el PDF viaja solo y no depende de imágenes sueltas.

Archivos que espera, en `.jpg`, `.jpeg`, `.png` o `.webp`:

| Archivo | Vehículo |
| --- | --- |
| `chevrolet-tahoe.*` | Chevrolet Tahoe |
| `toyota-4runner.*` | Toyota 4Runner SR5 |

Dejá las fotos acá y volvé a correr el build desde la carpeta de la cotización:

```bash
node build.mjs
```

Si falta alguna, la sección de flota se arma igual: esa ficha sale como tira
compacta, sin imagen, y el texto de la sección avisa que las fotos van adjuntas
por separado. No hay recuadros rotos ni huecos en el PDF.

Las fotos se ven mejor apaisadas y con el vehículo completo en cuadro: la
tarjeta las recorta a 190 px de alto en pantalla y 150 px en el PDF.
