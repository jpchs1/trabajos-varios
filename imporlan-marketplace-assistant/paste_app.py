#!/usr/bin/env python3
"""App gráfica simple: pegá el HTML de Marketplace -> lista de productos ESPECÍFICOS.

Pensada para el flujo seguro de "Pegar/Capturar HTML":

  1. Abrí Facebook Marketplace en tu navegador (YA logueado) y hacé la
     búsqueda, p. ej. "Mercruiser 4.5L". Bajá un poco para que carguen
     resultados.
  2. Mostrá el código fuente (Ctrl+U), seleccioná todo (Ctrl+A), copiá
     (Ctrl+C). O guardá la página (Ctrl+S) y abrí el .html.
  3. Pegá acá, escribí qué buscaste y apretá "Buscar productos específicos".

La app extrae cada anuncio (link, título, precio, ubicación), deja sólo los
que corresponden a tu búsqueda y te arma la lista de links específicos. No
guarda contraseñas ni automatiza el login.

Ejecutar:  python paste_app.py
"""

from __future__ import annotations

import sys
import traceback
import webbrowser
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from PyQt6.QtCore import Qt, QThread, pyqtSignal
from PyQt6.QtGui import QGuiApplication
from PyQt6.QtWidgets import (
    QApplication,
    QMainWindow,
    QWidget,
    QVBoxLayout,
    QHBoxLayout,
    QLabel,
    QLineEdit,
    QComboBox,
    QPushButton,
    QPlainTextEdit,
    QTableWidget,
    QTableWidgetItem,
    QHeaderView,
    QAbstractItemView,
    QFileDialog,
    QMessageBox,
    QStatusBar,
)

from app.listing_parser import parse_listings, filter_by_query

try:
    from app.ranking import rank_listings
except Exception:  # pragma: no cover
    rank_listings = None

try:
    from app.export import export_csv, export_json
except Exception:  # pragma: no cover
    export_csv = export_json = None


class AutoFetchWorker(QThread):
    """Corre la búsqueda automática (Playwright) en un hilo aparte.

    Así la ventana no se congela mientras el navegador carga los resultados.
    """

    status = pyqtSignal(str)
    finished_ok = pyqtSignal(list)
    failed = pyqtSignal(str)

    def __init__(self, query: str, match: str, scrolls: int) -> None:
        super().__init__()
        self.query = query
        self.match = match
        self.scrolls = scrolls

    def run(self) -> None:  # pragma: no cover - requiere navegador real
        try:
            from app.auto_fetch import fetch_listings

            listings = fetch_listings(
                self.query,
                match=self.match,
                scrolls=self.scrolls,
                on_status=lambda m: self.status.emit(m),
            )
            self.finished_ok.emit(listings)
        except Exception as exc:
            self.failed.emit(f"{exc}\n\n{traceback.format_exc()}")


