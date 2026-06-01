"""Extracción AUTOMÁTICA de productos específicos de Facebook Marketplace.

Abre un navegador real (Playwright) usando **tu propio perfil de Facebook**,
va a la página de resultados de una búsqueda, hace scroll para que carguen los
anuncios y devuelve la lista de productos específicos (link, título, precio,
ubicación) reusando ``app.listing_parser``.

Diseño y seguridad
-------------------
- Usa un *perfil persistente* (``launch_persistent_context``) guardado en una
  carpeta local. La PRIMERA vez te logueás vos a mano en la ventana que se
  abre; a partir de ahí la sesión queda en ese perfil y no hay que volver a
  loguearse. **No se piden ni se guardan tu usuario/contraseña** en el código:
  el login lo hacés vos en el navegador, igual que siempre.
- No envía tus datos a ningún servidor: todo corre en tu máquina.
- Nota: automatizar el navegador sobre Facebook puede ir contra sus Términos
  de Servicio y, si abusás (muchas búsquedas muy rápido), Facebook puede pedir
  verificaciones. Usalo con moderación y bajo tu responsabilidad. Si preferís
  el camino 100% seguro, está el modo "pegar HTML" (``paste_app.py`` /
  ``extract_listings.py``).

Requisitos
----------
    pip install playwright
    playwright install chromium
"""

from __future__ import annotations

import re
import sys
import time
from pathlib import Path
from typing import Callable, Optional
from urllib.parse import quote_plus

from .listing_parser import parse_listings, filter_by_query

# Carpeta del perfil persistente del navegador (donde queda tu sesión de FB).
DEFAULT_PROFILE_DIR = Path.home() / ".imporlan_marketplace_profile"

SEARCH_URL = "https://www.facebook.com/marketplace/search/?query={query}"


class PlaywrightNotInstalled(RuntimeError):
    """Playwright no está disponible en el entorno."""


class NotLoggedIn(RuntimeError):
    """No se detectó una sesión de Facebook iniciada."""


def _is_logged_in(page) -> bool:
    """¿La página actual indica que hay sesión de Facebook iniciada?

    Si seguimos en login/checkpoint o hay un campo de contraseña, NO está
    logueado. Si aparecen elementos típicos de la sesión, asumimos que sí.
    """
    try:
        url = (page.url or "").lower()
    except Exception:
        return False
    if any(x in url for x in ("login", "checkpoint", "/recover")):
        return False
    try:
        if page.query_selector('input[name="pass"]'):
            return False
        markers = (
            'div[role="navigation"]',
            '[aria-label="Tu perfil"]',
            '[aria-label="Your profile"]',
            'a[href*="/marketplace/"]',
        )
        for sel in markers:
            if page.query_selector(sel):
                return True
    except Exception:
        pass
    return "login" not in url


def _safe_content(page, retries: int = 6) -> str:
    """Devuelve el HTML de la página, reintentando si está navegando.

    Facebook hace muchas redirecciones; ``page.content()`` falla si se lo llama
    justo en medio ("page is navigating"). Esperamos a que se estabilice y
    reintentamos; como último recurso leemos el DOM directo.
    """
    last_error: Exception | None = None
    for _ in range(max(1, retries)):
        try:
            try:
                page.wait_for_load_state("networkidle", timeout=8000)
            except Exception:
                pass
            return page.content()
        except Exception as exc:
            last_error = exc
            time.sleep(1.5)
    try:
        return page.evaluate("() => document.documentElement.outerHTML")
    except Exception:
        if last_error:
            raise last_error
        return ""


def build_search_url(query: str, location_slug: str | None = None, radius_km: int | None = None) -> str:
    """Arma la URL de búsqueda de Marketplace.

    Si se pasa ``location_slug`` usa el formato por ciudad (mismo que el resto
    del programa); si no, la búsqueda global ``/marketplace/search/``.
    """
    encoded = quote_plus(query)
    if location_slug:
        url = f"https://www.facebook.com/marketplace/{location_slug}/search?query={encoded}&exact=false"
        if radius_km:
            url += f"&radius={radius_km}"
        return url
    return SEARCH_URL.format(query=encoded)


def _require_playwright():
    try:
        from playwright.sync_api import sync_playwright  # type: ignore

        return sync_playwright
    except Exception as exc:  # pragma: no cover - depende del entorno
        raise PlaywrightNotInstalled(
            "Playwright no está instalado. Instalalo con:\n"
            "    pip install playwright\n"
            "    playwright install chromium"
        ) from exc


