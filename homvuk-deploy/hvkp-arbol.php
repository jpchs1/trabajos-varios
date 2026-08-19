<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Ver la estructura de la portada (solo lectura)
 * Muestra el arbol de secciones y widgets alrededor de la seccion de vehiculos.
 */
require __DIR__ . '/wp-load.php';

$front = (int) get_option( 'page_on_front' );
$json  = get_post_meta( $front, '_elementor_data', true );
$arbol = is_string( $json ) ? json_decode( $json, true ) : null;
if ( ! is_array( $arbol ) ) {
    echo "[ERROR] No se pudo leer la portada\n";
    exit( 1 );
}

function hvka_ver( $nodo, $nivel, $ruta ) {
    $sangria = str_repeat( '  ', $nivel );
    $tipo    = isset( $nodo['elType'] ) ? $nodo['elType'] : '?';
    $id      = isset( $nodo['id'] ) ? $nodo['id'] : '?';
    $extra   = '';
    if ( 'widget' === $tipo ) {
        $tipo = isset( $nodo['widgetType'] ) ? $nodo['widgetType'] : 'widget';
        $s = isset( $nodo['settings'] ) && is_array( $nodo['settings'] ) ? $nodo['settings'] : array();
        foreach ( array( 'categories', 'title', 'text' ) as $k ) {
            if ( isset( $s[ $k ] ) && is_string( $s[ $k ] ) && '' !== $s[ $k ] ) {
                $extra .= ' ' . $k . '="' . mb_substr( $s[ $k ], 0, 30 ) . '"';
            }
        }
        if ( isset( $s['link']['url'] ) ) {
            $extra .= ' enlace="' . $s['link']['url'] . '"';
        }
    }
    echo $sangria . '[' . $ruta . '] ' . $tipo . ' #' . $id . $extra . "\n";
    if ( ! empty( $nodo['elements'] ) && is_array( $nodo['elements'] ) ) {
        foreach ( $nodo['elements'] as $i => $h ) {
            hvka_ver( $h, $nivel + 1, $ruta . '.' . $i );
        }
    }
}

// Mostrar solo las secciones que mencionan vehiculos, automoviles o un boton
function hvka_relevante( $nodo ) {
    $j = wp_json_encode( $nodo );
    return ( false !== stripos( $j, 'automoviles' ) || false !== stripos( $j, 'veh' ) || false !== stripos( $j, '"button"' ) );
}

echo "== Estructura de la portada (secciones relevantes) ==\n";
foreach ( $arbol as $i => $sec ) {
    if ( hvka_relevante( $sec ) ) {
        hvka_ver( $sec, 0, (string) $i );
        echo "---\n";
    }
}
echo "Listo. Este script puede borrarse: rm -f hvkp-arbol.php\n";
