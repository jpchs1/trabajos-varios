# Imporlan Marketplace Search Assistant

Asistente de escritorio para **buscar oportunidades en Facebook Marketplace**
(motores marinos, repuestos, etc.).

## Novedad: links a productos ESPECÍFICOS (no sólo búsquedas genéricas)

Antes el programa generaba la URL de una **búsqueda genérica**
(`facebook.com/marketplace/search?query=Mercruiser+4.5L`). Ahora puede
devolverte la **lista de anuncios concretos** con sus links directos, como:

```
https://www.facebook.com/marketplace/item/1301127085457538/
```

### ¿Por qué hace falta tu sesión de Facebook?

Facebook Marketplace está detrás de un **muro de login** y arma los resultados
con JavaScript. Una descarga anónima (sin tu sesión) casi no trae datos, por
eso antes sólo salía el link de la búsqueda genérica. Hay dos formas de obtener
los productos específicos, según cuánta comodidad vs. seguridad prefieras.

### Inicio fácil con doble clic (sin tocar la consola) 🖱️

- **Windows:** doble clic en **`Iniciar (Windows).bat`**.
- **macOS:** doble clic en **`Iniciar (Mac).command`** (si no te deja, clic
  derecho → *Abrir*, una sola vez).

La **primera vez** instala todo solo (Python debe estar instalado de antes;
puede tardar unos minutos) y abre la app. Las veces siguientes abre directo.

### Opción 1 — Modo automático con tu navegador (más cómodo) 🤖

El programa abre un navegador (Playwright) con **tu propio perfil de
Facebook**, hace la búsqueda, baja para cargar resultados y extrae los
productos solo. **La primera vez** te logueás a mano en la ventana que se abre;
después la sesión queda guardada en un perfil local (`~/.imporlan_marketplace_profile`).
**No se piden ni se guardan credenciales en el código** — el login lo hacés vos
en el navegador.

Instalación (una vez):
```bash
pip install playwright
playwright install chromium
```

Uso:
```bash
# Línea de comandos:
python extract_listings.py --auto --query "Mercruiser 4.5L"
python extract_listings.py --auto -q "Mercruiser 4.5L" --scrolls 12 --links-only

# App gráfica: botón "🤖 Buscar automático (navegador)"
python paste_app.py
```

> ⚠️ Aviso: automatizar el navegador sobre Facebook puede ir contra sus
> Términos de Servicio y, si hacés muchas búsquedas muy seguido, Facebook puede
> pedirte verificaciones o limitar la cuenta. Usalo con moderación. Si querés
> el camino 100% seguro, usá la Opción 2.

### Opción 2 — Pegar/Capturar el HTML (100% seguro)

No automatiza nada: vos copiás el HTML de la página ya logueada y el programa
lo procesa.

1. Abrí Facebook Marketplace en tu navegador, **ya logueado**, y hacé la
   búsqueda (ej.: `Mercruiser 4.5L`). Bajá un poco para que carguen
   resultados.
2. Mostrá el código fuente: **Ctrl+U** → **Ctrl+A** (seleccionar todo) →
   **Ctrl+C** (copiar). O guardá la página con **Ctrl+S**.
3. Obtené la lista de links específicos:

   **App gráfica:**
   ```bash
   python paste_app.py
   ```
   Pegás el HTML, escribís qué buscaste y apretás *"Buscar productos
   específicos"*. Tabla con título, precio, ubicación y link. Doble clic abre
   el anuncio; botones para copiar todos los links o exportar.

   **Línea de comandos:**
   ```bash
   python extract_listings.py pagina_guardada.html --query "Mercruiser 4.5L"
   # o pegando por stdin:
   pbpaste | python extract_listings.py - --query "Mercruiser 4.5L"
   # sólo los links:
   python extract_listings.py pagina.html -q "Mercruiser 4.5L" --links-only
   ```

En ambas opciones, el filtro deja sólo los productos que **corresponden a tu
búsqueda**: de todo lo que aparece, se queda con los que tienen todos los
términos (ej.: tanto `Mercruiser` como `4.5L`). Maneja variantes como
`4.5L` ↔ `4.5 L`.

## Características

- Genera búsquedas de Marketplace a partir de términos y sinónimos.
- **Extrae productos específicos** (link, título, precio, ubicación), ya sea
  automáticamente con tu navegador o desde el HTML que pegás — ver arriba.
- **Filtra por relevancia** para convertir una búsqueda genérica en una lista
  de productos concretos.
- Sistema de puntaje (ranking) para priorizar anuncios de interés.
- Guarda oportunidades en SQLite y permite exportar a CSV/JSON.
- Interfaz gráfica (PyQt6) con pestañas.

## Estructura

- `app/config.py` — configuración y constantes.
- `app/search_builder.py` — construcción de URLs de búsqueda.
- `app/listing_parser.py` — **(nuevo)** extracción de anuncios específicos.
- `app/auto_fetch.py` — **(nuevo)** búsqueda automática con el navegador (Playwright).
- `app/ranking.py` — puntaje de anuncios.
- `app/storage.py` — persistencia en SQLite.
- `app/export.py` — exportación CSV/JSON.
- `app/gui.py` — interfaz gráfica completa.
- `app/main.py` — punto de entrada de la app completa.
- `extract_listings.py` — **(nuevo)** extractor por línea de comandos (HTML o `--auto`).
- `paste_app.py` — **(nuevo)** app gráfica simple: pegar HTML o búsqueda automática.
- `tests/` — **(nuevo)** tests del extractor y de la construcción de URLs.

## Uso

```bash
pip install -r requirements.txt
python -m app.main        # app completa
python paste_app.py       # app simple "pegar HTML"
```

## Tests

```bash
python -m unittest discover -s tests -v
```

## Generar el .exe (reemplaza al programa viejo)

La app completa (`app/main.py`, la de pestañas) **ya trae integrada** la
búsqueda de productos específicos: botón **"Buscar productos automáticamente"**
en la pestaña *1. Search*. Para empaquetarla en un único `.exe` (igual al que
tenías, pero mejorado):

1. En una PC **Windows** con Python instalado, doble clic en
   **`build_windows.bat`** (o ejecutalo desde la consola).
2. Esperá: instala dependencias, baja el navegador y compila.
3. El ejecutable queda en `dist\Imporlan Marketplace Search Assistant.exe`.
4. Reemplazá tu `.exe` viejo por este. Es **un solo programa**.

La primera vez que uses *"Buscar productos automáticamente"*, el programa
descarga Chromium (una sola vez) y te pide loguearte a Facebook en la ventana
que se abre. No se guardan credenciales.

> El `.exe` sólo se puede compilar en Windows. Acá en la nube se preparó todo
> el código y el script; la compilación final la hacés en tu máquina.

## Nota legal

Usá esta herramienta respetando los Términos de Servicio de Facebook y sólo
sobre contenido al que accedés legítimamente con tu propia sesión. La
herramienta **no almacena tus credenciales**: en el modo automático el login lo
hacés vos en el navegador y la sesión queda en un perfil local tuyo. Tené en
cuenta que el modo automático (Opción 1) puede ir contra los Términos de
Servicio de Facebook; usalo bajo tu responsabilidad y con moderación.
