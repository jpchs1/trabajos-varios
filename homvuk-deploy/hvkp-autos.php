<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Autos PRO (hvkp-autos)
 *
 * Optimiza UI/UX del arriendo de autos SIN dejar archivos PHP en disco:
 *  1. Snippet "HOMVUK Autos PRO" guardado en BD (plugin Code Snippets):
 *     directo al pago tras Reservar, estilos PRO del formulario, textos ES,
 *     campo vehiculos oculto, sello de confianza y boton WhatsApp.
 *  2. Oculta los selectores de ubicacion con datos demo (Houston, San Diego...).
 *  3. Envia a papelera las ubicaciones demo del tema.
 *  4. Activa pago como invitado (sin crear cuenta = menos clicks).
 *
 * Uso:  php hvkp-autos.php            (instalar)
 *       php hvkp-autos.php restore    (revertir todo)
 */

$HVKA_SNIPPET_B64 = 'LyoqCiAqIEhPTVZVSyBBdXRvcyBQUk8gLSBmbHVqbyBkZSBhcnJpZW5kbyBjb24gbWVub3MgY2xpY2tzLgogKiBHdWFyZGFkbyBlbiBiYXNlIGRlIGRhdG9zIHZpYSBDb2RlIFNuaXBwZXRzIChzaW4gYXJjaGl2b3MgUEhQIGVuIGRpc2NvKS4KICovCgovLyAxKSBUcmFzIHB1bHNhciAiUmVzZXJ2YXIiLCBzYWx0YXIgZWwgY2Fycml0byBlIGlyIERJUkVDVE8gYWwgcGFnby4KYWRkX2ZpbHRlciggJ3dvb2NvbW1lcmNlX2FkZF90b19jYXJ0X3JlZGlyZWN0JywgZnVuY3Rpb24gKCAkdXJsICkgewogICAgaWYgKCBpc3NldCggJF9SRVFVRVNUWydjdXN0b21fcHJvZHVjdF90eXBlJ10gKQogICAgICAgICYmICdvdmFicndfY2FyX3JlbnRhbCcgPT09ICRfUkVRVUVTVFsnY3VzdG9tX3Byb2R1Y3RfdHlwZSddCiAgICAgICAgJiYgZnVuY3Rpb25fZXhpc3RzKCAnd2NfZ2V0X2NoZWNrb3V0X3VybCcgKSApIHsKICAgICAgICByZXR1cm4gd2NfZ2V0X2NoZWNrb3V0X3VybCgpOwogICAgfQogICAgcmV0dXJuICR1cmw7Cn0sIDk5ICk7CgovLyBTaW4gYXZpc28gImFncmVnYWRvIGFsIGNhcnJpdG8iOiBlbCBjbGllbnRlIHlhIGVzdGEgZW4gZWwgcGFnby4KYWRkX2ZpbHRlciggJ3djX2FkZF90b19jYXJ0X21lc3NhZ2VfaHRtbCcsIGZ1bmN0aW9uICggJG1lc3NhZ2UgKSB7CiAgICBpZiAoIGlzc2V0KCAkX1JFUVVFU1RbJ2N1c3RvbV9wcm9kdWN0X3R5cGUnXSApICYmICdvdmFicndfY2FyX3JlbnRhbCcgPT09ICRfUkVRVUVTVFsnY3VzdG9tX3Byb2R1Y3RfdHlwZSddICkgewogICAgICAgIHJldHVybiAnJzsKICAgIH0KICAgIHJldHVybiAkbWVzc2FnZTsKfSwgOTkgKTsKCi8vIDIpIFRleHRvcyBkZWwgZm9ybXVsYXJpbyBkZSByZXNlcnZhIHF1ZSBmYWx0YWJhbiBlbiBlc3Bhbm9sLgphZGRfZmlsdGVyKCAnZ2V0dGV4dCcsIGZ1bmN0aW9uICggJHRyYW5zbGF0aW9uLCAkdGV4dCwgJGRvbWFpbiApIHsKICAgIGlmICggJ292YS1icncnICE9PSAkZG9tYWluICkgewogICAgICAgIHJldHVybiAkdHJhbnNsYXRpb247CiAgICB9CiAgICAkbWFwID0gYXJyYXkoCiAgICAgICAgJ1RoaXMgZmllbGQgaXMgcmVxdWlyZWQuJyA9PiAnRXN0ZSBjYW1wbyBlcyBvYmxpZ2F0b3Jpby4nLAogICAgICAgICdUaGlzIGZpZWxkIGlzIHJlcXVpcmVkJyAgPT4gJ0VzdGUgY2FtcG8gZXMgb2JsaWdhdG9yaW8nLAogICAgICAgICdCb29raW5nIEZvcm0nICAgICAgICAgICAgPT4gJ1Jlc2VydmEgZW4gbMOtbmVhJywKICAgICAgICAnQXZhaWxhYmxlOicgICAgICAgICAgICAgID0+ICdEaXNwb25pYmxlOicsCiAgICAgICAgJ1BpY2stdXAgTG9jYXRpb24nICAgICAgICA9PiAnTHVnYXIgZGUgZW50cmVnYScsCiAgICAgICAgJ0Ryb3Atb2ZmIExvY2F0aW9uJyAgICAgICA9PiAnTHVnYXIgZGUgZGV2b2x1Y2nDs24nLAogICAgICAgICdTZWxlY3QgTG9jYXRpb24nICAgICAgICAgPT4gJ1NlbGVjY2lvbmEgdWJpY2FjacOzbicsCiAgICApOwogICAgcmV0dXJuIGlzc2V0KCAkbWFwWyAkdGV4dCBdICkgPyAkbWFwWyAkdGV4dCBdIDogJHRyYW5zbGF0aW9uOwp9LCAyMCwgMyApOwoKLy8gMykgRXN0aWxvIFBSTyBkZWwgZm9ybXVsYXJpbyBkZSByZXNlcnZhIHkgdGFyamV0YXMgZGVsIGNhdGFsb2dvLgphZGRfYWN0aW9uKCAnd3BfaGVhZCcsIGZ1bmN0aW9uICgpIHsKICAgIGVjaG8gJzxzdHlsZSBpZD0iaHZrYS1jc3MiPicKICAgIC4gJyNvdmEtcHVyY2hhc2V7YmFja2dyb3VuZDojZmZmO2JvcmRlcjoxcHggc29saWQgI2VlZjBmNDtib3JkZXItcmFkaXVzOjE4cHg7Ym94LXNoYWRvdzowIDE0cHggNDBweCByZ2JhKDE2LDI0LDQwLC4wOSk7b3ZlcmZsb3c6aGlkZGVufScKICAgIC4gJyNvdmEtcHVyY2hhc2UgdWwudGFic3tkaXNwbGF5OmZsZXghaW1wb3J0YW50O2dhcDo2cHg7YmFja2dyb3VuZDojZjZmN2ZiO3BhZGRpbmc6NnB4O21hcmdpbjowfScKICAgIC4gJyNvdmEtcHVyY2hhc2UgdWwudGFicyAuaXRlbXtmbGV4OjE7dGV4dC1hbGlnbjpjZW50ZXI7Ym9yZGVyLXJhZGl1czoxMnB4IWltcG9ydGFudDtwYWRkaW5nOjEzcHggMTBweCFpbXBvcnRhbnQ7Zm9udC13ZWlnaHQ6NzAwO2N1cnNvcjpwb2ludGVyO2JvcmRlcjowIWltcG9ydGFudDtiYWNrZ3JvdW5kOnRyYW5zcGFyZW50O2NvbG9yOiM1YTYwNzI7dHJhbnNpdGlvbjphbGwgLjE1cyBlYXNlfScKICAgIC4gJyNvdmEtcHVyY2hhc2UgdWwudGFicyAuaXRlbS5hY3RpdmV7YmFja2dyb3VuZDojZmZmIWltcG9ydGFudDtjb2xvcjojZDYzMzg0IWltcG9ydGFudDtib3gtc2hhZG93OjAgNHB4IDE0cHggcmdiYSgxNiwyNCw0MCwuMDgpfScKICAgIC4gJyNvdmEtcHVyY2hhc2UgLm92YWJyd19ib29raW5nX2Zvcm0sI292YS1wdXJjaGFzZSAucmVxdWVzdF9ib29raW5ne3BhZGRpbmc6NnB4IDE4cHggMThweH0nCiAgICAuICcjb3ZhLXB1cmNoYXNlIHNlbGVjdCwjb3ZhLXB1cmNoYXNlIGlucHV0W3R5cGU9dGV4dF0sI292YS1wdXJjaGFzZSBpbnB1dFt0eXBlPW51bWJlcl0sI292YS1wdXJjaGFzZSBpbnB1dFt0eXBlPWVtYWlsXSwjb3ZhLXB1cmNoYXNlIHRleHRhcmVhe2JvcmRlci1yYWRpdXM6MTJweCFpbXBvcnRhbnQ7Ym9yZGVyOjEuNXB4IHNvbGlkICNlNmU4ZWYhaW1wb3J0YW50O2JhY2tncm91bmQ6I2ZhZmJmZSFpbXBvcnRhbnQ7dHJhbnNpdGlvbjpib3JkZXItY29sb3IgLjE1cyBlYXNlLGJveC1zaGFkb3cgLjE1cyBlYXNlfScKICAgIC4gJyNvdmEtcHVyY2hhc2Ugc2VsZWN0OmZvY3VzLCNvdmEtcHVyY2hhc2UgaW5wdXQ6Zm9jdXMsI292YS1wdXJjaGFzZSB0ZXh0YXJlYTpmb2N1c3tib3JkZXItY29sb3I6I2U4NDM5MyFpbXBvcnRhbnQ7Ym94LXNoYWRvdzowIDAgMCAzcHggcmdiYSgyMzIsNjcsMTQ3LC4xMikhaW1wb3J0YW50O291dGxpbmU6bm9uZX0nCiAgICAuICcjYm9va2luZ19mb3JtIGJ1dHRvbi5zdWJtaXQsI292YS1wdXJjaGFzZSAuc3VibWl0LmJ0bl90cmFue3dpZHRoOjEwMCU7YmFja2dyb3VuZDpsaW5lYXItZ3JhZGllbnQoMTM1ZGVnLCNlODQzOTMsI2Q2MzM4NCkhaW1wb3J0YW50O2NvbG9yOiNmZmYhaW1wb3J0YW50O2JvcmRlcjowIWltcG9ydGFudDtib3JkZXItcmFkaXVzOjk5OXB4IWltcG9ydGFudDtwYWRkaW5nOjE1cHggMjBweCFpbXBvcnRhbnQ7Zm9udC13ZWlnaHQ6ODAwIWltcG9ydGFudDtsZXR0ZXItc3BhY2luZzouMDFlbTtib3gtc2hhZG93OjAgMTBweCAyNHB4IHJnYmEoMjMyLDY3LDE0NywuMzUpO3RyYW5zaXRpb246dHJhbnNmb3JtIC4xNXMgZWFzZSxib3gtc2hhZG93IC4xNXMgZWFzZTtjdXJzb3I6cG9pbnRlcn0nCiAgICAuICcjYm9va2luZ19mb3JtIGJ1dHRvbi5zdWJtaXQ6aG92ZXIsI292YS1wdXJjaGFzZSAuc3VibWl0LmJ0bl90cmFuOmhvdmVye3RyYW5zZm9ybTp0cmFuc2xhdGVZKC0ycHgpO2JveC1zaGFkb3c6MCAxNHB4IDMwcHggcmdiYSgyMzIsNjcsMTQ3LC40NSl9JwogICAgLiAnLnJlbnRhbF9pdGVtOmhhcyhpbnB1dFtuYW1lPW92YWJyd19udW1iZXJfdmVoaWNsZV1bbWF4PSIxIl0pe2Rpc3BsYXk6bm9uZSFpbXBvcnRhbnR9JwogICAgLiAnLmVudG94LWFzay1xdWVzdGlvbiAud2NmbV9jYXRhbG9nX2VucXVpcnksLndjZm1fY2F0YWxvZ19lbnF1aXJ5X2J1dHRvbl93cmFwcGVyIGEud2NmbV9jYXRhbG9nX2VucXVpcnl7YmFja2dyb3VuZDp0cmFuc3BhcmVudCFpbXBvcnRhbnQ7Ym9yZGVyOjEuNXB4IHNvbGlkICNkOGRiZTQhaW1wb3J0YW50O2NvbG9yOiMzYTNmNTIhaW1wb3J0YW50O2JvcmRlci1yYWRpdXM6OTk5cHghaW1wb3J0YW50O2JveC1zaGFkb3c6bm9uZSFpbXBvcnRhbnR9JwogICAgLiAnLmVudG94LWFzay1xdWVzdGlvbiAud2NmbV9jYXRhbG9nX2VucXVpcnk6aG92ZXJ7Ym9yZGVyLWNvbG9yOiNlODQzOTMhaW1wb3J0YW50O2NvbG9yOiNkNjMzODQhaW1wb3J0YW50fScKICAgIC4gJyNodmthLXRydXN0e2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpjZW50ZXI7anVzdGlmeS1jb250ZW50OmNlbnRlcjtnYXA6OHB4O21hcmdpbjoxMnB4IDAgNHB4O2NvbG9yOiM2YTcxODY7Zm9udC1zaXplOi44NXJlbTtmb250LXdlaWdodDo2MDB9JwogICAgLiAnYSNodmthLXdhe2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpjZW50ZXI7anVzdGlmeS1jb250ZW50OmNlbnRlcjtnYXA6OHB4O21hcmdpbjoxMHB4IDAgNHB4O3BhZGRpbmc6MTJweDtib3JkZXI6MS41cHggc29saWQgIzI1ZDM2Njtjb2xvcjojMTI4YzRiIWltcG9ydGFudDtib3JkZXItcmFkaXVzOjk5OXB4O2ZvbnQtd2VpZ2h0OjcwMDt0ZXh0LWRlY29yYXRpb246bm9uZTt0cmFuc2l0aW9uOmFsbCAuMTVzIGVhc2V9JwogICAgLiAnYSNodmthLXdhOmhvdmVye2JhY2tncm91bmQ6IzI1ZDM2Njtjb2xvcjojZmZmIWltcG9ydGFudH0nCiAgICAuICdAbWVkaWEobWluLXdpZHRoOjk5MnB4KXsuZW50b3gtc3VtbWFyeS1yaWdodHtwb3NpdGlvbjpzdGlja3k7dG9wOjk2cHg7YWxpZ24tc2VsZjpmbGV4LXN0YXJ0fX0nCiAgICAuICd1bC5wcm9kdWN0cyBsaS5wcm9kdWN0LGxpLnByb2R1Y3R7Ym9yZGVyLXJhZGl1czoxNnB4IWltcG9ydGFudDtvdmVyZmxvdzpoaWRkZW47dHJhbnNpdGlvbjp0cmFuc2Zvcm0gLjE4cyBlYXNlLGJveC1zaGFkb3cgLjE4cyBlYXNlIWltcG9ydGFudH0nCiAgICAuICd1bC5wcm9kdWN0cyBsaS5wcm9kdWN0OmhvdmVyLGxpLnByb2R1Y3Q6aG92ZXJ7dHJhbnNmb3JtOnRyYW5zbGF0ZVkoLTRweCk7Ym94LXNoYWRvdzowIDE2cHggMzRweCByZ2JhKDE2LDI0LDQwLC4xNCkhaW1wb3J0YW50fScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXRodW1ibmFpbCBpbWd7dHJhbnNpdGlvbjp0cmFuc2Zvcm0gLjM1cyBlYXNlfScKICAgIC4gJ2xpLnByb2R1Y3Q6aG92ZXIgLmVudG94LXByb2R1Y3QtdGh1bWJuYWlsIGltZ3t0cmFuc2Zvcm06c2NhbGUoMS4wNCl9JwogICAgLiAnQG1lZGlhIChwcmVmZXJzLXJlZHVjZWQtbW90aW9uOnJlZHVjZSl7I2Jvb2tpbmdfZm9ybSBidXR0b24uc3VibWl0LGxpLnByb2R1Y3QsLmVudG94LXByb2R1Y3QtdGh1bWJuYWlsIGltZ3t0cmFuc2l0aW9uOm5vbmUhaW1wb3J0YW50fSNib29raW5nX2Zvcm0gYnV0dG9uLnN1Ym1pdDpob3ZlcixsaS5wcm9kdWN0OmhvdmVye3RyYW5zZm9ybTpub25lIWltcG9ydGFudH19JwogICAgLiAnPC9zdHlsZT4nOwp9LCA5OSApOwoKLy8gNCkgTWVub3MgY2FtcG9zIHkgYXl1ZGFzOiAxIHZlaGljdWxvIGZpam8sIGNvbmZpYW56YSB5IFdoYXRzQXBwIGJham8gZWwgYm90b24uCmFkZF9hY3Rpb24oICd3cF9mb290ZXInLCBmdW5jdGlvbiAoKSB7CiAgICA/PgogICAgPHNjcmlwdCBpZD0iaHZrYS1qcyI+CiAgICAoZnVuY3Rpb24oKXsKICAgICAgICB2YXIgYmYgPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnYm9va2luZ19mb3JtJyk7CiAgICAgICAgaWYgKCFiZikgcmV0dXJuOwogICAgICAgIHZhciBudiA9IGJmLnF1ZXJ5U2VsZWN0b3IoJ2lucHV0W25hbWU9Im92YWJyd19udW1iZXJfdmVoaWNsZSJdJyk7CiAgICAgICAgaWYgKG52ICYmIG52LmdldEF0dHJpYnV0ZSgnbWF4JykgPT09ICcxJykgewogICAgICAgICAgICBudi52YWx1ZSA9ICcxJzsKICAgICAgICAgICAgdmFyIHJpID0gbnYuY2xvc2VzdCgnLnJlbnRhbF9pdGVtJyk7CiAgICAgICAgICAgIGlmIChyaSkgeyByaS5zdHlsZS5kaXNwbGF5ID0gJ25vbmUnOyB9CiAgICAgICAgfQogICAgICAgIHZhciBzYiA9IGJmLnF1ZXJ5U2VsZWN0b3IoJ2J1dHRvbi5zdWJtaXQnKTsKICAgICAgICBpZiAoc2IgJiYgIWRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdodmthLXRydXN0JykpIHsKICAgICAgICAgICAgdmFyIG5hbWUgPSAoZG9jdW1lbnQudGl0bGUgfHwgJycpLnNwbGl0KCd8JylbMF0udHJpbSgpOwogICAgICAgICAgICB2YXIgd2EgPSAnaHR0cHM6Ly93YS5tZS81Njk0MDIxMTQ1OT90ZXh0PScgKyBlbmNvZGVVUklDb21wb25lbnQoJ0hvbGEgSE9NVlVLLCBxdWllcm8gYXJyZW5kYXI6ICcgKyBuYW1lICsgJyAnICsgbG9jYXRpb24uaHJlZik7CiAgICAgICAgICAgIHNiLmluc2VydEFkamFjZW50SFRNTCgnYWZ0ZXJlbmQnLAogICAgICAgICAgICAgICAgJzxkaXYgaWQ9Imh2a2EtdHJ1c3QiPvCflJIgUGFnbyBzZWd1cm8gJm1pZGRvdDsgQ29uZmlybWFjacOzbiBpbm1lZGlhdGE8L2Rpdj4nICsKICAgICAgICAgICAgICAgICc8YSBpZD0iaHZrYS13YSIgaHJlZj0iJyArIHdhICsgJyIgdGFyZ2V0PSJfYmxhbmsiIHJlbD0ibm9vcGVuZXIiPicgKwogICAgICAgICAgICAgICAgJzxzdmcgd2lkdGg9IjE3IiBoZWlnaHQ9IjE3IiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9ImN1cnJlbnRDb2xvciIgYXJpYS1oaWRkZW49InRydWUiPjxwYXRoIGQ9Ik0xNy40NzIgMTQuMzgyYy0uMjk3LS4xNDktMS43NTgtLjg2Ny0yLjAzLS45NjctLjI3My0uMDk5LS40NzEtLjE0OC0uNjcuMTUtLjE5Ny4yOTctLjc2Ny45NjYtLjk0IDEuMTY0LS4xNzMuMTk5LS4zNDcuMjIzLS42NDQuMDc1LS4yOTctLjE1LTEuMjU1LS40NjMtMi4zOS0xLjQ3NS0uODgzLS43ODgtMS40OC0xLjc2MS0xLjY1My0yLjA1OS0uMTczLS4yOTctLjAxOC0uNDU4LjEzLS42MDYuMTM0LS4xMzMuMjk4LS4zNDcuNDQ2LS41Mi4xNDktLjE3NC4xOTgtLjI5OC4yOTgtLjQ5Ny4wOTktLjE5OC4wNS0uMzcxLS4wMjUtLjUyLS4wNzUtLjE0OS0uNjY5LTEuNjEyLS45MTYtMi4yMDctLjI0Mi0uNTc5LS40ODctLjUtLjY2OS0uNTEtLjE3My0uMDA4LS4zNzEtLjAxLS41Ny0uMDEtLjE5OCAwLS41Mi4wNzQtLjc5Mi4zNzItLjI3Mi4yOTctMS4wNCAxLjAxNi0xLjA0IDIuNDc5IDAgMS40NjIgMS4wNjUgMi44NzUgMS4yMTMgMy4wNzQuMTQ5LjE5OCAyLjA5NiAzLjIgNS4wNzcgNC40ODcuNzA5LjMwNiAxLjI2Mi40ODkgMS42OTQuNjI1LjcxMi4yMjcgMS4zNi4xOTUgMS44NzEuMTE4LjU3MS0uMDg1IDEuNzU4LS43MTkgMi4wMDYtMS40MTMuMjQ4LS42OTQuMjQ4LTEuMjg5LjE3My0xLjQxMy0uMDc0LS4xMjQtLjI3Mi0uMTk4LS41Ny0uMzQ3bS01LjQyMSA3LjQwM2gtLjAwNGE5Ljg3IDkuODcgMCAwMS01LjAzMS0xLjM3OGwtLjM2MS0uMjE0LTMuNzQxLjk4Mi45OTgtMy42NDgtLjIzNS0uMzc0YTkuODYgOS44NiAwIDAxLTEuNTEtNS4yNmMuMDAxLTUuNDUgNC40MzYtOS44ODQgOS44ODgtOS44ODQgMi42NCAwIDUuMTIyIDEuMDMgNi45ODggMi44OThhOS44MjUgOS44MjUgMCAwMTIuODkzIDYuOTk0Yy0uMDAzIDUuNDUtNC40MzcgOS44ODQtOS44ODUgOS44ODRtOC40MTMtMTguMjk3QTExLjgxNSAxMS44MTUgMCAwMDEyLjA1IDBDNS40OTUgMCAuMTYgNS4zMzUuMTU3IDExLjg5MmMwIDIuMDk2LjU0NyA0LjE0MiAxLjU4OCA1Ljk0NUwuMDU3IDI0bDYuMzA1LTEuNjU0YTExLjg4MiAxMS44ODIgMCAwMDUuNjgzIDEuNDQ4aC4wMDVjNi41NTQgMCAxMS44OS01LjMzNSAxMS44OTMtMTEuODkzYTExLjgyMSAxMS44MjEgMCAwMC0zLjQ4LTguNDEzeiIvPjwvc3ZnPicgKwogICAgICAgICAgICAgICAgJ8K/RHVkYXM/IFJlc2VydmEgcG9yIFdoYXRzQXBwPC9hPicpOwogICAgICAgIH0KICAgIH0pKCk7CiAgICA8L3NjcmlwdD4KICAgIDw/cGhwCn0sIDk5ICk7';
$HVKA_SNIPPET_MD5 = 'c2c880abe5d131a9654c325476eeae7b';
$HVKA_NAME        = 'HOMVUK Autos PRO';

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$ok    = array();
$warn  = array();
$err   = array();
$table = $wpdb->prefix . 'snippets';

