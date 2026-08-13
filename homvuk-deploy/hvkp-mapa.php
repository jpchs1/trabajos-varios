<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Corregir el mapa de Ubicacion (hvkp-mapa)
 *
 * El mapa mostraba otro punto: las coordenadas guardadas (-33.3964, -70.5910)
 * quedan a unos 7 km de Camino el Parque 100, Vitacura (-33.3748, -70.5252).
 *
 *   1. Corrige las coordenadas de ambas unidades en la base de datos.
 *   2. El mapa pasa a centrarse por DIRECCION (con marcador, que antes no
 *      tenia), leyendo la direccion de la propia tarjeta de la unidad.
 *   3. Agrega un boton "Como llegar" que abre Google Maps.
 *   4. Sube el numero de version de los assets para saltar la cache del
 *      navegador (se sirven con cache de un ano).
 *
 * El codigo va en portal.js y portal.css (archivos no PHP).
 *
 * Uso:  php hvkp-mapa.php            (aplicar)
 *       php hvkp-mapa.php restore    (revertir)
 */

$HVKM_JS_B64  = 'Ci8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogICBIVktQLU1BUEEgdjEg4oCUIGVsIG1hcGEgc2UgY2VudHJhIGVuIGxhIGRpcmVjY2lvbiByZWFsIChjb24gcGluKQogICB5IHNlIGFncmVnYSB1biBib3RvbiAiQ29tbyBsbGVnYXIiLgogICA9PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT0gKi8KKGZ1bmN0aW9uICgpIHsKICAgIHZhciB3cmFwID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLmh2a3AtbWFwLWVtYmVkJyk7CiAgICBpZiAoIXdyYXAgfHwgd3JhcC5nZXRBdHRyaWJ1dGUoJ2RhdGEtaHZrcC1tYXBhJykpIHsgcmV0dXJuOyB9CiAgICB2YXIgaWZyYW1lID0gd3JhcC5xdWVyeVNlbGVjdG9yKCdpZnJhbWUnKTsKICAgIGlmICghaWZyYW1lKSB7IHJldHVybjsgfQoKICAgIC8vIEJ1c2NhciBsYSBkaXJlY2Npb24gZW4gbGEgdGFyamV0YSBkZSBpbmZvcm1hY2lvbiBjb3JyZXNwb25kaWVudGUKICAgIHZhciBkaXJlY2Npb24gPSAnJzsKICAgIHZhciB0YXJqZXRhcyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJy5odmtwLWluZm8tY2FyZCcpOwogICAgZm9yICh2YXIgaSA9IDA7IGkgPCB0YXJqZXRhcy5sZW5ndGg7IGkrKykgewogICAgICAgIHZhciB0aXR1bG8gPSB0YXJqZXRhc1tpXS5xdWVyeVNlbGVjdG9yKCdoNCcpOwogICAgICAgIHZhciB2YWxvciAgPSB0YXJqZXRhc1tpXS5xdWVyeVNlbGVjdG9yKCdwLCAuaHZrcC1pbmZvLXZhbHVlJyk7CiAgICAgICAgaWYgKCF0aXR1bG8gfHwgIXZhbG9yKSB7IGNvbnRpbnVlOyB9CiAgICAgICAgaWYgKCEvZGlyZWNjaXxhZGRyZXNzfGVuZGVyZS9pLnRlc3QodGl0dWxvLnRleHRDb250ZW50IHx8ICcnKSkgeyBjb250aW51ZTsgfQogICAgICAgIGRpcmVjY2lvbiA9ICh2YWxvci50ZXh0Q29udGVudCB8fCAnJykudHJpbSgpOwogICAgICAgIGJyZWFrOwogICAgfQogICAgaWYgKCFkaXJlY2Npb24pIHsgcmV0dXJuOyB9CgogICAgLy8gUXVlZGFyc2UgY29uIGNhbGxlIHkgY29tdW5hOiBsbyBxdWUgdmEgYW50ZXMgZGVsIGd1aW9uIHNlcGFyYWRvcgogICAgdmFyIGNhbGxlID0gZGlyZWNjaW9uLnNwbGl0KC9ccytbLeKAk+KAlF1ccysvKVswXS50cmltKCk7CiAgICBpZiAoIWNhbGxlKSB7IGNhbGxlID0gZGlyZWNjaW9uOyB9CiAgICB2YXIgY29uc3VsdGEgPSBjYWxsZSArICcsIFNhbnRpYWdvLCBDaGlsZSc7CgogICAgaWZyYW1lLnNldEF0dHJpYnV0ZSgnc3JjJywgJ2h0dHBzOi8vbWFwcy5nb29nbGUuY29tL21hcHM/cT0nICsgZW5jb2RlVVJJQ29tcG9uZW50KGNvbnN1bHRhKSArICcmej0xNyZvdXRwdXQ9ZW1iZWQnKTsKICAgIGlmcmFtZS5zZXRBdHRyaWJ1dGUoJ3RpdGxlJywgY2FsbGUpOwogICAgd3JhcC5zZXRBdHRyaWJ1dGUoJ2RhdGEtaHZrcC1tYXBhJywgJzEnKTsKCiAgICAvLyBCb3RvbiAiQ29tbyBsbGVnYXIiCiAgICBpZiAoIWRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdodmtwLW1hcC1saW5rJykpIHsKICAgICAgICB2YXIgcmF3ICA9IChkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuZ2V0QXR0cmlidXRlKCdsYW5nJykgfHwgJ2VzJykudG9Mb3dlckNhc2UoKTsKICAgICAgICB2YXIgTCAgICA9IHJhdy5pbmRleE9mKCdlbicpID09PSAwID8gJ2VuJyA6IChyYXcuaW5kZXhPZigncHQnKSA9PT0gMCA/ICdwdCcgOiAnZXMnKTsKICAgICAgICB2YXIgdHh0ICA9IHsgZXM6ICdDw7NtbyBsbGVnYXInLCBlbjogJ0dldCBkaXJlY3Rpb25zJywgcHQ6ICdDb21vIGNoZWdhcicgfVtMXTsKICAgICAgICB2YXIgYSAgICA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2EnKTsKICAgICAgICBhLmlkICAgICA9ICdodmtwLW1hcC1saW5rJzsKICAgICAgICBhLmNsYXNzTmFtZSA9ICdodmtwLW1hcC1saW5rJzsKICAgICAgICBhLmhyZWYgICA9ICdodHRwczovL3d3dy5nb29nbGUuY29tL21hcHMvc2VhcmNoLz9hcGk9MSZxdWVyeT0nICsgZW5jb2RlVVJJQ29tcG9uZW50KGNvbnN1bHRhKTsKICAgICAgICBhLnRhcmdldCA9ICdfYmxhbmsnOwogICAgICAgIGEucmVsICAgID0gJ25vb3BlbmVyJzsKICAgICAgICBhLmlubmVySFRNTCA9ICc8c3ZnIHdpZHRoPSIxNSIgaGVpZ2h0PSIxNSIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxwb2x5Z29uIHBvaW50cz0iMyAxMSAyMiAyIDEzIDIxIDExIDEzIDMgMTEiLz48L3N2Zz4gJyArIHR4dDsKICAgICAgICB3cmFwLnBhcmVudE5vZGUuYXBwZW5kQ2hpbGQoYSk7CiAgICB9Cn0pKCk7Cg==';
$HVKM_CSS_B64 = 'Ci8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogICBIVktQLU1BUEEgdjEg4oCUIGJvdG9uICJDb21vIGxsZWdhciIgYmFqbyBlbCBtYXBhCiAgID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwouaHZrcC1tYXAtbGluayB7CiAgICBkaXNwbGF5OiBpbmxpbmUtZmxleDsKICAgIGFsaWduLWl0ZW1zOiBjZW50ZXI7CiAgICBnYXA6IDdweDsKICAgIG1hcmdpbi10b3A6IDEwcHg7CiAgICBwYWRkaW5nOiA5cHggMThweDsKICAgIGJvcmRlcjogMS41cHggc29saWQgI2U4NDM5MzsKICAgIGJvcmRlci1yYWRpdXM6IDk5OXB4OwogICAgY29sb3I6ICNkNjMzODQgIWltcG9ydGFudDsKICAgIGZvbnQtc2l6ZTogLjg2cmVtOwogICAgZm9udC13ZWlnaHQ6IDcwMDsKICAgIHRleHQtZGVjb3JhdGlvbjogbm9uZTsKICAgIHRyYW5zaXRpb246IGJhY2tncm91bmQgLjE1cyBlYXNlLCBjb2xvciAuMTVzIGVhc2U7Cn0KLmh2a3AtbWFwLWxpbms6aG92ZXIgewogICAgYmFja2dyb3VuZDogI2U4NDM5MzsKICAgIGNvbG9yOiAjZmZmICFpbXBvcnRhbnQ7Cn0KQG1lZGlhIChwcmVmZXJzLXJlZHVjZWQtbW90aW9uOiByZWR1Y2UpIHsKICAgIC5odmtwLW1hcC1saW5rIHsgdHJhbnNpdGlvbjogbm9uZTsgfQp9Cg==';
$HVKM_MARCA   = 'HVKP-MAPA v1';
$HVKM_SUFIJO  = '-b2';
$HVKM_LAT     = '-33.374762';
$HVKM_LNG     = '-70.525165';

