#!/usr/bin/env python3
"""Extractor de productos ESPECÍFICOS de Facebook Marketplace (línea de comandos).

Convierte el HTML de una página de resultados (la búsqueda genérica, p. ej.
"Mercruiser 4.5L") en una lista de **links a productos concretos** como
``https://www.facebook.com/marketplace/item/1301127085457538/``.

Cómo se usa (no se guardan credenciales ni se automatiza el login):

  1. Abrí Facebook Marketplace en tu navegador, YA logueado, y hacé la
     búsqueda (ej.: "Mercruiser 4.5L"). Bajá un poco para que carguen
     resultados.
  2. Guardá la página (Ctrl+S -> "Página web completa") o mostrá el código
     fuente (Ctrl+U) y copialo/guardalo en un archivo .html.
  3. Ejecutá:

       python extract_listings.py pagina_guardada.html --query "Mercruiser 4.5L"

     O pegando por stdin:

       pbpaste | python extract_listings.py - --query "Mercruiser 4.5L"

Opciones de salida: imprime los links en pantalla y, opcionalmente, exporta a
CSV/JSON reutilizando el resto del programa.
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

# Permite ejecutar el script directamente (python extract_listings.py ...)
sys.path.insert(0, str(Path(__file__).resolve().parent))

from app.listing_parser import parse_listings, filter_by_query  # noqa: E402

try:
    from app.ranking import rank_listings  # noqa: E402
except Exception:  # pragma: no cover - ranking es opcional
    rank_listings = None

try:
    from app.export import export_csv, export_json  # noqa: E402
except Exception:  # pragma: no cover - export es opcional
    export_csv = export_json = None


def _read_input(source: str) -> str:
    if source == "-":
        return sys.stdin.read()
    return Path(source).read_text(encoding="utf-8", errors="replace")


def main(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(
        description="Extrae links a productos específicos de un HTML de Facebook Marketplace.",
    )
    parser.add_argument(
        "html",
        help="Archivo HTML guardado de la búsqueda, o '-' para leer de stdin.",
    )
    parser.add_argument(
        "--query", "-q", default="",
        help="Lo que buscaste (ej.: 'Mercruiser 4.5L'). Filtra a los productos relevantes.",
    )
    parser.add_argument(
        "--match", choices=["all", "any", "off"], default="all",
        help="all: deben estar todos los términos (default); any: al menos uno; off: no filtrar.",
    )
    parser.add_argument("--csv", help="Exportar resultados a este archivo CSV.")
    parser.add_argument("--json", help="Exportar resultados a este archivo JSON.")
    parser.add_argument(
        "--links-only", action="store_true",
        help="Imprimir sólo los links, uno por línea.",
    )
    args = parser.parse_args(argv)

    html = _read_input(args.html)
    listings = parse_listings(html, args.query)

    if args.query and args.match != "off":
        listings = filter_by_query(listings, args.query, mode=args.match)

    if rank_listings is not None:
        listings = rank_listings(listings)

    if not listings:
        print("No se encontraron productos específicos en el HTML.", file=sys.stderr)
        print(
            "Sugerencia: asegurate de estar logueado en Facebook y de haber "
            "bajado en la página para que carguen los resultados antes de "
            "guardar/copiar el HTML.",
            file=sys.stderr,
        )
        return 1

    if args.links_only:
        for it in listings:
            print(it["url"])
    else:
        print(f"\n{len(listings)} producto(s) específico(s) encontrados"
              + (f" para '{args.query}'" if args.query else "") + ":\n")
        for i, it in enumerate(listings, 1):
            price = it.get("price_text") or ("—" if it.get("price") is None else it["price"])
            loc = it.get("location") or "—"
            title = it.get("title") or "(sin título)"
            print(f"{i:>2}. {title}")
            print(f"     Precio: {price}   |   Ubicación: {loc}")
            print(f"     {it['url']}\n")

    if args.csv and export_csv is not None:
        export_csv(listings, args.csv)
        print(f"CSV exportado a: {args.csv}", file=sys.stderr)
    if args.json and export_json is not None:
        export_json(listings, args.json)
        print(f"JSON exportado a: {args.json}", file=sys.stderr)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
