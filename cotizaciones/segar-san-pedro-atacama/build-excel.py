#!/usr/bin/env python3
"""Planilla del itinerario propuesto, en el mismo formato del export del
cliente y con columnas agregadas de diferencias y correcciones.

    python3 build-excel.py

Los valores de la cotización se leen de contenido.mjs vía node, así que no hay
precios duplicados: si cambia la cotización, cambia la planilla.
"""

import json
import subprocess
from pathlib import Path

from openpyxl import Workbook
from openpyxl.styles import Alignment, Border, Font, PatternFill, Side
from openpyxl.utils import get_column_letter

AQUI = Path(__file__).parent

# --- Datos de la cotización, desde la misma fuente que los PDF ---------------

_js = "import('./contenido.mjs').then(m=>console.log(JSON.stringify({doc:m.doc,cliente:m.cliente,programa:m.programa})))"
COT = json.loads(subprocess.run(
    ['node', '--input-type=module', '-e', _js],
    cwd=AQUI, capture_output=True, text=True, check=True).stdout)
DOC, CLIENTE, PROGRAMA = COT['doc'], COT['cliente'], COT['programa']
PAX = DOC['pax']

# --- Paleta y estilos ---------------------------------------------------------

TINTA      = 'FF16232B'
TINTA_SUAVE= 'FF566A72'
ACENTO     = 'FF0F766E'
AVISO      = 'FFA0561F'
CABECERA   = 'FF0B5B54'
GRIS_FILA  = 'FFF3F8F8'
VERDE_FILA = 'FFEAF5F3'
AMBAR_FILA = 'FFFDF3E9'
LINEA      = 'FFD3E0E0'

F = 'Arial'
h1     = Font(name=F, size=14, bold=True, color=TINTA)
h2     = Font(name=F, size=10, bold=True, color=TINTA_SUAVE)
th     = Font(name=F, size=9,  bold=True, color='FFFFFFFF')
td     = Font(name=F, size=9,  color=TINTA)
td_sv  = Font(name=F, size=9,  color=TINTA_SUAVE)
td_b   = Font(name=F, size=9,  bold=True, color=TINTA)
td_ac  = Font(name=F, size=9,  bold=True, color=ACENTO)
td_av  = Font(name=F, size=9,  bold=True, color=AVISO)

fill_th   = PatternFill('solid', fgColor=CABECERA)
fill_gris = PatternFill('solid', fgColor=GRIS_FILA)
fill_verd = PatternFill('solid', fgColor=VERDE_FILA)
fill_amb  = PatternFill('solid', fgColor=AMBAR_FILA)

borde = Border(*[Side(style='thin', color=LINEA)] * 4)
arriba_wrap = Alignment(vertical='top', wrap_text=True)
arriba      = Alignment(vertical='top')
centro      = Alignment(vertical='top', horizontal='center')

def impresion(ws, ancho_cols):
    """Apaisado, ajustado al ancho y repitiendo la fila de títulos: la planilla
    se va a imprimir o mandar en PDF, no sólo a mirar en pantalla."""
    ws.page_setup.orientation = 'landscape'
    ws.page_setup.fitToWidth = 1
    ws.page_setup.fitToHeight = 0
    ws.sheet_properties.pageSetUpPr.fitToPage = True
    ws.print_title_rows = '1:1'
    ws.page_margins.left = ws.page_margins.right = 0.3
    ws.page_margins.top = ws.page_margins.bottom = 0.4


def encabezado(ws, cols, fila=1):
    for i, (titulo, ancho) in enumerate(cols, start=1):
        c = ws.cell(row=fila, column=i, value=titulo)
        c.font, c.fill, c.border = th, fill_th, borde
        c.alignment = Alignment(vertical='center', wrap_text=True)
        ws.column_dimensions[get_column_letter(i)].width = ancho
    ws.row_dimensions[fila].height = 30
    ws.freeze_panes = ws.cell(row=fila + 1, column=1)

def escribir(ws, fila, valores, fill=None, negritas=()):
    for i, v in enumerate(valores, start=1):
        c = ws.cell(row=fila, column=i, value=v)
        c.font = td_b if i in negritas else td
        c.alignment = arriba_wrap
        c.border = borde
        if fill:
            c.fill = fill

