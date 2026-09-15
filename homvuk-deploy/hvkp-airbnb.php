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
 * HOMVUK - Precios en vivo desde Airbnb (hvkp-airbnb)
 *
 * Deja que /reserva-directa/ lea el precio directamente del anuncio de Airbnb
 * -tarifa por noche, descuento semanal o mensual, lo que este puesto en ese
 * momento- y le reste la comision que Airbnb cobra y que reservando aqui no se
 * paga. Las tarifas se siguen editando solo en Airbnb; aqui no hay nada que
 * mantener.
 *
 * El numero del anuncio no hay que cargarlo: sale de la URL del iCal que ya
 * tiene cada unidad del portal.
 *
 * Todo vive en la base de datos (Code Snippets), sin archivos nuevos.
 *
 * Uso:  php hvkp-airbnb.php                        (instalar y comprobar)
 *       php hvkp-airbnb.php restore                (volver a la tarifa del sitio)
 *       php hvkp-airbnb.php probar [slug] [entrada] [salida]
 *                                                  (leer un precio ahora mismo)
 *       php hvkp-airbnb.php descuento <%> [slug]   (que porcentaje se le cede al
 *           huesped; sin slug, para todos. Por defecto 15,5: la comision)
 *       php hvkp-airbnb.php anuncio <slug> <numero>
 *                                                  (fijar a mano el numero del
 *           anuncio de Airbnb de un departamento, si no sale del iCal)
 */

