# Plano de despiece — Paneles de cubierta (Deckeva)

Plano digital a escala generado a partir del levantamiento de medidas en obra
(6 fotos con cotas anotadas a mano).

| Archivo | Uso |
|---|---|
| `plano-cubierta-deckeva.pdf` | **Entregable principal.** 1 página A1 (841 × 594 mm), escala 1:10. Imprimir *al 100 %, sin ajustar a página* para que la escala sea real. |
| `plano-cubierta-deckeva.svg` | Vectorial editable (Illustrator, Inkscape, navegador). |
| `plano-cubierta-deckeva.png` | Vista rápida / envío por WhatsApp. |
| `generar_plano.py` | Script que genera los tres archivos. |
| `fotos/` | Fotos originales del levantamiento (foto-1 … foto-6). |

Regenerar: `python3 generar_plano.py` (requiere `cairosvg` y `pillow`).

## Relación de piezas

Todas las cotas en **centímetros**. Solo se incluyen las piezas marcadas en
**verde** en las fotos; lo marcado en amarillo o tachado queda excluido.

| Ref. | Descripción | Medidas (cm) | Cant. | Área (m²) | Foto |
|---|---|---|---|---|---|
| P-01 | Pasillo central de cubierta | 78 × 283 | 1 | 2,207 | 1 |
| P-02 | Cubierta zona escotillas (acceso escalera) | 166/94 × 185 | 1 | 2,646 | 2 |
| P-03 | Cubierta de proa — trapecio en V | 60/166 × 172, lados 180 | 1 | 1,944 | 3 |
| P-04 | Panel lateral A — banda babor | 45 × 103 | 1 | 0,464 | 4 |
| P-05 | Panel lateral B — banda babor | 70 × 103 | 1 | 0,721 | 4 |
| P-06 | Panel lateral C — banda estribor | 70 × 103 | 1 | 0,721 | 5 |
| P-07 | Panel lateral D — banda estribor | 40 × 103 | 1 | 0,412 | 5 |
| P-08 | Escalón superior | 58 × 28 | 1 | 0,162 | 6 |
| P-09 | Escalón inferior | 58 × 28 | 1 | 0,162 | 6 |
| | **TOTAL** | | **9** | **9,440** | |

Superficie neta, sin solapes ni despunte. Prever ~10–15 % de merma de corte.

## Criterios de interpretación de las fotos

Las fotos solo traen cotas sueltas, así que estas decisiones quedan documentadas
por si hay que corregirlas:

1. **P-01 y P-04 … P-09** — en las fotos el contorno verde aparece trapecial,
   pero es por la perspectiva del suelo: solo hay dos cotas anotadas en cada
   pieza, así que se interpretan como **rectángulos**.
2. **P-02** — el alto total de 185 se descompone en 120 (tramo recto) + 12
   (chaflán) + 53 (tramo recto). El retranqueo lateral anotado como 35 se
   resuelve con **36 por lado**, que es lo que cierra 166 − 72 = 94 en la base.
   Diferencia de 1 cm por lado respecto a lo anotado.
3. **P-03** — la altura de **172 es calculada**, no medida: sale de las bases 60
   y 166 con lados de 180 (√(180² − 53²) = 172,0). En el plano va marcada con
   asterisco.
4. **P-02 y P-03** tienen contorno irregular contra el casco: conviene
   **plantillar sobre el propio soporte** antes de cortar.
5. Holgura de montaje recomendada: **3 a 5 mm** en todo el perímetro.
6. Sentido del listonado: longitudinal a la eslora salvo indicación contraria.

## Pendiente de confirmar

- Cliente y embarcación (el rótulo del plano está en blanco a propósito).
- Asignación babor/estribor de P-04…P-07: se dedujo de que las fotos 4 y 5
  corresponden a bandas opuestas, pero no está confirmado cuál es cuál.
- El plano lleva una tabla **«Verificación de medidas en obra»** para repasar
  cota por cota antes de cortar.
