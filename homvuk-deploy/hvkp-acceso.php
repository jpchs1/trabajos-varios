<?php
/*
 * Nunca por web. Se comprueba por la peticion, no por el SAPI: en algunos
 * cPanel el `php` de la terminal es un binario CGI, y comparar con 'cli' a
 * secas hacia que el script terminara en silencio, sin imprimir nada.
 */
if ( PHP_SAPI !== 'cli' && ( isset( $_SERVER['REQUEST_METHOD'] ) || isset( $_SERVER['HTTP_HOST'] ) ) ) {
    exit;
}
// Si algo revienta, que se vea aqui y no solo en un log que nadie mira.
@ini_set( 'display_errors', '1' );
@ini_set( 'log_errors', '1' );
error_reporting( E_ALL );
/**
 * HOMVUK - Pantalla para dar acceso al portal (hvkp-acceso)
 *
 * Instala una pagina privada donde se cargan los datos de un huesped que
 * reservo directo y sale su enlace al portal, listo para copiar al WhatsApp.
 * Es lo mismo que hace `hvkp-reserva.php crear`, pero sin terminal.
 *
 *   https://homvuk.com/acceso/?k=<clave>
 *
 * Los huespedes que llegan por Airbnb no la necesitan: la sincronizacion da
 * de alta su codigo cada hora y entran solos.
 *
 * Todo vive en la base de datos (Code Snippets), sin archivos nuevos.
 *
 * Uso:  php hvkp-acceso.php            (instalar)
 *       php hvkp-acceso.php restore    (quitar la pagina)
 *       php hvkp-acceso.php clave      (rota la clave: el enlace anterior
 *           deja de servir)
 */

