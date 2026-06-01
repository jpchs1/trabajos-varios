# ============================================================
#  Imporlan Marketplace Assistant - Instalador / Actualizador
# ------------------------------------------------------------
#  Este script descarga la ultima version, instala todo lo que
#  necesita y abre el programa. Correlo de nuevo cuando quieras
#  ACTUALIZAR: siempre baja lo mas reciente.
#
#  Forma facil de usarlo (un solo renglon en PowerShell):
#    irm https://raw.githubusercontent.com/jpchs1/trabajos-varios/main/imporlan-marketplace-assistant/Instalar.ps1 | iex
# ============================================================

$ErrorActionPreference = "Stop"

# Carpeta donde queda instalado (en tu carpeta de usuario).
$Base    = Join-Path $env:USERPROFILE "ImporlanMarketplace"
$AppDir  = Join-Path $Base "imporlan-marketplace-assistant"
$ZipUrl  = "https://codeload.github.com/jpchs1/trabajos-varios/zip/refs/heads/main"
$ZipPath = Join-Path $Base "_descarga.zip"

function Escribir($txt, $color = "White") { Write-Host $txt -ForegroundColor $color }

Escribir ""
Escribir "==== Imporlan Marketplace Assistant ====" "Cyan"
Escribir ""

# --- 1) Comprobar Python ---
$python = $null
foreach ($cmd in @("python", "py")) {
    try {
        $v = & $cmd --version 2>&1
        if ($v -match "Python 3") { $python = $cmd; break }
    } catch { }
}
if (-not $python) {
    Escribir "No se encontro Python 3." "Red"
    Escribir "Instalalo desde https://www.python.org/downloads/ y marca" "Yellow"
    Escribir "la casilla 'Add Python to PATH'. Despues volve a correr este comando." "Yellow"
    Read-Host "`nPresiona Enter para cerrar"
    return
}
Escribir "Python encontrado: $(& $python --version)" "Green"

# --- 2) Descargar la ultima version ---
New-Item -ItemType Directory -Force -Path $Base | Out-Null
Escribir "Descargando la ultima version..." "Cyan"
Invoke-WebRequest -Uri $ZipUrl -OutFile $ZipPath

# Borrar copia anterior del codigo (no toca tu sesion ni tu base de datos,
# que viven en otra carpeta) y descomprimir la nueva.
$Extract = Join-Path $Base "_tmp_extract"
if (Test-Path $Extract) { Remove-Item $Extract -Recurse -Force }
Expand-Archive -Path $ZipPath -DestinationPath $Extract -Force

$Origen = Join-Path $Extract "trabajos-varios-main\imporlan-marketplace-assistant"
if (Test-Path $AppDir) { Remove-Item $AppDir -Recurse -Force }
New-Item -ItemType Directory -Force -Path $AppDir | Out-Null
Copy-Item -Path (Join-Path $Origen "*") -Destination $AppDir -Recurse -Force
Remove-Item $Extract -Recurse -Force
Remove-Item $ZipPath -Force
Escribir "Codigo actualizado en: $AppDir" "Green"

# --- 3) Entorno e instalacion de dependencias ---
Set-Location $AppDir
$VenvPy = Join-Path $AppDir ".venv\Scripts\python.exe"
if (-not (Test-Path $VenvPy)) {
    Escribir "Preparando entorno (solo la primera vez)..." "Cyan"
    & $python -m venv .venv
}

$SetupMarker = Join-Path $AppDir ".venv\.setup_done"
if (-not (Test-Path $SetupMarker)) {
    Escribir "Instalando dependencias (puede tardar unos minutos)..." "Cyan"
    & $VenvPy -m pip install --upgrade pip
    & $VenvPy -m pip install -r requirements.txt
    & $VenvPy -m pip install playwright beautifulsoup4
    Escribir "Descargando el navegador para la busqueda automatica..." "Cyan"
    & $VenvPy -m playwright install chromium
    "ok" | Out-File -FilePath $SetupMarker -Encoding ascii
} else {
    # En una actualizacion, refrescamos dependencias por si cambiaron.
    Escribir "Verificando dependencias..." "Cyan"
    & $VenvPy -m pip install -r requirements.txt | Out-Null
}

# --- 4) Crear un acceso directo en el Escritorio para abrir con doble clic ---
try {
    $Desktop  = [Environment]::GetFolderPath("Desktop")
    $LnkPath  = Join-Path $Desktop "Imporlan Marketplace.lnk"
    $WShell   = New-Object -ComObject WScript.Shell
    $Shortcut = $WShell.CreateShortcut($LnkPath)
    $Shortcut.TargetPath       = $VenvPy
    $Shortcut.Arguments        = "app\main.py"
    $Shortcut.WorkingDirectory = $AppDir
    $Shortcut.Save()
    Escribir "Acceso directo creado en el Escritorio: 'Imporlan Marketplace'" "Green"
} catch {
    Escribir "(No se pudo crear el acceso directo, pero el programa va a abrir igual.)" "Yellow"
}

# --- 5) Abrir el programa ---
Escribir ""
Escribir "Listo. Abriendo el programa..." "Green"
Escribir "La 1ra vez que uses 'Buscar productos automaticamente', logueate" "Yellow"
Escribir "a Facebook en la ventana que se abre. No se guardan contrasenas." "Yellow"
Escribir ""
& $VenvPy "app\main.py"
