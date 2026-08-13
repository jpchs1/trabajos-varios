<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Calefaccion por losa radiante (hvkp-calefaccion)
 *
 * Actualiza el bloque "Calefaccion" de Informacion General en los dos
 * departamentos (Magnolio 306 y 1506) y en los tres idiomas:
 * losa radiante conectada a la caldera, funciona perfectamente, y el
 * termostato debe mantenerse entre 20 y 21 grados como maximo.
 *
 * Solo base de datos. Uso:
 *   php hvkp-calefaccion.php            (aplicar)
 *   php hvkp-calefaccion.php restore    (volver al texto anterior)
 */

$HVKF_BLOQUES = array(
    'es'   => 'PGg0PkNhbGVmYWNjaSZvYWN1dGU7bjwvaDQ+CjxwPkVuIGludmllcm5vIGVsIGRlcGFydGFtZW50byBzZSBjYWxlZmFjY2lvbmEgcG9yIDxzdHJvbmc+bG9zYSByYWRpYW50ZTwvc3Ryb25nPiBjb25lY3RhZGEgYSBsYSBjYWxkZXJhIGRlbCBlZGlmaWNpbywgeSBmdW5jaW9uYSBwZXJmZWN0YW1lbnRlOiBlbCBjYWxvciBzdWJlIGRlc2RlIGVsIHBpc28gZGUgZm9ybWEgcGFyZWphIHkgc2lsZW5jaW9zYSwgc2luIHJ1aWRvIG5pIGVxdWlwb3MgYSBsYSB2aXN0YS48L3A+CjxwPjxzdHJvbmc+TWFudCZlYWN1dGU7biBlbCB0ZXJtb3N0YXRvIGVudHJlIDIwJmRlZzsgeSAyMSZkZWc7QyBjb21vIG0mYWFjdXRlO3hpbW8uPC9zdHJvbmc+IEVzIGxhIHRlbXBlcmF0dXJhIGlkZWFsIGRlIGNvbmZvcnQ7IHN1YmlybG8gbSZhYWN1dGU7cyBubyBjYWxpZW50YSBtJmFhY3V0ZTtzIHImYWFjdXRlO3BpZG8geSBzb2xvIGdlbmVyYSB1biBnYXN0byBpbm5lY2VzYXJpby48L3A+CjxwPlRlbiBwcmVzZW50ZSBxdWUgbGEgbG9zYSByYWRpYW50ZSB0YXJkYSB1biBwb2NvIGVuIHJlYWNjaW9uYXI6IGRlc3B1JmVhY3V0ZTtzIGRlIGNhbWJpYXIgbGEgdGVtcGVyYXR1cmEsIGVzcGVyYSB1biBwYXIgZGUgaG9yYXMgYW50ZXMgZGUgdm9sdmVyIGEgYWp1c3RhcmxhLjwvcD4K',
    'en'   => 'PGg0PkhlYXRpbmc8L2g0Pgo8cD5JbiB3aW50ZXIgdGhlIGFwYXJ0bWVudCBpcyBoZWF0ZWQgYnkgPHN0cm9uZz5yYWRpYW50IGZsb29yIGhlYXRpbmc8L3N0cm9uZz4gY29ubmVjdGVkIHRvIHRoZSBidWlsZGluZyZyc3F1bztzIGJvaWxlciwgYW5kIGl0IHdvcmtzIHBlcmZlY3RseTogdGhlIGhlYXQgcmlzZXMgZXZlbmx5IGFuZCBzaWxlbnRseSBmcm9tIHRoZSBmbG9vciwgd2l0aCBubyBub2lzZSBhbmQgbm8gdmlzaWJsZSBlcXVpcG1lbnQuPC9wPgo8cD48c3Ryb25nPktlZXAgdGhlIHRoZXJtb3N0YXQgYmV0d2VlbiAyMCZkZWc7IGFuZCAyMSZkZWc7QyAoNjgmbmRhc2g7NzAmZGVnO0YpIG1heGltdW0uPC9zdHJvbmc+IFRoYXQgaXMgdGhlIGlkZWFsIGNvbWZvcnQgdGVtcGVyYXR1cmU7IHNldHRpbmcgaXQgaGlnaGVyIHdpbGwgbm90IGhlYXQgdGhlIGFwYXJ0bWVudCBhbnkgZmFzdGVyIGFuZCBvbmx5IHdhc3RlcyBlbmVyZ3kuPC9wPgo8cD5LZWVwIGluIG1pbmQgdGhhdCByYWRpYW50IGZsb29yIGhlYXRpbmcgcmVhY3RzIHNsb3dseTogYWZ0ZXIgY2hhbmdpbmcgdGhlIHRlbXBlcmF0dXJlLCB3YWl0IGEgY291cGxlIG9mIGhvdXJzIGJlZm9yZSBhZGp1c3RpbmcgaXQgYWdhaW4uPC9wPgo=',
    'ptbr' => 'PGg0PkFxdWVjaW1lbnRvPC9oND4KPHA+Tm8gaW52ZXJubyBvIGFwYXJ0YW1lbnRvICZlYWN1dGU7IGFxdWVjaWRvIHBvciA8c3Ryb25nPnBpc28gcmFkaWFudGU8L3N0cm9uZz4gbGlnYWRvICZhZ3JhdmU7IGNhbGRlaXJhIGRvIGVkaWYmaWFjdXRlO2NpbywgZSBmdW5jaW9uYSBwZXJmZWl0YW1lbnRlOiBvIGNhbG9yIHNvYmUgZG8gcGlzbyBkZSBmb3JtYSB1bmlmb3JtZSBlIHNpbGVuY2lvc2EsIHNlbSBydSZpYWN1dGU7ZG8gbmVtIGVxdWlwYW1lbnRvcyAmYWdyYXZlOyB2aXN0YS48L3A+CjxwPjxzdHJvbmc+TWFudGVuaGEgbyB0ZXJtb3N0YXRvIGVudHJlIDIwJmRlZzsgZSAyMSZkZWc7QyBubyBtJmFhY3V0ZTt4aW1vLjwvc3Ryb25nPiAmRWFjdXRlOyBhIHRlbXBlcmF0dXJhIGlkZWFsIGRlIGNvbmZvcnRvOyBhdW1lbnRhciBtYWlzIG4mYXRpbGRlO28gYXF1ZWNlIG1haXMgciZhYWN1dGU7cGlkbyBlIHMmb2FjdXRlOyBnZXJhIGdhc3RvIGRlc25lY2VzcyZhYWN1dGU7cmlvLjwvcD4KPHA+TGVtYnJlLXNlIGRlIHF1ZSBvIHBpc28gcmFkaWFudGUgZGVtb3JhIGEgcmVhZ2lyOiBkZXBvaXMgZGUgbXVkYXIgYSB0ZW1wZXJhdHVyYSwgZXNwZXJlIGFsZ3VtYXMgaG9yYXMgYW50ZXMgZGUgYWp1c3RhciBub3ZhbWVudGUuPC9wPgo=',
);
$HVKF_MARCA = 'radiant'; // presente en "losa radiante", "radiant floor" y "piso radiante"

