"""Tests del extractor de productos específicos.

Usan HTML de muestra con la forma en que Facebook serializa los anuncios
(JSON embebido) y también enlaces sueltos, para verificar que se obtienen los
links específicos con su título/precio y que el filtro por búsqueda funciona.
"""

import os
import sys
import unittest

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.listing_parser import (
    extract_item_ids,
    parse_listings,
    filter_by_query,
    matches_query,
    sort_by_price,
    _parse_price,
)


# HTML minificado al estilo del que viaja dentro de los <script> de Facebook.
SAMPLE_JSON_HTML = (
    '<html><body><script>'
    '{"__typename":"GroupCommerceProductItem","id":"1301127085457538",'
    '"marketplace_listing_title":"Mercruiser 4.5L 250HP","listing_price":'
    '{"formatted_amount":"$5,000","amount":"5000","currency":"USD"},'
    '"location_text":{"text":"Miami, FL"}}'
    '{"__typename":"GroupCommerceProductItem","id":"2002002002002002",'
    '"marketplace_listing_title":"Volvo Penta 350 Mag","listing_price":'
    '{"formatted_amount":"$3,200","amount":"3200","currency":"USD"},'
    '"location_text":{"text":"Tampa, FL"}}'
    '{"__typename":"GroupCommerceProductItem","id":"3003003003003003",'
    '"marketplace_listing_title":"Bicicleta de montaña","listing_price":'
    '{"formatted_amount":"$150","amount":"150","currency":"USD"}}'
    '</script>'
    '<a href="/marketplace/item/1301127085457538/">link</a>'
    '</body></html>'
)

SAMPLE_DOM_HTML = (
    '<html><body>'
    '<a href="/marketplace/item/4004004004004004/?ref=search">'
    'Mercruiser 4.5L Sterndrive $7,500</a>'
    '<a href="/marketplace/item/5005005005005005/">Yamaha 90HP $2,000</a>'
    '</body></html>'
)


class TestItemIds(unittest.TestCase):
    def test_extract_unique_ids_in_order(self):
        html = (
            'x /marketplace/item/111/ y /marketplace/item/222/?ref=z '
            '/marketplace/item/111/ again'
        )
        self.assertEqual(extract_item_ids(html), ["111", "222"])

    def test_no_ids(self):
        self.assertEqual(extract_item_ids("<html>nada</html>"), [])


class TestParsePrice(unittest.TestCase):
    def test_thousands(self):
        self.assertEqual(_parse_price("$5,000"), 5000.0)

    def test_plain(self):
        self.assertEqual(_parse_price("$150"), 150.0)

    def test_decimal_comma(self):
        self.assertEqual(_parse_price("12,50"), 12.5)

    def test_empty(self):
        self.assertIsNone(_parse_price(""))


class TestParseEmbeddedJson(unittest.TestCase):
    def setUp(self):
        self.listings = parse_listings(SAMPLE_JSON_HTML, "Mercruiser 4.5L")
        self.by_id = {x["item_id"]: x for x in self.listings}

    def test_finds_all_items(self):
        self.assertEqual(len(self.listings), 3)

    def test_specific_link_format(self):
        it = self.by_id["1301127085457538"]
        self.assertEqual(
            it["url"],
            "https://www.facebook.com/marketplace/item/1301127085457538/",
        )

    def test_title_and_price_and_location(self):
        it = self.by_id["1301127085457538"]
        self.assertEqual(it["title"], "Mercruiser 4.5L 250HP")
        self.assertEqual(it["price"], 5000.0)
        self.assertEqual(it["price_text"], "$5,000")
        self.assertEqual(it["location"], "Miami, FL")

    def test_second_item_not_cross_contaminated(self):
        it = self.by_id["2002002002002002"]
        self.assertEqual(it["title"], "Volvo Penta 350 Mag")
        self.assertEqual(it["price"], 3200.0)
        self.assertEqual(it["location"], "Tampa, FL")


class TestParseDom(unittest.TestCase):
    def test_dom_fallback(self):
        # Si bs4 no está instalado, igual deben salir los links por regex.
        listings = parse_listings(SAMPLE_DOM_HTML, "Mercruiser 4.5L")
        ids = {x["item_id"] for x in listings}
        self.assertIn("4004004004004004", ids)
        self.assertIn("5005005005005005", ids)


class TestFiltering(unittest.TestCase):
    def test_matches_query_all_terms(self):
        self.assertTrue(matches_query("Mercruiser 4.5L 250HP", "Mercruiser 4.5L"))
        self.assertFalse(matches_query("Volvo Penta 350 Mag", "Mercruiser 4.5L"))

    def test_canonical_match_handles_spacing(self):
        # "4.5L" en la búsqueda y "4.5 L" en el título deben matchear.
        self.assertTrue(matches_query("Motor 4.5 L Mercruiser", "Mercruiser 4.5L"))

    def test_filter_keeps_only_specific(self):
        listings = parse_listings(SAMPLE_JSON_HTML, "Mercruiser 4.5L")
        filtered = filter_by_query(listings, "Mercruiser 4.5L", mode="all")
        self.assertEqual(len(filtered), 1)
        self.assertEqual(filtered[0]["item_id"], "1301127085457538")

    def test_filter_any_mode(self):
        listings = parse_listings(SAMPLE_JSON_HTML, "Mercruiser Volvo")
        filtered = filter_by_query(listings, "Mercruiser Volvo", mode="any")
        ids = {x["item_id"] for x in filtered}
        self.assertEqual(ids, {"1301127085457538", "2002002002002002"})

    def test_empty_query_passes_all(self):
        listings = parse_listings(SAMPLE_JSON_HTML, "")
        self.assertEqual(len(filter_by_query(listings, "")), 3)

    def test_number_must_be_exact_not_substring(self):
        # "4.5" no debe matchear con "450" ni con "14.5".
        self.assertFalse(matches_query("Mercruiser 450 HP", "Mercruiser 4.5L"))
        self.assertFalse(matches_query("Mercruiser 14.5 algo", "Mercruiser 4.5L"))
        self.assertTrue(matches_query("Mercruiser 4.5 L MPI", "Mercruiser 4.5L"))

    def test_displacement_comma_decimal(self):
        # "4,5" (coma) en el título también cuenta como 4.5.
        self.assertTrue(matches_query("Motor Mercruiser 4,5 litros", "Mercruiser 4.5L"))

    def test_motor_synonym_engine(self):
        # "motor" en la búsqueda acepta "engine" en el título (y viceversa).
        self.assertTrue(matches_query("Mercruiser 4.5L engine complete", "motor mercruiser 4.5L"))
        self.assertTrue(matches_query("Mercruiser 4.5L motor", "engine mercruiser 4.5L"))

    def test_motor_query_rejects_wrong_displacement(self):
        # "motor mercruiser 4.5L" no debe aceptar un 5.0L aunque sea motor mercruiser.
        self.assertFalse(matches_query("Mercruiser 5.0L engine", "motor mercruiser 4.5L"))


class TestSortByPrice(unittest.TestCase):
    def test_sorts_ascending_none_last(self):
        items = [
            {"url": "a", "price": 500.0},
            {"url": "b", "price": None},
            {"url": "c", "price": 120.0},
        ]
        ordered = [x["url"] for x in sort_by_price(items)]
        self.assertEqual(ordered, ["c", "a", "b"])


if __name__ == "__main__":
    unittest.main(verbosity=2)
