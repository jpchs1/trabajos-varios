<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Reparar la sincronizacion automatica de Airbnb (hvkp-cron)
 *
 * El evento horario que importa las reservas de Airbnb estaba desprogramado:
 * por eso las reservas nuevas no dejaban entrar a los huespedes hasta hacerlo
 * a mano. Este script:
 *   1. Reprograma el evento horario.
 *   2. Instala un guardian en la base de datos (Code Snippets) que lo vuelve a
 *      programar solo si alguna vez se pierde.
 *   3. Avisa si WP-Cron depende de visitas (y por eso conviene un cron real).
 *   4. Sincroniza ahora mismo y muestra el resultado.
 *
 * Uso:  php hvkp-cron.php            (reparar)
 *       php hvkp-cron.php restore    (quitar el guardian)
 */

$HVKC_GUARD_B64 = 'LyoqCiAqIEhPTVZVSyBQb3J0YWwgLSBHdWFyZGlhbiBkZSBsYSBzaW5jcm9uaXphY2lvbiBkZSBBaXJibmIuCiAqIFNpIGVsIGV2ZW50byBob3JhcmlvIGRlc2FwYXJlY2UgKHBvciBlamVtcGxvIHNpIGVsIHBsdWdpbiBzZSBkZXNhY3RpdmEgeSBzZQogKiB2dWVsdmUgYSBhY3RpdmFyIGNvcGlhbmRvIGFyY2hpdm9zKSwgbG8gdnVlbHZlIGEgcHJvZ3JhbWFyIHNvbG8uCiAqLwphZGRfYWN0aW9uKCAnaW5pdCcsIGZ1bmN0aW9uICgpIHsKICAgIGlmICggISBjbGFzc19leGlzdHMoICdIVktQX0FpcmJuYicgKSApIHsKICAgICAgICByZXR1cm47CiAgICB9CiAgICBpZiAoICEgd3BfbmV4dF9zY2hlZHVsZWQoICdodmtwX3N5bmNfYWlyYm5iX2NhbGVuZGFycycgKSApIHsKICAgICAgICB3cF9zY2hlZHVsZV9ldmVudCggdGltZSgpICsgMTIwLCAnaG91cmx5JywgJ2h2a3Bfc3luY19haXJibmJfY2FsZW5kYXJzJyApOwogICAgfQp9LCA5OSApOw==';
$HVKC_GUARD_MD5 = '97dfc91d220fc9e3e0672306ac59e3d3';
$HVKC_NAME      = 'HOMVUK Portal Cron Guard';
$HVKC_HOOK      = 'hvkp_sync_airbnb_calendars';

require __DIR__ . '/wp-load.php';
global $wpdb;

$mode  = isset( $argv[1] ) ? $argv[1] : 'install';
$tabla = $wpdb->prefix . 'snippets';
$ok    = array();
$warn  = array();

if ( 'restore' === $mode ) {
    $wpdb->update( $tabla, array( 'active' => 0 ), array( 'name' => $HVKC_NAME ) );
    echo "[OK]    Guardian desactivado (el evento horario sigue programado)\n";
    exit( 0 );
}

// 1) Reprogramar el evento horario
$prox = wp_next_scheduled( $HVKC_HOOK );
if ( ! $prox ) {
    wp_schedule_event( time() + 60, 'hourly', $HVKC_HOOK );
    $prox = wp_next_scheduled( $HVKC_HOOK );
    $ok[] = 'Sincronizacion automatica reprogramada (cada hora)';
} else {
    $ok[] = 'La sincronizacion automatica ya estaba programada';
}
if ( $prox ) {
    $ok[] = 'Proxima sincronizacion: ' . date_i18n( 'd/m/Y H:i', $prox );
} else {
    $warn[] = 'No se pudo programar el evento horario';
}

// 2) Guardian en base de datos (se autorrepara si vuelve a perderse)
$code = base64_decode( $HVKC_GUARD_B64 );
if ( md5( $code ) !== $HVKC_GUARD_MD5 ) {
    $warn[] = 'El guardian no paso la verificacion md5; no se instalo';
} elseif ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tabla ) ) !== $tabla ) {
    $warn[] = 'No existe la tabla de snippets; el guardian no se instalo';
} else {
    $fila = array(
        'name'        => $HVKC_NAME,
        'description' => 'Reprograma la sincronizacion horaria de Airbnb si el evento desaparece.',
        'code'        => $code,
        'tags'        => 'homvuk',
        'scope'       => 'global',
        'priority'    => 10,
        'active'      => 1,
        'modified'    => current_time( 'mysql' ),
    );
    $existe = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $tabla WHERE name = %s", $HVKC_NAME ) );
    if ( $existe ) {
        $wpdb->update( $tabla, $fila, array( 'id' => (int) $existe ) );
        $ok[] = 'Guardian actualizado (id ' . $existe . '): si el evento se pierde, se reprograma solo';
    } else {
        $wpdb->insert( $tabla, $fila );
        $ok[] = 'Guardian instalado (id ' . $wpdb->insert_id . '): si el evento se pierde, se reprograma solo';
    }
}

// 3) WP-Cron depende de visitas?
if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
    $warn[] = 'WP-Cron esta desactivado en wp-config.php: la sincronizacion SOLO correra con un cron real del servidor';
} else {
    $ok[] = 'WP-Cron activo (se dispara con las visitas al sitio; el cron del servidor lo hace puntual)';
}

// 4) Sincronizar ahora
if ( class_exists( 'HVKP_Airbnb' ) ) {
    $sync     = HVKP_Airbnb::instance();
    $unidades = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}portal_units WHERE active = 1" );
    foreach ( $unidades as $u ) {
        if ( empty( $u->ical_url ) ) {
            $warn[] = $u->name . ': sin calendario de Airbnb configurado';
            continue;
        }
        $res = $sync->sync_unit_calendar( $u );
        $msg = isset( $res['message'] ) ? $res['message'] : '';
        if ( ! empty( $res['success'] ) ) {
            $ok[] = $u->name . ': ' . $msg;
        } else {
            $warn[] = $u->name . ': ' . $msg;
        }
    }
} else {
    $warn[] = 'El plugin del portal no esta activo; no se pudo sincronizar';
}

$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}portal_airbnb_reservations WHERE status != 'cancelled'" );
$ok[] = "Reservas de Airbnb registradas: $total";

echo "== HOMVUK Portal - Sincronizacion automatica de Airbnb ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
echo "Listo. Este script puede borrarse: rm -f hvkp-cron.php\n";
