<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Arreglo del anuncio de San Damian (hvkp-arreglo-sd)
 *
 * Dos cosas, ambas reversibles:
 *
 * 1. La pagina del anuncio devolvia 500. La causa esta en el tema: imprime la
 *    descripcion con printf() en vez de echo
 *    (entox/woocommerce/single-product/loop/description.php linea 21), asi que
 *    un signo de porcentaje en el texto -por ejemplo "100 por ciento equipado"
 *    escrito con el simbolo- lo lee como marcador de formato y revienta. Se
 *    instala un snippet que define la funcion del tema antes que el tema, sin
 *    tocar ningun archivo. Esto arregla TODOS los anuncios, no solo San Damian.
 *
 * 2. San Damian estaba cobrado "por dia" y no "por noche" como los otros cinco
 *    departamentos: 7 noches se facturaban como 8 dias. Se corrige
 *    ovabrw_define_1_day a 'hotel', que ademas es lo que lo hace aparecer en
 *    /reserva-directa/.
 *
 * Uso:  php hvkp-arreglo-sd.php            (aplicar)
 *       php hvkp-arreglo-sd.php restore    (deshacer las dos cosas)
 */

$HVKSD_B64  = 'LyoqCiAqIEhPTVZVSyAtIEFycmVnbG8gZGUgbGEgZGVzY3JpcGNpb24gZGVsIHByb2R1Y3RvCiAqCiAqIEVsIHRlbWEgZW50b3ggaW1wcmltZSBsYSBkZXNjcmlwY2lvbiBjb24gYHByaW50ZiggJGRlc2NyaXB0aW9uIClgIGVuIHZleiBkZQogKiBgZWNob2AgKHRoZW1lcy9lbnRveC93b29jb21tZXJjZS9zaW5nbGUtcHJvZHVjdC9sb29wL2Rlc2NyaXB0aW9uLnBocDoyMSkuIFNpCiAqIGxhIGRlc2NyaXBjaW9uIHRyYWUgdW4gYCVgIC1wb3IgZWplbXBsbyAiMTAwJSBlcXVpcGFkbyItIFBIUCBsbyBsZWUgY29tbyB1bgogKiBtYXJjYWRvciBkZSBmb3JtYXRvLCBubyBlbmN1ZW50cmEgZWwgYXJndW1lbnRvIHkgbGEgcGFnaW5hIGRlbCBhbnVuY2lvIG11ZXJlCiAqIGNvbiBgQXJndW1lbnRDb3VudEVycm9yOiAyIGFyZ3VtZW50cyBhcmUgcmVxdWlyZWQsIDEgZ2l2ZW5gLiBFcyBsbyBxdWUgZXN0YWJhCiAqIHRpcmFuZG8gYWJham8gZWwgYW51bmNpbyBkZSBTYW4gRGFtaWFuLgogKgogKiBMYSBmdW5jaW9uIGRlbCB0ZW1hIGVzdGEgZW52dWVsdGEgZW4gYGlmICggISBmdW5jdGlvbl9leGlzdHMoKSApYCB5IENvZGUKICogU25pcHBldHMgY29ycmUgZW4gYHBsdWdpbnNfbG9hZGVkYCAocHJpb3JpZGFkIDEpLCBhbnRlcyBkZSBxdWUgc2UgY2FyZ3VlIGVsCiAqIGZ1bmN0aW9ucy5waHAgZGVsIHRlbWEuIERlZmluaWVuZG9sYSBhY2EsIGVsIHRlbWEgc2Ugc2FsdGEgbGEgc3V5YSB5IG51bmNhCiAqIGxsZWdhIGEgZWplY3V0YXIgZWwgcHJpbnRmLiBObyBzZSB0b2NhIG5pbmd1biBhcmNoaXZvIGRlbCB0ZW1hLCBhc2kgcXVlCiAqIHRhbXBvY28gc2UgcGllcmRlIGVuIHVuYSBhY3R1YWxpemFjaW9uLgogKi8KCmlmICggISBmdW5jdGlvbl9leGlzdHMoICdlbnRveF93Y190ZW1wbGF0ZV9wcm9kdWN0X2Rlc2NyaXB0aW9uJyApICkgewogICAgZnVuY3Rpb24gZW50b3hfd2NfdGVtcGxhdGVfcHJvZHVjdF9kZXNjcmlwdGlvbigpIHsKICAgICAgICBnbG9iYWwgJHBvc3Q7CiAgICAgICAgaWYgKCAhICRwb3N0ICkgewogICAgICAgICAgICByZXR1cm47CiAgICAgICAgfQoKICAgICAgICAkZGVzY3JpcGNpb24gPSBhcHBseV9maWx0ZXJzKCAndGhlX2NvbnRlbnQnLCAkcG9zdC0+cG9zdF9jb250ZW50ICk7CgogICAgICAgIGlmICggISB0cmltKCB3cF9zdHJpcF9hbGxfdGFncyggJGRlc2NyaXBjaW9uICkgKSApIHsKICAgICAgICAgICAgcmV0dXJuOwogICAgICAgIH0KICAgICAgICA/PgogICAgICAgIDxkaXYgY2xhc3M9ImVudG94LXByb2R1Y3QtZGVzY3JpcHRpb24iPgogICAgICAgICAgICA8aDIgY2xhc3M9InRpdGxlLWRlc2NyaXB0aW9uIj48P3BocCBlY2hvIGVzY19odG1sX18oICdEZXNjcmlwdGlvbicsICdlbnRveCcgKTsgPz48L2gyPgogICAgICAgICAgICA8P3BocCBlY2hvICRkZXNjcmlwY2lvbjsgLy8geWEgcGFzbyBwb3IgbG9zIGZpbHRyb3MgZGUgdGhlX2NvbnRlbnQgPz4KICAgICAgICA8L2Rpdj4KICAgICAgICA8ZGl2IGNsYXNzPSJlbnRveC1saW5lIj48L2Rpdj4KICAgICAgICA8P3BocAogICAgfQp9Cg==';
$HVKSD_MD5  = 'f29595a2c2baac6d1ab5f11a9bf95157';
$HVKSD_NAME = 'HOMVUK Arreglo descripcion';
$HVKSD_SLUG = 'exclusivo-departamento-en-san-damian-vitacura';