class PasteWindow(QMainWindow):
    COLS = ["Título", "Precio", "Ubicación", "Link"]

    def __init__(self) -> None:
        super().__init__()
        self.setWindowTitle("Imporlan — Productos específicos de Marketplace")
        self.resize(1000, 720)
        self._listings: list[dict] = []

        central = QWidget()
        layout = QVBoxLayout(central)

        # --- Fila de búsqueda ---
        top = QHBoxLayout()
        top.addWidget(QLabel("¿Qué buscaste?"))
        self.query_edit = QLineEdit()
        self.query_edit.setPlaceholderText("Ej.: Mercruiser 4.5L")
        top.addWidget(self.query_edit, 1)
        top.addWidget(QLabel("Coincidencia:"))
        self.match_combo = QComboBox()
        self.match_combo.addItem("Todos los términos", "all")
        self.match_combo.addItem("Al menos uno", "any")
        self.match_combo.addItem("No filtrar", "off")
        top.addWidget(self.match_combo)
        layout.addLayout(top)

        # --- Área para pegar el HTML ---
        layout.addWidget(QLabel(
            "Pegá acá el HTML de la página de resultados (Ctrl+U → Ctrl+A → Ctrl+C "
            "en tu navegador, ya logueado en Facebook):"
        ))
        self.html_edit = QPlainTextEdit()
        self.html_edit.setPlaceholderText("Pegá el código fuente de la página de Marketplace…")
        layout.addWidget(self.html_edit, 1)

        # --- Botones de acción ---
        actions = QHBoxLayout()
        self.search_btn = QPushButton("🔎 Buscar productos específicos")
        self.search_btn.clicked.connect(self.do_search)
        actions.addWidget(self.search_btn)

        self.auto_btn = QPushButton("🤖 Buscar automático (navegador)")
        self.auto_btn.setToolTip(
            "Abre un navegador con tu sesión de Facebook, hace la búsqueda y "
            "extrae los productos solo. Requiere Playwright."
        )
        self.auto_btn.clicked.connect(self.do_auto_search)
        actions.addWidget(self.auto_btn)

        self.open_html_btn = QPushButton("Abrir archivo .html…")
        self.open_html_btn.clicked.connect(self.open_html_file)
        actions.addWidget(self.open_html_btn)

        actions.addStretch(1)

        self.copy_btn = QPushButton("Copiar todos los links")
        self.copy_btn.clicked.connect(self.copy_links)
        actions.addWidget(self.copy_btn)
        self.csv_btn = QPushButton("Exportar CSV")
        self.csv_btn.clicked.connect(lambda: self.export("csv"))
        actions.addWidget(self.csv_btn)
        self.json_btn = QPushButton("Exportar JSON")
        self.json_btn.clicked.connect(lambda: self.export("json"))
        actions.addWidget(self.json_btn)
        layout.addLayout(actions)

        # --- Tabla de resultados ---
        self.table = QTableWidget()
        self.table.setColumnCount(len(self.COLS))
        self.table.setHorizontalHeaderLabels(self.COLS)
        self.table.horizontalHeader().setSectionResizeMode(QHeaderView.ResizeMode.Stretch)
        self.table.setSelectionBehavior(QAbstractItemView.SelectionBehavior.SelectRows)
        self.table.setEditTriggers(QAbstractItemView.EditTrigger.NoEditTriggers)
        self.table.verticalHeader().setVisible(False)
        self.table.cellDoubleClicked.connect(self._open_row)
        layout.addWidget(self.table, 2)

        self.setCentralWidget(central)
        self.setStatusBar(QStatusBar())
        self.statusBar().showMessage("Listo. Pegá el HTML y apretá buscar.")

    # ------------------------------------------------------------------
    def do_search(self) -> None:
        html = self.html_edit.toPlainText()
        query = self.query_edit.text().strip()
        if not html.strip():
            QMessageBox.warning(self, "Falta el HTML", "Primero pegá el HTML de la página.")
            return

        listings = parse_listings(html, query)
        mode = self.match_combo.currentData()
        if query and mode != "off":
            filtered = filter_by_query(listings, query, mode=mode)
            listings = filtered or listings
        if rank_listings is not None:
            listings = rank_listings(listings)

        self._listings = listings
        self._fill_table(listings)
        if not listings:
            self.statusBar().showMessage(
                "No se encontraron productos. ¿Estás logueado y cargaron los resultados?"
            )
        else:
            self.statusBar().showMessage(
                f"{len(listings)} producto(s) específico(s)."
                + (f" Filtrados por: '{query}'." if query and mode != 'off' else "")
            )

    # ------------------------------------------------------------------
    def do_auto_search(self) -> None:
        """Lanza la búsqueda automática con el navegador (en un hilo)."""
        query = self.query_edit.text().strip()
        if not query:
            QMessageBox.warning(
                self, "Falta la búsqueda",
                "Escribí qué querés buscar (ej.: Mercruiser 4.5L) antes de buscar automático.",
            )
            return
        try:
            import app.auto_fetch  # noqa: F401
        except Exception as exc:
            QMessageBox.critical(
                self, "Falta Playwright",
                "El modo automático necesita Playwright:\n\n"
                "    pip install playwright\n"
                "    playwright install chromium\n\n"
                f"Detalle: {exc}",
            )
            return

        mode = self.match_combo.currentData()
        self.auto_btn.setEnabled(False)
        self.search_btn.setEnabled(False)
        self.statusBar().showMessage("Abriendo navegador… (la 1ª vez logueate a Facebook en la ventana)")

        self._worker = AutoFetchWorker(query, mode, scrolls=8)
        self._worker.status.connect(lambda m: self.statusBar().showMessage(m))
        self._worker.finished_ok.connect(self._on_auto_done)
        self._worker.failed.connect(self._on_auto_failed)
        self._worker.start()

    def _on_auto_done(self, listings: list) -> None:
        self.auto_btn.setEnabled(True)
        self.search_btn.setEnabled(True)
        if rank_listings is not None:
            listings = rank_listings(listings)
        self._listings = listings
        self._fill_table(listings)
        if listings:
            self.statusBar().showMessage(f"{len(listings)} producto(s) específico(s) (modo automático).")
        else:
            self.statusBar().showMessage(
                "No se encontraron productos. Probá con más scroll o revisá que estés logueado."
            )

    def _on_auto_failed(self, message: str) -> None:
        self.auto_btn.setEnabled(True)
        self.search_btn.setEnabled(True)
        self.statusBar().showMessage("Falló la búsqueda automática.")
        QMessageBox.critical(self, "Error en búsqueda automática", message)

    def _fill_table(self, listings: list[dict]) -> None:
        self.table.setRowCount(len(listings))
        for row, it in enumerate(listings):
            price = it.get("price_text")
            if not price and it.get("price") is not None:
                price = str(it["price"])
            values = [
                it.get("title") or "(sin título)",
                price or "—",
                it.get("location") or "—",
                it.get("url", ""),
            ]
            for col, val in enumerate(values):
                self.table.setItem(row, col, QTableWidgetItem(val))

    def _open_row(self, row: int, _col: int) -> None:
        if 0 <= row < len(self._listings):
            webbrowser.open(self._listings[row]["url"])

    def open_html_file(self) -> None:
        path, _ = QFileDialog.getOpenFileName(
            self, "Abrir HTML guardado", "", "HTML (*.html *.htm);;Todos (*.*)"
        )
        if path:
            try:
                self.html_edit.setPlainText(
                    Path(path).read_text(encoding="utf-8", errors="replace")
                )
                self.statusBar().showMessage(f"Cargado: {path}")
            except Exception as e:
                QMessageBox.critical(self, "Error", f"No se pudo abrir el archivo:\n{e}")

    def copy_links(self) -> None:
        if not self._listings:
            return
        links = "\n".join(it["url"] for it in self._listings)
        QGuiApplication.clipboard().setText(links)
        self.statusBar().showMessage(f"Copiados {len(self._listings)} links al portapapeles.")

    def export(self, kind: str) -> None:
        if not self._listings:
            QMessageBox.information(self, "Nada para exportar", "Primero hacé una búsqueda.")
            return
        if kind == "csv" and export_csv is None or kind == "json" and export_json is None:
            QMessageBox.warning(self, "No disponible", "El módulo de exportación no está disponible.")
            return
        ext = "csv" if kind == "csv" else "json"
        path, _ = QFileDialog.getSaveFileName(
            self, f"Exportar {ext.upper()}", f"productos.{ext}", f"{ext.upper()} (*.{ext})"
        )
        if not path:
            return
        (export_csv if kind == "csv" else export_json)(self._listings, path)
        self.statusBar().showMessage(f"Exportado a {path}")


def main() -> int:
    app = QApplication(sys.argv)
    app.setApplicationName("Imporlan — Productos específicos")
    win = PasteWindow()
    win.show()
    return app.exec()


if __name__ == "__main__":
    raise SystemExit(main())
