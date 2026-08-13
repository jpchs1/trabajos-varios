<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Comida a domicilio (hvkp-delivery)
 *
 * Agrega el bloque "Comida a Domicilio" en Informacion General, justo despues
 * de "Supermercados y Servicios Cercanos": pedir por Uber Eats, la direccion
 * exacta que hay que escribir en la app (distinta para cada departamento) y el
 * retiro del pedido en el Lobby del piso -1 (Conserjeria).
 *
 * Solo base de datos. Uso:
 *   php hvkp-delivery.php            (aplicar)
 *   php hvkp-delivery.php restore    (quitar el bloque)
 */

$HVKD_BLOQUES = array(
    'es'   => 'PGg0PkNvbWlkYSBhIERvbWljaWxpbzwvaDQ+CjxwPlB1ZWRlcyBwZWRpciBjb21pZGEgYSBkb21pY2lsaW8gY29uIDxzdHJvbmc+VWJlciBFYXRzPC9zdHJvbmc+LCBxdWUgZnVuY2lvbmEgbXV5IGJpZW4gZW4gZWwgc2VjdG9yLiBMbyAmdWFjdXRlO25pY28gaW1wb3J0YW50ZSBlcyBlc2NyaWJpciBiaWVuIGxhIGRpcmVjY2kmb2FjdXRlO24gZW4gbGEgYXBsaWNhY2kmb2FjdXRlO246PC9wPgo8cCBzdHlsZT0iYmFja2dyb3VuZDojZjZmN2ZiO2JvcmRlcjoxcHggc29saWQgI2U2ZThlZjtib3JkZXItbGVmdDo0cHggc29saWQgI2U4NDM5Mztib3JkZXItcmFkaXVzOjEycHg7cGFkZGluZzoxNHB4IDE2cHg7bWFyZ2luOjEycHggMDtsaW5lLWhlaWdodDoxLjg7Zm9udC13ZWlnaHQ6NzAwO2NvbG9yOiMxYzIwMzMiPl9fRElSRUNDSU9OX188L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmZmY4ZWQ7Ym9yZGVyOjFweCBzb2xpZCAjZmNkOWE4O2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZjU5ZTBiO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwO2xpbmUtaGVpZ2h0OjEuNjtjb2xvcjojNDMzMzFmIj48c3Ryb25nPlJldGlyYSB0dSBwZWRpZG8gZW4gZWwgTG9iYnkgKHBpc28gLTEsIENvbnNlcmplciZpYWN1dGU7YSkuPC9zdHJvbmc+IExvcyByZXBhcnRpZG9yZXMgbm8gc3ViZW4gYSBsb3MgZGVwYXJ0YW1lbnRvcywgYXMmaWFjdXRlOyBxdWUgYmFqYSBjdWFuZG8gdGUgYXZpc2VuIHF1ZSBsbGVnJm9hY3V0ZTsuPC9wPgo=',
    'en'   => 'PGg0PkZvb2QgRGVsaXZlcnk8L2g0Pgo8cD5Zb3UgY2FuIG9yZGVyIGZvb2Qgd2l0aCA8c3Ryb25nPlViZXIgRWF0czwvc3Ryb25nPiwgd2hpY2ggd29ya3MgdmVyeSB3ZWxsIGluIHRoaXMgYXJlYS4gVGhlIG9ubHkgaW1wb3J0YW50IHRoaW5nIGlzIGVudGVyaW5nIHRoZSBhZGRyZXNzIGNvcnJlY3RseSBpbiB0aGUgYXBwOjwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2Y2ZjdmYjtib3JkZXI6MXB4IHNvbGlkICNlNmU4ZWY7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNlODQzOTM7Ym9yZGVyLXJhZGl1czoxMnB4O3BhZGRpbmc6MTRweCAxNnB4O21hcmdpbjoxMnB4IDA7bGluZS1oZWlnaHQ6MS44O2ZvbnQtd2VpZ2h0OjcwMDtjb2xvcjojMWMyMDMzIj5fX0RJUkVDQ0lPTl9fPC9wPgo8cCBzdHlsZT0iYmFja2dyb3VuZDojZmZmOGVkO2JvcmRlcjoxcHggc29saWQgI2ZjZDlhODtib3JkZXItbGVmdDo0cHggc29saWQgI2Y1OWUwYjtib3JkZXItcmFkaXVzOjEycHg7cGFkZGluZzoxNHB4IDE2cHg7bWFyZ2luOjEycHggMDtsaW5lLWhlaWdodDoxLjY7Y29sb3I6IzQzMzMxZiI+PHN0cm9uZz5QaWNrIHVwIHlvdXIgb3JkZXIgYXQgdGhlIExvYmJ5IChsZXZlbCAtMSwgZnJvbnQgZGVzaykuPC9zdHJvbmc+IENvdXJpZXJzIGFyZSBub3QgYWxsb3dlZCB1cCB0byB0aGUgYXBhcnRtZW50cywgc28gZ28gZG93biB3aGVuIHlvdSBhcmUgbm90aWZpZWQgdGhhdCB5b3VyIG9yZGVyIGhhcyBhcnJpdmVkLjwvcD4K',
    'ptbr' => 'PGg0PkRlbGl2ZXJ5IGRlIENvbWlkYTwvaDQ+CjxwPlZvYyZlY2lyYzsgcG9kZSBwZWRpciBjb21pZGEgcGVsbyA8c3Ryb25nPlViZXIgRWF0czwvc3Ryb25nPiwgcXVlIGZ1bmNpb25hIG11aXRvIGJlbSBuYSByZWdpJmF0aWxkZTtvLiBPIGltcG9ydGFudGUgJmVhY3V0ZTsgZGlnaXRhciBjb3JyZXRhbWVudGUgbyBlbmRlcmUmY2NlZGlsO28gbm8gYXBsaWNhdGl2bzo8L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmNmY3ZmI7Ym9yZGVyOjFweCBzb2xpZCAjZTZlOGVmO2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZTg0MzkzO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwO2xpbmUtaGVpZ2h0OjEuODtmb250LXdlaWdodDo3MDA7Y29sb3I6IzFjMjAzMyI+X19ESVJFQ0NJT05fXzwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2ZmZjhlZDtib3JkZXI6MXB4IHNvbGlkICNmY2Q5YTg7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNmNTllMGI7Ym9yZGVyLXJhZGl1czoxMnB4O3BhZGRpbmc6MTRweCAxNnB4O21hcmdpbjoxMnB4IDA7bGluZS1oZWlnaHQ6MS42O2NvbG9yOiM0MzMzMWYiPjxzdHJvbmc+UmV0aXJlIG8gcGVkaWRvIG5vIExvYmJ5IChwaXNvIC0xLCBwb3J0YXJpYSkuPC9zdHJvbmc+IE9zIGVudHJlZ2Fkb3JlcyBuJmF0aWxkZTtvIHNvYmVtIGF0JmVhY3V0ZTsgb3MgYXBhcnRhbWVudG9zLCBlbnQmYXRpbGRlO28gZGVzJmNjZWRpbDthIHF1YW5kbyBhdmlzYXJlbSBxdWUgY2hlZ291LjwvcD4K',
);
$HVKD_MARCA = 'Uber Eats';

