<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK - Agregar el NEW MG ZX 2026 (hvkp-mgzx)
 *
 * Crea un producto de arriendo nuevo clonando la configuracion del MG3 2019
 * (para heredar todo el sistema de reservas de OVA BRW: tipo de producto,
 * calendario, campos de reserva, categorias) y cambiando lo que corresponde:
 *   - Titulo: NEW MG ZX 2026
 *   - Precio por dia: USD 129 (se muestra como $105.264 CLP con la tasa 816)
 *   - Descripcion propia del modelo, color plateado
 *
 * Queda como BORRADOR hasta que tenga fotos; publicarlo despues es un comando:
 *   php hvkp-mgzx.php publicar
 *
 * Uso:  php hvkp-mgzx.php              (crear en borrador)
 *       php hvkp-mgzx.php publicar     (publicar cuando tenga fotos)
 *       php hvkp-mgzx.php restore      (enviar a la papelera lo creado)
 */

$HVKZ_DESC_B64 = 'PHA+QXJyaWVuZGEgZWwgPHN0cm9uZz5udWV2byBNRyBaWCAyMDI2PC9zdHJvbmc+IGVuIGNvbG9yIDxzdHJvbmc+cGxhdGVhZG88L3N0cm9uZz46IHVuIFNVViBjb21wYWN0byByZWNpJmVhY3V0ZTtuIHNhbGlkbyBkZSBmJmFhY3V0ZTticmljYSwgaWRlYWwgcGFyYSBtb3ZlcnNlIHBvciBTYW50aWFnbyBjb24gY29tb2RpZGFkIHkgcGFyYSBlc2NhcGFkYXMgZGUgZmluIGRlIHNlbWFuYS48L3A+CjxoMz5DYXJhY3RlciZpYWN1dGU7c3RpY2FzPC9oMz4KPHVsPgo8bGk+U1VWIGNvbXBhY3RvIHBhcmEgPHN0cm9uZz41IHBhc2FqZXJvczwvc3Ryb25nPiwgY29uIG1hbGV0ZXJvIGRlIDxzdHJvbmc+NDQ4IGxpdHJvczwvc3Ryb25nPjwvbGk+CjxsaT5UcmFuc21pc2kmb2FjdXRlO24gYXV0b20mYWFjdXRlO3RpY2E8L2xpPgo8bGk+UGFudGFsbGEgdCZhYWN1dGU7Y3RpbCBjb24gPHN0cm9uZz5BcHBsZSBDYXJQbGF5IHkgQW5kcm9pZCBBdXRvPC9zdHJvbmc+PC9saT4KPGxpPlNlZ3VyaWRhZCBjb24gPHN0cm9uZz42IGFpcmJhZ3M8L3N0cm9uZz4sIEFCUywgY29udHJvbCBkZSBlc3RhYmlsaWRhZCAoRVNQKSB5IGFzaXN0ZW5jaWFzIE1HIFBpbG90PC9saT4KPGxpPkJham8gY29uc3VtbyBkZSBjb21idXN0aWJsZTogaWRlYWwgcGFyYSBjaXVkYWQgeSBjYXJyZXRlcmE8L2xpPgo8L3VsPgo8aDM+VHUgYXJyaWVuZG8gaW5jbHV5ZTwvaDM+Cjx1bD4KPGxpPkVudHJlZ2EgcGVyc29uYWxpemFkYSB5IGNvb3JkaW5hZGEgcG9yIFdoYXRzQXBwPC9saT4KPGxpPlZlaCZpYWN1dGU7Y3VsbyBjb24gbWFudGVuY2lvbmVzIGFsIGQmaWFjdXRlO2EgeSByZXZpc2kmb2FjdXRlO24gcHJldmlhIGEgY2FkYSBlbnRyZWdhPC9saT4KPGxpPkF0ZW5jaSZvYWN1dGU7biBkaXJlY3RhIDcgZCZpYWN1dGU7YXMgYSBsYSBzZW1hbmEsIHNpbiBpbnRlcm1lZGlhcmlvczwvbGk+CjwvdWw+CjxoMz5SZXF1aXNpdG9zIHBhcmEgYXJyZW5kYXI8L2gzPgo8dWw+CjxsaT5Eb2N1bWVudG8gZGUgaWRlbnRpZGFkIHkgbGljZW5jaWEgZGUgY29uZHVjaXIgdmlnZW50ZSAoc2UgYWRqdW50YW4gYWwgcmVzZXJ2YXIpPC9saT4KPGxpPkRlcCZvYWN1dGU7c2l0byBkZSBnYXJhbnQmaWFjdXRlO2EgZGUgVVNEICQzMDAsIHJlZW1ib2xzYWJsZSBhbCBtaXNtbyBtZWRpbyBkZSBwYWdvIGFsIGRldm9sdmVyIGVsIHZlaCZpYWN1dGU7Y3VsbyBzaW4gZGEmbnRpbGRlO29zPC9saT4KPGxpPkFjZXB0YXIgbG9zIHQmZWFjdXRlO3JtaW5vcyB5IGNvbmRpY2lvbmVzIGRlIGFycmllbmRvPC9saT4KPC91bD4KPHA+PHN0cm9uZz5SZXNlcnZhIGVuIGwmaWFjdXRlO25lYSBlbiBtZW5vcyBkZSAxIG1pbnV0bzwvc3Ryb25nPjogZWxpZ2UgdHVzIGZlY2hhcywgYWRqdW50YSB0dXMgZG9jdW1lbnRvcyB5IHBhZ2EgZGUgZm9ybWEgc2VndXJhLiAmaXF1ZXN0O0R1ZGFzPyBFc2NyJmlhY3V0ZTtiZW5vcyBwb3IgV2hhdHNBcHAuPC9wPg==';
$HVKZ_TITULO   = 'NEW MG ZX 2026';
$HVKZ_SLUG     = 'new-mg-zx-2026';
$HVKZ_MODELO   = 'mg3-2019';   // producto base a clonar
$HVKZ_PRECIO   = '129';        // USD por dia
$HVKZ_PRECIO_ANT = '85';       // precio por dia del modelo base

