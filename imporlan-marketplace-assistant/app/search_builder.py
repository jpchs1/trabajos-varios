from __future__ import annotations

from dataclasses import dataclass
from typing import Iterable
from urllib.parse import quote_plus


@dataclass(frozen=True)
class GeneratedSearch:
    keyword: str
    country: str
    location: str
    radius_miles: int
    radius_km: int
    url: str
    opened: bool = False


def miles_to_km(miles: int) -> int:
    return round(miles * 1.60934)


def normalize_keywords(text: str) -> list[str]:
    seen: set[str] = set()
    keywords: list[str] = []
    for raw_line in text.splitlines():
        keyword = raw_line.strip()
        key = keyword.casefold()
        if keyword and key not in seen:
            keywords.append(keyword)
            seen.add(key)
    return keywords


def build_marketplace_url(location_slug: str, keyword: str, radius_km: int) -> str:
    encoded_keyword = quote_plus(keyword)
    return (
        f"https://www.facebook.com/marketplace/{location_slug}/search"
        f"?query={encoded_keyword}&exact=false&radius={radius_km}"
    )


def generate_searches(
    keywords: Iterable[str],
    countries: Iterable[str],
    locations_by_country: dict[str, list[dict[str, str]]],
    radius_miles: int,
) -> list[GeneratedSearch]:
    selected_countries = set(countries)
    radius_km = miles_to_km(radius_miles)
    searches: list[GeneratedSearch] = []

    for keyword in keywords:
        for country in selected_countries:
            for location in locations_by_country.get(country, []):
                searches.append(
                    GeneratedSearch(
                        keyword=keyword,
                        country=country,
                        location=location["name"],
                        radius_miles=radius_miles,
                        radius_km=radius_km,
                        url=build_marketplace_url(
                            location_slug=location["slug"],
                            keyword=keyword,
                            radius_km=radius_km,
                        ),
                    )
                )

    return searches

