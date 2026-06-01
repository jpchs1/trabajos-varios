@echo off
REM ============================================================
REM  Genera el .exe mejorado (con busqueda de productos especificos).
REM  Doble clic para construir. Reemplaza al programa viejo.
REM ============================================================
setlocal

cd /d "%~dp0"

echo Installing Python dependencies...
python -m pip install --upgrade pip
python -m pip install -r requirements.txt

echo Installing Playwright browser (Chromium)...
python -m playwright install chromium

echo Building Windows executable...
python -m PyInstaller ^
  --noconfirm ^
  --clean ^
  --onefile ^
  --windowed ^
  --name "Imporlan Marketplace Search Assistant" ^
  --add-data "data\locations.json;data" ^
  --collect-all playwright ^
  app\main.py

echo.
echo Build complete. The executable is in the dist folder:
echo   dist\Imporlan Marketplace Search Assistant.exe
echo.
echo La primera vez que uses "Buscar productos automaticamente", el programa
echo descargara el navegador Chromium (una sola vez) y te pedira loguearte
echo a Facebook en la ventana que se abre.
pause

endlocal