$purge_caches = function () {
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
    if ( function_exists( 'wp_cache_clear_cache' ) ) { wp_cache_clear_cache(); }
};

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_autos_backup' );
    if ( ! is_array( $b ) ) {
        echo "[AVISO] No hay respaldo guardado; nada que restaurar.\n";
        exit( 0 );
    }
    $wpdb->update( $table, array( 'active' => 0 ), array( 'name' => $HVKA_NAME ) );
    echo "[OK]    Snippet desactivado\n";
    if ( isset( $b['opt_pickup'] ) )  { update_option( 'ova_brw_booking_form_show_pickup_location', $b['opt_pickup'] ); }
    if ( isset( $b['opt_pickoff'] ) ) { update_option( 'ova_brw_booking_form_show_pickoff_location', $b['opt_pickoff'] ); }
    if ( isset( $b['opt_guest'] ) )   { update_option( 'woocommerce_enable_guest_checkout', $b['opt_guest'] ); }
    if ( ! empty( $b['product_meta'] ) && is_array( $b['product_meta'] ) ) {
        foreach ( $b['product_meta'] as $pid => $prev ) {
            if ( '' === $prev[0] ) { delete_post_meta( $pid, 'ovabrw_show_pickup_location_product' ); } else { update_post_meta( $pid, 'ovabrw_show_pickup_location_product', $prev[0] ); }
            if ( '' === $prev[1] ) { delete_post_meta( $pid, 'ovabrw_show_pickoff_location_product' ); } else { update_post_meta( $pid, 'ovabrw_show_pickoff_location_product', $prev[1] ); }
        }
        echo "[OK]    Metas de ubicacion de productos restauradas\n";
    }
    if ( ! empty( $b['trashed'] ) && is_array( $b['trashed'] ) ) {
        foreach ( $b['trashed'] as $pid ) { wp_untrash_post( $pid ); }
        echo "[OK]    Ubicaciones demo recuperadas de la papelera\n";
    }
    $purge_caches();
    echo "Restauracion completa.\n";
    exit( 0 );
}