/**
 * Decide el valor nuevo de una meta de precio.
 * Solo toca metas de precio cuyo valor sea exactamente el precio del modelo
 * base; cualquier otra cosa se copia tal cual.
 */
function hvkz_precio( $clave, $valor, $antes, $nuevo ) {
    if ( ! is_string( $valor ) && ! is_numeric( $valor ) ) {
        return $valor;
    }
    $v = trim( (string) $valor );
    if ( '' === $v ) {
        return $valor;
    }
    $es_precio = preg_match( '/price|daily|hourly|weekly|monthly|_regular_price|_sale_price/i', $clave );
    if ( ! $es_precio ) {
        return $valor;
    }
    $normal = str_replace( ',', '.', $v );
    if ( ! is_numeric( $normal ) ) {
        return $valor;
    }
    if ( abs( floatval( $normal ) - floatval( $antes ) ) > 0.001 ) {
        return $valor;
    }
    return $nuevo;
}

// ---- Pruebas locales ----
if ( getenv( 'HVKZ_TEST' ) ) {
    $fail = 0;
    $chk  = function ( $l, $c ) use ( &$fail ) { echo ( $c ? "[OK]    " : "[ERROR] " ) . $l . "\n"; if ( ! $c ) { $fail++; } };

    $chk( 'precio simple 85 -> 129', '129' === hvkz_precio( '_price', '85', '85', '129' ) );
    $chk( 'precio con decimales 85.00 -> 129', '129' === hvkz_precio( '_regular_price', '85.00', '85', '129' ) );
    $chk( 'precio con coma 85,00 -> 129', '129' === hvkz_precio( 'ovabrw_price_day', '85,00', '85', '129' ) );
    $chk( 'precio por dia de la semana -> 129', '129' === hvkz_precio( 'ovabrw_daily_monday', '85', '85', '129' ) );
    $chk( 'otro precio distinto NO se toca', '155' === hvkz_precio( 'ovabrw_price_week', '155', '85', '129' ) );
    $chk( 'meta que no es precio NO se toca', '85' === hvkz_precio( 'ovabrw_seats', '85', '85', '129' ) );
    $chk( 'texto en meta de precio NO se rompe', 'consultar' === hvkz_precio( 'ovabrw_price_note', 'consultar', '85', '129' ) );
    $chk( 'valor vacio se conserva', '' === hvkz_precio( '_price', '', '85', '129' ) );
    $chk( 'array (meta serializada) se conserva', array( 'a' ) === hvkz_precio( '_price_rules', array( 'a' ), '85', '129' ) );
    $chk( 'cero no se confunde con el precio base', '0' === hvkz_precio( '_sale_price', '0', '85', '129' ) );

    $d = base64_decode( $GLOBALS['HVKZ_DESC_B64'] );
    $chk( 'descripcion: modelo, color y datos reales', strpos( $d, 'MG ZX 2026' ) !== false && strpos( $d, 'plateado' ) !== false && strpos( $d, '448' ) !== false );
    $chk( 'descripcion: requisitos y deposito', strpos( $d, 'licencia de conducir' ) !== false && strpos( $d, 'USD $300' ) !== false );
    $chk( 'descripcion: sin rastros del MG3', stripos( $d, 'MG3' ) === false );

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';
global $wpdb;

$mode = isset( $argv[1] ) ? $argv[1] : 'crear';

$existente = $wpdb->get_var( $wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' AND post_status != 'trash' LIMIT 1",
    $HVKZ_SLUG
) );

if ( 'publicar' === $mode ) {
    if ( ! $existente ) {
        echo "[ERROR] Todavia no existe el producto; corre primero: php hvkp-mgzx.php\n";
        exit( 1 );
    }
    $miniatura = get_post_thumbnail_id( (int) $existente );
    if ( ! $miniatura ) {
        echo "[AVISO] El producto no tiene imagen destacada: se vera sin foto en el catalogo.\n";
    }
    $wpdb->update( $wpdb->posts, array( 'post_status' => 'publish' ), array( 'ID' => (int) $existente ) );
    clean_post_cache( (int) $existente );
    if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $existente ); }
    echo "[OK]    Publicado: " . get_permalink( (int) $existente ) . "\n";
    exit( 0 );
}