require __DIR__ . '/wp-load.php';
global $wpdb;

$tabla = $wpdb->prefix . 'snippets';
$mode  = isset( $argv[1] ) ? $argv[1] : 'aplicar';

$prod = $wpdb->get_row( $wpdb->prepare(
    "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' LIMIT 1",
    $HVKSD_SLUG ) );

if ( 'restore' === $mode ) {
    $wpdb->update( $tabla, array( 'active' => 0 ), array( 'name' => $HVKSD_NAME ) );
    if ( $prod ) {
        $antes = get_option( 'hvksd_define_antes' );
        update_post_meta( $prod->ID, 'ovabrw_define_1_day', $antes ? $antes : 'day' );
        clean_post_cache( (int) $prod->ID );
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Deshecho: el snippet queda inactivo y San Damian vuelve a cobrarse por dia.\n";
    exit( 0 );
}
if ( 'aplicar' !== $mode ) {
    echo "[ERROR] Argumento no reconocido: $mode. Usa 'aplicar' o 'restore'.\n";
    exit( 1 );
}

$code = base64_decode( $HVKSD_B64 );
if ( md5( $code ) !== $HVKSD_MD5 ) {
    echo "[ERROR] El contenido no paso la verificacion. Nada fue modificado.\n";
    exit( 1 );
}
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabla ) ) !== $tabla ) {
    echo "[ERROR] No existe la tabla de snippets (plugin Code Snippets).\n";
    exit( 1 );
}

$ok = array(); $warn = array();

/* --- 1. El arreglo del tema --------------------------------------------- */

