from __future__ import annotations

import re
import threading
import time
import webbrowser
from pathlib import Path
from tkinter import Menu, filedialog, messagebox, ttk

import customtkinter as ctk

from app.config import (
    APP_NAME,
    APP_SUBTITLE,
    CURRENCY_OPTIONS,
    DEFAULT_OPEN_DELAY_SECONDS,
    DEFAULT_RADIUS_MILES,
    PRIORITY_OPTIONS,
    RADIUS_OPTIONS_MILES,
    STATUS_OPTIONS,
    load_locations,
)
from app.export import export_csv, export_excel
from app.ranking import sort_best_first
from app.search_builder import generate_searches, normalize_keywords
from app.storage import OpportunityStorage


ctk.set_appearance_mode("light")
ctk.set_default_color_theme("blue")

BG = "#edf2f7"
NAVY = "#10233f"
NAVY_2 = "#17395f"
BLUE = "#2563eb"
GREEN = "#12805c"
ORANGE = "#d97706"
DANGER = "#b42318"
CARD = "#ffffff"
TEXT_MUTED = "#5b677a"

MARINE_ENGINE_VARIANTS = [
    "{keyword} engine",
    "{keyword} motor",
    "{keyword} block",
    "{keyword} long block",
    "{keyword} complete engine",
    "{keyword} marine engine",
    "{keyword} MPI",
]


