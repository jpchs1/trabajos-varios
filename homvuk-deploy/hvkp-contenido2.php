<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Contenidos de Informacion General (hvkp-contenido2)
 *
 * La seccion "Informacion General" separa sus bloques con <h3>, no con <h4>
 * (a diferencia de check-in, check-out y reglas). Por eso el intento anterior
 * no encontro los bloques y el de comida a domicilio quedo pegado dentro del
 * ultimo acordeon. Este instalador lo corrige y aplica:
 *
 *   1. Elimina el bloque de comida a domicilio mal insertado (si quedo).
 *   2. Calefaccion -> losa radiante, termostato 20-21 grados (ambas unidades).
 *   3. Secadora -> se elimina el bloque completo en Magnolio 306 (la Mabe no
 *      necesita vaciar bandeja, no hay nada que explicar). El 1506 no se toca.
 *   4. Comida a domicilio -> bloque propio, despues de Supermercados.
 *
 * Solo base de datos. Uso:
 *   php hvkp-contenido2.php            (aplicar)
 *   php hvkp-contenido2.php restore    (volver al contenido anterior)
 */

$HVKX = array(
    'calefaccion' => array( 'es' => 'PGgzPkNhbGVmYWNjaSZvYWN1dGU7bjwvaDM+CjxwPkVuIGludmllcm5vIGVsIGRlcGFydGFtZW50byBzZSBjYWxlZmFjY2lvbmEgcG9yIDxzdHJvbmc+bG9zYSByYWRpYW50ZTwvc3Ryb25nPiBjb25lY3RhZGEgYSBsYSBjYWxkZXJhIGRlbCBlZGlmaWNpbywgeSBmdW5jaW9uYSBwZXJmZWN0YW1lbnRlOiBlbCBjYWxvciBzdWJlIGRlc2RlIGVsIHBpc28gZGUgZm9ybWEgcGFyZWphIHkgc2lsZW5jaW9zYSwgc2luIHJ1aWRvIG5pIGVxdWlwb3MgYSBsYSB2aXN0YS48L3A+CjxwPjxzdHJvbmc+TWFudCZlYWN1dGU7biBlbCB0ZXJtb3N0YXRvIGVudHJlIDIwJmRlZzsgeSAyMSZkZWc7QyBjb21vIG0mYWFjdXRlO3hpbW8uPC9zdHJvbmc+IEVzIGxhIHRlbXBlcmF0dXJhIGlkZWFsIGRlIGNvbmZvcnQ7IHN1YmlybG8gbSZhYWN1dGU7cyBubyBjYWxpZW50YSBtJmFhY3V0ZTtzIHImYWFjdXRlO3BpZG8geSBzb2xvIGdlbmVyYSB1biBnYXN0byBpbm5lY2VzYXJpby48L3A+CjxwPlRlbiBwcmVzZW50ZSBxdWUgbGEgbG9zYSByYWRpYW50ZSB0YXJkYSB1biBwb2NvIGVuIHJlYWNjaW9uYXI6IGRlc3B1JmVhY3V0ZTtzIGRlIGNhbWJpYXIgbGEgdGVtcGVyYXR1cmEsIGVzcGVyYSB1biBwYXIgZGUgaG9yYXMgYW50ZXMgZGUgdm9sdmVyIGEgYWp1c3RhcmxhLjwvcD4K', 'en' => 'PGgzPkhlYXRpbmc8L2gzPgo8cD5JbiB3aW50ZXIgdGhlIGFwYXJ0bWVudCBpcyBoZWF0ZWQgYnkgPHN0cm9uZz5yYWRpYW50IGZsb29yIGhlYXRpbmc8L3N0cm9uZz4gY29ubmVjdGVkIHRvIHRoZSBidWlsZGluZyZyc3F1bztzIGJvaWxlciwgYW5kIGl0IHdvcmtzIHBlcmZlY3RseTogdGhlIGhlYXQgcmlzZXMgZXZlbmx5IGFuZCBzaWxlbnRseSBmcm9tIHRoZSBmbG9vciwgd2l0aCBubyBub2lzZSBhbmQgbm8gdmlzaWJsZSBlcXVpcG1lbnQuPC9wPgo8cD48c3Ryb25nPktlZXAgdGhlIHRoZXJtb3N0YXQgYmV0d2VlbiAyMCZkZWc7IGFuZCAyMSZkZWc7QyAoNjgmbmRhc2g7NzAmZGVnO0YpIG1heGltdW0uPC9zdHJvbmc+IFRoYXQgaXMgdGhlIGlkZWFsIGNvbWZvcnQgdGVtcGVyYXR1cmU7IHNldHRpbmcgaXQgaGlnaGVyIHdpbGwgbm90IGhlYXQgdGhlIGFwYXJ0bWVudCBhbnkgZmFzdGVyIGFuZCBvbmx5IHdhc3RlcyBlbmVyZ3kuPC9wPgo8cD5LZWVwIGluIG1pbmQgdGhhdCByYWRpYW50IGZsb29yIGhlYXRpbmcgcmVhY3RzIHNsb3dseTogYWZ0ZXIgY2hhbmdpbmcgdGhlIHRlbXBlcmF0dXJlLCB3YWl0IGEgY291cGxlIG9mIGhvdXJzIGJlZm9yZSBhZGp1c3RpbmcgaXQgYWdhaW4uPC9wPgo=', 'ptbr' => 'PGgzPkFxdWVjaW1lbnRvPC9oMz4KPHA+Tm8gaW52ZXJubyBvIGFwYXJ0YW1lbnRvICZlYWN1dGU7IGFxdWVjaWRvIHBvciA8c3Ryb25nPnBpc28gcmFkaWFudGU8L3N0cm9uZz4gbGlnYWRvICZhZ3JhdmU7IGNhbGRlaXJhIGRvIGVkaWYmaWFjdXRlO2NpbywgZSBmdW5jaW9uYSBwZXJmZWl0YW1lbnRlOiBvIGNhbG9yIHNvYmUgZG8gcGlzbyBkZSBmb3JtYSB1bmlmb3JtZSBlIHNpbGVuY2lvc2EsIHNlbSBydSZpYWN1dGU7ZG8gbmVtIGVxdWlwYW1lbnRvcyAmYWdyYXZlOyB2aXN0YS48L3A+CjxwPjxzdHJvbmc+TWFudGVuaGEgbyB0ZXJtb3N0YXRvIGVudHJlIDIwJmRlZzsgZSAyMSZkZWc7QyBubyBtJmFhY3V0ZTt4aW1vLjwvc3Ryb25nPiAmRWFjdXRlOyBhIHRlbXBlcmF0dXJhIGlkZWFsIGRlIGNvbmZvcnRvOyBhdW1lbnRhciBtYWlzIG4mYXRpbGRlO28gYXF1ZWNlIG1haXMgciZhYWN1dGU7cGlkbyBlIHMmb2FjdXRlOyBnZXJhIGdhc3RvIGRlc25lY2VzcyZhYWN1dGU7cmlvLjwvcD4KPHA+TGVtYnJlLXNlIGRlIHF1ZSBvIHBpc28gcmFkaWFudGUgZGVtb3JhIGEgcmVhZ2lyOiBkZXBvaXMgZGUgbXVkYXIgYSB0ZW1wZXJhdHVyYSwgZXNwZXJlIGFsZ3VtYXMgaG9yYXMgYW50ZXMgZGUgYWp1c3RhciBub3ZhbWVudGUuPC9wPgo=' ),
    'delivery'    => array( 'es' => 'PGgzPkNvbWlkYSBhIERvbWljaWxpbzwvaDM+CjxwPlB1ZWRlcyBwZWRpciBjb21pZGEgYSBkb21pY2lsaW8gY29uIDxzdHJvbmc+VWJlciBFYXRzPC9zdHJvbmc+LCBxdWUgZnVuY2lvbmEgbXV5IGJpZW4gZW4gZWwgc2VjdG9yLiBMbyAmdWFjdXRlO25pY28gaW1wb3J0YW50ZSBlcyBlc2NyaWJpciBiaWVuIGxhIGRpcmVjY2kmb2FjdXRlO24gZW4gbGEgYXBsaWNhY2kmb2FjdXRlO246PC9wPgo8cCBzdHlsZT0iYmFja2dyb3VuZDojZjZmN2ZiO2JvcmRlcjoxcHggc29saWQgI2U2ZThlZjtib3JkZXItbGVmdDo0cHggc29saWQgI2U4NDM5Mztib3JkZXItcmFkaXVzOjEycHg7cGFkZGluZzoxNHB4IDE2cHg7bWFyZ2luOjEycHggMDtsaW5lLWhlaWdodDoxLjg7Zm9udC13ZWlnaHQ6NzAwO2NvbG9yOiMxYzIwMzMiPl9fRElSRUNDSU9OX188L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmZmY4ZWQ7Ym9yZGVyOjFweCBzb2xpZCAjZmNkOWE4O2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZjU5ZTBiO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwO2xpbmUtaGVpZ2h0OjEuNjtjb2xvcjojNDMzMzFmIj48c3Ryb25nPlJldGlyYSB0dSBwZWRpZG8gZW4gZWwgTG9iYnkgKHBpc28gLTEsIENvbnNlcmplciZpYWN1dGU7YSkuPC9zdHJvbmc+IExvcyByZXBhcnRpZG9yZXMgbm8gc3ViZW4gYSBsb3MgZGVwYXJ0YW1lbnRvcywgYXMmaWFjdXRlOyBxdWUgYmFqYSBjdWFuZG8gdGUgYXZpc2VuIHF1ZSBsbGVnJm9hY3V0ZTsuPC9wPgo=', 'en' => 'PGgzPkZvb2QgRGVsaXZlcnk8L2gzPgo8cD5Zb3UgY2FuIG9yZGVyIGZvb2Qgd2l0aCA8c3Ryb25nPlViZXIgRWF0czwvc3Ryb25nPiwgd2hpY2ggd29ya3MgdmVyeSB3ZWxsIGluIHRoaXMgYXJlYS4gVGhlIG9ubHkgaW1wb3J0YW50IHRoaW5nIGlzIGVudGVyaW5nIHRoZSBhZGRyZXNzIGNvcnJlY3RseSBpbiB0aGUgYXBwOjwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2Y2ZjdmYjtib3JkZXI6MXB4IHNvbGlkICNlNmU4ZWY7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNlODQzOTM7Ym9yZGVyLXJhZGl1czoxMnB4O3BhZGRpbmc6MTRweCAxNnB4O21hcmdpbjoxMnB4IDA7bGluZS1oZWlnaHQ6MS44O2ZvbnQtd2VpZ2h0OjcwMDtjb2xvcjojMWMyMDMzIj5fX0RJUkVDQ0lPTl9fPC9wPgo8cCBzdHlsZT0iYmFja2dyb3VuZDojZmZmOGVkO2JvcmRlcjoxcHggc29saWQgI2ZjZDlhODtib3JkZXItbGVmdDo0cHggc29saWQgI2Y1OWUwYjtib3JkZXItcmFkaXVzOjEycHg7cGFkZGluZzoxNHB4IDE2cHg7bWFyZ2luOjEycHggMDtsaW5lLWhlaWdodDoxLjY7Y29sb3I6IzQzMzMxZiI+PHN0cm9uZz5QaWNrIHVwIHlvdXIgb3JkZXIgYXQgdGhlIExvYmJ5IChsZXZlbCAtMSwgZnJvbnQgZGVzaykuPC9zdHJvbmc+IENvdXJpZXJzIGFyZSBub3QgYWxsb3dlZCB1cCB0byB0aGUgYXBhcnRtZW50cywgc28gZ28gZG93biB3aGVuIHlvdSBhcmUgbm90aWZpZWQgdGhhdCB5b3VyIG9yZGVyIGhhcyBhcnJpdmVkLjwvcD4K', 'ptbr' => 'PGgzPkRlbGl2ZXJ5IGRlIENvbWlkYTwvaDM+CjxwPlZvYyZlY2lyYzsgcG9kZSBwZWRpciBjb21pZGEgcGVsbyA8c3Ryb25nPlViZXIgRWF0czwvc3Ryb25nPiwgcXVlIGZ1bmNpb25hIG11aXRvIGJlbSBuYSByZWdpJmF0aWxkZTtvLiBPIGltcG9ydGFudGUgJmVhY3V0ZTsgZGlnaXRhciBjb3JyZXRhbWVudGUgbyBlbmRlcmUmY2NlZGlsO28gbm8gYXBsaWNhdGl2bzo8L3A+CjxwIHN0eWxlPSJiYWNrZ3JvdW5kOiNmNmY3ZmI7Ym9yZGVyOjFweCBzb2xpZCAjZTZlOGVmO2JvcmRlci1sZWZ0OjRweCBzb2xpZCAjZTg0MzkzO2JvcmRlci1yYWRpdXM6MTJweDtwYWRkaW5nOjE0cHggMTZweDttYXJnaW46MTJweCAwO2xpbmUtaGVpZ2h0OjEuODtmb250LXdlaWdodDo3MDA7Y29sb3I6IzFjMjAzMyI+X19ESVJFQ0NJT05fXzwvcD4KPHAgc3R5bGU9ImJhY2tncm91bmQ6I2ZmZjhlZDtib3JkZXI6MXB4IHNvbGlkICNmY2Q5YTg7Ym9yZGVyLWxlZnQ6NHB4IHNvbGlkICNmNTllMGI7Ym9yZGVyLXJhZGl1czoxMnB4O3BhZGRpbmc6MTRweCAxNnB4O21hcmdpbjoxMnB4IDA7bGluZS1oZWlnaHQ6MS42O2NvbG9yOiM0MzMzMWYiPjxzdHJvbmc+UmV0aXJlIG8gcGVkaWRvIG5vIExvYmJ5IChwaXNvIC0xLCBwb3J0YXJpYSkuPC9zdHJvbmc+IE9zIGVudHJlZ2Fkb3JlcyBuJmF0aWxkZTtvIHNvYmVtIGF0JmVhY3V0ZTsgb3MgYXBhcnRhbWVudG9zLCBlbnQmYXRpbGRlO28gZGVzJmNjZWRpbDthIHF1YW5kbyBhdmlzYXJlbSBxdWUgY2hlZ291LjwvcD4K' ),
);

