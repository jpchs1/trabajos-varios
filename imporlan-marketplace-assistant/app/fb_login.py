"""Login de Facebook en el servidor (una sola vez).

Abre un navegador CON ventana apuntando a Facebook para que inicies sesión.
Está pensado para correr en el VPS bajo un display virtual (Xvfb) y verlo desde
el navegador con noVNC (ver DEPLOY_VPS.md). La sesión queda guardada en el
perfil persistente, así la búsqueda automática ya entra logueada.

Uso:
    IMPORLAN_PROFILE_DIR=/ruta/al/perfil python -m app.fb_login
"""

from __future__ import annotations

import os
import time
from pathlib import Path

PROFILE = Path(
    os.getenv("IMPORLAN_PROFILE_DIR", str(Path.home() / ".imporlan_marketplace_profile"))
)


def main() -> None:
    from playwright.sync_api import sync_playwright

    PROFILE.mkdir(parents=True, exist_ok=True)
    print(f"Perfil de Facebook en: {PROFILE}")
    with sync_playwright() as p:
        ctx = p.chromium.launch_persistent_context(
            user_data_dir=str(PROFILE),
            headless=False,
            viewport={"width": 1280, "height": 800},
        )
        page = ctx.pages[0] if ctx.pages else ctx.new_page()
        try:
            page.goto("https://www.facebook.com/", wait_until="domcontentloaded")
        except Exception:
            pass
        print(
            "\n>>> Iniciá sesión en la ventana del navegador (vía noVNC).\n"
            ">>> Cuando ya estés DENTRO de Facebook, cortá con Ctrl+C.\n"
        )
        try:
            while True:
                time.sleep(2)
        except KeyboardInterrupt:
            print("Cerrando… la sesión quedó guardada en el perfil.")
        finally:
            try:
                ctx.close()
            except Exception:
                pass


if __name__ == "__main__":
    main()
