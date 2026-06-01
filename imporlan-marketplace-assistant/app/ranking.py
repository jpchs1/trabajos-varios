from __future__ import annotations

import re
from typing import Any


PRIORITY_SCORE = {
    "High": 300,
    "Medium": 200,
    "Low": 100,
}

STATUS_SCORE = {
    "New": 90,
    "Negotiating": 80,
    "Contacted": 55,
    "Waiting Reply": 50,
    "Purchased": 35,
    "Discarded": -200,
}

LOGISTICS_HUBS = [
    "miami",
    "tampa",
    "houston",
    "baltimore",
    "new york",
    "new jersey",
    "los angeles",
    "seattle",
]


def parse_price(value: Any) -> float | None:
    if value is None:
        return None
    text = str(value).strip()
    if not text:
        return None
    cleaned = re.sub(r"[^0-9.,]", "", text)
    if not cleaned:
        return None

    if "," in cleaned and "." in cleaned:
        cleaned = cleaned.replace(",", "")
    elif "," in cleaned and "." not in cleaned:
        cleaned = cleaned.replace(",", ".")

    try:
        return float(cleaned)
    except ValueError:
        return None


def calculate_score(opportunity: dict[str, Any]) -> float:
    priority = str(opportunity.get("priority") or "Low")
    status = str(opportunity.get("status") or "New")
    location = str(opportunity.get("location") or "").casefold()
    country = str(opportunity.get("country") or "").casefold()
    price = parse_price(opportunity.get("price"))

    score = PRIORITY_SCORE.get(priority, 100)
    score += STATUS_SCORE.get(status, 40)

    if country in {"usa", "united states", "us"} and any(hub in location for hub in LOGISTICS_HUBS):
        score += 25

    if price is None:
        score += 5
    else:
        # Keeps price important without letting it dominate priority/status.
        score += max(0, 120 - min(price / 100, 120))

    return round(score, 2)


def sort_best_first(opportunities: list[dict[str, Any]]) -> list[dict[str, Any]]:
    return sorted(
        opportunities,
        key=lambda item: (
            calculate_score(item),
            -(parse_price(item.get("price")) or 999999999),
            str(item.get("date_added") or ""),
        ),
        reverse=True,
    )

