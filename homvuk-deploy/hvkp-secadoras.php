<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Secadoras por unidad (hvkp-secadoras)
 *
 * Corrige la informacion de la secadora dentro del bloque Electrodomesticos:
 *   - Magnolio 1506: es Fensa (el texto decia "Fensa o Bosch"). Mantiene la
 *     instruccion de vaciar el estanque de agua y el video.
 *   - Magnolio 306: es Mabe, que expulsa el agua sola por la parte frontal,
 *     asi que no hay bandeja que vaciar. Se agrega esa aclaracion breve.
 *
 * Solo base de datos. Uso:
 *   php hvkp-secadoras.php            (aplicar)
 *   php hvkp-secadoras.php restore    (volver al texto anterior)
 */

$HVKY_1506 = array( 'es' => 'PGg0PlNlY2Fkb3JhIEZlbnNhPC9oND4KPHA+RWwgZGVwYXJ0YW1lbnRvIGN1ZW50YSBjb24gPHN0cm9uZz5zZWNhZG9yYSBGZW5zYTwvc3Ryb25nPi48L3A+CjxwPjxzdHJvbmc+SW1wb3J0YW50ZTo8L3N0cm9uZz4gc2kgbGEgc2VjYWRvcmEgbm8gc2VjYSBiaWVuLCBlcyBwb3JxdWUgZWwgZXN0YW5xdWUgZGUgYWd1YSBlc3QmYWFjdXRlOyBsbGVubyB5IGhheSBxdWUgdmFjaWFybG8uIEVzdGUgdmlkZW8gbXVlc3RyYSBjJm9hY3V0ZTttbyBoYWNlcmxvIChlcyBkZSBvdHJhIG1hcmNhLCBwZXJvIGVsIHByb2NlZGltaWVudG8gZXMgaWQmZWFjdXRlO250aWNvKTo8L3A+CjxwPjxhIGhyZWY9Imh0dHBzOi8vd3d3LnlvdXR1YmUuY29tL3dhdGNoP3Y9ZVVhRzZrNUdJcFEiIHRhcmdldD0iX2JsYW5rIiByZWw9Im5vb3BlbmVyIiBzdHlsZT0iZGlzcGxheTppbmxpbmUtYmxvY2s7YmFja2dyb3VuZDojZTg0MzkzO2NvbG9yOiNmZmY7cGFkZGluZzoxMHB4IDE4cHg7Ym9yZGVyLXJhZGl1czo4cHg7dGV4dC1kZWNvcmF0aW9uOm5vbmU7Zm9udC13ZWlnaHQ6NjAwIj4mIzk2NTQ7IFZlciB2aWRlbzogYyZvYWN1dGU7bW8gdmFjaWFyIGVsIGVzdGFucXVlIGRlIGFndWEgZGUgbGEgc2VjYWRvcmE8L2E+PC9wPgo=', 'en' => 'PGg0PkZlbnNhIERyeWVyPC9oND4KPHA+VGhlIGFwYXJ0bWVudCBoYXMgYSA8c3Ryb25nPkZlbnNhIGRyeWVyPC9zdHJvbmc+LjwvcD4KPHA+PHN0cm9uZz5JbXBvcnRhbnQ6PC9zdHJvbmc+IGlmIHRoZSBkcnllciBpcyBub3QgZHJ5aW5nIHdlbGwsIHRoZSB3YXRlciB0YW5rIGlzIGZ1bGwgYW5kIG5lZWRzIHRvIGJlIGVtcHRpZWQuIFRoaXMgdmlkZW8gc2hvd3MgaG93IHRvIGRvIGl0IChpdCBmZWF0dXJlcyBhbm90aGVyIGJyYW5kLCBidXQgdGhlIHByb2NlZHVyZSBpcyBpZGVudGljYWwpOjwvcD4KPHA+PGEgaHJlZj0iaHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1lVWFHNms1R0lwUSIgdGFyZ2V0PSJfYmxhbmsiIHJlbD0ibm9vcGVuZXIiIHN0eWxlPSJkaXNwbGF5OmlubGluZS1ibG9jaztiYWNrZ3JvdW5kOiNlODQzOTM7Y29sb3I6I2ZmZjtwYWRkaW5nOjEwcHggMThweDtib3JkZXItcmFkaXVzOjhweDt0ZXh0LWRlY29yYXRpb246bm9uZTtmb250LXdlaWdodDo2MDAiPiYjOTY1NDsgV2F0Y2ggdmlkZW86IGhvdyB0byBlbXB0eSB0aGUgZHJ5ZXIgd2F0ZXIgdGFuazwvYT48L3A+Cg==', 'ptbr' => 'PGg0PlNlY2Fkb3JhIEZlbnNhPC9oND4KPHA+TyBhcGFydGFtZW50byBwb3NzdWkgPHN0cm9uZz5zZWNhZG9yYSBGZW5zYTwvc3Ryb25nPi48L3A+CjxwPjxzdHJvbmc+SW1wb3J0YW50ZTo8L3N0cm9uZz4gc2UgYSBzZWNhZG9yYSBuJmF0aWxkZTtvIGVzdGl2ZXIgc2VjYW5kbyBiZW0sIG8gcmVzZXJ2YXQmb2FjdXRlO3JpbyBkZSAmYWFjdXRlO2d1YSBlc3QmYWFjdXRlOyBjaGVpbyBlIHByZWNpc2Egc2VyIGVzdmF6aWFkby4gRXN0ZSB2JmlhY3V0ZTtkZW8gbW9zdHJhIGNvbW8gZmF6ZXIgKCZlYWN1dGU7IGRlIG91dHJhIG1hcmNhLCBtYXMgbyBwcm9jZWRpbWVudG8gJmVhY3V0ZTsgaWQmZWNpcmM7bnRpY28pOjwvcD4KPHA+PGEgaHJlZj0iaHR0cHM6Ly93d3cueW91dHViZS5jb20vd2F0Y2g/dj1lVWFHNms1R0lwUSIgdGFyZ2V0PSJfYmxhbmsiIHJlbD0ibm9vcGVuZXIiIHN0eWxlPSJkaXNwbGF5OmlubGluZS1ibG9jaztiYWNrZ3JvdW5kOiNlODQzOTM7Y29sb3I6I2ZmZjtwYWRkaW5nOjEwcHggMThweDtib3JkZXItcmFkaXVzOjhweDt0ZXh0LWRlY29yYXRpb246bm9uZTtmb250LXdlaWdodDo2MDAiPiYjOTY1NDsgVmVyIHYmaWFjdXRlO2RlbzogY29tbyBlc3ZhemlhciBvIHJlc2VydmF0Jm9hY3V0ZTtyaW8gZGUgJmFhY3V0ZTtndWEgZGEgc2VjYWRvcmE8L2E+PC9wPgo=' );
$HVKY_306  = array( 'es' => 'PGg0PlNlY2Fkb3JhIE1hYmU8L2g0Pgo8cD5FbCBkZXBhcnRhbWVudG8gY3VlbnRhIGNvbiA8c3Ryb25nPnNlY2Fkb3JhIE1hYmU8L3N0cm9uZz4uIE5vIG5lY2VzaXRhcyB2YWNpYXIgbmluZ3VuYSBiYW5kZWphIGRlIGFndWE6IGxhIGV4cHVsc2Egc29sYSBwb3IgbGEgcGFydGUgZnJvbnRhbC4gU29sbyBjYXJnYSBsYSByb3BhIHkgZWxpZ2UgZWwgcHJvZ3JhbWEuPC9wPgo=', 'en' => 'PGg0Pk1hYmUgRHJ5ZXI8L2g0Pgo8cD5UaGUgYXBhcnRtZW50IGhhcyBhIDxzdHJvbmc+TWFiZSBkcnllcjwvc3Ryb25nPi4gVGhlcmUgaXMgbm8gd2F0ZXIgdHJheSB0byBlbXB0eTogaXQgZHJhaW5zIHRoZSB3YXRlciBieSBpdHNlbGYgdGhyb3VnaCB0aGUgZnJvbnQuIEp1c3QgbG9hZCB0aGUgY2xvdGhlcyBhbmQgcGljayBhIHByb2dyYW1tZS48L3A+Cg==', 'ptbr' => 'PGg0PlNlY2Fkb3JhIE1hYmU8L2g0Pgo8cD5PIGFwYXJ0YW1lbnRvIHBvc3N1aSA8c3Ryb25nPnNlY2Fkb3JhIE1hYmU8L3N0cm9uZz4uIE4mYXRpbGRlO28gJmVhY3V0ZTsgcHJlY2lzbyBlc3ZhemlhciBuZW5odW1hIGJhbmRlamEgZGUgJmFhY3V0ZTtndWE6IGVsYSBleHBlbGUgYSAmYWFjdXRlO2d1YSBzb3ppbmhhIHBlbGEgcGFydGUgZnJvbnRhbC4gQmFzdGEgY29sb2NhciBhcyByb3VwYXMgZSBlc2NvbGhlciBvIHByb2dyYW1hLjwvcD4K' );
$HVKY_T_ELE = '/electrodom|appliances|eletrodom/i';
$HVKY_T_SEC = '/secadora|dryer/i';

