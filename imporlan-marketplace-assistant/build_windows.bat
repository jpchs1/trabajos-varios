@echo off
setlocal

cd /d "%~dp0"

echo Installing Python dependencies...
python -m pip install --upgrade pip
python -m pip install -r requirements.txt

echo Building Windows executable...
python -m PyInstaller ^
  --noconfirm ^
  --clean ^
  --onefile ^
  --windowed ^
  --name "Imporlan Marketplace Search Assistant" ^
  --add-data "data\locations.json;data" ^
  app\main.py

echo.
echo Build complete. The executable is in the dist folder.
pause

