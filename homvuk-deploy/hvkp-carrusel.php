<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Carrusel de vehiculos en la portada (hvkp-carrusel)
 *
 * Convierte la seccion "Vehiculos en Alquiler" de la portada, hoy una grilla
 * fija de 3 autos, en el carrusel de productos del tema: muestra todos los
 * autos del catalogo y se navega con flechas y puntos. La seccion de
 * alojamientos NO se toca.
 *
 * Uso:  php hvkp-carrusel.php            (aplicar)
 *       php hvkp-carrusel.php restore    (volver a la grilla)
 */

$HVKR_DE   = 'entox_elementor_product_list';
$HVKR_A    = 'entox_elementor_product_slider';
$HVKR_CAT  = 'automoviles';

/**
 * Recorre el arbol de Elementor y convierte el widget de listado de la
 * categoria indicada en carrusel. Devuelve la cantidad de widgets tocados.
 */
function hvkr_convertir( &$nodo, $de, $a, $cat, &$tocados, &$detalle ) {
    if ( ! is_array( $nodo ) ) {
        return;
    }
    if ( isset( $nodo['elType'], $nodo['widgetType'] ) && 'widget' === $nodo['elType'] && $de === $nodo['widgetType'] ) {
        $set = isset( $nodo['settings'] ) && is_array( $nodo['settings'] ) ? $nodo['settings'] : array();
        $suya = isset( $set['categories'] ) ? $set['categories'] : '';
        if ( $suya === $cat ) {
            $nuevo = array(
                'categories'       => $cat,
                'posts_per_page'   => 12,
                'orderby'          => isset( $set['orderby'] ) ? $set['orderby'] : 'date',
                'order'            => isset( $set['order'] ) ? $set['order'] : 'DESC',
                'item_number'      => 3,
                'slides_to_scroll' => 1,
                'margin_items'     => 30,
                'infinite'         => 'yes',
                'autoplay'         => '',
                'pause_on_hover'   => 'yes',
                'nav_control'      => 'yes',
                'dot_control'      => 'yes',
            );
            foreach ( array( 'show_featured', 'category_in', 'category_not_in', 'target' ) as $k ) {
                if ( isset( $set[ $k ] ) && '' !== $set[ $k ] ) {
                    $nuevo[ $k ] = $set[ $k ];
                }
            }
            $nodo['widgetType'] = $a;
            $nodo['settings']   = $nuevo;
            $tocados++;
            $detalle[] = isset( $nodo['id'] ) ? $nodo['id'] : '?';
            return;
        }
    }
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as &$hijo ) {
            hvkr_convertir( $hijo, $de, $a, $cat, $tocados, $detalle );
        }
        unset( $hijo );
    }
}

if ( getenv( 'HVKR_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $arbol = array( array( 'id' => 'sec1', 'elType' => 'container', 'elements' => array(
        array( 'id' => 'w1', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_list',
               'settings' => array( 'categories' => 'alojamientos', 'columns' => 'column3' ) ),
        array( 'id' => 'w2', 'elType' => 'widget', 'widgetType' => 'entox_elementor_heading',
               'settings' => array( 'title' => 'Vehiculos en Alquiler' ) ),
        array( 'id' => 'w3', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_list',
               'settings' => array( 'categories' => 'automoviles', 'columns' => 'column3', 'orderby' => 'date', 'target' => 'yes' ) ),
    ) ) );
    $t = 0; $d = array();
    foreach ( $arbol as &$n ) { hvkr_convertir( $n, 'entox_elementor_product_list', 'entox_elementor_product_slider', 'automoviles', $t, $d ); }
    unset( $n );
    $hijos = $arbol[0]['elements'];
    $chk( 'convierte exactamente un widget', 1 === $t && array( 'w3' ) === $d );
    $chk( 'el de autos quedo como carrusel', 'entox_elementor_product_slider' === $hijos[2]['widgetType'] );
    $chk( 'el de alojamientos NO se toco', 'entox_elementor_product_list' === $hijos[0]['widgetType'] && 'alojamientos' === $hijos[0]['settings']['categories'] );
    $chk( 'el titulo de la seccion sigue igual', 'entox_elementor_heading' === $hijos[1]['widgetType'] );
    $chk( 'mantiene la categoria y el id del widget', 'automoviles' === $hijos[2]['settings']['categories'] && 'w3' === $hijos[2]['id'] );
    $chk( 'trae mas autos que la grilla', 12 === $hijos[2]['settings']['posts_per_page'] );
    $chk( 'navegacion con flechas y puntos, sin autoplay', 'yes' === $hijos[2]['settings']['nav_control'] && 'yes' === $hijos[2]['settings']['dot_control'] && '' === $hijos[2]['settings']['autoplay'] );
    $chk( 'conserva opciones propias del original', 'yes' === $hijos[2]['settings']['target'] );
    $chk( 'descarta opciones de la grilla que ya no aplican', ! isset( $hijos[2]['settings']['columns'] ) );

    // idempotencia: al reejecutar no encuentra listados de autos
    $t2 = 0; $d2 = array();
    foreach ( $arbol as &$n2 ) { hvkr_convertir( $n2, 'entox_elementor_product_list', 'entox_elementor_product_slider', 'automoviles', $t2, $d2 ); }
    unset( $n2 );
    $chk( 'idempotente: segunda pasada no toca nada', 0 === $t2 );

    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';