# --- Hoja 1: itinerario propuesto --------------------------------------------
# Cada fila es un bloque del día. "Cambio" es la diferencia respecto de lo que
# hoy está cargado en el sistema Tourevo.

R, D = 'Northern Chile', 'San Pedro de Atacama'
TB, NA = 'To be booked', '-'

# (fecha, inicio, fin, tipo, servicio, estado, opera, cambio, como_estaba,
#  diferencia_y_correccion, nota_original, respuesta)
ITINERARIO = [
 ('2026-12-22','06:35','07:05','Transfer','Traslado al aeropuerto de Puerto Natales (PNT)',NA,'Cliente','Sin cambio','06:35 – 07:05','Tramo de salida desde Puerto Natales. Fuera del alcance de Tourevo.','',''),
 ('2026-12-22','09:05','12:14','Flight','Vuelo Puerto Natales (PNT) → Santiago · LATAM',NA,'Cliente','Sin cambio','09:05 – 12:14','','',''),
 ('2026-12-22','13:45','15:15','Other','Conexión en Santiago · almuerzo en el aeropuerto',NA,'Cliente','Sin cambio','13:45 – 15:15','','',''),
 ('2026-12-22','15:33','17:41','Flight','Vuelo Santiago → Calama (CJC) · LATAM',NA,'Cliente','Sin cambio','15:33 – 17:41','','',''),
 ('2026-12-22','18:15','19:15','Transfer','Traslado Calama → San Pedro de Atacama',TB,'Tourevo','Sin cambio','Sistema 18:15 – 19:15 · Export 18:15 – 19:35',
  'Ojo con el desfase: el export dice que termina 19:35 y el sistema 19:15. Tomamos el del sistema, que calza con el check-in de las 19:35.','',''),
 ('2026-12-22','19:35','20:30','Other','Check-in en San Pedro de Atacama y descanso',NA,'Cliente','Sin cambio','19:35 – 20:30',
  'Con la llegada a las 19:15, el 22 no da para ninguna actividad más.','',''),

 ('2026-12-23','09:00','13:00','Tour or activity','Aclimatación · mañana tranquila en San Pedro',NA,'Cliente','Sin cambio','Sistema 23 dic 09:00 – 13:00 · Export 22 dic',
  'En el export estaba el 22, lo que era imposible: el vuelo llega 19:15. En el sistema ya quedó el 23. Falta definir si la quieren guiada o como tiempo libre; si es guiada, la cotizamos.',
  'Baltinache / Ver si es mejor en horario AM o PM',
  'Baltinache no es lo mismo que Cejar: son las Lagunas Escondidas, más lejos, con acceso restringido, y también se flota. En este itinerario el único hueco libre del 23 es la TARDE (13:00 – 17:30), así que si Baltinache reemplaza a Cejar tiene que ir PM. Para hacerlo AM habría que sacar la aclimatación, que es justo lo que protege el día antes de subir a 3.500 m el 24.'),
 ('2026-12-23','13:30','16:30','Tour or activity','Laguna Cejar + Ojos de Tebenquiche + Laguna de Tebenquiche',TB,'Tourevo','Nuevo','No está en el sistema',
  'Está cotizado (CLP 80.000 + CLP 21.000 de entrada por persona) y no aparecía en el itinerario. Versión de 3 horas, sin la espera del atardecer en Tebenquiche, porque el atardecer lo tienen esa misma tarde en el Valle de la Luna. Es el único hueco del programa donde entra completo.',
  'Baltinache and valley iof the Moon sunset.',
  'Si prefieren Baltinache en vez de Cejar, va en esta misma franja y hay que recotizarlo: es otro acceso y otro cupo.'),
 ('2026-12-23','17:30','20:45','Tour or activity','Valle de la Luna at sunset',TB,'Tourevo','Sin cambio','Sistema 17:30 – 20:45 · Cotizado 16:30 – 20:30',
  'Una hora más tarde y quince minutos más largo que lo cotizado. Hay que confirmar que a las 17:30 alcance el circuito off circuit por el Vallecito, que se hace caminando, y no el recorrido estándar.',
  'Baltinache and valley iof the Moon sunset.',
  'El atardecer queda acá. Por eso Cejar (o Baltinache) va en versión corta, sin esperar el atardecer en Tebenquiche: sería el mismo atardecer dos veces.'),
 ('2026-12-23','','','Tour or activity','Pukará de Quitor — el cliente lo pedía para el 23 PM',NA,'Tourevo','Movido','Sistema 26 dic 08:30 – 10:00 · Export 23 dic sin hora',
  'No cabe el 23 entre 15:30 y 20:00: el Valle de la Luna toma 17:30 – 20:45 y Cejar 13:30 – 16:30. El sistema ya lo tiene el 26 a las 08:30, que además es mejor hora para el pukará. Conviene confirmar con la comunidad hasta qué hora abre el sitio.',
  'Can Pukara be visited between 3:30 - 8pm on the 23rd. Self guided?',
  'SÍ se puede visitar por cuenta propia pagando la entrada a la comunidad: es un sitio abierto y con senderos marcados. Pero sin guía es una ladera con muros de piedra; lo que hace que valga la visita son las dos horas de interpretación. Recomendamos guiado, y el 26 a las 08:30 como ya está.'),

 ('2026-12-24','09:30','13:30','Tour or activity','Termas de Puritama · 3.500 m',TB,'Tourevo','Sin cambio','Sistema 24 dic 09:30 – 13:30 · Cotizado 23 dic',
  'No venía en el export, pero sí está en el sistema. Cotizado para el 23; el sistema lo corrió al 24 y está bien: deja el 23 para aclimatar a 2.400 m antes de subir a 3.500 m, con Miscanti sobre 4.000 m el 25.','',''),
 ('2026-12-24','13:30','15:15','Other','Vuelta a San Pedro, almuerzo y descanso',NA,'Libre','Nuevo','No existía',
  'Franja que se abre al mover el Valle de la Muerte. Antes Puritama cerraba 13:30 y el valle arrancaba 13:30, con las termas a unos 30 km.','',''),
 ('2026-12-24','15:30','17:30','Tour or activity','Valle de la Muerte · sandboard',TB,'Tourevo','Movido','Sistema 24 dic 13:30 – 15:30',
  'Movido dos horas. A las 13:30 chocaba con el cierre de Puritama. Ocupa la franja que el itinerario ya tenía como tarde libre y, de paso, sale de la hora de más calor de un día de diciembre.',
  'Itinerary needs to be finalized/discussed',
  'Propuesta cerrada: 15:30 – 17:30 con sandboard, que es lo cotizado. Los cuadriciclos no están cotizados y no los operamos nosotros: si los quieren, hay que buscar operador y recotizar.'),
 ('2026-12-24','17:30','20:00','Other','Descanso antes de la cena',NA,'Libre','Sin cambio','','','',''),
 ('2026-12-24','20:00','22:00','Other','Cena de Nochebuena',NA,'Cliente','Sin cambio','20:00 – 22:00','','',''),
 ('2026-12-24','22:15','23:59','Tour or activity','Astronomía privada',TB,'Tourevo','Sin cambio','Sistema 24 dic 22:15 – 23:59 · Export 22 dic',
  'En el export estaba el 22; el sistema ya la tiene el 24. Se mantiene la hora, pero la recogida tiene que ser en el restaurante a las 22:10 y no en el hotel: la cena termina 22:00.',
  'Itinerary needs to be finalized/discussed',
  'DOS COSAS ANTES DE RESERVAR. (1) La luna esa noche está al 99,4% iluminada, y ninguna otra noche del viaje es mejor: del 22 al 26 va entre 92% y 100%. En un privado con esa luna hay que reorientarlo a luna y planetas, o el cliente se lleva una decepción. (2) Lo cotizado es el tour COMPARTIDO a CLP 40.000 por persona; el privado es otro producto y hay que cotizarlo.'),

 ('2026-12-25','08:00','18:00','Tour or activity','Altiplano · Chaxa, Toconao, Miscanti y Piedras Rojas',TB,'Tourevo','Sin cambio','Sistema 25 dic 08:00 – 18:00 · Cotizado 10:00 – 18:00',
  'Dos horas más y otro alcance: entra Chaxa, que tiene su propia entrada, y salen Tuyajto y las protoaldeas que estaban cotizadas. Hay que recotizarlo.',
  'Can we add Rainbow Valley',
  'NO en este día. Chaxa, Toconao, Miscanti y Piedras Rojas están al SURESTE, subiendo por el borde este del salar. El Valle del Arcoíris está al NOROESTE, camino a Río Grande: dirección contraria. Sumarlo son varios cientos de kilómetros extra y deja el resto del día en pasadas de auto. Necesita su propia media jornada, y el único hueco es el 24 por la mañana, donde hoy está Puritama. Si el Arcoíris pesa más que Puritama, se puede cambiar, pero hay que decidirlo.'),
 ('2026-12-25','20:00','21:30','Other','Cena',NA,'Cliente','Sin cambio','20:00 – 21:30','','',''),

 ('2026-12-26','07:45','08:15','Other','Check-out y carga del equipaje',TB,'Tourevo','Nuevo','No existía',
  'Sin este bloque quedan 24 minutos entre que Quitor termina (10:00) y sale el traslado (10:24) para volver del pukará, pasar por el hotel, hacer el check-out y cargar maletas. Con el equipaje ya en el vehículo, el traslado sale directo desde el pukará.','',''),
 ('2026-12-26','08:30','10:00','Tour or activity','Pukará de Quitor · guiado',TB,'Tourevo','Sin cambio','Sistema 26 dic 08:30 – 10:00 · Cotizado 08:00 – 10:00',
  'Hora y media contra las dos horas cotizadas: confirmar si el recorrido guiado entra, o arrancar 08:15.',
  '26 Free',
  'EL 26 NO ESTÁ LIBRE. El sistema ya tiene Quitor 08:30 – 10:00 y el traslado a Calama 10:24 – 11:44, y el vuelo sale 13:44. La fila "26 Free" del export quedó desactualizada.'),
 ('2026-12-26','10:24','11:44','Transfer','Traslado a Calama',TB,'Tourevo','Sin cambio','10:24 – 11:44',
  'Sale desde el pukará, no desde el hotel. Llega 11:44 para el vuelo de las 13:44: dos horas de margen, bien para un doméstico.','',''),
 ('2026-12-26','13:44','15:52','Flight','Vuelo Calama → Santiago · LATAM',NA,'Cliente','Sin cambio','13:44 – 15:52','','',''),
 ('2026-12-26','20:00','23:45','Other','Check-in internacional en Santiago',NA,'Cliente','Sin cambio','20:00 – 23:45','','',''),
 ('2026-12-26','23:45','23:59','Flight','Vuelo Santiago → Dallas · AA940 (comprado)',NA,'Cliente','Sin cambio','23:45','','',''),
]

