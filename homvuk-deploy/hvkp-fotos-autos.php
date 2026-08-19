<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Fotos de los autos con fondo uniforme (hvkp-fotos-autos)
 *
 * Instala las fotos editadas: cada auto recortado de su fondo original y
 * montado sobre el mismo fondo claro de marca, con sombra bajo las ruedas y
 * el mismo encuadre, para que la seccion de vehiculos se vea pareja.
 * Al Porsche se le puso la patente HOMVUK; el MG ZS ya la traia.
 *
 * Las fotos originales NO se borran: quedan en la biblioteca por si quieres
 * volver atras, y este script tiene modo restore.
 *
 * Uso:  php hvkp-fotos-autos.php            (instalar)
 *       php hvkp-fotos-autos.php restore    (volver a las fotos anteriores)
 */

$HVKF_BASE  = 'https://raw.githubusercontent.com/jpchs1/trabajos-varios/main/homvuk-deploy/autos/';
$HVKF_FOTOS = array(
    'porsche-cayenne-2018' => array( 'archivo' => 'porsche-cayenne-2018.jpg', 'md5' => 'eb66803710aee57bceb2c75bde3c0e26', 'alt' => 'Arriendo Porsche Cayenne 2018 en Santiago - HOMVUK' ),
    'mg3-2019'             => array( 'archivo' => 'mg3-2019.jpg',             'md5' => '8247686e5639632326038f2a79d0a9bc', 'alt' => 'Arriendo MG3 2019 en Santiago - HOMVUK' ),
    'new-mg-zs-2026'       => array( 'archivo' => 'new-mg-zs-2026.jpg',       'md5' => '4346153c378f63fdd3f17d2613fbaa1b', 'alt' => 'Arriendo MG ZS 2026 plateado en Santiago - HOMVUK' ),
);

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode = isset( $argv[1] ) ? $argv[1] : 'instalar';
$ok = array(); $warn = array();

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_fotos_backup' );
    if ( ! is_array( $b ) || ! $b ) {
        echo "[AVISO] No hay respaldo.\n";
        exit( 0 );
    }
    foreach ( $b as $pid => $antes ) {
        if ( $antes ) {
            set_post_thumbnail( (int) $pid, (int) $antes );
        } else {
            delete_post_thumbnail( (int) $pid );
        }
        clean_post_cache( (int) $pid );
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Fotos anteriores restauradas (" . count( $b ) . " autos)\n";
    exit( 0 );
}

require_once ABSPATH . 'wp-admin/includes/image.php';
$updir  = wp_upload_dir();
$backup = get_option( 'hvkp_fotos_backup', array() );
if ( ! is_array( $backup ) ) { $backup = array(); }

foreach ( $HVKF_FOTOS as $slug => $info ) {
    $prod = $wpdb->get_row( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' AND post_status != 'trash' LIMIT 1",
        $slug
    ) );
    if ( ! $prod ) {
        $warn[] = "$slug: no se encontro el producto";
        continue;
    }

    $res = wp_remote_get( $HVKF_BASE . $info['archivo'], array( 'timeout' => 90, 'sslverify' => false ) );
    if ( is_wp_error( $res ) ) {
        $warn[] = "$slug: no se pudo descargar la foto (" . $res->get_error_message() . ")";
        continue;
    }
    $bytes = wp_remote_retrieve_body( $res );
    if ( md5( $bytes ) !== $info['md5'] ) {
        $warn[] = "$slug: la foto descargada no coincide con la esperada; se omite";
        continue;
    }

    $destino = trailingslashit( $updir['path'] ) . $info['archivo'];
    file_put_contents( $destino, $bytes );

    $att = wp_insert_attachment( array(
        'post_mime_type' => 'image/jpeg',
        'post_title'     => $info['alt'],
        'post_status'    => 'inherit',
    ), $destino, (int) $prod->ID );
    wp_update_attachment_metadata( $att, wp_generate_attachment_metadata( $att, $destino ) );
    update_post_meta( $att, '_wp_attachment_image_alt', $info['alt'] );

    if ( ! isset( $backup[ (int) $prod->ID ] ) ) {
        $backup[ (int) $prod->ID ] = (int) get_post_thumbnail_id( (int) $prod->ID );
    }
    set_post_thumbnail( (int) $prod->ID, (int) $att );
    clean_post_cache( (int) $prod->ID );
    if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $prod->ID ); }

    $ok[] = $slug . ': foto nueva instalada (' . round( strlen( $bytes ) / 1024 ) . ' KB)';
}

if ( $backup ) {
    update_option( 'hvkp_fotos_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( class_exists( '\Elementor\Plugin' ) ) {
    try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
}
$ok[] = 'Caches purgadas';

// Datos del Jaguar, para poder editar su foto tambien
$jag = $wpdb->get_row( "SELECT ID FROM {$wpdb->posts} WHERE post_name = 'jaguar-xf-2012-3-0cc-automatico' AND post_type = 'product' LIMIT 1" );
if ( $jag ) {
    $tid = get_post_thumbnail_id( (int) $jag->ID );
    if ( $tid ) {
        echo "\n== Jaguar (pendiente de editar) ==\n";
        echo "   " . wp_get_attachment_url( $tid ) . "\n";
    }
}

echo "\n== HOMVUK - Fotos de los autos ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Para volver a las fotos anteriores: php hvkp-fotos-autos.php restore\n";