/** Anexa un bloque a un archivo si aun no esta. */
function hvkm_anexar( $ruta, $bloque, $marca ) {
    if ( ! file_exists( $ruta ) ) {
        return 'sin-archivo';
    }
    $actual = file_get_contents( $ruta );
    if ( false !== strpos( $actual, $marca ) ) {
        return 'ya-instalado';
    }
    if ( ! file_exists( $ruta . '.bak-mapa' ) ) {
        copy( $ruta, $ruta . '.bak-mapa' );
    }
    return ( false === file_put_contents( $ruta, $actual . $bloque ) ) ? 'sin-permiso' : 'ok';
}

/** Fija el sufijo de version en el enlace a los assets. Idempotente. */
function hvkm_version( $codigo, $sufijo ) {
    $base = '?v=<?php echo HVKP_VERSION; ?>';
    if ( false !== strpos( $codigo, $base . $sufijo ) ) {
        return array( null, 'ya-instalado' );
    }
    if ( false === strpos( $codigo, $base ) ) {
        return array( null, 'sin-coincidencias' );
    }
    // Quitar cualquier sufijo previo del tipo -bN y poner el nuevo
    $n = 0;
    $salida = preg_replace(
        '/\?v=<\?php echo HVKP_VERSION; \?>(?:-b\d+)?/',
        $base . $sufijo,
        $codigo,
        -1,
        $n
    );
    return array( $salida, 'ok:' . $n );
}