$HVKAC_B64  = 'LyoqCiAqIEhPTVZVSyAtIFBhbnRhbGxhIHBhcmEgZGFyIGFjY2VzbyBhbCBwb3J0YWwgKGh2a2FjKQogKgogKiBVbiBodWVzcGVkIHF1ZSBsbGVnYSBwb3IgQWlyYm5iIGVudHJhIHNvbG86IGxhIHNpbmNyb25pemFjaW9uIGRhIGRlIGFsdGEgc3UKICogY29kaWdvIGNhZGEgaG9yYSB5IGVsIHBvcnRhbCBzZSBsbyByZWNvbm9jZS4gUGVybyBlbCBxdWUgcmVzZXJ2YSBkaXJlY3RvCiAqIC1wb3IgV2hhdHNBcHAsIHBvciB0ZWxlZm9uby0gbm8gdGllbmUgbmluZ3VubywgeSBoYXkgcXVlIGNyZWFyc2Vsby4KICoKICogSGFzdGEgYWhvcmEgZXNvIGVyYSBlbnRyYXIgYWwgZXNjcml0b3JpbyBkZSBXb3JkUHJlc3MgeSByZWxsZW5hciBsYSBwYW50YWxsYQogKiBkZSBSZXNlcnZhcywgbyBwZWRpcmxlIGEgYWxndWllbiBxdWUgY29ycmllcmEgdW4gY29tYW5kby4gRXN0byBlcyBsbyBtaXNtbwogKiBlbiB1bmEgcGFnaW5hOiBzZSBwb25lbiBsb3MgZGF0b3MsIHNhbGUgZWwgZW5sYWNlLCBzZSBjb3BpYSBhbCBXaGF0c0FwcC4KICoKICogICBodHRwczovL2hvbXZ1ay5jb20vYWNjZXNvLz9rPTxjbGF2ZT4KICoKICogTGEgY2xhdmUgZXMgdW5hIGNyZWRlbmNpYWw6IHF1aWVuIGxhIHRlbmdhIHB1ZWRlIGNyZWFyIGFjY2Vzb3MgYWwgcG9ydGFsLgogKiBTZSByb3RhIGNvbiBgcGhwIGh2a3AtYWNjZXNvLnBocCBjbGF2ZWAuIEFsIGFicmlybGEgdW5hIHZleiBxdWVkYSBlbiB1bmEKICogY29va2llIGRlIDMwIGRpYXMsIGFzaSBxdWUgbm8gaGF5IHF1ZSBhcnJhc3RyYXJsYSBlbiBsYSBiYXJyYS4KICoKICogRWwgZW5sYWNlIHF1ZSBzYWxlIEVTIGxhIGxsYXZlIGRlIGVzYSByZXNlcnZhOiBxdWllbiBsbyByZWNpYmEgZW50cmEgYSBlbGxhLAogKiBzZWEgcXVpZW4gc2VhLiBMYSBwYWdpbmEgbG8gZGljZSwgcG9ycXVlIHJlZW52aWFyIGVsIGRlIG90cm8gaHVlc3BlZCBmdWUKICogZXhhY3RhbWVudGUgbG8gcXVlIGNvbmZ1bmRpbyBhIHVuIGNsaWVudGUgY29uIGxhIHJlc2VydmEgZGUgb3Ryby4KICovCgpkZWZpbmUoICdIVktBQ19SVVRBJywgJ2FjY2VzbycgKTsKCi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0KICogMS4gUXVpZW4gcHVlZGUgZW50cmFyCiAqID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi8KCmZ1bmN0aW9uIGh2a2FjX3B1ZWRlKCkgewogICAgaWYgKCBjdXJyZW50X3VzZXJfY2FuKCAnbWFuYWdlX29wdGlvbnMnICkgfHwgY3VycmVudF91c2VyX2NhbiggJ2VkaXRfc2hvcF9vcmRlcnMnICkgKSB7CiAgICAgICAgcmV0dXJuIHRydWU7CiAgICB9CgogICAgJGNsYXZlID0gKHN0cmluZykgZ2V0X29wdGlvbiggJ2h2a2FjX2NsYXZlJywgJycgKTsKICAgIGlmICggc3RybGVuKCAkY2xhdmUgKSA8IDIwICkgewogICAgICAgIHJldHVybiBmYWxzZTsKICAgIH0KCiAgICBpZiAoIGlzc2V0KCAkX0dFVFsnayddICkgKSB7CiAgICAgICAgJGsgPSBzYW5pdGl6ZV90ZXh0X2ZpZWxkKCB3cF91bnNsYXNoKCAkX0dFVFsnayddICkgKTsKICAgICAgICBpZiAoIGhhc2hfZXF1YWxzKCAkY2xhdmUsICRrICkgKSB7CiAgICAgICAgICAgIGlmICggISBoZWFkZXJzX3NlbnQoKSApIHsKICAgICAgICAgICAgICAgIHNldGNvb2tpZSgKICAgICAgICAgICAgICAgICAgICAnaHZrYWNfaycsCiAgICAgICAgICAgICAgICAgICAgJGNsYXZlLAogICAgICAgICAgICAgICAgICAgIHRpbWUoKSArIDMwICogREFZX0lOX1NFQ09ORFMsCiAgICAgICAgICAgICAgICAgICAgZGVmaW5lZCggJ0NPT0tJRVBBVEgnICkgJiYgQ09PS0lFUEFUSCA/IENPT0tJRVBBVEggOiAnLycsCiAgICAgICAgICAgICAgICAgICAgZGVmaW5lZCggJ0NPT0tJRV9ET01BSU4nICkgPyBDT09LSUVfRE9NQUlOIDogJycsCiAgICAgICAgICAgICAgICAgICAgaXNfc3NsKCksCiAgICAgICAgICAgICAgICAgICAgdHJ1ZQogICAgICAgICAgICAgICAgKTsKICAgICAgICAgICAgfQogICAgICAgICAgICByZXR1cm4gdHJ1ZTsKICAgICAgICB9CiAgICAgICAgcmV0dXJuIGZhbHNlOwogICAgfQoKICAgIGlmICggaXNzZXQoICRfQ09PS0lFWydodmthY19rJ10gKSApIHsKICAgICAgICByZXR1cm4gaGFzaF9lcXVhbHMoICRjbGF2ZSwgKHN0cmluZykgd3BfdW5zbGFzaCggJF9DT09LSUVbJ2h2a2FjX2snXSApICk7CiAgICB9CgogICAgcmV0dXJuIGZhbHNlOwp9CgovKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09CiAqIDIuIFJ1dGEKICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwoKYWRkX2FjdGlvbiggJ2luaXQnLCAnaHZrYWNfcnV0YXMnICk7CmZ1bmN0aW9uIGh2a2FjX3J1dGFzKCkgewogICAgYWRkX3Jld3JpdGVfcnVsZSggJ14nIC4gSFZLQUNfUlVUQSAuICcvPyQnLCAnaW5kZXgucGhwP2h2a2FjPTEnLCAndG9wJyApOwp9CgphZGRfZmlsdGVyKCAncXVlcnlfdmFycycsICdodmthY19xdWVyeV92YXJzJyApOwpmdW5jdGlvbiBodmthY19xdWVyeV92YXJzKCAkdmFycyApIHsKICAgICR2YXJzW10gPSAnaHZrYWMnOwogICAgcmV0dXJuICR2YXJzOwp9CgphZGRfYWN0aW9uKCAndGVtcGxhdGVfcmVkaXJlY3QnLCAnaHZrYWNfcm91dGVyJyApOwpmdW5jdGlvbiBodmthY19yb3V0ZXIoKSB7CiAgICBpZiAoICEgZ2V0X3F1ZXJ5X3ZhciggJ2h2a2FjJyApICkgewogICAgICAgIHJldHVybjsKICAgIH0KICAgIGlmICggISBjbGFzc19leGlzdHMoICdIVktQX1Rva2VuJyApICkgewogICAgICAgIHN0YXR1c19oZWFkZXIoIDUwMyApOwogICAgICAgIGVjaG8gJ0VsIHBvcnRhbCBubyBlc3RhIGRpc3BvbmlibGUuJzsKICAgICAgICBleGl0OwogICAgfQogICAgaWYgKCAhIGh2a2FjX3B1ZWRlKCkgKSB7CiAgICAgICAgc3RhdHVzX2hlYWRlciggNDAzICk7CiAgICAgICAgbm9jYWNoZV9oZWFkZXJzKCk7CiAgICAgICAgaHZrYWNfdmlzdGFfc2luX3Blcm1pc28oKTsKICAgICAgICBleGl0OwogICAgfQoKICAgIG5vY2FjaGVfaGVhZGVycygpOwogICAgc3RhdHVzX2hlYWRlciggMjAwICk7CgogICAgJGhlY2hvID0gbnVsbDsKICAgIGlmICggJ1BPU1QnID09PSAkX1NFUlZFUlsnUkVRVUVTVF9NRVRIT0QnXSApIHsKICAgICAgICAkaGVjaG8gPSBodmthY19jcmVhcigpOwogICAgfQogICAgaHZrYWNfdmlzdGEoICRoZWNobyApOwogICAgZXhpdDsKfQoKLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogKiAzLiBMb3MgZGF0b3MKICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwoKZnVuY3Rpb24gaHZrYWNfdW5pZGFkZXMoKSB7CiAgICBnbG9iYWwgJHdwZGI7CiAgICAkdCA9ICR3cGRiLT5wcmVmaXggLiAncG9ydGFsX3VuaXRzJzsKICAgIGlmICggJHdwZGItPmdldF92YXIoICR3cGRiLT5wcmVwYXJlKCAnU0hPVyBUQUJMRVMgTElLRSAlcycsICR0ICkgKSAhPT0gJHQgKSB7CiAgICAgICAgcmV0dXJuIGFycmF5KCk7CiAgICB9CiAgICByZXR1cm4gKGFycmF5KSAkd3BkYi0+Z2V0X3Jlc3VsdHMoICJTRUxFQ1QgaWQsIG5hbWUsIHNsdWcgRlJPTSAkdCBXSEVSRSBhY3RpdmUgPSAxIE9SREVSIEJZIG5hbWUiICk7Cn0KCi8qKiBMYXMgcXVlIHNpZ3VlbiB2aWdlbnRlcywgcGFyYSB2b2x2ZXIgYSBjb3BpYXIgdW4gZW5sYWNlIHNpbiBjcmVhciBuYWRhLiAqLwpmdW5jdGlvbiBodmthY192aWdlbnRlcyggJG1heCA9IDEyICkgewogICAgZ2xvYmFsICR3cGRiOwogICAgJHRyID0gJHdwZGItPnByZWZpeCAuICdwb3J0YWxfcmVzZXJ2YXRpb25zJzsKICAgICR0dSA9ICR3cGRiLT5wcmVmaXggLiAncG9ydGFsX3VuaXRzJzsKICAgIHJldHVybiAoYXJyYXkpICR3cGRiLT5nZXRfcmVzdWx0cyggJHdwZGItPnByZXBhcmUoCiAgICAgICAgIlNFTEVDVCByLiosIHUubmFtZSBBUyB1bml0X25hbWUgRlJPTSAkdHIgciBMRUZUIEpPSU4gJHR1IHUgT04gci51bml0X2lkID0gdS5pZAogICAgICAgICBXSEVSRSByLmNoZWNrX291dCA+PSBDVVJEQVRFKCkgQU5EIHIuc3RhdHVzICE9ICdjYW5jZWxsZWQnCiAgICAgICAgIE9SREVSIEJZIHIuY2hlY2tfaW4gTElNSVQgJWQiLCAkbWF4ICkgKTsKfQoKZnVuY3Rpb24gaHZrYWNfZW5sYWNlKCAkcmVzX2lkICkgewogICAgZ2xvYmFsICR3cGRiOwogICAgJHR0ID0gJHdwZGItPnByZWZpeCAuICdwb3J0YWxfYWNjZXNzX3Rva2Vucyc7CiAgICAkdG9rZW4gPSAkd3BkYi0+Z2V0X3ZhciggJHdwZGItPnByZXBhcmUoCiAgICAgICAgIlNFTEVDVCB0b2tlbiBGUk9NICR0dCBXSEVSRSByZXNlcnZhdGlvbl9pZCA9ICVkIEFORCBhY3RpdmUgPSAxIEFORCBleHBpcmVzX2F0ID4gTk9XKCkKICAgICAgICAgT1JERVIgQlkgY3JlYXRlZF9hdCBERVNDIExJTUlUIDEiLCAkcmVzX2lkICkgKTsKICAgIGlmICggISAkdG9rZW4gKSB7CiAgICAgICAgJHRva2VuID0gSFZLUF9Ub2tlbjo6Y3JlYXRlX2Zvcl9yZXNlcnZhdGlvbiggJHJlc19pZCApOwogICAgfQogICAgcmV0dXJuIEhWS1BfVG9rZW46OmdldF9wb3J0YWxfdXJsKCAkdG9rZW4gKTsKfQoKLyoqCiAqIENyZWEgbGEgcmVzZXJ2YSB5IGRldnVlbHZlIHF1ZSBwYXNvLgogKgogKiBEZXZ1ZWx2ZSBzaWVtcHJlIHVuIGFycmF5IGNvbiAnZXJyb3InIG8gY29uICdyZXNlcnZhJywgcGFyYSBxdWUgbGEgdmlzdGEgbm8KICogdGVuZ2EgcXVlIGFkaXZpbmFyIG5hZGEuCiAqLwpmdW5jdGlvbiBodmthY19jcmVhcigpIHsKICAgIGdsb2JhbCAkd3BkYjsKCiAgICBpZiAoICEgaXNzZXQoICRfUE9TVFsnaHZrYWNfbm9uY2UnXSApCiAgICAgICAgfHwgISB3cF92ZXJpZnlfbm9uY2UoIHNhbml0aXplX3RleHRfZmllbGQoIHdwX3Vuc2xhc2goICRfUE9TVFsnaHZrYWNfbm9uY2UnXSApICksICdodmthY19jcmVhcicgKSApIHsKICAgICAgICByZXR1cm4gYXJyYXkoICdlcnJvcicgPT4gJ0xhIHBhZ2luYSBsbGV2YWJhIGRlbWFzaWFkbyB0aWVtcG8gYWJpZXJ0YS4gVnVlbHZlIGEgZW52aWFybGEuJyApOwogICAgfQoKICAgICRjYW1wbyA9IGZ1bmN0aW9uICggJGsgKSB7CiAgICAgICAgcmV0dXJuIGlzc2V0KCAkX1BPU1RbICRrIF0gKSA/IHNhbml0aXplX3RleHRfZmllbGQoIHdwX3Vuc2xhc2goICRfUE9TVFsgJGsgXSApICkgOiAnJzsKICAgIH07CgogICAgJHVuaWRhZCAgID0gKGludCkgJGNhbXBvKCAndW5pZGFkJyApOwogICAgJG5vbWJyZSAgID0gJGNhbXBvKCAnbm9tYnJlJyApOwogICAgJGFwZWxsaWRvID0gJGNhbXBvKCAnYXBlbGxpZG8nICk7CiAgICAkaW4gICAgICAgPSAkY2FtcG8oICdpbicgKTsKICAgICRvdXQgICAgICA9ICRjYW1wbyggJ291dCcgKTsKICAgICRwYXggICAgICA9IG1heCggMSwgKGludCkgJGNhbXBvKCAncGF4JyApICk7CiAgICAkZW1haWwgICAgPSBpc3NldCggJF9QT1NUWydlbWFpbCddICkgPyBzYW5pdGl6ZV9lbWFpbCggd3BfdW5zbGFzaCggJF9QT1NUWydlbWFpbCddICkgKSA6ICcnOwogICAgJHRlbGVmb25vID0gJGNhbXBvKCAndGVsZWZvbm8nICk7CiAgICAkb3JpZ2VuICAgPSAkY2FtcG8oICdvcmlnZW4nICk7CgogICAgaWYgKCAhICR1bmlkYWQgfHwgISAkbm9tYnJlIHx8ICEgJGFwZWxsaWRvIHx8ICEgJGluIHx8ICEgJG91dCApIHsKICAgICAgICByZXR1cm4gYXJyYXkoICdlcnJvcicgPT4gJ0ZhbHRhbiBlbCBkZXBhcnRhbWVudG8sIGVsIG5vbWJyZSwgZWwgYXBlbGxpZG8gbyBsYXMgZmVjaGFzLicgKTsKICAgIH0KICAgIGlmICggISBwcmVnX21hdGNoKCAnL15cZHs0fS1cZHsyfS1cZHsyfSQvJywgJGluICkgfHwgISBwcmVnX21hdGNoKCAnL15cZHs0fS1cZHsyfS1cZHsyfSQvJywgJG91dCApCiAgICAgICAgfHwgc3RydG90aW1lKCAkb3V0ICkgPD0gc3RydG90aW1lKCAkaW4gKSApIHsKICAgICAgICByZXR1cm4gYXJyYXkoICdlcnJvcicgPT4gJ1JldmlzYSBsYXMgZmVjaGFzOiBsYSBzYWxpZGEgdGllbmUgcXVlIHNlciBwb3N0ZXJpb3IgYSBsYSBlbnRyYWRhLicgKTsKICAgIH0KCiAgICAkdHUgPSAkd3BkYi0+cHJlZml4IC4gJ3BvcnRhbF91bml0cyc7CiAgICAkdSAgPSAkd3BkYi0+Z2V0X3JvdyggJHdwZGItPnByZXBhcmUoICJTRUxFQ1QgKiBGUk9NICR0dSBXSEVSRSBpZCA9ICVkIEFORCBhY3RpdmUgPSAxIiwgJHVuaWRhZCApICk7CiAgICBpZiAoICEgJHUgKSB7CiAgICAgICAgcmV0dXJuIGFycmF5KCAnZXJyb3InID0+ICdFc2UgZGVwYXJ0YW1lbnRvIG5vIGV4aXN0ZS4nICk7CiAgICB9CgogICAgJHRyICAgICA9ICR3cGRiLT5wcmVmaXggLiAncG9ydGFsX3Jlc2VydmF0aW9ucyc7CiAgICAkY29kaWdvID0gJ0hWSy0nIC4gc3RydG91cHBlciggd3BfZ2VuZXJhdGVfcGFzc3dvcmQoIDgsIGZhbHNlLCBmYWxzZSApICk7CgogICAgLy8gVW4gY29kaWdvIHJlcGV0aWRvIGRlamFyaWEgYSBkb3MgaHVlc3BlZGVzIGVudHJhbmRvIGEgbGEgbWlzbWEgcGFudGFsbGEuCiAgICAvLyBDb24gb2NobyBjYXJhY3RlcmVzIGVzIGltcHJvYmFibGUsIHBlcm8gc2FsZSBncmF0aXMgY29tcHJvYmFybG8uCiAgICAkdnVlbHRhcyA9IDA7CiAgICB3aGlsZSAoICR3cGRiLT5nZXRfdmFyKCAkd3BkYi0+cHJlcGFyZSggIlNFTEVDVCBpZCBGUk9NICR0ciBXSEVSRSByZXNlcnZhdGlvbl9jb2RlID0gJXMiLCAkY29kaWdvICkgKSApIHsKICAgICAgICAkY29kaWdvID0gJ0hWSy0nIC4gc3RydG91cHBlciggd3BfZ2VuZXJhdGVfcGFzc3dvcmQoIDgsIGZhbHNlLCBmYWxzZSApICk7CiAgICAgICAgaWYgKCArKyR2dWVsdGFzID4gNSApIHsKICAgICAgICAgICAgcmV0dXJuIGFycmF5KCAnZXJyb3InID0+ICdObyBzZSBwdWRvIGdlbmVyYXIgdW4gY29kaWdvIGxpYnJlLiBJbnRlbnRhbG8gZGUgbnVldm8uJyApOwogICAgICAgIH0KICAgIH0KCiAgICAkd3BkYi0+aW5zZXJ0KCAkdHIsIGFycmF5KAogICAgICAgICd1bml0X2lkJyAgICAgICAgICA9PiAoaW50KSAkdS0+aWQsCiAgICAgICAgJ2d1ZXN0X25hbWUnICAgICAgID0+ICRub21icmUsCiAgICAgICAgJ2d1ZXN0X2xhc3RfbmFtZScgID0+ICRhcGVsbGlkbywKICAgICAgICAnZ3Vlc3RfZW1haWwnICAgICAgPT4gJGVtYWlsID8gJGVtYWlsIDogbnVsbCwKICAgICAgICAnZ3Vlc3RfcGhvbmUnICAgICAgPT4gJHRlbGVmb25vID8gJHRlbGVmb25vIDogbnVsbCwKICAgICAgICAncmVzZXJ2YXRpb25fY29kZScgPT4gJGNvZGlnbywKICAgICAgICAnY2hlY2tfaW4nICAgICAgICAgPT4gJGluLAogICAgICAgICdjaGVja19vdXQnICAgICAgICA9PiAkb3V0LAogICAgICAgICdndWVzdHNfY291bnQnICAgICA9PiAkcGF4LAogICAgICAgICdzb3VyY2UnICAgICAgICAgICA9PiAkb3JpZ2VuID8gJG9yaWdlbiA6ICdkaXJlY3QnLAogICAgICAgICdzdGF0dXMnICAgICAgICAgICA9PiAnY29uZmlybWVkJywKICAgICkgKTsKCiAgICAkaWQgPSAoaW50KSAkd3BkYi0+aW5zZXJ0X2lkOwogICAgaWYgKCAhICRpZCApIHsKICAgICAgICByZXR1cm4gYXJyYXkoICdlcnJvcicgPT4gJ05vIHNlIHB1ZG8gZ3VhcmRhciBsYSByZXNlcnZhLicgKTsKICAgIH0KCiAgICAkciA9ICR3cGRiLT5nZXRfcm93KCAkd3BkYi0+cHJlcGFyZSgKICAgICAgICAiU0VMRUNUIHIuKiwgdS5uYW1lIEFTIHVuaXRfbmFtZSBGUk9NICR0ciByIExFRlQgSk9JTiAkdHUgdSBPTiByLnVuaXRfaWQgPSB1LmlkCiAgICAgICAgIFdIRVJFIHIuaWQgPSAlZCIsICRpZCApICk7CgogICAgLy8gUXVlIHNlIHBpc2VuIGRvcyByZXNlcnZhcyBlbiBlbCBtaXNtbyBkZXBhcnRhbWVudG8gY2FzaSBzaWVtcHJlIGVzIHF1ZQogICAgLy8gc2UgY2FyZ28gZG9zIHZlY2VzIGxhIG1pc21hLiBTZSBhdmlzYSwgbm8gc2UgaW1waWRlOiBwdWVkZSBzZXIgbGVnaXRpbW8uCiAgICAkY2hvcXVlID0gJHdwZGItPmdldF9yZXN1bHRzKCAkd3BkYi0+cHJlcGFyZSgKICAgICAgICAiU0VMRUNUIHJlc2VydmF0aW9uX2NvZGUsIGd1ZXN0X25hbWUsIGd1ZXN0X2xhc3RfbmFtZSwgY2hlY2tfaW4sIGNoZWNrX291dAogICAgICAgICBGUk9NICR0ciBXSEVSRSB1bml0X2lkID0gJWQgQU5EIGlkIDw+ICVkIEFORCBzdGF0dXMgIT0gJ2NhbmNlbGxlZCcKICAgICAgICAgICBBTkQgY2hlY2tfaW4gPCAlcyBBTkQgY2hlY2tfb3V0ID4gJXMiLAogICAgICAgIChpbnQpICR1LT5pZCwgJGlkLCAkb3V0LCAkaW4gKSApOwoKICAgIHJldHVybiBhcnJheSggJ3Jlc2VydmEnID0+ICRyLCAnY2hvcXVlJyA9PiAkY2hvcXVlICk7Cn0KCi8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0KICogNC4gTGEgcGFudGFsbGEKICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwoKZnVuY3Rpb24gaHZrYWNfZmVjaGFfbGFyZ2EoICRpc28gKSB7CiAgICAkbSA9IGFycmF5KCAxID0+ICdlbmUnLCAnZmViJywgJ21hcicsICdhYnInLCAnbWF5JywgJ2p1bicsICdqdWwnLCAnYWdvJywgJ3NlcCcsICdvY3QnLCAnbm92JywgJ2RpYycgKTsKICAgICR0ID0gc3RydG90aW1lKCAkaXNvICk7CiAgICByZXR1cm4gJHQgPyBnbWRhdGUoICdqJywgJHQgKSAuICcgJyAuICRtWyAoaW50KSBnbWRhdGUoICduJywgJHQgKSBdIDogJGlzbzsKfQoKZnVuY3Rpb24gaHZrYWNfY3NzKCkgewogICAgcmV0dXJuICcKOnJvb3R7LS1pbms6IzE2MTgyZDstLW11dGVkOiM2YjcyODA7LS1saW5lOiNlN2U5ZjI7LS1iZzojZjZmN2ZiOy0tcGluazojZTg0MzkzOy0tZ3JlZW46IzBiOGM1OH0KKntib3gtc2l6aW5nOmJvcmRlci1ib3h9CmJvZHl7bWFyZ2luOjA7YmFja2dyb3VuZDp2YXIoLS1iZyk7Y29sb3I6dmFyKC0taW5rKTtsaW5lLWhlaWdodDoxLjY7CmZvbnQtZmFtaWx5OkludGVyLC1hcHBsZS1zeXN0ZW0sQmxpbmtNYWNTeXN0ZW1Gb250LCJTZWdvZSBVSSIsUm9ib3RvLHNhbnMtc2VyaWY7LXdlYmtpdC1mb250LXNtb290aGluZzphbnRpYWxpYXNlZH0KLmhke2JhY2tncm91bmQ6I2ZmZjtib3JkZXItYm90dG9tOjFweCBzb2xpZCB2YXIoLS1saW5lKTtwYWRkaW5nOjE0cHggMThweDtkaXNwbGF5OmZsZXg7CmFsaWduLWl0ZW1zOmNlbnRlcjtnYXA6MTJweDtwb3NpdGlvbjpzdGlja3k7dG9wOjA7ei1pbmRleDo1fQouaGQgYntmb250LXNpemU6MS4wNXJlbTtsZXR0ZXItc3BhY2luZzotLjAyZW19Ci5oZCBzcGFue2ZvbnQtc2l6ZTouN3JlbTtmb250LXdlaWdodDo4MDA7bGV0dGVyLXNwYWNpbmc6LjA4ZW07dGV4dC10cmFuc2Zvcm06dXBwZXJjYXNlOwpjb2xvcjp2YXIoLS1waW5rKTtiYWNrZ3JvdW5kOiNmZGVhZjQ7cGFkZGluZzo0cHggMTBweDtib3JkZXItcmFkaXVzOjk5OXB4fQoud3JhcHttYXgtd2lkdGg6MTA4MHB4O21hcmdpbjowIGF1dG87cGFkZGluZzoxOHB4fQouZ3JpZHtkaXNwbGF5OmdyaWQ7Z3JpZC10ZW1wbGF0ZS1jb2x1bW5zOjFmciAxZnI7Z2FwOjE4cHg7YWxpZ24taXRlbXM6c3RhcnR9CkBtZWRpYShtYXgtd2lkdGg6ODIwcHgpey5ncmlke2dyaWQtdGVtcGxhdGUtY29sdW1uczoxZnJ9fQouY2FqYXtiYWNrZ3JvdW5kOiNmZmY7Ym9yZGVyOjFweCBzb2xpZCB2YXIoLS1saW5lKTtib3JkZXItcmFkaXVzOjE2cHg7cGFkZGluZzoxOHB4O21hcmdpbi1ib3R0b206MThweH0KLmNhamEgaDJ7bWFyZ2luOjAgMCA0cHg7Zm9udC1zaXplOjEuMDVyZW19Ci5jYWphIHAuaGludHttYXJnaW46MCAwIDE0cHg7Y29sb3I6dmFyKC0tbXV0ZWQpO2ZvbnQtc2l6ZTouODVyZW19CmxhYmVse2Rpc3BsYXk6YmxvY2s7Zm9udC1zaXplOi43MnJlbTtmb250LXdlaWdodDo4MDA7bGV0dGVyLXNwYWNpbmc6LjA2ZW07dGV4dC10cmFuc2Zvcm06dXBwZXJjYXNlOwpjb2xvcjp2YXIoLS1tdXRlZCk7bWFyZ2luOjAgMCA1cHh9CmlucHV0LHNlbGVjdHt3aWR0aDoxMDAlO3BhZGRpbmc6MTFweCAxMnB4O2JvcmRlcjoxcHggc29saWQgdmFyKC0tbGluZSk7Ym9yZGVyLXJhZGl1czoxMXB4Owpmb250OmluaGVyaXQ7Zm9udC1zaXplOi45NXJlbTtiYWNrZ3JvdW5kOiNmZmY7Y29sb3I6dmFyKC0taW5rKX0KaW5wdXQ6Zm9jdXMsc2VsZWN0OmZvY3Vze291dGxpbmU6MnB4IHNvbGlkICNmNmMzZGQ7Ym9yZGVyLWNvbG9yOnZhcigtLXBpbmspfQouZnttYXJnaW4tYm90dG9tOjEzcHh9Ci5mMntkaXNwbGF5OmdyaWQ7Z3JpZC10ZW1wbGF0ZS1jb2x1bW5zOjFmciAxZnI7Z2FwOjEycHh9Ci5jdGF7ZGlzcGxheTpibG9jazt3aWR0aDoxMDAlO21hcmdpbi10b3A6NnB4O3BhZGRpbmc6MTRweCAyMHB4O2JvcmRlcjowO2JvcmRlci1yYWRpdXM6OTk5cHg7CmJhY2tncm91bmQ6bGluZWFyLWdyYWRpZW50KDEzNWRlZywjZTg0MzkzLCNkNjMzODQpO2NvbG9yOiNmZmY7Zm9udDppbmhlcml0O2ZvbnQtc2l6ZToxcmVtOwpmb250LXdlaWdodDo4MDA7Y3Vyc29yOnBvaW50ZXJ9Ci5jdGE6aG92ZXJ7ZmlsdGVyOmJyaWdodG5lc3MoMS4wNil9Ci5hdmlzb3tiYWNrZ3JvdW5kOiNmZmY4ZWU7Ym9yZGVyOjFweCBzb2xpZCAjZjBkY2JkO2NvbG9yOiM3YTVhMWU7Ym9yZGVyLXJhZGl1czoxNHB4OwpwYWRkaW5nOjEycHggMTRweDtmb250LXNpemU6LjgycmVtO21hcmdpbi1ib3R0b206MThweH0KLm1hbG97YmFja2dyb3VuZDojZmRlY2VhO2JvcmRlcjoxcHggc29saWQgI2Y1YzJiZDtjb2xvcjojOGMyZjIyO2JvcmRlci1yYWRpdXM6MTRweDsKcGFkZGluZzoxMnB4IDE0cHg7Zm9udC1zaXplOi44NnJlbTttYXJnaW4tYm90dG9tOjE0cHh9Ci5iaWVue2JhY2tncm91bmQ6I2U4ZjdlZjtib3JkZXI6MXB4IHNvbGlkICNiZGU1Y2Y7Ym9yZGVyLXJhZGl1czoxNnB4O3BhZGRpbmc6MThweDttYXJnaW4tYm90dG9tOjE4cHh9Ci5iaWVuIGgye21hcmdpbjowIDAgM3B4O2NvbG9yOnZhcigtLWdyZWVuKTtmb250LXNpemU6MS4wNXJlbX0KLmJpZW4gLnF1aWVue2ZvbnQtc2l6ZTouOXJlbTtjb2xvcjojM2M2YTU1O21hcmdpbjowIDAgMTRweH0KLmRhdG97bWFyZ2luLWJvdHRvbToxMnB4fQouZGF0byBsYWJlbHttYXJnaW4tYm90dG9tOjRweH0KLmZpbGF7ZGlzcGxheTpmbGV4O2dhcDo4cHh9Ci5maWxhIGlucHV0e2ZvbnQtZmFtaWx5OnVpLW1vbm9zcGFjZSxTRk1vbm8tUmVndWxhcixNZW5sbyxtb25vc3BhY2U7Zm9udC1zaXplOi44MnJlbTtiYWNrZ3JvdW5kOiNmZmZ9Ci5jcHtmbGV4OjAgMCBhdXRvO3BhZGRpbmc6MCAxNnB4O2JvcmRlcjowO2JvcmRlci1yYWRpdXM6MTFweDtiYWNrZ3JvdW5kOnZhcigtLWluayk7Y29sb3I6I2ZmZjsKZm9udDppbmhlcml0O2ZvbnQtc2l6ZTouODJyZW07Zm9udC13ZWlnaHQ6NzAwO2N1cnNvcjpwb2ludGVyfQouY3Aub2t7YmFja2dyb3VuZDp2YXIoLS1ncmVlbil9CnRleHRhcmVhe3dpZHRoOjEwMCU7bWluLWhlaWdodDoxMjBweDtwYWRkaW5nOjExcHggMTJweDtib3JkZXI6MXB4IHNvbGlkIHZhcigtLWxpbmUpOwpib3JkZXItcmFkaXVzOjExcHg7Zm9udDppbmhlcml0O2ZvbnQtc2l6ZTouODVyZW07cmVzaXplOnZlcnRpY2FsO2JhY2tncm91bmQ6I2ZmZjtjb2xvcjp2YXIoLS1pbmspfQp0YWJsZXt3aWR0aDoxMDAlO2JvcmRlci1jb2xsYXBzZTpjb2xsYXBzZTtmb250LXNpemU6Ljg0cmVtfQp0aHt0ZXh0LWFsaWduOmxlZnQ7Zm9udC1zaXplOi42OHJlbTtsZXR0ZXItc3BhY2luZzouMDZlbTt0ZXh0LXRyYW5zZm9ybTp1cHBlcmNhc2U7Y29sb3I6dmFyKC0tbXV0ZWQpOwpwYWRkaW5nOjAgOHB4IDhweCAwO2ZvbnQtd2VpZ2h0OjgwMH0KdGR7cGFkZGluZzo5cHggOHB4IDlweCAwO2JvcmRlci10b3A6MXB4IHNvbGlkIHZhcigtLWxpbmUpO3ZlcnRpY2FsLWFsaWduOm1pZGRsZX0KdGQuY29ke2ZvbnQtZmFtaWx5OnVpLW1vbm9zcGFjZSxTRk1vbm8tUmVndWxhcixNZW5sbyxtb25vc3BhY2U7Zm9udC1zaXplOi43OHJlbX0KLm1pbml7cGFkZGluZzo2cHggMTJweDtib3JkZXI6MXB4IHNvbGlkIHZhcigtLWxpbmUpO2JvcmRlci1yYWRpdXM6OTk5cHg7YmFja2dyb3VuZDojZmZmOwpmb250OmluaGVyaXQ7Zm9udC1zaXplOi43NnJlbTtmb250LXdlaWdodDo3MDA7Y3Vyc29yOnBvaW50ZXI7d2hpdGUtc3BhY2U6bm93cmFwfQoubWluaS5va3tiYWNrZ3JvdW5kOnZhcigtLWdyZWVuKTtjb2xvcjojZmZmO2JvcmRlci1jb2xvcjp2YXIoLS1ncmVlbil9Ci52YWNpb3tjb2xvcjp2YXIoLS1tdXRlZCk7Zm9udC1zaXplOi44NnJlbTttYXJnaW46MH0KJzsKfQoKZnVuY3Rpb24gaHZrYWNfY2FiZWNlcmEoICR0aXR1bG8gKSB7CiAgICA/PjwhRE9DVFlQRSBodG1sPgo8aHRtbCBsYW5nPSJlcyI+PGhlYWQ+CjxtZXRhIGNoYXJzZXQ9InV0Zi04Ij4KPG1ldGEgbmFtZT0idmlld3BvcnQiIGNvbnRlbnQ9IndpZHRoPWRldmljZS13aWR0aCxpbml0aWFsLXNjYWxlPTEiPgo8bWV0YSBuYW1lPSJyb2JvdHMiIGNvbnRlbnQ9Im5vaW5kZXgsbm9mb2xsb3ciPgo8dGl0bGU+PD9waHAgZWNobyBlc2NfaHRtbCggJHRpdHVsbyApOyA/PjwvdGl0bGU+CjxsaW5rIHJlbD0icHJlY29ubmVjdCIgaHJlZj0iaHR0cHM6Ly9mb250cy5nb29nbGVhcGlzLmNvbSI+CjxsaW5rIHJlbD0icHJlY29ubmVjdCIgaHJlZj0iaHR0cHM6Ly9mb250cy5nc3RhdGljLmNvbSIgY3Jvc3NvcmlnaW4+CjxsaW5rIGhyZWY9Imh0dHBzOi8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzMj9mYW1pbHk9SW50ZXI6d2dodEA0MDA7NjAwOzcwMDs4MDAmZGlzcGxheT1zd2FwIiByZWw9InN0eWxlc2hlZXQiPgo8c3R5bGU+PD9waHAgZWNobyBodmthY19jc3MoKTsgPz48L3N0eWxlPgo8L2hlYWQ+PGJvZHk+CjxkaXYgY2xhc3M9ImhkIj48Yj5IT01WVUs8L2I+PHNwYW4+QWNjZXNvIGFsIHBvcnRhbDwvc3Bhbj48L2Rpdj4KPG1haW4gY2xhc3M9IndyYXAiPgogICAgPD9waHAKfQoKZnVuY3Rpb24gaHZrYWNfdmlzdGFfc2luX3Blcm1pc28oKSB7CiAgICBodmthY19jYWJlY2VyYSggJ1NpbiBwZXJtaXNvIMK3IEhPTVZVSycgKTsKICAgID8+CiAgICA8ZGl2IGNsYXNzPSJjYWphIj4KICAgICAgPGgyPkVzdGEgcGFudGFsbGEgZXMgcHJpdmFkYTwvaDI+CiAgICAgIDxwIGNsYXNzPSJoaW50Ij5IYWNlIGZhbHRhIGVsIGVubGFjZSBjb24gbGEgY2xhdmUuIFNpIGxvIHBlcmRpc3RlLCBzZSB2dWVsdmUgYQogICAgICBnZW5lcmFyIGRlc2RlIGxhIHRlcm1pbmFsIGNvbiA8Y29kZT5waHAgaHZrcC1hY2Nlc28ucGhwIGNsYXZlPC9jb2RlPi48L3A+CiAgICA8L2Rpdj4KICAgIDwvbWFpbj48L2JvZHk+PC9odG1sPgogICAgPD9waHAKfQoKZnVuY3Rpb24gaHZrYWNfdmlzdGEoICRoZWNobyApIHsKICAgICR1bmlkYWRlcyA9IGh2a2FjX3VuaWRhZGVzKCk7CiAgICAkaG95ICAgICAgPSBjdXJyZW50X3RpbWUoICdZLW0tZCcgKTsKCiAgICBodmthY19jYWJlY2VyYSggJ0FjY2VzbyBhbCBwb3J0YWwgwrcgSE9NVlVLJyApOwogICAgPz4KICAgIDxkaXYgY2xhc3M9ImF2aXNvIj4KICAgICAgPGI+RXN0YSBwYW50YWxsYSBlcyBzw7NsbyB0dXlhLjwvYj4gUXVpZW4gdGVuZ2EgZXN0YSBkaXJlY2Npw7NuIHB1ZWRlIGNyZWFyIGFjY2Vzb3MgYWwgcG9ydGFsLjxicj4KICAgICAgWSBvam8gY29uIGxvIGRlIGFiYWpvOiA8Yj5jYWRhIGVubGFjZSBlcyBsYSBsbGF2ZSBkZSB1bmEgcmVzZXJ2YSBjb25jcmV0YTwvYj4uIFNpIHJlZW52w61hcyBlbCBkZQogICAgICB1biBodcOpc3BlZCBhIG90cm8sIGVsIHNlZ3VuZG8gZW50cmEgYSBsYSByZXNlcnZhIGRlbCBwcmltZXJvLgogICAgPC9kaXY+CgogICAgPD9waHAgaWYgKCAkaGVjaG8gJiYgISBlbXB0eSggJGhlY2hvWydlcnJvciddICkgKSA6ID8+CiAgICAgIDxkaXYgY2xhc3M9Im1hbG8iPjw/cGhwIGVjaG8gZXNjX2h0bWwoICRoZWNob1snZXJyb3InXSApOyA/PjwvZGl2PgogICAgPD9waHAgZW5kaWY7ID8+CgogICAgPD9waHAKICAgIGlmICggJGhlY2hvICYmICEgZW1wdHkoICRoZWNob1sncmVzZXJ2YSddICkgKSA6CiAgICAgICAgJHIgICA9ICRoZWNob1sncmVzZXJ2YSddOwogICAgICAgICR1cmwgPSBodmthY19lbmxhY2UoIChpbnQpICRyLT5pZCApOwogICAgICAgICRtc2cgPSAnSG9sYSAnIC4gJHItPmd1ZXN0X25hbWUgLiAiIPCfkYtcblxuIgogICAgICAgICAgICAuICJUZSBkZWpvIHR1IGFjY2VzbyBhbCBwb3J0YWwgZGUgIiAuICRyLT51bml0X25hbWUgLiAiLCBkb25kZSBlc3TDoSB0b2RvIGxvIHF1ZSAiCiAgICAgICAgICAgIC4gIm5lY2VzaXRhcyBwYXJhIHR1IGVzdGFkw61hIGRlbCAiIC4gaHZrYWNfZmVjaGFfbGFyZ2EoICRyLT5jaGVja19pbiApIC4gJyBhbCAnCiAgICAgICAgICAgIC4gaHZrYWNfZmVjaGFfbGFyZ2EoICRyLT5jaGVja19vdXQgKSAuICI6XG5cbiIgLiAkdXJsIC4gIlxuXG4iCiAgICAgICAgICAgIC4gIlNlIGFicmUgZGlyZWN0bywgbm8gdGllbmVzIHF1ZSByZWdpc3RyYXJ0ZSBuaSBwb25lciBjbGF2ZXMuXG4iCiAgICAgICAgICAgIC4gIkN1YWxxdWllciBjb3NhIG1lIGVzY3JpYmVzIHBvciBhY8OhIPCfmYwiOwogICAgICAgID8+CiAgICAgIDxkaXYgY2xhc3M9ImJpZW4iPgogICAgICAgIDxoMj5BY2Nlc28gY3JlYWRvPC9oMj4KICAgICAgICA8cCBjbGFzcz0icXVpZW4iPjw/cGhwIGVjaG8gZXNjX2h0bWwoCiAgICAgICAgICAgICRyLT5ndWVzdF9uYW1lIC4gJyAnIC4gJHItPmd1ZXN0X2xhc3RfbmFtZSAuICcgwrcgJyAuICRyLT51bml0X25hbWUgLiAnIMK3ICcKICAgICAgICAgICAgLiBodmthY19mZWNoYV9sYXJnYSggJHItPmNoZWNrX2luICkgLiAnIGFsICcgLiBodmthY19mZWNoYV9sYXJnYSggJHItPmNoZWNrX291dCApCiAgICAgICAgICAgIC4gJyDCtyAnIC4gKGludCkgJHItPmd1ZXN0c19jb3VudCAuICcgaHXDqXNwZWRlcycgKTsgPz48L3A+CgogICAgICAgIDxkaXYgY2xhc3M9ImRhdG8iPgogICAgICAgICAgPGxhYmVsPkVubGFjZSBwYXJhIGVsIGh1w6lzcGVkIOKAlCBtw6FuZGFsZSBlc3RlPC9sYWJlbD4KICAgICAgICAgIDxkaXYgY2xhc3M9ImZpbGEiPgogICAgICAgICAgICA8aW5wdXQgdHlwZT0idGV4dCIgaWQ9ImFjVXJsIiByZWFkb25seSB2YWx1ZT0iPD9waHAgZWNobyBlc2NfYXR0ciggJHVybCApOyA/PiI+CiAgICAgICAgICAgIDxidXR0b24gdHlwZT0iYnV0dG9uIiBjbGFzcz0iY3AiIGRhdGEtY3A9ImFjVXJsIj5Db3BpYXI8L2J1dHRvbj4KICAgICAgICAgIDwvZGl2PgogICAgICAgIDwvZGl2PgoKICAgICAgICA8ZGl2IGNsYXNzPSJkYXRvIj4KICAgICAgICAgIDxsYWJlbD5TdSBjw7NkaWdvLCBwb3Igc2kgcHJlZmllcmUgZXNjcmliaXJsbzwvbGFiZWw+CiAgICAgICAgICA8ZGl2IGNsYXNzPSJmaWxhIj4KICAgICAgICAgICAgPGlucHV0IHR5cGU9InRleHQiIGlkPSJhY0NvZCIgcmVhZG9ubHkgdmFsdWU9Ijw/cGhwIGVjaG8gZXNjX2F0dHIoICRyLT5yZXNlcnZhdGlvbl9jb2RlICk7ID8+Ij4KICAgICAgICAgICAgPGJ1dHRvbiB0eXBlPSJidXR0b24iIGNsYXNzPSJjcCIgZGF0YS1jcD0iYWNDb2QiPkNvcGlhcjwvYnV0dG9uPgogICAgICAgICAgPC9kaXY+CiAgICAgICAgPC9kaXY+CgogICAgICAgIDxkaXYgY2xhc3M9ImRhdG8iPgogICAgICAgICAgPGxhYmVsPk1lbnNhamUgbGlzdG8gcGFyYSBXaGF0c0FwcDwvbGFiZWw+CiAgICAgICAgICA8dGV4dGFyZWEgaWQ9ImFjTXNnIiByZWFkb25seT48P3BocCBlY2hvIGVzY190ZXh0YXJlYSggJG1zZyApOyA/PjwvdGV4dGFyZWE+CiAgICAgICAgICA8ZGl2IGNsYXNzPSJmaWxhIiBzdHlsZT0ibWFyZ2luLXRvcDo4cHgiPgogICAgICAgICAgICA8YnV0dG9uIHR5cGU9ImJ1dHRvbiIgY2xhc3M9ImNwIiBkYXRhLWNwPSJhY01zZyIgc3R5bGU9ImZsZXg6MSI+Q29waWFyIGVsIG1lbnNhamU8L2J1dHRvbj4KICAgICAgICAgIDwvZGl2PgogICAgICAgIDwvZGl2PgogICAgICA8L2Rpdj4KCiAgICAgIDw/cGhwIGlmICggISBlbXB0eSggJGhlY2hvWydjaG9xdWUnXSApICkgOiA/PgogICAgICAgIDxkaXYgY2xhc3M9ImF2aXNvIj48Yj5Pam86PC9iPiBlc2FzIGZlY2hhcyBzZSBjcnV6YW4gY29uCiAgICAgICAgICA8P3BocCBmb3JlYWNoICggJGhlY2hvWydjaG9xdWUnXSBhcyAkYyApIDogPz4KICAgICAgICAgICAgPD9waHAgZWNobyBlc2NfaHRtbCggJGMtPmd1ZXN0X25hbWUgLiAnICcgLiAkYy0+Z3Vlc3RfbGFzdF9uYW1lCiAgICAgICAgICAgICAgICAuICcgKCcgLiAkYy0+cmVzZXJ2YXRpb25fY29kZSAuICcsICcgLiAkYy0+Y2hlY2tfaW4gLiAnIGFsICcgLiAkYy0+Y2hlY2tfb3V0IC4gJyknICk7ID8+CiAgICAgICAgICA8P3BocCBlbmRmb3JlYWNoOyA/PgogICAgICAgICAgZW4gZWwgbWlzbW8gZGVwYXJ0YW1lbnRvLiBTZSBjcmXDsyBpZ3VhbCwgcGVybyBzaSBlcmEgbGEgbWlzbWEgcmVzZXJ2YSBjYXJnYWRhIGRvcyB2ZWNlcywKICAgICAgICAgIGNvbnZpZW5lIGJvcnJhciBsYSBxdWUgc29icmUuPC9kaXY+CiAgICAgIDw/cGhwIGVuZGlmOyA/PgogICAgPD9waHAgZW5kaWY7ID8+CgogICAgPGRpdiBjbGFzcz0iZ3JpZCI+CiAgICAgIDxkaXY+CiAgICAgICAgPGZvcm0gY2xhc3M9ImNhamEiIG1ldGhvZD0icG9zdCI+CiAgICAgICAgICA8aDI+TnVldm8gYWNjZXNvPC9oMj4KICAgICAgICAgIDxwIGNsYXNzPSJoaW50Ij5QYXJhIGVsIGh1w6lzcGVkIHF1ZSByZXNlcnbDsyBkaXJlY3RvLiBMb3MgZGUgQWlyYm5iIHlhIGVudHJhbiBjb24gc3UgcHJvcGlvCiAgICAgICAgICBjw7NkaWdvLCBubyBoYWNlIGZhbHRhIGNyZWFybGVzIG5hZGEuPC9wPgoKICAgICAgICAgIDw/cGhwIHdwX25vbmNlX2ZpZWxkKCAnaHZrYWNfY3JlYXInLCAnaHZrYWNfbm9uY2UnICk7ID8+CgogICAgICAgICAgPGRpdiBjbGFzcz0iZiI+PGxhYmVsPkRlcGFydGFtZW50bzwvbGFiZWw+CiAgICAgICAgICAgIDxzZWxlY3QgbmFtZT0idW5pZGFkIiByZXF1aXJlZD4KICAgICAgICAgICAgICA8P3BocCBmb3JlYWNoICggJHVuaWRhZGVzIGFzICR1ICkgOiA/PgogICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT0iPD9waHAgZWNobyAoaW50KSAkdS0+aWQ7ID8+Ij48P3BocCBlY2hvIGVzY19odG1sKCAkdS0+bmFtZSApOyA/Pjwvb3B0aW9uPgogICAgICAgICAgICAgIDw/cGhwIGVuZGZvcmVhY2g7ID8+CiAgICAgICAgICAgIDwvc2VsZWN0PgogICAgICAgICAgPC9kaXY+CgogICAgICAgICAgPGRpdiBjbGFzcz0iZiBmMiI+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPk5vbWJyZTwvbGFiZWw+PGlucHV0IHR5cGU9InRleHQiIG5hbWU9Im5vbWJyZSIgcmVxdWlyZWQ+PC9kaXY+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPkFwZWxsaWRvPC9sYWJlbD48aW5wdXQgdHlwZT0idGV4dCIgbmFtZT0iYXBlbGxpZG8iIHJlcXVpcmVkPjwvZGl2PgogICAgICAgICAgPC9kaXY+CgogICAgICAgICAgPGRpdiBjbGFzcz0iZiBmMiI+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPkxsZWdhZGE8L2xhYmVsPgogICAgICAgICAgICAgIDxpbnB1dCB0eXBlPSJkYXRlIiBuYW1lPSJpbiIgcmVxdWlyZWQgbWluPSI8P3BocCBlY2hvIGVzY19hdHRyKCAkaG95ICk7ID8+Ij48L2Rpdj4KICAgICAgICAgICAgPGRpdj48bGFiZWw+U2FsaWRhPC9sYWJlbD4KICAgICAgICAgICAgICA8aW5wdXQgdHlwZT0iZGF0ZSIgbmFtZT0ib3V0IiByZXF1aXJlZCBtaW49Ijw/cGhwIGVjaG8gZXNjX2F0dHIoICRob3kgKTsgPz4iPjwvZGl2PgogICAgICAgICAgPC9kaXY+CgogICAgICAgICAgPGRpdiBjbGFzcz0iZiBmMiI+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPkh1w6lzcGVkZXM8L2xhYmVsPgogICAgICAgICAgICAgIDxzZWxlY3QgbmFtZT0icGF4Ij48P3BocCBmb3IgKCAkaSA9IDE7ICRpIDw9IDEwOyAkaSsrICkgewogICAgICAgICAgICAgICAgICBwcmludGYoICc8b3B0aW9uIHZhbHVlPSIlMSRkIiUyJHM+JTEkZDwvb3B0aW9uPicsICRpLCAyID09PSAkaSA/ICcgc2VsZWN0ZWQnIDogJycgKTsKICAgICAgICAgICAgICB9ID8+PC9zZWxlY3Q+PC9kaXY+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPkPDs21vIGxsZWfDszwvbGFiZWw+CiAgICAgICAgICAgICAgPHNlbGVjdCBuYW1lPSJvcmlnZW4iPgogICAgICAgICAgICAgICAgPG9wdGlvbiB2YWx1ZT0iZGlyZWN0Ij5EaXJlY3RvPC9vcHRpb24+CiAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPSJ3aGF0c2FwcCI+V2hhdHNBcHA8L29wdGlvbj4KICAgICAgICAgICAgICAgIDxvcHRpb24gdmFsdWU9ImFpcmJuYiI+QWlyYm5iPC9vcHRpb24+CiAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPSJib29raW5nIj5Cb29raW5nPC9vcHRpb24+CiAgICAgICAgICAgICAgICA8b3B0aW9uIHZhbHVlPSJhZ2VuY3kiPkFnZW5jaWE8L29wdGlvbj4KICAgICAgICAgICAgICA8L3NlbGVjdD48L2Rpdj4KICAgICAgICAgIDwvZGl2PgoKICAgICAgICAgIDxkaXYgY2xhc3M9ImYgZjIiPgogICAgICAgICAgICA8ZGl2PjxsYWJlbD5Db3JyZW8gPHNwYW4gc3R5bGU9InRleHQtdHJhbnNmb3JtOm5vbmU7Zm9udC13ZWlnaHQ6NjAwIj4ob3BjaW9uYWwpPC9zcGFuPjwvbGFiZWw+CiAgICAgICAgICAgICAgPGlucHV0IHR5cGU9ImVtYWlsIiBuYW1lPSJlbWFpbCI+PC9kaXY+CiAgICAgICAgICAgIDxkaXY+PGxhYmVsPlRlbMOpZm9ubyA8c3BhbiBzdHlsZT0idGV4dC10cmFuc2Zvcm06bm9uZTtmb250LXdlaWdodDo2MDAiPihvcGNpb25hbCk8L3NwYW4+PC9sYWJlbD4KICAgICAgICAgICAgICA8aW5wdXQgdHlwZT0idGV4dCIgbmFtZT0idGVsZWZvbm8iPjwvZGl2PgogICAgICAgICAgPC9kaXY+CgogICAgICAgICAgPGJ1dHRvbiB0eXBlPSJzdWJtaXQiIGNsYXNzPSJjdGEiPkNyZWFyIGVsIGFjY2VzbzwvYnV0dG9uPgogICAgICAgIDwvZm9ybT4KICAgICAgPC9kaXY+CgogICAgICA8ZGl2PgogICAgICAgIDxkaXYgY2xhc3M9ImNhamEiPgogICAgICAgICAgPGgyPkVzdGFkw61hcyB2aWdlbnRlczwvaDI+CiAgICAgICAgICA8cCBjbGFzcz0iaGludCI+UGFyYSB2b2x2ZXIgYSBjb3BpYXIgdW4gZW5sYWNlIHNpbiBjcmVhciBuYWRhLjwvcD4KICAgICAgICAgIDw/cGhwICR2aWcgPSBodmthY192aWdlbnRlcygpOyA/PgogICAgICAgICAgPD9waHAgaWYgKCAhICR2aWcgKSA6ID8+CiAgICAgICAgICAgIDxwIGNsYXNzPSJ2YWNpbyI+Tm8gaGF5IG5pbmd1bmEgcG9yIGFob3JhLjwvcD4KICAgICAgICAgIDw/cGhwIGVsc2UgOiA/PgogICAgICAgICAgPHRhYmxlPgogICAgICAgICAgICA8dHI+PHRoPkh1w6lzcGVkPC90aD48dGg+RMOzbmRlIHkgY3XDoW5kbzwvdGg+PHRoPjwvdGg+PC90cj4KICAgICAgICAgICAgPD9waHAgZm9yZWFjaCAoICR2aWcgYXMgJHYgKSA6ICR2dSA9IGh2a2FjX2VubGFjZSggKGludCkgJHYtPmlkICk7ID8+CiAgICAgICAgICAgICAgPHRyPgogICAgICAgICAgICAgICAgPHRkPjw/cGhwIGVjaG8gZXNjX2h0bWwoICR2LT5ndWVzdF9uYW1lIC4gJyAnIC4gJHYtPmd1ZXN0X2xhc3RfbmFtZSApOyA/Pjxicj4KICAgICAgICAgICAgICAgICAgPHNwYW4gY2xhc3M9ImNvZCIgc3R5bGU9ImNvbG9yOiM2YjcyODAiPjw/cGhwIGVjaG8gZXNjX2h0bWwoICR2LT5yZXNlcnZhdGlvbl9jb2RlICk7ID8+PC9zcGFuPjwvdGQ+CiAgICAgICAgICAgICAgICA8dGQ+PD9waHAgZWNobyBlc2NfaHRtbCggJHYtPnVuaXRfbmFtZSApOyA/Pjxicj4KICAgICAgICAgICAgICAgICAgPHNwYW4gc3R5bGU9ImNvbG9yOiM2YjcyODAiPjw/cGhwIGVjaG8gZXNjX2h0bWwoCiAgICAgICAgICAgICAgICAgICAgaHZrYWNfZmVjaGFfbGFyZ2EoICR2LT5jaGVja19pbiApIC4gJyBhbCAnIC4gaHZrYWNfZmVjaGFfbGFyZ2EoICR2LT5jaGVja19vdXQgKSApOyA/Pjwvc3Bhbj48L3RkPgogICAgICAgICAgICAgICAgPHRkIHN0eWxlPSJ0ZXh0LWFsaWduOnJpZ2h0Ij4KICAgICAgICAgICAgICAgICAgPGlucHV0IHR5cGU9ImhpZGRlbiIgaWQ9InU8P3BocCBlY2hvIChpbnQpICR2LT5pZDsgPz4iIHZhbHVlPSI8P3BocCBlY2hvIGVzY19hdHRyKCAkdnUgKTsgPz4iPgogICAgICAgICAgICAgICAgICA8YnV0dG9uIHR5cGU9ImJ1dHRvbiIgY2xhc3M9Im1pbmkiIGRhdGEtY3A9InU8P3BocCBlY2hvIChpbnQpICR2LT5pZDsgPz4iPkNvcGlhciBlbmxhY2U8L2J1dHRvbj4KICAgICAgICAgICAgICAgIDwvdGQ+CiAgICAgICAgICAgICAgPC90cj4KICAgICAgICAgICAgPD9waHAgZW5kZm9yZWFjaDsgPz4KICAgICAgICAgIDwvdGFibGU+CiAgICAgICAgICA8P3BocCBlbmRpZjsgPz4KICAgICAgICA8L2Rpdj4KICAgICAgPC9kaXY+CiAgICA8L2Rpdj4KCiAgICA8c2NyaXB0PgogICAgZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lcignY2xpY2snLCBmdW5jdGlvbihlKXsKICAgICAgdmFyIGIgPSBlLnRhcmdldC5jbG9zZXN0KCdbZGF0YS1jcF0nKTsKICAgICAgaWYoIWIpIHJldHVybjsKICAgICAgdmFyIGVsID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoYi5nZXRBdHRyaWJ1dGUoJ2RhdGEtY3AnKSk7CiAgICAgIGlmKCFlbCkgcmV0dXJuOwogICAgICAvLyBleGVjQ29tbWFuZCBjb21vIHJlc3BhbGRvOiBlbCBwb3J0YXBhcGVsZXMgbW9kZXJubyBuZWNlc2l0YSBodHRwcyB5CiAgICAgIC8vIHBlcm1pc28sIHkgZXN0byBzZSBhYnJlIHRhbWJpZW4gZGVzZGUgZWwgdGVsZWZvbm8uCiAgICAgIHZhciB0ZXh0byA9IGVsLnZhbHVlOwogICAgICB2YXIgbGlzdG8gPSBmdW5jdGlvbigpewogICAgICAgIHZhciBhbnRlcyA9IGIudGV4dENvbnRlbnQ7CiAgICAgICAgYi50ZXh0Q29udGVudCA9ICdDb3BpYWRvJzsgYi5jbGFzc0xpc3QuYWRkKCdvaycpOwogICAgICAgIHNldFRpbWVvdXQoZnVuY3Rpb24oKXsgYi50ZXh0Q29udGVudCA9IGFudGVzOyBiLmNsYXNzTGlzdC5yZW1vdmUoJ29rJyk7IH0sIDE2MDApOwogICAgICB9OwogICAgICBpZiAobmF2aWdhdG9yLmNsaXBib2FyZCAmJiB3aW5kb3cuaXNTZWN1cmVDb250ZXh0KSB7CiAgICAgICAgbmF2aWdhdG9yLmNsaXBib2FyZC53cml0ZVRleHQodGV4dG8pLnRoZW4obGlzdG8sIGZ1bmN0aW9uKCl7IGVsLnNlbGVjdCgpOyBkb2N1bWVudC5leGVjQ29tbWFuZCgnY29weScpOyBsaXN0bygpOyB9KTsKICAgICAgfSBlbHNlIHsKICAgICAgICBlbC5zZWxlY3QoKTsgZWwuc2V0U2VsZWN0aW9uUmFuZ2UoMCwgOTk5OTkpOyBkb2N1bWVudC5leGVjQ29tbWFuZCgnY29weScpOyBsaXN0bygpOwogICAgICB9CiAgICB9KTsKICAgIDwvc2NyaXB0PgogICAgPC9tYWluPjwvYm9keT48L2h0bWw+CiAgICA8P3BocAp9Cg==';
$HVKAC_MD5  = '7cc2da01aec61d16645b2c756eba9c34';
$HVKAC_NAME = 'HOMVUK Acceso Portal';