PREGUNTAS = [
 (1,'Alta','Disponibilidad y recargo del 24 y 25 de diciembre','Nochebuena y Navidad, con el full day del altiplano el 25. Es lo primero que hay que cerrar: si el 25 no hay operación, se rearma el viaje entero.'),
 (2,'Alta','¿Pueden operar Cejar el 23 de 13:30 a 16:30, en versión de 3 horas?','Sin la espera del atardecer en Tebenquiche. Si necesitan las 4 horas cotizadas, la única forma de darlas es correr el Valle de la Luna de ese día, que ya está visto con el cliente.'),
 (3,'Alta','¿Pueden correr el Valle de la Muerte al 24 de 15:30 a 17:30?','¿El sandboard funciona a esa hora o recomiendan otra franja por el calor?'),
 (4,'Media','Cuadriciclos: ¿los operan ustedes?','El cliente los pidió como alternativa al sandboard. El sandboard está cotizado; los cuadriciclos no. Si no los operan, ¿con quién se coordinan?'),
 (5,'Media','Astronomía del 24: la luna está llena','La noche del 24 va cerca del 99% iluminada, y ninguna otra noche del viaje es mejor (22 al 26, entre 92% y 100%). ¿Cómo lo manejan en un privado? ¿Conviene orientarlo a luna y planetas?'),
 (6,'Media','Recogida de la astronomía en el restaurante a las 22:10','La cena de Nochebuena termina 22:00 y el tour arranca 22:15. Confírmennos el punto exacto.'),
 (7,'Media','Valle de la Luna del 23 arrancando 17:30','¿Alcanza para el circuito off circuit por el Vallecito, que se hace caminando? Es el que está cotizado.'),
 (8,'Media','Full day del 25: alcance y entradas','Entra Chaxa y salen Tuyajto y las protoaldeas respecto de lo cotizado. ¿Cómo queda el valor y qué entradas se compran por adelantado?'),
 (9,'Media','Valle del Arcoíris','El cliente pregunta si se puede agregar. No entra en el día de Piedras Rojas. ¿Lo operan como media jornada propia y con qué horario?'),
 (10,'Media','Baltinache en vez de Cejar','El itinerario lo deja anotado como pendiente. ¿Lo operan? ¿AM o PM, y con qué cupo?'),
 (11,'Baja','Quitor el 26 en hora y media','Está cotizado a dos horas. ¿Entra el recorrido guiado en 08:30 – 10:00, o mejor arrancamos 08:15? ¿Hasta qué hora abre el sitio?'),
 (12,'Baja','Tarifas para 4 pasajeros y vehículo del full day','Las alternativas de trekking venían cotizadas como mínimo de grupo para 2. Y para el full day al altiplano, con 4 pasajeros más guía-conductor y equipaje, ¿qué vehículo asignan?'),
]