// Direccion a mostrar en la app, por unidad. La torre solo se afirma donde se
// confirmo; para las demas se omite en vez de inventarla.
$HVKD_DIRECCIONES = array(
    'magnolio-306'  => 'Camino el Parque 100, Vitacura<br>Torre 1 &mdash; Edificio Magnolio<br>Depto 306',
    'magnolio-1506' => 'Camino el Parque 100, Vitacura<br>Edificio Magnolio<br>Depto 1506',
);

/**
 * Inserta el bloque despues del <h4> de supermercados; si no existe, al final.
 * Devuelve array( contenido_nuevo|null, motivo ).
 */
function hvkd_insertar( $html, $nuevo, $marca ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    if ( false !== strpos( $html, $marca ) ) {
        return array( null, 'ya-instalado' );
    }
    $partes    = preg_split( '/(?=<h4>)/i', $html );
    $salida    = '';
    $insertado = false;
    foreach ( $partes as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $salida .= $parte;
        if ( $insertado ) {
            continue;
        }
        if ( 0 === stripos( ltrim( $parte ), '<h4>' )
            && preg_match( '#<h4>(.*?)</h4>#is', $parte, $m )
            && preg_match( '/supermercado|supermarket|servicios cercanos|nearby/i', $m[1] ) ) {
            $salida   .= $nuevo;
            $insertado = true;
        }
    }
    if ( ! $insertado ) {
        $salida   .= $nuevo;
        $insertado = true;
        return array( $salida, 'ok-al-final' );
    }
    return array( $salida, 'ok' );
}