$fila = array(
    'name'        => $HVKSD_NAME,
    'description' => 'Reemplaza el printf() de la descripcion del tema entox por echo: un simbolo de porcentaje en el texto tiraba abajo la pagina del anuncio.',
    'code'        => $code,
    'tags'        => 'homvuk',
    'scope'       => 'front-end',
    'priority'    => 1,
    'active'      => 1,
    'modified'    => current_time( 'mysql' ),
);
$existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKSD_NAME ) );
if ( $existe ) {
    $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
    $ok[] = "Arreglo de la descripcion actualizado (snippet id $existe)";
} else {
    $wpdb->insert( $tabla, $fila );
    $ok[] = 'Arreglo de la descripcion instalado (snippet id ' . $wpdb->insert_id . ')';
}

/* --- 2. San Damian, por noche ------------------------------------------- */

if ( ! $prod ) {
    $warn[] = "No se encontro el producto $HVKSD_SLUG; no se toco la forma de cobro.";
} else {
    $antes = get_post_meta( $prod->ID, 'ovabrw_define_1_day', true );
    if ( 'hotel' === $antes ) {
        $ok[] = 'San Damian ya estaba cobrado por noche';
    } else {
        if ( false === get_option( 'hvksd_define_antes' ) ) {
            update_option( 'hvksd_define_antes', $antes ? $antes : 'day', false );
        }
        update_post_meta( $prod->ID, 'ovabrw_define_1_day', 'hotel' );
        clean_post_cache( (int) $prod->ID );
        if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $prod->ID ); }
        $ok[] = "San Damian pasa de cobrarse '$antes' a 'hotel' (por noche)";
    }

    $precio = (float) get_post_meta( $prod->ID, '_regular_price', true );
    $tasa   = (float) get_option( 'tasaCLP', 0 );
    if ( $tasa <= 0 ) { $tasa = 816.0; }
    echo "\n== Efecto en el precio ==\n";
    echo '  Tarifa: USD ' . number_format( $precio, 2, ',', '.' )
        . ' por noche (~$' . number_format( round( $precio * $tasa ), 0, ',', '.' ) . " CLP)\n";
    echo '  7 noches: antes USD ' . number_format( $precio * 8, 2, ',', '.' )
        . ' (8 dias) -> ahora USD ' . number_format( $precio * 7, 2, ',', '.' ) . " (7 noches)\n";

    $daily = get_post_meta( $prod->ID, 'ovabrw_daily_monday', true );
    if ( '' !== $daily ) {
        $warn[] = "San Damian tiene precio por dia de la semana cargado solo para el lunes ($daily). "
            . 'Los otros cinco no lo usan. Como coincide con la tarifa normal no cambia nada hoy, '
            . 'pero conviene vaciarlo en el producto para que no sorprenda mas adelante.';
    }
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
$ok[] = 'Caches purgadas';

/* --- 3. Comprobacion en vivo --------------------------------------------- */

echo "\n== Comprobacion ==\n";
$urls = array(
    'Anuncio de San Damian' => home_url( '/experiencia/' . $HVKSD_SLUG . '/' ),
    'Reserva directa'       => home_url( '/reserva-directa/?casa=' . $HVKSD_SLUG ),
);
foreach ( $urls as $et => $url ) {
    $sep = ( false !== strpos( $url, '?' ) ) ? '&' : '?';
    $res = wp_remote_get( $url . $sep . 'v=' . time(), array( 'timeout' => 45, 'sslverify' => false ) );
    if ( is_wp_error( $res ) ) {
        echo '  ' . str_pad( $et, 24 ) . 'no se pudo comprobar (' . $res->get_error_message() . ")\n";
        continue;
    }
    $cod = (int) wp_remote_retrieve_response_code( $res );
    echo '  ' . str_pad( $et, 24 ) . 'HTTP ' . $cod . ( 200 === $cod ? '  OK' : '  <-- revisar' ) . "\n";
    if ( 200 !== $cod ) {
        $warn[] = "$et sigue respondiendo $cod.";
    }
}

echo "\n== HOMVUK - Arreglo de San Damian ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "\nPara deshacer: php hvkp-arreglo-sd.php restore\n";