$HVKAB_B64  = 'LyoqCiAqIEhPTVZVSyAtIFByZWNpb3MgZW4gdml2byBkZXNkZSBBaXJibmIgKGh2a2FiKQogKgogKiBFbCBwcm9ibGVtYTogbGFzIHRhcmlmYXMgeSBsb3MgZGVzY3VlbnRvcyBzZSBlZGl0YW4gZW4gQWlyYm5iLCB2YXJpYXMgdmVjZXMKICogcG9yIHNlbWFuYSwgeSBlbiBob212dWsuY29tIGhhYmlhIHF1ZSByZXBldGlybG9zIGEgbWFuby4gQ3VhbHF1aWVyIG9sdmlkbwogKiBkZWphYmEgbGEgcmVzZXJ2YSBkaXJlY3RhIG1vc3RyYW5kbyB1biBudW1lcm8gcGVvciBxdWUgZWwgZGUgQWlyYm5iLCBxdWUgZXMKICoganVzdG8gbG8gY29udHJhcmlvIGRlIGxvIHF1ZSBzZSBsZSBwcm9tZXRlIGFsIGh1ZXNwZWQuCiAqCiAqIFF1ZSBoYWNlIGVzdG86IGxlZSBlbCBwcmVjaW8gcXVlIEFpcmJuYiBsZSBtdWVzdHJhIGFsIGh1ZXNwZWQgcGFyYSBlc2UKICogYW51bmNpbyB5IGVzYXMgZmVjaGFzIC10YXJpZmEgYmFzZSwgZGVzY3VlbnRvIHNlbWFuYWwgbyBtZW5zdWFsLCBhaG9ycm9zIHBvcgogKiBlc3RhZGlhIGxhcmdhLSB5IGxvIHVzYSBjb21vIHJlZmVyZW5jaWEuIEVsIHByZWNpbyBkaXJlY3RvIHNlIGNhbGN1bGEKICogcmVzdGFuZG9sZSBlbCBwb3JjZW50YWplIHF1ZSBlbCBhbmZpdHJpb24gZGVjaWRlIGNlZGVyLiBObyBoYXkgbmFkYSBxdWUKICogbWFudGVuZXIgZW4gdW4gc2VndW5kbyBwYW5lbDogc2UgZWRpdGEgZW4gQWlyYm5iIHkgYWNhIHNlIHJlZmxlamEuCiAqCiAqIENvbW8gbG8gbGVlOiBlbCBtaXNtbyBlbmRwb2ludCBxdWUgdXNhIGxhIHByb3BpYSB3ZWIgZGUgQWlyYm5iIHBhcmEgcGludGFyCiAqIGVsIHJlY3VhZHJvIGRlIHJlc2VydmEgKGBTdGF5c1BkcFNlY3Rpb25zYCksIHNpbiBzZXNpb24gbmkgY3JlZGVuY2lhbGVzIC1lcwogKiBpbmZvcm1hY2lvbiBwdWJsaWNhIGRlbCBhbnVuY2lvLCBsYSBxdWUgdmUgY3VhbHF1aWVyYSBxdWUgbG8gYWJyYS0uIFNlCiAqIGNvbnN1bHRhIGNvbiBgd3BfcmVtb3RlX3Bvc3RgLCBzZSBjYWNoZWEsIHkgc2kgZmFsbGEgbm8gcm9tcGUgbmFkYTogcXVpZW4KICogbGxhbWUgc2UgcXVlZGEgY29uIGVsIHByZWNpbyBxdWUgeWEgdGVuaWEuCiAqCiAqIExvIGZyYWdpbCB5IGNvbW8gc2UgY3VpZGE6CiAqCiAqICAgLSBMYSBjb25zdWx0YSB2YSBmaXJtYWRhIGNvbiB1biBoYXNoIHF1ZSBBaXJibmIgY2FtYmlhIGN1YW5kbyBwdWJsaWNhLiBObwogKiAgICAgc2UgZGVqYSBlc2NyaXRvIGEgbWFubzogYGh2a2FiX2hhc2goKWAgbG8gc2FjYSBkZWwgcHJvcGlvIEphdmFTY3JpcHQgZGVsCiAqICAgICBhbnVuY2lvIHkgbG8gZ3VhcmRhIHVuYSBzZW1hbmEuIFNpIHVuIGRpYSBkZWphIGRlIHNlcnZpciwgc2UgdnVlbHZlIGEKICogICAgIGJ1c2NhciBzb2xvLgogKiAgIC0gQWlyYm5iIHJlc3BvbmRlIGVsIHRvdGFsIGV4YWN0byBjdWFuZG8gbGEgZXN0YWRpYSBlcyBjb3J0YQogKiAgICAgKGBkaXNwbGF5UHJpY2VTdHlsZSA9IFRPVEFMX09OTFlgKS4gUGFyYSBlc3RhZGlhcyBsYXJnYXMgcmVzcG9uZGUgdW4KICogICAgIHByb21lZGlvIG1lbnN1YWw7IGVuIGVzZSBjYXNvIHNlIHJlY29uc3RydXllIGVsIHRvdGFsIGEgcGFydGlyIGRlIGxhCiAqICAgICB0YXJpZmEgcG9yIG5vY2hlIHkgZGUgbG9zIG1pc21vcyBwb3JjZW50YWplcyBkZSBkZXNjdWVudG8gcXVlIHZpZW5lbiBlbgogKiAgICAgZWwgZGVzZ2xvc2UsIHkgcXVlZGEgbWFyY2FkbyBjb21vIGFwcm94aW1hZG8uCiAqCiAqIE5hZGEgZGUgZXN0byB0b2NhIGVsIGNvYnJvOiBlbCBjaGVja291dCBzaWd1ZSBzaWVuZG8gZWwgZGUgV29vQ29tbWVyY2UuCiAqLwoKZGVmaW5lKCAnSFZLQUJfQVBJS0VZJywgJ2QzMDZ6b3lqc3lhcnA3aWZodTY3cmp4bjUydHYwdDIwJyApOwpkZWZpbmUoICdIVktBQl9IT1NUJywgJ2h0dHBzOi8vd3d3LmFpcmJuYi5jbCcgKTsKZGVmaW5lKCAnSFZLQUJfQ0FDSEUnLCAxNSAqIE1JTlVURV9JTl9TRUNPTkRTICk7CgovKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09CiAqIDEuIFF1ZSBhbnVuY2lvIGRlIEFpcmJuYiBjb3JyZXNwb25kZSBhIGNhZGEgcHJvZHVjdG8KICogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwoKLyoqCiAqIEVsIG51bWVybyBkZWwgYW51bmNpby4gU2UgYnVzY2EgZW4gdHJlcyBzaXRpb3MsIGRlIG1hcyBhIG1lbm9zIGV4cGxpY2l0bzoKICogbGEgbWV0YSBkZWwgcHJvZHVjdG8sIGxhIGNvbHVtbmEgZGUgbGEgdW5pZGFkIGRlbCBwb3J0YWwsIHkgLWxvIGhhYml0dWFsLAogKiBwb3JxdWUgeWEgZXN0YSBjYXJnYWRvIHBhcmEgZWwgY2FsZW5kYXJpby0gbGEgVVJMIGRlbCBpQ2FsLCBxdWUgbG8gbGxldmEKICogZGVudHJvOiAuLi4vY2FsZW5kYXIvaWNhbC88bnVtZXJvPi5pY3M/cz0uLi4KICovCmZ1bmN0aW9uIGh2a2FiX2xpc3RpbmdfaWQoICRwaWQgKSB7CiAgICBnbG9iYWwgJHdwZGI7CgogICAgJHBpZCA9IChpbnQpICRwaWQ7CiAgICBpZiAoICEgJHBpZCApIHsKICAgICAgICByZXR1cm4gJyc7CiAgICB9CgogICAgJG0gPSB0cmltKCAoc3RyaW5nKSBnZXRfcG9zdF9tZXRhKCAkcGlkLCAnaHZrYWJfbGlzdGluZ19pZCcsIHRydWUgKSApOwogICAgaWYgKCBjdHlwZV9kaWdpdCggJG0gKSApIHsKICAgICAgICByZXR1cm4gJG07CiAgICB9CgogICAgJHRhYmxhID0gJHdwZGItPnByZWZpeCAuICdwb3J0YWxfdW5pdHMnOwogICAgaWYgKCAkd3BkYi0+Z2V0X3ZhciggJHdwZGItPnByZXBhcmUoICdTSE9XIFRBQkxFUyBMSUtFICVzJywgJHRhYmxhICkgKSAhPT0gJHRhYmxhICkgewogICAgICAgIHJldHVybiAnJzsKICAgIH0KICAgICR1ID0gJHdwZGItPmdldF9yb3coICR3cGRiLT5wcmVwYXJlKAogICAgICAgICJTRUxFQ1QgYWlyYm5iX2xpc3RpbmdfaWQsIGljYWxfdXJsIEZST00gJHRhYmxhIFdIRVJFIGxpc3RpbmdfaWQgPSAlZCBBTkQgYWN0aXZlID0gMSBMSU1JVCAxIiwKICAgICAgICAkcGlkCiAgICApICk7CiAgICBpZiAoICEgJHUgKSB7CiAgICAgICAgcmV0dXJuICcnOwogICAgfQogICAgaWYgKCAkdS0+YWlyYm5iX2xpc3RpbmdfaWQgJiYgY3R5cGVfZGlnaXQoIHRyaW0oICR1LT5haXJibmJfbGlzdGluZ19pZCApICkgKSB7CiAgICAgICAgcmV0dXJuIHRyaW0oICR1LT5haXJibmJfbGlzdGluZ19pZCApOwogICAgfQogICAgaWYgKCAkdS0+aWNhbF91cmwgJiYgcHJlZ19tYXRjaCggJyMvY2FsZW5kYXIvaWNhbC8oXGQrKVwuaWNzIycsICR1LT5pY2FsX3VybCwgJG0yICkgKSB7CiAgICAgICAgcmV0dXJuICRtMlsxXTsKICAgIH0KICAgIHJldHVybiAnJzsKfQoKLyoqCiAqIExvIHF1ZSBzZSBsZSBkZXNjdWVudGEgYWwgaHVlc3BlZCBwb3IgcmVzZXJ2YXIgZGlyZWN0bywgZW4gcG9yY2VudGFqZS4KICoKICogUG9yIGRlZmVjdG8gMTUsNTogbGEgY29taXNpb24gcXVlIEFpcmJuYiBsZSBjb2JyYSBhbCBhbmZpdHJpb24geSBxdWUgZW4gdW5hCiAqIHJlc2VydmEgZGlyZWN0YSBubyBleGlzdGUuIENlZGllbmRvbGEgZW50ZXJhLCBlbCBhbmZpdHJpb24gcmVjaWJlIGxvIG1pc21vCiAqIHF1ZSBob3kgeSBlbCBodWVzcGVkIHBhZ2EgbWVub3MuIFNlIHB1ZWRlIGNhbWJpYXIgZ2xvYmFsCiAqIChgaHZrYWJfZGVzY3VlbnRvYCkgbyBwb3IgYW51bmNpbyAobWV0YSBgaHZrYWJfZGVzY3VlbnRvYCkuCiAqLwpmdW5jdGlvbiBodmthYl9kZXNjdWVudG8oICRwaWQgPSAwICkgewogICAgJGQgPSBnZXRfcG9zdF9tZXRhKCAoaW50KSAkcGlkLCAnaHZrYWJfZGVzY3VlbnRvJywgdHJ1ZSApOwogICAgaWYgKCAnJyA9PT0gJGQgfHwgbnVsbCA9PT0gJGQgKSB7CiAgICAgICAgJGQgPSBnZXRfb3B0aW9uKCAnaHZrYWJfZGVzY3VlbnRvJywgJycgKTsKICAgIH0KICAgIGlmICggJycgPT09ICRkIHx8IG51bGwgPT09ICRkICkgewogICAgICAgICRkID0gMTUuNTsKICAgIH0KICAgIHJldHVybiBtaW4oIDQwLjAsIG1heCggMC4wLCAoZmxvYXQpICRkICkgKTsKfQoKLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogKiAyLiBFbCBoYXNoIGRlIGxhIGNvbnN1bHRhCiAqCiAqIEFpcmJuYiBubyBhY2VwdGEgZWwgdGV4dG8gZGUgbGEgY29uc3VsdGE6IGhheSBxdWUgbWFuZGFyIGVsIGhhc2ggZGUgdW5hIHF1ZQogKiB0ZW5nYSByZWdpc3RyYWRhLCB5IGxvIGNhbWJpYSBjYWRhIHZleiBxdWUgcHVibGljYS4gRWwgaWRlbnRpZmljYWRvciBlc3RhIGVuCiAqIGVsIEphdmFTY3JpcHQgZGVsIHByb3BpbyBhbnVuY2lvLCBhc2kgcXVlIHNlIGJ1c2NhIGFoaSB5IHNlIGd1YXJkYS4gU2UKICogcmVmcmVzY2Egc29sbyBjdWFuZG8gY2FkdWNhIG8gY3VhbmRvIGxhIEFQSSBsbyByZWNoYXphLgogKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovCgpmdW5jdGlvbiBodmthYl9oYXNoKCAkZm9yemFyID0gZmFsc2UgKSB7CiAgICAkZyA9IGdldF9vcHRpb24oICdodmthYl9oYXNoJywgYXJyYXkoKSApOwogICAgaWYgKCAhICRmb3J6YXIgJiYgaXNfYXJyYXkoICRnICkgJiYgISBlbXB0eSggJGdbJ2hhc2gnXSApCiAgICAgICAgJiYgISBlbXB0eSggJGdbJ3RzJ10gKSAmJiAoIHRpbWUoKSAtIChpbnQpICRnWyd0cyddICkgPCBXRUVLX0lOX1NFQ09ORFMgKSB7CiAgICAgICAgcmV0dXJuICRnWydoYXNoJ107CiAgICB9CgogICAgLy8gQnVzY2FybG8gY3Vlc3RhIHZhcmlhcyBkZXNjYXJnYXMuIFNpIGFjYWJhIGRlIGZhbGxhciwgbm8gc2UgcmVpbnRlbnRhIGVuCiAgICAvLyBjYWRhIHZpc2l0YTogc2Ugc2lndWUgY29uIGVsIHVsdGltbyBxdWUgZnVuY2lvbm8sIG8gc2luIHByZWNpbyBkZSBBaXJibmIuCiAgICBpZiAoICEgJGZvcnphciAmJiBnZXRfdHJhbnNpZW50KCAnaHZrYWJfYnVzY2FuZG8nICkgKSB7CiAgICAgICAgcmV0dXJuIGlzX2FycmF5KCAkZyApICYmICEgZW1wdHkoICRnWydoYXNoJ10gKSA/ICRnWydoYXNoJ10gOiAnJzsKICAgIH0KICAgIHNldF90cmFuc2llbnQoICdodmthYl9idXNjYW5kbycsIDEsIDEwICogTUlOVVRFX0lOX1NFQ09ORFMgKTsKCiAgICAkaGFzaCA9IGh2a2FiX2Rlc2N1YnJpcl9oYXNoKCk7CiAgICBpZiAoICRoYXNoICkgewogICAgICAgIHVwZGF0ZV9vcHRpb24oICdodmthYl9oYXNoJywgYXJyYXkoICdoYXNoJyA9PiAkaGFzaCwgJ3RzJyA9PiB0aW1lKCkgKSwgZmFsc2UgKTsKICAgICAgICByZXR1cm4gJGhhc2g7CiAgICB9CiAgICAvLyBTaW4gZGVzY3VicmltaWVudG8sIG1lam9yIGVsIHVsdGltbyBxdWUgZnVuY2lvbm8gcXVlIG5hZGEuCiAgICByZXR1cm4gaXNfYXJyYXkoICRnICkgJiYgISBlbXB0eSggJGdbJ2hhc2gnXSApID8gJGdbJ2hhc2gnXSA6ICcnOwp9CgpmdW5jdGlvbiBodmthYl9kZXNjdWJyaXJfaGFzaCgpIHsKICAgICRsaWQgPSBodmthYl9jdWFscXVpZXJfbGlzdGluZygpOwogICAgaWYgKCAhICRsaWQgKSB7CiAgICAgICAgcmV0dXJuICcnOwogICAgfQoKICAgICRodG1sID0gaHZrYWJfZ2V0KCBIVktBQl9IT1NUIC4gJy9yb29tcy8nIC4gcmF3dXJsZW5jb2RlKCAkbGlkICkgKTsKICAgIGlmICggISAkaHRtbCApIHsKICAgICAgICByZXR1cm4gJyc7CiAgICB9CgogICAgaWYgKCAhIHByZWdfbWF0Y2hfYWxsKAogICAgICAgICcjaHR0cHM6Ly9hMFwubXVzY2FjaGVcLmNvbS9haXJibmIvc3RhdGljL3BhY2thZ2VzL1teIlxcXFwgKV0rXC5qcyMnLAogICAgICAgICRodG1sLCAkbSApICkgewogICAgICAgIHJldHVybiAnJzsKICAgIH0KCiAgICAvLyBFbCBkZSBsYSBwYWdpbmEgZGVsIGFudW5jaW8gcHJpbWVybzogZXMgZG9uZGUgc3VlbGUgZXN0YXIsIHkgYXNpIGNhc2kKICAgIC8vIHNpZW1wcmUgYmFzdGEgY29uIHVuYSBkZXNjYXJnYS4gTG9zIGRlbWFzIHNvbiBkZSByZXNwYWxkbyBwb3Igc2kgQWlyYm5iCiAgICAvLyByZW9yZ2FuaXphIHN1cyBwYXF1ZXRlcy4KICAgICRidW5kbGVzID0gYXJyYXlfdmFsdWVzKCBhcnJheV91bmlxdWUoICRtWzBdICkgKTsKICAgIHVzb3J0KCAkYnVuZGxlcywgZnVuY3Rpb24gKCAkYSwgJGIgKSB7CiAgICAgICAgJHBhID0gKCBmYWxzZSAhPT0gc3RycG9zKCAkYSwgJ1BkcFBsYXRmb3JtUm91dGUnICkgKSA/IDAgOiAxOwogICAgICAgICRwYiA9ICggZmFsc2UgIT09IHN0cnBvcyggJGIsICdQZHBQbGF0Zm9ybVJvdXRlJyApICkgPyAwIDogMTsKICAgICAgICByZXR1cm4gJHBhIC0gJHBiOwogICAgfSApOwoKICAgIGZvcmVhY2ggKCBhcnJheV9zbGljZSggJGJ1bmRsZXMsIDAsIDYgKSBhcyAkdXJsICkgewogICAgICAgICRqcyA9IGh2a2FiX2dldCggJHVybCApOwogICAgICAgIGlmICggISAkanMgKSB7CiAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgIH0KICAgICAgICBpZiAoIHByZWdfbWF0Y2goCiAgICAgICAgICAgICIjbmFtZTonU3RheXNQZHBTZWN0aW9ucycsdHlwZToncXVlcnknLG9wZXJhdGlvbklkOicoW2EtZjAtOV17NjR9KScjIiwKICAgICAgICAgICAgJGpzLCAkaCApICkgewogICAgICAgICAgICByZXR1cm4gJGhbMV07CiAgICAgICAgfQogICAgfQogICAgcmV0dXJuICcnOwp9CgovKiogVW4gbnVtZXJvIGRlIGFudW5jaW8gY3VhbHF1aWVyYSBkZSBsb3MgbnVlc3Ryb3MsIHBhcmEgaXIgYSBsZWVyIGVsIGhhc2guICovCmZ1bmN0aW9uIGh2a2FiX2N1YWxxdWllcl9saXN0aW5nKCkgewogICAgZ2xvYmFsICR3cGRiOwoKICAgICR0YWJsYSA9ICR3cGRiLT5wcmVmaXggLiAncG9ydGFsX3VuaXRzJzsKICAgIGlmICggJHdwZGItPmdldF92YXIoICR3cGRiLT5wcmVwYXJlKCAnU0hPVyBUQUJMRVMgTElLRSAlcycsICR0YWJsYSApICkgPT09ICR0YWJsYSApIHsKICAgICAgICAkaWNhbCA9ICR3cGRiLT5nZXRfY29sKCAiU0VMRUNUIGljYWxfdXJsIEZST00gJHRhYmxhIFdIRVJFIGFjdGl2ZSA9IDEiICk7CiAgICAgICAgJGlkcyAgPSAkd3BkYi0+Z2V0X2NvbCggIlNFTEVDVCBhaXJibmJfbGlzdGluZ19pZCBGUk9NICR0YWJsYSBXSEVSRSBhY3RpdmUgPSAxIiApOwogICAgICAgIGZvcmVhY2ggKCAoYXJyYXkpICRpZHMgYXMgJGkgKSB7CiAgICAgICAgICAgIGlmICggY3R5cGVfZGlnaXQoIHRyaW0oIChzdHJpbmcpICRpICkgKSApIHsKICAgICAgICAgICAgICAgIHJldHVybiB0cmltKCAkaSApOwogICAgICAgICAgICB9CiAgICAgICAgfQogICAgICAgIGZvcmVhY2ggKCAoYXJyYXkpICRpY2FsIGFzICR1ICkgewogICAgICAgICAgICBpZiAoICR1ICYmIHByZWdfbWF0Y2goICcjL2NhbGVuZGFyL2ljYWwvKFxkKylcLmljcyMnLCAkdSwgJG0gKSApIHsKICAgICAgICAgICAgICAgIHJldHVybiAkbVsxXTsKICAgICAgICAgICAgfQogICAgICAgIH0KICAgIH0KICAgIHJldHVybiAoc3RyaW5nKSBnZXRfb3B0aW9uKCAnaHZrYWJfbGlzdGluZ19yZWZlcmVuY2lhJywgJycgKTsKfQoKLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogKiAzLiBMYSBjb25zdWx0YQogKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovCgpmdW5jdGlvbiBodmthYl9nZXQoICR1cmwgKSB7CiAgICAkciA9IHdwX3JlbW90ZV9nZXQoICR1cmwsIGFycmF5KAogICAgICAgICd0aW1lb3V0JyAgICAgPT4gMTUsCiAgICAgICAgJ3JlZGlyZWN0aW9uJyA9PiAzLAogICAgICAgICdoZWFkZXJzJyAgICAgPT4gYXJyYXkoCiAgICAgICAgICAgICdVc2VyLUFnZW50JyAgICAgID0+IGh2a2FiX2FnZW50ZSgpLAogICAgICAgICAgICAnQWNjZXB0LUxhbmd1YWdlJyA9PiAnZXMtQ0wsZXM7cT0wLjknLAogICAgICAgICksCiAgICApICk7CiAgICBpZiAoIGlzX3dwX2Vycm9yKCAkciApIHx8IDIwMCAhPT0gKGludCkgd3BfcmVtb3RlX3JldHJpZXZlX3Jlc3BvbnNlX2NvZGUoICRyICkgKSB7CiAgICAgICAgcmV0dXJuICcnOwogICAgfQogICAgcmV0dXJuIChzdHJpbmcpIHdwX3JlbW90ZV9yZXRyaWV2ZV9ib2R5KCAkciApOwp9CgpmdW5jdGlvbiBodmthYl9hZ2VudGUoKSB7CiAgICByZXR1cm4gJ01vemlsbGEvNS4wIChXaW5kb3dzIE5UIDEwLjA7IFdpbjY0OyB4NjQpIEFwcGxlV2ViS2l0LzUzNy4zNicKICAgICAgICAuICcgKEtIVE1MLCBsaWtlIEdlY2tvKSBDaHJvbWUvMTMxLjAuMC4wIFNhZmFyaS81MzcuMzYnOwp9CgovKioKICogTGFzIHZhcmlhYmxlcyBkZSBsYSBjb25zdWx0YS4gQ2FzaSB0b2RhcyBzb24gaW50ZXJydXB0b3JlcyBkZSBsb3MgdHJvem9zIGRlCiAqIGxhIHBhZ2luYSBxdWUgc2UgcXVpZXJlbiB0cmFlcjsgYWNhIHNlIHBpZGUgc29sbyBlbCByZWN1YWRybyBkZSByZXNlcnZhLAogKiBxdWUgZXMgZG9uZGUgdmEgZWwgcHJlY2lvLgogKi8KZnVuY3Rpb24gaHZrYWJfdmFyaWFibGVzKCAkbGlkLCAkaW4sICRvdXQsICRhZHVsdG9zICkgewogICAgJHNpID0gYXJyYXkoCiAgICAgICAgJ0dwQWNjZXNzaWJpbGl0eUZlYXR1cmVzRnJhZ21lbnQnLCAnR3BMdXhlU2VydmljZXNGcmFnbWVudCcsCiAgICAgICAgJ0dwQWRtaW5CYW5uZXJGcmFnbWVudCcsICdHcEJvb2tJdEZyYWdtZW50JywgJ0dwQW1lbml0aWVzRnJhZ21lbnQnLAogICAgICAgICdHcENhbmNlbGxhdGlvblBvbGljeVBpY2tlck1vZGFsRnJhZ21lbnQnLCAnR3BBdmFpbGFiaWxpdHlDYWxlbmRhcklubGluZUZyYWdtZW50JywKICAgICAgICAnR3BBdmFpbGFiaWxpdHlDYWxlbmRhckZyYWdtZW50JywgJ0dwRGVzY3JpcHRpb25GcmFnbWVudCcsICdHcEhlcm9GcmFnbWVudCcsCiAgICAgICAgJ0dwSGlnaGxpZ2h0c0NvbXBhY3RGcmFnbWVudCcsICdHcEhpZ2hsaWdodHNGcmFnbWVudCcsICdHcExvY2F0aW9uUGRwRnJhZ21lbnQnLAogICAgICAgICdHcE1lZXRZb3VySG9zdEZyYWdtZW50JywgJ0dwTWVzc2FnZUJhbm5lckZyYWdtZW50JywgJ0dwTmF2RnJhZ21lbnQnLAogICAgICAgICdHcE5hdk1vYmlsZUZyYWdtZW50JywgJ0dwQm9va0l0Tm9uRXhwZXJpZW5jZWRHdWVzdEZyYWdtZW50JywgJ0dwQmF0aHJvb21GcmFnbWVudCcsCiAgICAgICAgJ0dwT3ZlcnZpZXdWMkZyYWdtZW50JywgJ0dwUHJvcGVydHlBdmFpbGFibGVSb29tc0ZyYWdtZW50JywKICAgICAgICAnR3BSZXZpZXdzSGlnaGxpZ2h0QmFubmVyRnJhZ21lbnQnLCAnR3BIb3N0T3ZlcnZpZXdEZWZhdWx0RnJhZ21lbnQnLAogICAgICAgICdHcE5vbkV4cGVyaWVuY2VkR3Vlc3RMZWFybk1vcmVNb2RhbEZyYWdtZW50JywgJ0dwUmVwb3J0VG9BaXJibmJGcmFnbWVudCcsCiAgICAgICAgJ0dwUmV2aWV3c0ZyYWdtZW50JywgJ0dwUmV2aWV3c0VtcHR5RnJhZ21lbnQnLCAnR3BTZW9MaW5rc0ZyYWdtZW50JywKICAgICAgICAnR3BTbGVlcGluZ0FycmFuZ2VtZW50RnJhZ21lbnQnLCAnR3BTbGVlcGluZ0FycmFuZ2VtZW50SW1hZ2VzRnJhZ21lbnQnLAogICAgICAgICdHcFRpdGxlRnJhZ21lbnQnLCAnR3BVZ2NUcmFuc2xhdGlvbkZyYWdtZW50JywgJ0dwUG9saWNpZXNGcmFnbWVudCcsCiAgICAgICAgJ0dwTWFycXVlZUJvb2tJdEZsb2F0aW5nRm9vdGVyRnJhZ21lbnQnLCAnR3BNYXJxdWVlQm9va0l0TmF2RnJhZ21lbnQnLAogICAgICAgICdHcE1hcnF1ZWVCb29rSXRTaWRlYmFyRnJhZ21lbnQnLAogICAgKTsKICAgICRubyA9IGFycmF5KAogICAgICAgICdQZHBMYXlvdXRQaXBlbGluZUlucHV0cycsICdQZHBNaWdyYXRpb25BY2Nlc3NpYmlsaXR5RmVhdHVyZXNNb2RhbEZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uQWNjZXNzaWJpbGl0eUZlYXR1cmVzUHJldmlld0Nhcm91c2VsRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25MdXhlU2VydmljZXNGcmFnbWVudCcsICdQZHBNaWdyYXRpb25Cb29rSXROYXZGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbkFtZW5pdGllc0ZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvbkF2YWlsYWJpbGl0eUNhbGVuZGFySW5saW5lRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25BdmFpbGFiaWxpdHlDYWxlbmRhckZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvbkRlc2NyaXB0aW9uRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25IZXJvRnJhZ21lbnQnLCAnUGRwTWlncmF0aW9uSGlnaGxpZ2h0c0NvbXBhY3RGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbkhpZ2hsaWdodHNGcmFnbWVudCcsICdQZHBNaWdyYXRpb25Mb2NhdGlvblBkcEZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uTWVldFlvdXJIb3N0RnJhZ21lbnQnLCAnUGRwTWlncmF0aW9uTWVzc2FnZUJhbm5lckZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uTmF2RnJhZ21lbnQnLCAnUGRwTWlncmF0aW9uTmF2TW9iaWxlRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25Cb29rSXRGbG9hdGluZ0Zvb3RlckZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvbkJvb2tJdFNpZGViYXJGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbkJvb2tJdENhbGVuZGFyU2hlZXRGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbkJvb2tJdE5vbkV4cGVyaWVuY2VkR3Vlc3RGcmFnbWVudCcsICdQZHBNaWdyYXRpb25CYXRocm9vbUZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uT3ZlcnZpZXdWMkZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvblByb3BlcnR5QXZhaWxhYmxlUm9vbXNGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvblJldmlld3NIaWdobGlnaHRCYW5uZXJGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbkhvc3RPdmVydmlld0RlZmF1bHRGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvbk5vbkV4cGVyaWVuY2VkR3Vlc3RMZWFybk1vcmVNb2RhbEZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uUmVwb3J0VG9BaXJibmJGcmFnbWVudCcsICdQZHBNaWdyYXRpb25SZXZpZXdzRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25SZXZpZXdzRW1wdHlGcmFnbWVudCcsICdQZHBNaWdyYXRpb25TZW9MaW5rc0ZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uU2xlZXBpbmdBcnJhbmdlbWVudEZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uU2xlZXBpbmdBcnJhbmdlbWVudEltYWdlc0ZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvblRpdGxlRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25Qb2xpY2llc0ZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvbk1hcnF1ZWVCb29rSXRGbG9hdGluZ0Zvb3RlckZyYWdtZW50JywKICAgICAgICAnUGRwTWlncmF0aW9uTWFycXVlZUJvb2tJdE5hdkZyYWdtZW50JywgJ1BkcE1pZ3JhdGlvbk1hcnF1ZWVCb29rSXRTaWRlYmFyRnJhZ21lbnQnLAogICAgICAgICdQZHBNaWdyYXRpb25Pbmx5T25Cb29rSXRGcmFnbWVudCcsICdQZHBNaWdyYXRpb25Pbmx5T25Cb29rSXROYXZGcmFnbWVudCcsCiAgICAgICAgJ1BkcE1pZ3JhdGlvblBkcEVkdWNhdGlvbkZyYWdtZW50JywgJ1JlY2VudEFza1BkcFF1ZXN0aW9ucycsCiAgICApOwoKICAgICRpbXAgPSAncDNfJyAuIHRpbWUoKSAuICdfUDMnIC4gd3BfZ2VuZXJhdGVfcGFzc3dvcmQoIDEyLCBmYWxzZSwgZmFsc2UgKTsKCiAgICAkdiA9IGFycmF5KAogICAgICAgICdpZCcgICAgICAgICAgICAgICAgICA9PiBiYXNlNjRfZW5jb2RlKCAnU3RheUxpc3Rpbmc6JyAuICRsaWQgKSwKICAgICAgICAnZGVtYW5kU3RheUxpc3RpbmdJZCcgPT4gYmFzZTY0X2VuY29kZSggJ0RlbWFuZFN0YXlMaXN0aW5nOicgLiAkbGlkICksCiAgICAgICAgJ3BkcFNlY3Rpb25zUmVxdWVzdCcgID0+IGFycmF5KAogICAgICAgICAgICAnYWR1bHRzJyAgICAgICAgICAgICAgICAgICAgICA9PiAoc3RyaW5nKSAkYWR1bHRvcywKICAgICAgICAgICAgJ2J5cGFzc1RhcmdldGluZ3MnICAgICAgICAgICAgPT4gZmFsc2UsCiAgICAgICAgICAgICdob3N0UHJldmlldycgICAgICAgICAgICAgICAgID0+IGZhbHNlLAogICAgICAgICAgICAnbGF5b3V0cycgICAgICAgICAgICAgICAgICAgICA9PiBhcnJheSggJ1NJREVCQVInLCAnU0lOR0xFX0NPTFVNTicgKSwKICAgICAgICAgICAgJ3BldHMnICAgICAgICAgICAgICAgICAgICAgICAgPT4gMCwKICAgICAgICAgICAgJ3ByZXZpZXcnICAgICAgICAgICAgICAgICAgICAgPT4gZmFsc2UsCiAgICAgICAgICAgICdwcml2YXRlQm9va2luZycgICAgICAgICAgICAgID0+IGZhbHNlLAogICAgICAgICAgICAnc3RheXNCb29raW5nTWlncmF0aW9uRW5hYmxlZCcgPT4gZmFsc2UsCiAgICAgICAgICAgICd1c2VOZXdTZWN0aW9uV3JhcHBlckFwaScgICAgID0+IGZhbHNlLAogICAgICAgICAgICAnc2VjdGlvbklkcycgICAgICAgICAgICAgICAgICA9PiBhcnJheSggJ0JPT0tfSVRfU0lERUJBUicgKSwKICAgICAgICAgICAgJ2NoZWNrSW4nICAgICAgICAgICAgICAgICAgICAgPT4gJGluLAogICAgICAgICAgICAnY2hlY2tPdXQnICAgICAgICAgICAgICAgICAgICA9PiAkb3V0LAogICAgICAgICAgICAncDNJbXByZXNzaW9uSWQnICAgICAgICAgICAgICA9PiAkaW1wLAogICAgICAgICksCiAgICAgICAgJ3AzSW1wcmVzc2lvbklkJyAgICAgID0+ICRpbXAsCiAgICAgICAgJ2RhdGVSYW5nZScgICAgICAgICAgID0+IGFycmF5KCAnc3RhcnREYXRlJyA9PiAkaW4sICdlbmREYXRlJyA9PiAkb3V0ICksCiAgICAgICAgJ2d1ZXN0Q291bnRzJyAgICAgICAgID0+IGFycmF5KCAnbnVtYmVyT2ZBZHVsdHMnID0+IChpbnQpICRhZHVsdG9zICksCiAgICApOwogICAgZm9yZWFjaCAoICRzaSBhcyAkayApIHsKICAgICAgICAkdlsgJ2luY2x1ZGUnIC4gJGsgXSA9IHRydWU7CiAgICB9CiAgICBmb3JlYWNoICggJG5vIGFzICRrICkgewogICAgICAgICR2WyAnaW5jbHVkZScgLiAkayBdID0gZmFsc2U7CiAgICB9CiAgICByZXR1cm4gJHY7Cn0KCmZ1bmN0aW9uIGh2a2FiX2NvbnN1bHRhciggJGxpZCwgJGluLCAkb3V0LCAkYWR1bHRvcywgJGhhc2ggKSB7CiAgICAkdXJsID0gSFZLQUJfSE9TVCAuICcvYXBpL3YzL1N0YXlzUGRwU2VjdGlvbnMvJyAuICRoYXNoCiAgICAgICAgLiAnP29wZXJhdGlvbk5hbWU9U3RheXNQZHBTZWN0aW9ucyZsb2NhbGU9ZXMtWEwmY3VycmVuY3k9Q0xQJzsKCiAgICAkciA9IHdwX3JlbW90ZV9wb3N0KCAkdXJsLCBhcnJheSgKICAgICAgICAndGltZW91dCcgPT4gMTIsCiAgICAgICAgJ2hlYWRlcnMnID0+IGFycmF5KAogICAgICAgICAgICAnQ29udGVudC1UeXBlJyAgICAgICAgICAgICAgPT4gJ2FwcGxpY2F0aW9uL2pzb24nLAogICAgICAgICAgICAnQWNjZXB0JyAgICAgICAgICAgICAgICAgICAgPT4gJ2FwcGxpY2F0aW9uL2pzb24nLAogICAgICAgICAgICAnQWNjZXB0LUxhbmd1YWdlJyAgICAgICAgICAgPT4gJ2VzLUNMLGVzO3E9MC45JywKICAgICAgICAgICAgJ1VzZXItQWdlbnQnICAgICAgICAgICAgICAgID0+IGh2a2FiX2FnZW50ZSgpLAogICAgICAgICAgICAnWC1BaXJibmItQVBJLUtleScgICAgICAgICAgPT4gSFZLQUJfQVBJS0VZLAogICAgICAgICAgICAnWC1BaXJibmItR3JhcGhRTC1QbGF0Zm9ybScgPT4gJ3dlYicsCiAgICAgICAgKSwKICAgICAgICAnYm9keScgICAgPT4gd3BfanNvbl9lbmNvZGUoIGFycmF5KAogICAgICAgICAgICAnb3BlcmF0aW9uTmFtZScgPT4gJ1N0YXlzUGRwU2VjdGlvbnMnLAogICAgICAgICAgICAndmFyaWFibGVzJyAgICAgPT4gaHZrYWJfdmFyaWFibGVzKCAkbGlkLCAkaW4sICRvdXQsICRhZHVsdG9zICksCiAgICAgICAgICAgICdleHRlbnNpb25zJyAgICA9PiBhcnJheSggJ3BlcnNpc3RlZFF1ZXJ5JyA9PiBhcnJheSgKICAgICAgICAgICAgICAgICd2ZXJzaW9uJyA9PiAxLCAnc2hhMjU2SGFzaCcgPT4gJGhhc2ggKSApLAogICAgICAgICkgKSwKICAgICkgKTsKCiAgICBpZiAoIGlzX3dwX2Vycm9yKCAkciApICkgewogICAgICAgIHJldHVybiBhcnJheSggJ2Vycm9yJyA9PiAkci0+Z2V0X2Vycm9yX21lc3NhZ2UoKSApOwogICAgfQogICAgaWYgKCAyMDAgIT09IChpbnQpIHdwX3JlbW90ZV9yZXRyaWV2ZV9yZXNwb25zZV9jb2RlKCAkciApICkgewogICAgICAgIHJldHVybiBhcnJheSggJ2Vycm9yJyA9PiAnaHR0cCAnIC4gd3BfcmVtb3RlX3JldHJpZXZlX3Jlc3BvbnNlX2NvZGUoICRyICkgKTsKICAgIH0KICAgICRkID0ganNvbl9kZWNvZGUoIChzdHJpbmcpIHdwX3JlbW90ZV9yZXRyaWV2ZV9ib2R5KCAkciApLCB0cnVlICk7CiAgICBpZiAoICEgaXNfYXJyYXkoICRkICkgKSB7CiAgICAgICAgcmV0dXJuIGFycmF5KCAnZXJyb3InID0+ICdyZXNwdWVzdGEgaWxlZ2libGUnICk7CiAgICB9CiAgICBpZiAoICEgZW1wdHkoICRkWydlcnJvcnMnXSApICkgewogICAgICAgICRtc2cgPSBpc3NldCggJGRbJ2Vycm9ycyddWzBdWydtZXNzYWdlJ10gKSA/ICRkWydlcnJvcnMnXVswXVsnbWVzc2FnZSddIDogJ2Vycm9yJzsKICAgICAgICByZXR1cm4gYXJyYXkoICdlcnJvcicgPT4gJG1zZywgJ3JlaW50ZW50YXInID0+IHRydWUgKTsKICAgIH0KICAgIHJldHVybiBhcnJheSggJ2RhdG9zJyA9PiAkZCApOwp9CgovKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09CiAqIDQuIExlZXIgZWwgcHJlY2lvIGRlIGxhIHJlc3B1ZXN0YQogKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovCgovKiogIiQxLDQyMyw0MjcgQ0xQIiAtPiAxNDIzNDI3LjAgOyAiLSQyMDYsNjQwIENMUCIgLT4gLTIwNjY0MC4wICovCmZ1bmN0aW9uIGh2a2FiX251bSggJHR4dCApIHsKICAgICR0ID0gKHN0cmluZykgJHR4dDsKICAgICRuZWcgPSAoIGZhbHNlICE9PSBzdHJwb3MoICR0LCAnLScgKSApOwogICAgJG4gPSBwcmVnX3JlcGxhY2UoICcvW14wLTldLycsICcnLCAkdCApOwogICAgaWYgKCAnJyA9PT0gJG4gKSB7CiAgICAgICAgcmV0dXJuIDAuMDsKICAgIH0KICAgIHJldHVybiAkbmVnID8gLShmbG9hdCkgJG4gOiAoZmxvYXQpICRuOwp9CgovKiogQnVzY2EgcmVjdXJzaXZhbWVudGUgbGEgcHJpbWVyYSBjbGF2ZSBjb24gZXNlIG5vbWJyZS4gKi8KZnVuY3Rpb24gaHZrYWJfYnVzY2FyKCAkYXJyLCAkY2xhdmUgKSB7CiAgICBpZiAoICEgaXNfYXJyYXkoICRhcnIgKSApIHsKICAgICAgICByZXR1cm4gbnVsbDsKICAgIH0KICAgIGlmICggaXNzZXQoICRhcnJbICRjbGF2ZSBdICkgJiYgbnVsbCAhPT0gJGFyclsgJGNsYXZlIF0gKSB7CiAgICAgICAgcmV0dXJuICRhcnJbICRjbGF2ZSBdOwogICAgfQogICAgZm9yZWFjaCAoICRhcnIgYXMgJHYgKSB7CiAgICAgICAgaWYgKCBpc19hcnJheSggJHYgKSApIHsKICAgICAgICAgICAgJHIgPSBodmthYl9idXNjYXIoICR2LCAkY2xhdmUgKTsKICAgICAgICAgICAgaWYgKCBudWxsICE9PSAkciApIHsKICAgICAgICAgICAgICAgIHJldHVybiAkcjsKICAgICAgICAgICAgfQogICAgICAgIH0KICAgIH0KICAgIHJldHVybiBudWxsOwp9CgovKioKICogQ29udmllcnRlIGVsIHJlY3VhZHJvIGRlIHJlc2VydmEgZW4gY2lmcmFzLgogKgogKiBBaXJibmIgZGV2dWVsdmUgYFRPVEFMX09OTFlgIGNvbiBlbCB0b3RhbCBkZSBsYSBlc3RhZGlhIGN1YW5kbyBlc3RhIGVzCiAqIGNvcnRhLCB5IGBNT05USExZYCBjb24gdW4gcHJvbWVkaW8gbWVuc3VhbCBjdWFuZG8gcGFzYSBkZSB1biBtZXMuIEVuIGVsCiAqIHNlZ3VuZG8gY2FzbyBlbCB0b3RhbCBkZSBsYSBlc3RhZGlhIG5vIHZpZW5lLCBhc2kgcXVlIHNlIHJlY29uc3RydXllOiBsYQogKiB0YXJpZmEgcG9yIG5vY2hlIHNhbGUgZGVsIHByb21lZGlvLCB5IGVuY2ltYSBzZSBhcGxpY2FuIGxvcyBtaXNtb3MKICogcG9yY2VudGFqZXMgZGUgZGVzY3VlbnRvIHF1ZSBBaXJibmIgeWEgbGlzdG8gZW4gZWwgZGVzZ2xvc2UuCiAqLwpmdW5jdGlvbiBodmthYl9sZWVyKCAkZCwgJG5vY2hlcyApIHsKICAgICRwID0gaHZrYWJfYnVzY2FyKCAkZCwgJ3N0cnVjdHVyZWREaXNwbGF5UHJpY2UnICk7CiAgICBpZiAoICEgaXNfYXJyYXkoICRwICkgfHwgZW1wdHkoICRwWydwcmltYXJ5TGluZSddICkgKSB7CiAgICAgICAgJGRpc3AgPSBodmthYl9idXNjYXIoICRkLCAnYXZhaWxhYmxlJyApOwogICAgICAgIHJldHVybiBhcnJheSgKICAgICAgICAgICAgJ2Vycm9yJyAgICAgID0+ICdzaW4gcHJlY2lvJywKICAgICAgICAgICAgJ2Rpc3BvbmlibGUnID0+ICggZmFsc2UgPT09ICRkaXNwICkgPyBmYWxzZSA6IG51bGwsCiAgICAgICAgKTsKICAgIH0KCiAgICAkbGluZWEgID0gJHBbJ3ByaW1hcnlMaW5lJ107CiAgICAkZXN0aWxvID0gaXNzZXQoICRwWydkaXNwbGF5UHJpY2VTdHlsZSddICkgPyAoc3RyaW5nKSAkcFsnZGlzcGxheVByaWNlU3R5bGUnXSA6ICcnOwogICAgJGJydXRvICA9IGh2a2FiX251bSggaXNzZXQoICRsaW5lYVsnb3JpZ2luYWxQcmljZSddICkgPyAkbGluZWFbJ29yaWdpbmFsUHJpY2UnXSA6ICcnICk7CiAgICAkbmV0byAgID0gaHZrYWJfbnVtKCBpc3NldCggJGxpbmVhWydkaXNjb3VudGVkUHJpY2UnXSApID8gJGxpbmVhWydkaXNjb3VudGVkUHJpY2UnXSA6ICcnICk7CiAgICBpZiAoICRuZXRvIDw9IDAgKSB7CiAgICAgICAgJG5ldG8gPSBodmthYl9udW0oIGlzc2V0KCAkbGluZWFbJ3ByaWNlJ10gKSA/ICRsaW5lYVsncHJpY2UnXSA6ICcnICk7CiAgICB9CiAgICBpZiAoICRicnV0byA8PSAwICkgewogICAgICAgICRicnV0byA9ICRuZXRvOwogICAgfQogICAgaWYgKCAkbmV0byA8PSAwICkgewogICAgICAgIHJldHVybiBhcnJheSggJ2Vycm9yJyA9PiAnc2luIHByZWNpbycgKTsKICAgIH0KCiAgICAkbGluZWFzID0gYXJyYXkoKTsKICAgIGlmICggISBlbXB0eSggJHBbJ2V4cGxhbmF0aW9uRGF0YSddWydwcmljZURldGFpbHMnXSApICkgewogICAgICAgIGZvcmVhY2ggKCAkcFsnZXhwbGFuYXRpb25EYXRhJ11bJ3ByaWNlRGV0YWlscyddIGFzICRnICkgewogICAgICAgICAgICBpZiAoIGVtcHR5KCAkZ1snaXRlbXMnXSApICkgewogICAgICAgICAgICAgICAgY29udGludWU7CiAgICAgICAgICAgIH0KICAgICAgICAgICAgZm9yZWFjaCAoICRnWydpdGVtcyddIGFzICRpdCApIHsKICAgICAgICAgICAgICAgIGlmICggZW1wdHkoICRpdFsnZGVzY3JpcHRpb24nXSApIHx8ICEgaXNzZXQoICRpdFsncHJpY2VTdHJpbmcnXSApICkgewogICAgICAgICAgICAgICAgICAgIGNvbnRpbnVlOwogICAgICAgICAgICAgICAgfQogICAgICAgICAgICAgICAgJGxpbmVhc1tdID0gYXJyYXkoCiAgICAgICAgICAgICAgICAgICAgJ3RleHRvJyAgID0+IChzdHJpbmcpICRpdFsnZGVzY3JpcHRpb24nXSwKICAgICAgICAgICAgICAgICAgICAnbW9udG8nICAgPT4gaHZrYWJfbnVtKCAkaXRbJ3ByaWNlU3RyaW5nJ10gKSwKICAgICAgICAgICAgICAgICAgICAnZGVzdGFjYScgPT4gKCBpc3NldCggJGl0WydfX3R5cGVuYW1lJ10gKQogICAgICAgICAgICAgICAgICAgICAgICAmJiAnSGlnaGxpZ2h0RXhwbGFuYXRpb25MaW5lSXRlbScgPT09ICRpdFsnX190eXBlbmFtZSddICksCiAgICAgICAgICAgICAgICApOwogICAgICAgICAgICB9CiAgICAgICAgfQogICAgfQoKICAgICRleGFjdG8gPSB0cnVlOwoKICAgIGlmICggJ01PTlRITFknID09PSAkZXN0aWxvICYmICRub2NoZXMgPiAwICkgewogICAgICAgIGlmICggJG5vY2hlcyA+PSAyOCAmJiAkbm9jaGVzIDw9IDMxICkgewogICAgICAgICAgICAvLyBFbCBtZXMgeSBsYSBlc3RhZGlhIGNvaW5jaWRlbjogZWwgbmV0byB5YSBlcyBlbCB0b3RhbC4KICAgICAgICAgICAgJHRvdGFsID0gJG5ldG87CiAgICAgICAgfSBlbHNlIHsKICAgICAgICAgICAgLy8gRWwgYnJ1dG8gZXMgdW4gbWVzIGRlIDMwIG5vY2hlcy4gTGEgcHJvcG9yY2lvbiBlbnRyZSBuZXRvIHkgYnJ1dG8KICAgICAgICAgICAgLy8gbGxldmEgZGVudHJvIHRvZG9zIGxvcyBkZXNjdWVudG9zIHF1ZSBBaXJibmIgYXBsaWNvLCBhc2kgcXVlIGJhc3RhCiAgICAgICAgICAgIC8vIGxsZXZhcmxhIGEgbGFzIG5vY2hlcyByZWFsZXMuCiAgICAgICAgICAgICR0b3RhbCAgPSAkbm9jaGVzICogKCAkYnJ1dG8gLyAzMCApICogKCAkbmV0byAvICRicnV0byApOwogICAgICAgICAgICAkZXhhY3RvID0gZmFsc2U7CiAgICAgICAgfQogICAgfSBlbHNlIHsKICAgICAgICAkdG90YWwgPSAkbmV0bzsKICAgIH0KCiAgICByZXR1cm4gYXJyYXkoCiAgICAgICAgJ3RvdGFsJyAgICAgID0+IHJvdW5kKCAkdG90YWwgKSwKICAgICAgICAnYnJ1dG8nICAgICAgPT4gcm91bmQoICRicnV0byApLAogICAgICAgICdub2NoZScgICAgICA9PiAkbm9jaGVzID4gMCA/IHJvdW5kKCAkdG90YWwgLyAkbm9jaGVzICkgOiAwLAogICAgICAgICdsaW5lYXMnICAgICA9PiAkbGluZWFzLAogICAgICAgICdlc3RpbG8nICAgICA9PiAkZXN0aWxvLAogICAgICAgICdleGFjdG8nICAgICA9PiAkZXhhY3RvLAogICAgICAgICdkaXNwb25pYmxlJyA9PiB0cnVlLAogICAgKTsKfQoKLyogPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogKiA1LiBMYSBwdWVydGEgZGUgZW50cmFkYQogKiA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovCgovKioKICogUHJlY2lvIGRlIEFpcmJuYiBlbiBDTFAgcGFyYSB1biBwcm9kdWN0byB5IHVuYXMgZmVjaGFzLgogKgogKiBEZXZ1ZWx2ZSBudWxsIGN1YW5kbyBlbCBhbnVuY2lvIG5vIGVzdGEgZW5sYXphZG8gbyBBaXJibmIgbm8gcmVzcG9uZGU7IHF1aWVuCiAqIGxsYW1lIGRlYmUgc2VndWlyIGNvbiBsbyBxdWUgdGVuaWEuIFNlIGNhY2hlYSB1biBjdWFydG8gZGUgaG9yYTogZXMgbG8gcXVlCiAqIHRhcmRhIGVuIG5vdGFyc2UgdW4gY2FtYmlvIGRlIHRhcmlmYSwgeSBldml0YSBzYWxpciBhIEFpcmJuYiBlbiBjYWRhIHZpc2l0YS4KICovCmZ1bmN0aW9uIGh2a2FiX3ByZWNpbyggJHBpZCwgJGluLCAkb3V0LCAkYWR1bHRvcyA9IDIsICRyZWZyZXNjYXIgPSBmYWxzZSApIHsKICAgICRsaWQgPSBodmthYl9saXN0aW5nX2lkKCAkcGlkICk7CiAgICBpZiAoICEgJGxpZCApIHsKICAgICAgICByZXR1cm4gbnVsbDsKICAgIH0KCiAgICAkdHNfaW4gID0gc3RydG90aW1lKCAkaW4gKTsKICAgICR0c19vdXQgPSBzdHJ0b3RpbWUoICRvdXQgKTsKICAgIGlmICggISAkdHNfaW4gfHwgISAkdHNfb3V0IHx8ICR0c19vdXQgPD0gJHRzX2luICkgewogICAgICAgIHJldHVybiBudWxsOwogICAgfQogICAgJGluICAgICA9IGdtZGF0ZSggJ1ktbS1kJywgJHRzX2luICk7CiAgICAkb3V0ICAgID0gZ21kYXRlKCAnWS1tLWQnLCAkdHNfb3V0ICk7CiAgICAkbm9jaGVzID0gKGludCkgcm91bmQoICggJHRzX291dCAtICR0c19pbiApIC8gREFZX0lOX1NFQ09ORFMgKTsKCiAgICAkY2xhdmUgPSAnaHZrYWJfJyAuIG1kNSggJGxpZCAuICd8JyAuICRpbiAuICd8JyAuICRvdXQgLiAnfCcgLiAoaW50KSAkYWR1bHRvcyApOwogICAgaWYgKCAhICRyZWZyZXNjYXIgKSB7CiAgICAgICAgJGNhY2hlID0gZ2V0X3RyYW5zaWVudCggJGNsYXZlICk7CiAgICAgICAgaWYgKCBpc19hcnJheSggJGNhY2hlICkgKSB7CiAgICAgICAgICAgIHJldHVybiAkY2FjaGU7CiAgICAgICAgfQogICAgICAgIC8vIFRyYXMgdW4gZmFsbG8gc2UgZXNwZXJhIHVuIHBvY28gYW50ZXMgZGUgdm9sdmVyIGEgaW5zaXN0aXIsIHBhcmEgbm8KICAgICAgICAvLyBzYWxpciBhIEFpcmJuYiB1bmEgdmV6IHBvciB2aXNpdGEgbWllbnRyYXMgZHVyZSBsYSBjYWlkYS4KICAgICAgICBpZiAoIGdldF90cmFuc2llbnQoICRjbGF2ZSAuICdfbm8nICkgfHwgZ2V0X3RyYW5zaWVudCggJ2h2a2FiX2NhaWRvJyApICkgewogICAgICAgICAgICByZXR1cm4gbnVsbDsKICAgICAgICB9CiAgICB9CgogICAgJGhhc2ggPSBodmthYl9oYXNoKCk7CiAgICBpZiAoICEgJGhhc2ggKSB7CiAgICAgICAgcmV0dXJuIG51bGw7CiAgICB9CgogICAgJHIgPSBodmthYl9jb25zdWx0YXIoICRsaWQsICRpbiwgJG91dCwgJGFkdWx0b3MsICRoYXNoICk7CgogICAgLy8gU2kgQWlyYm5iIHB1YmxpY28geSBlbCBoYXNoIHF1ZWRvIHZpZWpvLCBzZSBidXNjYSBkZSBudWV2byB5IHNlIHJlaW50ZW50YS4KICAgIGlmICggaXNzZXQoICRyWydlcnJvciddICkgJiYgISBlbXB0eSggJHJbJ3JlaW50ZW50YXInXSApICkgewogICAgICAgICRoYXNoMiA9IGh2a2FiX2hhc2goIHRydWUgKTsKICAgICAgICBpZiAoICRoYXNoMiAmJiAkaGFzaDIgIT09ICRoYXNoICkgewogICAgICAgICAgICAkciA9IGh2a2FiX2NvbnN1bHRhciggJGxpZCwgJGluLCAkb3V0LCAkYWR1bHRvcywgJGhhc2gyICk7CiAgICAgICAgfQogICAgfQoKICAgIGlmICggaXNzZXQoICRyWydlcnJvciddICkgKSB7CiAgICAgICAgdXBkYXRlX29wdGlvbiggJ2h2a2FiX3VsdGltb19lcnJvcicsIGFycmF5KAogICAgICAgICAgICAnbXNnJyA9PiAkclsnZXJyb3InXSwgJ3RzJyA9PiB0aW1lKCksICdsaWQnID0+ICRsaWQgKSwgZmFsc2UgKTsKICAgICAgICAvLyBVbiBmYWxsbyBwYXNhamVybyBubyBkZWJlIGRpc3BhcmFyIHVuYSBjb25zdWx0YSBwb3IgdmlzaXRhLCBuaSBwYXJhCiAgICAgICAgLy8gZXN0YXMgZmVjaGFzIG5pIC1zaSBsbyBxdWUgc2UgY2F5byBlcyBsYSBzYWxpZGEgYSBpbnRlcm5ldC0gcGFyYQogICAgICAgIC8vIG5pbmd1bmE6IGNhZGEgaW50ZW50byBjdWVzdGEgc2VndW5kb3MgZGUgZXNwZXJhIGFsIGh1ZXNwZWQuCiAgICAgICAgc2V0X3RyYW5zaWVudCggJGNsYXZlIC4gJ19ubycsIDEsIDMgKiBNSU5VVEVfSU5fU0VDT05EUyApOwogICAgICAgIGlmICggZmFsc2UgIT09IHN0cmlwb3MoIChzdHJpbmcpICRyWydlcnJvciddLCAnY1VSTCcgKQogICAgICAgICAgICB8fCBmYWxzZSAhPT0gc3RyaXBvcyggKHN0cmluZykgJHJbJ2Vycm9yJ10sICdyZXNvbCcgKQogICAgICAgICAgICB8fCBmYWxzZSAhPT0gc3RyaXBvcyggKHN0cmluZykgJHJbJ2Vycm9yJ10sICd0aW1lZCBvdXQnICkgKSB7CiAgICAgICAgICAgIHNldF90cmFuc2llbnQoICdodmthYl9jYWlkbycsIDEsIDUgKiBNSU5VVEVfSU5fU0VDT05EUyApOwogICAgICAgIH0KICAgICAgICByZXR1cm4gbnVsbDsKICAgIH0KCiAgICAkcCA9IGh2a2FiX2xlZXIoICRyWydkYXRvcyddLCAkbm9jaGVzICk7CiAgICBpZiAoIGlzc2V0KCAkcFsnZXJyb3InXSApICkgewogICAgICAgIC8vIFNpbiBwcmVjaW8gc3VlbGUgc2lnbmlmaWNhciBxdWUgZXNhcyBmZWNoYXMgbm8gZXN0YW4gbGlicmVzIGVuIEFpcmJuYi4KICAgICAgICAkcCA9IGFycmF5KAogICAgICAgICAgICAndG90YWwnICAgICAgPT4gMCwKICAgICAgICAgICAgJ25vY2hlcycgICAgID0+ICRub2NoZXMsCiAgICAgICAgICAgICdkaXNwb25pYmxlJyA9PiBpc3NldCggJHBbJ2Rpc3BvbmlibGUnXSApID8gJHBbJ2Rpc3BvbmlibGUnXSA6IGZhbHNlLAogICAgICAgICAgICAnbGluZWFzJyAgICAgPT4gYXJyYXkoKSwKICAgICAgICAgICAgJ2V4YWN0bycgICAgID0+IHRydWUsCiAgICAgICAgICAgICdsaXN0aW5nJyAgICA9PiAkbGlkLAogICAgICAgICAgICAndHMnICAgICAgICAgPT4gdGltZSgpLAogICAgICAgICk7CiAgICAgICAgc2V0X3RyYW5zaWVudCggJGNsYXZlLCAkcCwgSFZLQUJfQ0FDSEUgKTsKICAgICAgICByZXR1cm4gJHA7CiAgICB9CgogICAgJHBbJ25vY2hlcyddICA9ICRub2NoZXM7CiAgICAkcFsnbGlzdGluZyddID0gJGxpZDsKICAgICRwWyd0cyddICAgICAgPSB0aW1lKCk7CiAgICBzZXRfdHJhbnNpZW50KCAkY2xhdmUsICRwLCBIVktBQl9DQUNIRSApOwogICAgcmV0dXJuICRwOwp9CgovKioKICogTG8gcXVlIHNlIGxlIGNvYnJhIGFsIGh1ZXNwZWQgcG9yIHJlc2VydmFyIGRpcmVjdG8sIGVuIENMUCwgeSBlbCBhaG9ycm8uCiAqIE51bGwgc2kgbm8gc2UgcHVkbyBsZWVyIEFpcmJuYi4KICovCmZ1bmN0aW9uIGh2a2FiX2RpcmVjdG8oICRwaWQsICRpbiwgJG91dCwgJGFkdWx0b3MgPSAyLCAkcmVmcmVzY2FyID0gZmFsc2UgKSB7CiAgICAkYSA9IGh2a2FiX3ByZWNpbyggJHBpZCwgJGluLCAkb3V0LCAkYWR1bHRvcywgJHJlZnJlc2NhciApOwogICAgaWYgKCAhIGlzX2FycmF5KCAkYSApIHx8IGVtcHR5KCAkYVsndG90YWwnXSApICkgewogICAgICAgIHJldHVybiBudWxsOwogICAgfQogICAgJHBjdCAgICAgPSBodmthYl9kZXNjdWVudG8oICRwaWQgKTsKICAgICRkaXJlY3RvID0gcm91bmQoICRhWyd0b3RhbCddICogKCAxIC0gJHBjdCAvIDEwMCApICk7CiAgICByZXR1cm4gYXJyYXkoCiAgICAgICAgJ2FpcmJuYl9jbHAnICA9PiAoZmxvYXQpICRhWyd0b3RhbCddLAogICAgICAgICdkaXJlY3RvX2NscCcgPT4gKGZsb2F0KSAkZGlyZWN0bywKICAgICAgICAnYWhvcnJvX2NscCcgID0+IChmbG9hdCkgKCAkYVsndG90YWwnXSAtICRkaXJlY3RvICksCiAgICAgICAgJ3BjdCcgICAgICAgICA9PiAkcGN0LAogICAgICAgICdub2NoZXMnICAgICAgPT4gKGludCkgJGFbJ25vY2hlcyddLAogICAgICAgICdsaW5lYXMnICAgICAgPT4gJGFbJ2xpbmVhcyddLAogICAgICAgICdleGFjdG8nICAgICAgPT4gISBlbXB0eSggJGFbJ2V4YWN0byddICksCiAgICAgICAgJ2Rpc3BvbmlibGUnICA9PiAhIGVtcHR5KCAkYVsnZGlzcG9uaWJsZSddICksCiAgICAgICAgJ3RzJyAgICAgICAgICA9PiAoaW50KSAkYVsndHMnXSwKICAgICk7Cn0K';
$HVKAB_MD5  = '5d635af1a6bbc1f86e64087ae7a7c4c9';
$HVKAB_NAME = 'HOMVUK Precios Airbnb';

