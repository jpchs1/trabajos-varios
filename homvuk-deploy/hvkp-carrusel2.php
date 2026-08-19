<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Carrusel en movimiento, navegacion nueva y boton "Ver Todos"
 * (hvkp-carrusel2)
 *
 *   1. El carrusel de vehiculos ahora avanza solo (se detiene al pasar el
 *      mouse por encima) y da la vuelta al llegar al final.
 *   2. Flechas y puntos rediseniados.
 *   3. El boton "Ver Todos" de esa seccion apuntaba a ?cat=car, una categoria
 *      que no existe, por eso mostraba tambien casas y departamentos. Pasa a
 *      apuntar a los automoviles.
 *
 * Uso:  php hvkp-carrusel2.php            (aplicar)
 *       php hvkp-carrusel2.php restore    (deshacer)
 */

$HVKX_B64  = 'LyoqCiAqIEhPTVZVSyBUYXJqZXRhcyBQUk8KICogLSBDb3JyaWdlIGxhIGJhbmRlcmEgZGUgQ2hpbGUsIHF1ZSBlbCBjYXJydXNlbCBlc3RpcmFiYSBhIHBhbnRhbGxhIGNvbXBsZXRhLgogKiAtIE1vZGVybml6YSBsYXMgdGFyamV0YXMgZGUgcHJvcGllZGFkZXMgeSB2ZWhpY3Vsb3MgZGVsIHNpdGlvIHB1YmxpY28uCiAqLwphZGRfYWN0aW9uKCAnd3BfaGVhZCcsIGZ1bmN0aW9uICgpIHsKICAgIGlmICggaXNfYWRtaW4oKSApIHsKICAgICAgICByZXR1cm47CiAgICB9CiAgICBlY2hvICc8c3R5bGUgaWQ9Imh2a3QtY3NzIj4nCgogICAgLyogLS0tIEJhbmRlcmEgZGVsIHByZWNpbyBlbiBwZXNvczogZWwgY2FycnVzZWwgZXN0aXJhIHRvZGEgPGltZz4gYWwgMTAwJSAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlLmRvcyBpbWcsLmVudG94LXByb2R1Y3QtcHJpY2UgaW1nW3NyYyo9IkZsYWdfb2ZfQ2hpbGUiXSwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5lbnRveC1wcm9kdWN0LXByaWNlIGltZywub3ZhLXByb2R1Y3QtbGlzdCAuZW50b3gtcHJvZHVjdC1wcmljZSBpbWd7d2lkdGg6MThweCFpbXBvcnRhbnQ7aGVpZ2h0OjEycHghaW1wb3J0YW50O21heC13aWR0aDoxOHB4IWltcG9ydGFudDttaW4td2lkdGg6MCFpbXBvcnRhbnQ7b2JqZWN0LWZpdDpjb3Zlcjtib3JkZXItcmFkaXVzOjJweDtkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnQ7dmVydGljYWwtYWxpZ246bWlkZGxlO21hcmdpbi1yaWdodDo3cHg7Ym94LXNoYWRvdzowIDAgMCAxcHggcmdiYSgxNiwyNCw0MCwuMTApfScKCiAgICAvKiAtLS0gVGFyamV0YSAtLS0gKi8KICAgIC4gJy5vdmEtcHJvZHVjdC1saXN0IC5lbnRveF9wcm9kdWN0LC5vdmEtcHJvZHVjdC1zbGlkZXIgLmVudG94X3Byb2R1Y3R7YmFja2dyb3VuZDojZmZmO2JvcmRlcjoxcHggc29saWQgI2VjZWVmMztib3JkZXItcmFkaXVzOjE4cHg7b3ZlcmZsb3c6aGlkZGVuO2JveC1zaGFkb3c6MCAxcHggM3B4IHJnYmEoMTYsMjQsNDAsLjA0KSwwIDhweCAyNHB4IHJnYmEoMTYsMjQsNDAsLjA1KTt0cmFuc2l0aW9uOnRyYW5zZm9ybSAuMjJzIGN1YmljLWJlemllciguMiwuOCwuMiwxKSxib3gtc2hhZG93IC4yMnMgZWFzZSxib3JkZXItY29sb3IgLjIycyBlYXNlO2hlaWdodDoxMDAlO2Rpc3BsYXk6ZmxleDtmbGV4LWRpcmVjdGlvbjpjb2x1bW59JwogICAgLiAnLm92YS1wcm9kdWN0LWxpc3QgLmVudG94X3Byb2R1Y3Q6aG92ZXIsLm92YS1wcm9kdWN0LXNsaWRlciAuZW50b3hfcHJvZHVjdDpob3Zlcnt0cmFuc2Zvcm06dHJhbnNsYXRlWSgtNnB4KTtib3gtc2hhZG93OjAgNnB4IDE2cHggcmdiYSgxNiwyNCw0MCwuMDgpLDAgMjBweCA0MHB4IHJnYmEoMTYsMjQsNDAsLjEyKTtib3JkZXItY29sb3I6I2UzZTZlZX0nCgogICAgLyogLS0tIEZvdG86IG1pc21hIHByb3BvcmNpb24gZW4gdG9kYXMgeSB6b29tIHN1YXZlIC0tLSAqLwogICAgLiAnLmVudG94X2hlYWRfcHJvZHVjdHtwb3NpdGlvbjpyZWxhdGl2ZTtvdmVyZmxvdzpoaWRkZW47YmFja2dyb3VuZDojZjRmNWY4fScKICAgIC4gJy5lbnRveF9oZWFkX3Byb2R1Y3QgYXtkaXNwbGF5OmJsb2NrfScKICAgIC4gJy5lbnRveF9oZWFkX3Byb2R1Y3QgaW1nLC5lbnRveC1wcm9kdWN0LXRodW1ibmFpbCBpbWd7d2lkdGg6MTAwJSFpbXBvcnRhbnQ7aGVpZ2h0OmF1dG87YXNwZWN0LXJhdGlvOjQvMztvYmplY3QtZml0OmNvdmVyO2Rpc3BsYXk6YmxvY2s7dHJhbnNpdGlvbjp0cmFuc2Zvcm0gLjVzIGN1YmljLWJlemllciguMiwuOCwuMiwxKX0nCiAgICAuICcuZW50b3hfcHJvZHVjdDpob3ZlciAuZW50b3hfaGVhZF9wcm9kdWN0IGltZ3t0cmFuc2Zvcm06c2NhbGUoMS4wNSl9JwoKICAgIC8qIC0tLSBDdWVycG8gLS0tICovCiAgICAuICcuZW50b3hfZm9vdF9wcm9kdWN0e3BhZGRpbmc6MThweCAxOHB4IDIwcHg7ZGlzcGxheTpmbGV4O2ZsZXgtZGlyZWN0aW9uOmNvbHVtbjtnYXA6MnB4O2ZsZXg6MX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC10aXRsZSxoMi5lbnRveC1wcm9kdWN0LXRpdGxle21hcmdpbjowIDAgMTBweDtmb250LXNpemU6MS4wMnJlbTtsaW5lLWhlaWdodDoxLjM1fScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXRpdGxlIGF7Y29sb3I6IzE2MWEyYiFpbXBvcnRhbnQ7Zm9udC13ZWlnaHQ6NzAwO3RleHQtZGVjb3JhdGlvbjpub25lO2Rpc3BsYXk6LXdlYmtpdC1ib3g7LXdlYmtpdC1saW5lLWNsYW1wOjI7LXdlYmtpdC1ib3gtb3JpZW50OnZlcnRpY2FsO292ZXJmbG93OmhpZGRlbjt0cmFuc2l0aW9uOmNvbG9yIC4xNnMgZWFzZX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC10aXRsZSBhOmhvdmVye2NvbG9yOiNkNjMzODQhaW1wb3J0YW50fScKCiAgICAvKiAtLS0gUHJlY2lvcyAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXdyYXBwZXItcHJpY2V7bWFyZ2luLXRvcDphdXRvfScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNle2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpiYXNlbGluZTtnYXA6NnB4O2ZsZXgtd3JhcDp3cmFwO21hcmdpbjowfScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlIC5wcm9kdWN0LWFtb3VudCwuZW50b3gtcHJvZHVjdC1wcmljZSAud29vY29tbWVyY2UtUHJpY2UtYW1vdW50e2ZvbnQtc2l6ZToxLjQycmVtIWltcG9ydGFudDtmb250LXdlaWdodDo4MDAhaW1wb3J0YW50O2NvbG9yOiNkNjMzODQhaW1wb3J0YW50O2xldHRlci1zcGFjaW5nOi0uMDJlbTtsaW5lLWhlaWdodDoxLjE1fScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlIC5kZWZpbmVfMV9kYXksLmVudG94LXByb2R1Y3QtcHJpY2UgLmRlZmluZV8xX2hvdXJ7Zm9udC1zaXplOi44MnJlbTtjb2xvcjojN2I4MTk0O2ZvbnQtd2VpZ2h0OjUwMH0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1wcmljZS5kb3N7ZGlzcGxheTpmbGV4O2FsaWduLWl0ZW1zOmNlbnRlcjtnYXA6MDttYXJnaW4tdG9wOjZweDtmb250LXNpemU6Ljg0cmVtO2NvbG9yOiM3YjgxOTQ7Zm9udC13ZWlnaHQ6NjAwfScKCiAgICAvKiAtLS0gVmFsb3JhY2lvbiAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXJldmlld3ttYXJnaW4tdG9wOjEycHg7cGFkZGluZy10b3A6MTJweDtib3JkZXItdG9wOjFweCBzb2xpZCAjZjFmMmY2O2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpjZW50ZXI7Z2FwOjhweH0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1yZXZpZXcgLnN0YXItcmF0aW5ne2ZvbnQtc2l6ZTouOGVtfScKCiAgICAvKiAtLS0gRmF2b3JpdG8gLS0tICovCiAgICAuICcuZW50b3gtcHJvZHVjdC1mYXZvdXJpdGV7Ym9yZGVyLXJhZGl1czo5OTlweCFpbXBvcnRhbnQ7YmFja2dyb3VuZDpyZ2JhKDI1NSwyNTUsMjU1LC45MikhaW1wb3J0YW50O2JhY2tkcm9wLWZpbHRlcjpibHVyKDRweCk7Ym94LXNoYWRvdzowIDJweCA4cHggcmdiYSgxNiwyNCw0MCwuMTQpO3RyYW5zaXRpb246dHJhbnNmb3JtIC4xNnMgZWFzZSxiYWNrZ3JvdW5kIC4xNnMgZWFzZX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1mYXZvdXJpdGU6aG92ZXJ7dHJhbnNmb3JtOnNjYWxlKDEuMDgpO2JhY2tncm91bmQ6I2ZmZiFpbXBvcnRhbnR9JwoKICAgIC8qIC0tLSBFc3BhY2lvIHBhcmEgcXVlIGxhIHNvbWJyYSBubyBzZSBjb3J0ZSBlbiBlbCBjYXJydXNlbCAtLS0gKi8KICAgIC4gJy5vdmEtcHJvZHVjdC1zbGlkZXIgLm93bC1zdGFnZS1vdXRlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5zbGljay1saXN0e3BhZGRpbmc6MTRweCAwIDIycHghaW1wb3J0YW50O21hcmdpbjotMTRweCAwIC0yMnB4fScKICAgIC4gJy5vdmEtcHJvZHVjdC1zbGlkZXIgLm93bC1pdGVtLC5vdmEtcHJvZHVjdC1zbGlkZXIgLnNsaWNrLXNsaWRle2Rpc3BsYXk6ZmxleDtoZWlnaHQ6YXV0b30nCiAgICAuICcub3ZhLXByb2R1Y3Qtc2xpZGVyIC5vd2wtaXRlbT4qLC5vdmEtcHJvZHVjdC1zbGlkZXIgLnNsaWNrLXNsaWRlPip7d2lkdGg6MTAwJX0nCgogICAgLyogLS0tIEZsZWNoYXMgZGVsIGNhcnJ1c2VsIC0tLSAqLwogICAgLiAnLm92YS1wcm9kdWN0LXNsaWRlciAub3dsLW5hdiBidXR0b24sLm92YS1wcm9kdWN0LXNsaWRlciAuc2xpY2stYXJyb3d7dHJhbnNpdGlvbjpiYWNrZ3JvdW5kIC4xNnMgZWFzZSxjb2xvciAuMTZzIGVhc2UsdHJhbnNmb3JtIC4xNnMgZWFzZX0nCiAgICAuICcub3ZhLXByb2R1Y3Qtc2xpZGVyIC5vd2wtbmF2IGJ1dHRvbjpob3Zlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5zbGljay1hcnJvdzpob3ZlcntiYWNrZ3JvdW5kOiNlODQzOTMhaW1wb3J0YW50O2NvbG9yOiNmZmYhaW1wb3J0YW50O3RyYW5zZm9ybTp0cmFuc2xhdGVZKC0ycHgpfScKCiAgICAuICdAbWVkaWEgKG1heC13aWR0aDo2NDBweCl7LmVudG94X2Zvb3RfcHJvZHVjdHtwYWRkaW5nOjE2cHh9LmVudG94LXByb2R1Y3QtcHJpY2UgLnByb2R1Y3QtYW1vdW50LC5lbnRveC1wcm9kdWN0LXByaWNlIC53b29jb21tZXJjZS1QcmljZS1hbW91bnR7Zm9udC1zaXplOjEuM3JlbSFpbXBvcnRhbnR9fScKICAgIC4gJ0BtZWRpYSAocHJlZmVycy1yZWR1Y2VkLW1vdGlvbjpyZWR1Y2Upey5vdmEtcHJvZHVjdC1saXN0IC5lbnRveF9wcm9kdWN0LC5vdmEtcHJvZHVjdC1zbGlkZXIgLmVudG94X3Byb2R1Y3QsLmVudG94X2hlYWRfcHJvZHVjdCBpbWd7dHJhbnNpdGlvbjpub25lIWltcG9ydGFudH0ub3ZhLXByb2R1Y3QtbGlzdCAuZW50b3hfcHJvZHVjdDpob3Zlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5lbnRveF9wcm9kdWN0OmhvdmVye3RyYW5zZm9ybTpub25lIWltcG9ydGFudH0uZW50b3hfcHJvZHVjdDpob3ZlciAuZW50b3hfaGVhZF9wcm9kdWN0IGltZ3t0cmFuc2Zvcm06bm9uZSFpbXBvcnRhbnR9fScKICAgIC4gJzwvc3R5bGU+JzsKfSwgOTkgKTsKCi8qKgogKiBIT01WVUsgQ2FycnVzZWwgUFJPIC0gbmF2ZWdhY2lvbiBkZWwgY2FycnVzZWwgZGUgdmVoaWN1bG9zLgogKi8KYWRkX2FjdGlvbiggJ3dwX2hlYWQnLCBmdW5jdGlvbiAoKSB7CiAgICBpZiAoIGlzX2FkbWluKCkgKSB7CiAgICAgICAgcmV0dXJuOwogICAgfQogICAgJGMgPSAnLm92YS1wcm9kdWN0LXNsaWRlciAuY29udGVudC1wcm9kdWN0LXNsaWRlci5vd2wtY2Fyb3VzZWwnOwogICAgZWNobyAnPHN0eWxlIGlkPSJodmtjLWNzcyI+JwoKICAgIC8qIC0tLSBGbGVjaGFzIC0tLSAqLwogICAgLiAkYyAuICcgLm93bC1uYXZ7ZGlzcGxheTpmbGV4O2dhcDoxMHB4O2FsaWduLWl0ZW1zOmNlbnRlcn0nCiAgICAuICRjIC4gJyAub3dsLW5hdiBidXR0b257d2lkdGg6NDhweCFpbXBvcnRhbnQ7aGVpZ2h0OjQ4cHghaW1wb3J0YW50O2JvcmRlci1yYWRpdXM6OTk5cHghaW1wb3J0YW50O2JhY2tncm91bmQ6I2ZmZiFpbXBvcnRhbnQ7Y29sb3I6IzE2MWEyYiFpbXBvcnRhbnQ7Ym9yZGVyOjFweCBzb2xpZCAjZTZlOGVmIWltcG9ydGFudDtib3gtc2hhZG93OjAgMnB4IDEwcHggcmdiYSgxNiwyNCw0MCwuMDgpIWltcG9ydGFudDtkaXNwbGF5OmZsZXghaW1wb3J0YW50O2FsaWduLWl0ZW1zOmNlbnRlcjtqdXN0aWZ5LWNvbnRlbnQ6Y2VudGVyO2ZvbnQtc2l6ZToxcmVtO3RyYW5zaXRpb246YmFja2dyb3VuZCAuMThzIGVhc2UsY29sb3IgLjE4cyBlYXNlLHRyYW5zZm9ybSAuMThzIGVhc2UsYm94LXNoYWRvdyAuMThzIGVhc2UsYm9yZGVyLWNvbG9yIC4xOHMgZWFzZX0nCiAgICAuICRjIC4gJyAub3dsLW5hdiBidXR0b246aG92ZXJ7YmFja2dyb3VuZDojZTg0MzkzIWltcG9ydGFudDtjb2xvcjojZmZmIWltcG9ydGFudDtib3JkZXItY29sb3I6I2U4NDM5MyFpbXBvcnRhbnQ7dHJhbnNmb3JtOnRyYW5zbGF0ZVkoLTJweCk7Ym94LXNoYWRvdzowIDEwcHggMjJweCByZ2JhKDIzMiw2NywxNDcsLjMyKSFpbXBvcnRhbnR9JwogICAgLiAkYyAuICcgLm93bC1uYXYgYnV0dG9uOmFjdGl2ZXt0cmFuc2Zvcm06dHJhbnNsYXRlWSgwKX0nCiAgICAuICRjIC4gJyAub3dsLW5hdiBidXR0b24uZGlzYWJsZWR7b3BhY2l0eTouNDtwb2ludGVyLWV2ZW50czpub25lfScKCiAgICAvKiAtLS0gUHVudG9zOiBiYXJyaXRhcywgbGEgYWN0aXZhIHNlIGFsYXJnYSAtLS0gKi8KICAgIC4gJGMgLiAnIC5vd2wtZG90c3tkaXNwbGF5OmZsZXg7anVzdGlmeS1jb250ZW50OmNlbnRlcjtnYXA6OHB4O21hcmdpbi10b3A6MjZweH0nCiAgICAuICRjIC4gJyAub3dsLWRvdHMgLm93bC1kb3Qgc3Bhbnt3aWR0aDo5cHghaW1wb3J0YW50O2hlaWdodDo5cHghaW1wb3J0YW50O21hcmdpbjowIWltcG9ydGFudDtib3JkZXItcmFkaXVzOjk5OXB4IWltcG9ydGFudDtiYWNrZ3JvdW5kOiNkOWRjZTUhaW1wb3J0YW50O2Rpc3BsYXk6YmxvY2s7dHJhbnNpdGlvbjp3aWR0aCAuMjVzIGVhc2UsYmFja2dyb3VuZCAuMjVzIGVhc2V9JwogICAgLiAkYyAuICcgLm93bC1kb3RzIC5vd2wtZG90LmFjdGl2ZSBzcGFue3dpZHRoOjI4cHghaW1wb3J0YW50O2JhY2tncm91bmQ6I2U4NDM5MyFpbXBvcnRhbnR9JwogICAgLiAkYyAuICcgLm93bC1kb3RzIC5vd2wtZG90OmhvdmVyIHNwYW57YmFja2dyb3VuZDojYjliZGNhIWltcG9ydGFudH0nCiAgICAuICRjIC4gJyAub3dsLWRvdHMgLm93bC1kb3QuYWN0aXZlOmhvdmVyIHNwYW57YmFja2dyb3VuZDojZTg0MzkzIWltcG9ydGFudH0nCgogICAgLyogLS0tIERlc2xpemFtaWVudG8gbWFzIHN1YXZlIC0tLSAqLwogICAgLiAkYyAuICcgLm93bC1zdGFnZXt0cmFuc2l0aW9uLXRpbWluZy1mdW5jdGlvbjpjdWJpYy1iZXppZXIoLjI1LC44LC4yNSwxKSFpbXBvcnRhbnR9JwoKICAgIC4gJ0BtZWRpYSAobWF4LXdpZHRoOjY0MHB4KXsnIC4gJGMgLiAnIC5vd2wtbmF2IGJ1dHRvbnt3aWR0aDo0MnB4IWltcG9ydGFudDtoZWlnaHQ6NDJweCFpbXBvcnRhbnR9JyAuICRjIC4gJyAub3dsLWRvdHN7bWFyZ2luLXRvcDoyMHB4fX0nCiAgICAuICdAbWVkaWEgKHByZWZlcnMtcmVkdWNlZC1tb3Rpb246cmVkdWNlKXsnIC4gJGMgLiAnIC5vd2wtbmF2IGJ1dHRvbiwnIC4gJGMgLiAnIC5vd2wtZG90cyAub3dsLWRvdCBzcGFue3RyYW5zaXRpb246bm9uZSFpbXBvcnRhbnR9fScKICAgIC4gJzwvc3R5bGU+JzsKfSwgOTkgKTs=';
$HVKX_MD5  = '89c5502c76442c959264309c2efdb26c';
$HVKX_NAME = 'HOMVUK Tarjetas PRO';
$HVKX_CAT  = 'automoviles';
$HVKX_URL  = '/explorar/?cat=automoviles';