printf( "HOMVUK %s · PHP %s (%s) · %s\n", basename( __FILE__ ), PHP_VERSION, PHP_SAPI, date( 'Y-m-d H:i' ) );

/**
 * Encuentra la instalacion de WordPress.
 *
 * Asumir que esta en la misma carpeta que el script fallaba en silencio -el
 * require moria antes de que nada se imprimiera- cuando WordPress no vive en
 * public_html. Se busca hacia arriba y un nivel hacia abajo, y se puede forzar
 * con WP_PATH=/ruta/a/wordpress php <script>.
 */
function hvkp_buscar_wordpress( $desde ) {
    $forzado = getenv( 'WP_PATH' );
    if ( $forzado ) {
        $forzado = rtrim( $forzado, '/' );
        foreach ( array( $forzado . '/wp-load.php', $forzado ) as $c ) {
            if ( is_file( $c ) && 'wp-load.php' === basename( $c ) ) {
                return $c;
            }
        }
        echo "[ERROR] WP_PATH=$forzado no contiene wp-load.php\n";
        exit( 1 );
    }

    $dir = $desde;
    for ( $i = 0; $i < 6; $i++ ) {
        if ( is_file( $dir . '/wp-load.php' ) ) {
            return $dir . '/wp-load.php';
        }
        $padre = dirname( $dir );
        if ( $padre === $dir ) {
            break;
        }
        $dir = $padre;
    }

    // Un nivel hacia abajo. Si aparece mas de una instalacion no se elige por
    // el agente: equivocarse de sitio aqui es escribir en la base que no era.
    foreach ( array( $desde, dirname( $desde ) ) as $base ) {
        $cand = array_values( array_filter( (array) glob( $base . '/*/wp-load.php' ), 'is_file' ) );
        if ( 1 === count( $cand ) ) {
            return $cand[0];
        }
        if ( count( $cand ) > 1 ) {
            echo "[ERROR] Hay varias instalaciones de WordPress cerca:\n";
            foreach ( $cand as $c ) {
                echo '          ' . dirname( $c ) . "\n";
            }
            echo "        Elige una:  WP_PATH=<la que sea> php " . basename( __FILE__ ) . "\n";
            exit( 1 );
        }
    }
    return '';
}