$backup = array();

// 1) Asegurar plugin Code Snippets activo
$active_plugins = (array) get_option( 'active_plugins', array() );
if ( ! in_array( 'code-snippets/code-snippets.php', $active_plugins, true ) ) {
    if ( file_exists( WP_CONTENT_DIR . '/plugins/code-snippets/code-snippets.php' ) ) {
        $active_plugins[] = 'code-snippets/code-snippets.php';
        update_option( 'active_plugins', array_values( $active_plugins ) );
        $ok[] = 'Plugin Code Snippets activado (estaba instalado pero inactivo)';
    } else {
        echo "[ERROR] El plugin Code Snippets no esta en el servidor. Nada fue modificado.\n";
        exit( 1 );
    }
} else {
    $ok[] = 'Plugin Code Snippets ya estaba activo';
}

// 2) Asegurar tabla de snippets
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
    $charset = $wpdb->get_charset_collate();
    $wpdb->query( "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name TINYTEXT NOT NULL,
        description TEXT NOT NULL,
        code LONGTEXT NOT NULL,
        tags LONGTEXT NOT NULL,
        scope VARCHAR(15) NOT NULL DEFAULT 'global',
        priority SMALLINT NOT NULL DEFAULT 10,
        active TINYINT(1) NOT NULL DEFAULT 0,
        modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (id), KEY scope (scope), KEY active (active)
    ) $charset" );
    $ok[] = 'Tabla de snippets creada';
}

