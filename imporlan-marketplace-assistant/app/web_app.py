"""Versión WEB del asistente, para correr en un servidor (VPS) y verla desde
el celular.

Qué hace
--------
- Sirve una página web (apta para celular) con los productos encontrados.
- Busca SOLO cada cierto tiempo (programador) en las ciudades configuradas.
- Permite disparar una búsqueda al instante desde el botón "Buscar ahora".
- Reutiliza el mismo buscador/parser/filtro que la app de escritorio
  (``app.auto_fetch`` + ``app.listing_parser``), así que filtra igual de fino.

Diseño
------
- El navegador (Playwright/Chromium) corre en el servidor en modo headless.
  La sesión de Facebook queda guardada en un *perfil persistente* en disco; el
  login inicial se hace una sola vez (ver DEPLOY_VPS.md, sección login).
- La config (keywords, ciudades, radio, intervalo) y los resultados se guardan
  en archivos JSON dentro de ``data/``.
- No hay base de datos ni dependencias pesadas: Flask + APScheduler.

Variables de entorno
--------------------
- ``IMPORLAN_HEADLESS``: "1" (default) corre sin ventana; "0" muestra ventana
  (útil solo para el login bajo un display virtual).
- ``IMPORLAN_PROFILE_DIR``: carpeta del perfil de Facebook (sesión).
- ``IMPORLAN_PORT``: puerto del servidor web (default 8000).
"""

from __future__ import annotations

import json
import os
import threading
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

from flask import Flask, jsonify, redirect, render_template, request, url_for

from .config import data_dir, load_locations, DEFAULT_RADIUS_MILES
from .search_builder import miles_to_km, normalize_keywords

# --- Rutas de archivos de estado -------------------------------------------

CONFIG_PATH = data_dir() / "web_config.json"
RESULTS_PATH = data_dir() / "web_results.json"

DEFAULT_PROFILE_DIR = Path(
    os.getenv("IMPORLAN_PROFILE_DIR", str(Path.home() / ".imporlan_marketplace_profile"))
)
HEADLESS = os.getenv("IMPORLAN_HEADLESS", "1") != "0"


# --- Config y resultados (persistencia simple en JSON) ----------------------

def _default_config() -> dict[str, Any]:
    return {
        "keywords": ["MerCruiser 4.5L"],
        "cities": [],            # lista de slugs; vacío = todas las de USA+Canadá
        "radius_miles": DEFAULT_RADIUS_MILES,
        "interval_minutes": 180,  # cada cuánto busca sola
        "match": "all",
    }


def load_config() -> dict[str, Any]:
    if CONFIG_PATH.exists():
        try:
            cfg = json.loads(CONFIG_PATH.read_text(encoding="utf-8"))
            base = _default_config()
            base.update(cfg)
            return base
        except Exception:
            pass
    return _default_config()


def save_config(cfg: dict[str, Any]) -> None:
    CONFIG_PATH.write_text(json.dumps(cfg, ensure_ascii=False, indent=2), encoding="utf-8")


def load_results() -> dict[str, Any]:
    if RESULTS_PATH.exists():
        try:
            return json.loads(RESULTS_PATH.read_text(encoding="utf-8"))
        except Exception:
            pass
    return {"updated_at": None, "by_keyword": {}}


def save_results(results: dict[str, Any]) -> None:
    RESULTS_PATH.write_text(json.dumps(results, ensure_ascii=False, indent=2), encoding="utf-8")


# --- Ciudades disponibles ---------------------------------------------------

def all_locations() -> list[dict[str, str]]:
    """Lista plana de todas las ciudades (de todos los países)."""
    out: list[dict[str, str]] = []
    for country_locs in load_locations().values():
        out.extend(country_locs)
    return out


def selected_locations(cfg: dict[str, Any]) -> list[dict[str, str]]:
    """Ciudades elegidas en la config (por slug). Vacío = todas."""
    everything = all_locations()
    slugs = cfg.get("cities") or []
    if not slugs:
        return everything
    by_slug = {loc["slug"]: loc for loc in everything}
    return [by_slug[s] for s in slugs if s in by_slug]


# --- Estado del buscador (en memoria) ---------------------------------------

class SearchState:
    def __init__(self) -> None:
        self.lock = threading.Lock()
        self.running = False
        self.message = "Listo."
        self.last_run: str | None = None

    def set(self, message: str) -> None:
        self.message = message


STATE = SearchState()


def _now_iso() -> str:
    return datetime.now(timezone.utc).astimezone().strftime("%Y-%m-%d %H:%M")


