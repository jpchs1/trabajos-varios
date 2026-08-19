<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Boton "Ver Todos" de la seccion de vehiculos (hvkp-boton-autos)
 *
 * El boton apuntaba a ?cat=car, una categoria que no existe en el sitio (la
 * real es "automoviles"), asi que el filtro se ignoraba y la pagina mostraba
 * tambien casas y departamentos.
 *
 * El intento anterior no lo alcanzo: buscaba el boton entre los hijos directos
 * del contenedor del carrusel, pero en la portada esta en otra rama de la
 * misma seccion. Ahora se busca en toda la seccion que contiene el carrusel,
 * y solo se tocan botones cuyo enlace apunte al explorador o cuyo texto sea
 * del tipo "Ver Todos".
 *
 * Uso:  php hvkp-boton-autos.php            (aplicar)
 *       php hvkp-boton-autos.php restore    (deshacer)
 */

$HVKB_CAT = 'automoviles';
$HVKB_URL = '/explorar/?cat=automoviles';

/** Dice si dentro de esta rama esta el carrusel (o listado) de la categoria. */
function hvkb_contiene( $nodo, $cat ) {
    if ( ! is_array( $nodo ) ) {
        return false;
    }
    if ( isset( $nodo['widgetType'] )
        && in_array( $nodo['widgetType'], array( 'entox_elementor_product_slider', 'entox_elementor_product_list' ), true )
        && isset( $nodo['settings']['categories'] ) && $cat === $nodo['settings']['categories'] ) {
        return true;
    }
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as $h ) {
            if ( hvkb_contiene( $h, $cat ) ) {
                return true;
            }
        }
    }
    return false;
}

/** Corrige, en toda la rama, el enlace de los botones que llevan al explorador. */
function hvkb_corregir( &$nodo, $url, &$hechos ) {
    if ( ! is_array( $nodo ) ) {
        return;
    }
    if ( isset( $nodo['elType'], $nodo['widgetType'] ) && 'widget' === $nodo['elType'] && 'button' === $nodo['widgetType'] ) {
        $set   = isset( $nodo['settings'] ) && is_array( $nodo['settings'] ) ? $nodo['settings'] : array();
        $antes = isset( $set['link']['url'] ) ? (string) $set['link']['url'] : '';
        $texto = isset( $set['text'] ) ? (string) $set['text'] : '';
        $es_ver_todos = ( false !== stripos( $antes, 'explorar' ) )
            || preg_match( '/ver\s*todos?|view\s*all|ver\s*m[aá]s/i', $texto );
        if ( $es_ver_todos && $antes !== $url ) {
            if ( ! isset( $nodo['settings'] ) || ! is_array( $nodo['settings'] ) ) {
                $nodo['settings'] = array();
            }
            $link = isset( $nodo['settings']['link'] ) && is_array( $nodo['settings']['link'] ) ? $nodo['settings']['link'] : array();
            $link['url'] = $url;
            $nodo['settings']['link'] = $link;
            $hechos[] = 'boton "' . ( $texto ? $texto : 'sin texto' ) . '": ' . ( $antes ? $antes : '(vacio)' ) . ' -> ' . $url;
        }
        return;
    }
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as &$h ) {
            hvkb_corregir( $h, $url, $hechos );
        }
        unset( $h );
    }
}

