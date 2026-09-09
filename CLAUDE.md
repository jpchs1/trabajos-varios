# CLAUDE.md — trabajos-varios

Trabajos sueltos, una carpeta por proyecto: el taller de Vittorio, el reclamo a
Puerto Columbo, las propuestas de Tourevo, y lo que vaya cayendo. Ver el
[README](README.md) para qué es cada uno.

Lo de Tourevo que es código vive en [`jpchs1/tourevo-cl`](https://github.com/jpchs1/tourevo-cl),
que tiene su propio CLAUDE.md — más largo y con las reglas de producción. Acá
sólo van documentos: HTML, PDF, notas.

## Trabajo mecánico · no se devuelve

**A JP se le piden decisiones, no ejecuciones.** Si el agente puede hacer algo
—subir un archivo, correr un script, abrir un PR, generar un PDF— lo hace.
Pedirle que abra el File Manager, que arrastre un archivo a una carpeta o que
pegue un heredoc largo en la terminal es devolverle una tarea que la máquina
hace mejor y sin errores de copiado.

Cuando falte un acceso, se dice **con la comprobación hecha** (`env | grep FTP`,
`command -v lftp`) y se pide **una sola cosa, una sola vez**: que la credencial
se cargue en el entorno. Nunca por chat, que queda en el historial. Una
credencial que falta se arregla una vez; no se compensa con trabajo manual todos
los días.

## Deploy por FTP

`scripts/deploy-ftp.sh` publica `propuestas/` por FTPS con `lftp`. Excluye los
`.md`, así que los `NOTAS-INTERNAS.md` de cada carpeta no se publican — están
para la revisión previa al envío, no para el cliente.

Credenciales, siempre del entorno y nunca del código:

```
FTP_HOST   FTP_USER   FTP_PASSWORD   [FTP_PORT=21]   [FTP_REMOTE_DIR]   [FTP_LOCAL_DIR]
```

Viven como secrets de GitHub Actions (*Settings → Secrets and variables →
Actions*), que el workflow `deploy-ftp.yml` lee en cada push a `main` que toque
`propuestas/`.

> **El agente no puede usar FTP**, y cargarle las credenciales no lo arregla. Su
> contenedor sale sólo por HTTPS/443: el puerto 21 (FTP) y el 22 (SSH/SFTP) están
> cerrados. Comprobado — `curl ftp://ftp.gnu.org/` da timeout mientras
> `curl https://ftp.gnu.org/` responde 200 desde el mismo contenedor. Este script
> es para el runner de GitHub y para la máquina de JP; el agente publica
> mergeando, que es lo que dispara el workflow.

Para un archivo suelto no hace falta el script:

```bash
lftp -c "set ftp:ssl-force true; set ssl:verify-certificate no;
  open -u '$FTP_USER','$FTP_PASSWORD' '$FTP_HOST';
  put archivo.pdf -o /public_html/destino/archivo.pdf; bye"
```

## Documentos para clientes y socios

Las propuestas y los documentos de socios comparten el sistema de diseño de la
casa: variables CSS (`--accent`, `--summit`, `--warn`), serif para títulos, sans
para el cuerpo, y un `@media print` con A4 y `break-inside: avoid`. La forma más
barata de arrancar uno nuevo es copiar el `<style>` de
`propuestas/segar-san-pedro-atacama/index.html`.

El PDF se genera con Chromium headless y **se verifica leyendo el PDF, no el
HTML**:

```bash
chromium --headless --no-pdf-header-footer --print-to-pdf=salida.pdf file://$PWD/doc.html
```

Dos reglas que ya costaron caro:

- **No inventar contenido.** Si el documento reproduce una cotización que nos
  mandaron, va tal cual: se puede describir un lugar, nunca en qué consiste el
  servicio cotizado — eso lo sabe quien lo cotizó. Ante la duda, se pregunta.
- **Cuidar lo que se publica de un cliente.** Si el documento va a un tercero,
  el nombre del huésped y las tarifas de otro proveedor no van. Verificarlo
  contra el texto del PDF ya generado, no contra el HTML.