class MarketplaceAssistantApp(ctk.CTk):
    def __init__(self) -> None:
        super().__init__()
        self.title(APP_NAME)
        self.geometry("1440x900")
        self.minsize(1180, 760)
        self.configure(fg_color=BG)

        self.locations = load_locations()
        self.storage = OpportunityStorage()
        self.generated_searches: list[dict[str, object]] = []
        self.opportunities: list[dict[str, object]] = []
        self.current_opportunity_id: int | None = None
        self.location_vars: dict[str, dict[str, ctk.BooleanVar]] = {}
        self.direct_links: list[dict[str, object]] = []
        self.clipboard_capture_active = False
        self.last_clipboard_text = ""

        self._configure_grid()
        self._build_header()
        self._build_tabs()
        self._build_status_bar()
        self._load_opportunities()
        self.after(50, lambda: self.geometry("1440x900"))
        self.after(1000, self._poll_clipboard_for_specific_links)
        self._set_status("Vista principal: pega links directos /marketplace/item/... para listarlos, abrirlos, copiarlos y guardarlos como oportunidades.")

    def _configure_grid(self) -> None:
        self.grid_columnconfigure(0, weight=1)
        self.grid_rowconfigure(1, weight=1)

    def _build_header(self) -> None:
        header = ctk.CTkFrame(self, corner_radius=0, fg_color=NAVY)
        header.grid(row=0, column=0, sticky="ew")
        header.grid_columnconfigure(0, weight=1)
        header.grid_columnconfigure(1, weight=0)

        title = ctk.CTkLabel(
            header,
            text=APP_NAME,
            font=ctk.CTkFont(size=26, weight="bold"),
            text_color="white",
        )
        title.grid(row=0, column=0, padx=26, pady=(18, 2), sticky="w")

        subtitle = ctk.CTkLabel(
            header,
            text=f"{APP_SUBTITLE}  |  Manual, seguro y sin scraping",
            font=ctk.CTkFont(size=14),
            text_color="#d7e5f8",
        )
        subtitle.grid(row=1, column=0, padx=26, pady=(0, 18), sticky="w")

        safety = ctk.CTkLabel(
            header,
            text="No login  |  No cookies  |  No automatizacion de Facebook",
            font=ctk.CTkFont(size=13, weight="bold"),
            text_color="#b9f6d1",
        )
        safety.grid(row=0, column=1, rowspan=2, padx=26, pady=18, sticky="e")

    def _build_tabs(self) -> None:
        self.tabs = ctk.CTkTabview(self, fg_color=BG, segmented_button_selected_color=BLUE)
        self.tabs.grid(row=1, column=0, padx=18, pady=(18, 8), sticky="nsew")
        self.search_tab = self.tabs.add("1. Search")
        self.saved_tab = self.tabs.add("2. Saved Opportunities")
        self._build_search_tab()
        self._build_saved_tab()
        self.tabs.set("1. Search")

    def _build_status_bar(self) -> None:
        self.status_var = ctk.StringVar(value="")
        bar = ctk.CTkFrame(self, corner_radius=0, fg_color="#dfe7f1", height=34)
        bar.grid(row=2, column=0, sticky="ew")
        bar.grid_columnconfigure(0, weight=1)
        self.status_label = ctk.CTkLabel(
            bar,
            textvariable=self.status_var,
            font=ctk.CTkFont(size=12),
            text_color=NAVY,
            anchor="w",
        )
        self.status_label.grid(row=0, column=0, padx=20, pady=6, sticky="ew")

    def _build_search_tab(self) -> None:
        self.search_tab.grid_columnconfigure(0, weight=0)
        self.search_tab.grid_columnconfigure(1, weight=1)
        self.search_tab.grid_rowconfigure(0, weight=1)

        controls = ctk.CTkScrollableFrame(self.search_tab, fg_color=CARD, width=370)
        controls.grid(row=0, column=0, padx=(10, 12), pady=10, sticky="nsw")
        controls.grid_columnconfigure(0, weight=1)

        self._section_label(controls, "Busqueda", 0)
        ctk.CTkLabel(
            controls,
            text="Ingresa keywords, una por linea. Puedes pegar listas completas.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            wraplength=315,
            justify="left",
        ).grid(row=1, column=0, padx=16, pady=(0, 8), sticky="w")

        self.keywords_text = ctk.CTkTextbox(controls, width=320, height=155, border_width=1)
        self.keywords_text.grid(row=2, column=0, padx=16, pady=(0, 14), sticky="ew")
        self.keywords_text.insert(
            "1.0",
            "MerCruiser 4.5\nBayliner VR5\nSea Ray 240\nboat trailer\nmarine engine",
        )

        keyword_actions = ctk.CTkFrame(controls, fg_color="transparent")
        keyword_actions.grid(row=3, column=0, padx=16, pady=(0, 10), sticky="ew")
        keyword_actions.grid_columnconfigure((0, 1), weight=1)
        ctk.CTkButton(
            keyword_actions,
            text="Expandir motores",
            height=30,
            fg_color=NAVY_2,
            command=self._expand_marine_engine_keywords,
        ).grid(row=0, column=0, padx=(0, 5), sticky="ew")
        ctk.CTkButton(
            keyword_actions,
            text="Limpiar keywords",
            height=30,
            fg_color="#6b7280",
            command=self._clear_keywords,
        ).grid(row=0, column=1, padx=(5, 0), sticky="ew")

        self._section_label(controls, "Paises", 4)
        self.country_vars: dict[str, ctk.BooleanVar] = {}
        row = 5
        for country in self.locations.keys():
            variable = ctk.BooleanVar(value=True)
            self.country_vars[country] = variable
            ctk.CTkCheckBox(
                controls,
                text=country,
                variable=variable,
                command=self._update_search_preview,
            ).grid(row=row, column=0, padx=18, pady=4, sticky="w")
            row += 1

        self._section_label(controls, "Ciudades", row)
        row += 1
        ctk.CTkLabel(
            controls,
            text="Puedes desmarcar ciudades para abrir menos pestanas.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            wraplength=315,
            justify="left",
        ).grid(row=row, column=0, padx=16, pady=(0, 8), sticky="w")
        row += 1

        location_actions = ctk.CTkFrame(controls, fg_color="transparent")
        location_actions.grid(row=row, column=0, padx=16, pady=(0, 8), sticky="ew")
        location_actions.grid_columnconfigure((0, 1), weight=1)
        ctk.CTkButton(
            location_actions,
            text="Marcar todas",
            height=30,
            fg_color=NAVY_2,
            command=lambda: self._set_all_locations(True),
        ).grid(row=0, column=0, padx=(0, 5), sticky="ew")
        ctk.CTkButton(
            location_actions,
            text="Limpiar",
            height=30,
            fg_color="#6b7280",
            command=lambda: self._set_all_locations(False),
        ).grid(row=0, column=1, padx=(5, 0), sticky="ew")
        row += 1

        for country, locations in self.locations.items():
            country_label = ctk.CTkLabel(
                controls,
                text=country,
                font=ctk.CTkFont(size=13, weight="bold"),
                text_color=NAVY,
            )
            country_label.grid(row=row, column=0, padx=16, pady=(8, 2), sticky="w")
            row += 1
            self.location_vars[country] = {}
            for location in locations:
                variable = ctk.BooleanVar(value=True)
                self.location_vars[country][location["name"]] = variable
                ctk.CTkCheckBox(
                    controls,
                    text=location["name"],
                    variable=variable,
                    command=self._update_search_preview,
                ).grid(row=row, column=0, padx=28, pady=2, sticky="w")
                row += 1

        self._section_label(controls, "Apertura", row)
        row += 1

        self.radius_var = ctk.StringVar(value=f"{DEFAULT_RADIUS_MILES} miles")
        self.radius_menu = ctk.CTkOptionMenu(
            controls,
            variable=self.radius_var,
            values=[f"{miles} miles" for miles in RADIUS_OPTIONS_MILES],
            command=lambda _value: self._update_search_preview(),
        )
        self.radius_menu.grid(row=row, column=0, padx=16, pady=(0, 10), sticky="ew")
        row += 1

        ctk.CTkLabel(
            controls,
            text="Delay entre pestanas",
            font=ctk.CTkFont(size=13, weight="bold"),
        ).grid(row=row, column=0, padx=16, pady=(4, 4), sticky="w")
        row += 1
        self.delay_entry = ctk.CTkEntry(controls)
        self.delay_entry.insert(0, str(DEFAULT_OPEN_DELAY_SECONDS))
        self.delay_entry.grid(row=row, column=0, padx=16, pady=(0, 12), sticky="ew")
        row += 1

        self.search_preview_label = ctk.CTkLabel(
            controls,
            text="",
            font=ctk.CTkFont(size=12, weight="bold"),
            text_color=ORANGE,
            wraplength=315,
            justify="left",
        )
        self.search_preview_label.grid(row=row, column=0, padx=16, pady=(0, 12), sticky="w")
        row += 1

        ctk.CTkButton(
            controls,
            text="Abrir busquedas e iniciar captura",
            height=42,
            fg_color=BLUE,
            font=ctk.CTkFont(size=14, weight="bold"),
            command=self._generate_searches,
        ).grid(row=row, column=0, padx=16, pady=(4, 8), sticky="ew")
        row += 1

        ctk.CTkButton(
            controls,
            text="Abrir primeras 10 busquedas manuales",
            height=38,
            fg_color=GREEN,
            command=self._open_first_ten_searches,
        ).grid(row=row, column=0, padx=16, pady=4, sticky="ew")
        row += 1

        ctk.CTkButton(
            controls,
            text="Abrir primeras 10",
            height=38,
            fg_color=NAVY_2,
            command=self._open_first_ten_searches,
        ).grid(row=row, column=0, padx=16, pady=4, sticky="ew")
        row += 1

        ctk.CTkButton(
            controls,
            text="Abrir todas",
            height=38,
            fg_color=ORANGE,
            command=self._open_all_searches,
        ).grid(row=row, column=0, padx=16, pady=4, sticky="ew")
        row += 1

        ctk.CTkButton(
            controls,
            text="Pegar links especificos",
            height=36,
            fg_color="#64748b",
            command=self._paste_specific_links_into_search_table,
        ).grid(row=row, column=0, padx=16, pady=4, sticky="ew")
        row += 1

        self.capture_button = ctk.CTkButton(
            controls,
            text="Captura portapapeles: OFF",
            height=36,
            fg_color="#64748b",
            command=self._toggle_clipboard_capture,
        )
        self.capture_button.grid(row=row, column=0, padx=16, pady=4, sticky="ew")
        row += 1

        ctk.CTkLabel(
            controls,
            text="Paso 1: genera busqueda. Paso 2: abre Facebook. Paso 3: copia links /marketplace/item/...; apareceran en la tabla.",
            font=ctk.CTkFont(size=12, weight="bold"),
            text_color=GREEN,
            wraplength=330,
            justify="left",
        ).grid(row=row, column=0, padx=16, pady=(8, 18), sticky="w")

        table_frame = ctk.CTkFrame(self.search_tab, fg_color=CARD)
        table_frame.grid(row=0, column=1, padx=(0, 10), pady=10, sticky="nsew")
        table_frame.grid_columnconfigure(0, weight=1)
        table_frame.grid_rowconfigure(3, weight=1)

        self._build_search_dashboard(table_frame)

        helper = ctk.CTkLabel(
            table_frame,
            text="La app no extrae resultados desde Facebook. Esta tabla se llena cuando copias links reales /marketplace/item/... desde el navegador.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            anchor="w",
        )
        helper.grid(row=1, column=0, padx=14, pady=(4, 2), sticky="ew")

        toolbar = ctk.CTkFrame(table_frame, fg_color="transparent")
        toolbar.grid(row=2, column=0, padx=12, pady=8, sticky="ew")
        toolbar.grid_columnconfigure(4, weight=1)
        ctk.CTkButton(toolbar, text="Pegar links especificos", width=165, fg_color=BLUE, command=self._paste_specific_links_into_search_table).grid(row=0, column=0, padx=4)
        ctk.CTkButton(toolbar, text="Abrir publicacion", width=130, fg_color=GREEN, command=self._open_selected_specific_links).grid(row=0, column=1, padx=4)
        ctk.CTkButton(toolbar, text="Copiar link", width=105, fg_color="#64748b", command=self._copy_selected_specific_link).grid(row=0, column=2, padx=4)
        ctk.CTkButton(toolbar, text="Guardar oportunidad", width=150, fg_color=ORANGE, command=self._save_selected_specific_links).grid(row=0, column=3, padx=4)

        self.search_tree_frame = self._create_treeview(
            table_frame,
            columns=("item_id", "opened", "saved", "url"),
            headings=("Item ID", "Opened", "Saved", "Specific listing URL copied from Facebook"),
            widths=(180, 90, 90, 940),
        )
        self.search_tree_frame.grid(row=3, column=0, padx=12, pady=(0, 12), sticky="nsew")
        search_tree = self._get_search_tree()
        search_tree.bind("<Control-c>", lambda _event: self._copy_selected_specific_link())
        search_tree.bind("<Double-1>", lambda _event: self._open_selected_specific_links())
        search_tree.bind("<Button-3>", self._show_search_context_menu)
        self._update_search_preview()

    def _build_search_dashboard(self, parent: ctk.CTkFrame) -> None:
        dashboard = ctk.CTkFrame(parent, fg_color="transparent")
        dashboard.grid(row=0, column=0, padx=12, pady=(12, 4), sticky="ew")
        dashboard.grid_columnconfigure((0, 1, 2), weight=1)

        self.search_total_card = self._metric_card(dashboard, "Links capturados", "0", BLUE, 0)
        self.search_opened_card = self._metric_card(dashboard, "Publicaciones abiertas", "0", GREEN, 1)
        self.search_pending_card = self._metric_card(dashboard, "Guardadas", "0", ORANGE, 2)

    def _build_direct_links_tab(self) -> None:
        return
        self.direct_links_tab.grid_columnconfigure(0, weight=0)
        self.direct_links_tab.grid_columnconfigure(1, weight=1)
        self.direct_links_tab.grid_rowconfigure(0, weight=1)

        controls = ctk.CTkFrame(self.direct_links_tab, fg_color=CARD, width=410)
        controls.grid(row=0, column=0, padx=(10, 12), pady=10, sticky="nsw")
        controls.grid_columnconfigure(0, weight=1)
        controls.grid_rowconfigure(2, weight=1)

        self._section_label(controls, "Vista principal: publicaciones concretas", 0)
        ctk.CTkLabel(
            controls,
            text=(
                "Aqui va la lista real que te interesa: links especificos tipo "
                "facebook.com/marketplace/item/1301127085457538/. "
                "Importante: por seguridad la app no extrae resultados desde Facebook; "
                "tu copias manualmente los links de publicaciones que correspondan y aqui se normalizan."
            ),
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            wraplength=350,
            justify="left",
        ).grid(row=1, column=0, padx=16, pady=(0, 10), sticky="w")

        self.direct_links_text = ctk.CTkTextbox(controls, width=365, height=320, border_width=1)
        self.direct_links_text.grid(row=2, column=0, padx=16, pady=(0, 12), sticky="nsew")
        self.direct_links_text.insert(
            "1.0",
            "https://www.facebook.com/marketplace/item/1301127085457538/",
        )

        ctk.CTkButton(
            controls,
            text="Extraer links directos",
            height=42,
            fg_color=BLUE,
            font=ctk.CTkFont(size=14, weight="bold"),
            command=self._extract_direct_links_from_textbox,
        ).grid(row=3, column=0, padx=16, pady=(0, 8), sticky="ew")
        ctk.CTkButton(
            controls,
            text="Pegar desde portapapeles",
            height=36,
            fg_color=NAVY_2,
            command=self._paste_direct_links_clipboard,
        ).grid(row=4, column=0, padx=16, pady=4, sticky="ew")
        ctk.CTkButton(
            controls,
            text="Abrir seleccionados",
            height=36,
            fg_color=GREEN,
            command=self._open_selected_direct_links,
        ).grid(row=5, column=0, padx=16, pady=4, sticky="ew")
        ctk.CTkButton(
            controls,
            text="Guardar seleccionados como oportunidades",
            height=36,
            fg_color=ORANGE,
            command=self._save_selected_direct_links_as_opportunities,
        ).grid(row=6, column=0, padx=16, pady=4, sticky="ew")
        ctk.CTkButton(
            controls,
            text="Copiar link seleccionado",
            height=36,
            fg_color="#64748b",
            command=self._copy_selected_direct_link,
        ).grid(row=7, column=0, padx=16, pady=(4, 16), sticky="ew")

        table_frame = ctk.CTkFrame(self.direct_links_tab, fg_color=CARD)
        table_frame.grid(row=0, column=1, padx=(0, 10), pady=10, sticky="nsew")
        table_frame.grid_columnconfigure(0, weight=1)
        table_frame.grid_rowconfigure(2, weight=1)

        dashboard = ctk.CTkFrame(table_frame, fg_color="transparent")
        dashboard.grid(row=0, column=0, padx=12, pady=(12, 4), sticky="ew")
        dashboard.grid_columnconfigure((0, 1, 2), weight=1)
        self.direct_total_card = self._metric_card(dashboard, "Publicaciones listadas", "0", BLUE, 0)
        self.direct_opened_card = self._metric_card(dashboard, "Abiertos", "0", GREEN, 1)
        self.direct_saved_card = self._metric_card(dashboard, "Guardados", "0", ORANGE, 2)

        ctk.CTkLabel(
            table_frame,
            text="Doble click abre la publicacion directa. Click derecho muestra acciones.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            anchor="w",
        ).grid(row=1, column=0, padx=14, pady=(4, 8), sticky="ew")

        self.direct_links_tree_frame = self._create_treeview(
            table_frame,
            columns=("item_id", "opened", "saved", "url"),
            headings=("Item ID", "Opened", "Saved", "Direct URL"),
            widths=(180, 90, 90, 900),
        )
        self.direct_links_tree_frame.grid(row=2, column=0, padx=12, pady=(0, 12), sticky="nsew")
        direct_tree = self._get_direct_links_tree()
        direct_tree.bind("<Double-1>", lambda _event: self._open_selected_direct_links())
        direct_tree.bind("<Control-c>", lambda _event: self._copy_selected_direct_link())
        direct_tree.bind("<Button-3>", self._show_direct_link_context_menu)

    def _build_saved_tab(self) -> None:
        self.saved_tab.grid_columnconfigure(0, weight=0)
        self.saved_tab.grid_columnconfigure(1, weight=1)
        self.saved_tab.grid_rowconfigure(0, weight=1)

        form = ctk.CTkScrollableFrame(self.saved_tab, fg_color=CARD, width=410)
        form.grid(row=0, column=0, padx=(10, 12), pady=10, sticky="nsw")
        form.grid_columnconfigure(0, weight=1)
        form.grid_columnconfigure(1, weight=1)

        self.form_fields: dict[str, ctk.CTkEntry | ctk.CTkTextbox | ctk.CTkOptionMenu] = {}

        self._section_label(form, "Nueva oportunidad", 0)
        self._add_entry(form, "Marketplace link", "link", 1, columnspan=2)

        paste_row = ctk.CTkFrame(form, fg_color="transparent")
        paste_row.grid(row=3, column=0, columnspan=2, padx=14, pady=(0, 6), sticky="ew")
        paste_row.grid_columnconfigure((0, 1), weight=1)
        ctk.CTkButton(paste_row, text="Pegar link", height=30, fg_color=NAVY_2, command=self._paste_link_from_clipboard).grid(row=0, column=0, padx=(0, 5), sticky="ew")
        ctk.CTkButton(paste_row, text="Abrir link", height=30, fg_color=GREEN, command=self._open_form_link).grid(row=0, column=1, padx=(5, 0), sticky="ew")

        self._add_entry(form, "Title", "title", 4, columnspan=2)
        self._add_entry(form, "Price", "price", 6)
        self._add_option(form, "Currency", "currency", CURRENCY_OPTIONS, 6, column=1, default="USD")
        self._add_entry(form, "Location", "location", 8)
        self._add_option(form, "Country", "country", ["USA", "Canada", "Other"], 8, column=1, default="USA")
        self._add_entry(form, "Category", "category", 10)
        self._add_option(form, "Priority", "priority", PRIORITY_OPTIONS, 10, column=1, default="Medium")
        self._add_option(form, "Status", "status", STATUS_OPTIONS, 12, columnspan=2, default="New")

        ctk.CTkLabel(form, text="Notes").grid(row=14, column=0, columnspan=2, padx=14, pady=(8, 4), sticky="w")
        notes = ctk.CTkTextbox(form, width=380, height=110, border_width=1)
        notes.grid(row=15, column=0, columnspan=2, padx=14, pady=(0, 12), sticky="ew")
        self.form_fields["notes"] = notes

        self.selected_score_label = ctk.CTkLabel(
            form,
            text="Score: -",
            font=ctk.CTkFont(size=14, weight="bold"),
            text_color=BLUE,
        )
        self.selected_score_label.grid(row=16, column=0, columnspan=2, padx=14, pady=(0, 8), sticky="w")

        button_grid = ctk.CTkFrame(form, fg_color="transparent")
        button_grid.grid(row=17, column=0, columnspan=2, padx=14, pady=(4, 18), sticky="ew")
        button_grid.grid_columnconfigure((0, 1), weight=1)

        ctk.CTkButton(button_grid, text="Nueva", command=self._clear_opportunity_form, fg_color="#64748b").grid(
            row=0, column=0, padx=(0, 6), pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Guardar", command=self._save_opportunity, fg_color=BLUE).grid(
            row=0, column=1, padx=(6, 0), pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Ordenar mejores primero", command=self._sort_opportunities, fg_color=GREEN).grid(
            row=1, column=0, columnspan=2, pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Importar varios links", command=self._show_bulk_import_dialog, fg_color=ORANGE).grid(
            row=2, column=0, columnspan=2, pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Eliminar seleccionada", command=self._delete_selected_opportunity, fg_color=DANGER).grid(
            row=3, column=0, columnspan=2, pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Export CSV", command=self._export_csv, fg_color=NAVY_2).grid(
            row=4, column=0, padx=(0, 6), pady=4, sticky="ew"
        )
        ctk.CTkButton(button_grid, text="Export Excel", command=self._export_excel, fg_color=NAVY_2).grid(
            row=4, column=1, padx=(6, 0), pady=4, sticky="ew"
        )

        table_frame = ctk.CTkFrame(self.saved_tab, fg_color=CARD)
        table_frame.grid(row=0, column=1, padx=(0, 10), pady=10, sticky="nsew")
        table_frame.grid_columnconfigure(0, weight=1)
        table_frame.grid_rowconfigure(3, weight=1)

        self._build_opportunity_dashboard(table_frame)

        toolbar = ctk.CTkFrame(table_frame, fg_color="transparent")
        toolbar.grid(row=1, column=0, padx=12, pady=(4, 8), sticky="ew")
        toolbar.grid_columnconfigure(1, weight=1)
        ctk.CTkLabel(toolbar, text="Filtrar", font=ctk.CTkFont(size=13, weight="bold")).grid(row=0, column=0, padx=(4, 8))
        self.opportunity_filter_var = ctk.StringVar(value="")
        self.opportunity_filter_var.trace_add("write", lambda *_args: self._apply_opportunity_filter())
        ctk.CTkEntry(toolbar, textvariable=self.opportunity_filter_var, placeholder_text="Buscar por titulo, ubicacion, status, categoria o link").grid(row=0, column=1, padx=4, sticky="ew")
        ctk.CTkButton(toolbar, text="Abrir link", width=105, fg_color=GREEN, command=self._open_selected_opportunity_link).grid(row=0, column=2, padx=4)
        ctk.CTkButton(toolbar, text="Copiar link", width=105, fg_color="#64748b", command=self._copy_selected_opportunity_link).grid(row=0, column=3, padx=4)

        ctk.CTkLabel(
            table_frame,
            text="Doble click carga la oportunidad en el formulario. Click derecho muestra acciones.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            anchor="w",
        ).grid(row=2, column=0, padx=14, pady=(0, 8), sticky="ew")

        self.opportunity_tree_frame = self._create_treeview(
            table_frame,
            columns=(
                "score",
                "priority",
                "status",
                "title",
                "price",
                "currency",
                "location",
                "country",
                "category",
                "link",
                "date_added",
                "last_updated",
            ),
            headings=(
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
                "Date added",
                "Last updated",
            ),
            widths=(75, 95, 125, 250, 90, 80, 160, 90, 130, 440, 150, 150),
        )
        self.opportunity_tree_frame.grid(row=3, column=0, padx=12, pady=(0, 12), sticky="nsew")
        opportunity_tree = self._get_opportunity_tree()
        opportunity_tree.bind("<<TreeviewSelect>>", self._load_selected_opportunity)
        opportunity_tree.bind("<Double-1>", self._load_selected_opportunity)
        opportunity_tree.bind("<Button-3>", self._show_opportunity_context_menu)

    def _build_opportunity_dashboard(self, parent: ctk.CTkFrame) -> None:
        dashboard = ctk.CTkFrame(parent, fg_color="transparent")
        dashboard.grid(row=0, column=0, padx=12, pady=(12, 4), sticky="ew")
        dashboard.grid_columnconfigure((0, 1, 2, 3), weight=1)

        self.saved_total_card = self._metric_card(dashboard, "Guardadas", "0", BLUE, 0)
        self.saved_high_card = self._metric_card(dashboard, "Alta prioridad", "0", ORANGE, 1)
        self.saved_active_card = self._metric_card(dashboard, "Activas", "0", GREEN, 2)
        self.saved_best_card = self._metric_card(dashboard, "Mejor score", "-", NAVY_2, 3)

    def _metric_card(self, parent: ctk.CTkFrame, title: str, value: str, color: str, column: int) -> ctk.CTkLabel:
        card = ctk.CTkFrame(parent, fg_color="#f8fafc", border_width=1, border_color="#d8e1ec")
        card.grid(row=0, column=column, padx=5, sticky="ew")
        ctk.CTkLabel(card, text=title, font=ctk.CTkFont(size=12), text_color=TEXT_MUTED).grid(
            row=0, column=0, padx=12, pady=(9, 0), sticky="w"
        )
        value_label = ctk.CTkLabel(card, text=value, font=ctk.CTkFont(size=22, weight="bold"), text_color=color)
        value_label.grid(row=1, column=0, padx=12, pady=(0, 9), sticky="w")
        return value_label

    def _section_label(self, parent: ctk.CTkFrame, text: str, row: int) -> None:
        ctk.CTkLabel(
            parent,
            text=text.upper(),
            font=ctk.CTkFont(size=13, weight="bold"),
            text_color=NAVY,
        ).grid(row=row, column=0, padx=16, pady=(16, 6), sticky="w")

    def _create_treeview(
        self,
        parent: ctk.CTkFrame,
        columns: tuple[str, ...],
        headings: tuple[str, ...],
        widths: tuple[int, ...],
    ) -> ttk.Frame:
        container = ttk.Frame(parent)
        container.grid_columnconfigure(0, weight=1)
        container.grid_rowconfigure(0, weight=1)

        style = ttk.Style()
        style.theme_use("default")
        style.configure(
            "Treeview",
            rowheight=30,
            font=("Segoe UI", 10),
            background="#ffffff",
            fieldbackground="#ffffff",
            foreground="#182235",
            borderwidth=0,
        )
        style.configure(
            "Treeview.Heading",
            font=("Segoe UI", 10, "bold"),
            background="#e7eef7",
            foreground=NAVY,
            relief="flat",
        )
        style.map("Treeview", background=[("selected", "#dbeafe")], foreground=[("selected", "#10233f")])

        tree = ttk.Treeview(container, columns=columns, show="headings", selectmode="extended")
        vertical = ttk.Scrollbar(container, orient="vertical", command=tree.yview)
        horizontal = ttk.Scrollbar(container, orient="horizontal", command=tree.xview)
        tree.configure(yscrollcommand=vertical.set, xscrollcommand=horizontal.set)

        for column, heading, width in zip(columns, headings, widths):
            tree.heading(column, text=heading)
            tree.column(column, width=width, minwidth=65, anchor="w", stretch=False)

        tree.grid(row=0, column=0, sticky="nsew")
        vertical.grid(row=0, column=1, sticky="ns")
        horizontal.grid(row=1, column=0, sticky="ew")
        return container

    def _tree_widget(self, container: ttk.Frame) -> ttk.Treeview:
        for child in container.winfo_children():
            if isinstance(child, ttk.Treeview):
                return child
        raise RuntimeError("Treeview not found.")

    def _add_entry(
        self,
        parent: ctk.CTkFrame,
        label: str,
        key: str,
        row: int,
        column: int = 0,
        columnspan: int = 1,
    ) -> None:
        ctk.CTkLabel(parent, text=label).grid(
            row=row, column=column, columnspan=columnspan, padx=14, pady=(8, 4), sticky="w"
        )
        entry = ctk.CTkEntry(parent, width=180, border_width=1)
        entry.grid(
            row=row + 1,
            column=column,
            columnspan=columnspan,
            padx=14,
            pady=(0, 4),
            sticky="ew",
        )
        self.form_fields[key] = entry

    def _add_option(
        self,
        parent: ctk.CTkFrame,
        label: str,
        key: str,
        values: list[str],
        row: int,
        column: int = 0,
        columnspan: int = 1,
        default: str | None = None,
    ) -> None:
        ctk.CTkLabel(parent, text=label).grid(
            row=row, column=column, columnspan=columnspan, padx=14, pady=(8, 4), sticky="w"
        )
        option = ctk.CTkOptionMenu(parent, values=values)
        option.set(default or values[0])
        option.grid(
            row=row + 1,
            column=column,
            columnspan=columnspan,
            padx=14,
            pady=(0, 4),
            sticky="ew",
        )
        self.form_fields[key] = option

    def _get_search_tree(self) -> ttk.Treeview:
        return self._tree_widget(self.search_tree_frame)

    def _get_direct_links_tree(self) -> ttk.Treeview:
        return self._get_search_tree()

    def _get_opportunity_tree(self) -> ttk.Treeview:
        return self._tree_widget(self.opportunity_tree_frame)

    def _set_status(self, message: str) -> None:
        self.status_var.set(message)

    def _toggle_clipboard_capture(self) -> None:
        self._set_clipboard_capture(not self.clipboard_capture_active)

    def _set_clipboard_capture(self, active: bool) -> None:
        self.clipboard_capture_active = active
        if hasattr(self, "capture_button"):
            self.capture_button.configure(
                text=f"Captura portapapeles: {'ON' if active else 'OFF'}",
                fg_color=GREEN if active else "#64748b",
            )
        if active:
            try:
                self.last_clipboard_text = self.clipboard_get()
            except Exception:
                self.last_clipboard_text = ""
            self._set_status("Captura ON: copia links /marketplace/item/... desde Facebook y apareceran en la tabla.")
        else:
            self._set_status("Captura de portapapeles desactivada.")

    def _poll_clipboard_for_specific_links(self) -> None:
        try:
            current_text = self.clipboard_get()
        except Exception:
            current_text = ""

        if self.clipboard_capture_active and current_text and current_text != self.last_clipboard_text:
            self.last_clipboard_text = current_text
            urls = self._extract_marketplace_item_urls(current_text)
            if urls:
                added, skipped = self._merge_specific_listing_links(urls)
                self._refresh_direct_links_table()
                if added:
                    self._set_status(f"Captura ON: agregado {added} link especifico. Duplicados omitidos: {skipped}.")

        self.after(1000, self._poll_clipboard_for_specific_links)

    def _extract_direct_links_from_textbox(self) -> None:
        urls = self._extract_marketplace_item_urls(self.direct_links_text.get("1.0", "end"))
        self.direct_links = [
            {
                "item_id": self._marketplace_item_id(url),
                "url": url,
                "opened": "No",
                "saved": "No",
            }
            for url in urls
        ]
        self._refresh_direct_links_table()
        if urls:
            self._set_status(f"Extraidos {len(urls)} links directos de publicaciones Marketplace.")
        else:
            self._set_status("No se encontraron links directos tipo /marketplace/item/{id}/ en el texto.")

    def _paste_direct_links_clipboard(self) -> None:
        try:
            value = self.clipboard_get().strip()
        except Exception:
            value = ""
        if not value:
            self._set_status("No hay texto en el portapapeles.")
            return
        self.direct_links_text.delete("1.0", "end")
        self.direct_links_text.insert("1.0", value)
        self._extract_direct_links_from_textbox()

    def _refresh_direct_links_table(self) -> None:
        tree = self._get_direct_links_tree()
        tree.delete(*tree.get_children())
        opened = 0
        saved = 0
        for index, item in enumerate(self.direct_links):
            if item["opened"] == "Yes":
                opened += 1
            if item["saved"] == "Yes":
                saved += 1
            tree.insert(
                "",
                "end",
                iid=str(index),
                values=(item["item_id"], item["opened"], item["saved"], item["url"]),
            )
        if hasattr(self, "direct_total_card"):
            self.direct_total_card.configure(text=str(len(self.direct_links)))
            self.direct_opened_card.configure(text=str(opened))
            self.direct_saved_card.configure(text=str(saved))
        if hasattr(self, "search_tree_frame"):
            self._refresh_search_table()

    def _selected_direct_link_indices(self) -> list[int]:
        tree = self._get_direct_links_tree()
        return [int(item_id) for item_id in tree.selection()]

    def _open_selected_direct_links(self) -> None:
        indices = self._selected_direct_link_indices()
        if not indices:
            self._set_status("Selecciona uno o mas links directos para abrir.")
            return
        for index in indices:
            webbrowser.open_new_tab(str(self.direct_links[index]["url"]))
            self.direct_links[index]["opened"] = "Yes"
        self._refresh_direct_links_table()
        self._set_status(f"Abiertos {len(indices)} links directos en el navegador.")

    def _copy_selected_direct_link(self) -> None:
        indices = self._selected_direct_link_indices()
        if not indices:
            self._set_status("Selecciona un link directo para copiar.")
            return
        url = str(self.direct_links[indices[0]]["url"])
        self.clipboard_clear()
        self.clipboard_append(url)
        self._set_status("Link directo copiado al portapapeles.")

    def _save_selected_direct_links_as_opportunities(self) -> None:
        indices = self._selected_direct_link_indices()
        if not indices:
            self._set_status("Selecciona uno o mas links directos para guardar.")
            return

        existing_links = {self._normalize_marketplace_item_url(str(item.get("link") or "")) for item in self.storage.list_opportunities()}
        imported = 0
        skipped = 0
        for index in indices:
            url = str(self.direct_links[index]["url"])
            if url in existing_links:
                skipped += 1
                self.direct_links[index]["saved"] = "Yes"
                continue
            item_id = str(self.direct_links[index]["item_id"])
            self.storage.save_opportunity(
                {
                    "link": url,
                    "title": f"Marketplace item {item_id}",
                    "price": "",
                    "currency": "USD",
                    "location": "",
                    "country": "USA",
                    "category": "Marine engine",
                    "notes": "Direct Marketplace listing link added manually.",
                    "priority": "Medium",
                    "status": "New",
                }
            )
            existing_links.add(url)
            self.direct_links[index]["saved"] = "Yes"
            imported += 1

        self._refresh_direct_links_table()
        self._load_opportunities()
        self._set_status(f"Guardados {imported} links directos como oportunidades. Duplicados omitidos: {skipped}.")

    def _merge_specific_listing_links(self, urls: list[str]) -> tuple[int, int]:
        existing = {str(item["url"]).casefold() for item in self.direct_links}
        added = 0
        skipped = 0
        for url in urls:
            key = url.casefold()
            if key in existing:
                skipped += 1
                continue
            self.direct_links.append(
                {
                    "item_id": self._marketplace_item_id(url),
                    "url": url,
                    "opened": "No",
                    "saved": "No",
                }
            )
            existing.add(key)
            added += 1
        return added, skipped

    def _paste_specific_links_into_search_table(self) -> None:
        try:
            raw_text = self.clipboard_get().strip()
        except Exception:
            raw_text = ""
        if not raw_text:
            self._set_status("No hay texto en el portapapeles para pegar links especificos.")
            return

        urls = self._extract_marketplace_item_urls(raw_text)
        if not urls:
            messagebox.showwarning(
                "Sin links especificos",
                "No encontre links tipo https://www.facebook.com/marketplace/item/{id}/ en el portapapeles.",
            )
            return

        added, skipped = self._merge_specific_listing_links(urls)
        self._refresh_direct_links_table()
        self._set_status(f"Links especificos agregados a la tabla: {added}. Duplicados omitidos: {skipped}.")

    def _selected_specific_link_indices(self) -> list[int]:
        tree = self._get_search_tree()
        indices: list[int] = []
        for item_id in tree.selection():
            if str(item_id).isdigit():
                indices.append(int(item_id))
        return indices

    def _open_selected_specific_links(self) -> None:
        indices = self._selected_specific_link_indices()
        if not indices:
            self._set_status("Selecciona uno o mas links especificos para abrir.")
            return
        for index in indices:
            webbrowser.open_new_tab(str(self.direct_links[index]["url"]))
            self.direct_links[index]["opened"] = "Yes"
        self._refresh_direct_links_table()
        self._set_status(f"Abiertos {len(indices)} links especificos.")

    def _copy_selected_specific_link(self) -> None:
        indices = self._selected_specific_link_indices()
        if not indices:
            self._set_status("Selecciona un link especifico para copiar.")
            return
        self.clipboard_clear()
        self.clipboard_append(str(self.direct_links[indices[0]]["url"]))
        self._set_status("Link especifico copiado al portapapeles.")

    def _save_selected_specific_links(self) -> None:
        indices = self._selected_specific_link_indices()
        if not indices:
            self._set_status("Selecciona uno o mas links especificos para guardar.")
            return
        direct_tree = self._get_direct_links_tree()
        direct_tree.selection_set([str(index) for index in indices])
        self._save_selected_direct_links_as_opportunities()

    def _show_direct_link_context_menu(self, event: object) -> None:
        tree = self._get_direct_links_tree()
        row_id = tree.identify_row(event.y)  # type: ignore[attr-defined]
        if row_id:
            tree.selection_set(row_id)

        menu = Menu(self, tearoff=False)
        menu.add_command(label="Abrir publicacion", command=self._open_selected_direct_links)
        menu.add_command(label="Copiar link", command=self._copy_selected_direct_link)
        menu.add_command(label="Guardar como oportunidad", command=self._save_selected_direct_links_as_opportunities)
        menu.tk_popup(event.x_root, event.y_root)  # type: ignore[attr-defined]

    def _clear_keywords(self) -> None:
        self.keywords_text.delete("1.0", "end")
        self._update_search_preview()
        self._set_status("Keywords limpiadas.")

    def _expand_marine_engine_keywords(self) -> None:
        keywords = normalize_keywords(self.keywords_text.get("1.0", "end"))
        if not keywords:
            messagebox.showinfo("Sin keywords", "Ingresa una keyword base, por ejemplo: MerCruiser 4.5")
            return

        expanded: list[str] = []
        seen: set[str] = set()
        for keyword in keywords:
            candidates = [keyword, *[template.format(keyword=keyword) for template in MARINE_ENGINE_VARIANTS]]
            for candidate in candidates:
                clean = candidate.strip()
                key = clean.casefold()
                if clean and key not in seen:
                    expanded.append(clean)
                    seen.add(key)

        self.keywords_text.delete("1.0", "end")
        self.keywords_text.insert("1.0", "\n".join(expanded))
        self._update_search_preview()
        self._set_status(f"Keywords expandidas a {len(expanded)} variantes orientadas a motores/bloques.")

    def _selected_locations_by_country(self) -> dict[str, list[dict[str, str]]]:
        selected: dict[str, list[dict[str, str]]] = {}
        for country, country_locations in self.locations.items():
            if not self.country_vars.get(country, ctk.BooleanVar(value=False)).get():
                continue
            selected[country] = [
                location
                for location in country_locations
                if self.location_vars.get(country, {}).get(location["name"], ctk.BooleanVar(value=False)).get()
            ]
        return selected

    def _set_all_locations(self, selected: bool) -> None:
        for country_vars in self.location_vars.values():
            for variable in country_vars.values():
                variable.set(selected)
        self._update_search_preview()

    def _update_search_preview(self) -> None:
        if not hasattr(self, "search_preview_label"):
            return
        keywords = normalize_keywords(self.keywords_text.get("1.0", "end")) if hasattr(self, "keywords_text") else []
        locations_count = sum(len(locations) for locations in self._selected_locations_by_country().values())
        total = len(keywords) * locations_count
        warning = " Recomendado abrir en bloques." if total > 20 else ""
        self.search_preview_label.configure(text=f"Preview: {len(keywords)} keywords x {locations_count} ciudades = {total} URLs.{warning}")

    def _generate_searches(self) -> None:
        keywords = normalize_keywords(self.keywords_text.get("1.0", "end"))
        selected_locations = self._selected_locations_by_country()
        countries = list(selected_locations.keys())
        locations_count = sum(len(locations) for locations in selected_locations.values())

        if not keywords:
            messagebox.showwarning("Faltan keywords", "Ingresa al menos una keyword.")
            return
        if not countries or locations_count == 0:
            messagebox.showwarning("Faltan ubicaciones", "Selecciona al menos un pais y una ciudad.")
            return

        radius_miles = int(self.radius_var.get().split()[0])
        generated = generate_searches(keywords, countries, selected_locations, radius_miles)
        self.generated_searches = [
            {
                "keyword": item.keyword,
                "country": item.country,
                "location": item.location,
                "radius": f"{item.radius_miles} mi / {item.radius_km} km",
                "url": item.url,
                "opened": "No",
            }
            for item in generated
        ]
        self.direct_links = []
        self._set_clipboard_capture(True)
        self._refresh_search_table()
        self._set_status(
            f"Generada sesion con {len(self.generated_searches)} busquedas para revisar manualmente. "
            "La tabla se llenara solo con links especificos /marketplace/item/... que copies desde Facebook."
        )
        self._open_search_indices(list(range(min(10, len(self.generated_searches)))))

    def _refresh_search_table(self) -> None:
        tree = self._get_search_tree()
        tree.delete(*tree.get_children())
        if not self.direct_links:
            tree.insert(
                "",
                "end",
                iid="empty",
                values=(
                    "",
                    "",
                    "",
                    "Copia desde Facebook un link tipo https://www.facebook.com/marketplace/item/1295383876095976/ y aparecera aqui.",
                ),
            )
        for index, item in enumerate(self.direct_links):
            tree.insert(
                "",
                "end",
                iid=str(index),
                values=(item["item_id"], item["opened"], item["saved"], item["url"]),
            )
        opened = sum(1 for item in self.direct_links if item["opened"] == "Yes")
        saved = sum(1 for item in self.direct_links if item["saved"] == "Yes")
        self.search_total_card.configure(text=str(len(self.direct_links)))
        self.search_opened_card.configure(text=str(opened))
        self.search_pending_card.configure(text=str(saved))

    def _selected_search_indices(self) -> list[int]:
        tree = self._get_search_tree()
        return [int(item_id) for item_id in tree.selection()]

    def _open_selected_searches(self) -> None:
        indices = self._selected_search_indices()
        if not indices:
            messagebox.showinfo("Sin seleccion", "Selecciona una o mas busquedas primero.")
            return
        self._open_search_indices(indices)

    def _open_first_ten_searches(self) -> None:
        pending = [index for index, item in enumerate(self.generated_searches) if item["opened"] != "Yes"]
        self._open_search_indices(pending[:10])

    def _open_all_searches(self) -> None:
        self._open_search_indices(list(range(len(self.generated_searches))))

    def _open_search_indices(self, indices: list[int]) -> None:
        if not indices:
            messagebox.showinfo("Sin busquedas", "Genera busquedas primero o no quedan pendientes.")
            return
        if len(indices) > 20:
            proceed = messagebox.askyesno(
                "Abrir muchas pestanas",
                f"Esto abrira {len(indices)} pestanas en tu navegador normal. Continuar?",
            )
            if not proceed:
                self._set_status("Apertura cancelada.")
                return

        try:
            delay = max(0, float(self.delay_entry.get().strip() or DEFAULT_OPEN_DELAY_SECONDS))
        except ValueError:
            messagebox.showwarning("Delay invalido", "El delay debe ser un numero de segundos.")
            return

        self._set_status(f"Abriendo {len(indices)} busquedas con {delay:g}s de delay entre pestanas...")
        threading.Thread(target=self._open_urls_worker, args=(indices, delay), daemon=True).start()

    def _open_urls_worker(self, indices: list[int], delay: float) -> None:
        for position, index in enumerate(indices):
            item = self.generated_searches[index]
            webbrowser.open_new_tab(str(item["url"]))
            self.after(0, self._mark_search_opened, index)
            if position < len(indices) - 1 and delay:
                time.sleep(delay)
        self.after(0, self._set_status, f"Apertura terminada: {len(indices)} busquedas enviadas al navegador.")

    def _mark_search_opened(self, index: int) -> None:
        self.generated_searches[index]["opened"] = "Yes"
        self._refresh_search_table()

    def _copy_selected_search_url(self) -> None:
        indices = self._selected_search_indices()
        if not indices:
            self._set_status("Selecciona una busqueda para copiar su URL.")
            return
        url = str(self.generated_searches[indices[0]]["url"])
        self.clipboard_clear()
        self.clipboard_append(url)
        self._set_status("URL copiada al portapapeles.")

    def _show_search_context_menu(self, event: object) -> None:
        tree = self._get_search_tree()
        row_id = tree.identify_row(event.y)  # type: ignore[attr-defined]
        if row_id:
            tree.selection_set(row_id)

        menu = Menu(self, tearoff=False)
        menu.add_command(label="Abrir publicacion especifica", command=self._open_selected_specific_links)
        menu.add_command(label="Copiar link especifico", command=self._copy_selected_specific_link)
        menu.add_command(label="Guardar como oportunidad", command=self._save_selected_specific_links)
        menu.tk_popup(event.x_root, event.y_root)  # type: ignore[attr-defined]

    def _clear_opportunity_form(self) -> None:
        self.current_opportunity_id = None
        for key, widget in self.form_fields.items():
            if isinstance(widget, ctk.CTkTextbox):
                widget.delete("1.0", "end")
            elif isinstance(widget, ctk.CTkEntry):
                widget.delete(0, "end")
            elif isinstance(widget, ctk.CTkOptionMenu):
                default = {
                    "currency": "USD",
                    "country": "USA",
                    "priority": "Medium",
                    "status": "New",
                }.get(key)
                if default:
                    widget.set(default)
        self.selected_score_label.configure(text="Score: -")
        self._set_status("Formulario listo para una nueva oportunidad.")

    def _form_data(self) -> dict[str, str]:
        data: dict[str, str] = {}
        for key, widget in self.form_fields.items():
            if isinstance(widget, ctk.CTkTextbox):
                data[key] = widget.get("1.0", "end").strip()
            elif isinstance(widget, ctk.CTkEntry):
                data[key] = widget.get().strip()
            elif isinstance(widget, ctk.CTkOptionMenu):
                data[key] = widget.get().strip()
        return data

    def _paste_link_from_clipboard(self) -> None:
        try:
            value = self.clipboard_get().strip()
        except Exception:
            value = ""
        if not value:
            self._set_status("No hay texto en el portapapeles.")
            return
        link_widget = self.form_fields["link"]
        if isinstance(link_widget, ctk.CTkEntry):
            link_widget.delete(0, "end")
            link_widget.insert(0, value)
        self._set_status("Link pegado desde el portapapeles.")

    def _show_bulk_import_dialog(self) -> None:
        dialog = ctk.CTkToplevel(self)
        dialog.title("Importar links en lote")
        dialog.geometry("720x520")
        dialog.transient(self)
        dialog.grab_set()
        dialog.grid_columnconfigure(0, weight=1)
        dialog.grid_rowconfigure(2, weight=1)

        ctk.CTkLabel(
            dialog,
            text="Pega aqui links de publicaciones Marketplace, uno por linea",
            font=ctk.CTkFont(size=18, weight="bold"),
            text_color=NAVY,
        ).grid(row=0, column=0, padx=18, pady=(18, 4), sticky="w")

        ctk.CTkLabel(
            dialog,
            text="Tip: abre Facebook, entra a una publicacion que parezca correcta, copia su URL y pegala aqui. La app no lee Facebook automaticamente.",
            font=ctk.CTkFont(size=12),
            text_color=TEXT_MUTED,
            wraplength=660,
            justify="left",
        ).grid(row=1, column=0, padx=18, pady=(0, 10), sticky="w")

        textbox = ctk.CTkTextbox(dialog, border_width=1)
        textbox.grid(row=2, column=0, padx=18, pady=(0, 12), sticky="nsew")

        footer = ctk.CTkFrame(dialog, fg_color="transparent")
        footer.grid(row=3, column=0, padx=18, pady=(0, 18), sticky="ew")
        footer.grid_columnconfigure(0, weight=1)

        ctk.CTkButton(
            footer,
            text="Cancelar",
            width=120,
            fg_color="#64748b",
            command=dialog.destroy,
        ).grid(row=0, column=1, padx=6)
        ctk.CTkButton(
            footer,
            text="Importar links",
            width=150,
            fg_color=GREEN,
            command=lambda: self._bulk_import_links(textbox.get("1.0", "end"), dialog),
        ).grid(row=0, column=2, padx=6)

    def _bulk_import_links(self, raw_text: str, dialog: ctk.CTkToplevel) -> None:
        urls = self._extract_marketplace_urls(raw_text)
        if not urls:
            messagebox.showwarning("Sin links validos", "No encontre links de Facebook Marketplace en el texto pegado.")
            return

        existing_links = {str(item.get("link") or "") for item in self.storage.list_opportunities()}
        category = self._form_data().get("category") or "Marine engine"
        country = self._form_data().get("country") or "USA"
        imported = 0
        skipped = 0

        for url in urls:
            if url in existing_links:
                skipped += 1
                continue
            self.storage.save_opportunity(
                {
                    "link": url,
                    "title": "",
                    "price": "",
                    "currency": "USD" if country == "USA" else "CAD",
                    "location": "",
                    "country": country,
                    "category": category,
                    "notes": "Imported from manual Marketplace link paste.",
                    "priority": "Medium",
                    "status": "New",
                }
            )
            imported += 1
            existing_links.add(url)

        dialog.destroy()
        self._load_opportunities()
        self._set_status(f"Importados {imported} links manuales. Duplicados omitidos: {skipped}.")

    def _extract_marketplace_urls(self, raw_text: str) -> list[str]:
        urls = re.findall(r"https?://[^\s<>\"]+", raw_text)
        clean_urls: list[str] = []
        seen: set[str] = set()
        for url in urls:
            clean = url.rstrip(".,);]")
            if "facebook.com/marketplace" not in clean.lower():
                continue
            key = clean.casefold()
            if key not in seen:
                clean_urls.append(clean)
                seen.add(key)
        return clean_urls

    def _extract_marketplace_item_urls(self, raw_text: str) -> list[str]:
        urls = re.findall(r"https?://[^\s<>\"]+", raw_text)
        clean_urls: list[str] = []
        seen: set[str] = set()
        for url in urls:
            normalized = self._normalize_marketplace_item_url(url)
            if not normalized:
                continue
            key = normalized.casefold()
            if key not in seen:
                clean_urls.append(normalized)
                seen.add(key)
        return clean_urls

    def _normalize_marketplace_item_url(self, url: str) -> str:
        match = re.search(r"facebook\.com/marketplace/item/(\d+)", url, flags=re.IGNORECASE)
        if not match:
            return ""
        return f"https://www.facebook.com/marketplace/item/{match.group(1)}/"

    def _marketplace_item_id(self, url: str) -> str:
        match = re.search(r"/marketplace/item/(\d+)/?", url, flags=re.IGNORECASE)
        return match.group(1) if match else ""

    def _open_form_link(self) -> None:
        link = self._form_data().get("link", "")
        if not link:
            self._set_status("No hay link en el formulario.")
            return
        webbrowser.open_new_tab(link)
        self._set_status("Link del formulario abierto en el navegador.")

    def _save_opportunity(self) -> None:
        data = self._form_data()
        if data.get("link") and "facebook.com/marketplace" not in data["link"].lower():
            proceed = messagebox.askyesno(
                "Link no parece Marketplace",
                "El link no parece ser de Facebook Marketplace. Guardarlo igualmente?",
            )
            if not proceed:
                return

        existing = [
            item for item in self.storage.list_opportunities()
            if item.get("link") == data.get("link") and item.get("id") != self.current_opportunity_id
        ]
        if existing:
            proceed = messagebox.askyesno(
                "Posible duplicado",
                "Ya existe una oportunidad con este link. Guardar de todas formas?",
            )
            if not proceed:
                return

        try:
            saved_id = self.storage.save_opportunity(data, self.current_opportunity_id)
        except ValueError as error:
            messagebox.showwarning("No se puede guardar", str(error))
            return

        self._load_opportunities()
        self._clear_opportunity_form()
        self._set_status(f"Oportunidad guardada correctamente. ID #{saved_id}.")

    def _load_opportunities(self) -> None:
        self.opportunities = self.storage.list_opportunities()
        self._refresh_opportunity_table(self.opportunities)

    def _refresh_opportunity_table(self, opportunities: list[dict[str, object]]) -> None:
        tree = self._get_opportunity_tree()
        tree.delete(*tree.get_children())
        for item in opportunities:
            tree.insert(
                "",
                "end",
                iid=str(item["id"]),
                values=(
                    item.get("score", ""),
                    item.get("priority", ""),
                    item.get("status", ""),
                    item.get("title", ""),
                    item.get("price", ""),
                    item.get("currency", ""),
                    item.get("location", ""),
                    item.get("country", ""),
                    item.get("category", ""),
                    item.get("link", ""),
                    item.get("date_added", ""),
                    item.get("last_updated", ""),
                ),
            )
        self._update_opportunity_metrics(opportunities)

    def _update_opportunity_metrics(self, opportunities: list[dict[str, object]]) -> None:
        high = sum(1 for item in opportunities if item.get("priority") == "High")
        active = sum(1 for item in opportunities if item.get("status") not in {"Discarded", "Purchased"})
        best = max((float(item.get("score") or 0) for item in opportunities), default=0)
        self.saved_total_card.configure(text=str(len(opportunities)))
        self.saved_high_card.configure(text=str(high))
        self.saved_active_card.configure(text=str(active))
        self.saved_best_card.configure(text=f"{best:.0f}" if best else "-")

    def _apply_opportunity_filter(self) -> None:
        query = self.opportunity_filter_var.get().strip().casefold()
        if not query:
            self._refresh_opportunity_table(self.opportunities)
            return

        filtered = [
            item for item in self.opportunities
            if query in " ".join(str(item.get(key, "")) for key in (
                "title", "location", "country", "category", "link", "status", "priority", "notes"
            )).casefold()
        ]
        self._refresh_opportunity_table(filtered)
        self._set_status(f"Filtro aplicado: {len(filtered)} de {len(self.opportunities)} oportunidades visibles.")

    def _load_selected_opportunity(self, _event: object | None = None) -> None:
        tree = self._get_opportunity_tree()
        selected = tree.selection()
        if not selected:
            return

        opportunity_id = int(selected[0])
        selected_item = next((item for item in self.opportunities if item["id"] == opportunity_id), None)
        if not selected_item:
            return

        self.current_opportunity_id = opportunity_id
        for key, widget in self.form_fields.items():
            value = str(selected_item.get(key) or "")
            if isinstance(widget, ctk.CTkTextbox):
                widget.delete("1.0", "end")
                widget.insert("1.0", value)
            elif isinstance(widget, ctk.CTkEntry):
                widget.delete(0, "end")
                widget.insert(0, value)
            elif isinstance(widget, ctk.CTkOptionMenu):
                widget.set(value or widget.get())
        self.selected_score_label.configure(text=f"Score: {selected_item.get('score', '-')}")
        self._set_status(f"Oportunidad #{opportunity_id} cargada en el formulario.")

    def _selected_opportunity(self) -> dict[str, object] | None:
        tree = self._get_opportunity_tree()
        selected = tree.selection()
        if not selected:
            return None
        opportunity_id = int(selected[0])
        return next((item for item in self.opportunities if item["id"] == opportunity_id), None)

    def _open_selected_opportunity_link(self) -> None:
        item = self._selected_opportunity()
        if not item:
            self._set_status("Selecciona una oportunidad para abrir su link.")
            return
        link = str(item.get("link") or "")
        if link:
            webbrowser.open_new_tab(link)
            self._set_status("Link de oportunidad abierto en el navegador.")

    def _copy_selected_opportunity_link(self) -> None:
        item = self._selected_opportunity()
        if not item:
            self._set_status("Selecciona una oportunidad para copiar su link.")
            return
        link = str(item.get("link") or "")
        self.clipboard_clear()
        self.clipboard_append(link)
        self._set_status("Link de oportunidad copiado al portapapeles.")

    def _delete_selected_opportunity(self) -> None:
        item = self._selected_opportunity()
        if not item:
            self._set_status("Selecciona una oportunidad para eliminar.")
            return
        title = item.get("title") or item.get("link") or f"ID #{item.get('id')}"
        proceed = messagebox.askyesno("Eliminar oportunidad", f"Eliminar esta oportunidad?\n\n{title}")
        if not proceed:
            return
        self.storage.delete_opportunity(int(item["id"]))
        self.current_opportunity_id = None
        self._load_opportunities()
        self._clear_opportunity_form()
        self._set_status("Oportunidad eliminada.")

    def _show_opportunity_context_menu(self, event: object) -> None:
        tree = self._get_opportunity_tree()
        row_id = tree.identify_row(event.y)  # type: ignore[attr-defined]
        if row_id:
            tree.selection_set(row_id)

        menu = Menu(self, tearoff=False)
        menu.add_command(label="Cargar en formulario", command=self._load_selected_opportunity)
        menu.add_command(label="Abrir link", command=self._open_selected_opportunity_link)
        menu.add_command(label="Copiar link", command=self._copy_selected_opportunity_link)
        menu.add_separator()
        menu.add_command(label="Eliminar", command=self._delete_selected_opportunity)
        menu.tk_popup(event.x_root, event.y_root)  # type: ignore[attr-defined]

    def _sort_opportunities(self) -> None:
        self.opportunities = sort_best_first(self.storage.list_opportunities())
        self._refresh_opportunity_table(self.opportunities)
        self._set_status("Oportunidades ordenadas por mejor score.")

    def _export_csv(self) -> None:
        self._export("csv")

    def _export_excel(self) -> None:
        self._export("xlsx")

    def _export(self, extension: str) -> None:
        opportunities = sort_best_first(self.storage.list_opportunities())
        if not opportunities:
            messagebox.showinfo("Nada para exportar", "Agrega al menos una oportunidad antes de exportar.")
            return

        filetypes = [("CSV files", "*.csv")] if extension == "csv" else [("Excel files", "*.xlsx")]
        path = filedialog.asksaveasfilename(
            title="Export opportunities",
            defaultextension=f".{extension}",
            filetypes=filetypes,
        )
        if not path:
            return

        if extension == "csv":
            export_csv(opportunities, Path(path))
        else:
            export_excel(opportunities, Path(path))
        messagebox.showinfo("Export complete", f"Reporte guardado en:\n{path}")
        self._set_status(f"Export completado: {path}")
