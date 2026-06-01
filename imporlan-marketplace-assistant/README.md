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

### ¿Por qué hace falta pegar/capturar el HTML?

Facebook Marketplace está detrás de un **muro de login** y arma los resultados
con JavaScript. Una descarga anónima (sin tu sesión de Facebook) casi no trae
datos, por eso antes sólo salía el link de la búsqueda. La forma **robusta y
segura** de obtener productos específicos es trabajar sobre el HTML de la
página **ya logueada** que vos copiás/guardás desde tu navegador. Así **no se
guardan tus credenciales ni se automatiza el login** (no arriesga tu cuenta).

### Cómo usarlo (paso a paso)

1. Abrí Facebook Marketplace en tu navegador, **ya logueado**, y hacé la
   búsqueda (ej.: `Mercruiser 4.5L`). Bajá un poco para que carguen
   resultados.
2. Mostrá el código fuente: **Ctrl+U** → **Ctrl+A** (seleccionar todo) →
   **Ctrl+C** (copiar). O guardá la página con **Ctrl+S**.
3. Obtené la lista de links específicos con cualquiera de estas opciones:

   **a) App gráfica simple (recomendada):**
   ```bash
   python paste_app.py
   ```
   Pegás el HTML, escribís qué buscaste y apretás *"Buscar productos
   específicos"*. Te arma la tabla con título, precio, ubicación y link.
   Doble clic abre el anuncio; botones para copiar todos los links o exportar.

   **b) Línea de comandos:**
   ```bash
   python extract_listings.py pagina_guardada.html --query "Mercruiser 4.5L"
   # o pegando por stdin:
   pbpaste | python extract_listings.py - --query "Mercruiser 4.5L"
   # sólo los links:
   python extract_listings.py pagina.html -q "Mercruiser 4.5L" --links-only
   ```

   **c) En la app completa:** la pestaña de búsqueda con la opción *fetch*
   ahora usa el parser robusto (`_parse_listings` delega en
   `app/listing_parser.py`).

El filtro deja sólo los productos que **corresponden a tu búsqueda**: de todo
lo que aparece en la página, se queda con los que tienen todos los términos
(ej.: tanto `Mercruiser` como `4.5L`). Maneja variantes como `4.5L` ↔ `4.5 L`.

## Características

- Genera búsquedas de Marketplace a partir de términos y sinónimos.
- **Extrae productos específicos** (link, título, precio, ubicación) del HTML
  de una página de resultados — ver arriba.
- **Filtra por relevancia** para convertir una búsqueda genérica en una lista
  de productos concretos.
- Sistema de puntaje (ranking) para priorizar anuncios de interés.
- Guarda oportunidades en SQLite y permite exportar a CSV/JSON.
- Interfaz gráfica (PyQt6) con pestañas.

## Estructura

- `app/config.py` — configuración y constantes.
- `app/search_builder.py` — construcción de URLs de búsqueda.
- `app/listing_parser.py` — **(nuevo)** extracción de anuncios específicos.
- `app/ranking.py` — puntaje de anuncios.
- `app/storage.py` — persistencia en SQLite.
- `app/export.py` — exportación CSV/JSON.
- `app/gui.py` — interfaz gráfica completa.
- `app/main.py` — punto de entrada de la app completa.
- `extract_listings.py` — **(nuevo)** extractor por línea de comandos.
- `paste_app.py` — **(nuevo)** app gráfica simple "pegar HTML → links".
- `tests/test_listing_parser.py` — **(nuevo)** tests del extractor.

## Uso

```bash
pip install -r requirements.txt
python -m app.main        # app completa
python paste_app.py       # app simple "pegar HTML"
```

## Tests

```bash
python -m unittest tests.test_listing_parser -v
```

## Build (Windows)

```bash
build_windows.bat
```

## Nota legal

Usá esta herramienta respetando los Términos de Servicio de Facebook y sólo
sobre contenido al que accedés legítimamente con tu propia sesión. No automatiza
el login ni almacena credenciales.