printf( "HOMVUK %s · PHP %s (%s) · %s\n\n", basename( __FILE__ ), PHP_VERSION, PHP_SAPI, date( 'Y-m-d H:i' ) );

require __DIR__ . '/wp-load.php';
global $wpdb;

$tabla = $wpdb->prefix . 'snippets';
$mode  = isset( $argv[1] ) ? $argv[1] : 'instalar';

/** El producto de alojamiento que responde a ese slug. */
function hvkab_cli_producto( $slug ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT ID, post_title, post_name FROM {$wpdb->posts}
         WHERE post_name = %s AND post_type = 'product' AND post_status = 'publish' LIMIT 1",
        $slug
    ) );
}

/** Los departamentos cobrados por noche. */
function hvkab_cli_props() {
    return get_posts( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 60,
        'fields'         => 'ids',
        'orderby'        => 'title',
        'order'          => 'ASC',
        'meta_query'     => array( array( 'key' => 'ovabrw_define_1_day', 'value' => 'hotel' ) ),
    ) );
}

function hvkab_cli_clp( $n ) {
    return '$' . number_format( (float) $n, 0, ',', '.' );
}

/* =========================================================================
 * Modos que no instalan nada
 * ========================================================================= */

if ( 'restore' === $mode ) {
    $wpdb->update( $tabla, array( 'active' => 0 ), array( 'name' => $HVKAB_NAME ) );
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Desactivado. /reserva-directa/ vuelve a la tarifa cargada en el sitio.\n";
    exit( 0 );
}