# Cotizado vs sistema: día y horario de cada uno de los nueve servicios.
# (id, día sistema, horario sistema, diferencia)
SISTEMA = {
 'transfer-in':     ('2026-12-22','18:15 – 19:15','Se fija la hora que faltaba'),
 'puritama':        ('2026-12-24','09:30 – 13:30','Corre de día: 23 → 24'),
 'luna-sur':        ('2026-12-23','17:30 – 20:45','Cambia el horario: una hora más tarde'),
 'astronomico':     ('2026-12-24','22:15 – 23:59','Corre de día 23 → 24, y de compartido a privado'),
 'marte-sandboard': ('2026-12-24','13:30 – 15:30','Cambia el horario: mañana → tarde, 4 h → 2 h'),
 'cejar':           (None,None,'No está en el sistema'),
 'piedras-rojas':   ('2026-12-25','08:00 – 18:00','Cambia el horario: arranca 2 h antes, y otro alcance'),
 'arqueologico':    ('2026-12-26','08:30 – 10:00','Cambia el horario: media hora más tarde y más corto'),
 'transfer-out':    ('2026-12-26','10:24 – 11:44','Se fija la hora que faltaba'),
}

DIA_ES = {0:'lun',1:'mar',2:'mié',3:'jue',4:'vie',5:'sáb',6:'dom'}
MES_ES = {12:'dic'}

