<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Secadora Mabe del 306 (hvkp-secadora)
 *
 * Reemplaza el bloque "Secadora de Ropa" de Informacion General SOLO en
 * Magnolio 306: ya no es Bosch/Fensa con estanque de agua que hay que vaciar,
 * ahora es una Mabe que expulsa el agua sola por la parte frontal. Se elimina
 * tambien el video de como vaciar el estanque, que ya no aplica.
 *
 * Solo base de datos. Uso:
 *   php hvkp-secadora.php            (aplicar)
 *   php hvkp-secadora.php restore    (volver al texto anterior)
 */

$HVKS_BLOQUES = array(
    'es'   => 'PGg0PlNlY2Fkb3JhIGRlIFJvcGE8L2g0Pgo8cD5FbCBkZXBhcnRhbWVudG8gY3VlbnRhIGNvbiA8c3Ryb25nPnNlY2Fkb3JhIE1hYmU8L3N0cm9uZz4uIE5vIG5lY2VzaXRhcyB2YWNpYXIgbmluZyZ1YWN1dGU7biBlc3RhbnF1ZSBkZSBhZ3VhOiBsYSBzZWNhZG9yYSBleHB1bHNhIGVsIGFndWEgc29sYSBwb3IgbGEgcGFydGUgZnJvbnRhbC48L3A+CjxwPlNvbG8gY2FyZ2EgbGEgcm9wYSwgZWxpZ2UgZWwgcHJvZ3JhbWEgeSBsaXN0by4gU2kgbm90YXMgcXVlIGVtcGllemEgYSBzZWNhciBtJmFhY3V0ZTtzIGxlbnRvLCByZXZpc2EgcXVlIGVsIGZpbHRybyBkZSBwZWx1c2EgZXN0JmVhY3V0ZTsgbGltcGlvLjwvcD4K',
    'en'   => 'PGg0PkNsb3RoZXMgRHJ5ZXI8L2g0Pgo8cD5UaGUgYXBhcnRtZW50IGhhcyBhIDxzdHJvbmc+TWFiZSBkcnllcjwvc3Ryb25nPi4gVGhlcmUgaXMgbm8gd2F0ZXIgdGFuayB0byBlbXB0eTogdGhlIGRyeWVyIGRyYWlucyB0aGUgd2F0ZXIgYnkgaXRzZWxmIHRocm91Z2ggdGhlIGZyb250LjwvcD4KPHA+SnVzdCBsb2FkIHRoZSBjbG90aGVzLCBwaWNrIGEgcHJvZ3JhbSBhbmQgeW91IGFyZSBkb25lLiBJZiBpdCBzdGFydHMgZHJ5aW5nIG1vcmUgc2xvd2x5LCBjaGVjayB0aGF0IHRoZSBsaW50IGZpbHRlciBpcyBjbGVhbi48L3A+Cg==',
    'ptbr' => 'PGg0PlNlY2Fkb3JhIGRlIFJvdXBhczwvaDQ+CjxwPk8gYXBhcnRhbWVudG8gcG9zc3VpIDxzdHJvbmc+c2VjYWRvcmEgTWFiZTwvc3Ryb25nPi4gTiZhdGlsZGU7byAmZWFjdXRlOyBwcmVjaXNvIGVzdmF6aWFyIG5lbmh1bSByZXNlcnZhdCZvYWN1dGU7cmlvIGRlICZhYWN1dGU7Z3VhOiBhIHNlY2Fkb3JhIGV4cGVsZSBhICZhYWN1dGU7Z3VhIHNvemluaGEgcGVsYSBwYXJ0ZSBmcm9udGFsLjwvcD4KPHA+QmFzdGEgY29sb2NhciBhcyByb3VwYXMsIGVzY29saGVyIG8gcHJvZ3JhbWEgZSBwcm9udG8uIFNlIGNvbWUmY2NlZGlsO2FyIGEgc2VjYXIgbWFpcyBkZXZhZ2FyLCB2ZXJpZmlxdWUgc2UgbyBmaWx0cm8gZGUgZmlhcG9zIGVzdCZhYWN1dGU7IGxpbXBvLjwvcD4K',
);
$HVKS_SLUG  = 'magnolio-306';
$HVKS_MARCA = 'Mabe';

/**
 * Reemplaza el bloque <h4> de la secadora dentro del contenido de la seccion.
 * Devuelve array( contenido_nuevo|null, motivo ).
 */
function hvks_reemplazar( $html, $nuevo, $marca ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    if ( false !== strpos( $html, $marca ) ) {
        return array( null, 'ya-instalado' );
    }
    $partes = preg_split( '/(?=<h4>)/i', $html );
    $salida = '';
    $puesto = false;
    $n      = 0;
    foreach ( $partes as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $titulo = '';
        $es_h4  = ( 0 === stripos( ltrim( $parte ), '<h4>' ) );
        if ( $es_h4 && preg_match( '#<h4>(.*?)</h4>#is', $parte, $m ) ) {
            $titulo = $m[1];
        }
        if ( $es_h4 && preg_match( '/secadora|dryer/i', $titulo ) ) {
            $n++;
            if ( ! $puesto ) {
                $salida .= $nuevo;
                $puesto  = true;
            }
            continue;
        }
        $salida .= $parte;
    }
    if ( ! $puesto ) {
        return array( null, 'no-se-encontro-el-bloque' );
    }
    return array( $salida, 'ok:' . $n );
}

