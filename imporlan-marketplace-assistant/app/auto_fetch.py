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
) -> str:
    """Abre Marketplace en un navegador real y devuelve el HTML ya cargado.

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

    status(f"Abriendo navegador (perfil: {profile_dir})…")
    with sync_playwright() as p:
        context = p.chromium.launch_persistent_context(
            user_data_dir=str(profile_dir),
            headless=headless,
            viewport={"width": 1366, "height": 900},
        )
        page = context.pages[0] if context.pages else context.new_page()
        status(f"Navegando a: {url}")
        page.goto(url, timeout=load_timeout_ms, wait_until="domcontentloaded")

        # Si no hay sesión, Facebook redirige a login. Avisamos y esperamos.
        if "login" in page.url:
            status(
                "Parece que no estás logueado. Iniciá sesión en la ventana del "
                "navegador; cuando veas los resultados, dejá que continúe."
            )
            # Damos tiempo para que el usuario se loguee manualmente.
            try:
                page.wait_for_url("**/marketplace/**", timeout=180000)
            except Exception:
                pass
            if "login" not in page.url:
                page.goto(url, timeout=load_timeout_ms, wait_until="domcontentloaded")

        status("Cargando resultados (scroll)…")
        for i in range(max(0, scrolls)):
            page.mouse.wheel(0, 4000)
            time.sleep(scroll_pause)
            status(f"Scroll {i + 1}/{scrolls}…")

        html = page.content()
        context.close()
        status("HTML capturado.")
        return html


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

    Combina ``fetch_search_html`` con el parser/filtro. ``match`` controla el
    filtrado: ``all`` (todos los términos, default), ``any`` o ``off``.
    """
    html = fetch_search_html(
        query,
        location_slug=location_slug,
        radius_km=radius_km,
        profile_dir=profile_dir,
        headless=headless,
        scrolls=scrolls,
        scroll_pause=scroll_pause,
        on_status=on_status,
    )
    listings = parse_listings(html, query)
    if query and match != "off":
        filtered = filter_by_query(listings, query, mode=match)
        listings = filtered or listings
    return listings