def run_search(reason: str = "manual") -> None:
    """Ejecuta una búsqueda completa (todas las keywords y ciudades).

    Pensada para correr en un hilo. Usa un lock para que no se solapen dos
    búsquedas a la vez (las del programador y las manuales).
    """
    from .auto_fetch import fetch_listings_multi, PlaywrightNotInstalled, NotLoggedIn

    if not STATE.lock.acquire(blocking=False):
        STATE.set("Ya hay una búsqueda en curso; esperá a que termine.")
        return

    STATE.running = True
    try:
        cfg = load_config()
        keywords = normalize_keywords("\n".join(cfg.get("keywords", [])))
        locations = selected_locations(cfg)
        radius_km = miles_to_km(int(cfg.get("radius_miles", DEFAULT_RADIUS_MILES)))
        match = cfg.get("match", "all")

        results = load_results()
        by_keyword = results.get("by_keyword", {})

        for kw in keywords:
            STATE.set(f"Buscando '{kw}' en {len(locations)} ciudades…")
            try:
                listings = fetch_listings_multi(
                    kw,
                    locations,
                    match=match,
                    radius_km=radius_km,
                    profile_dir=DEFAULT_PROFILE_DIR,
                    headless=HEADLESS,
                    on_status=lambda m, kw=kw: STATE.set(f"'{kw}': {m}"),
                )
            except NotLoggedIn:
                STATE.set(
                    "Falta iniciar sesión en Facebook en el servidor. "
                    "Ver DEPLOY_VPS.md (sección login)."
                )
                return
            except PlaywrightNotInstalled as exc:
                STATE.set(f"Falta instalar el navegador: {exc}")
                return
            by_keyword[kw] = {
                "updated_at": _now_iso(),
                "count": len(listings),
                "listings": listings,
            }

        results["by_keyword"] = by_keyword
        results["updated_at"] = _now_iso()
        save_results(results)
        STATE.last_run = results["updated_at"]
        total = sum(v.get("count", 0) for v in by_keyword.values())
        STATE.set(f"Búsqueda terminada ({reason}). {total} productos en total.")
    except Exception as exc:  # pragma: no cover - depende del entorno
        STATE.set(f"Error en la búsqueda: {exc}")
    finally:
        STATE.running = False
        STATE.lock.release()


def run_search_async(reason: str = "manual") -> None:
    threading.Thread(target=run_search, args=(reason,), daemon=True).start()


# --- Programador (búsqueda automática) --------------------------------------

_scheduler = None


def start_scheduler(app: Flask) -> None:
    global _scheduler
    try:
        from apscheduler.schedulers.background import BackgroundScheduler
    except Exception:
        app.logger.warning("APScheduler no instalado: la búsqueda automática queda desactivada.")
        return

    cfg = load_config()
    minutes = max(15, int(cfg.get("interval_minutes", 180)))
    _scheduler = BackgroundScheduler(daemon=True)
    _scheduler.add_job(
        lambda: run_search("automática"),
        "interval",
        minutes=minutes,
        id="busqueda_periodica",
        replace_existing=True,
    )
    _scheduler.start()
    app.logger.info("Programador activo: busca cada %s minutos.", minutes)


# --- App Flask --------------------------------------------------------------

def create_app() -> Flask:
    app = Flask(
        __name__,
        template_folder=str(Path(__file__).parent / "templates"),
    )

    @app.route("/")
    def index():
        cfg = load_config()
        results = load_results()
        return render_template(
            "index.html",
            cfg=cfg,
            results=results,
            locations=all_locations(),
            state=STATE,
            headless=HEADLESS,
        )

    @app.route("/buscar", methods=["POST"])
    def buscar():
        run_search_async("manual")
        return redirect(url_for("index"))

    @app.route("/config", methods=["POST"])
    def config():
        cfg = load_config()
        keywords_text = request.form.get("keywords", "")
        cfg["keywords"] = normalize_keywords(keywords_text)
        cfg["cities"] = request.form.getlist("cities")
        try:
            cfg["radius_miles"] = int(request.form.get("radius_miles", DEFAULT_RADIUS_MILES))
        except Exception:
            cfg["radius_miles"] = DEFAULT_RADIUS_MILES
        try:
            cfg["interval_minutes"] = max(15, int(request.form.get("interval_minutes", 180)))
        except Exception:
            cfg["interval_minutes"] = 180
        save_config(cfg)
        # Reprogramar con el nuevo intervalo.
        if _scheduler is not None:
            try:
                _scheduler.reschedule_job(
                    "busqueda_periodica", trigger="interval", minutes=cfg["interval_minutes"]
                )
            except Exception:
                pass
        return redirect(url_for("index"))

    @app.route("/api/results")
    def api_results():
        return jsonify(load_results())

    @app.route("/api/status")
    def api_status():
        return jsonify({
            "running": STATE.running,
            "message": STATE.message,
            "last_run": STATE.last_run,
        })

    return app


def main() -> None:
    app = create_app()
    start_scheduler(app)
    port = int(os.getenv("IMPORLAN_PORT", "8000"))
    app.run(host="0.0.0.0", port=port)


if __name__ == "__main__":
    main()