def dia_txt(iso):
    if not iso:
        return '—'
    from datetime import date
    y, m, d = (int(x) for x in iso.split('-'))
    return f'{DIA_ES[date(y,m,d).weekday()]} {d} {MES_ES.get(m, m)}'

# --- Armado -------------------------------------------------------------------

wb = Workbook()

# Hoja 1 ----------------------------------------------------------------------
ws = wb.active
ws.title = 'Itinerario propuesto'
COLS1 = [('Fecha',11),('Región',14),('Destino',20),('Inicio',8),('Fin',8),('Tipo',15),
         ('Servicio',44),('Pax',6),('Estado',13),('Opera',10),('Cambio',12),
         ('Cómo estaba',30),('Diferencia y corrección',60),
         ('Nota del itinerario',30),('Respuesta',66)]
encabezado(ws, COLS1)
impresion(ws, COLS1)

FILL_CAMBIO = {'Nuevo': fill_verd, 'Movido': fill_amb}
fila = 2
for (f, ini, fin, tipo, serv, est, opera, cambio, antes, dif, nota, resp) in ITINERARIO:
    escribir(ws, fila,
             [f, R, D, ini, fin, tipo, serv, PAX, est, opera, cambio, antes, dif, nota, resp],
             fill=FILL_CAMBIO.get(cambio, fill_gris if opera != 'Tourevo' else None),
             negritas=(7, 11))
    c = ws.cell(row=fila, column=11)
    if cambio == 'Nuevo':
        c.font = td_ac
    elif cambio == 'Movido':
        c.font = td_av
    for col in (4, 5, 8):
        ws.cell(row=fila, column=col).alignment = centro
    if opera != 'Tourevo':
        for col in (7,):
            ws.cell(row=fila, column=col).font = td_sv
    fila += 1