if ( getenv( 'HVKB_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    // Estructura como la real: el boton esta en OTRA rama de la misma seccion
    $arbol = array(
        array( 'id' => 'secAloj', 'elType' => 'container', 'elements' => array(
            array( 'id' => 'cA', 'elType' => 'container', 'elements' => array(
                array( 'id' => 'bA', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Ver Todos', 'link' => array( 'url' => '/explorar/?cat=alojamientos' ) ) ),
            ) ),
            array( 'id' => 'wA', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_list', 'settings' => array( 'categories' => 'alojamientos' ) ),
        ) ),
        array( 'id' => 'secAutos', 'elType' => 'container', 'elements' => array(
            array( 'id' => 'cB', 'elType' => 'container', 'elements' => array(
                array( 'id' => 'hB', 'elType' => 'widget', 'widgetType' => 'entox_elementor_heading', 'settings' => array( 'title' => 'Vehiculos en Alquiler' ) ),
                array( 'id' => 'bB', 'elType' => 'widget', 'widgetType' => 'button', 'settings' => array( 'text' => 'Ver Todos', 'link' => array( 'url' => '/explorar/?cat=car', 'is_external' => '' ) ) ),
            ) ),
            array( 'id' => 'cC', 'elType' => 'container', 'elements' => array(
                array( 'id' => 'wB', 'elType' => 'widget', 'widgetType' => 'entox_elementor_product_slider', 'settings' => array( 'categories' => 'automoviles' ) ),
            ) ),
        ) ),
    );

    $hechos = array();
    foreach ( $arbol as &$sec ) {
        if ( hvkb_contiene( $sec, 'automoviles' ) ) {
            hvkb_corregir( $sec, '/explorar/?cat=automoviles', $hechos );
        }
    }
    unset( $sec );

    $boton_autos = $arbol[1]['elements'][0]['elements'][1]['settings'];
    $boton_aloj  = $arbol[0]['elements'][0]['elements'][0]['settings'];
    $chk( 'encuentra el boton aunque este en otra rama', 1 === count( $hechos ) );
    $chk( 'boton de autos corregido', '/explorar/?cat=automoviles' === $boton_autos['link']['url'] );
    $chk( 'conserva texto y demas datos del enlace', 'Ver Todos' === $boton_autos['text'] && isset( $boton_autos['link']['is_external'] ) );
    $chk( 'NO toca el boton de la seccion de alojamientos', '/explorar/?cat=alojamientos' === $boton_aloj['link']['url'] );
    $chk( 'no altera los widgets de productos', 'automoviles' === $arbol[1]['elements'][1]['elements'][0]['settings']['categories'] );

    $h2 = array();
    foreach ( $arbol as &$s2 ) {
        if ( hvkb_contiene( $s2, 'automoviles' ) ) { hvkb_corregir( $s2, '/explorar/?cat=automoviles', $h2 ); }
    }
    unset( $s2 );
    $chk( 'idempotente: no vuelve a cambiar nada', 0 === count( $h2 ) );

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
    $prev = get_option( 'hvkp_boton_backup' );
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
foreach ( $arbol as &$sec ) {
    if ( hvkb_contiene( $sec, $HVKB_CAT ) ) {
        hvkb_corregir( $sec, $HVKB_URL, $hechos );
    }
}
unset( $sec );

if ( ! $hechos ) {
    echo "[AVISO] No se encontro ningun boton por corregir (quiza ya estaba bien).\n";
    exit( 0 );
}

$nuevo = wp_json_encode( $arbol );
if ( ! is_string( $nuevo ) || null === json_decode( $nuevo, true ) ) {
    echo "[ERROR] La portada no se pudo reescribir de forma segura. Nada fue modificado.\n";
    exit( 1 );
}
update_option( 'hvkp_boton_backup', $json, false );
update_post_meta( $front, '_elementor_data', wp_slash( $nuevo ) );
$purgar();

foreach ( $hechos as $h ) { echo "[OK]    $h\n"; }
echo "[OK]    Caches purgadas\n";

$res = wp_remote_get( home_url( '/?hvkb=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $res ) ) {
    $body = (string) wp_remote_retrieve_body( $res );
    if ( false !== strpos( $body, 'cat=automoviles' ) ) {
        echo "[OK]    Verificado: el boton ya filtra por automoviles\n";
    } else {
        echo "[AVISO] Aun no se ve el filtro nuevo en la portada (cache)\n";
    }
    if ( false !== strpos( $body, 'cat=car' ) ) {
        echo "[AVISO] Todavia queda algun enlace a la categoria inexistente ?cat=car\n";
    }
}
echo "Listo. Para volver atras: php hvkp-boton-autos.php restore\n";