if ( 'descuento' === $mode ) {
    $pct  = isset( $argv[2] ) ? (float) str_replace( ',', '.', $argv[2] ) : -1;
    $slug = isset( $argv[3] ) ? $argv[3] : '';
    if ( $pct < 0 || $pct > 40 ) {
        echo "[ERROR] Uso: php hvkp-airbnb.php descuento <0-40> [slug]\n";
        echo "        Ej.: php hvkp-airbnb.php descuento 15.5\n";
        exit( 1 );
    }
    if ( $slug ) {
        $p = hvkab_cli_producto( $slug );
        if ( ! $p ) {
            echo "[ERROR] No existe el producto '$slug'.\n";
            exit( 1 );
        }
        update_post_meta( (int) $p->ID, 'hvkab_descuento', $pct );
        echo "[OK]    {$p->post_title}: se le descontara {$pct}% al precio de Airbnb.\n";
    } else {
        update_option( 'hvkab_descuento', $pct, false );
        echo "[OK]    A todos los departamentos se les descontara {$pct}% al precio de Airbnb.\n";
    }
    exit( 0 );
}

if ( 'anuncio' === $mode ) {
    $slug = isset( $argv[2] ) ? $argv[2] : '';
    $num  = isset( $argv[3] ) ? preg_replace( '/\D/', '', $argv[3] ) : '';
    if ( ! $slug || ! $num ) {
        echo "[ERROR] Uso: php hvkp-airbnb.php anuncio <slug-producto> <numero-airbnb>\n";
        echo "        El numero es el de la URL del anuncio: airbnb.cl/rooms/<numero>\n";
        exit( 1 );
    }
    $p = hvkab_cli_producto( $slug );
    if ( ! $p ) {
        echo "[ERROR] No existe el producto '$slug'.\n";
        exit( 1 );
    }
    update_post_meta( (int) $p->ID, 'hvkab_listing_id', $num );
    echo "[OK]    {$p->post_title} quedo apuntando al anuncio $num.\n";
    echo "        Compruebalo: php hvkp-airbnb.php probar $slug\n";
    exit( 0 );
}

