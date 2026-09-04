# COT-2026-0160 — Segar · San Pedro de Atacama

Revisión previa al envío. **No se publica**: `scripts/deploy-ftp.sh` excluye `*.md`.

- **Fuente:** el correo con el programa ajustado que la agencia de San Pedro de
  Atacama le envió a Juan Pablo.
- **Relación:** es una **agencia de turismo asociada a la nuestra, que
  recomendamos** — no es "nuestro operador". Los dos PDF la tratan así: la
  primera mención dice "an associated travel agency in San Pedro de Atacama, one
  we work alongside and recommend", y de ahí en adelante es "the agency". No
  queda ni una mención a "our operator" en ninguno de los dos documentos.
- **Emitida:** 04 sep 2026 · **Viaje:** 22–26 dic 2026 · 4 pasajeros.

## Regla de este documento

**Los dos PDF sólo contienen lo que dice ese correo.** Nada de datos propios:
sin altitudes, sin distancias, sin clima, sin códigos de aeropuerto, sin
conversión a dólares, sin consejos de equipaje ni de vuelos, sin exclusiones que
la agencia no listó. Los siete servicios van en el orden y con los horarios,
descripciones, inclusiones y precios que él escribió, incluida la etiqueta "full
day" del jueves 24 tal cual la puso.

Y queda dicho en los dos documentos: **el formato es nuestro, el contenido es de
ellos**. La nota de apertura de la cotización lo dice ("what we have done is put
their quotation into this format so that it is easier to read and follow — the
programme, the prices and the terms are theirs, unchanged"), el pie de la última
página lo repite, y la carta lo dice en su primer párrafo.

Lo único que se agrega es de forma, no de contenido: la traducción al inglés, el
orden visual, y tres cosas que sí vienen de JP y no de la agencia —

- el nombre de Segar y las fechas de diciembre 2026 (el correo dice sólo
  "Martes 22" y "Sábado 26", sin mes);
- la **política de transparencia**: el pago va directo a la agencia de San Pedro,
  a nombre del pasajero, y Tourevo no agrega ni retiene nada;
- el **cierre por disponibilidad**, redactado sobre las palabras de la propia
  agencia ("nuestra agenda se llena anticipadamente y ya no admite el ingreso
  de otras nuevas").

Lo demás derivado es aritmética sobre sus propias cifras: 580.000 × 4 =
2.320.000 (consistente con su 40% = 928.000), y las fechas 23 sep / 23 oct, que
son sus plazos de 90 y 60 días aplicados al 22 de diciembre. Las dos van dichas
como lo que son.

## Lo que NO se publicó, a propósito

**El total de entradas.** Sumadas como él las lista, las entradas dan 81.000 por
persona → 324.000 por los cuatro. Pero su correo dice *"el total de las entradas
(CLP 284.000)"*, y 284.000 son exactamente las tres anticipadas (Cejar 21.000 +
Puritama 35.000 + Piedras Rojas 15.000 = 71.000 × 4). Las dos cifras no
concilian salvo que el 284.000 cubra sólo las anticipadas y el Valle del
Arcoíris (10.000 pp) se pague aparte.

Probé si algún mix de menores daba 284.000 y no hay solución: con 4 adultos son
324.000, y las rebajas posibles (8.000 por menor de 18, 21.000 por menor de 13)
no suman los 40.000 de diferencia en ninguna combinación.

Por eso los documentos **no publican ningún total de entradas ni total general**.
Muestran cada entrada como él la escribió, y el abono con sus palabras y sus
cifras: 928.000 + 284.000 = 1.212.000, saldo al iniciar.

- [ ] **Preguntarle a la agencia qué cubre el 284.000** antes de que Segar pague.
      Son CLP 40.000 de diferencia y hoy nadie sabe si el Arcoíris está dentro o
      se paga en portería.

## Otro pendiente del correo

- [ ] **Jueves 24.** Dice "full day: Valle de la Luna Sur" pero da horario 16:30
      a 20:30, y ese mismo día ya hay Puritama de 9:30 a 13:30. Va reproducido
      tal cual, sin corregirlo. Conviene que la agencia aclare si es error de
      etiqueta o de horario.
- [ ] **Correo de Segar.** El bloque "Prepared for" va sin dirección.

## Los dos archivos

| Archivo | Qué es |
| --- | --- |
| `index.html` → `Tourevo-COT-2026-0160-Segar.pdf` | La cotización, 7 páginas A4. Sólo el contenido del correo. |
| `carta.html` → `Tourevo-COT-2026-0160-Segar-Carta.pdf` | La carta de presentación, 1 página A4. Va primero por WhatsApp. |

## Generar los PDF de nuevo

```bash
cd propuestas/segar-san-pedro-atacama
for f in index carta; do
  chromium --headless --no-pdf-header-footer \
    --print-to-pdf="$f.pdf" "file://$PWD/$f.html"
done
```

La carta usa `zoom: .90` en impresión, que es lo que la deja en una sola página
— si le agregás texto, revisá que siga saliendo en una.

## Nota sobre dónde vive este archivo

`propuestas/` se había sacado de este repo en el commit `5cf66d2` ("se mudó a
tourevo-cl/website/propuestas/"). Esta cotización se creó acá porque la sesión
venía apuntada a `jpchs1/trabajos-varios`, rama
`claude/segar-san-pedro-atacama-0tn9yz`. Si el destino definitivo es
`jpchs1/tourevo-cl`, hay que portarla.
