#!/usr/bin/env bash
#
# Sube las propuestas al hosting por FTP.
#
# Lo usa el workflow .github/workflows/deploy-ftp.yml, y tambien sirve para
# correrlo a mano desde tu maquina. Nunca escribas credenciales aca dentro:
# se leen del entorno.
#
#   export FTP_HOST=ftp.tudominio.cl
#   export FTP_USER=usuario
#   export FTP_PASSWORD='...'         # mejor: read -rs FTP_PASSWORD
#   export FTP_REMOTE_DIR=/public_html
#   ./scripts/deploy-ftp.sh
#
# Opcionales:
#   FTP_LOCAL_DIR   carpeta local a publicar   (default: propuestas)
#   FTP_REMOTE_DIR  carpeta remota destino     (default: /)
#   FTP_PORT        puerto                     (default: 21)
#   FTP_USE_TLS     true para forzar FTPS      (default: true)
#   FTP_DRY_RUN     true para simular          (default: false)

set -euo pipefail

LOCAL_DIR="${FTP_LOCAL_DIR:-propuestas}"
REMOTE_DIR="${FTP_REMOTE_DIR:-/}"
PORT="${FTP_PORT:-21}"
USE_TLS="${FTP_USE_TLS:-true}"
DRY_RUN="${FTP_DRY_RUN:-false}"

faltan=""
[ -n "${FTP_HOST:-}" ]     || faltan="$faltan FTP_HOST"
[ -n "${FTP_USER:-}" ]     || faltan="$faltan FTP_USER"
[ -n "${FTP_PASSWORD:-}" ] || faltan="$faltan FTP_PASSWORD"
if [ -n "$faltan" ]; then
  echo "Faltan variables de entorno:$faltan" >&2
  exit 1
fi

if ! command -v lftp >/dev/null 2>&1; then
  echo "lftp no esta instalado. En Debian/Ubuntu: sudo apt-get install lftp" >&2
  echo "En macOS: brew install lftp" >&2
  exit 1
fi

cd "$(dirname "$0")/.."

if [ ! -d "$LOCAL_DIR" ]; then
  echo "La carpeta local '$LOCAL_DIR' no existe." >&2
  exit 1
fi

# El destino remoto refleja la carpeta local, para no pisar el resto del sitio.
DEST="${REMOTE_DIR%/}/$(basename "$LOCAL_DIR")"

mirror_flags=(--reverse --only-newer --parallel=4 --verbose)
# .htaccess SI se sube: alguna propuesta lo usa para Basic Auth (ver
# propuestas/kiran-shah-patagonia-rapa-nui/). Solo se excluye basura de
# editor/OS y el .htpasswd, que nunca vive en este repo -- se genera directo
# en el servidor para no dejar el hash de ninguna clave en git publico.
mirror_flags+=(--exclude-glob '.DS_Store' --exclude-glob '.htpasswd' --exclude-glob '*.md')
[ "$DRY_RUN" = "true" ] && mirror_flags+=(--dry-run)

echo "Publicando $LOCAL_DIR/ -> ${FTP_HOST}:${DEST}"
[ "$DRY_RUN" = "true" ] && echo "(simulacion: no se sube nada)"

# set ssl:verify-certificate no permite hosting compartido con certificados
# autofirmados, que es lo habitual en cPanel. Si tu servidor tiene un
# certificado valido, cambialo a 'yes'.
lftp -c "
  set cmd:fail-exit yes;
  set ftp:ssl-force ${USE_TLS};
  set ftp:ssl-protect-data yes;
  set ssl:verify-certificate no;
  set net:max-retries 3;
  set net:timeout 20;
  open -u '${FTP_USER}','${FTP_PASSWORD}' -p '${PORT}' '${FTP_HOST}';
  mkdir -p '${DEST}';
  mirror ${mirror_flags[*]} '${LOCAL_DIR}' '${DEST}';
  bye
"

echo "Listo."