// ---- Pruebas locales ----
if ( getenv( 'HVKD_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $nuevo = str_replace( '__DIRECCION__', $GLOBALS['HVKD_DIRECCIONES']['magnolio-306'], base64_decode( $GLOBALS['HVKD_BLOQUES']['es'] ) );
    $prev  = '<h3>Informaci&oacute;n General</h3><p>intro</p>'
        . '<h4>Calefacci&oacute;n</h4><p>losa radiante</p>'
        . '<h4>Supermercados y Servicios Cercanos</h4><p>Jumbo Portal La Dehesa</p>'
        . '<h4>Basura y Reciclaje</h4><p>shaft</p>';
    list( $out, $why ) = hvkd_insertar( $prev, $nuevo, $GLOBALS['HVKD_MARCA'] );
    $chk( 'inserta el bloque', 'ok' === $why );
    $chk( 'queda justo despues de supermercados', strpos( $out, 'Jumbo Portal La Dehesa' ) < strpos( $out, 'Uber Eats' ) && strpos( $out, 'Uber Eats' ) < strpos( $out, 'shaft' ) );
    $chk( 'ahora hay 4 bloques', 4 === substr_count( strtolower( $out ), '<h4>' ) );
    $chk( 'conserva todo lo anterior', strpos( $out, 'losa radiante' ) !== false && strpos( $out, 'shaft' ) !== false && strpos( $out, 'intro' ) !== false );
    $chk( 'muestra la direccion del 306 con torre', strpos( $out, 'Camino el Parque 100' ) !== false && strpos( $out, 'Torre 1' ) !== false && strpos( $out, 'Depto 306' ) !== false );
    $chk( 'no quedan marcadores sin reemplazar', strpos( $out, '__DIRECCION__' ) === false );
    $chk( 'menciona el retiro en el Lobby piso -1', stripos( $out, 'Lobby' ) !== false && stripos( $out, 'piso -1' ) !== false );
    list( $o2, $w2 ) = hvkd_insertar( $out, $nuevo, $GLOBALS['HVKD_MARCA'] );
    $chk( 'idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvkd_insertar( '<h4>Otro</h4><p>x</p>', $nuevo, $GLOBALS['HVKD_MARCA'] );
    $chk( 'sin supermercados: lo agrega al final', 'ok-al-final' === $w3 && strpos( $o3, 'Uber Eats' ) > strpos( $o3, 'Otro' ) );

    $n1506 = str_replace( '__DIRECCION__', $GLOBALS['HVKD_DIRECCIONES']['magnolio-1506'], base64_decode( $GLOBALS['HVKD_BLOQUES']['es'] ) );
    $chk( '1506: direccion con su depto y sin inventar torre', strpos( $n1506, 'Depto 1506' ) !== false && strpos( $n1506, 'Torre' ) === false );

    foreach ( array( 'en' => 'Food Delivery', 'ptbr' => 'Delivery de Comida' ) as $l => $titulo ) {
        $b = str_replace( '__DIRECCION__', $GLOBALS['HVKD_DIRECCIONES']['magnolio-306'], base64_decode( $GLOBALS['HVKD_BLOQUES'][ $l ] ) );
        $chk( "$l: titulo y direccion correctos", strpos( $b, $titulo ) !== false && strpos( $b, 'Camino el Parque 100' ) !== false && strpos( $b, '__DIRECCION__' ) === false );
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
    $b = get_option( 'hvkp_delivery_backup' );
    if ( ! is_array( $b ) || empty( $b ) ) {
        echo "[AVISO] No hay respaldo; nada que restaurar.\n";
        exit( 0 );
    }
    foreach ( $b as $fila ) {
        $wpdb->update( $tabla, array( $fila['campo'] => $fila['valor'] ), array( 'id' => (int) $fila['id'] ) );
    }
    echo "[OK]    Contenido anterior restaurado (" . count( $b ) . " campos)\n";
    exit( 0 );
}

$unidades = $wpdb->get_results( "SELECT id, name, slug FROM $tabla WHERE active = 1" );
if ( ! $unidades ) {
    echo "[ERROR] No se encontraron unidades activas.\n";
    exit( 1 );
}

$backup = array();
foreach ( $unidades as $u ) {
    if ( ! isset( $HVKD_DIRECCIONES[ $u->slug ] ) ) {
        $warn[] = $u->name . ': sin direccion definida para delivery, se omite';
        continue;
    }
    $direccion = $HVKD_DIRECCIONES[ $u->slug ];
    foreach ( $HVKD_BLOQUES as $lang => $b64 ) {
        $campo  = 'general_' . $lang;
        $bloque = str_replace( '__DIRECCION__', $direccion, base64_decode( $b64 ) );
        $actual = $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        list( $nuevo, $motivo ) = hvkd_insertar( (string) $actual, $bloque, $HVKD_MARCA );
        if ( 'ya-instalado' === $motivo ) {
            $ok[] = "$u->name [$lang]: ya estaba agregado";
            continue;
        }
        if ( null === $nuevo ) {
            $warn[] = "$u->name [$lang]: sin cambios ($motivo)";
            continue;
        }
        $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => (string) $actual );
        $wpdb->update( $tabla, array( $campo => $nuevo ), array( 'id' => (int) $u->id ) );
        $ok[] = "$u->name [$lang]: comida a domicilio agregada" . ( 'ok-al-final' === $motivo ? ' (al final de la seccion)' : '' );
    }
}
if ( $backup ) {
    update_option( 'hvkp_delivery_backup', $backup, false );
}
$warn[] = 'Magnolio 1506: la direccion quedo sin numero de torre (no estaba confirmada). Avisar si corresponde agregarla.';

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - Comida a domicilio ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-delivery.php\n";
echo "Para revertir: php hvkp-delivery.php restore\n";
