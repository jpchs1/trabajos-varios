"""Extracción de anuncios ESPECÍFICOS de Facebook Marketplace.

Convierte el HTML de una página de resultados de Marketplace en una lista de
anuncios concretos, cada uno con: ``item_id``, ``url``, ``title``, ``price`` y
``location``.

¿Por qué hace falta esto? Marketplace está detrás de un muro de login y se
arma con JavaScript. Una descarga anónima (sin sesión de Facebook) casi no
trae datos, por eso las versiones anteriores sólo devolvían el link de la
búsqueda *genérica*. La forma robusta y segura de obtener productos
específicos es trabajar sobre el HTML de la página **ya logueada** que el
usuario copia/guarda desde su navegador (no se guardan credenciales ni se
automatiza el login).

Estrategias, de más a menos rica en metadatos:

1. **JSON embebido** de Facebook (lo que viaja dentro de los ``<script>``):
   permite recuperar título + precio + ubicación de cada anuncio.
2. **DOM con BeautifulSoup** (si está instalado): toma los enlaces
   ``/marketplace/item/<id>/`` y el texto cercano.
3. **Regex de respaldo**: como mínimo siempre devuelve los ``item_id`` (los
   links específicos), aunque sin metadatos.

Después se deduplica por ``item_id`` y se puede filtrar por relevancia
respecto de lo que el usuario buscó (p. ej. ``"Mercruiser 4.5L"``).
"""

from __future__ import annotations

import json
import re
from typing import Iterable, Optional

ITEM_BASE = "https://www.facebook.com/marketplace/item/{}/"

# /marketplace/item/<id>/  -> el id numérico del anuncio
_ITEM_ID_RE = re.compile(r"/marketplace/item/(\d+)")

# Cuerpo de string JSON (admite escapes): lo reutilizamos en varios patrones.
_JSON_STR = r'((?:[^"\\]|\\.)*)'

_TITLE_RE = re.compile(r'"marketplace_listing_title":"' + _JSON_STR + r'"')
_PRICE_RE = re.compile(r'"formatted_amount":"' + _JSON_STR + r'"')
_ID_RE = re.compile(r'"id":"(\d{6,})"')
# Ubicación: Facebook la serializa de varias formas según la vista.
_LOCATION_RES = [
    re.compile(r'"location_text":\{"text":"' + _JSON_STR + r'"'),
    re.compile(r'"reverse_geocode":\{"city":"' + _JSON_STR + r'"'),
    re.compile(r'"city_page_title":"' + _JSON_STR + r'"'),
]

# Ventana (en caracteres) para asociar id/precio al título dentro del mismo
# objeto JSON del anuncio. Evita que un título tome datos del anuncio vecino.
_WINDOW = 4000


def _unescape(raw: str) -> str:
    """Convierte el cuerpo de un string JSON crudo a texto real."""
    if raw is None:
        return ""
    try:
        return json.loads('"' + raw + '"')
    except Exception:
        return raw.replace('\\/', '/').replace('\\"', '"').strip()


def _canon(text: str) -> str:
    """Forma canónica para comparar: minúsculas y sólo alfanuméricos.

    Así ``"4.5L"`` y ``"4.5 l"`` quedan ambos como ``"45l"`` y matchean.
    """
    return re.sub(r"[^a-z0-9]", "", (text or "").lower())


def _parse_price(formatted: str) -> Optional[float]:
    """Extrae un número de un precio formateado tipo ``"$1,234"`` -> ``1234.0``."""
    if not formatted:
        return None
    cleaned = re.sub(r"[^\d.,]", "", formatted)
    if not cleaned:
        return None
    # Heurística para separadores de miles / decimales.
    if "," in cleaned and "." in cleaned:
        cleaned = cleaned.replace(",", "") if cleaned.rfind(".") > cleaned.rfind(",") else cleaned.replace(".", "").replace(",", ".")
    elif "," in cleaned:
        # "1,234" -> miles ; "12,5" -> decimal
        cleaned = cleaned.replace(",", "") if len(cleaned.split(",")[-1]) == 3 else cleaned.replace(",", ".")
    try:
        return float(cleaned)
    except ValueError:
        return None


def extract_item_ids(html: str) -> list[str]:
    """Devuelve los ``item_id`` únicos presentes en el HTML, en orden."""
    seen: set[str] = set()
    ids: list[str] = []
    for m in _ITEM_ID_RE.finditer(html or ""):
        iid = m.group(1)
        if iid not in seen:
            seen.add(iid)
            ids.append(iid)
    return ids


def _nearest_before(positions: list[tuple[int, str]], at: int) -> Optional[str]:
    """Último valor cuyo offset es <= ``at`` y está dentro de la ventana."""
    best = None
    for pos, val in positions:
        if pos <= at and (at - pos) <= _WINDOW:
            best = val
        elif pos > at:
            break
    return best


def _nearest_after(positions: list[tuple[int, str]], at: int) -> Optional[str]:
    """Primer valor cuyo offset es >= ``at`` y está dentro de la ventana."""
    for pos, val in positions:
        if pos >= at and (pos - at) <= _WINDOW:
            return val
    return None


