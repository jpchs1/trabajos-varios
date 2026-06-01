"""Tests de la lógica pura de auto_fetch (sin abrir navegador)."""

import os
import sys
import unittest

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.auto_fetch import build_search_url, _parse_card_text


class TestParseCardText(unittest.TestCase):
    def test_price_title_location(self):
        title, price, loc = _parse_card_text("$5,000\nMerCruiser 4.5L 250HP\nMiami, FL")
        self.assertEqual(price, "$5,000")
        self.assertEqual(title, "MerCruiser 4.5L 250HP")
        self.assertEqual(loc, "Miami, FL")

    def test_no_location(self):
        title, price, loc = _parse_card_text("$1,200\nMotor fuera de borda")
        self.assertEqual(price, "$1,200")
        self.assertEqual(title, "Motor fuera de borda")
        self.assertEqual(loc, "")

    def test_free_price(self):
        title, price, loc = _parse_card_text("Free\nRemos viejos\nTampa, FL")
        self.assertEqual(price, "Free")
        self.assertEqual(title, "Remos viejos")

    def test_picks_longest_as_title(self):
        # Entre líneas extra (estado/condición), el título es la más descriptiva.
        title, price, loc = _parse_card_text(
            "$8,500\nUsado\nMotor MerCruiser 4.5L MPI completo\nOrlando, FL"
        )
        self.assertEqual(title, "Motor MerCruiser 4.5L MPI completo")
        self.assertEqual(loc, "Orlando, FL")

    def test_empty(self):
        self.assertEqual(_parse_card_text(""), ("", None, ""))


class TestBuildSearchUrl(unittest.TestCase):
    def test_global_search_encodes_query(self):
        url = build_search_url("Mercruiser 4.5L")
        self.assertEqual(
            url,
            "https://www.facebook.com/marketplace/search/?query=Mercruiser+4.5L",
        )

    def test_location_search(self):
        url = build_search_url("Mercruiser 4.5L", location_slug="miami")
        self.assertIn("/marketplace/miami/search?query=Mercruiser+4.5L", url)
        self.assertIn("exact=false", url)

    def test_location_search_with_radius(self):
        url = build_search_url("motor", location_slug="tampa", radius_km=400)
        self.assertIn("radius=400", url)


if __name__ == "__main__":
    unittest.main(verbosity=2)