$HVKX_DIRECCIONES = array(
    'magnolio-306'  => 'Camino el Parque 100, Vitacura<br>Torre 1 &mdash; Edificio Magnolio<br>Depto 306',
    'magnolio-1506' => 'Camino el Parque 100, Vitacura<br>Edificio Magnolio<br>Depto 1506',
);

/** Quita el bloque de delivery que se inserto antes como <h4>. */
function hvkx_limpiar( $html ) {
    return preg_replace(
        '#<h4>\s*(?:Comida a Domicilio|Food Delivery|Delivery de Comida)\s*</h4>(?:(?!<h3>).)*#is',
        '',
        (string) $html
    );
}

/** Recorre los bloques <h3> del contenido y devuelve array( titulo, html ). */
function hvkx_bloques( $html ) {
    $out = array();
    foreach ( preg_split( '/(?=<h3>)/i', (string) $html ) as $parte ) {
        if ( '' === trim( $parte ) ) {
            continue;
        }
        $titulo = '';
        if ( 0 === stripos( ltrim( $parte ), '<h3>' ) && preg_match( '#<h3>(.*?)</h3>#is', $parte, $m ) ) {
            $titulo = $m[1];
        }
        $out[] = array( $titulo, $parte );
    }
    return $out;
}

/** Elimina el bloque <h3> cuyo titulo coincide. */
function hvkx_eliminar( $html, $titulos ) {
    $salida    = '';
    $eliminado = false;
    foreach ( hvkx_bloques( $html ) as $b ) {
        if ( $b[0] && preg_match( $titulos, $b[0] ) ) {
            $eliminado = true;
            continue;
        }
        $salida .= $b[1];
    }
    return array( $eliminado ? $salida : null, $eliminado ? 'eliminado' : 'no-estaba' );
}