if ( 'restore' === $mode ) {
    $id = (int) get_option( 'hvkp_mgzx_id' );
    if ( ! $id ) {
        echo "[AVISO] No hay registro de un producto creado por este script.\n";
        exit( 0 );
    }
    wp_trash_post( $id );
    delete_option( 'hvkp_mgzx_id' );
    echo "[OK]    Producto enviado a la papelera (id $id)\n";
    exit( 0 );
}

if ( $existente ) {
    echo "[AVISO] El producto ya existe (id $existente): " . get_permalink( (int) $existente ) . "\n";
    echo "        Estado: " . get_post_status( (int) $existente ) . ". Para publicarlo: php hvkp-mgzx.php publicar\n";
    exit( 0 );
}

// 1) Producto base a clonar
$base = $wpdb->get_row( $wpdb->prepare(
    "SELECT * FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'product' LIMIT 1",
    $HVKZ_MODELO
) );
if ( ! $base ) {
    echo "[ERROR] No se encontro el producto base ($HVKZ_MODELO) para clonar la configuracion de arriendo.\n";
    exit( 1 );
}

// 2) Crear el producto
$nuevo_id = wp_insert_post( array(
    'post_title'   => $HVKZ_TITULO,
    'post_name'    => $HVKZ_SLUG,
    'post_content' => base64_decode( $HVKZ_DESC_B64 ),
    'post_excerpt' => 'SUV compacto 2026, color plateado, automatico, 5 pasajeros.',
    'post_status'  => 'draft',
    'post_type'    => 'product',
    'post_author'  => $base->post_author,
), true );

if ( is_wp_error( $nuevo_id ) ) {
    echo "[ERROR] No se pudo crear el producto: " . $nuevo_id->get_error_message() . "\n";
    exit( 1 );
}
update_option( 'hvkp_mgzx_id', (int) $nuevo_id, false );
echo "[OK]    Producto creado en borrador (id $nuevo_id)\n";

// 3) Copiar taxonomias (categorias, tipo de producto, etiquetas)
foreach ( get_object_taxonomies( 'product' ) as $tax ) {
    $terminos = wp_get_object_terms( $base->ID, $tax, array( 'fields' => 'ids' ) );
    if ( ! is_wp_error( $terminos ) && $terminos ) {
        wp_set_object_terms( (int) $nuevo_id, $terminos, $tax );
    }
}
echo "[OK]    Categorias y tipo de producto copiados del MG3 (arriendo de vehiculos)\n";

// 4) Copiar la configuracion (metas), ajustando los precios
$metas    = get_post_meta( $base->ID );
$saltar   = array( '_thumbnail_id', '_product_image_gallery', '_edit_lock', '_edit_last', '_wp_old_slug' );
$copiadas = 0;
$precios  = array();
foreach ( $metas as $clave => $valores ) {
    if ( in_array( $clave, $saltar, true ) ) {
        continue;
    }
    foreach ( $valores as $valor ) {
        $valor = maybe_unserialize( $valor );
        $nuevo_valor = hvkz_precio( $clave, $valor, $HVKZ_PRECIO_ANT, $HVKZ_PRECIO );
        if ( $nuevo_valor !== $valor ) {
            $precios[] = $clave;
        }
        add_post_meta( (int) $nuevo_id, $clave, $nuevo_valor );
        $copiadas++;
    }
}
echo "[OK]    Configuracion de arriendo copiada ($copiadas datos)\n";
echo "[OK]    Precio por dia: USD $HVKZ_PRECIO en " . count( $precios ) . " campos (" . implode( ', ', array_slice( array_unique( $precios ), 0, 6 ) ) . ")\n";

// 5) Sanidad: el precio quedo aplicado
$precio_final = get_post_meta( (int) $nuevo_id, '_price', true );
if ( '' !== $precio_final && floatval( $precio_final ) != floatval( $HVKZ_PRECIO ) ) {
    echo "[AVISO] El precio principal quedo en '$precio_final' (se esperaba $HVKZ_PRECIO); revisar en el admin.\n";
} else {
    echo "[OK]    Verificado: precio principal = " . ( '' !== $precio_final ? $precio_final : $HVKZ_PRECIO ) . " USD (~$" . number_format( floatval( $HVKZ_PRECIO ) * 816, 0, ',', '.' ) . " CLP)\n";
}

if ( function_exists( 'wc_delete_product_transients' ) ) { wc_delete_product_transients( (int) $nuevo_id ); }
if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
do_action( 'litespeed_purge_all' );

echo "\n== NEW MG ZX 2026 ==\n";
echo "Estado:    BORRADOR (no visible al publico todavia)\n";
echo "Editar:    " . admin_url( 'post.php?post=' . (int) $nuevo_id . '&action=edit' ) . "\n";
echo "Falta:     subir las fotos del auto (imagen destacada + galeria)\n";
echo "Publicar:  php hvkp-mgzx.php publicar\n";
echo "Deshacer:  php hvkp-mgzx.php restore\n";
