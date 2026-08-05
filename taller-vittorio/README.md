# Taller Automotriz Vittorio — sistema de cotización

Reemplazo de la planilla Excel con la que se cotizaba hasta ahora. Misma lógica de cálculo,
pero con catálogo de precios, buscador, documento imprimible, enlace para el cliente y todo
funcionando en el celular del taller.

**En línea:** https://jpchs1.github.io/trabajos-varios/taller-vittorio/

## Qué hace

| | |
| --- | --- |
| **Tablero** | Todas las cotizaciones con estado (borrador, enviada, aprobada, en taller, entregada), búsqueda por patente/cliente/folio y KPIs del mes: cotizado, por cobrar, en curso y ticket promedio. |
| **Cotizar** | Datos del vehículo y del cliente, líneas de mano de obra y de repuestos, abonos. El total se recalcula en vivo en la barra lateral. |
| **Catálogo** | Servicios y repuestos con precio. Se agregan a la cotización con ⌘K o desde *Del catálogo*: se busca, se aprieta Enter y la línea queda cargada con precio y observación. |
| **Documento** | Vista imprimible en A4 (Imprimir → Guardar como PDF), en dos formatos: **cotización** para el cliente y **orden de trabajo** con casilleros de "realizado" y firmas. |
| **Compartir** | Genera un enlace de sólo lectura que lleva la cotización dentro de la URL: el cliente la abre en el navegador, sin instalar ni registrarse. También sale por WhatsApp o correo con el resumen ya escrito. |
| **Ajustes** | Datos del taller, IVA, porcentaje de gastos, validez, garantía y condiciones. Respaldo en JSON y exportación a CSV. |

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

## Dónde viven los datos

Todo queda en el `localStorage` del navegador que se usa: no hay servidor, ni cuenta, ni base de
datos que administrar. Eso significa que:

- funciona sin internet una vez abierta (es una PWA: se puede "instalar" en el celular);
- las cotizaciones son de ese dispositivo. Para pasarlas a otro, o para no perderlas si se borra
  el navegador, hay que usar **Ajustes → Descargar respaldo** y luego **Restaurar respaldo**;
- el enlace compartido no depende del taller: viaja completo dentro de la URL.

## Atajos

| Atajo | Qué hace |
| --- | --- |
| `⌘K` / `Ctrl+K` | Buscador y acciones rápidas |
| `Enter` en la última línea | Agrega otra línea |
| `Enter` en el catálogo | Agrega el ítem marcado y deja el buscador abierto |
| `Esc` | Cierra el diálogo abierto |

## Estructura

```
taller-vittorio/
├── index.html              Marco de la app y sprite de íconos
├── manifest.webmanifest    Para instalarla como app
├── sw.js                   Service worker (funciona sin conexión)
└── assets/
    ├── styles.css          Diseño, tema claro/oscuro y hoja de impresión
    ├── datos.js            Catálogo inicial y cotización de ejemplo
    └── app.js              Estado, cálculo, vistas y documento
```

Sin dependencias ni compilación: son archivos estáticos. Para trabajarlo localmente basta
abrir `index.html`, o levantar un servidor si se quiere probar el modo sin conexión:

```sh
python3 -m http.server 8000   # luego: http://localhost:8000/taller-vittorio/
```