if ( 'probar' === $mode ) {
    if ( ! function_exists( 'hvkab_precio' ) ) {
        echo "[ERROR] El snippet no esta activo. Instala primero: php hvkp-airbnb.php\n";
        exit( 1 );
    }
    $slug = isset( $argv[2] ) ? $argv[2] : '';
    $in   = isset( $argv[3] ) ? $argv[3] : gmdate( 'Y-m-d', strtotime( '+21 days' ) );
    $out  = isset( $argv[4] ) ? $argv[4] : gmdate( 'Y-m-d', strtotime( '+28 days' ) );

    $ids = array();
    if ( $slug ) {
        $p = hvkab_cli_producto( $slug );
        if ( ! $p ) {
            echo "[ERROR] No existe el producto '$slug'.\n";
            exit( 1 );
        }
        $ids[] = (int) $p->ID;
    } else {
        $ids = hvkab_cli_props();
    }

    echo "\n== Precios leidos de Airbnb ($in a $out) ==\n\n";
    foreach ( $ids as $pid ) {
        $lid = hvkab_listing_id( $pid );
        printf( "  %s\n", get_the_title( $pid ) );
        if ( ! $lid ) {
            echo "    sin anuncio de Airbnb asociado\n\n";
            continue;
        }
        printf( "    anuncio %s  ->  https://www.airbnb.cl/rooms/%s\n", $lid, $lid );
        $a = hvkab_precio( $pid, $in, $out, 2, true );
        if ( ! $a ) {
            $e = get_option( 'hvkab_ultimo_error', array() );
            echo "    Airbnb no respondio" . ( isset( $e['msg'] ) ? ': ' . $e['msg'] : '' ) . "\n\n";
            continue;
        }
        if ( empty( $a['total'] ) ) {
            echo "    sin precio (esas fechas figuran ocupadas en Airbnb)\n\n";
            continue;
        }
        foreach ( $a['lineas'] as $l ) {
            printf( "      %-44s %14s\n", $l['texto'], hvkab_cli_clp( $l['monto'] ) );
        }
        $d = hvkab_directo( $pid, $in, $out, 2 );
        printf( "      %-44s %14s%s\n", 'Total en Airbnb',
            hvkab_cli_clp( $a['total'] ), empty( $a['exacto'] ) ? '  (aprox)' : '' );
        if ( $d ) {
            printf( "      %-44s %14s\n", 'Reservando directo (-' . $d['pct'] . '%)',
                hvkab_cli_clp( $d['directo_clp'] ) );
            printf( "      %-44s %14s\n", 'Ahorra el huesped', hvkab_cli_clp( $d['ahorro_clp'] ) );
        }
        echo "\n";
    }
    exit( 0 );
}