ws.auto_filter.ref = f'A1:{get_column_letter(len(COLS1))}{fila-1}'
FIN1 = fila - 1

# Hoja 2 ----------------------------------------------------------------------
ws2 = wb.create_sheet('Preguntas al operador')
encabezado(ws2, [('Nº',6),('Urgencia',11),('Pregunta',54),('Detalle',86)])
impresion(ws2, None)
for i, (n, urg, q, det) in enumerate(PREGUNTAS, start=2):
    escribir(ws2, i, [n, urg, q, det],
             fill=fill_amb if urg == 'Alta' else None, negritas=(3,))
    ws2.cell(row=i, column=1).alignment = centro
    ws2.cell(row=i, column=2).alignment = centro
    if urg == 'Alta':
        ws2.cell(row=i, column=2).font = td_av

# Hoja 3 ----------------------------------------------------------------------
ws3 = wb.create_sheet('Cotización vs sistema')
COLS3 = [('Nº',6),('Servicio cotizado',44),('Día cotizado',14),('Horario cotizado',18),
         ('Día en sistema',14),('Horario en sistema',18),('Diferencia',42),
         ('Servicio p/p',14),('Entradas p/p',14),('Subtotal p/p',14),(f'Total {PAX} pax',15)]
encabezado(ws3, COLS3)
impresion(ws3, COLS3)

fila = 2
for i, s in enumerate(PROGRAMA, start=1):
    sd, sh, dif = SISTEMA[s['id']]
    escribir(ws3, fila, [
        i, s['titulo']['es'], dia_txt(s['fecha']), s['horario']['es'].replace(' hrs.', ''),
        dia_txt(sd), sh or '—', dif, s['valor'], s['entradas'], None, None,
    ], fill=fill_amb if sd is None else None, negritas=(2,))
    ws3.cell(row=fila, column=10, value=f'=H{fila}+I{fila}')
    ws3.cell(row=fila, column=11, value=f'=J{fila}*{PAX}')
    for col in (1, 3, 4, 5, 6):
        ws3.cell(row=fila, column=col).alignment = centro
    for col in (8, 9, 10, 11):
        c = ws3.cell(row=fila, column=col)
        c.number_format = '#,##0'
        c.alignment = Alignment(vertical='top', horizontal='right')
        c.font = td_ac if col == 10 else td
    fila += 1

TOT = fila
escribir(ws3, TOT, ['', 'TOTAL DEL PACK', '', '', '', '', '', None, None, None, None], fill=fill_verd)
for col, letra in ((8, 'H'), (9, 'I'), (10, 'J'), (11, 'K')):
    c = ws3.cell(row=TOT, column=col, value=f'=SUM({letra}2:{letra}{TOT-1})')
    c.number_format = '#,##0'
    c.font = Font(name=F, size=10, bold=True, color=CABECERA)
    c.alignment = Alignment(vertical='center', horizontal='right')
    c.fill = fill_verd
    c.border = borde
ws3.cell(row=TOT, column=2).font = Font(name=F, size=10, bold=True, color=CABECERA)

# Hoja 4 ----------------------------------------------------------------------
ws4 = wb.create_sheet('Leyenda y resumen')
ws4.column_dimensions['A'].width = 26
ws4.column_dimensions['B'].width = 96
ws4['A1'] = f'{DOC["numero"]} · {CLIENTE["nombre"]} · San Pedro de Atacama, 22 – 26 dic 2026'
ws4['A1'].font = h1
ws4['A2'] = f'{PAX} pasajeros · itinerario propuesto contra lo cargado en el sistema Tourevo'
ws4['A2'].font = h2

