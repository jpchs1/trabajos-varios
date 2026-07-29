# Pruebas E2E — Aloha Pay en tourevo.cl /pago/

**Fecha:** 29-07-2026 · **Sitio probado:** https://tourevo.cl/pago/ (producción, post-deploy)
**Guion:** `aloha-deploy-y-prueba.md` (PR #756 de `jpchs1/tourevo-cl`, rama `setup/initial-structure`)
**Método:** navegador Chromium automatizado (Playwright), desktop 1366px y mobile 375px.
**Importante:** no se completó ningún pago real — el flujo se detuvo en el checkout de Aloha, tal como indicaba el guion.

## Resultado general: ✅ APROBADO (7/7 puntos del guion)

| # | Prueba | Resultado | Evidencia |
|---|--------|-----------|-----------|
| 1 | `GET /api/aloha.php?action=config` responde `enabled:true` | ✅ `{"success":true,"enabled":true,"currency":"CLP","min_amount_clp":2000}` | — |
| 2 | Tile "Aloha Pay" visible junto a WebPay / MercadoPago / PayPal, con logo y textos correctos, en desktop y mobile 375px | ✅ Logo `aloha.svg` carga bien; textos sin errores ortográficos; layout correcto en ambos tamaños | `01`, `02`, `03` |
| 3 | Flujo con teléfono chileno (+569…) y $5.000 CLP redirige a `checkout.alohapay.co/s/<uuid>` con monto y concepto correctos, ofreciendo transferencia y tarjeta (WebPay) | ✅ Redirigió a `checkout.alohapay.co/s/18c986c9-…`; muestra "$5.000 CLP", el concepto, "Pay with your bank" (transferencia) y "Webpay — Credit, debit or prepaid card". **No se pagó.** | `04`, `05` |
| 4 | Al volver a /pago/ NO aparece "pago exitoso"; `?action=status` responde `paid:false` | ✅ Sin overlay de éxito; respuesta: `{"success":true,"paid":false,"status":"active","amount":5000,"currency":"CLP"}` | `06` |
| 5a | Monto menor a $2.000 CLP da error claro | ✅ "Error al procesar el pago: El monto mínimo con Aloha es $2.000 CLP." (no redirige) | `07` |
| 5b | Con moneda USD, Aloha no se ofrece (solo PayPal/Google Pay) | ✅ Con `?moneda=USD` queda seleccionado PayPal; al ingresar teléfono extranjero (+1…) se ocultan Aloha, WebPay y MercadoPago | `08` |
| 6 | WebPay, MercadoPago, PayPal y Google Pay siguen funcionando | ✅ WebPay y MercadoPago seleccionables con su botón correcto; sin errores nuevos atribuibles a Aloha (ver observaciones sobre PayPal/Google Pay) | `09` |
| 7 | Sin credenciales de Aloha en el HTML/JS del navegador | ✅ Se escanearon 51 respuestas (HTML/JS/JSON) buscando `pay_…` y `X-API-KEY`: **0 coincidencias**. La API key vive solo server-side | — |

## Observaciones (no bloqueantes)

1. **Texto del hero desactualizado:** /pago/ dice "Pagá seguro · **4 métodos** integrados" y el subtítulo lista "PayPal · WebPay (Transbank) · MercadoPago · Google Pay" — no menciona Aloha Pay (ya son 5 métodos). Los chips de "ACEPTAMOS" sí incluyen "Aloha Pay". Sugerido: actualizar a "5 métodos" y agregar Aloha al subtítulo.
2. **Checkout de Aloha en inglés por defecto:** en un navegador nuevo (sin cookies) el checkout abrió en inglés ("Pay with your bank", selector "EN" arriba a la derecha). Si la mayoría de los clientes son hispanohablantes, conviene revisar en el panel de Aloha si se puede predeterminar español.
3. **Link de prueba pendiente:** la prueba creó el payment link `18c986c9-0184-4737-a5d1-629d046419ef` ($5.000 CLP, "Prueba integración Aloha Pay (test - no pagar)"), que quedó `active` sin pagar. Se puede anular desde el panel de Aloha para no dejar basura.
4. **PayPal / Google Pay no verificables desde este entorno:** el sandbox donde corrió la prueba bloquea `www.paypal.com` (403 del proxy de egreso corporativo), por lo que el SDK de PayPal no cargó y Google Pay (que depende de él) tampoco. Esto es una limitación del entorno de prueba, **no** un error del sitio; los endpoints propios (`paypal.php`) responden bien. Vale una verificación visual rápida desde un navegador normal.
5. **Ruidos de consola pre-existentes (no introducidos por Aloha):** el CSP del sitio bloquea el beacon de Cloudflare Insights (`static.cloudflareinsights.com`) y el manifiesto de pago `https://www.google.com/pay` (directiva `connect-src`). Ocurren en cada carga, con o sin Aloha. Si se quiere consola limpia: agregar esos orígenes al CSP o desactivar el beacon de Cloudflare.

## Capturas

| Archivo | Contenido |
|---------|-----------|
| `capturas/01-tiles-desktop.png` | Selector de métodos en desktop con el tile Aloha Pay |
| `capturas/02-tile-aloha-detalle.png` | Detalle del tile (logo, badge "CHILE · CLP", chips) |
| `capturas/03-tiles-mobile-375px.png` | Tiles en viewport móvil de 375px |
| `capturas/04-formulario-listo.png` | Formulario completo antes de enviar ($5.000, Aloha seleccionado) |
| `capturas/05-checkout-aloha.png` | Checkout de Aloha: monto, concepto, transferencia y Webpay |
| `capturas/06-retorno-sin-pago-exitoso.png` | Retorno a /pago/ sin overlay de "pago exitoso" |
| `capturas/07-error-monto-minimo.png` | Error claro con monto $1.500 (< mínimo $2.000) |
| `capturas/08-usd-telefono-extranjero.png` | Con USD + teléfono extranjero solo queda PayPal |
| `capturas/09-otros-metodos.png` | WebPay/MercadoPago operativos tras las pruebas |
