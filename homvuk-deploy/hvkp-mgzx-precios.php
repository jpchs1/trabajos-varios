<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Revisar (y corregir) los precios del NEW MG ZX 2026
 *
 * El producto se creo clonando el MG3, cuyo precio por dia era USD 85. El
 * cambio directo solo alcanzo los campos simples; OVA BRW guarda ademas
 * precios por dia de la semana y por periodo dentro de estructuras
 * serializadas. Este script los busca y los deja en el precio correcto.
 *
 * Uso:  php hvkp-mgzx-precios.php            (solo revisar, no modifica nada)
 *       php hvkp-mgzx-precios.php aplicar    (corregir lo que encuentre)
 */

$HVKQ_SLUG   = 'new-mg-zx-2026';
$HVKQ_ANTES  = 85;
$HVKQ_NUEVO  = 129;

/**
 * Recorre un valor (escalar o anidado) y reemplaza los precios del modelo
 * base por el nuevo. Devuelve array( valor, cambios ).
 */
function hvkq_reemplazar( $valor, $antes, $nuevo, &$rutas, $ruta = '' ) {
    if ( is_array( $valor ) ) {
        foreach ( $valor as $k => $v ) {
            $valor[ $k ] = hvkq_reemplazar( $v, $antes, $nuevo, $rutas, $ruta . '/' . $k );
        }
        return $valor;
    }
    if ( is_object( $valor ) ) {
        foreach ( get_object_vars( $valor ) as $k => $v ) {
            $valor->$k = hvkq_reemplazar( $v, $antes, $nuevo, $rutas, $ruta . '/' . $k );
        }
        return $valor;
    }
    if ( ! is_string( $valor ) && ! is_numeric( $valor ) ) {
        return $valor;
    }
    $v = trim( (string) $valor );
    if ( '' === $v ) {
        return $valor;
    }
    $normal = str_replace( ',', '.', $v );
    if ( ! is_numeric( $normal ) || abs( floatval( $normal ) - $antes ) > 0.001 ) {
        return $valor;
    }
    $rutas[] = ltrim( $ruta, '/' );
    return (string) $nuevo;
}

if ( getenv( 'HVKQ_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };
    $r = array();
    $dias = array( 'monday' => '85', 'tuesday' => '85,00', 'sunday' => '90' );
    $out  = hvkq_reemplazar( $dias, 85, 129, $r );
    $chk( 'cambia lunes y martes, respeta domingo', '129' === $out['monday'] && '129' === $out['tuesday'] && '90' === $out['sunday'] );
    $chk( 'reporta las rutas cambiadas', 2 === count( $r ) && in_array( 'monday', $r, true ) );
    $r = array();
    $anidado = array( 'reglas' => array( array( 'precio' => '85', 'dias' => 3 ) ) );
    $out = hvkq_reemplazar( $anidado, 85, 129, $r );
    $chk( 'entra en estructuras anidadas', '129' === $out['reglas'][0]['precio'] );
    $chk( 'no confunde otros numeros', 3 === $out['reglas'][0]['dias'] );
    $r = array();
    $chk( 'texto libre intacto', 'consultar' === hvkq_reemplazar( 'consultar', 85, 129, $r ) );
    $chk( 'numero distinto intacto', '155' === hvkq_reemplazar( '155', 85, 129, $r ) );
    $chk( 'valor 850 no se confunde con 85', '850' === hvkq_reemplazar( '850', 85, 129, $r ) );
    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';
global $wpdb;

$aplicar = ( isset( $argv[1] ) && 'aplicar' === $argv[1] );

$id = $wpdb->get_var( $wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' AND post_status != 'trash' LIMIT 1",
    $HVKQ_SLUG
) );
if ( ! $id ) {
    echo "[ERROR] No se encontro el producto $HVKQ_SLUG\n";
    exit( 1 );
}

$metas      = get_post_meta( (int) $id );
$encontrado = 0;
$cambiados  = 0;
foreach ( $metas as $clave => $valores ) {
    foreach ( $valores as $bruto ) {
        $valor = maybe_unserialize( $bruto );
        $rutas = array();
        $nuevo = hvkq_reemplazar( $valor, $HVKQ_ANTES, $HVKQ_NUEVO, $rutas );
        if ( ! $rutas ) {
            continue;
        }
        $encontrado += count( $rutas );
        $detalle = $rutas[0] ? ' -> ' . implode( ', ', array_slice( $rutas, 0, 8 ) ) : '';
        if ( $aplicar ) {
            update_post_meta( (int) $id, $clave, $nuevo );
            $cambiados += count( $rutas );
            echo "[OK]    $clave$detalle\n";
        } else {
            echo "[PENDIENTE] $clave$detalle\n";
        }
    }
}

if ( ! $encontrado ) {
    echo "[OK]    No quedan precios de USD $HVKQ_ANTES en el producto: todo esta en USD $HVKQ_NUEVO\n";
} elseif ( $aplicar ) {
    if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $id ); }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    echo "[OK]    Corregidos $cambiados valores a USD $HVKQ_NUEVO (~$" . number_format( $HVKQ_NUEVO * 816, 0, ',', '.' ) . " CLP)\n";
} else {
    echo "\nSe encontraron $encontrado valores todavia en USD $HVKQ_ANTES.\n";
    echo "Para corregirlos: php hvkp-mgzx-precios.php aplicar\n";
}