$mode  = isset( $argv[1] ) ? $argv[1] : 'aplicar';
$front = (int) get_option( 'page_on_front' );
if ( ! $front ) {
    echo "[ERROR] No hay pagina de portada configurada.\n";
    exit( 1 );
}

$purgar = function () use ( $front ) {
    delete_post_meta( $front, '_elementor_css' );
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
};

if ( 'restore' === $mode ) {
    $prev = get_option( 'hvkp_carrusel_backup' );
    if ( ! $prev ) {
        echo "[AVISO] No hay respaldo.\n";
        exit( 0 );
    }
    update_post_meta( $front, '_elementor_data', wp_slash( $prev ) );
    $purgar();
    echo "[OK]    Portada restaurada a la version anterior (grilla)\n";
    exit( 0 );
}

$json = get_post_meta( $front, '_elementor_data', true );
$arbol = is_string( $json ) ? json_decode( $json, true ) : null;
if ( ! is_array( $arbol ) ) {
    echo "[ERROR] No se pudo leer la portada.\n";
    exit( 1 );
}

$tocados = 0; $detalle = array();
foreach ( $arbol as &$nodo ) {
    hvkr_convertir( $nodo, $HVKR_DE, $HVKR_A, $HVKR_CAT, $tocados, $detalle );
}
unset( $nodo );

if ( ! $tocados ) {
    echo "[AVISO] No se encontro la grilla de vehiculos (quiza ya es carrusel). Nada fue modificado.\n";
    exit( 0 );
}

$nuevo = wp_json_encode( $arbol );
if ( ! is_string( $nuevo ) || null === json_decode( $nuevo, true ) ) {
    echo "[ERROR] La portada no se pudo reescribir de forma segura. Nada fue modificado.\n";
    exit( 1 );
}

update_option( 'hvkp_carrusel_backup', $json, false );
update_post_meta( $front, '_elementor_data', wp_slash( $nuevo ) );
$purgar();

echo "== HOMVUK - Carrusel de vehiculos ==\n";
echo "[OK]    Seccion de vehiculos convertida en carrusel (widget " . implode( ', ', $detalle ) . ")\n";
echo "[OK]    Muestra 3 autos a la vez, con flechas y puntos, sin movimiento automatico\n";
echo "[OK]    La seccion de alojamientos quedo igual\n";
echo "[OK]    Caches purgadas\n";

$res = wp_remote_get( home_url( '/?hvkr=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $body = (string) wp_remote_retrieve_body( $res );
    if ( false !== strpos( $body, 'product_slider' ) || false !== strpos( $body, 'owl-carousel' ) || false !== strpos( $body, 'slick' ) ) {
        echo "[OK]    Verificado: el carrusel ya se muestra en la portada\n";
    } else {
        echo "[AVISO] El carrusel aun no aparece en el HTML (puede ser cache; revisar en unos minutos)\n";
    }
    foreach ( array( 'new-mg-zs-2026' => 'MG ZS', 'mg3-2019' => 'MG3', 'porsche-cayenne-2018' => 'Porsche', 'jaguar-xf' => 'Jaguar' ) as $slug => $nombre ) {
        if ( false !== strpos( $body, $slug ) ) { echo "[OK]    En el carrusel: $nombre\n"; }
    }
    if ( false !== strpos( $body, 'audi-a7' ) ) {
        echo "[AVISO] El Audi A7 sigue apareciendo en la portada\n";
    }
}
echo "Para volver atras: php hvkp-carrusel.php restore\n";