// 3) Instalar / actualizar el snippet (verificado por md5)
$code = base64_decode( $HVKA_SNIPPET_B64 );
if ( md5( $code ) !== $HVKA_SNIPPET_MD5 ) {
    echo "[ERROR] El snippet embebido no paso la verificacion md5. Nada fue modificado.\n";
    exit( 1 );
}
$row = array(
    'name'        => $HVKA_NAME,
    'description' => 'Flujo de arriendo optimizado: directo al pago, estilos PRO del formulario, textos en espanol, WhatsApp. Instalado via hvkp-autos.',
    'code'        => $code,
    'tags'        => 'homvuk',
    'scope'       => 'front-end',
    'priority'    => 10,
    'active'      => 1,
    'modified'    => current_time( 'mysql' ),
);
$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE name = %s", $HVKA_NAME ) );
if ( $existing ) {
    $res = $wpdb->update( $table, $row, array( 'id' => (int) $existing ) );
    $ok[] = 'Snippet "HOMVUK Autos PRO" actualizado (id ' . $existing . ')';
} else {
    $res = $wpdb->insert( $table, $row );
    if ( false === $res ) {
        echo "[ERROR] No se pudo insertar el snippet: " . $wpdb->last_error . "\n";
        exit( 1 );
    }
    $ok[] = 'Snippet "HOMVUK Autos PRO" instalado y activado (id ' . $wpdb->insert_id . ')';
}