/**
 * Reemplaza el bloque <h4> de calefaccion dentro del contenido de la seccion.
 * Devuelve array( contenido_nuevo|null, motivo ).
 */
function hvkf_reemplazar( $html, $nuevo, $marca ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    if ( false !== stripos( $html, $marca ) ) {
        return array( null, 'ya-instalado' );
    }
    $partes    = preg_split( '/(?=<h4>)/i', $html );
    $salida    = '';
    $puesto    = false;
    $encontrado = 0;
    foreach ( $partes as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $es_h4 = ( 0 === stripos( ltrim( $parte ), '<h4>' ) );
        $titulo = '';
        if ( $es_h4 && preg_match( '#<h4>(.*?)</h4>#is', $parte, $m ) ) {
            $titulo = $m[1];
        }
        if ( $es_h4 && preg_match( '/calefacci|heating|aquecimento/i', $titulo ) ) {
            $encontrado++;
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
    return array( $salida, 'ok:' . $encontrado );
}

// ---- Pruebas locales (no requieren WordPress) ----
if ( getenv( 'HVKF_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $nuevo = base64_decode( $GLOBALS['HVKF_BLOQUES']['es'] );
    $prev  = '<h3>Informaci&oacute;n General</h3><p>intro</p>'
        . '<h4>Secadora de Ropa</h4><p>secadora</p>'
        . '<h4>Calefacci&oacute;n</h4><p>La calefacci&oacute;n es central (por caldera) y mantiene 21&deg;C.</p>'
        . '<h4>TV y Streaming</h4><p>Zapping</p>'
        . '<h4>Basura y Reciclaje</h4><p>shaft 8:00 a 20:00</p>';
    list( $out, $why ) = hvkf_reemplazar( $prev, $nuevo, $GLOBALS['HVKF_MARCA'] );
    $chk( 'reemplaza el bloque de calefaccion', 'ok:1' === $why );
    $chk( 'sigue habiendo 4 bloques', 4 === substr_count( strtolower( $out ), '<h4>' ) );
    $chk( 'conserva los demas bloques', strpos( $out, 'Zapping' ) !== false && strpos( $out, 'shaft' ) !== false && strpos( $out, 'secadora' ) !== false );
    $chk( 'conserva el titulo de la seccion y la intro', strpos( $out, '<h3>' ) !== false && strpos( $out, 'intro' ) !== false );
    $chk( 'ya no menciona el texto antiguo', strpos( $out, 'es central (por caldera)' ) === false );
    $chk( 'incluye losa radiante y 20-21 grados', stripos( $out, 'losa radiante' ) !== false && strpos( $out, '20&deg; y 21&deg;C' ) !== false );
    list( $o2, $w2 ) = hvkf_reemplazar( $out, $nuevo, $GLOBALS['HVKF_MARCA'] );
    $chk( 'idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvkf_reemplazar( '<h4>Otro</h4><p>x</p>', $nuevo, $GLOBALS['HVKF_MARCA'] );
    $chk( 'sin bloque de calefaccion: no toca nada', null === $o3 && 'no-se-encontro-el-bloque' === $w3 );

    // ingles y portugues
    list( $oe, $we ) = hvkf_reemplazar( '<h4>Heating</h4><p>boiler 21C</p><h4>Trash</h4><p>x</p>', base64_decode( $GLOBALS['HVKF_BLOQUES']['en'] ), $GLOBALS['HVKF_MARCA'] );
    $chk( 'ingles: reemplaza Heating', 'ok:1' === $we && stripos( $oe, 'radiant floor' ) !== false && strpos( $oe, 'Trash' ) !== false );
    list( $op, $wp ) = hvkf_reemplazar( '<h4>Aquecimento</h4><p>caldeira 21C</p><h4>Lixo</h4><p>x</p>', base64_decode( $GLOBALS['HVKF_BLOQUES']['ptbr'] ), $GLOBALS['HVKF_MARCA'] );
    $chk( 'portugues: reemplaza Aquecimento', 'ok:1' === $wp && stripos( $op, 'piso radiante' ) !== false && strpos( $op, 'Lixo' ) !== false );

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
    $b = get_option( 'hvkp_calefaccion_backup' );
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

$unidades = $wpdb->get_results( "SELECT id, name FROM $tabla WHERE active = 1" );
if ( ! $unidades ) {
    echo "[ERROR] No se encontraron unidades activas.\n";
    exit( 1 );
}

$backup = array();
foreach ( $unidades as $u ) {
    foreach ( $HVKF_BLOQUES as $lang => $b64 ) {
        $campo  = 'general_' . $lang;
        $actual = $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        list( $nuevo, $motivo ) = hvkf_reemplazar( (string) $actual, base64_decode( $b64 ), $HVKF_MARCA );
        if ( 'ya-instalado' === $motivo ) {
            $ok[] = "$u->name [$lang]: ya estaba actualizado";
            continue;
        }
        if ( null === $nuevo ) {
            $warn[] = "$u->name [$lang]: sin cambios ($motivo)";
            continue;
        }
        $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => (string) $actual );
        $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $u->id ) );
        $ok[] = "$u->name [$lang]: calefaccion actualizada (losa radiante, 20-21 grados)";
    }
}
if ( $backup ) {
    update_option( 'hvkp_calefaccion_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - Calefaccion por losa radiante ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-calefaccion.php\n";
echo "Para revertir: php hvkp-calefaccion.php restore\n";