/**
 * Reemplaza (o agrega) el sub-bloque <h4> de la secadora DENTRO del bloque
 * <h3> de electrodomesticos. Devuelve array( contenido|null, motivo ).
 */
function hvky_aplicar( $html, $nuevo, $t_ele, $t_sec, $marca ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    $partes     = preg_split( '/(?=<h3>)/i', $html );
    $salida     = '';
    $encontrado = false;
    $cambiado   = false;
    foreach ( $partes as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $titulo = '';
        if ( 0 === stripos( ltrim( $parte ), '<h3>' ) && preg_match( '#<h3>(.*?)</h3>#is', $parte, $m ) ) {
            $titulo = $m[1];
        }
        if ( ! $titulo || ! preg_match( $t_ele, $titulo ) ) {
            $salida .= $parte;
            continue;
        }
        $encontrado = true;
        if ( false !== strpos( $parte, $marca ) ) {
            $salida .= $parte;
            continue;
        }
        // Dentro del bloque: separar por sub-bloques <h4>
        $subs      = preg_split( '/(?=<h4>)/i', $parte );
        $reconstru = '';
        $puesto    = false;
        foreach ( $subs as $sub ) {
            if ( '' === trim( $sub ) ) {
                continue;
            }
            $es_h4 = ( 0 === stripos( ltrim( $sub ), '<h4>' ) );
            $t4    = '';
            if ( $es_h4 && preg_match( '#<h4>(.*?)</h4>#is', $sub, $m4 ) ) {
                $t4 = $m4[1];
            }
            if ( $es_h4 && $t4 && preg_match( $t_sec, $t4 ) ) {
                if ( ! $puesto ) {
                    $reconstru .= $nuevo;
                    $puesto     = true;
                }
                continue;
            }
            $reconstru .= $sub;
        }
        if ( ! $puesto ) {
            $reconstru .= $nuevo;
        }
        $salida  .= $reconstru;
        $cambiado = true;
    }
    if ( ! $encontrado ) {
        return array( null, 'sin-bloque-electrodomesticos' );
    }
    if ( ! $cambiado ) {
        return array( null, 'ya-instalado' );
    }
    return array( $salida, 'ok' );
}