def _parse_embedded_json(html: str) -> dict[str, dict]:
    """Mapea item_id -> {title, price, price_text, location} desde el JSON embebido.

    Facebook serializa cada anuncio aprox. así (minificado)::

        {"__typename":"GroupCommerceProductItem","id":"123...",
         "marketplace_listing_title":"Mercruiser 4.5L",
         "listing_price":{"formatted_amount":"$5,000",...},
         "location_text":{"text":"Miami, FL"}, ...}

    El ``id`` aparece antes del título y el precio después, así que para cada
    título buscamos el id más cercano hacia atrás y el precio más cercano hacia
    adelante (acotado por una ventana).
    """
    titles = [(m.start(), _unescape(m.group(1))) for m in _TITLE_RE.finditer(html)]
    if not titles:
        return {}

    ids = [(m.start(), m.group(1)) for m in _ID_RE.finditer(html)]
    prices = [(m.start(), _unescape(m.group(1))) for m in _PRICE_RE.finditer(html)]
    locations: list[tuple[int, str]] = []
    for rgx in _LOCATION_RES:
        locations.extend((m.start(), _unescape(m.group(1))) for m in rgx.finditer(html))
    locations.sort()

    out: dict[str, dict] = {}
    for pos, title in titles:
        item_id = _nearest_before(ids, pos)
        if not item_id:
            continue
        price_text = _nearest_after(prices, pos)
        location = _nearest_after(locations, pos) or _nearest_before(locations, pos)
        # Si ya teníamos este id pero sin título, mejoramos los datos.
        prev = out.get(item_id)
        if prev and prev.get("title") and not title:
            continue
        out[item_id] = {
            "title": title or (prev or {}).get("title", ""),
            "price_text": price_text or (prev or {}).get("price_text"),
            "price": _parse_price(price_text) if price_text else (prev or {}).get("price"),
            "location": location or (prev or {}).get("location", ""),
        }
    return out


def _parse_dom(html: str) -> dict[str, dict]:
    """Estrategia con BeautifulSoup para HTML ya renderizado (best-effort)."""
    try:
        from bs4 import BeautifulSoup  # type: ignore
    except Exception:
        return {}

    soup = BeautifulSoup(html, "html.parser")
    out: dict[str, dict] = {}
    for a in soup.find_all("a", href=True):
        m = _ITEM_ID_RE.search(a["href"])
        if not m:
            continue
        item_id = m.group(1)
        text = " ".join(a.get_text(" ", strip=True).split())
        if item_id not in out or (text and not out[item_id].get("title")):
            price_text = None
            pm = re.search(r"[$€£]\s?[\d.,]+", text)
            if pm:
                price_text = pm.group(0)
            out[item_id] = {
                "title": text,
                "price_text": price_text,
                "price": _parse_price(price_text) if price_text else None,
                "location": "",
            }
    return out


def parse_listings(html: str, query: str = "") -> list[dict]:
    """Extrae anuncios específicos del HTML de Marketplace.

    Devuelve una lista de dicts con: ``item_id``, ``url``, ``title``,
    ``price``, ``price_text``, ``location`` y ``query``. Siempre devuelve, como
    mínimo, los enlaces específicos encontrados (aunque falten metadatos).
    """
    html = html or ""
    meta = _parse_embedded_json(html)
    if not meta:
        meta = _parse_dom(html)

    listings: list[dict] = []
    seen: set[str] = set()
    # Unimos los ids de los enlaces con los del JSON para no perder ninguno.
    all_ids = list(meta.keys())
    for iid in extract_item_ids(html):
        if iid not in meta:
            all_ids.append(iid)

    for item_id in all_ids:
        if item_id in seen:
            continue
        seen.add(item_id)
        info = meta.get(item_id, {})
        title = info.get("title") or query or ""
        listings.append(
            {
                "item_id": item_id,
                "url": ITEM_BASE.format(item_id),
                "title": title,
                "price": info.get("price"),
                "price_text": info.get("price_text"),
                "location": info.get("location", ""),
                "query": query,
            }
        )
    return listings


def query_terms(query: str) -> list[str]:
    """Tokeniza la búsqueda en términos significativos para filtrar."""
    raw = re.split(r"\s+", (query or "").strip())
    terms = [t for t in raw if len(_canon(t)) >= 2]
    return terms


def matches_query(title: str, query: str, mode: str = "all") -> bool:
    """¿El título corresponde a lo buscado?

    ``mode="all"`` exige que estén todos los términos (búsqueda específica);
    ``mode="any"`` con que aparezca uno alcanza. Si no hay términos, pasa todo.
    """
    terms = query_terms(query)
    if not terms:
        return True
    ctitle = _canon(title)
    checks = [_canon(t) in ctitle for t in terms]
    return all(checks) if mode == "all" else any(checks)


def filter_by_query(listings: Iterable[dict], query: str, mode: str = "all") -> list[dict]:
    """Filtra los anuncios para quedarse con los que matchean la búsqueda.

    Esto es lo que convierte una búsqueda *genérica* en una lista de
    *productos específicos*: de todo lo que aparece en la página, deja sólo lo
    que realmente corresponde a lo que el usuario buscó.
    """
    return [it for it in listings if matches_query(it.get("title", ""), query, mode)]