/**
 * Ajusta la seccion de vehiculos: enciende el movimiento del carrusel y
 * corrige el enlace del boton que la acompania.
 * Devuelve array( cambios, detalle ).
 */
function hvkx_ajustar( &$nodo, $cat, $url, &$hechos ) {
    if ( ! is_array( $nodo ) ) {
        return false;
    }
    // 1) Es esta rama la del carrusel de vehiculos?
    $tiene_carrusel = hvkx_contiene_carrusel( $nodo, $cat );

    if ( isset( $nodo['elType'], $nodo['widgetType'] ) && 'widget' === $nodo['elType'] ) {
        if ( 'entox_elementor_product_slider' === $nodo['widgetType']
            && isset( $nodo['settings']['categories'] ) && $cat === $nodo['settings']['categories'] ) {
            $nodo['settings']['autoplay']       = 'yes';
            $nodo['settings']['autoplay_speed'] = 4000;
            $nodo['settings']['smartspeed']     = 700;
            $nodo['settings']['pause_on_hover'] = 'yes';
            $nodo['settings']['infinite']       = 'yes';
            $hechos[] = 'carrusel en movimiento';
            return true;
        }
    }

    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as &$hijo ) {
            // El boton solo se toca si esta en la misma seccion que el carrusel
            if ( $tiene_carrusel && isset( $hijo['elType'], $hijo['widgetType'] )
                && 'widget' === $hijo['elType'] && 'button' === $hijo['widgetType'] ) {
                if ( ! isset( $hijo['settings'] ) || ! is_array( $hijo['settings'] ) ) {
                    $hijo['settings'] = array();
                }
                $antes = isset( $hijo['settings']['link']['url'] ) ? $hijo['settings']['link']['url'] : '';
                $hijo['settings']['link'] = array_merge(
                    isset( $hijo['settings']['link'] ) && is_array( $hijo['settings']['link'] ) ? $hijo['settings']['link'] : array(),
                    array( 'url' => $url )
                );
                $hechos[] = 'boton "Ver Todos": ' . ( $antes ? $antes : '(vacio)' ) . ' -> ' . $url;
            }
            hvkx_ajustar( $hijo, $cat, $url, $hechos );
        }
        unset( $hijo );
    }
    return false;
}

