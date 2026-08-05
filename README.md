# trabajos-varios
Varios Trabajos generales, de todo un poco.

## Proyectos

- **[Taller Automotriz Vittorio](taller-vittorio/)** — Gestión del taller en reemplazo de la planilla Excel: cotizaciones con catálogo de precios y totales en vivo, ficha por cliente y por patente con historial mecánico, avisos de seguimiento y mantención, fotos y firma del cliente, documento imprimible en PDF y enlace de sólo lectura para compartir. En línea en [`jpchs1.github.io/trabajos-varios/taller-vittorio/`](https://jpchs1.github.io/trabajos-varios/taller-vittorio/).
- **[Reclamo público — Puerto Columbo S.A.](reclamo-puerto-columbo/)** — Reclamo público y solicitud formal de revisión por cobros no aclarados (Factura Electrónica Nº 551879). Versión web en [`reclamo-puerto-columbo/index.html`](reclamo-puerto-columbo/index.html); publicable en `https://jpchs1.github.io/trabajos-varios/reclamo-puerto-columbo/`.
- **[Propuestas Tourevo](propuestas/)** — Cotizaciones de viaje en HTML, una carpeta por cliente. Actual: [Crystal Low — Torres del Paine](propuestas/crystal-low-torres-del-paine/) (COT-2026-0158).

## Deploy por FTP

Las propuestas se publican por FTP con [`scripts/deploy-ftp.sh`](scripts/deploy-ftp.sh). El script corre en dos lugares:

- **Automático:** el workflow [`deploy-ftp.yml`](.github/workflows/deploy-ftp.yml) lo ejecuta en cada push a `main` que toque `propuestas/`. Mientras falten los secrets, el workflow se salta el deploy sin fallar.
- **A mano:** exportás las variables y lo corrés vos (necesita `lftp`).

Configuración, en *Settings → Secrets and variables → Actions*:

| Dónde | Nombre | Para qué |
| --- | --- | --- |
| Secret | `FTP_HOST` | Servidor, por ejemplo `ftp.tourevo.cl` |
| Secret | `FTP_USER` | Usuario FTP |
| Secret | `FTP_PASSWORD` | Contraseña |
| Variable | `FTP_REMOTE_DIR` | Carpeta remota, por ejemplo `/public_html` (default `/`) |
| Variable | `FTP_LOCAL_DIR` | Carpeta local a publicar (default `propuestas`) |
| Variable | `FTP_PORT` | Puerto (default `21`) |
| Variable | `FTP_USE_TLS` | `false` si tu hosting no soporta FTPS (default `true`) |

El deploy espeja `propuestas/` dentro de `FTP_REMOTE_DIR/propuestas/`, así que no toca el resto del sitio. Para probar sin subir nada, corré el workflow desde la pestaña *Actions* con **dry run** marcado.