filas4 = [
 ('', ''),
 ('QUÉ ES CADA HOJA', ''),
 ('Itinerario propuesto', 'El día completo, hora por hora, en el mismo formato del export. Las columnas Cambio, Cómo estaba, Diferencia y corrección, Nota del itinerario y Respuesta son las agregadas.'),
 ('Preguntas al operador', 'Lo que hay que confirmar con la agencia que cotizó, en orden de urgencia.'),
 ('Cotización vs sistema', 'Los nueve servicios cotizados, con su día y horario en cada lado y su valor.'),
 ('', ''),
 ('CÓMO LEER LA COLUMNA CAMBIO', ''),
 ('Sin cambio', 'Queda en el día y la hora que el cliente ya vio en el sistema.'),
 ('Movido', 'Cambia de hora o de día respecto del sistema. Fondo ámbar.'),
 ('Nuevo', 'Bloque que hoy no existe en el sistema. Fondo verde.'),
 ('Filas en gris', 'No son servicios nuestros: vuelos, comidas, check-in y tiempo libre. Van para que se vea el día entero.'),
 ('', ''),
 ('RESUMEN DE CAMBIOS', ''),
]
r = 4
for k, v in filas4:
    ws4.cell(row=r, column=1, value=k).font = td_b if k.isupper() and k else td_b
    ws4.cell(row=r, column=2, value=v).font = td
    ws4.cell(row=r, column=2).alignment = arriba_wrap
    if k.isupper() and k:
        ws4.cell(row=r, column=1).font = Font(name=F, size=9, bold=True, color=CABECERA)
    r += 1

for etiqueta, criterio in (('Bloques sin cambio', 'Sin cambio'), ('Bloques movidos', 'Movido'), ('Bloques nuevos', 'Nuevo')):
    ws4.cell(row=r, column=1, value=etiqueta).font = td
    c = ws4.cell(row=r, column=2, value=f"=COUNTIF('Itinerario propuesto'!K2:K{FIN1},\"{criterio}\")")
    c.font = td_b
    c.alignment = arriba
    r += 1
ws4.cell(row=r, column=1, value='Total de bloques').font = td_b
c = ws4.cell(row=r, column=2, value=f"=COUNTA('Itinerario propuesto'!A2:A{FIN1})")
c.font = td_b

r += 2
ws4.cell(row=r, column=1, value='DE DÓNDE SALE CADA DATO').font = Font(name=F, size=9, bold=True, color=CABECERA)
r += 1
FUENTES = [
 ('Cotización', f'{DOC["numero"]}, programa y precios enviados por el operador. Los valores de la hoja "Cotización vs sistema" se leen de contenido.mjs, la misma fuente que los PDF.'),
 ('Sistema Tourevo', 'Itinerario cargado en el sistema al 24 ago 2026, ya revisado con el cliente. Es la referencia de la columna "Cómo estaba".'),
 ('Export del cliente', 'Planilla enviada por el cliente con su columna de comentarios. Alimenta la columna "Nota del itinerario". Donde difiere del sistema, se indica en "Diferencia y corrección".'),
 ('Fase lunar', 'Cálculo propio: edad lunar sobre la luna nueva de referencia del 6 ene 2000, mes sinódico de 29,530589 días. Del 22 al 26 dic 2026 la iluminación va entre 92% y 100%; el 24 está en 99,4%.'),
 ('Distancias y altitudes', 'Referencias de la zona citadas por el operador y por el propio sistema (Puritama 3.500 m). Conviene que el operador las confirme.'),
]
for k, v in FUENTES:
    ws4.cell(row=r, column=1, value=k).font = td_b
    c = ws4.cell(row=r, column=2, value=v); c.font = td; c.alignment = arriba_wrap
    r += 1

SALIDA = AQUI / f'Tourevo-{DOC["numero"]}-{CLIENTE["nombre"]}-Itinerario-y-diferencias.xlsx'
wb.save(SALIDA)
print(f'✓ {SALIDA.name} · {FIN1-1} bloques · {len(PREGUNTAS)} preguntas · {len(PROGRAMA)} servicios cotizados')
