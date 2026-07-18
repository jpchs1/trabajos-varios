# Video tutorial: iCut — Software de control para CNC Router

Video interactivo en HTML (14 capítulos, ~10 minutos) que explica cómo funciona el
software **iCut / MDR CNC Router** (sistema Xing Duo Wei, máquinas TechPro):
interfaz completa, flujo de trabajo de corte de principio a fin y funciones avanzadas.

## Cómo verlo

Abrir `index.html` en cualquier navegador moderno (Chrome/Edge recomendado) y pulsar ▶.

- **Narración por voz en español** (sintetizada por el navegador) + subtítulos.
- Controles tipo video: reproducir/pausa (`espacio`), capítulos anterior/siguiente (`←`/`→`),
  menú de capítulos (`C` o ☰), silenciar narración (`N`), pantalla completa (`F`).
- La barra de progreso permite saltar a cualquier capítulo.

## Contenido

1. Bienvenida
2. ¿Qué es iCut? (flujo diseño → iCut → controlador → máquina)
3. La pantalla principal, zona por zona (recreación de la interfaz real)
4. Sistemas de coordenadas (mecánicas vs. pieza, G54–G59)
5. El panel de control manual (jog, F/S, pasos, periféricos, F9–F12)
6. Volver a HOME
7. Ajuste de herramienta: flotante, fijo y manual (parámetro 30201)
8. Origen de la pieza (XY->0 / Z->0, cuchilla vibratoria)
9. Importar DXF/AI/PLT y configurar capas (Layer F1, T1/T2, profundidades)
10. CreateFile F3 → Simulation F3 → Start F9
11. G-code desde CAM externo (ArtCAM/Aspire/Fusion, formato .tap)
12. Funciones avanzadas (compensación, ordenamiento, reanudar tras apagón, ciclos, fresado de base)
13. Parámetros del sistema y página de diagnóstico (contraseña de fabricante, backups)
14. Resumen del flujo en 6 pasos

Fuentes: *Manual de usuario del sistema CNC Xing Duo Wei* y *Manual de operación
para principiantes Xing Duo Wei (TechPro)*, complementado con documentación pública
del software MDR CNC Router.


## 🎓 Curso completo (carpeta `curso/`)

Además del video resumen, la carpeta `curso/` contiene el **curso completo en 8 módulos**
(~42 min en total, 53 capítulos), cada uno con narración por voz, subtítulos y cámara
dinámica sobre la interfaz recreada de iCut. Abrir `curso/index.html` para ver la portada
con el índice completo:

| Módulo | Tema | Duración |
|---|---|---|
| 1 | Fundamentos e interfaz (pantalla, menús, coordenadas) | 5:0 |
| 2 | Puesta en marcha y orígenes (HOME, homing 11xxx, ajuste de herramienta, Tool Setting) | 4:18 |
| 3 | Del diseño al corte (DXF, File Parameter, capas, simulación) | 3:53 |
| 4 | G-code y producción (CAM externo, File Management, reanudación, ciclos, MDI) | 3:41 |
| 5 | Edición de trayectorias (compensación, ordenamiento, espejo/matrices) | 3:04 |
| 6 | Visión CCD y corte de contorno (marcas, edge patrol, FAQ 3.1) | 3:08 |
| 7 | Parámetros a fondo (series 10xxx/11xxx/50xxx, respaldos, idioma) | 4:49 |
| 8 | Diagnóstico y mantenimiento (Diagnosis, IP, firmware .XDW, problemas comunes) | 3:36 |

Los módulos se construyen desde componentes compartidos (motor de reproducción + plantillas
de la interfaz del software), por lo que todos mantienen el mismo estilo y controles.
