<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Aviso destacado de basura (hvkp-basura-vista)
 *
 *  1. Aviso destacado de las reglas de basura en la VISTA INICIAL del portal
 *     (sobre las tarjetas), en es/en/pt, con enlace directo a las reglas.
 *  2. En Informacion General, el bloque "Basura y Reciclaje" queda resaltado
 *     y abierto por defecto.
 *  3. Cache-buster para que los huespedes que ya abrieron el portal reciban
 *     los cambios (los assets se sirven con cache de 1 ano "immutable").
 *
 * Todo va en archivos .js/.css (no PHP). Lo unico que se toca en PHP es el
 * numero de version del enlace a esos assets en las plantillas, con respaldo.
 *
 * Uso:  php hvkp-basura-vista.php            (instalar)
 *       php hvkp-basura-vista.php restore    (revertir)
 */

$HVKB_JS_B64  = 'Ci8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogICBIVktQLUJBU1VSQSB2MSDigJQgYXZpc28gZGVzdGFjYWRvIGRlIHJlZ2xhcyBkZSBiYXN1cmEKICAgKHZpc3RhIGluaWNpYWwgKyByZXNhbHRhZG8gZGVsIGFjb3JkZW9uIGVuIEluZm9ybWFjaW9uIEdlbmVyYWwpCiAgID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PSAqLwooZnVuY3Rpb24gKCkgewogICAgdmFyIHJhdyA9IChkb2N1bWVudC5kb2N1bWVudEVsZW1lbnQuZ2V0QXR0cmlidXRlKCdsYW5nJykgfHwgJ2VzJykudG9Mb3dlckNhc2UoKTsKICAgIHZhciBMICAgPSByYXcuaW5kZXhPZignZW4nKSA9PT0gMCA/ICdlbicgOiAocmF3LmluZGV4T2YoJ3B0JykgPT09IDAgPyAncHQnIDogJ2VzJyk7CiAgICB2YXIgVCA9IHsKICAgICAgICBlczogewogICAgICAgICAgICB0aXRsZTogJ1JlZ2xhcyBkZSBiYXN1cmEnLAogICAgICAgICAgICBiYWRnZTogJ0ltcG9ydGFudGUnLAogICAgICAgICAgICBpMTogJ1VzYSA8Yj5ib2xzYXMgcGVxdWXDsWFzPC9iPjogZGViZW4gY2FiZXIgcG9yIGVsIHNoYWZ0JywKICAgICAgICAgICAgaTI6ICc8Yj5OdW5jYTwvYj4gZGVqZXMgYm9sc2FzIGVuIGxhIHNhbGEgZGUgYmFzdXJhJywKICAgICAgICAgICAgaTM6ICdIb3JhcmlvIHBhcmEgYm90YXI6IDxiPjg6MDAgYSAyMDowMCBocnM8L2I+JywKICAgICAgICAgICAgbGluazogJ1ZlciByZWdsYXMgY29tcGxldGFzJwogICAgICAgIH0sCiAgICAgICAgZW46IHsKICAgICAgICAgICAgdGl0bGU6ICdUcmFzaCBydWxlcycsCiAgICAgICAgICAgIGJhZGdlOiAnSW1wb3J0YW50JywKICAgICAgICAgICAgaTE6ICdVc2UgPGI+c21hbGwgYmFnczwvYj46IHRoZXkgbXVzdCBmaXQgZG93biB0aGUgY2h1dGUnLAogICAgICAgICAgICBpMjogJzxiPk5ldmVyPC9iPiBsZWF2ZSBiYWdzIGluIHRoZSB0cmFzaCByb29tJywKICAgICAgICAgICAgaTM6ICdEaXNwb3NhbCBob3VyczogPGI+ODowMCB0byAyMDowMDwvYj4nLAogICAgICAgICAgICBsaW5rOiAnU2VlIGZ1bGwgcnVsZXMnCiAgICAgICAgfSwKICAgICAgICBwdDogewogICAgICAgICAgICB0aXRsZTogJ1JlZ3JhcyBkbyBsaXhvJywKICAgICAgICAgICAgYmFkZ2U6ICdJbXBvcnRhbnRlJywKICAgICAgICAgICAgaTE6ICdVc2UgPGI+c2Fjb3MgcGVxdWVub3M8L2I+OiBkZXZlbSBjYWJlciBwZWxvIGR1dG8nLAogICAgICAgICAgICBpMjogJzxiPk51bmNhPC9iPiBkZWl4ZSBzYWNvcyBuYSBzYWxhIGRlIGxpeG8nLAogICAgICAgICAgICBpMzogJ0hvcsOhcmlvIHBhcmEgZGVzY2FydGU6IDxiPjg6MDAgw6BzIDIwOjAwPC9iPicsCiAgICAgICAgICAgIGxpbms6ICdWZXIgcmVncmFzIGNvbXBsZXRhcycKICAgICAgICB9CiAgICB9W0xdOwoKICAgIC8vIDEpIFZpc3RhIGluaWNpYWw6IGF2aXNvIGRlc3RhY2FkbyBzb2JyZSBsYSBncmlsbGEgZGUgdGFyamV0YXMKICAgIHZhciBncmlkID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvcignLmh2a3AtY2FyZHMtZ3JpZCcpOwogICAgaWYgKGdyaWQgJiYgIWRvY3VtZW50LmdldEVsZW1lbnRCeUlkKCdodmtwLXRyYXNoLWFsZXJ0JykpIHsKICAgICAgICB2YXIgZ2VuZXJhbCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5odmtwLWNhcmRzLWdyaWQgYVtocmVmKj0iL3NlY3Rpb24vZ2VuZXJhbCJdJyk7CiAgICAgICAgdmFyIGhyZWYgPSBnZW5lcmFsID8gZ2VuZXJhbC5nZXRBdHRyaWJ1dGUoJ2hyZWYnKSArICcjYmFzdXJhJyA6ICcjJzsKICAgICAgICB2YXIgYm94ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnZGl2Jyk7CiAgICAgICAgYm94LmlkID0gJ2h2a3AtdHJhc2gtYWxlcnQnOwogICAgICAgIGJveC5jbGFzc05hbWUgPSAnaHZrcC10cmFzaC1hbGVydCc7CiAgICAgICAgYm94LmlubmVySFRNTCA9CiAgICAgICAgICAgICc8ZGl2IGNsYXNzPSJodmtwLXRyYXNoLWhlYWQiPicgKwogICAgICAgICAgICAgICAgJzxzcGFuIGNsYXNzPSJodmtwLXRyYXNoLWljb24iPuKZu++4jzwvc3Bhbj4nICsKICAgICAgICAgICAgICAgICc8aDM+JyArIFQudGl0bGUgKyAnPC9oMz4nICsKICAgICAgICAgICAgICAgICc8c3BhbiBjbGFzcz0iaHZrcC10cmFzaC1iYWRnZSI+JyArIFQuYmFkZ2UgKyAnPC9zcGFuPicgKwogICAgICAgICAgICAnPC9kaXY+JyArCiAgICAgICAgICAgICc8dWwgY2xhc3M9Imh2a3AtdHJhc2gtbGlzdCI+JyArCiAgICAgICAgICAgICAgICAnPGxpPicgKyBULmkxICsgJzwvbGk+JyArCiAgICAgICAgICAgICAgICAnPGxpPicgKyBULmkyICsgJzwvbGk+JyArCiAgICAgICAgICAgICAgICAnPGxpPicgKyBULmkzICsgJzwvbGk+JyArCiAgICAgICAgICAgICc8L3VsPicgKwogICAgICAgICAgICAnPGEgY2xhc3M9Imh2a3AtdHJhc2gtbGluayIgaHJlZj0iJyArIGhyZWYgKyAnIj4nICsgVC5saW5rICsgJyAmcmFycjs8L2E+JzsKICAgICAgICBncmlkLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGJveCwgZ3JpZCk7CiAgICB9CgogICAgLy8gMikgSW5mb3JtYWNpb24gR2VuZXJhbDogcmVzYWx0YXIgeSBhYnJpciBlbCBhY29yZGVvbiBkZSBiYXN1cmEKICAgIHZhciBhY2NzID0gZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnZGV0YWlscy5odmtwLWFjYycpOwogICAgZm9yICh2YXIgaSA9IDA7IGkgPCBhY2NzLmxlbmd0aDsgaSsrKSB7CiAgICAgICAgdmFyIHN1bSA9IGFjY3NbaV0ucXVlcnlTZWxlY3Rvcignc3VtbWFyeScpOwogICAgICAgIGlmICghc3VtKSB7IGNvbnRpbnVlOyB9CiAgICAgICAgaWYgKCEvYmFzdXJhfHJlY2ljbGFqZXx0cmFzaHxnYXJiYWdlfGxpeG98cmVjaWNsL2kudGVzdChzdW0udGV4dENvbnRlbnQgfHwgJycpKSB7IGNvbnRpbnVlOyB9CiAgICAgICAgYWNjc1tpXS5pZCA9ICdiYXN1cmEnOwogICAgICAgIGFjY3NbaV0uY2xhc3NMaXN0LmFkZCgnaHZrcC1hY2MtYWxlcnQnKTsKICAgICAgICBpZiAoIXN1bS5xdWVyeVNlbGVjdG9yKCcuaHZrcC10cmFzaC1iYWRnZScpKSB7CiAgICAgICAgICAgIHZhciBiID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnc3BhbicpOwogICAgICAgICAgICBiLmNsYXNzTmFtZSA9ICdodmtwLXRyYXNoLWJhZGdlJzsKICAgICAgICAgICAgYi50ZXh0Q29udGVudCA9IFQuYmFkZ2U7CiAgICAgICAgICAgIHN1bS5hcHBlbmRDaGlsZChiKTsKICAgICAgICB9CiAgICAgICAgYWNjc1tpXS5vcGVuID0gdHJ1ZTsKICAgICAgICBpZiAod2luZG93LmxvY2F0aW9uLmhhc2ggPT09ICcjYmFzdXJhJykgewogICAgICAgICAgICAoZnVuY3Rpb24gKGVsKSB7CiAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uICgpIHsgZWwuc2Nyb2xsSW50b1ZpZXcoeyBiZWhhdmlvcjogJ3Ntb290aCcsIGJsb2NrOiAnc3RhcnQnIH0pOyB9LCAyNTApOwogICAgICAgICAgICB9KShhY2NzW2ldKTsKICAgICAgICB9CiAgICAgICAgYnJlYWs7CiAgICB9Cn0pKCk7Cg==';
$HVKB_CSS_B64 = 'Ci8qID09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PQogICBIVktQLUJBU1VSQSB2MSDigJQgYXZpc28gZGVzdGFjYWRvIGRlIHJlZ2xhcyBkZSBiYXN1cmEKICAgPT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09PT09ICovCi5odmtwLXRyYXNoLWFsZXJ0IHsKICAgIGJhY2tncm91bmQ6IGxpbmVhci1ncmFkaWVudCgxMzVkZWcsICNmZmY4ZWQgMCUsICNmZmYxZjcgMTAwJSk7CiAgICBib3JkZXI6IDEuNXB4IHNvbGlkICNmY2Q5YTg7CiAgICBib3JkZXItbGVmdDogNXB4IHNvbGlkICNmNTllMGI7CiAgICBib3JkZXItcmFkaXVzOiAxNnB4OwogICAgcGFkZGluZzogMThweCAyMHB4OwogICAgbWFyZ2luOiAwIDAgMjJweDsKICAgIGJveC1zaGFkb3c6IDAgOHB4IDI0cHggcmdiYSgyNDUsIDE1OCwgMTEsIC4xMik7Cn0KLmh2a3AtdHJhc2gtaGVhZCB7CiAgICBkaXNwbGF5OiBmbGV4OwogICAgYWxpZ24taXRlbXM6IGNlbnRlcjsKICAgIGdhcDogMTBweDsKICAgIGZsZXgtd3JhcDogd3JhcDsKICAgIG1hcmdpbi1ib3R0b206IDEycHg7Cn0KLmh2a3AtdHJhc2gtaWNvbiB7IGZvbnQtc2l6ZTogMS4zcmVtOyBsaW5lLWhlaWdodDogMTsgfQouaHZrcC10cmFzaC1oZWFkIGgzIHsKICAgIG1hcmdpbjogMDsKICAgIGZvbnQtc2l6ZTogMS4wNXJlbTsKICAgIGZvbnQtd2VpZ2h0OiA4MDA7CiAgICBjb2xvcjogIzdjMmQxMjsKICAgIGxldHRlci1zcGFjaW5nOiAtLjAxZW07Cn0KLmh2a3AtdHJhc2gtYmFkZ2UgewogICAgYmFja2dyb3VuZDogI2Y1OWUwYjsKICAgIGNvbG9yOiAjZmZmOwogICAgZm9udC1zaXplOiAuNjhyZW07CiAgICBmb250LXdlaWdodDogODAwOwogICAgdGV4dC10cmFuc2Zvcm06IHVwcGVyY2FzZTsKICAgIGxldHRlci1zcGFjaW5nOiAuMDRlbTsKICAgIGJvcmRlci1yYWRpdXM6IDk5OXB4OwogICAgcGFkZGluZzogNHB4IDEwcHg7CiAgICB3aGl0ZS1zcGFjZTogbm93cmFwOwp9Ci5odmtwLXRyYXNoLWxpc3QgewogICAgbWFyZ2luOiAwIDAgMTRweDsKICAgIHBhZGRpbmc6IDA7CiAgICBsaXN0LXN0eWxlOiBub25lOwp9Ci5odmtwLXRyYXNoLWxpc3QgbGkgewogICAgcG9zaXRpb246IHJlbGF0aXZlOwogICAgcGFkZGluZzogMCAwIDAgMjJweDsKICAgIG1hcmdpbjogMCAwIDhweDsKICAgIGNvbG9yOiAjNDMzMzFmOwogICAgZm9udC1zaXplOiAuOTJyZW07CiAgICBsaW5lLWhlaWdodDogMS41Owp9Ci5odmtwLXRyYXNoLWxpc3QgbGk6bGFzdC1jaGlsZCB7IG1hcmdpbi1ib3R0b206IDA7IH0KLmh2a3AtdHJhc2gtbGlzdCBsaTpiZWZvcmUgewogICAgY29udGVudDogJyc7CiAgICBwb3NpdGlvbjogYWJzb2x1dGU7CiAgICBsZWZ0OiA2cHg7CiAgICB0b3A6IC41NWVtOwogICAgd2lkdGg6IDZweDsKICAgIGhlaWdodDogNnB4OwogICAgYm9yZGVyLXJhZGl1czogNTAlOwogICAgYmFja2dyb3VuZDogI2Y1OWUwYjsKfQouaHZrcC10cmFzaC1saXN0IGIgeyBjb2xvcjogIzdjMmQxMjsgZm9udC13ZWlnaHQ6IDgwMDsgfQouaHZrcC10cmFzaC1saW5rIHsKICAgIGRpc3BsYXk6IGlubGluZS1ibG9jazsKICAgIGJhY2tncm91bmQ6ICNmNTllMGI7CiAgICBjb2xvcjogI2ZmZiAhaW1wb3J0YW50OwogICAgZm9udC13ZWlnaHQ6IDcwMDsKICAgIGZvbnQtc2l6ZTogLjg4cmVtOwogICAgdGV4dC1kZWNvcmF0aW9uOiBub25lOwogICAgYm9yZGVyLXJhZGl1czogOTk5cHg7CiAgICBwYWRkaW5nOiA5cHggMThweDsKICAgIHRyYW5zaXRpb246IHRyYW5zZm9ybSAuMTVzIGVhc2UsIGJveC1zaGFkb3cgLjE1cyBlYXNlOwp9Ci5odmtwLXRyYXNoLWxpbms6aG92ZXIgewogICAgdHJhbnNmb3JtOiB0cmFuc2xhdGVZKC0xcHgpOwogICAgYm94LXNoYWRvdzogMCA4cHggMThweCByZ2JhKDI0NSwgMTU4LCAxMSwgLjM1KTsKfQpkZXRhaWxzLmh2a3AtYWNjLWFsZXJ0IHsKICAgIGJvcmRlci1jb2xvcjogI2ZjZDlhOCAhaW1wb3J0YW50OwogICAgYm9yZGVyLWxlZnQ6IDRweCBzb2xpZCAjZjU5ZTBiICFpbXBvcnRhbnQ7CiAgICBiYWNrZ3JvdW5kOiBsaW5lYXItZ3JhZGllbnQoMTM1ZGVnLCAjZmZmOGVkIDAlLCAjZmZmZGY5IDEwMCUpICFpbXBvcnRhbnQ7Cn0KZGV0YWlscy5odmtwLWFjYy1hbGVydCA+IHN1bW1hcnkgewogICAgZGlzcGxheTogZmxleDsKICAgIGFsaWduLWl0ZW1zOiBjZW50ZXI7CiAgICBnYXA6IDEwcHg7CiAgICBmbGV4LXdyYXA6IHdyYXA7CiAgICBjb2xvcjogIzdjMmQxMiAhaW1wb3J0YW50OwogICAgZm9udC13ZWlnaHQ6IDgwMCAhaW1wb3J0YW50Owp9CkBtZWRpYSAobWF4LXdpZHRoOiA0ODBweCkgewogICAgLmh2a3AtdHJhc2gtYWxlcnQgeyBwYWRkaW5nOiAxNnB4OyB9CiAgICAuaHZrcC10cmFzaC1oZWFkIGgzIHsgZm9udC1zaXplOiAxcmVtOyB9Cn0KQG1lZGlhIChwcmVmZXJzLXJlZHVjZWQtbW90aW9uOiByZWR1Y2UpIHsKICAgIC5odmtwLXRyYXNoLWxpbmsgeyB0cmFuc2l0aW9uOiBub25lOyB9CiAgICAuaHZrcC10cmFzaC1saW5rOmhvdmVyIHsgdHJhbnNmb3JtOiBub25lOyB9Cn0K';
$HVKB_MARCA   = 'HVKP-BASURA v1';
$HVKB_SUFIJO  = '-b1';