if ( 'instalar' !== $mode ) {
    echo "[ERROR] Argumento no reconocido: $mode. Usa 'instalar', 'restore', 'probar', 'descuento' o 'anuncio'.\n";
    exit( 1 );
}

/* =========================================================================
 * Instalacion
 * ========================================================================= */

$code = base64_decode( $HVKAB_B64 );
if ( '' === $HVKAB_MD5 || md5( $code ) !== $HVKAB_MD5 ) {
    echo "[ERROR] El contenido no paso la verificacion. Nada fue modificado.\n";
    exit( 1 );
}
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabla ) ) !== $tabla ) {
    echo "[ERROR] No existe la tabla de snippets (plugin Code Snippets).\n";
    exit( 1 );
}

$ok = array(); $warn = array();

$fila = array(
    'name'        => $HVKAB_NAME,
    'description' => 'Lee del anuncio de Airbnb la tarifa y los descuentos vigentes, para que /reserva-directa/ no dependa de precios cargados a mano.',
    'code'        => $code,
    'tags'        => 'homvuk',
    // Global, igual que el de reserva directa: la cotizacion se pide por
    // admin-ajax.php, que cuenta como admin, y un snippet de front-end no se
    // ejecuta ahi.
    'scope'       => 'global',
    // Antes que el de reserva directa, que es quien lo llama.
    'priority'    => 5,
    'active'      => 1,
    'modified'    => current_time( 'mysql' ),
);
$existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKAB_NAME ) );
if ( $existe ) {
    $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
    $ok[] = "Snippet actualizado (id $existe)";
} else {
    $wpdb->insert( $tabla, $fila );
    $ok[] = 'Snippet instalado (id ' . $wpdb->insert_id . ')';
}

