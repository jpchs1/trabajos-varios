<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Correcciones del catalogo de vehiculos (hvkp-autos-catalogo)
 *
 *   1. El auto nuevo es un MG ZS, no ZX: corrige nombre, direccion web y
 *      ficha (el maletero del ZS es de 443 litros).
 *   2. Envia el Audi A7 Premium a la papelera.
 *   3. Muestra como esta armada la seccion "Vehiculos en Alquiler" de la
 *      portada, para poder convertirla en carrusel.
 *
 * Uso:  php hvkp-autos-catalogo.php            (aplicar)
 *       php hvkp-autos-catalogo.php restore    (deshacer)
 */

$HVKC_DESC_B64 = 'PHA+QXJyaWVuZGEgZWwgPHN0cm9uZz5udWV2byBNRyBaUyAyMDI2PC9zdHJvbmc+IGVuIGNvbG9yIDxzdHJvbmc+cGxhdGVhZG88L3N0cm9uZz46IHVuIFNVViBjb21wYWN0byByZWNpJmVhY3V0ZTtuIHNhbGlkbyBkZSBmJmFhY3V0ZTticmljYSwgaWRlYWwgcGFyYSBtb3ZlcnNlIHBvciBTYW50aWFnbyBjb24gY29tb2RpZGFkIHkgcGFyYSBlc2NhcGFkYXMgZGUgZmluIGRlIHNlbWFuYS48L3A+CjxoMz5DYXJhY3RlciZpYWN1dGU7c3RpY2FzPC9oMz4KPHVsPgo8bGk+U1VWIGNvbXBhY3RvIHBhcmEgPHN0cm9uZz41IHBhc2FqZXJvczwvc3Ryb25nPiwgY29uIG1hbGV0ZXJvIGRlIDxzdHJvbmc+NDQzIGxpdHJvczwvc3Ryb25nPjwvbGk+CjxsaT5UcmFuc21pc2kmb2FjdXRlO24gYXV0b20mYWFjdXRlO3RpY2E8L2xpPgo8bGk+UGFudGFsbGEgdCZhYWN1dGU7Y3RpbCBjb24gPHN0cm9uZz5BcHBsZSBDYXJQbGF5IHkgQW5kcm9pZCBBdXRvPC9zdHJvbmc+PC9saT4KPGxpPlNlZ3VyaWRhZCBjb24gPHN0cm9uZz42IGFpcmJhZ3M8L3N0cm9uZz4sIEFCUywgY29udHJvbCBkZSBlc3RhYmlsaWRhZCB5IGFzaXN0ZW5jaWFzIDxzdHJvbmc+TUcgUGlsb3Q8L3N0cm9uZz48L2xpPgo8bGk+QmFqbyBjb25zdW1vIGRlIGNvbWJ1c3RpYmxlOiBpZGVhbCBwYXJhIGNpdWRhZCB5IGNhcnJldGVyYTwvbGk+CjwvdWw+CjxoMz5UdSBhcnJpZW5kbyBpbmNsdXllPC9oMz4KPHVsPgo8bGk+RW50cmVnYSBwZXJzb25hbGl6YWRhIHkgY29vcmRpbmFkYSBwb3IgV2hhdHNBcHA8L2xpPgo8bGk+VmVoJmlhY3V0ZTtjdWxvIGNvbiBtYW50ZW5jaW9uZXMgYWwgZCZpYWN1dGU7YSB5IHJldmlzaSZvYWN1dGU7biBwcmV2aWEgYSBjYWRhIGVudHJlZ2E8L2xpPgo8bGk+QXRlbmNpJm9hY3V0ZTtuIGRpcmVjdGEgNyBkJmlhY3V0ZTthcyBhIGxhIHNlbWFuYSwgc2luIGludGVybWVkaWFyaW9zPC9saT4KPC91bD4KPGgzPlJlcXVpc2l0b3MgcGFyYSBhcnJlbmRhcjwvaDM+Cjx1bD4KPGxpPkRvY3VtZW50byBkZSBpZGVudGlkYWQgeSBsaWNlbmNpYSBkZSBjb25kdWNpciB2aWdlbnRlIChzZSBhZGp1bnRhbiBhbCByZXNlcnZhcik8L2xpPgo8bGk+RGVwJm9hY3V0ZTtzaXRvIGRlIGdhcmFudCZpYWN1dGU7YSBkZSBVU0QgJDMwMCwgcmVlbWJvbHNhYmxlIGFsIG1pc21vIG1lZGlvIGRlIHBhZ28gYWwgZGV2b2x2ZXIgZWwgdmVoJmlhY3V0ZTtjdWxvIHNpbiBkYSZudGlsZGU7b3M8L2xpPgo8bGk+QWNlcHRhciBsb3MgdCZlYWN1dGU7cm1pbm9zIHkgY29uZGljaW9uZXMgZGUgYXJyaWVuZG88L2xpPgo8L3VsPgo8cD48c3Ryb25nPlJlc2VydmEgZW4gbCZpYWN1dGU7bmVhIGVuIG1lbm9zIGRlIDEgbWludXRvPC9zdHJvbmc+OiBlbGlnZSB0dXMgZmVjaGFzLCBhZGp1bnRhIHR1cyBkb2N1bWVudG9zIHkgcGFnYSBkZSBmb3JtYSBzZWd1cmEuICZpcXVlc3Q7RHVkYXM/IEVzY3ImaWFjdXRlO2Jlbm9zIHBvciBXaGF0c0FwcC48L3A+';
$HVKC_SLUG_ANT = 'new-mg-zx-2026';
$HVKC_SLUG_NEW = 'new-mg-zs-2026';
$HVKC_TIT_NEW  = 'NEW MG ZS 2026';
$HVKC_AUDI     = 'audi-a7-premium';