def _ensure_chromium(on_status: Optional[Callable[[str], None]] = None) -> None:
    """Descarga el navegador Chromium de Playwright si todavía no está.

    Así el .exe puede ser chico: en vez de empaquetar el navegador (que pesa
    cientos de MB y no se lleva bien con PyInstaller --onefile), lo baja solo
    la primera vez que se usa la búsqueda automática.
    """
    import subprocess

    def status(msg: str) -> None:
        if on_status:
            on_status(msg)

    try:
        from playwright._impl._driver import compute_driver_executable  # type: ignore
    except Exception:
        compute_driver_executable = None  # type: ignore

    status("Verificando el navegador (se descarga solo la primera vez)…")
    # Reutiliza el Python actual; dentro de un .exe usa el módulo de Playwright.
    cmd = [sys.executable, "-m", "playwright", "install", "chromium"]
    if getattr(sys, "frozen", False):
        # En un ejecutable congelado, sys.executable es el .exe; igual sabe
        # interpretar "-m playwright" porque Playwright queda embebido.
        pass
    try:
        subprocess.run(cmd, check=True, capture_output=True, text=True)
        status("Navegador listo.")
    except Exception as exc:  # pragma: no cover - depende del entorno
        # No abortamos: puede que ya esté instalado. Dejamos que el launch lo diga.
        status(f"No se pudo verificar/instalar el navegador automáticamente: {exc}")


# JavaScript que recorre las tarjetas de resultado YA RENDERIZADAS y saca de
# cada una: el link, el texto visible (precio/título/ubicación) y la foto. Es
# mucho más confiable que leer el HTML crudo con expresiones regulares, porque
# lee lo mismo que ve el usuario en pantalla.
_JS_EXTRACT_CARDS = r"""
() => {
  const out = [];
  const seen = new Set();
  const anchors = document.querySelectorAll('a[href*="/marketplace/item/"]');
  for (const a of anchors) {
    const m = a.href.match(/\/marketplace\/item\/(\d+)/);
    if (!m) continue;
    const id = m[1];
    if (seen.has(id)) continue;
    seen.add(id);
    const text = (a.innerText || "").trim();
    const img = a.querySelector('img');
    let photo = "";
    if (img) { photo = img.getAttribute('src') || img.currentSrc || ""; }
    out.push({ id, href: a.href.split('?')[0], text, photo });
  }
  return out;
}
"""


def _parse_card_text(text: str) -> tuple[str, str | None, str]:
    """De el texto visible de una tarjeta saca (título, precio_texto, ubicación).

    En Marketplace el orden típico es: precio, título, ubicación (cada uno en su
    línea). Detectamos el precio por el símbolo de moneda y la ubicación por el
    formato "Ciudad, ST"; lo demás es el título.
    """
    lines = [ln.strip() for ln in (text or "").split("\n") if ln.strip()]
    if not lines:
        return "", None, ""

    price_text = None
    price_idx = None
    for i, ln in enumerate(lines):
        low = ln.lower()
        if re.search(r"[$€£]\s?\d", ln) or low in ("free", "gratis"):
            price_text = ln
            price_idx = i
            break

    rest = [ln for i, ln in enumerate(lines) if i != price_idx]
    location = ""
    title_lines = rest
    if rest and (re.search(r",\s*[A-Za-z]{2,}\.?$", rest[-1]) or re.search(r",\s*[A-Z]{2}$", rest[-1])):
        location = rest[-1]
        title_lines = rest[:-1]

    # El título suele ser la línea más larga de las que quedan (evita textos
    # cortos tipo "Nuevo", "Usado", distancia en millas, etc.).
    title = max(title_lines, key=len) if title_lines else ""
    return title, price_text, location


def _extract_listings_from_dom(page, query: str) -> list[dict]:
    """Lee las tarjetas renderizadas y devuelve listings estructurados reales."""
    from .listing_parser import _parse_price, ITEM_BASE

    try:
        cards = page.evaluate(_JS_EXTRACT_CARDS)
    except Exception:
        return []

    listings: list[dict] = []
    for card in cards or []:
        item_id = str(card.get("id") or "")
        if not item_id:
            continue
        title, price_text, location = _parse_card_text(card.get("text", ""))
        photo = card.get("photo") or ""
        # Ignoramos imágenes vacías o placeholders en base64.
        if photo.startswith("data:"):
            photo = ""
        listings.append(
            {
                "item_id": item_id,
                "url": card.get("href") or ITEM_BASE.format(item_id),
                "title": title,
                "price_text": price_text,
                "price": _parse_price(price_text) if price_text else None,
                "location": location,
                "photo": photo,
                "query": query,
            }
        )
    return listings