$hvkp_wp = hvkp_buscar_wordpress( __DIR__ );
if ( ! $hvkp_wp ) {
    echo "[ERROR] No encontre WordPress (wp-load.php) desde " . __DIR__ . "\n";
    echo "        Buscalo con:  find ~ -maxdepth 4 -name wp-load.php 2>/dev/null\n";
    echo "        Y luego:      WP_PATH=/la/carpeta/que/salga php " . basename( __FILE__ ) . "\n";
    exit( 1 );
}
if ( dirname( $hvkp_wp ) !== __DIR__ ) {
    echo 'WordPress: ' . dirname( $hvkp_wp ) . "\n";
}
echo "\n";

require $hvkp_wp;
global $wpdb;

$tabla = $wpdb->prefix . 'snippets';
$mode  = isset( $argv[1] ) ? $argv[1] : 'instalar';

/** Vuelve a escribir las reglas de URL con /acceso/ incluida. */
function hvkac_flush( $con_reglas = true ) {
    if ( $con_reglas ) {
        add_rewrite_rule( '^acceso/?$', 'index.php?hvkac=1', 'top' );
        add_filter( 'query_vars', function ( $v ) { $v[] = 'hvkac'; return $v; } );
    }
    flush_rewrite_rules( false );
}

if ( 'restore' === $mode ) {
    $wpdb->update( $tabla, array( 'active' => 0 ), array( 'name' => $HVKAC_NAME ) );
    hvkac_flush( false );
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Desactivado. /acceso/ deja de responder.\n";
    echo "        Las reservas ya creadas y sus enlaces siguen funcionando.\n";
    exit( 0 );
}