// ---- Pruebas locales ----
if ( getenv( 'HVKM_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $js  = base64_decode( $GLOBALS['HVKM_JS_B64'] );
    $css = base64_decode( $GLOBALS['HVKM_CSS_B64'] );
    $chk( 'JS: marcador, busqueda de direccion y embed por consulta', strpos( $js, $GLOBALS['HVKM_MARCA'] ) !== false && strpos( $js, 'hvkp-info-card' ) !== false && strpos( $js, 'output=embed' ) !== false );
    $chk( 'JS: los 3 idiomas del boton', strpos( $js, 'Cómo llegar' ) !== false && strpos( $js, 'Get directions' ) !== false && strpos( $js, 'Como chegar' ) !== false );
    $chk( 'CSS: marcador y clase del boton', strpos( $css, $GLOBALS['HVKM_MARCA'] ) !== false && strpos( $css, '.hvkp-map-link' ) !== false );

    $tmp = sys_get_temp_dir() . '/hvkm-' . getmypid() . '.js';
    file_put_contents( $tmp, "var previo = 1;\n" );
    $chk( 'primer anexado', 'ok' === hvkm_anexar( $tmp, $js, $GLOBALS['HVKM_MARCA'] ) );
    $chk( 'respaldo creado', file_exists( $tmp . '.bak-mapa' ) );
    $chk( 'conserva el contenido previo', strpos( file_get_contents( $tmp ), 'var previo = 1;' ) === 0 );
    $chk( 'no duplica en la segunda pasada', 'ya-instalado' === hvkm_anexar( $tmp, $js, $GLOBALS['HVKM_MARCA'] ) );
    @unlink( $tmp ); @unlink( $tmp . '.bak-mapa' );

    $tpl_b1 = '<link href="x<?php echo esc_url( A ); ?>?v=<?php echo HVKP_VERSION; ?>-b1">' . "\n" . '<script src="<?php echo esc_url( B ); ?>?v=<?php echo HVKP_VERSION; ?>-b1"></script>';
    list( $o, $w ) = hvkm_version( $tpl_b1, $GLOBALS['HVKM_SUFIJO'] );
    $chk( 'sube de -b1 a -b2 en las 2 referencias', 'ok:2' === $w && 2 === substr_count( $o, '?v=<?php echo HVKP_VERSION; ?>-b2' ) );
    $chk( 'no quedan referencias a -b1', strpos( $o, '-b1' ) === false );
    $chk( 'no rompe el PHP alrededor', strpos( $o, 'esc_url( A )' ) !== false && strpos( $o, '</script>' ) !== false );
    list( $o2, $w2 ) = hvkm_version( $o, $GLOBALS['HVKM_SUFIJO'] );
    $chk( 'idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvkm_version( '<link href="?v=<?php echo HVKP_VERSION; ?>">', $GLOBALS['HVKM_SUFIJO'] );
    $chk( 'tambien funciona sin sufijo previo', 'ok:1' === $w3 && strpos( $o3, '-b2' ) !== false );
    list( $o4, $w4 ) = hvkm_version( '<p>sin assets</p>', $GLOBALS['HVKM_SUFIJO'] );
    $chk( 'archivo sin assets: no se toca', null === $o4 && 'sin-coincidencias' === $w4 );

    $chk( 'coordenadas dentro de Vitacura', abs( floatval( $GLOBALS['HVKM_LAT'] ) + 33.3748 ) < 0.01 && abs( floatval( $GLOBALS['HVKM_LNG'] ) + 70.5252 ) < 0.01 );

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';
global $wpdb;

$dir   = WP_CONTENT_DIR . '/plugins/homvuk-portal';
$tabla = $wpdb->prefix . 'portal_units';
$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$ok    = array();
$warn  = array();

$purge = function () {
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
};

if ( 'restore' === $mode ) {
    foreach ( array( $dir . '/assets/js/portal.js', $dir . '/assets/css/portal.css' ) as $f ) {
        if ( file_exists( $f . '.bak-mapa' ) ) {
            copy( $f . '.bak-mapa', $f );
            echo "[OK]    Restaurado: " . basename( $f ) . "\n";
        }
    }
    $b = get_option( 'hvkp_mapa_backup' );
    if ( is_array( $b ) ) {
        foreach ( $b as $fila ) {
            $wpdb->update( $tabla, array( 'map_lat' => $fila['lat'], 'map_lng' => $fila['lng'] ), array( 'id' => (int) $fila['id'] ) );
        }
        echo "[OK]    Coordenadas anteriores restauradas\n";
    }
    $purge();
    echo "Restauracion completa.\n";
    exit( 0 );
}

// 1) Coordenadas correctas en la base de datos
$unidades = $wpdb->get_results( "SELECT id, name, map_lat, map_lng FROM $tabla WHERE active = 1" );
$backup   = array();
foreach ( $unidades as $u ) {
    $backup[] = array( 'id' => (int) $u->id, 'lat' => $u->map_lat, 'lng' => $u->map_lng );
    if ( (string) $u->map_lat === $HVKM_LAT && (string) $u->map_lng === $HVKM_LNG ) {
        $ok[] = $u->name . ': coordenadas ya estaban corregidas';
        continue;
    }
    $wpdb->update( $tabla, array( 'map_lat' => $HVKM_LAT, 'map_lng' => $HVKM_LNG ), array( 'id' => (int) $u->id ) );
    $ok[] = $u->name . ": coordenadas corregidas ($u->map_lat, $u->map_lng -> $HVKM_LAT, $HVKM_LNG)";
}
if ( $backup ) {
    update_option( 'hvkp_mapa_backup', $backup, false );
}

// 2) Mapa por direccion + boton "Como llegar"
$r = hvkm_anexar( $dir . '/assets/js/portal.js', base64_decode( $HVKM_JS_B64 ), $HVKM_MARCA );
if ( 'ok' === $r )               { $ok[] = 'Mapa por direccion y boton "Como llegar" agregados a portal.js'; }
elseif ( 'ya-instalado' === $r ) { $ok[] = 'portal.js ya tenia la correccion del mapa'; }
else                             { echo "[ERROR] No se pudo escribir portal.js ($r)\n"; exit( 1 ); }

$r = hvkm_anexar( $dir . '/assets/css/portal.css', base64_decode( $HVKM_CSS_B64 ), $HVKM_MARCA );
if ( 'ok' === $r )               { $ok[] = 'Estilos del boton agregados a portal.css'; }
elseif ( 'ya-instalado' === $r ) { $ok[] = 'portal.css ya tenia los estilos'; }
else                             { echo "[ERROR] No se pudo escribir portal.css ($r)\n"; exit( 1 ); }

// 3) Nueva version de los assets (para saltar la cache del navegador)
$tocados = 0;
foreach ( (array) glob( $dir . '/templates/*.php' ) as $tpl ) {
    $codigo = file_get_contents( $tpl );
    list( $nuevo, $motivo ) = hvkm_version( $codigo, $HVKM_SUFIJO );
    if ( null === $nuevo ) {
        continue;
    }
    if ( ! file_exists( $tpl . '.bak-mapa' ) ) {
        copy( $tpl, $tpl . '.bak-mapa' );
    }
    file_put_contents( $tpl, $nuevo );
    $tocados++;
}
$ok[] = $tocados ? "Version de assets actualizada en $tocados plantillas" : 'La version de assets ya estaba actualizada';

// 4) Sanidad de las plantillas
$rotos = array();
foreach ( (array) glob( $dir . '/templates/*.php' ) as $tpl ) {
    $salida = array();
    $codigo_salida = 0;
    @exec( 'php -l ' . escapeshellarg( $tpl ) . ' 2>&1', $salida, $codigo_salida );
    if ( 0 !== $codigo_salida ) {
        $rotos[] = basename( $tpl );
    }
}
if ( $rotos ) {
    foreach ( (array) glob( $dir . '/templates/*.php.bak-mapa' ) as $b ) {
        copy( $b, substr( $b, 0, -9 ) );
    }
    echo "[ERROR] Plantillas con error de sintaxis (" . implode( ', ', $rotos ) . "). Se restauraron desde el respaldo.\n";
    exit( 1 );
}
$ok[] = 'Verificado: todas las plantillas siguen siendo PHP valido';

// 5) Purga y verificacion en vivo
$purge();
$ok[] = 'Caches purgadas';

$landing = wp_remote_get( home_url( '/Magnolio/?lang=es&cb=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $landing ) ) {
    $codigo = (int) wp_remote_retrieve_response_code( $landing );
    $cuerpo = (string) wp_remote_retrieve_body( $landing );
    $ok[] = ( 200 === $codigo ) ? 'Verificado: el portal responde correctamente (HTTP 200)' : "El portal respondio HTTP $codigo";
    if ( false !== strpos( $cuerpo, HVKP_VERSION . $HVKM_SUFIJO ) ) {
        $ok[] = 'Verificado: los assets se piden con la version nueva (' . HVKP_VERSION . $HVKM_SUFIJO . ')';
    } else {
        $warn[] = 'El HTML aun no muestra la version nueva de los assets (puede ser cache de pagina)';
    }
} else {
    $warn[] = 'No se pudo verificar el portal: ' . $landing->get_error_message();
}

$js_url = plugins_url( 'assets/js/portal.js', $dir . '/homvuk-portal.php' ) . '?v=' . HVKP_VERSION . $HVKM_SUFIJO;
$js_res = wp_remote_get( $js_url, array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $js_res ) && false !== strpos( (string) wp_remote_retrieve_body( $js_res ), $HVKM_MARCA ) ) {
    $ok[] = 'Verificado: el JavaScript del mapa ya se sirve publicamente';
} else {
    $warn[] = 'No se pudo confirmar el JavaScript publicado; revisar en unos minutos';
}

echo "== HOMVUK Portal - Mapa de Ubicacion ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-mapa.php\n";
echo "Para revertir: php hvkp-mapa.php restore\n";
