# Taller Automotriz Vittorio — gestión de cotizaciones, clientes y vehículos

Reemplazo de la planilla Excel con la que se cotizaba hasta ahora. Misma lógica de cálculo,
pero además con ficha por cliente y por auto, historial mecánico por patente, avisos de
seguimiento, documento imprimible, firma del cliente y enlace para compartir.

**En línea:** https://jpchs1.github.io/trabajos-varios/taller-vittorio/

## Qué hace

| | |
| --- | --- |
| **Tablero** | Lo del mes en un vistazo: cotizado (con variación vs. el mes anterior), por cobrar, trabajos en el taller y clientes. Gráfico de los últimos 6 meses, cotizaciones sin respuesta y vehículos a los que les toca mantención. |
| **Cotizaciones** | Lista completa con búsqueda por patente, cliente o folio, filtros por estado (borrador, enviada, aprobada, en taller, entregada, anulada) y orden por fecha, monto o cliente. |
| **Clientes** | Ficha por cliente: datos, sus vehículos, cuánto lleva gastado, saldo pendiente e historial completo de atenciones. Editar la ficha actualiza sus cotizaciones. |
| **Vehículos** | Ficha por patente: datos del auto, kilometraje, historial mecánico (qué se le hizo, cuándo y con cuántos km) y próxima mantención sugerida. Botón para avisarle al cliente por WhatsApp. |
| **Cotizar** | Datos del vehículo y del cliente, líneas de mano de obra y de repuestos, abonos. El total se recalcula en vivo en la barra lateral. Si la patente ya vino antes, la app lo avisa y completa los datos sola. |
| **Catálogo** | Servicios y repuestos con precio. Se agregan con `⌘K` o desde *Del catálogo*: se busca, se aprieta Enter y la línea queda cargada con precio y observación. |
| **Fotos y firma** | Fotos del estado del vehículo (se comprimen antes de guardarse) y firma del cliente con el dedo en el celular, que sale impresa en la orden de trabajo. |
| **Documento** | Vista imprimible en A4 (Imprimir → Guardar como PDF), en dos formatos: **cotización** para el cliente y **orden de trabajo** con casilleros de "realizado" y firmas. Se puede ocultar el valor unitario de los repuestos. |
| **Compartir** | Enlace de sólo lectura que lleva la cotización dentro de la URL: el cliente la abre en el navegador, sin instalar ni registrarse. También sale por WhatsApp o correo con el resumen ya escrito. |
| **Ajustes** | Datos del taller, IVA, porcentaje de gastos, validez, plazos de seguimiento y mantención, garantía y condiciones. Respaldo en JSON, exportación a CSV y medidor de espacio usado. |

## Cómo se calcula (igual que la planilla)

```
Total mano de obra   = suma de las líneas de trabajo
Total repuestos      = suma (cantidad × valor unitario) + gastos (% configurable, 10 % por defecto)
Neto                 = mano de obra + repuestos − descuento
Total                = neto + IVA (opcional, 19 %)
A pagar              = total − abonos
```

La cotización de ejemplo que viene cargada es la misma del Jaguar XF 30 de la planilla:
$365.000 de mano de obra + $220.000 de repuestos con gastos = **$585.000**.

## Cómo se arman las fichas

No hay que cargar clientes ni autos aparte: **se crean solos al cotizar**. Cuando escribís una
patente, la app busca si ese auto ya vino; cuando escribís un nombre o teléfono, hace lo mismo
con el cliente. A partir de ahí:

- la patente queda con su historial de trabajos y su kilometraje;
- el cliente queda con todos sus autos y todo lo que gastó;
- si el auto ya vino, aparece un aviso con la última atención y un botón para completar los datos;
- desde cualquier ficha se crea una atención nueva con todo precargado.

## Dónde viven los datos

Todo queda en el `localStorage` del navegador que se usa: no hay servidor, ni cuenta, ni base de
datos que administrar. Eso significa que:

- funciona sin internet una vez abierta (es una PWA: se puede "instalar" en el celular);
- las cotizaciones son de ese dispositivo. Para pasarlas a otro, o para no perderlas si se borra
  el navegador, hay que usar **Ajustes → Descargar respaldo** y luego **Restaurar respaldo**;
- el enlace compartido no depende del taller: viaja completo dentro de la URL (las fotos no se
  incluyen, para que el enlace siga siendo mandable por WhatsApp).

## Atajos

| Atajo | Qué hace |
| --- | --- |
| `⌘K` / `Ctrl+K` | Buscador global (cotizaciones, clientes, patentes) y acciones rápidas |
| `Enter` en la última línea | Agrega otra línea |
| `Enter` en el catálogo | Agrega el ítem marcado y deja el buscador abierto |
| `Esc` | Cierra el diálogo abierto |

## Estructura

```
taller-vittorio/
├── index.html              Marco de la app, navegación y sprite de íconos
├── manifest.webmanifest    Para instalarla como app
├── sw.js                   Service worker (funciona sin conexión)
└── assets/
    ├── styles.css          Diseño, tema claro/oscuro y hoja de impresión
    ├── datos.js            Catálogo inicial y cotización de ejemplo
    ├── app.js              Datos, cálculo, router y acciones globales
    ├── vistas.js           Tablero, cotizaciones, clientes, vehículos, catálogo y ajustes
    ├── editor.js           Editor de cotización, fotos y firma
    └── documento.js        Documento imprimible y vista compartida
```

Sin dependencias ni compilación: son archivos estáticos. Para trabajarlo localmente basta
abrir `index.html`, o levantar un servidor si se quiere probar el modo sin conexión:

```sh
python3 -m http.server 8000   # luego: http://localhost:8000/taller-vittorio/
```
