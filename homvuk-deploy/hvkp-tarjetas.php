<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Tarjetas de productos (hvkp-tarjetas)
 *
 *  1. Arregla la bandera de Chile, que en el carrusel se estiraba a lo ancho
 *     de toda la tarjeta (el carrusel fuerza width:100% a cualquier imagen,
 *     y la etiqueta de la bandera venia con height=auto sin comillas).
 *  2. Moderniza las tarjetas de vehiculos y propiedades: fotos con la misma
 *     proporcion, zoom suave al pasar el mouse, tarjeta elevada con sombra,
 *     precio destacado y el valor en pesos como linea secundaria.
 *
 * Todo vive en la base de datos (Code Snippets), sin archivos nuevos.
 *
 * Uso:  php hvkp-tarjetas.php            (aplicar)
 *       php hvkp-tarjetas.php restore    (desactivar)
 */

$HVKT_B64  = 'LyoqCiAqIEhPTVZVSyBUYXJqZXRhcyBQUk8KICogLSBDb3JyaWdlIGxhIGJhbmRlcmEgZGUgQ2hpbGUsIHF1ZSBlbCBjYXJydXNlbCBlc3RpcmFiYSBhIHBhbnRhbGxhIGNvbXBsZXRhLgogKiAtIE1vZGVybml6YSBsYXMgdGFyamV0YXMgZGUgcHJvcGllZGFkZXMgeSB2ZWhpY3Vsb3MgZGVsIHNpdGlvIHB1YmxpY28uCiAqLwphZGRfYWN0aW9uKCAnd3BfaGVhZCcsIGZ1bmN0aW9uICgpIHsKICAgIGlmICggaXNfYWRtaW4oKSApIHsKICAgICAgICByZXR1cm47CiAgICB9CiAgICBlY2hvICc8c3R5bGUgaWQ9Imh2a3QtY3NzIj4nCgogICAgLyogLS0tIEJhbmRlcmEgZGVsIHByZWNpbyBlbiBwZXNvczogZWwgY2FycnVzZWwgZXN0aXJhIHRvZGEgPGltZz4gYWwgMTAwJSAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlLmRvcyBpbWcsLmVudG94LXByb2R1Y3QtcHJpY2UgaW1nW3NyYyo9IkZsYWdfb2ZfQ2hpbGUiXSwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5lbnRveC1wcm9kdWN0LXByaWNlIGltZywub3ZhLXByb2R1Y3QtbGlzdCAuZW50b3gtcHJvZHVjdC1wcmljZSBpbWd7d2lkdGg6MThweCFpbXBvcnRhbnQ7aGVpZ2h0OjEycHghaW1wb3J0YW50O21heC13aWR0aDoxOHB4IWltcG9ydGFudDttaW4td2lkdGg6MCFpbXBvcnRhbnQ7b2JqZWN0LWZpdDpjb3Zlcjtib3JkZXItcmFkaXVzOjJweDtkaXNwbGF5OmlubGluZS1ibG9jayFpbXBvcnRhbnQ7dmVydGljYWwtYWxpZ246bWlkZGxlO21hcmdpbi1yaWdodDo3cHg7Ym94LXNoYWRvdzowIDAgMCAxcHggcmdiYSgxNiwyNCw0MCwuMTApfScKCiAgICAvKiAtLS0gVGFyamV0YSAtLS0gKi8KICAgIC4gJy5vdmEtcHJvZHVjdC1saXN0IC5lbnRveF9wcm9kdWN0LC5vdmEtcHJvZHVjdC1zbGlkZXIgLmVudG94X3Byb2R1Y3R7YmFja2dyb3VuZDojZmZmO2JvcmRlcjoxcHggc29saWQgI2VjZWVmMztib3JkZXItcmFkaXVzOjE4cHg7b3ZlcmZsb3c6aGlkZGVuO2JveC1zaGFkb3c6MCAxcHggM3B4IHJnYmEoMTYsMjQsNDAsLjA0KSwwIDhweCAyNHB4IHJnYmEoMTYsMjQsNDAsLjA1KTt0cmFuc2l0aW9uOnRyYW5zZm9ybSAuMjJzIGN1YmljLWJlemllciguMiwuOCwuMiwxKSxib3gtc2hhZG93IC4yMnMgZWFzZSxib3JkZXItY29sb3IgLjIycyBlYXNlO2hlaWdodDoxMDAlO2Rpc3BsYXk6ZmxleDtmbGV4LWRpcmVjdGlvbjpjb2x1bW59JwogICAgLiAnLm92YS1wcm9kdWN0LWxpc3QgLmVudG94X3Byb2R1Y3Q6aG92ZXIsLm92YS1wcm9kdWN0LXNsaWRlciAuZW50b3hfcHJvZHVjdDpob3Zlcnt0cmFuc2Zvcm06dHJhbnNsYXRlWSgtNnB4KTtib3gtc2hhZG93OjAgNnB4IDE2cHggcmdiYSgxNiwyNCw0MCwuMDgpLDAgMjBweCA0MHB4IHJnYmEoMTYsMjQsNDAsLjEyKTtib3JkZXItY29sb3I6I2UzZTZlZX0nCgogICAgLyogLS0tIEZvdG86IG1pc21hIHByb3BvcmNpb24gZW4gdG9kYXMgeSB6b29tIHN1YXZlIC0tLSAqLwogICAgLiAnLmVudG94X2hlYWRfcHJvZHVjdHtwb3NpdGlvbjpyZWxhdGl2ZTtvdmVyZmxvdzpoaWRkZW47YmFja2dyb3VuZDojZjRmNWY4fScKICAgIC4gJy5lbnRveF9oZWFkX3Byb2R1Y3QgYXtkaXNwbGF5OmJsb2NrfScKICAgIC4gJy5lbnRveF9oZWFkX3Byb2R1Y3QgaW1nLC5lbnRveC1wcm9kdWN0LXRodW1ibmFpbCBpbWd7d2lkdGg6MTAwJSFpbXBvcnRhbnQ7aGVpZ2h0OmF1dG87YXNwZWN0LXJhdGlvOjQvMztvYmplY3QtZml0OmNvdmVyO2Rpc3BsYXk6YmxvY2s7dHJhbnNpdGlvbjp0cmFuc2Zvcm0gLjVzIGN1YmljLWJlemllciguMiwuOCwuMiwxKX0nCiAgICAuICcuZW50b3hfcHJvZHVjdDpob3ZlciAuZW50b3hfaGVhZF9wcm9kdWN0IGltZ3t0cmFuc2Zvcm06c2NhbGUoMS4wNSl9JwoKICAgIC8qIC0tLSBDdWVycG8gLS0tICovCiAgICAuICcuZW50b3hfZm9vdF9wcm9kdWN0e3BhZGRpbmc6MThweCAxOHB4IDIwcHg7ZGlzcGxheTpmbGV4O2ZsZXgtZGlyZWN0aW9uOmNvbHVtbjtnYXA6MnB4O2ZsZXg6MX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC10aXRsZSxoMi5lbnRveC1wcm9kdWN0LXRpdGxle21hcmdpbjowIDAgMTBweDtmb250LXNpemU6MS4wMnJlbTtsaW5lLWhlaWdodDoxLjM1fScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXRpdGxlIGF7Y29sb3I6IzE2MWEyYiFpbXBvcnRhbnQ7Zm9udC13ZWlnaHQ6NzAwO3RleHQtZGVjb3JhdGlvbjpub25lO2Rpc3BsYXk6LXdlYmtpdC1ib3g7LXdlYmtpdC1saW5lLWNsYW1wOjI7LXdlYmtpdC1ib3gtb3JpZW50OnZlcnRpY2FsO292ZXJmbG93OmhpZGRlbjt0cmFuc2l0aW9uOmNvbG9yIC4xNnMgZWFzZX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC10aXRsZSBhOmhvdmVye2NvbG9yOiNkNjMzODQhaW1wb3J0YW50fScKCiAgICAvKiAtLS0gUHJlY2lvcyAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXdyYXBwZXItcHJpY2V7bWFyZ2luLXRvcDphdXRvfScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNle2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpiYXNlbGluZTtnYXA6NnB4O2ZsZXgtd3JhcDp3cmFwO21hcmdpbjowfScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlIC5wcm9kdWN0LWFtb3VudCwuZW50b3gtcHJvZHVjdC1wcmljZSAud29vY29tbWVyY2UtUHJpY2UtYW1vdW50e2ZvbnQtc2l6ZToxLjQycmVtIWltcG9ydGFudDtmb250LXdlaWdodDo4MDAhaW1wb3J0YW50O2NvbG9yOiNkNjMzODQhaW1wb3J0YW50O2xldHRlci1zcGFjaW5nOi0uMDJlbTtsaW5lLWhlaWdodDoxLjE1fScKICAgIC4gJy5lbnRveC1wcm9kdWN0LXByaWNlIC5kZWZpbmVfMV9kYXksLmVudG94LXByb2R1Y3QtcHJpY2UgLmRlZmluZV8xX2hvdXJ7Zm9udC1zaXplOi44MnJlbTtjb2xvcjojN2I4MTk0O2ZvbnQtd2VpZ2h0OjUwMH0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1wcmljZS5kb3N7ZGlzcGxheTpmbGV4O2FsaWduLWl0ZW1zOmNlbnRlcjtnYXA6MDttYXJnaW4tdG9wOjZweDtmb250LXNpemU6Ljg0cmVtO2NvbG9yOiM3YjgxOTQ7Zm9udC13ZWlnaHQ6NjAwfScKCiAgICAvKiAtLS0gVmFsb3JhY2lvbiAtLS0gKi8KICAgIC4gJy5lbnRveC1wcm9kdWN0LXJldmlld3ttYXJnaW4tdG9wOjEycHg7cGFkZGluZy10b3A6MTJweDtib3JkZXItdG9wOjFweCBzb2xpZCAjZjFmMmY2O2Rpc3BsYXk6ZmxleDthbGlnbi1pdGVtczpjZW50ZXI7Z2FwOjhweH0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1yZXZpZXcgLnN0YXItcmF0aW5ne2ZvbnQtc2l6ZTouOGVtfScKCiAgICAvKiAtLS0gRmF2b3JpdG8gLS0tICovCiAgICAuICcuZW50b3gtcHJvZHVjdC1mYXZvdXJpdGV7Ym9yZGVyLXJhZGl1czo5OTlweCFpbXBvcnRhbnQ7YmFja2dyb3VuZDpyZ2JhKDI1NSwyNTUsMjU1LC45MikhaW1wb3J0YW50O2JhY2tkcm9wLWZpbHRlcjpibHVyKDRweCk7Ym94LXNoYWRvdzowIDJweCA4cHggcmdiYSgxNiwyNCw0MCwuMTQpO3RyYW5zaXRpb246dHJhbnNmb3JtIC4xNnMgZWFzZSxiYWNrZ3JvdW5kIC4xNnMgZWFzZX0nCiAgICAuICcuZW50b3gtcHJvZHVjdC1mYXZvdXJpdGU6aG92ZXJ7dHJhbnNmb3JtOnNjYWxlKDEuMDgpO2JhY2tncm91bmQ6I2ZmZiFpbXBvcnRhbnR9JwoKICAgIC8qIC0tLSBFc3BhY2lvIHBhcmEgcXVlIGxhIHNvbWJyYSBubyBzZSBjb3J0ZSBlbiBlbCBjYXJydXNlbCAtLS0gKi8KICAgIC4gJy5vdmEtcHJvZHVjdC1zbGlkZXIgLm93bC1zdGFnZS1vdXRlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5zbGljay1saXN0e3BhZGRpbmc6MTRweCAwIDIycHghaW1wb3J0YW50O21hcmdpbjotMTRweCAwIC0yMnB4fScKICAgIC4gJy5vdmEtcHJvZHVjdC1zbGlkZXIgLm93bC1pdGVtLC5vdmEtcHJvZHVjdC1zbGlkZXIgLnNsaWNrLXNsaWRle2Rpc3BsYXk6ZmxleDtoZWlnaHQ6YXV0b30nCiAgICAuICcub3ZhLXByb2R1Y3Qtc2xpZGVyIC5vd2wtaXRlbT4qLC5vdmEtcHJvZHVjdC1zbGlkZXIgLnNsaWNrLXNsaWRlPip7d2lkdGg6MTAwJX0nCgogICAgLyogLS0tIEZsZWNoYXMgZGVsIGNhcnJ1c2VsIC0tLSAqLwogICAgLiAnLm92YS1wcm9kdWN0LXNsaWRlciAub3dsLW5hdiBidXR0b24sLm92YS1wcm9kdWN0LXNsaWRlciAuc2xpY2stYXJyb3d7dHJhbnNpdGlvbjpiYWNrZ3JvdW5kIC4xNnMgZWFzZSxjb2xvciAuMTZzIGVhc2UsdHJhbnNmb3JtIC4xNnMgZWFzZX0nCiAgICAuICcub3ZhLXByb2R1Y3Qtc2xpZGVyIC5vd2wtbmF2IGJ1dHRvbjpob3Zlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5zbGljay1hcnJvdzpob3ZlcntiYWNrZ3JvdW5kOiNlODQzOTMhaW1wb3J0YW50O2NvbG9yOiNmZmYhaW1wb3J0YW50O3RyYW5zZm9ybTp0cmFuc2xhdGVZKC0ycHgpfScKCiAgICAuICdAbWVkaWEgKG1heC13aWR0aDo2NDBweCl7LmVudG94X2Zvb3RfcHJvZHVjdHtwYWRkaW5nOjE2cHh9LmVudG94LXByb2R1Y3QtcHJpY2UgLnByb2R1Y3QtYW1vdW50LC5lbnRveC1wcm9kdWN0LXByaWNlIC53b29jb21tZXJjZS1QcmljZS1hbW91bnR7Zm9udC1zaXplOjEuM3JlbSFpbXBvcnRhbnR9fScKICAgIC4gJ0BtZWRpYSAocHJlZmVycy1yZWR1Y2VkLW1vdGlvbjpyZWR1Y2Upey5vdmEtcHJvZHVjdC1saXN0IC5lbnRveF9wcm9kdWN0LC5vdmEtcHJvZHVjdC1zbGlkZXIgLmVudG94X3Byb2R1Y3QsLmVudG94X2hlYWRfcHJvZHVjdCBpbWd7dHJhbnNpdGlvbjpub25lIWltcG9ydGFudH0ub3ZhLXByb2R1Y3QtbGlzdCAuZW50b3hfcHJvZHVjdDpob3Zlciwub3ZhLXByb2R1Y3Qtc2xpZGVyIC5lbnRveF9wcm9kdWN0OmhvdmVye3RyYW5zZm9ybTpub25lIWltcG9ydGFudH0uZW50b3hfcHJvZHVjdDpob3ZlciAuZW50b3hfaGVhZF9wcm9kdWN0IGltZ3t0cmFuc2Zvcm06bm9uZSFpbXBvcnRhbnR9fScKICAgIC4gJzwvc3R5bGU+JzsKfSwgOTkgKTs=';
$HVKT_MD5  = 'd65549b81911cad985e47539ce6c9529';
$HVKT_NAME = 'HOMVUK Tarjetas PRO';