/** Resume un widget de Elementor para el diagnostico. */
function hvkc_resumir( $el, &$salida, $ruta = '' ) {
    if ( ! is_array( $el ) ) {
        return;
    }
    if ( isset( $el['elType'] ) && 'widget' === $el['elType'] ) {
        $tipo = isset( $el['widgetType'] ) ? $el['widgetType'] : '?';
        $set  = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
        $texto = wp_json_encode( $set );
        if ( preg_match( '/veh|alquiler|rental|product|car/i', $tipo . ' ' . $texto ) ) {
            $claves = array();
            foreach ( $set as $k => $v ) {
                if ( is_scalar( $v ) && preg_match( '/layout|style|column|slider|carousel|slide|item|number|limit|cat|type|autoplay|arrow|dots/i', $k ) ) {
                    $claves[] = $k . '=' . ( is_bool( $v ) ? ( $v ? 'true' : 'false' ) : substr( (string) $v, 0, 28 ) );
                }
            }
            $titulo = '';
            foreach ( array( 'title', 'heading', 'sub_title' ) as $k ) {
                if ( isset( $set[ $k ] ) && is_string( $set[ $k ] ) ) { $titulo = $set[ $k ]; break; }
            }
            $salida[] = array(
                'id'     => isset( $el['id'] ) ? $el['id'] : '?',
                'tipo'   => $tipo,
                'titulo' => $titulo,
                'ruta'   => $ruta,
                'claves' => $claves,
            );
        }
    }
    if ( ! empty( $el['elements'] ) && is_array( $el['elements'] ) ) {
        foreach ( $el['elements'] as $i => $hijo ) {
            hvkc_resumir( $hijo, $salida, $ruta . '/' . $i );
        }
    }
}