/**
 * Reemplaza el bloque <h3> indicado; si no existe y hay ancla, lo inserta
 * despues del bloque ancla (o al final si tampoco esta).
 */
function hvkx_aplicar( $html, $nuevo, $titulos, $ancla = '' ) {
    if ( ! is_string( $html ) || '' === trim( $html ) ) {
        return array( null, 'contenido-vacio' );
    }
    $salida         = '';
    $reemplazado    = false;
    $posicion_ancla = null;
    foreach ( hvkx_bloques( $html ) as $b ) {
        list( $titulo, $parte ) = $b;
        if ( $titulo && preg_match( $titulos, $titulo ) ) {
            if ( ! $reemplazado ) {
                $salida     .= $nuevo;
                $reemplazado = true;
            }
            continue;
        }
        $salida .= $parte;
        if ( ! $reemplazado && null === $posicion_ancla && $ancla && $titulo && preg_match( $ancla, $titulo ) ) {
            $posicion_ancla = strlen( $salida );
        }
    }
    if ( $reemplazado ) {
        return array( $salida, 'reemplazado' );
    }
    if ( null !== $posicion_ancla ) {
        return array( substr( $salida, 0, $posicion_ancla ) . $nuevo . substr( $salida, $posicion_ancla ), 'insertado' );
    }
    if ( $ancla ) {
        return array( $salida . $nuevo, 'insertado-al-final' );
    }
    return array( null, 'no-se-encontro-el-bloque' );
}