def fetch_search_html(
    query: str,
    *,
    location_slug: str | None = None,
    radius_km: int | None = None,
    profile_dir: Path | str = DEFAULT_PROFILE_DIR,
    headless: bool = False,
    scrolls: int = 8,
    scroll_pause: float = 1.5,
    load_timeout_ms: int = 60000,
    on_status: Optional[Callable[[str], None]] = None,
) -> tuple[str, list[dict]]:
    """Abre Marketplace en un navegador real y lee los resultados.

    Devuelve ``(html, listings)``: el HTML crudo (para diagnóstico) y la lista
    de productos extraídos directamente de las tarjetas renderizadas (título,
    precio, ubicación y foto reales).

    Parámetros clave:
    - ``headless=False`` (default): muestra la ventana. Necesario la primera
      vez para que te loguees a Facebook en ese perfil.
    - ``scrolls``: cuántas veces baja para cargar más resultados.
    """
    sync_playwright = _require_playwright()

    def status(msg: str) -> None:
        if on_status:
            on_status(msg)

    profile_dir = Path(profile_dir)
    profile_dir.mkdir(parents=True, exist_ok=True)
    url = build_search_url(query, location_slug, radius_km)

    _ensure_chromium(on_status)
    status(f"Abriendo navegador (perfil: {profile_dir})…")
    with sync_playwright() as p:
        context = p.chromium.launch_persistent_context(
            user_data_dir=str(profile_dir),
            headless=headless,
            viewport={"width": 1366, "height": 900},
        )
        page = context.pages[0] if context.pages else context.new_page()

        # 1) Abrimos Facebook a secas para chequear/forzar el login.
        status("Verificando tu sesión de Facebook…")
        try:
            page.goto("https://www.facebook.com/", timeout=load_timeout_ms, wait_until="domcontentloaded")
        except Exception:
            pass
        time.sleep(2)

        if not _is_logged_in(page):
            status(
                "INICIÁ SESIÓN en la ventana del navegador que se abrió. "
                "Cuando ya estés dentro de Facebook, esperá: el programa sigue solo."
            )
            # Esperamos (hasta 5 min) a que el usuario complete el login a mano.
            deadline = time.time() + 300
            while time.time() < deadline:
                if _is_logged_in(page):
                    break
                time.sleep(2)
            if not _is_logged_in(page):
                context.close()
                raise NotLoggedIn(
                    "No se detectó tu sesión de Facebook. Iniciá sesión en la "
                    "ventana del navegador y volvé a apretar el botón."
                )

        status("Sesión OK. Buscando los productos…")

        # 2) Ya logueados, vamos a la búsqueda y esperamos a que cargue.
        try:
            page.goto(url, timeout=load_timeout_ms, wait_until="domcontentloaded")
        except Exception:
            pass
        time.sleep(3)
        try:
            page.wait_for_selector('a[href*="/marketplace/item/"]', timeout=30000)
        except Exception:
            status("No aparecieron resultados todavía; intento cargar igual…")

        status("Cargando resultados (scroll)…")
        for i in range(max(0, scrolls)):
            try:
                page.mouse.wheel(0, 4000)
            except Exception:
                pass
            time.sleep(scroll_pause)
            status(f"Scroll {i + 1}/{scrolls}…")

        # Subimos al principio para que las fotos de arriba terminen de cargar.
        try:
            page.mouse.wheel(0, -40000)
            time.sleep(1.5)
        except Exception:
            pass

        # Extraemos los datos REALES leyendo las tarjetas renderizadas.
        listings = _extract_listings_from_dom(page, query)
        html = _safe_content(page)
        context.close()

        # Guardamos el HTML para diagnóstico (por si hay que revisar qué devolvió
        # Facebook: resultados, login o página vacía).
        try:
            diag = Path(profile_dir).parent / "ultima_busqueda.html"
            diag.write_text(html, encoding="utf-8", errors="replace")
        except Exception:
            pass

        status(f"Resultados capturados ({len(listings)} tarjetas leídas).")
        return html, listings


def fetch_listings(
    query: str,
    *,
    match: str = "all",
    location_slug: str | None = None,
    radius_km: int | None = None,
    profile_dir: Path | str = DEFAULT_PROFILE_DIR,
    headless: bool = False,
    scrolls: int = 8,
    scroll_pause: float = 1.5,
    on_status: Optional[Callable[[str], None]] = None,
) -> list[dict]:
    """Devuelve la lista de productos específicos para ``query``.

    Lee las tarjetas reales del navegador y aplica el filtro. ``match`` controla
    el filtrado: ``all`` (todos los términos, default), ``any`` o ``off``.

    IMPORTANTE: el filtro es ESTRICTO. Si ningún aviso coincide con lo buscado,
    devuelve lista vacía (mejor no mostrar nada que mostrar productos que no
    corresponden).
    """
    from .listing_parser import sort_by_price

    html, listings = fetch_search_html(
        query,
        location_slug=location_slug,
        radius_km=radius_km,
        profile_dir=profile_dir,
        headless=headless,
        scrolls=scrolls,
        scroll_pause=scroll_pause,
        on_status=on_status,
    )
    # Si por algún motivo la lectura de tarjetas no trajo nada, como respaldo
    # intentamos el viejo parseo del HTML crudo.
    if not listings:
        listings = parse_listings(html, query)

    if query and match != "off":
        # Filtro estricto: solo lo que realmente coincide (sin fallback).
        listings = filter_by_query(listings, query, mode=match)

    return sort_by_price(listings)
