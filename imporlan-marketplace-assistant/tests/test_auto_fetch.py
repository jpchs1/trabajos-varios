"""Tests de la lógica pura de auto_fetch (sin abrir navegador)."""

import os
import sys
import unittest

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.auto_fetch import build_search_url


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
