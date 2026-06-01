from __future__ import annotations

import json
import os
import sys
from pathlib import Path
from typing import Any


APP_NAME = "Imporlan Marketplace Search Assistant"
APP_SUBTITLE = "USA & Canada Import Opportunity Tracker"

RADIUS_OPTIONS_MILES = [100, 250, 500]
DEFAULT_RADIUS_MILES = 500
DEFAULT_OPEN_DELAY_SECONDS = 3

PRIORITY_OPTIONS = ["High", "Medium", "Low"]
STATUS_OPTIONS = [
    "New",
    "Contacted",
    "Waiting Reply",
    "Negotiating",
    "Discarded",
    "Purchased",
]
CURRENCY_OPTIONS = ["USD", "CAD", "CLP", "EUR", "Other"]


def project_root() -> Path:
    return Path(__file__).resolve().parents[1]


def bundled_root() -> Path:
    if getattr(sys, "frozen", False):
        return Path(getattr(sys, "_MEIPASS", Path(sys.executable).parent))
    return project_root()


def data_dir() -> Path:
    if getattr(sys, "frozen", False):
        appdata = os.getenv("APPDATA")
        base = Path(appdata) if appdata else Path.home()
        path = base / "Imporlan Marketplace Assistant"
    else:
        path = project_root() / "data"
    path.mkdir(parents=True, exist_ok=True)
    return path


def locations_path() -> Path:
    local_path = data_dir() / "locations.json"
    if local_path.exists():
        return local_path

    bundled_path = bundled_root() / "data" / "locations.json"
    if bundled_path.exists():
        return bundled_path

    return project_root() / "data" / "locations.json"


def database_path() -> Path:
    return data_dir() / "opportunities.sqlite"


def load_locations() -> dict[str, list[dict[str, Any]]]:
    path = locations_path()
    with path.open("r", encoding="utf-8") as file:
        return json.load(file)