if ( 'clave' === $mode ) {
    $nueva = bin2hex( random_bytes( 20 ) );
    update_option( 'hvkac_clave', $nueva, false );
    echo "[OK]    Clave rotada. El enlace anterior ya no sirve. El nuevo es:\n";
    echo '        ' . home_url( '/acceso/?k=' . $nueva ) . "\n";
    exit( 0 );
}

if ( 'instalar' !== $mode ) {
    echo "[ERROR] Argumento no reconocido: $mode. Usa 'instalar', 'restore' o 'clave'.\n";
    exit( 1 );
}

$code = base64_decode( $HVKAC_B64 );
if ( '' === $HVKAC_MD5 || md5( $code ) !== $HVKAC_MD5 ) {
    echo "[ERROR] El contenido no paso la verificacion. Nada fue modificado.\n";
    exit( 1 );
}
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabla ) ) !== $tabla ) {
    echo "[ERROR] No existe la tabla de snippets (plugin Code Snippets).\n";
    exit( 1 );
}
if ( ! class_exists( 'HVKP_Token' ) ) {
    echo "[ERROR] El plugin del portal no esta activo; la pagina no tendria nada que crear.\n";
    exit( 1 );
}

$ok = array(); $warn = array();

$fila = array(
    'name'        => $HVKAC_NAME,
    'description' => 'Pagina privada para crear el acceso al portal de un huesped directo y copiar su enlace.',
    'code'        => $code,
    'tags'        => 'homvuk',
    // Global, como los demas: la pagina se sirve en el front pero el snippet
    // tiene que estar cargado antes de que WordPress resuelva la URL.
    'scope'       => 'global',
    'priority'    => 10,
    'active'      => 1,
    'modified'    => current_time( 'mysql' ),
);
$existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKAC_NAME ) );
if ( $existe ) {
    $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
    $ok[] = "Snippet actualizado (id $existe)";
} else {
    $wpdb->insert( $tabla, $fila );
    $ok[] = 'Snippet instalado (id ' . $wpdb->insert_id . ')';
}

