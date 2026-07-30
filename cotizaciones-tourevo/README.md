# Cotizaciones Tourevo / CDSKI — formato 3.0

Cotizaciones formales para **Tourevo Private Tours** y su línea de clases de ski
**CDSKI** ([clasesdeski.cl](https://clasesdeski.cl/)), en el estilo de documento web
autocontenido que se usa en este repositorio: una sola página HTML, sin dependencias
externas, legible en el teléfono y lista para imprimir o guardar como PDF.

## Cotizaciones emitidas

| Nº | Cliente | Programa | Total | Documento |
|---|---|---|---:|---|
| COT-CDSKI-2026-0730-01 | Gonzalo (gonroca@gmail.com) | 2 adultos · 2 días · clase privada medio día · **1 instructor por persona** · Valle Nevado, 08–09 ago 2026 | **USD 1.016** | [`gonzalo-cdski/index.html`](gonzalo-cdski/index.html) · [mensajes listos para enviar](gonzalo-cdski/mensaje-whatsapp.md) |

## Caso Gonzalo — de qué se trata

Gonzalo llegó por el cotizador del sitio con una estimación automática que **no correspondía
al programa real**: 1 adulto, 3 días, CLP 825.000 (≈ USD 868). En la conversación por WhatsApp
el alcance cambió dos veces:

1. **De 1 a 2 pasajeros y de 3 a 2 días** — viaja con su señora y quieren 2 días de medio día.
2. **De instructor compartido a instructor 1 a 1** — se le ofreció primero la modalidad
   compartida (USD 512 por los 2 días para ambos), pero él baja **pistas rojas y negras**
   mientras su señora **todavía no baja azules**. Con instructor compartido la clase se ajusta
   al nivel del más principiante, así que no servía.

La cotización recoge el alcance final: **USD 254 por persona por día → USD 508 por persona por
los 2 días → USD 1.016 en total**, con un instructor dedicado para cada uno.

### Datos que definen la cotización

- **Pasajeros:** 2 adultos, 0 niños.
- **Actividad:** ski, privada, medio día = **3 horas de clase por día por persona** (CDSKI).
- **Fechas:** sábado 08 y domingo 09 de agosto de 2026 (el segundo día queda por confirmar).
- **Centro de ski:** Valle Nevado. **Alojamiento:** Hotel Puertas del Sol, dentro del centro,
  por lo que **no hay traslado desde Santiago**.
- **Horarios:** pidió mañana. El inicio más temprano es **10:30 h** por condición de pistas y
  **11:00 h para principiantes**. Propuesta: Gonzalo 10:30–13:30, su señora 11:00–14:00.
- **Equipo:** no requiere arriendo (equipo propio). Si lo necesitaran, el arriendo CDSKI es de
  **CLP 45.000 por persona** (ski + bastones + casco, botas no incluidas).
- **Ticket de andarivel:** **no incluido**, se compra directamente en Valle Nevado.
- **Moneda:** cotizado en USD, tal como se conversó. Los montos en CLP son referenciales
  a **CLP 950 por USD** (tipo de cambio implícito en la estimación original del sitio:
  825.000 / 868 = 950,5).

### Lo que quedó deliberadamente abierto

La conversación no fijó forma de pago, anticipo ni política de cancelación, así que el
documento **no los inventa**: los lista en la sección 9.2 como puntos a confirmar por escrito
antes de cualquier cobro, junto con los nombres completos de los dos pasajeros y el horario
definitivo.

## Estructura del documento

Cada cotización es una carpeta con un `index.html` autocontenido (CSS embebido, sin JS externo,
sin imágenes remotas) y, cuando aplica, un archivo con los mensajes listos para enviar:

```
cotizaciones-tourevo/
├── README.md
└── gonzalo-cdski/
    ├── index.html              ← la cotización
    └── mensaje-whatsapp.md     ← textos para WhatsApp y correo
```

Secciones del formato 3.0: resumen con cifras destacadas → datos de la reserva → justificación
técnica de la modalidad → programa día por día con horarios → inversión desglosada → alternativas
comparadas (para que se vea de dónde viene cada número enviado por WhatsApp) → incluye / no
incluye → logística → condiciones y puntos por confirmar → próximos pasos.

## Cómo verla

- **Local:** abrir `gonzalo-cdski/index.html` en el navegador.
- **Imprimir / PDF:** el botón «Imprimir / Guardar PDF» del documento, o `Ctrl/Cmd + P`. Los
  estilos de impresión ocultan los botones y pasan el encabezado a blanco y negro.
- **Publicada con GitHub Pages:** `https://jpchs1.github.io/trabajos-varios/cotizaciones-tourevo/gonzalo-cdski/`
  (requiere Pages habilitado en Settings → Pages → Deploy from a branch → `main` → `/ (root)`).

> **Nota de privacidad:** el documento contiene el correo y el teléfono del cliente. Si se va a
> publicar en un sitio público, conviene enviarlo como PDF por correo en lugar de dejarlo
> indexable, o quitar los datos de contacto de la sección 2 antes de publicar.

## Fuentes de los datos operativos

- Conversación de WhatsApp del 29-07-2026 con el cliente (valores USD 254 / 508 / 512, niveles,
  fechas, alojamiento, horarios de inicio 10:30 / 11:00).
- [clasesdeski.cl](https://clasesdeski.cl/) — CDSKI: medio día = 3 h, full day = 5 h; arriendo de
  equipo CLP 45.000 por persona (botas no incluidas); ticket de pista no incluido; ropa y guantes
  se arriendan en el centro de ski; contacto WhatsApp +56 9 4021 1459, info@clasesdeski.cl,
  atención de lunes a domingo 08:00–22:00.
- [tourevo.cl](https://tourevo.cl/) — operación de tours privados y link de pago
  [tourevo.cl/pago/](https://tourevo.cl/pago/) para pagos desde el exterior.