// ---- Pruebas locales ----
if ( getenv( 'HVKS_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $nuevo = base64_decode( $GLOBALS['HVKS_BLOQUES']['es'] );
    $prev  = '<h3>Informaci&oacute;n General</h3><p>intro</p>'
        . '<h4>Secadora de Ropa</h4><p>marca Fensa o Bosch. Hay que vaciar el estanque.</p><p><a href="/video.mp4">Ver video: como vaciar el estanque</a></p>'
        . '<h4>Calefacci&oacute;n</h4><p>losa radiante</p>'
        . '<h4>Basura y Reciclaje</h4><p>shaft</p>';
    list( $out, $why ) = hvks_reemplazar( $prev, $nuevo, $GLOBALS['HVKS_MARCA'] );
    $chk( 'reemplaza el bloque de la secadora', 'ok:1' === $why );
    $chk( 'sigue habiendo 3 bloques', 3 === substr_count( strtolower( $out ), '<h4>' ) );
    $chk( 'elimina Bosch, Fensa y la instruccion de vaciar', stripos( $out, 'Bosch' ) === false && stripos( $out, 'Fensa' ) === false && stripos( $out, 'Hay que vaciar el estanque' ) === false );
    $chk( 'aclara que ya no hay estanque que vaciar', stripos( $out, 'No necesitas vaciar' ) !== false );
    $chk( 'elimina el video de vaciar el estanque', strpos( $out, 'video.mp4' ) === false );
    $chk( 'menciona Mabe y expulsion frontal', strpos( $out, 'Mabe' ) !== false && stripos( $out, 'parte frontal' ) !== false );
    $chk( 'conserva calefaccion y basura', strpos( $out, 'losa radiante' ) !== false && strpos( $out, 'shaft' ) !== false );
    list( $o2, $w2 ) = hvks_reemplazar( $out, $nuevo, $GLOBALS['HVKS_MARCA'] );
    $chk( 'idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvks_reemplazar( '<h4>Otro</h4><p>x</p>', $nuevo, $GLOBALS['HVKS_MARCA'] );
    $chk( 'sin bloque de secadora: no toca nada', null === $o3 );
    list( $oe, $we ) = hvks_reemplazar( '<h4>Clothes Dryer</h4><p>Bosch tank</p><h4>Heating</h4><p>x</p>', base64_decode( $GLOBALS['HVKS_BLOQUES']['en'] ), $GLOBALS['HVKS_MARCA'] );
    $chk( 'ingles: reemplaza Clothes Dryer', 'ok:1' === $we && strpos( $oe, 'Mabe dryer' ) !== false && strpos( $oe, 'Heating' ) !== false );
    list( $op, $wp ) = hvks_reemplazar( '<h4>Secadora de Roupas</h4><p>Bosch tanque</p><h4>Lixo</h4><p>x</p>', base64_decode( $GLOBALS['HVKS_BLOQUES']['ptbr'] ), $GLOBALS['HVKS_MARCA'] );
    $chk( 'portugues: reemplaza Secadora de Roupas', 'ok:1' === $wp && strpos( $op, 'secadora Mabe' ) !== false && strpos( $op, 'Lixo' ) !== false );

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$tabla = $wpdb->prefix . 'portal_units';
$ok    = array();
$warn  = array();

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_secadora_backup' );
    if ( ! is_array( $b ) || empty( $b ) ) {
        echo "[AVISO] No hay respaldo; nada que restaurar.\n";
        exit( 0 );
    }
    foreach ( $b as $fila ) {
        $wpdb->update( $tabla, array( $fila['campo'] => $fila['valor'] ), array( 'id' => (int) $fila['id'] ) );
    }
    echo "[OK]    Texto anterior restaurado (" . count( $b ) . " campos)\n";
    exit( 0 );
}

$unidad = $wpdb->get_row( $wpdb->prepare( "SELECT id, name FROM $tabla WHERE slug = %s AND active = 1", $HVKS_SLUG ) );
if ( ! $unidad ) {
    echo "[ERROR] No se encontro la unidad $HVKS_SLUG.\n";
    exit( 1 );
}

$backup = array();
foreach ( $HVKS_BLOQUES as $lang => $b64 ) {
    $campo  = 'general_' . $lang;
    $actual = $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $unidad->id ) );
    list( $nuevo, $motivo ) = hvks_reemplazar( (string) $actual, base64_decode( $b64 ), $HVKS_MARCA );
    if ( 'ya-instalado' === $motivo ) {
        $ok[] = "$unidad->name [$lang]: ya estaba actualizado";
        continue;
    }
    if ( null === $nuevo ) {
        $warn[] = "$unidad->name [$lang]: sin cambios ($motivo)";
        continue;
    }
    $backup[] = array( 'id' => (int) $unidad->id, 'campo' => $campo, 'valor' => (string) $actual );
    $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $unidad->id ) );
    $ok[] = "$unidad->name [$lang]: secadora Mabe (sin estanque de agua)";
}
if ( $backup ) {
    update_option( 'hvkp_secadora_backup', $backup, false );
}

// Aviso sobre la otra unidad, que sigue con el texto antiguo
$otras = $wpdb->get_results( $wpdb->prepare( "SELECT id, name, general_es FROM $tabla WHERE active = 1 AND slug != %s", $HVKS_SLUG ) );
foreach ( $otras as $o ) {
    if ( stripos( (string) $o->general_es, 'Bosch' ) !== false || stripos( (string) $o->general_es, 'estanque' ) !== false ) {
        $warn[] = $o->name . ': sigue diciendo Bosch/Fensa con estanque de agua (no se toco; avisar si tambien cambio)';
    }
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - Secadora Mabe (Magnolio 306) ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-secadora.php\n";
echo "Para revertir: php hvkp-secadora.php restore\n";