// 4) Ocultar selectores de ubicacion (datos demo) en formularios de reserva
$backup['opt_pickup']  = get_option( 'ova_brw_booking_form_show_pickup_location', '' );
$backup['opt_pickoff'] = get_option( 'ova_brw_booking_form_show_pickoff_location', '' );
update_option( 'ova_brw_booking_form_show_pickup_location', 'no' );
update_option( 'ova_brw_booking_form_show_pickoff_location', 'no' );

$product_ids = get_posts( array( 'post_type' => 'product', 'numberposts' => -1, 'fields' => 'ids', 'post_status' => 'any' ) );
$backup['product_meta'] = array();
foreach ( $product_ids as $pid ) {
    $backup['product_meta'][ $pid ] = array(
        (string) get_post_meta( $pid, 'ovabrw_show_pickup_location_product', true ),
        (string) get_post_meta( $pid, 'ovabrw_show_pickoff_location_product', true ),
    );
    update_post_meta( $pid, 'ovabrw_show_pickup_location_product', 'no' );
    update_post_meta( $pid, 'ovabrw_show_pickoff_location_product', 'no' );
}
$ok[] = 'Selectores de ubicacion ocultos en ' . count( $product_ids ) . ' productos (menos campos, menos clicks)';

// 5) Papelera para las ubicaciones demo del tema
$demo_titles = array( 'Houston', 'San Diego', 'San Antonio', 'Airport' );
$locs = get_posts( array( 'post_type' => 'location', 'numberposts' => -1, 'post_status' => 'publish' ) );
$backup['trashed'] = array();
foreach ( $locs as $loc ) {
    if ( in_array( trim( $loc->post_title ), $demo_titles, true ) ) {
        wp_trash_post( $loc->ID );
        $backup['trashed'][] = $loc->ID;
    }
}
if ( $backup['trashed'] ) {
    $ok[] = 'Ubicaciones demo enviadas a papelera: ' . count( $backup['trashed'] ) . ' (Houston, San Diego...)';
} else {
    $warn[] = 'No se encontraron ubicaciones demo publicadas (quiza ya estaban borradas)';
}