// ---- Pruebas locales ----
if ( getenv( 'HVKY_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $a = base64_decode( $GLOBALS['HVKY_1506']['es'] );
    $b = base64_decode( $GLOBALS['HVKY_306']['es'] );
    $chk( '1506: menciona solo Fensa', strpos( $a, 'secadora Fensa' ) !== false && stripos( $a, 'Bosch' ) === false );
    $chk( '1506: conserva estanque y video', stripos( $a, 'estanque' ) !== false && strpos( $a, 'youtube.com' ) !== false );
    $chk( '306: menciona Mabe y expulsion frontal', strpos( $b, 'secadora Mabe' ) !== false && stripos( $b, 'parte frontal' ) !== false );
    $chk( '306: sin estanque que vaciar', stripos( $b, 'no necesitas vaciar' ) !== false );

    // 1506 con el bloque de secadora dentro de electrodomesticos
    $u1506 = '<h3>Bienvenido</h3><p>x</p>'
        . '<h3>Cocina de Inducci&oacute;n</h3><p>induccion</p>'
        . '<h3>Electrodom&eacute;sticos</h3><h4>Lavavajillas Teka</h4><p>pasos Teka</p><p>manual.pdf</p>'
        . '<h4>Secadora de Ropa</h4><p>marca Fensa o Bosch; ambas funcionan igual</p><p><a href="/v">video viejo</a></p>'
        . '<h3>Supermercados y Servicios Cercanos</h3><p>Jumbo</p>';
    list( $r, $m ) = hvky_aplicar( $u1506, $a, $GLOBALS['HVKY_T_ELE'], $GLOBALS['HVKY_T_SEC'], 'Fensa</strong>' );
    $chk( '1506: aplicado', 'ok' === $m );
    $chk( '1506: elimina la mencion a Bosch', stripos( $r, 'Bosch' ) === false && stripos( $r, 'ambas funcionan igual' ) === false );
    $chk( '1506: conserva el lavavajillas y su manual', strpos( $r, 'pasos Teka' ) !== false && strpos( $r, 'manual.pdf' ) !== false );
    $chk( '1506: sigue dentro de Electrodomesticos', strpos( $r, 'Electrodom' ) < strpos( $r, 'Secadora Fensa' ) && strpos( $r, 'Secadora Fensa' ) < strpos( $r, 'Jumbo' ) );
    $chk( '1506: no toca los demas bloques h3', 4 === substr_count( strtolower( $r ), '<h3>' ) && strpos( $r, 'induccion' ) !== false );
    $chk( '1506: un solo sub-bloque de secadora', 1 === preg_match_all( '#<h4>[^<]*Secadora[^<]*</h4>#i', $r ) );
    list( $r2, $m2 ) = hvky_aplicar( $r, $a, $GLOBALS['HVKY_T_ELE'], $GLOBALS['HVKY_T_SEC'], 'Fensa</strong>' );
    $chk( '1506: idempotente', null === $r2 && 'ya-instalado' === $m2 );

    // 306: sin sub-bloque de secadora, se agrega
    $u306 = '<h3>Bienvenido</h3><p>x</p>'
        . '<h3>Electrodom&eacute;sticos</h3><h4>Lavavajillas Teka</h4><p>pasos Teka</p>'
        . '<h3>Basura y Reciclaje</h3><p>shaft</p>';
    list( $r3, $m3 ) = hvky_aplicar( $u306, $b, $GLOBALS['HVKY_T_ELE'], $GLOBALS['HVKY_T_SEC'], 'Mabe' );
    $chk( '306: aplicado', 'ok' === $m3 );
    $chk( '306: la secadora queda tras el lavavajillas', strpos( $r3, 'pasos Teka' ) < strpos( $r3, 'Secadora Mabe' ) );
    $chk( '306: dentro de Electrodomesticos, antes de Basura', strpos( $r3, 'Secadora Mabe' ) < strpos( $r3, 'shaft' ) );
    $chk( '306: sin Fensa ni Bosch', stripos( $r3, 'Fensa' ) === false && stripos( $r3, 'Bosch' ) === false );
    list( $r4, $m4 ) = hvky_aplicar( $r3, $b, $GLOBALS['HVKY_T_ELE'], $GLOBALS['HVKY_T_SEC'], 'Mabe' );
    $chk( '306: idempotente', null === $r4 && 'ya-instalado' === $m4 );

    list( $r5, $m5 ) = hvky_aplicar( '<h3>Solo esto</h3><p>x</p>', $a, $GLOBALS['HVKY_T_ELE'], $GLOBALS['HVKY_T_SEC'], 'Fensa</strong>' );
    $chk( 'sin bloque de electrodomesticos: no toca nada', null === $r5 && 'sin-bloque-electrodomesticos' === $m5 );

    foreach ( array( 'en' => 'Fensa dryer', 'ptbr' => 'secadora Fensa' ) as $l => $txt ) {
        $x = base64_decode( $GLOBALS['HVKY_1506'][ $l ] );
        $chk( "1506 $l: texto correcto sin Bosch", strpos( $x, $txt ) !== false && stripos( $x, 'Bosch' ) === false );
    }
    foreach ( array( 'en' => 'Mabe dryer', 'ptbr' => 'secadora Mabe' ) as $l => $txt ) {
        $x = base64_decode( $GLOBALS['HVKY_306'][ $l ] );
        $chk( "306 $l: texto correcto", strpos( $x, $txt ) !== false );
    }

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
    $b = get_option( 'hvkp_secadoras_backup' );
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

$config = array(
    'magnolio-1506' => array( 'bloques' => $HVKY_1506, 'marca' => 'Fensa</strong>', 'nombre' => 'secadora Fensa' ),
    'magnolio-306'  => array( 'bloques' => $HVKY_306,  'marca' => 'Mabe',           'nombre' => 'secadora Mabe' ),
);

$unidades = $wpdb->get_results( "SELECT id, name, slug FROM $tabla WHERE active = 1" );
$backup   = array();
foreach ( $unidades as $u ) {
    if ( ! isset( $config[ $u->slug ] ) ) {
        $warn[] = $u->name . ': sin secadora configurada, se omite';
        continue;
    }
    $cfg = $config[ $u->slug ];
    foreach ( array( 'es', 'en', 'ptbr' ) as $lang ) {
        $campo = 'general_' . $lang;
        $antes = (string) $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        list( $nuevo, $motivo ) = hvky_aplicar( $antes, base64_decode( $cfg['bloques'][ $lang ] ), $HVKY_T_ELE, $HVKY_T_SEC, $cfg['marca'] );
        if ( 'ya-instalado' === $motivo ) {
            $ok[] = "$u->name [$lang]: ya estaba correcto";
            continue;
        }
        if ( null === $nuevo ) {
            $warn[] = "$u->name [$lang]: sin cambios ($motivo)";
            continue;
        }
        $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => $antes );
        $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $u->id ) );
        $ok[] = "$u->name [$lang]: " . $cfg['nombre'];
    }
}
if ( $backup ) {
    update_option( 'hvkp_secadoras_backup', $backup, false );
}

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - Secadoras (Fensa en 1506, Mabe en 306) ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-secadoras.php\n";
echo "Para revertir: php hvkp-secadoras.php restore\n";
