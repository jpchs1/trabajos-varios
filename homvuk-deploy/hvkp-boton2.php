<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Corregir el enlace roto ?cat=car (hvkp-boton2)
 *
 * El boton "Ver Todos" de la seccion de vehiculos apunta a ?cat=car, una
 * categoria que no existe (la real es "automoviles"), por lo que el filtro se
 * ignora y la pagina lista tambien casas y departamentos.
 *
 * Los intentos anteriores buscaban el boton dentro de la seccion del carrusel,
 * pero en la portada el boton esta en la seccion del titulo y el carrusel en
 * la siguiente. Este corrige el enlace roto en cualquier parte de la pagina,
 * que ademas es seguro: solo toca enlaces que apuntan a una categoria
 * inexistente.
 *
 * Uso:  php hvkp-boton2.php            (aplicar)
 *       php hvkp-boton2.php restore    (deshacer)
 */

$HVKN_MALO = 'cat=car';
$HVKN_BIEN = 'cat=automoviles';

/** Corrige el enlace en todo el arbol. Devuelve la lista de cambios. */
function hvkn_corregir( &$nodo, $malo, $bien, &$hechos ) {
    if ( ! is_array( $nodo ) ) {
        return;
    }
    if ( isset( $nodo['settings']['link']['url'] ) && is_string( $nodo['settings']['link']['url'] ) ) {
        $antes = $nodo['settings']['link']['url'];
        // Solo si el parametro es exactamente cat=car (no cat=carX ni cat=cars)
        if ( preg_match( '/(\?|&)cat=car(?![a-z0-9_-])/i', $antes ) ) {
            $despues = preg_replace( '/(\?|&)cat=car(?![a-z0-9_-])/i', '$1' . $bien, $antes );
            $nodo['settings']['link']['url'] = $despues;
            $texto = isset( $nodo['settings']['text'] ) ? $nodo['settings']['text'] : 'enlace';
            $hechos[] = '"' . $texto . '": ' . $antes . ' -> ' . $despues;
        }
    }
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as &$h ) {
            hvkn_corregir( $h, $malo, $bien, $hechos );
        }
        unset( $h );
    }
}