// 6) Pago como invitado (sin obligar a crear cuenta)
$backup['opt_guest'] = get_option( 'woocommerce_enable_guest_checkout', '' );
update_option( 'woocommerce_enable_guest_checkout', 'yes' );
$ok[] = 'Pago como invitado activado (checkout sin registro)';

update_option( 'hvkp_autos_backup', $backup, false );

// 7) Purga de caches y verificacion en vivo
$purge_caches();
$test_url = home_url( '/experiencia/porsche-cayenne-2018/?hvka=' ) . time();
$resp = wp_remote_get( $test_url, array( 'timeout' => 30, 'sslverify' => false ) );
if ( is_wp_error( $resp ) ) {
    $warn[] = 'No se pudo verificar en vivo: ' . $resp->get_error_message();
} else {
    $body = (string) wp_remote_retrieve_body( $resp );
    if ( false !== strpos( $body, 'hvka-css' ) ) {
        $ok[] = 'Verificado: estilos PRO del snippet activos en la pagina del auto';
    } else {
        $warn[] = 'El snippet aun no aparece en el HTML (puede ser cache; revisar en unos minutos)';
    }
    if ( false === strpos( $body, 'Houston' ) ) {
        $ok[] = 'Verificado: ubicaciones demo ya no aparecen';
    } else {
        $warn[] = 'Aun se ve "Houston" en la pagina (puede ser cache)';
    }
}

echo "== HOMVUK Autos PRO (sin archivos PHP persistentes) ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
foreach ( $err as $m )  { echo "[ERROR] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-autos.php\n";
echo "Para revertir todo: php hvkp-autos.php restore\n";