// El hash de la consulta se guarda en una opcion; al reinstalar conviene
// volver a buscarlo, por si Airbnb publico entremedio.
delete_option( 'hvkab_hash' );
foreach ( (array) $wpdb->get_col(
    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_hvkab_%'" ) as $t ) {
    delete_option( $t );
}
$ok[] = 'Cache de precios vaciada';

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
$ok[] = 'Caches purgadas';

// El snippet se guardo en la base de datos, pero este proceso no lo tiene
// cargado. Se evalua aqui para poder comprobar de verdad que funciona.
if ( ! function_exists( 'hvkab_precio' ) ) {
    eval( $code );
}

/* --- ¿Se llega a Airbnb desde este servidor? ----------------------------- */

echo "\n== Conexion con Airbnb ==\n";
$hash = hvkab_hash( true );
if ( $hash ) {
    echo "  [OK]  Consulta reconocida (" . substr( $hash, 0, 12 ) . "...)\n";
    $ok[] = 'El servidor llega a Airbnb';
} else {
    echo "  [--]  No se pudo leer Airbnb desde el servidor.\n";
    $warn[] = 'El servidor no llega a Airbnb (salida HTTPS bloqueada, o el anuncio no esta publico). '
        . 'Sin eso la pagina sigue usando la tarifa cargada en el sitio, que es como estaba antes.';
}

/* --- Los departamentos, uno a uno ---------------------------------------- */

echo "\n== Departamentos ==\n";
$in  = gmdate( 'Y-m-d', strtotime( '+21 days' ) );
$out = gmdate( 'Y-m-d', strtotime( '+28 days' ) );
$con = 0;

foreach ( hvkab_cli_props() as $pid ) {
    $titulo = mb_substr( get_the_title( $pid ), 0, 44 );
    $lid    = hvkab_listing_id( $pid );
    if ( ! $lid ) {
        printf( "  %-46s sin anuncio de Airbnb\n", $titulo );
        $warn[] = get_post_field( 'post_name', $pid ) . ': no se le encontro anuncio de Airbnb. '
            . 'Si tiene uno, indicalo con: php hvkp-airbnb.php anuncio '
            . get_post_field( 'post_name', $pid ) . ' <numero>';
        continue;
    }
    $con++;
    $d = $hash ? hvkab_directo( $pid, $in, $out, 2, true ) : null;
    if ( $d ) {
        printf( "  %-46s anuncio %-20s  Airbnb %12s  ->  directo %12s\n",
            $titulo, $lid, hvkab_cli_clp( $d['airbnb_clp'] ), hvkab_cli_clp( $d['directo_clp'] ) );
    } else {
        printf( "  %-46s anuncio %-20s  sin precio para %s (ocupado o sin respuesta)\n",
            $titulo, $lid, $in );
    }
}
if ( ! $con ) {
    $warn[] = 'Ningun departamento tiene anuncio de Airbnb asociado. Se saca de la URL del iCal '
        . 'de cada unidad del portal; si no la tienen cargada, usa el modo "anuncio".';
}

/* --- Que porcentaje se cede ---------------------------------------------- */

$pct = (float) get_option( 'hvkab_descuento', 15.5 );
echo "\n== Descuento por reservar directo ==\n";
printf( "  %s%% sobre el precio de Airbnb.\n", rtrim( rtrim( number_format( $pct, 1, ',', '' ), '0' ), ',' ) );
echo "  Es la comision que Airbnb te cobra a ti y que en una reserva directa no existe:\n";
echo "  cediendola entera recibes lo mismo que hoy y el huesped paga menos.\n";
echo "  Para cambiarlo:  php hvkp-airbnb.php descuento 10\n";

/* --- La pagina publica, de verdad ---------------------------------------- */

$prop = null;
foreach ( hvkab_cli_props() as $pid ) {
    if ( hvkab_listing_id( $pid ) ) { $prop = $pid; break; }
}
if ( $prop ) {
    $url = home_url( '/reserva-directa/?casa=' . get_post_field( 'post_name', $prop )
        . '&in=' . $in . '&out=' . $out . '&pax=2&v=' . time() );
    $res = wp_remote_get( $url, array( 'timeout' => 60, 'sslverify' => false ) );
    if ( is_wp_error( $res ) ) {
        $warn[] = 'No se pudo comprobar la pagina publica: ' . $res->get_error_message();
    } else {
        $cod  = (int) wp_remote_retrieve_response_code( $res );
        $body = (string) wp_remote_retrieve_body( $res );
        if ( 200 === $cod && false !== strpos( $body, 'id="desgBox"' ) ) {
            $ok[] = 'Verificado: la pagina del huesped muestra el desglose de Airbnb';
        } elseif ( 200 === $cod ) {
            $warn[] = 'La pagina responde, pero no aparecio el desglose. Revisa que el snippet '
                . 'de Reserva Directa este al dia: php hvkp-directa.php';
        } else {
            $warn[] = "La pagina del huesped respondio $cod.";
        }
    }
}

/* --- Resumen -------------------------------------------------------------- */

echo "\n";
foreach ( $ok as $l )   { echo "[OK]    $l\n"; }
foreach ( $warn as $l ) { echo "[AVISO] $l\n"; }

echo "\nComprueba un precio cuando quieras:\n";
echo "  php hvkp-airbnb.php probar\n";
echo "Y si algo sale mal, se vuelve atras con:\n";
echo "  php hvkp-airbnb.php restore\n";
echo "\nYa puedes borrar este archivo:  rm -f hvkp-airbnb.php\n";
