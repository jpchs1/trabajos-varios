@echo off
REM ============================================================
REM  Imporlan Marketplace Assistant - Lanzador para Windows
REM ------------------------------------------------------------
REM  Doble clic para abrir la app. La PRIMERA vez instala todo
REM  (puede tardar unos minutos); las siguientes abre directo.
REM ============================================================

setlocal
cd /d "%~dp0"

REM --- Comprobar que Python este instalado ---
where python >nul 2>nul
if errorlevel 1 (
    echo.
    echo No se encontro Python.
    echo Instalalo desde https://www.python.org/downloads/
    echo IMPORTANTE: marca la casilla "Add Python to PATH" durante la instalacion.
    echo.
    pause
    exit /b 1
)

REM --- Crear el entorno virtual la primera vez ---
if not exist ".venv\Scripts\python.exe" (
    echo Creando entorno por primera vez...
    python -m venv .venv
)

set "PY=.venv\Scripts\python.exe"

REM --- Instalar dependencias solo una vez (marcador .setup_done) ---
if not exist ".venv\.setup_done" (
    echo Instalando dependencias, esto puede tardar unos minutos...
    "%PY%" -m pip install --upgrade pip
    "%PY%" -m pip install -r requirements.txt
    "%PY%" -m pip install playwright PyQt6 beautifulsoup4
    echo Descargando el navegador para la busqueda automatica...
    "%PY%" -m playwright install chromium
    echo listo> ".venv\.setup_done"
)

REM --- Abrir la app grafica ---
echo Abriendo la aplicacion...
"%PY%" paste_app.py

if errorlevel 1 (
    echo.
    echo La aplicacion se cerro con un error. Revisa el mensaje de arriba.
    pause
)

endlocal