if ( getenv( 'HVKC_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };
    $d = base64_decode( $GLOBALS['HVKC_DESC_B64'] );
    $chk( 'ficha: dice MG ZS y no ZX', strpos( $d, 'MG ZS 2026' ) !== false && stripos( $d, 'MG ZX' ) === false );
    $chk( 'ficha: maletero 443 L del ZS', strpos( $d, '443' ) !== false && strpos( $d, '448' ) === false );
    $chk( 'ficha: conserva color, requisitos y deposito', strpos( $d, 'plateado' ) !== false && strpos( $d, 'licencia de conducir' ) !== false && strpos( $d, 'USD $300' ) !== false );
    $chk( 'ficha: sin rastros del MG3', stripos( $d, 'MG3' ) === false );
    $chk( 'direccion web nueva coherente', 'new-mg-zs-2026' === $GLOBALS['HVKC_SLUG_NEW'] && 'NEW MG ZS 2026' === $GLOBALS['HVKC_TIT_NEW'] );
    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode = isset( $argv[1] ) ? $argv[1] : 'aplicar';
$ok = array(); $warn = array();

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_catalogo_backup' );
    if ( ! is_array( $b ) ) {
        echo "[AVISO] No hay respaldo.\n";
        exit( 0 );
    }
    if ( ! empty( $b['auto'] ) ) {
        $wpdb->update( $wpdb->posts, array(
            'post_title'   => $b['auto']['title'],
            'post_name'    => $b['auto']['name'],
            'post_content' => $b['auto']['content'],
            'post_excerpt' => $b['auto']['excerpt'],
        ), array( 'ID' => (int) $b['auto']['id'] ) );
        clean_post_cache( (int) $b['auto']['id'] );
        echo "[OK]    Auto restaurado a su nombre anterior\n";
    }
    if ( ! empty( $b['audi'] ) ) {
        wp_untrash_post( (int) $b['audi'] );
        echo "[OK]    Audi A7 recuperado de la papelera\n";
    }
    exit( 0 );
}

$backup = array();

// 1) MG ZX -> MG ZS
$auto = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type = 'product' AND post_status != 'trash' LIMIT 1",
    $HVKC_SLUG_ANT, $HVKC_SLUG_NEW
) );
if ( ! $auto ) {
    $warn[] = 'No se encontro el auto nuevo para renombrar';
} elseif ( $HVKC_TIT_NEW === $auto->post_title && $HVKC_SLUG_NEW === $auto->post_name ) {
    $ok[] = 'El auto ya estaba como MG ZS';
} else {
    $backup['auto'] = array(
        'id' => (int) $auto->ID, 'title' => $auto->post_title, 'name' => $auto->post_name,
        'content' => $auto->post_content, 'excerpt' => $auto->post_excerpt,
    );
    $wpdb->update( $wpdb->posts, array(
        'post_title'   => $HVKC_TIT_NEW,
        'post_name'    => $HVKC_SLUG_NEW,
        'post_content' => base64_decode( $HVKC_DESC_B64 ),
        'post_excerpt' => 'SUV compacto 2026, color plateado, automatico, 5 pasajeros.',
    ), array( 'ID' => (int) $auto->ID ) );
    // WordPress redirige la direccion antigua a la nueva
    add_post_meta( (int) $auto->ID, '_wp_old_slug', $HVKC_SLUG_ANT );
    clean_post_cache( (int) $auto->ID );
    if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $auto->ID ); }
    $ok[] = 'Renombrado a ' . $HVKC_TIT_NEW . ' -> ' . get_permalink( (int) $auto->ID );
    $ok[] = 'Ficha corregida (maletero 443 L del ZS); la direccion antigua redirige a la nueva';
}

// 2) Audi A7 a la papelera
$audi = $wpdb->get_row( $wpdb->prepare(
    "SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' LIMIT 1",
    $HVKC_AUDI
) );
if ( ! $audi ) {
    $warn[] = 'No se encontro el Audi A7 (quiza ya fue eliminado)';
} elseif ( 'trash' === $audi->post_status ) {
    $ok[] = 'El Audi A7 ya estaba en la papelera';
} else {
    $backup['audi'] = (int) $audi->ID;
    wp_trash_post( (int) $audi->ID );
    $ok[] = 'Audi A7 Premium enviado a la papelera (id ' . $audi->ID . ', recuperable)';
}

if ( $backup ) {
    update_option( 'hvkp_catalogo_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( class_exists( '\Elementor\Plugin' ) ) {
    try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
}
$ok[] = 'Caches purgadas';

echo "== HOMVUK - Catalogo de vehiculos ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }

// 3) Diagnostico de la seccion de vehiculos de la portada
echo "\n== Seccion \"Vehiculos en Alquiler\" de la portada ==\n";
$front = (int) get_option( 'page_on_front' );
$datos = $front ? get_post_meta( $front, '_elementor_data', true ) : '';
$arbol = is_string( $datos ) ? json_decode( $datos, true ) : null;
if ( ! is_array( $arbol ) ) {
    echo "[AVISO] No se pudo leer la portada (post $front)\n";
} else {
    $encontrados = array();
    foreach ( $arbol as $i => $nodo ) {
        hvkc_resumir( $nodo, $encontrados, (string) $i );
    }
    if ( ! $encontrados ) {
        echo "[AVISO] No se encontraron widgets de productos en la portada\n";
    }
    foreach ( $encontrados as $w ) {
        echo "  widget " . $w['id'] . " (" . $w['tipo'] . ")" . ( $w['titulo'] ? ' - "' . $w['titulo'] . '"' : '' ) . "\n";
        if ( $w['claves'] ) {
            echo "     opciones: " . implode( ' | ', array_slice( $w['claves'], 0, 12 ) ) . "\n";
        }
    }
}
echo "\nListo. Este script puede borrarse: rm -f hvkp-autos-catalogo.php\n";
