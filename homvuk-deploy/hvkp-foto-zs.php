<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Foto del MG ZS: limpiar el fondo y publicar el auto (hvkp-foto-zs)
 *
 * Toma la imagen que subiste a la biblioteca de medios, le deja el fondo
 * blanco parejo (sin tocar el auto), la deja del tamano del catalogo, la pone
 * como imagen del producto y publica el MG ZS.
 *
 * El fondo se limpia desde los bordes hacia adentro: solo se blanquea lo que
 * esta conectado con el borde y es de un color parecido al del fondo, asi que
 * el auto nunca se toca.
 *
 * Uso:  php hvkp-foto-zs.php                 (usa la ultima imagen subida)
 *       php hvkp-foto-zs.php --forzar       (aunque no sea reciente)
 *       php hvkp-foto-zs.php 1234            (usa la imagen con ese ID)
 *       php hvkp-foto-zs.php --no-publicar   (deja el auto en borrador)
 */

$HVKF_SLUG = 'new-mg-zs-2026';
$HVKF_ANCHO = 900;   // tamano final
$HVKF_TOL   = 34;    // tolerancia de color del fondo

/**
 * Blanquea el fondo conectado a los bordes de la imagen.
 * Devuelve la cantidad de pixeles cambiados.
 */
function hvkf_limpiar_fondo( $im, $tol ) {
    $w = imagesx( $im );
    $h = imagesy( $im );
    $blanco = imagecolorallocate( $im, 255, 255, 255 );

    // Color de referencia: promedio de las cuatro esquinas
    $muestras = array( array( 1, 1 ), array( $w - 2, 1 ), array( 1, $h - 2 ), array( $w - 2, $h - 2 ) );
    $sr = $sg = $sb = 0;
    foreach ( $muestras as $m ) {
        $c = imagecolorat( $im, $m[0], $m[1] );
        $sr += ( $c >> 16 ) & 0xFF;
        $sg += ( $c >> 8 ) & 0xFF;
        $sb += $c & 0xFF;
    }
    $rr = $sr / 4; $rg = $sg / 4; $rb = $sb / 4;

    $visto  = array();
    $cola   = array();
    foreach ( range( 0, $w - 1 ) as $x ) {
        $cola[] = array( $x, 0 );
        $cola[] = array( $x, $h - 1 );
    }
    foreach ( range( 0, $h - 1 ) as $y ) {
        $cola[] = array( 0, $y );
        $cola[] = array( $w - 1, $y );
    }

    $cambiados = 0;
    while ( $cola ) {
        list( $x, $y ) = array_pop( $cola );
        if ( $x < 0 || $y < 0 || $x >= $w || $y >= $h ) {
            continue;
        }
        $k = $y * $w + $x;
        if ( isset( $visto[ $k ] ) ) {
            continue;
        }
        $visto[ $k ] = 1;
        $c = imagecolorat( $im, $x, $y );
        $r = ( $c >> 16 ) & 0xFF; $g = ( $c >> 8 ) & 0xFF; $b = $c & 0xFF;
        if ( abs( $r - $rr ) > $tol || abs( $g - $rg ) > $tol || abs( $b - $rb ) > $tol ) {
            continue; // llegamos al auto o a una sombra marcada: no seguimos
        }
        imagesetpixel( $im, $x, $y, $blanco );
        $cambiados++;
        $cola[] = array( $x + 1, $y );
        $cola[] = array( $x - 1, $y );
        $cola[] = array( $x, $y + 1 );
        $cola[] = array( $x, $y - 1 );
    }
    return $cambiados;
}