require __DIR__ . '/wp-load.php';
global $wpdb;

$tabla = $wpdb->prefix . 'snippets';
$mode  = isset( $argv[1] ) ? $argv[1] : 'aplicar';

if ( 'restore' === $mode ) {
    $wpdb->update( $tabla, array( 'active' => 0 ), array( 'name' => $HVKT_NAME ) );
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Estilos desactivados (las tarjetas vuelven a como estaban)\n";
    exit( 0 );
}

$code = base64_decode( $HVKT_B64 );
if ( md5( $code ) !== $HVKT_MD5 ) {
    echo "[ERROR] El contenido no paso la verificacion. Nada fue modificado.\n";
    exit( 1 );
}
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabla ) ) !== $tabla ) {
    echo "[ERROR] No existe la tabla de snippets (plugin Code Snippets).\n";
    exit( 1 );
}

$fila = array(
    'name'        => $HVKT_NAME,
    'description' => 'Corrige la bandera del precio en pesos y moderniza las tarjetas de productos.',
    'code'        => $code,
    'tags'        => 'homvuk',
    'scope'       => 'front-end',
    'priority'    => 12,
    'active'      => 1,
    'modified'    => current_time( 'mysql' ),
);
$existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKT_NAME ) );
if ( $existe ) {
    $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
    echo "[OK]    Estilos actualizados (id $existe)\n";
} else {
    $wpdb->insert( $tabla, $fila );
    echo "[OK]    Estilos instalados (id " . $wpdb->insert_id . ")\n";
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( class_exists( '\Elementor\Plugin' ) ) {
    try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
}
echo "[OK]    Caches purgadas\n";

$res = wp_remote_get( home_url( '/?hvkt=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $body = (string) wp_remote_retrieve_body( $res );
    if ( false !== strpos( $body, 'hvkt-css' ) ) {
        echo "[OK]    Verificado: los estilos ya se aplican en la portada\n";
    } else {
        echo "[AVISO] Aun no aparecen en la portada (puede ser cache; revisar en unos minutos)\n";
    }
}
echo "Listo. Este script puede borrarse: rm -f hvkp-tarjetas.php\n";
echo "Para volver atras: php hvkp-tarjetas.php restore\n";