if ( getenv( 'HVKN_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    // Estructura real: boton en la seccion 2, carrusel en la 3
    $arbol = array(
        array( 'id' => 's2', 'elType' => 'section', 'elements' => array(
            array( 'id' => 'c1', 'elType' => 'column', 'elements' => array(
                array( 'id' => 'h1', 'elType' => 'widget', 'widgetType' => 'entox_elementor_heading', 'settings' => array( 'title' => 'Vehiculos en Alquiler' ) ),
            ) ),
            array( 'id' => 'c2', 'elType' => 'column', 'elements' => array(
                array( 'id' => 'b1', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Ver Todos', 'link' => array( 'url' => 'https://homvuk.com/explorar/?cat=car', 'is_external' => '' ) ) ),
            ) ),
        ) ),
        array( 'id' => 's3', 'elType' => 'section', 'elements' => array(
            array( 'id' => 'c3', 'elType' => 'column', 'elements' => array(
                array( 'id' => 'w1', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_slider', 'settings' => array( 'categories' => 'automoviles' ) ),
            ) ),
        ) ),
        array( 'id' => 's4', 'elType' => 'section', 'elements' => array(
            array( 'id' => 'c4', 'elType' => 'column', 'elements' => array(
                array( 'id' => 'b2', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Formulario Propiedad', 'link' => array( 'url' => 'https://homvuk.com/administramos-tu-propiedad/' ) ) ),
                array( 'id' => 'b3', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Alojamientos', 'link' => array( 'url' => '/explorar/?cat=alojamientos' ) ) ),
                array( 'id' => 'b4', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Autos usados', 'link' => array( 'url' => '/explorar/?cat=cars' ) ) ),
            ) ),
        ) ),
    );

    $hechos = array();
    foreach ( $arbol as &$n ) { hvkn_corregir( $n, 'cat=car', 'cat=automoviles', $hechos ); }
    unset( $n );

    $b1 = $arbol[0]['elements'][1]['elements'][0]['settings'];
    $chk( 'corrige el boton aunque este en otra seccion', 'https://homvuk.com/explorar/?cat=automoviles' === $b1['link']['url'] );
    $chk( 'conserva texto y datos del enlace', 'Ver Todos' === $b1['text'] && isset( $b1['link']['is_external'] ) );
    $chk( 'un solo cambio', 1 === count( $hechos ) );
    $chk( 'no toca enlaces sin categoria', 'https://homvuk.com/administramos-tu-propiedad/' === $arbol[2]['elements'][0]['elements'][0]['settings']['link']['url'] );
    $chk( 'no toca otras categorias', '/explorar/?cat=alojamientos' === $arbol[2]['elements'][0]['elements'][1]['settings']['link']['url'] );
    $chk( 'no confunde cat=cars con cat=car', '/explorar/?cat=cars' === $arbol[2]['elements'][0]['elements'][2]['settings']['link']['url'] );
    $chk( 'no altera el carrusel', 'automoviles' === $arbol[1]['elements'][0]['elements'][0]['settings']['categories'] );

    $h2 = array();
    foreach ( $arbol as &$n2 ) { hvkn_corregir( $n2, 'cat=car', 'cat=automoviles', $h2 ); }
    unset( $n2 );
    $chk( 'idempotente', 0 === count( $h2 ) );

    // Variante con el parametro en medio de la URL
    $x = array( 'settings' => array( 'link' => array( 'url' => '/explorar/?orden=fecha&cat=car&pag=2' ) ) );
    $h3 = array();
    hvkn_corregir( $x, 'cat=car', 'cat=automoviles', $h3 );
    $chk( 'corrige el parametro en medio de la direccion', '/explorar/?orden=fecha&cat=automoviles&pag=2' === $x['settings']['link']['url'] );

    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';

$mode  = isset( $argv[1] ) ? $argv[1] : 'aplicar';
$front = (int) get_option( 'page_on_front' );

$purgar = function () use ( $front ) {
    delete_post_meta( $front, '_elementor_css' );
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
};

if ( 'restore' === $mode ) {
    $prev = get_option( 'hvkp_boton2_backup' );
    if ( $prev ) {
        update_post_meta( $front, '_elementor_data', wp_slash( $prev ) );
        echo "[OK]    Portada restaurada\n";
    } else {
        echo "[AVISO] No hay respaldo.\n";
    }
    $purgar();
    exit( 0 );
}

$json  = get_post_meta( $front, '_elementor_data', true );
$arbol = is_string( $json ) ? json_decode( $json, true ) : null;
if ( ! is_array( $arbol ) ) {
    echo "[ERROR] No se pudo leer la portada.\n";
    exit( 1 );
}

$hechos = array();
foreach ( $arbol as &$nodo ) {
    hvkn_corregir( $nodo, $HVKN_MALO, $HVKN_BIEN, $hechos );
}
unset( $nodo );

if ( ! $hechos ) {
    echo "[AVISO] No se encontro ningun enlace con ?cat=car (quiza ya esta corregido).\n";
    exit( 0 );
}

$nuevo = wp_json_encode( $arbol );
if ( ! is_string( $nuevo ) || null === json_decode( $nuevo, true ) ) {
    echo "[ERROR] La portada no se pudo reescribir de forma segura. Nada fue modificado.\n";
    exit( 1 );
}
update_option( 'hvkp_boton2_backup', $json, false );
update_post_meta( $front, '_elementor_data', wp_slash( $nuevo ) );
$purgar();

foreach ( $hechos as $h ) { echo "[OK]    $h\n"; }
echo "[OK]    Caches purgadas\n";

$res = wp_remote_get( home_url( '/?hvkn=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $body = (string) wp_remote_retrieve_body( $res );
    echo ( false !== strpos( $body, 'cat=automoviles' ) ? "[OK]    Verificado: el boton ya apunta a los automoviles\n" : "[AVISO] Aun no se ve el cambio en la portada (cache)\n" );
    if ( false !== strpos( $body, 'cat=car&' ) || false !== strpos( $body, 'cat=car"' ) ) {
        echo "[AVISO] Todavia queda algun enlace a ?cat=car\n";
    }
}
echo "Listo. Para volver atras: php hvkp-boton2.php restore\n";