if ( getenv( 'HVKF_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };
    if ( ! function_exists( 'imagecreatetruecolor' ) ) {
        echo "[ERROR] Este PHP no tiene la libreria de imagenes (GD)\n";
        exit( 1 );
    }
    // Imagen de prueba: fondo gris claro, un "auto" oscuro al centro y una mancha clara DENTRO del auto
    $im = imagecreatetruecolor( 60, 40 );
    $fondo = imagecolorallocate( $im, 240, 240, 238 );
    imagefill( $im, 0, 0, $fondo );
    $auto = imagecolorallocate( $im, 120, 122, 130 );
    imagefilledrectangle( $im, 15, 10, 44, 29, $auto );
    $brillo = imagecolorallocate( $im, 243, 243, 241 ); // reflejo claro dentro del auto
    imagefilledrectangle( $im, 25, 18, 28, 21, $brillo );

    $cambiados = hvkf_limpiar_fondo( $im, 34 );
    $esq = imagecolorat( $im, 0, 0 );
    $centro = imagecolorat( $im, 30, 15 );
    $reflejo = imagecolorat( $im, 26, 19 );
    $chk( 'el fondo quedo blanco', 0xFFFFFF === ( $esq & 0xFFFFFF ) );
    $chk( 'el auto no se toco', 0xFFFFFF !== ( $centro & 0xFFFFFF ) );
    $chk( 'un reflejo claro DENTRO del auto se conserva', 0xFFFFFF !== ( $reflejo & 0xFFFFFF ) );
    $chk( 'cambio una cantidad razonable de pixeles', $cambiados > 100 && $cambiados < 60 * 40 );
    imagedestroy( $im );

    // Sin fondo uniforme (foto en la calle): no debe arrasar con la imagen
    $im2 = imagecreatetruecolor( 40, 30 );
    for ( $x = 0; $x < 40; $x++ ) {
        for ( $y = 0; $y < 30; $y++ ) {
            imagesetpixel( $im2, $x, $y, imagecolorallocate( $im2, 40 + $x * 4, 90 + $y * 3, 60 + $x ) );
        }
    }
    $c2 = hvkf_limpiar_fondo( $im2, 34 );
    $chk( 'foto sin fondo plano: cambia poco', $c2 < 40 * 30 * 0.5 );
    imagedestroy( $im2 );

    echo $fail ? "FALLARON $fail\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

require __DIR__ . '/wp-load.php';
global $wpdb;

$publicar = true;
$forzar   = false;
$attach_id = 0;
foreach ( array_slice( $argv, 1 ) as $a ) {
    if ( '--no-publicar' === $a ) { $publicar = false; }
    elseif ( '--forzar' === $a ) { $forzar = true; }
    elseif ( ctype_digit( $a ) ) { $attach_id = (int) $a; $forzar = true; }
}

if ( ! function_exists( 'imagecreatetruecolor' ) ) {
    echo "[ERROR] Este PHP no tiene la libreria de imagenes (GD); no se puede procesar la foto.\n";
    exit( 1 );
}

// 1) El auto (en la papelera WordPress le agrega __trashed al identificador)
$auto = $wpdb->get_row( $wpdb->prepare(
    "SELECT ID, post_status FROM {$wpdb->posts} WHERE post_name IN (%s, %s) AND post_type = 'product' LIMIT 1",
    $HVKF_SLUG, $HVKF_SLUG . '__trashed'
) );
if ( ! $auto ) {
    $guardado = (int) get_option( 'hvkp_mgzx_id' );
    if ( $guardado ) {
        $auto = $wpdb->get_row( $wpdb->prepare( "SELECT ID, post_status FROM {$wpdb->posts} WHERE ID = %d LIMIT 1", $guardado ) );
    }
}
if ( ! $auto ) {
    echo "[ERROR] No se encontro el MG ZS (ni publicado ni en la papelera).\n";
    exit( 1 );
}
echo "[OK]    Producto: MG ZS (id " . $auto->ID . ", estado actual: " . $auto->post_status . ")\n";

// 2) La imagen
if ( ! $attach_id ) {
    $attach_id = (int) $wpdb->get_var(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY post_date DESC LIMIT 1"
    );
}
if ( ! $attach_id ) {
    echo "[ERROR] No hay imagenes en la biblioteca de medios.\n";
    exit( 1 );
}
$ruta = get_attached_file( $attach_id );
if ( ! $ruta || ! file_exists( $ruta ) ) {
    echo "[ERROR] No se encontro el archivo de la imagen $attach_id\n";
    exit( 1 );
}
echo "[OK]    Imagen: " . basename( $ruta ) . " (id $attach_id, subida " . get_the_date( 'd/m/Y H:i', $attach_id ) . ")\n";

// Resguardo: si la imagen no es reciente, probablemente no es la que subiste
$edad = time() - get_post_time( 'U', true, $attach_id );
if ( ! $forzar && $edad > 2 * HOUR_IN_SECONDS ) {
    echo "[AVISO] Esa imagen se subio hace " . human_time_diff( time() - $edad ) . ", asi que quiza no es la foto del MG ZS.\n";
    echo "        Si es la correcta, vuelve a correrlo asi:  php hvkp-foto-zs.php --forzar\n";
    echo "        O indica el ID de la imagen:                php hvkp-foto-zs.php <ID>\n";
    echo "        No se modifico nada.\n";
    exit( 0 );
}

// 3) Procesar: fondo blanco parejo y tamano de catalogo
$tipo = wp_check_filetype( $ruta );
$ext  = strtolower( $tipo['ext'] );
switch ( $ext ) {
    case 'jpg': case 'jpeg': $im = @imagecreatefromjpeg( $ruta ); break;
    case 'png': $im = @imagecreatefrompng( $ruta ); break;
    case 'webp': $im = function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $ruta ) : false; break;
    default: $im = false;
}
if ( ! $im ) {
    echo "[ERROR] No se pudo abrir la imagen ($ext).\n";
    exit( 1 );
}

$w = imagesx( $im ); $h = imagesy( $im );
if ( $w > $HVKF_ANCHO ) {
    $nh  = (int) round( $h * ( $HVKF_ANCHO / $w ) );
    $tmp = imagecreatetruecolor( $HVKF_ANCHO, $nh );
    imagecopyresampled( $tmp, $im, 0, 0, 0, 0, $HVKF_ANCHO, $nh, $w, $h );
    imagedestroy( $im );
    $im = $tmp;
    $w  = $HVKF_ANCHO; $h = $nh;
    echo "[OK]    Redimensionada a {$w}x{$h} para el catalogo\n";
}

$cambiados = hvkf_limpiar_fondo( $im, $HVKF_TOL );
$porc = round( 100 * $cambiados / ( $w * $h ) );
if ( $porc < 3 ) {
    echo "[AVISO] El fondo no es parejo (solo $porc% limpiado): se deja la foto tal cual\n";
} elseif ( $porc > 85 ) {
    echo "[AVISO] Se detecto demasiado fondo ($porc%): se deja la foto original por seguridad\n";
    imagedestroy( $im );
    $im = null;
} else {
    echo "[OK]    Fondo emparejado a blanco ($porc% de la imagen)\n";
}

$destino = $ruta;
if ( $im ) {
    $updir   = wp_upload_dir();
    $destino = trailingslashit( $updir['path'] ) . 'mg-zs-2026-homvuk.jpg';
    imagejpeg( $im, $destino, 90 );
    imagedestroy( $im );

    $nuevo_id = wp_insert_attachment( array(
        'post_mime_type' => 'image/jpeg',
        'post_title'     => 'MG ZS 2026 plateado - HOMVUK',
        'post_content'   => '',
        'post_status'    => 'inherit',
    ), $destino, (int) $auto->ID );
    require_once ABSPATH . 'wp-admin/includes/image.php';
    wp_update_attachment_metadata( $nuevo_id, wp_generate_attachment_metadata( $nuevo_id, $destino ) );
    update_post_meta( $nuevo_id, '_wp_attachment_image_alt', 'Arriendo MG ZS 2026 plateado en Santiago - HOMVUK' );
    $attach_id = $nuevo_id;
    echo "[OK]    Imagen lista: " . wp_get_attachment_url( $nuevo_id ) . "\n";
}

// 4) Asignarla al auto
set_post_thumbnail( (int) $auto->ID, (int) $attach_id );
echo "[OK]    Asignada como imagen del MG ZS\n";

// 5) Sacarlo de la papelera y publicar
if ( 'trash' === $auto->post_status ) {
    wp_untrash_post( (int) $auto->ID );
    echo "[OK]    Producto recuperado de la papelera\n";
}
if ( $publicar ) {
    $wpdb->update( $wpdb->posts, array( 'post_status' => 'publish', 'post_name' => $HVKF_SLUG ), array( 'ID' => (int) $auto->ID ) );
    clean_post_cache( (int) $auto->ID );
    echo "[OK]    Publicado: " . get_permalink( (int) $auto->ID ) . "\n";
} else {
    echo "[OK]    Queda en borrador (sin publicar)\n";
}

if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $auto->ID ); }
if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );
if ( class_exists( '\Elementor\Plugin' ) ) {
    try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
}
echo "[OK]    Caches purgadas\n";