$HVKX_T_CAL = '/calefacci|heating|aquecimento/i';
$HVKX_T_SEC = '/secadora|dryer/i';
$HVKX_T_DEL = '/comida a domicilio|food delivery|delivery de comida/i';
$HVKX_A_DEL = '/supermercado|supermarket|nearby/i';

// ---- Pruebas locales ----
if ( getenv( 'HVKX_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $cal = base64_decode( $GLOBALS['HVKX']['calefaccion']['es'] );
    $del = str_replace( '__DIRECCION__', $GLOBALS['HVKX_DIRECCIONES']['magnolio-306'], base64_decode( $GLOBALS['HVKX']['delivery']['es'] ) );

    // Igual a produccion en el 306: bloques <h3> + delivery mal pegado al final
    $prod = '<h3>Bienvenido</h3><p>intro</p>'
        . '<h3>Secadora de Ropa</h3><p>marca Fensa o Bosch, vaciar el estanque</p><p><a href="/v.mp4">Ver video</a></p>'
        . '<h3>Calefacci&oacute;n</h3><p>central por caldera, 21 grados</p>'
        . '<h3>Supermercados y Servicios Cercanos</h3><p>Jumbo Portal La Dehesa</p>'
        . '<h3>Basura y Reciclaje</h3><p>shaft</p>'
        . '<h3>Ventanas de la Terraza</h3><p>video terraza</p>'
        . '<h4>Comida a Domicilio</h4><p>Uber Eats mal insertado</p>';
    $chk( 'punto de partida: 6 bloques y un h4 colgando', 6 === substr_count( strtolower( $prod ), '<h3>' ) && strpos( $prod, '<h4>' ) !== false );

    $p = hvkx_limpiar( $prod );
    $chk( 'limpia el bloque mal insertado', strpos( $p, 'Uber Eats' ) === false && strpos( $p, '<h4>' ) === false );
    $chk( 'la limpieza conserva el resto', strpos( $p, 'Ventanas de la Terraza' ) !== false && strpos( $p, 'video terraza' ) !== false );

    list( $p, $m ) = hvkx_aplicar( $p, $cal, $GLOBALS['HVKX_T_CAL'] );
    $chk( 'calefaccion: reemplazada', 'reemplazado' === $m && stripos( $p, 'losa radiante' ) !== false );
    $chk( 'calefaccion: elimina el texto antiguo', strpos( $p, 'central por caldera' ) === false );
    $chk( 'calefaccion: incluye 20 y 21 grados', strpos( $p, '20&deg; y 21&deg;C' ) !== false );

    list( $p, $m ) = hvkx_eliminar( $p, $GLOBALS['HVKX_T_SEC'] );
    $chk( 'secadora: bloque eliminado', 'eliminado' === $m && stripos( $p, 'Secadora de Ropa' ) === false );
    $chk( 'secadora: se va tambien el video de vaciado', strpos( $p, 'v.mp4' ) === false && stripos( $p, 'Bosch' ) === false );

    list( $p, $m ) = hvkx_aplicar( $p, $del, $GLOBALS['HVKX_T_DEL'], $GLOBALS['HVKX_A_DEL'] );
    $chk( 'delivery: insertado tras supermercados', 'insertado' === $m );
    $chk( 'delivery: queda entre supermercados y basura', strpos( $p, 'Jumbo' ) < strpos( $p, 'Uber Eats' ) && strpos( $p, 'Uber Eats' ) < strpos( $p, 'shaft' ) );
    $chk( 'delivery: con su propio <h3>', preg_match( '#<h3>\s*Comida a Domicilio\s*</h3>#i', $p ) === 1 );
    $chk( 'delivery: sin marcadores pendientes', strpos( $p, '__DIRECCION__' ) === false );
    $chk( 'delivery: direccion y retiro en Lobby', strpos( $p, 'Torre 1' ) !== false && stripos( $p, 'piso -1' ) !== false );

    $chk( 'no quedan <h4> sueltos', strpos( $p, '<h4>' ) === false );
    $chk( 'estructura final: 6 bloques (se fue secadora, entro delivery)', 6 === substr_count( strtolower( $p ), '<h3>' ) );
    $chk( 'conserva la bienvenida al inicio', strpos( $p, '<h3>Bienvenido</h3>' ) === 0 );
    $chk( 'conserva basura y ventanas', strpos( $p, 'shaft' ) !== false && strpos( $p, 'video terraza' ) !== false );

    // Segunda pasada completa: nada se duplica
    $q = hvkx_limpiar( $p );
    list( $r1, $x1 ) = hvkx_aplicar( $q, $cal, $GLOBALS['HVKX_T_CAL'] );
    $q = ( null !== $r1 ) ? $r1 : $q;
    list( $r2, $x2 ) = hvkx_eliminar( $q, $GLOBALS['HVKX_T_SEC'] );
    $q = ( null !== $r2 ) ? $r2 : $q;
    list( $r3, $x3 ) = hvkx_aplicar( $q, $del, $GLOBALS['HVKX_T_DEL'], $GLOBALS['HVKX_A_DEL'] );
    $q = ( null !== $r3 ) ? $r3 : $q;
    $chk( 'idempotente: misma cantidad de bloques', 6 === substr_count( strtolower( $q ), '<h3>' ) );
    $chk( 'idempotente: un solo bloque de delivery', 1 === substr_count( $q, 'Uber Eats' ) && 'reemplazado' === $x3 );
    $chk( 'idempotente: secadora ya no estaba', 'no-estaba' === $x2 );
    $chk( 'idempotente: contenido identico', $q === $p );

    // El 1506 conserva su secadora
    $prod_1506 = '<h3>Bienvenido</h3><p>x</p><h3>Secadora de Ropa</h3><p>Bosch estanque</p><h3>Calefacci&oacute;n</h3><p>caldera</p><h3>Supermercados y Servicios Cercanos</h3><p>Jumbo</p>';
    list( $s1, $y1 ) = hvkx_aplicar( $prod_1506, $cal, $GLOBALS['HVKX_T_CAL'] );
    list( $s2, $y2 ) = hvkx_aplicar( $s1, str_replace( '__DIRECCION__', $GLOBALS['HVKX_DIRECCIONES']['magnolio-1506'], base64_decode( $GLOBALS['HVKX']['delivery']['es'] ) ), $GLOBALS['HVKX_T_DEL'], $GLOBALS['HVKX_A_DEL'] );
    $chk( '1506: conserva su bloque de secadora', stripos( $s2, 'Secadora de Ropa' ) !== false && stripos( $s2, 'Bosch' ) !== false );
    $chk( '1506: direccion propia sin inventar torre', strpos( $s2, 'Depto 1506' ) !== false && strpos( $s2, 'Torre' ) === false );

    // Ingles y portugues
    $prod_en = '<h3>Welcome</h3><p>x</p><h3>Clothes Dryer</h3><p>Bosch tank</p><h3>Heating</h3><p>boiler</p><h3>Nearby Supermarkets and Services</h3><p>Jumbo</p>';
    list( $e, $me ) = hvkx_aplicar( $prod_en, base64_decode( $GLOBALS['HVKX']['calefaccion']['en'] ), $GLOBALS['HVKX_T_CAL'] );
    $chk( 'ingles: calefaccion reemplazada', 'reemplazado' === $me && stripos( $e, 'radiant floor' ) !== false );
    list( $e, $me ) = hvkx_eliminar( $e, $GLOBALS['HVKX_T_SEC'] );
    $chk( 'ingles: secadora eliminada', 'eliminado' === $me && stripos( $e, 'Clothes Dryer' ) === false );
    list( $e, $me ) = hvkx_aplicar( $e, str_replace( '__DIRECCION__', 'x', base64_decode( $GLOBALS['HVKX']['delivery']['en'] ) ), $GLOBALS['HVKX_T_DEL'], $GLOBALS['HVKX_A_DEL'] );
    $chk( 'ingles: delivery tras supermercados', 'insertado' === $me && strpos( $e, 'Jumbo' ) < strpos( $e, 'Uber Eats' ) );

    $prod_pt = '<h3>Bem-vindo</h3><p>x</p><h3>Secadora de Roupas</h3><p>Bosch tanque</p><h3>Aquecimento</h3><p>caldeira</p><h3>Supermercados e Servi&ccedil;os Pr&oacute;ximos</h3><p>Jumbo</p>';
    list( $t, $mt ) = hvkx_aplicar( $prod_pt, base64_decode( $GLOBALS['HVKX']['calefaccion']['ptbr'] ), $GLOBALS['HVKX_T_CAL'] );
    $chk( 'portugues: calefaccion reemplazada', 'reemplazado' === $mt && stripos( $t, 'piso radiante' ) !== false );
    list( $t, $mt ) = hvkx_eliminar( $t, $GLOBALS['HVKX_T_SEC'] );
    $chk( 'portugues: secadora eliminada', 'eliminado' === $mt && stripos( $t, 'Secadora de Roupas' ) === false );
    list( $t, $mt ) = hvkx_aplicar( $t, str_replace( '__DIRECCION__', 'x', base64_decode( $GLOBALS['HVKX']['delivery']['ptbr'] ) ), $GLOBALS['HVKX_T_DEL'], $GLOBALS['HVKX_A_DEL'] );
    $chk( 'portugues: delivery tras supermercados', 'insertado' === $mt && strpos( $t, 'Jumbo' ) < strpos( $t, 'Uber Eats' ) );

    // Casos borde
    list( $z, $mz ) = hvkx_aplicar( '<h3>Solo esto</h3><p>x</p>', $cal, $GLOBALS['HVKX_T_CAL'] );
    $chk( 'sin bloque objetivo y sin ancla: no toca nada', null === $z && 'no-se-encontro-el-bloque' === $mz );
    list( $z, $mz ) = hvkx_eliminar( '<h3>Solo esto</h3><p>x</p>', $GLOBALS['HVKX_T_SEC'] );
    $chk( 'eliminar lo inexistente: no toca nada', null === $z && 'no-estaba' === $mz );
    list( $z, $mz ) = hvkx_aplicar( '', $cal, $GLOBALS['HVKX_T_CAL'] );
    $chk( 'contenido vacio: no revienta', null === $z && 'contenido-vacio' === $mz );

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
    $b = get_option( 'hvkp_contenido2_backup' );
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
    foreach ( array( 'es', 'en', 'ptbr' ) as $lang ) {
        $campo  = 'general_' . $lang;
        $antes  = (string) $wpdb->get_var( $wpdb->prepare( "SELECT `$campo` FROM $tabla WHERE id = %d", $u->id ) );
        if ( '' === trim( $antes ) ) {
            $warn[] = "$u->name [$lang]: contenido vacio, se omite";
            continue;
        }
        $cambios = array();
        $trabajo = hvkx_limpiar( $antes );
        if ( $trabajo !== $antes ) {
            $cambios[] = 'delivery mal ubicado eliminado';
        }

        list( $r, $m ) = hvkx_aplicar( $trabajo, base64_decode( $HVKX['calefaccion'][ $lang ] ), $HVKX_T_CAL );
        if ( null !== $r ) {
            $trabajo   = $r;
            $cambios[] = 'calefaccion por losa radiante (20-21 grados)';
        } else {
            $warn[] = "$u->name [$lang]: no se encontro el bloque de calefaccion";
        }

        if ( 'magnolio-306' === $u->slug ) {
            list( $r, $m ) = hvkx_eliminar( $trabajo, $HVKX_T_SEC );
            if ( null !== $r ) {
                $trabajo   = $r;
                $cambios[] = 'bloque de secadora eliminado';
            }
        }

        if ( isset( $HVKX_DIRECCIONES[ $u->slug ] ) ) {
            $bloque = str_replace( '__DIRECCION__', $HVKX_DIRECCIONES[ $u->slug ], base64_decode( $HVKX['delivery'][ $lang ] ) );
            list( $r, $m ) = hvkx_aplicar( $trabajo, $bloque, $HVKX_T_DEL, $HVKX_A_DEL );
            if ( null !== $r ) {
                $trabajo   = $r;
                $cambios[] = 'comida a domicilio' . ( 'insertado-al-final' === $m ? ' (al final)' : '' );
            }
        } else {
            $warn[] = "$u->name: sin direccion definida para delivery";
        }

        if ( $trabajo !== $antes ) {
            $backup[] = array( 'id' => (int) $u->id, 'campo' => $campo, 'valor' => $antes );
            $wpdb->update( $tabla, array( $campo => $trabajo ), array( 'id' => (int) $u->id ) );
            $ok[] = "$u->name [$lang]: " . implode( ', ', $cambios );
        } else {
            $ok[] = "$u->name [$lang]: ya estaba al dia";
        }
    }
}
if ( $backup ) {
    update_option( 'hvkp_contenido2_backup', $backup, false );
}
$warn[] = 'Magnolio 1506: la direccion de delivery quedo sin numero de torre (no estaba confirmada).';

if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
$ok[] = 'Caches purgadas';

echo "== HOMVUK Portal - Contenidos de Informacion General ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-contenido2.php\n";
echo "Para revertir: php hvkp-contenido2.php restore\n";