hvkac_flush( true );
$ok[] = 'Reglas de URL actualizadas';

$clave = (string) get_option( 'hvkac_clave', '' );
if ( strlen( $clave ) < 20 ) {
    $clave = bin2hex( random_bytes( 20 ) );
    update_option( 'hvkac_clave', $clave, false );
    $ok[] = 'Clave creada';
}
$url = home_url( '/acceso/?k=' . $clave );

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
$ok[] = 'Caches purgadas';

/* --- Los departamentos que va a ofrecer ---------------------------------- */

echo "== Departamentos del portal ==\n";
$uds = $wpdb->get_results( "SELECT name, slug FROM {$wpdb->prefix}portal_units WHERE active = 1 ORDER BY name" );
foreach ( (array) $uds as $u ) {
    printf( "  %-18s %s\n", $u->slug, $u->name );
}
if ( ! $uds ) {
    $warn[] = 'No hay ninguna unidad activa en el portal: la pantalla no tendria donde crear la reserva.';
}

/* --- Que responda de verdad ---------------------------------------------- */

// Con la clave y sin sesion: es exactamente lo que hara su navegador.
$res = wp_remote_get( $url . '&v=' . time(), array( 'timeout' => 45, 'sslverify' => false ) );
if ( is_wp_error( $res ) ) {
    $warn[] = 'No se pudo comprobar la pagina: ' . $res->get_error_message();
} else {
    $cod  = (int) wp_remote_retrieve_response_code( $res );
    $body = (string) wp_remote_retrieve_body( $res );
    if ( 200 === $cod && false !== strpos( $body, 'name="apellido"' ) ) {
        $ok[] = 'Verificado: la pagina abre con la clave, sin iniciar sesion';
    } else {
        $warn[] = "La pagina respondio $cod. Si es 404, vuelve a guardar los enlaces permanentes "
            . 'en Ajustes -> Enlaces permanentes.';
    }
}

// Y sin la clave no debe abrirse. Es la mitad que importa.
$res = wp_remote_get( home_url( '/acceso/?v=' . time() ), array( 'timeout' => 45, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $cod = (int) wp_remote_retrieve_response_code( $res );
    if ( 200 === $cod ) {
        $warn[] = 'ATENCION: la pagina abre SIN la clave. Avisame antes de usarla.';
    } else {
        $ok[] = "Verificado: sin la clave no abre (responde $cod)";
    }
}

/* --- Resumen -------------------------------------------------------------- */

echo "\n";
foreach ( $ok as $l )   { echo "[OK]    $l\n"; }
foreach ( $warn as $l ) { echo "[AVISO] $l\n"; }

echo "\n== Tu pantalla ==\n";
echo "Guardala en favoritos. Funciona en el telefono y de incognito, sin iniciar\n";
echo "sesion. Es privada: quien la tenga puede crear accesos al portal.\n\n";
echo '  ' . $url . "\n\n";
echo "Si se te escapa, rotala con:  php hvkp-acceso.php clave\n";
echo "Para quitarla:                php hvkp-acceso.php restore\n";
echo "\nYa puedes borrar este archivo:  rm -f hvkp-acceso.php\n";
