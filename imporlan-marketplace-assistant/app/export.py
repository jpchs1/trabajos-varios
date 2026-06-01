from __future__ import annotations

import csv
from pathlib import Path
from typing import Any

from openpyxl import Workbook

from app.ranking import calculate_score


EXPORT_COLUMNS = [
    "Score",
    "Priority",
    "Status",
    "Title",
    "Price",
    "Currency",
    "Location",
    "Country",
    "Category",
    "Link",
    "Notes",
    "Date added",
    "Last updated",
]


def _to_export_rows(opportunities: list[dict[str, Any]]) -> list[dict[str, Any]]:
    rows: list[dict[str, Any]] = []
    for item in opportunities:
        rows.append(
            {
                "Score": item.get("score", calculate_score(item)),
                "Priority": item.get("priority", ""),
                "Status": item.get("status", ""),
                "Title": item.get("title", ""),
                "Price": item.get("price", ""),
                "Currency": item.get("currency", ""),
                "Location": item.get("location", ""),
                "Country": item.get("country", ""),
                "Category": item.get("category", ""),
                "Link": item.get("link", ""),
                "Notes": item.get("notes", ""),
                "Date added": item.get("date_added", ""),
                "Last updated": item.get("last_updated", ""),
            }
        )
    return rows


def export_csv(opportunities: list[dict[str, Any]], path: str | Path) -> None:
    rows = _to_export_rows(opportunities)
    with Path(path).open("w", encoding="utf-8-sig", newline="") as file:
        writer = csv.DictWriter(file, fieldnames=EXPORT_COLUMNS)
        writer.writeheader()
        writer.writerows(rows)


def export_excel(opportunities: list[dict[str, Any]], path: str | Path) -> None:
    workbook = Workbook()
    worksheet = workbook.active
    worksheet.title = "Opportunities"

    worksheet.append(EXPORT_COLUMNS)
    for row in _to_export_rows(opportunities):
        worksheet.append([row.get(column, "") for column in EXPORT_COLUMNS])

    for column_cells in worksheet.columns:
        max_length = max(len(str(cell.value or "")) for cell in column_cells)
        adjusted_width = min(max(max_length + 2, 12), 60)
        worksheet.column_dimensions[column_cells[0].column_letter].width = adjusted_width

    workbook.save(path)