/** Dice si dentro de esta rama esta el carrusel de la categoria indicada. */
function hvkx_contiene_carrusel( $nodo, $cat ) {
    if ( ! is_array( $nodo ) ) {
        return false;
    }
    if ( isset( $nodo['widgetType'] ) && 'entox_elementor_product_slider' === $nodo['widgetType']
        && isset( $nodo['settings']['categories'] ) && $cat === $nodo['settings']['categories'] ) {
        return true;
    }
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as $h ) {
            if ( hvkx_contiene_carrusel( $h, $cat ) ) {
                return true;
            }
        }
    }
    return false;
}

if ( getenv( 'HVKX2_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $arbol = array(
        array( 'id' => 'secA', 'elType' => 'container', 'elements' => array(
            array( 'id' => 'bA', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Ver Todos', 'link' => array( 'url' => '/explorar/' ) ) ),
            array( 'id' => 'wA', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_list', 'settings' => array( 'categories' => 'alojamientos' ) ),
        ) ),
        array( 'id' => 'secB', 'elType' => 'container', 'elements' => array(
            array( 'id' => 'hB', 'elType' => 'widget', 'widgetType' => 'entox_elementor_heading', 'settings' => array( 'title' => 'Vehiculos en Alquiler' ) ),
            array( 'id' => 'bB', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Ver Todos', 'link' => array( 'url' => '/explorar/?cat=car', 'is_external' => '' ) ) ),
            array( 'id' => 'wB', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_slider', 'settings' => array( 'categories' => 'automoviles', 'autoplay' => '', 'item_number' => 3 ) ),
        ) ),
    );
    $hechos = array();
    foreach ( $arbol as &$n ) { hvkx_ajustar( $n, 'automoviles', '/explorar/?cat=automoviles', $hechos ); }
    unset( $n );

    $slider = $arbol[1]['elements'][2]['settings'];
    $chk( 'carrusel: movimiento activado', 'yes' === $slider['autoplay'] && 4000 === $slider['autoplay_speed'] );
    $chk( 'carrusel: se detiene al pasar el mouse y da la vuelta', 'yes' === $slider['pause_on_hover'] && 'yes' === $slider['infinite'] );
    $chk( 'carrusel: conserva sus otras opciones', 3 === $slider['item_number'] && 'automoviles' === $slider['categories'] );
    $chk( 'boton de vehiculos corregido', '/explorar/?cat=automoviles' === $arbol[1]['elements'][1]['settings']['link']['url'] );
    $chk( 'boton conserva su texto y demas datos', 'Ver Todos' === $arbol[1]['elements'][1]['settings']['text'] && isset( $arbol[1]['elements'][1]['settings']['link']['is_external'] ) );
    $chk( 'boton de OTRA seccion no se toca', '/explorar/' === $arbol[0]['elements'][0]['settings']['link']['url'] );
    $chk( 'la grilla de alojamientos no se toca', 'alojamientos' === $arbol[0]['elements'][1]['settings']['categories'] );

    // Idempotencia
    $h2 = array();
    foreach ( $arbol as &$n2 ) { hvkx_ajustar( $n2, 'automoviles', '/explorar/?cat=automoviles', $h2 ); }
    unset( $n2 );
    $chk( 'idempotente: mismo resultado al repetir', '/explorar/?cat=automoviles' === $arbol[1]['elements'][1]['settings']['link']['url'] && 'yes' === $arbol[1]['elements'][2]['settings']['autoplay'] );

    $chk( 'estilos: incluyen flechas y puntos del carrusel', false !== strpos( base64_decode( $GLOBALS['HVKX_B64'] ), 'owl-dots' ) && false !== strpos( base64_decode( $GLOBALS['HVKX_B64'] ), 'hvkc-css' ) );
    $chk( 'estilos: conservan lo de las tarjetas', false !== strpos( base64_decode( $GLOBALS['HVKX_B64'] ), 'hvkt-css' ) && false !== strpos( base64_decode( $GLOBALS['HVKX_B64'] ), 'Flag_of_Chile' ) );

    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'aplicar';
$front = (int) get_option( 'page_on_front' );
$tabla = $wpdb->prefix . 'snippets';

$purgar = function () use ( $front ) {
    delete_post_meta( $front, '_elementor_css' );
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
};

if ( 'restore' === $mode ) {
    $prev = get_option( 'hvkp_carrusel2_backup' );
    if ( $prev ) {
        update_post_meta( $front, '_elementor_data', wp_slash( $prev ) );
        echo "[OK]    Portada restaurada\n";
    }
    $purgar();
    exit( 0 );
}

// 1) Portada: movimiento del carrusel y boton
$json  = get_post_meta( $front, '_elementor_data', true );
$arbol = is_string( $json ) ? json_decode( $json, true ) : null;
if ( ! is_array( $arbol ) ) {
    echo "[ERROR] No se pudo leer la portada.\n";
    exit( 1 );
}
$hechos = array();
foreach ( $arbol as &$nodo ) {
    hvkx_ajustar( $nodo, $HVKX_CAT, $HVKX_URL, $hechos );
}
unset( $nodo );

if ( $hechos ) {
    $nuevo = wp_json_encode( $arbol );
    if ( ! is_string( $nuevo ) || null === json_decode( $nuevo, true ) ) {
        echo "[ERROR] La portada no se pudo reescribir de forma segura. Nada fue modificado.\n";
        exit( 1 );
    }
    update_option( 'hvkp_carrusel2_backup', $json, false );
    update_post_meta( $front, '_elementor_data', wp_slash( $nuevo ) );
    foreach ( $hechos as $h ) { echo "[OK]    $h\n"; }
} else {
    echo "[AVISO] No se encontro el carrusel de vehiculos en la portada\n";
}

// 2) Estilos
$code = base64_decode( $HVKX_B64 );
if ( md5( $code ) !== $HVKX_MD5 ) {
    echo "[ERROR] Los estilos no pasaron la verificacion; se dejan como estaban.\n";
} else {
    $existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKX_NAME ) );
    $fila = array( 'code' => $code, 'active' => 1, 'scope' => 'front-end', 'modified' => current_time( 'mysql' ) );
    if ( $existe ) {
        $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
        echo "[OK]    Estilos del carrusel actualizados (flechas y puntos nuevos)\n";
    } else {
        $wpdb->insert( $tabla, array_merge( $fila, array( 'name' => $HVKX_NAME, 'description' => 'Tarjetas y carrusel', 'tags' => 'homvuk', 'priority' => 12 ) ) );
        echo "[OK]    Estilos instalados\n";
    }
}

$purgar();
echo "[OK]    Caches purgadas\n";

// 3) Verificacion
$res = wp_remote_get( home_url( '/?hvkx=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $body = (string) wp_remote_retrieve_body( $res );
    echo ( false !== strpos( $body, 'cat=automoviles' ) ? "[OK]    Verificado: el boton ya filtra por automoviles\n" : "[AVISO] El boton aun no muestra el filtro nuevo (cache)\n" );
    echo ( false !== strpos( $body, 'hvkc-css' ) ? "[OK]    Verificado: estilos del carrusel activos\n" : "[AVISO] Los estilos del carrusel aun no aparecen (cache)\n" );
    echo ( false !== strpos( $body, '"autoplay":true' ) || false !== strpos( $body, "data-autoplay" ) ? "[OK]    Verificado: el carrusel arranca en movimiento\n" : "" );
}
echo "Listo. Para volver atras: php hvkp-carrusel2.php restore\n";
