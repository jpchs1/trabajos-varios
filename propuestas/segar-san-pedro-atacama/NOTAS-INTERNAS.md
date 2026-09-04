# COT-2026-0160 — Segar · San Pedro de Atacama

Revisión previa al envío. **No se publica**: `scripts/deploy-ftp.sh` excluye `*.md`.

- **Fuente:** programa ajustado que envió la agencia de San Pedro de Atacama a Juan Pablo.
- **Emitida:** 04 sep 2026 · **Válida hasta:** 23 sep 2026 · **Viaje:** 22–26 dic 2026.

---

## Lo que se dedujo (y con qué se comprobó)

### Cantidad de pasajeros: 4

El programa nunca lo dice. Sale de las dos cifras del pie del correo, y las dos cierran:

- 40% del programa = CLP 928.000 → total programa CLP 2.320.000 → ÷ 580.000 pp = **4 pax**.
- Entradas por adelantado: Cejar 21.000 + Puritama 35.000 + Piedras Rojas 15.000 = **71.000 pp** × 4 = **CLP 284.000** ✓ (la cifra exacta del correo).
- Abono total: 928.000 + 284.000 = **CLP 1.212.000** ✓.

**Confirmar con el cliente igual.** Si alguno es menor, el total baja: hay tarifa
reducida en 3 de los 4 parques (<18 en Arcoíris y Piedras Rojas, <13 en Cejar).
La cotización los tarifica a los cuatro como adultos y lo dice explícitamente.

### Fechas: 22–26 diciembre 2026

El programa dice "Martes 22" a "Sábado 26" sin mes. En el calendario cercano el
martes 22 / sábado 26 sólo calza en **sep 2026**, **dic 2026** y **jun 2027**.
Confirmado por JP: diciembre 2026. Es además el único de los tres donde la
política de 90/60 días del operador todavía tiene sentido al emitir.

### Los CLP 284.000 son SÓLO las entradas por adelantado

Ojo con esto al hablar con el cliente. Las entradas totales son **CLP 324.000**
(81.000 pp): las tres por adelantado más **Valle del Arcoíris, CLP 10.000 pp =
40.000, que se paga en la portería el mismo día** y no está dentro del abono.

Cuadratura completa:

| Concepto | CLP |
| --- | --- |
| Abono de confirmación (40% programa + entradas anticipadas) | 1.212.000 |
| Saldo antes de iniciar (60% programa) | 1.392.000 |
| Entradas Arcoíris, en portería el miércoles 23 | 40.000 |
| **Total** | **2.644.000** |

Programa 2.320.000 + entradas 324.000 = 2.644.000 · CLP 661.000 por persona.

---

## Errores y avisos que encontramos en el programa del operador

1. **"Jueves 24, full day: Valle de la Luna Sur (16:30 a 20:30)"** — no es full
   day, es PM. El jueves son dos medios días (Puritama AM + Valle de la Luna PM)
   con 3 horas libres al medio. El precio cotizado, 75.000, es de medio día, así
   que **el precio está bien y la etiqueta está mal**. En la cotización va
   listado como PM. *Igual conviene confirmarlo con el operador por escrito*,
   por si fuese al revés (servicio full day mal tarifado).
2. **El jueves 24 es Nochebuena** y vuelven al pueblo a las 20:30. En San Pedro
   las cocinas cierran temprano ese día o hacen menú único con reserva. Es el
   punto más accionable de toda la cotización y por eso va primero.
3. **El viernes 25 es Navidad** y es el día largo (10:00–18:00, almuerzo incluido
   en terreno). Sin problema operativo, pero el pueblo va a estar cerrado al
   volver.
4. **Altura bien ordenada:** Piedras Rojas / lagunas altiplánicas (~4.500 m) cae
   al cuarto día, después de tres noches a 2.400 m. No moverlo.
5. **Sábado 26:** el traslado deja en El Loa a las 11:45. El vuelo de salida
   debería ser de las 13:15 en adelante.
6. **Valle de la Luna Sur no cobra entrada hoy**, pero el operador avisa que
   podría cobrarla en diciembre. Queda dicho en la tabla de entradas.

---

## Decisiones comerciales tomadas

- **Sin markup.** Por la política de transparencia que pidió JP, el documento
  dice explícitamente que **el pago va directo a la agencia de San Pedro**, a
  nombre del pasajero, y que Tourevo no recibe, retiene ni transfiere ese
  dinero, ni toma comisión sobre esas cifras. Hay una sección propia
  ("How you pay — and who you pay") y un punto en el sello Tourevo.
- **No se incluyó fee de Tourevo.** A diferencia de la COT-2026-0159 (Kiran
  Shah), acá no hay planes con tarifa fija. **Si corresponde cobrar fee,
  decidirlo antes de enviar** y agregarlo como sección aparte — el documento ya
  deja el terreno preparado ("What you pay Tourevo is separate").
- **Idioma: inglés.** El operador aclara que las entradas están al valor de
  pasajero extranjero, y las dos cotizaciones anteriores del portafolio también
  van en inglés. Si Segar prefiere español, es un cambio de texto, no de
  estructura.

---

## Pendientes antes de enviar

- [ ] **Nombre completo y correo de Segar.** El bloque "Prepared for" hoy dice
      sólo `Segar & party · 4 travellers`, sin correo.
- [ ] **Composición del grupo** (adultos / menores). Cambia el total de entradas.
- [ ] **Nombre de la persona de la agencia de San Pedro.** El documento promete
      presentarla por nombre al confirmar; conviene tenerlo listo.
- [ ] **Datos bancarios de la agencia**, para el momento del abono.
- [ ] **Referencia en USD.** Va como "roughly USD 2,700–2,850 at recent rates",
      declarada como indicativa. Actualizar con el tipo de cambio del día o
      borrar la línea.
- [ ] **Confirmar por escrito con el operador** el punto 1 de arriba (jueves PM
      vs full day) y que las tarifas de menores sigan vigentes en diciembre.
- [ ] **Cena de Nochebuena:** definir con Segar y reservar.

---

## Nota sobre dónde vive este archivo

`propuestas/` se había sacado de este repo en el commit `5cf66d2` ("se mudó a
tourevo-cl/website/propuestas/"). Esta cotización se creó acá porque la sesión
venía apuntada a `jpchs1/trabajos-varios`, rama
`claude/segar-san-pedro-atacama-0tn9yz`. **Si el destino definitivo es
`jpchs1/tourevo-cl`, hay que portarla** — es una carpeta con dos archivos y el
PDF, se mueve tal cual. Mientras tanto el workflow de FTP de este repo no sube
nada porque nunca tuvo los secrets cargados.

## Generar el PDF de nuevo

```bash
cd propuestas/segar-san-pedro-atacama
chromium --headless --no-pdf-header-footer \
  --print-to-pdf=Tourevo-COT-2026-0160-Segar.pdf "file://$PWD/index.html"
```

El `@media print` del documento fija A4, márgenes de 12/10 mm y evita que se
corten las tarjetas de día, las tablas y los bloques de condiciones. Salen 10
páginas.
