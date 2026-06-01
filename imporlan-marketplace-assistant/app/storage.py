from __future__ import annotations

import sqlite3
from datetime import datetime
from pathlib import Path
from typing import Any

from app.config import database_path
from app.ranking import calculate_score


OPPORTUNITY_COLUMNS = [
    "id",
    "link",
    "title",
    "price",
    "currency",
    "location",
    "country",
    "category",
    "notes",
    "priority",
    "status",
    "date_added",
    "last_updated",
]


class OpportunityStorage:
    def __init__(self, path: Path | None = None) -> None:
        self.path = path or database_path()
        self.path.parent.mkdir(parents=True, exist_ok=True)
        self._initialize()

    def _connect(self) -> sqlite3.Connection:
        connection = sqlite3.connect(self.path)
        connection.row_factory = sqlite3.Row
        return connection

    def _initialize(self) -> None:
        with self._connect() as connection:
            connection.execute(
                """
                CREATE TABLE IF NOT EXISTS opportunities (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    link TEXT NOT NULL,
                    title TEXT,
                    price TEXT,
                    currency TEXT,
                    location TEXT,
                    country TEXT,
                    category TEXT,
                    notes TEXT,
                    priority TEXT NOT NULL DEFAULT 'Medium',
                    status TEXT NOT NULL DEFAULT 'New',
                    date_added TEXT NOT NULL,
                    last_updated TEXT NOT NULL
                )
                """
            )
            connection.commit()

    def list_opportunities(self) -> list[dict[str, Any]]:
        with self._connect() as connection:
            rows = connection.execute(
                "SELECT * FROM opportunities ORDER BY datetime(last_updated) DESC, id DESC"
            ).fetchall()

        opportunities = [dict(row) for row in rows]
        for item in opportunities:
            item["score"] = calculate_score(item)
        return opportunities

    def save_opportunity(self, data: dict[str, Any], opportunity_id: int | None = None) -> int:
        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        clean = {
            "link": str(data.get("link") or "").strip(),
            "title": str(data.get("title") or "").strip(),
            "price": str(data.get("price") or "").strip(),
            "currency": str(data.get("currency") or "").strip(),
            "location": str(data.get("location") or "").strip(),
            "country": str(data.get("country") or "").strip(),
            "category": str(data.get("category") or "").strip(),
            "notes": str(data.get("notes") or "").strip(),
            "priority": str(data.get("priority") or "Medium").strip(),
            "status": str(data.get("status") or "New").strip(),
        }
        if not clean["link"]:
            raise ValueError("Link is required.")

        with self._connect() as connection:
            if opportunity_id:
                connection.execute(
                    """
                    UPDATE opportunities
                    SET link = :link,
                        title = :title,
                        price = :price,
                        currency = :currency,
                        location = :location,
                        country = :country,
                        category = :category,
                        notes = :notes,
                        priority = :priority,
                        status = :status,
                        last_updated = :last_updated
                    WHERE id = :id
                    """,
                    {**clean, "last_updated": now, "id": opportunity_id},
                )
                connection.commit()
                return opportunity_id

            cursor = connection.execute(
                """
                INSERT INTO opportunities (
                    link, title, price, currency, location, country, category,
                    notes, priority, status, date_added, last_updated
                )
                VALUES (
                    :link, :title, :price, :currency, :location, :country, :category,
                    :notes, :priority, :status, :date_added, :last_updated
                )
                """,
                {**clean, "date_added": now, "last_updated": now},
            )
            connection.commit()
            return int(cursor.lastrowid)

    def delete_opportunity(self, opportunity_id: int) -> None:
        with self._connect() as connection:
            connection.execute("DELETE FROM opportunities WHERE id = ?", (opportunity_id,))
            connection.commit()