/**
 * Anexa un bloque a un archivo si aun no esta (idempotente por marcador).
 * Devuelve: 'ok' | 'ya-instalado' | 'sin-archivo' | 'sin-permiso'
 */
function hvkb_anexar( $ruta, $bloque, $marca ) {
    if ( ! file_exists( $ruta ) ) {
        return 'sin-archivo';
    }
    $actual = file_get_contents( $ruta );
    if ( false !== strpos( $actual, $marca ) ) {
        return 'ya-instalado';
    }
    if ( ! file_exists( $ruta . '.bak-basura' ) ) {
        copy( $ruta, $ruta . '.bak-basura' );
    }
    $escrito = file_put_contents( $ruta, $actual . $bloque );
    if ( false === $escrito ) {
        return 'sin-permiso';
    }
    return 'ok';
}

/**
 * Agrega un sufijo al parametro de version de los assets, para romper la
 * cache de los navegadores. Idempotente.
 */
function hvkb_cache_buster( $codigo, $sufijo ) {
    $buscar = '?v=<?php echo HVKP_VERSION; ?>';
    $nuevo  = $buscar . $sufijo;
    if ( false !== strpos( $codigo, $nuevo ) ) {
        return array( null, 'ya-instalado' );
    }
    if ( false === strpos( $codigo, $buscar ) ) {
        return array( null, 'sin-coincidencias' );
    }
    $n = 0;
    $salida = str_replace( $buscar, $nuevo, $codigo, $n );
    return array( $salida, 'ok:' . $n );
}

