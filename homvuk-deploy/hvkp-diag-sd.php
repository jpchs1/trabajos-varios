<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Diagnostico del anuncio de San Damian (hvkp-diag-sd)
 *
 * La pagina https://homvuk.com/experiencia/exclusivo-departamento-en-san-damian-vitacura/
 * devuelve 500 y el producto tampoco aparece entre los reservables. Este script
 * no cambia nada: solo compara su configuracion con la de un anuncio que si
 * funciona (Las Condes) y muestra los errores completos del servidor.
 *
 * Uso:  php hvkp-diag-sd.php
 */

require __DIR__ . '/wp-load.php';
global $wpdb;

$SLUG_MALO  = 'exclusivo-departamento-en-san-damian-vitacura';
$SLUG_BUENO = 'hermoso-departamento-en-las-condes';

function hvksd_producto( $slug ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT ID, post_title, post_status, post_name FROM {$wpdb->posts}
         WHERE post_name = %s AND post_type = 'product' LIMIT 1",
        $slug
    ) );
}

$malo  = hvksd_producto( $SLUG_MALO );
$bueno = hvksd_producto( $SLUG_BUENO );

echo "== El producto ==\n";
if ( ! $malo ) {
    echo "  No existe ningun producto con el slug '$SLUG_MALO'.\n";
    echo "  Productos que se le parecen:\n";
    $otros = $wpdb->get_results(
        "SELECT ID, post_name, post_status FROM {$wpdb->posts}
         WHERE post_type = 'product' AND ( post_title LIKE '%Damian%' OR post_title LIKE '%Damián%' )" );
    foreach ( $otros as $o ) {
        echo "    id={$o->ID}  {$o->post_name}  ({$o->post_status})\n";
    }
} else {
    $tipo = wp_get_object_terms( $malo->ID, 'product_type', array( 'fields' => 'names' ) );
    echo "  id={$malo->ID}  estado={$malo->post_status}  tipo=" . ( $tipo ? implode( ',', $tipo ) : '(ninguno)' ) . "\n";
    echo "  titulo: {$malo->post_title}\n";
}
if ( $bueno ) {
    $tb = wp_get_object_terms( $bueno->ID, 'product_type', array( 'fields' => 'names' ) );
    echo "  referencia OK: id={$bueno->ID} tipo=" . ( $tb ? implode( ',', $tb ) : '(ninguno)' ) . "\n";
}

/* --- Comparacion de metas ------------------------------------------------ */

if ( $malo && $bueno ) {
    echo "\n== Configuracion: San Damian vs Las Condes ==\n";
    $claves = array(
        '_regular_price', '_price', '_stock_status', '_virtual',
        'ovabrw_price_type', 'ovabrw_define_1_day', 'ovabrw_number_vehicle',
        'ovabrw_regul_price_hour', 'ovabrw_rt_price', 'ovabrw_rt_price_hour',
        'ovabrw_rt_startdate', 'ovabrw_rt_enddate',
        'ovabrw_petime_label', 'ovabrw_petime_price', 'ovabrw_price_location',
        'ovabrw_daily_monday', 'ovabrw_enable_deposit', 'ovabrw_amount_deposit',
        'ovabrw_manage_store', 'ovabrw_untime_startdate', 'ovabrw_untime_enddate',
    );
    printf( "  %-26s | %-26s | %s\n", 'meta', 'San Damian', 'Las Condes' );
    printf( "  %s\n", str_repeat( '-', 78 ) );
    foreach ( $claves as $k ) {
        $a = get_post_meta( $malo->ID, $k, true );
        $b = get_post_meta( $bueno->ID, $k, true );
        $fa = is_array( $a ) ? 'array(' . count( $a ) . ')' : ( '' === $a ? '(vacio)' : mb_substr( (string) $a, 0, 24 ) );
        $fb = is_array( $b ) ? 'array(' . count( $b ) . ')' : ( '' === $b ? '(vacio)' : mb_substr( (string) $b, 0, 24 ) );
        $marca = ( $fa === $fb ) ? '  ' : '* ';
        printf( "%s%-26s | %-26s | %s\n", $marca, $k, $fa, $fb );
    }
    echo "  (* = donde difieren)\n";

    // Metas de ova-brw que tiene uno y no el otro
    $ma = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE 'ovabrw%%'", $malo->ID ) );
    $mb = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE 'ovabrw%%'", $bueno->ID ) );
    $solo_b = array_diff( $mb, $ma );
    $solo_a = array_diff( $ma, $mb );
    if ( $solo_b ) { echo "\n  Le faltan a San Damian: " . implode( ', ', $solo_b ) . "\n"; }
    if ( $solo_a ) { echo "  Solo tiene San Damian:  " . implode( ', ', $solo_a ) . "\n"; }
}

/* --- Que pasa al cotizarlo ----------------------------------------------- */

if ( $malo && function_exists( 'get_price_by_date' ) ) {
    echo "\n== Cotizacion de prueba ==\n";
    $in  = strtotime( '+14 days' );
    $out = strtotime( '+21 days' );
    foreach ( array( 'San Damian' => $malo->ID, 'Las Condes' => $bueno ? $bueno->ID : 0 ) as $et => $pid ) {
        if ( ! $pid ) { continue; }
        try {
            $t = get_price_by_date( $pid, $in, $out, array(
                'product_id' => $pid, 'ovabrw_number_vehicle' => 1, 'resources' => array(),
                'ovabrw_service' => '[]', 'period_price' => '', 'ova_type_deposit' => '',
            ) );
            echo "  $et: line_total = " . ( isset( $t['line_total'] ) ? $t['line_total'] : '?' ) . "\n";
        } catch ( \Throwable $e ) {
            echo "  $et: " . get_class( $e ) . ' -> ' . $e->getMessage() . "\n";
            echo "     en " . $e->getFile() . ':' . $e->getLine() . "\n";
        }
    }
}

/* --- Errores completos del servidor -------------------------------------- */

echo "\n== Errores fatales (completos) ==\n";
$log = ABSPATH . 'error_log';
if ( file_exists( $log ) && is_readable( $log ) ) {
    $lineas = @file( $log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    $fat = array_values( array_filter( (array) $lineas, function ( $l ) {
        return stripos( $l, 'ArgumentCountError' ) !== false
            || stripos( $l, 'TypeError' ) !== false
            || stripos( $l, 'Fatal error' ) !== false;
    } ) );
    foreach ( array_slice( $fat, -8 ) as $f ) {
        echo '  ' . trim( $f ) . "\n\n";
    }
    if ( ! $fat ) { echo "  (sin errores fatales)\n"; }
} else {
    echo "  No se pudo leer $log\n";
}

/* --- Unidades del portal, para enlazarlas -------------------------------- */

echo "\n== Unidades del portal y su anuncio de Airbnb ==\n";
$uds = $wpdb->get_results( "SELECT id, name, slug, listing_id, airbnb_listing_id FROM {$wpdb->prefix}portal_units WHERE active = 1" );
foreach ( $uds as $u ) {
    echo "  {$u->slug}  (airbnb_listing_id={$u->airbnb_listing_id})  listing_id="
        . ( $u->listing_id ? $u->listing_id : 'vacio' ) . "\n";
}
echo "\n  Productos de alojamiento disponibles para enlazar:\n";
$prods = $wpdb->get_results(
    "SELECT ID, post_name, post_title FROM {$wpdb->posts}
     WHERE post_type = 'product' AND post_status = 'publish' ORDER BY post_title" );
foreach ( $prods as $p ) {
    $d1 = get_post_meta( $p->ID, 'ovabrw_define_1_day', true );
    if ( 'hotel' !== $d1 && ! preg_match( '/damian|dehesa|condes|colbun|pucon/i', $p->post_title ) ) { continue; }
    echo sprintf( "    %-46s %s\n", mb_substr( $p->post_name, 0, 44 ), 'define_1_day=' . ( $d1 ? $d1 : '(vacio)' ) );
}

echo "\nEste script no modifico nada. Puede borrarse: rm -f hvkp-diag-sd.php\n";
