#!/bin/bash
# ============================================================
#  Imporlan Marketplace Assistant - Lanzador para macOS
# ------------------------------------------------------------
#  Doble clic para abrir la app. La PRIMERA vez instala todo
#  (puede tardar unos minutos); las siguientes abre directo.
#
#  Nota: si macOS no te deja abrirlo con doble clic, hacé clic
#  derecho sobre el archivo -> "Abrir". Una sola vez.
# ============================================================

# Ir a la carpeta donde está este script.
cd "$(dirname "$0")" || exit 1

# --- Comprobar Python 3 ---
if ! command -v python3 >/dev/null 2>&1; then
    echo
    echo "No se encontró Python 3."
    echo "Instalalo desde https://www.python.org/downloads/ y volvé a intentar."
    echo
    read -r -p "Presioná Enter para cerrar."
    exit 1
fi

# --- Crear el entorno virtual la primera vez ---
if [ ! -x ".venv/bin/python" ]; then
    echo "Creando entorno por primera vez..."
    python3 -m venv .venv
fi

PY=".venv/bin/python"

# --- Instalar dependencias solo una vez ---
if [ ! -f ".venv/.setup_done" ]; then
    echo "Instalando dependencias, esto puede tardar unos minutos..."
    "$PY" -m pip install --upgrade pip
    "$PY" -m pip install -r requirements.txt
    "$PY" -m pip install playwright beautifulsoup4
    echo "Descargando el navegador para la búsqueda automática..."
    "$PY" -m playwright install chromium
    echo "listo" > ".venv/.setup_done"
fi

# --- Abrir la app gráfica (app completa con todas las mejoras) ---
echo "Abriendo la aplicación..."
"$PY" app/main.py

status=$?
if [ $status -ne 0 ]; then
    echo
    echo "La aplicación se cerró con un error (código $status). Revisá el mensaje de arriba."
    read -r -p "Presioná Enter para cerrar."
fi