// ---- Pruebas locales (no requieren WordPress) ----
if ( getenv( 'HVKB_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $js  = base64_decode( $GLOBALS['HVKB_JS_B64'] );
    $css = base64_decode( $GLOBALS['HVKB_CSS_B64'] );
    $chk( 'bloque JS trae el marcador y los 3 idiomas', strpos( $js, $GLOBALS['HVKB_MARCA'] ) !== false && strpos( $js, 'Trash rules' ) !== false && strpos( $js, 'Regras do lixo' ) !== false );
    $chk( 'bloque JS busca la grilla y los acordeones', strpos( $js, 'hvkp-cards-grid' ) !== false && strpos( $js, 'details.hvkp-acc' ) !== false );
    $chk( 'bloque CSS trae el marcador y las clases', strpos( $css, $GLOBALS['HVKB_MARCA'] ) !== false && strpos( $css, '.hvkp-trash-alert' ) !== false && strpos( $css, '.hvkp-acc-alert' ) !== false );

    // anexado idempotente sobre archivos temporales
    $tmp = sys_get_temp_dir() . '/hvkb-' . getmypid() . '.js';
    file_put_contents( $tmp, "var original = 1;\n" );
    $chk( 'primer anexado: ok', 'ok' === hvkb_anexar( $tmp, $js, $GLOBALS['HVKB_MARCA'] ) );
    $chk( 'respaldo creado', file_exists( $tmp . '.bak-basura' ) );
    $chk( 'contenido original intacto', strpos( file_get_contents( $tmp ), 'var original = 1;' ) === 0 );
    $chk( 'segundo anexado: no duplica', 'ya-instalado' === hvkb_anexar( $tmp, $js, $GLOBALS['HVKB_MARCA'] ) );
    $chk( 'archivo inexistente: reportado', 'sin-archivo' === hvkb_anexar( $tmp . '.no-existe', $js, $GLOBALS['HVKB_MARCA'] ) );
    @unlink( $tmp ); @unlink( $tmp . '.bak-basura' );

    // cache-buster
    $tpl = '<link href="<?php echo esc_url( X ); ?>?v=<?php echo HVKP_VERSION; ?>">' . "\n"
         . '<script src="<?php echo esc_url( Y ); ?>?v=<?php echo HVKP_VERSION; ?>"></script>';
    list( $out, $why ) = hvkb_cache_buster( $tpl, $GLOBALS['HVKB_SUFIJO'] );
    $chk( 'cache-buster: reemplaza las 2 referencias', 'ok:2' === $why );
    $chk( 'cache-buster: version con sufijo', substr_count( $out, '?v=<?php echo HVKP_VERSION; ?>-b1' ) === 2 );
    $chk( 'cache-buster: no rompe el PHP alrededor', strpos( $out, 'esc_url( X )' ) !== false && strpos( $out, '</script>' ) !== false );
    list( $o2, $w2 ) = hvkb_cache_buster( $out, $GLOBALS['HVKB_SUFIJO'] );
    $chk( 'cache-buster: idempotente', null === $o2 && 'ya-instalado' === $w2 );
    list( $o3, $w3 ) = hvkb_cache_buster( '<p>sin assets</p>', $GLOBALS['HVKB_SUFIJO'] );
    $chk( 'cache-buster: archivo sin assets no se toca', null === $o3 && 'sin-coincidencias' === $w3 );

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';

$dir  = WP_CONTENT_DIR . '/plugins/homvuk-portal';
$ok   = array();
$warn = array();
$err  = array();

$purge = function () {
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
};

$mode = isset( $argv[1] ) ? $argv[1] : 'install';

if ( 'restore' === $mode ) {
    foreach ( array( $dir . '/assets/js/portal.js', $dir . '/assets/css/portal.css' ) as $f ) {
        if ( file_exists( $f . '.bak-basura' ) ) {
            copy( $f . '.bak-basura', $f );
            echo "[OK]    Restaurado: " . basename( $f ) . "\n";
        }
    }
    foreach ( (array) glob( $dir . '/templates/*.php.bak-basura' ) as $b ) {
        $destino = substr( $b, 0, -12 );
        copy( $b, $destino );
    }
    echo "[OK]    Plantillas restauradas\n";
    $purge();
    echo "Restauracion completa.\n";
    exit( 0 );
}

// 1) Aviso y estilos (archivos .js / .css)
$r = hvkb_anexar( $dir . '/assets/js/portal.js', base64_decode( $HVKB_JS_B64 ), $HVKB_MARCA );
if ( 'ok' === $r )                { $ok[] = 'Aviso de basura agregado a portal.js (respaldo .bak-basura)'; }
elseif ( 'ya-instalado' === $r )  { $ok[] = 'portal.js ya tenia el aviso'; }
else                              { $err[] = 'No se pudo escribir portal.js (' . $r . ')'; }

$r = hvkb_anexar( $dir . '/assets/css/portal.css', base64_decode( $HVKB_CSS_B64 ), $HVKB_MARCA );
if ( 'ok' === $r )                { $ok[] = 'Estilos del aviso agregados a portal.css (respaldo .bak-basura)'; }
elseif ( 'ya-instalado' === $r )  { $ok[] = 'portal.css ya tenia los estilos'; }
else                              { $err[] = 'No se pudo escribir portal.css (' . $r . ')'; }

if ( $err ) {
    echo "== HOMVUK Portal - Aviso de basura ==\n";
    foreach ( $err as $m ) { echo "[ERROR] $m\n"; }
    exit( 1 );
}

// 2) Cache-buster en las plantillas (cambio minimo, con respaldo)
$tocados = 0;
foreach ( (array) glob( $dir . '/templates/*.php' ) as $tpl ) {
    $codigo = file_get_contents( $tpl );
    list( $nuevo, $motivo ) = hvkb_cache_buster( $codigo, $HVKB_SUFIJO );
    if ( null === $nuevo ) {
        continue;
    }
    if ( ! file_exists( $tpl . '.bak-basura' ) ) {
        copy( $tpl, $tpl . '.bak-basura' );
    }
    file_put_contents( $tpl, $nuevo );
    $tocados++;
}
if ( $tocados ) {
    $ok[] = "Cache-buster aplicado en $tocados plantillas (los huespedes que ya entraron reciben los cambios)";
} else {
    $ok[] = 'Cache-buster ya estaba aplicado';
}

// 3) Sanidad: las plantillas siguen siendo PHP valido
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
    foreach ( (array) glob( $dir . '/templates/*.php.bak-basura' ) as $b ) {
        copy( $b, substr( $b, 0, -12 ) );
    }
    echo "[ERROR] Plantillas con error de sintaxis (" . implode( ', ', $rotos ) . "). Se restauraron todas desde el respaldo.\n";
    exit( 1 );
} else {
    $ok[] = 'Verificado: todas las plantillas siguen siendo PHP valido';
}

// 4) Purga y verificacion en vivo
$purge();
$ok[] = 'Caches purgadas';

$landing = wp_remote_get( home_url( '/Magnolio/?lang=es&cb=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $landing ) ) {
    $codigo = (int) wp_remote_retrieve_response_code( $landing );
    $cuerpo = (string) wp_remote_retrieve_body( $landing );
    if ( 200 === $codigo ) {
        $ok[] = 'Verificado: el portal responde correctamente (HTTP 200)';
    } else {
        $warn[] = "El portal respondio HTTP $codigo; revisar";
    }
    if ( false !== strpos( $cuerpo, HVKP_VERSION . $HVKB_SUFIJO ) ) {
        $ok[] = 'Verificado: los assets se piden con la version nueva (' . HVKP_VERSION . $HVKB_SUFIJO . ')';
    } else {
        $warn[] = 'El HTML aun no muestra la version nueva de los assets (puede ser cache de pagina)';
    }
} else {
    $warn[] = 'No se pudo verificar el portal: ' . $landing->get_error_message();
}

$css_url = plugins_url( 'assets/css/portal.css', $dir . '/homvuk-portal.php' ) . '?v=' . HVKP_VERSION . $HVKB_SUFIJO;
$css_res = wp_remote_get( $css_url, array( 'timeout' => 30, 'sslverify' => false ) );
if ( ! is_wp_error( $css_res ) && false !== strpos( (string) wp_remote_retrieve_body( $css_res ), 'hvkp-trash-alert' ) ) {
    $ok[] = 'Verificado: los estilos del aviso ya se sirven publicamente';
} else {
    $warn[] = 'No se pudo confirmar el CSS publicado; revisar en unos minutos';
}

echo "== HOMVUK Portal - Aviso destacado de basura ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-basura-vista.php\n";
echo "Para revertir: php hvkp-basura-vista.php restore\n";
