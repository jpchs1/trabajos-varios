<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Portal - Diagnostico y reparacion de un codigo de reserva
 *
 * Busca el codigo en todas las tablas del portal; si no aparece, sincroniza
 * los calendarios de Airbnb y vuelve a buscar. Muestra un diagnostico claro
 * de por que un huesped no puede entrar.
 *
 * Uso:  php hvkp-diag-reserva.php HMWPAEZCMC
 */

require __DIR__ . '/wp-load.php';
global $wpdb;

$codigo = isset( $argv[1] ) ? strtoupper( trim( $argv[1] ) ) : '';
if ( ! $codigo ) {
    echo "Uso: php hvkp-diag-reserva.php CODIGO\n";
    exit( 1 );
}

$t_res    = $wpdb->prefix . 'portal_reservations';
$t_abnb   = $wpdb->prefix . 'portal_airbnb_reservations';
$t_events = $wpdb->prefix . 'portal_airbnb_events';
$t_units  = $wpdb->prefix . 'portal_units';

function hvkd_buscar( $codigo ) {
    global $wpdb;
    $t_res  = $wpdb->prefix . 'portal_reservations';
    $t_abnb = $wpdb->prefix . 'portal_airbnb_reservations';

    $r = $wpdb->get_row( $wpdb->prepare(
        "SELECT r.*, u.name AS unidad FROM $t_res r
         LEFT JOIN {$wpdb->prefix}portal_units u ON r.unit_id = u.id
         WHERE UPPER(TRIM(r.reservation_code)) = %s",
        $codigo
    ) );
    if ( $r ) {
        return array( 'portal', $r );
    }
    $a = $wpdb->get_row( $wpdb->prepare(
        "SELECT a.*, u.name AS unidad FROM $t_abnb a
         LEFT JOIN {$wpdb->prefix}portal_units u ON a.unit_id = u.id
         WHERE UPPER(TRIM(a.airbnb_code)) = %s",
        $codigo
    ) );
    if ( $a ) {
        return array( 'airbnb', $a );
    }
    return array( '', null );
}

echo "== Diagnostico del codigo $codigo ==\n\n";

// 1) Busqueda inicial
list( $tipo, $fila ) = hvkd_buscar( $codigo );

// 2) Si no esta, buscar en los eventos del calendario y sincronizar
if ( ! $fila ) {
    echo "[INFO]  No esta registrado en el portal. Revisando el calendario de Airbnb...\n";

    $en_evento = $wpdb->get_row( $wpdb->prepare(
        "SELECT e.*, u.name AS unidad FROM $t_events e
         LEFT JOIN $t_units u ON e.unit_id = u.id
         WHERE e.description LIKE %s",
        '%' . $wpdb->esc_like( $codigo ) . '%'
    ) );
    if ( $en_evento ) {
        echo "[INFO]  El codigo aparece en el calendario de " . $en_evento->unidad . " (" . $en_evento->dtstart . " a " . $en_evento->dtend . ") pero no se creo la reserva.\n";
    } else {
        echo "[INFO]  El codigo todavia no aparece en el calendario descargado.\n";
    }

    echo "[INFO]  Sincronizando calendarios de Airbnb ahora...\n";
    if ( class_exists( 'HVKP_Airbnb' ) ) {
        $sync = HVKP_Airbnb::instance();
        $unidades = $wpdb->get_results( "SELECT * FROM $t_units WHERE active = 1" );
        foreach ( $unidades as $u ) {
            if ( empty( $u->ical_url ) ) {
                echo "[AVISO] " . $u->name . ": no tiene calendario de Airbnb configurado\n";
                continue;
            }
            $res = $sync->sync_unit_calendar( $u );
            $msg = isset( $res['message'] ) ? $res['message'] : '';
            echo ( ! empty( $res['success'] ) ? "[OK]    " : "[ERROR] " ) . $u->name . ": $msg\n";
        }
    }
    list( $tipo, $fila ) = hvkd_buscar( $codigo );
}

echo "\n";

// 3) Resultado
if ( $fila ) {
    if ( 'portal' === $tipo ) {
        echo "[OK]    Reserva encontrada (reserva del portal)\n";
        echo "        Huesped:  " . trim( $fila->guest_name . ' ' . $fila->guest_last_name ) . "\n";
        echo "        Unidad:   " . $fila->unidad . "\n";
        echo "        Fechas:   " . $fila->check_in . " a " . $fila->check_out . "\n";
        echo "        Estado:   " . $fila->status . "\n";
        if ( 'cancelled' === $fila->status ) {
            echo "[ERROR] La reserva esta CANCELADA: por eso el portal la rechaza.\n";
        }
    } else {
        echo "[OK]    Reserva encontrada (reserva de Airbnb)\n";
        echo "        Huesped:  " . trim( $fila->guest_first_name . ' ' . $fila->guest_last_name ) . "\n";
        echo "        Unidad:   " . $fila->unidad . "\n";
        echo "        Fechas:   " . $fila->check_in . " a " . $fila->check_out . "\n";
        echo "        Estado:   " . $fila->status . "\n";
        if ( 'cancelled' === $fila->status ) {
            echo "[ERROR] La reserva esta CANCELADA: por eso el portal la rechaza.\n";
        }
    }

    // Prueba real de ingreso
    if ( class_exists( 'HVKP_Token' ) ) {
        $prueba = HVKP_Token::validate_by_code( $codigo );
        if ( $prueba ) {
            echo "[OK]    Prueba de ingreso: el codigo YA FUNCIONA en el portal\n";
            echo "        Unidad asignada: " . ( isset( $prueba->unit_name ) ? $prueba->unit_name : '?' ) . "\n";
        } else {
            echo "[ERROR] Prueba de ingreso: el portal sigue rechazando el codigo\n";
        }
    }
} else {
    echo "[ERROR] El codigo $codigo NO existe en el portal ni en el calendario de Airbnb.\n";
    echo "        Causas posibles: la reserva es muy reciente y Airbnb aun no la publica\n";
    echo "        en el calendario, el codigo esta mal escrito, o la reserva es de otra\n";
    echo "        plataforma (Booking/Your.Rentals) y hay que cargarla a mano.\n";
}

// 4) Contexto
echo "\n== Estado del sistema ==\n";
$unidades = $wpdb->get_results( "SELECT id, name, ical_url FROM $t_units WHERE active = 1" );
foreach ( $unidades as $u ) {
    $n = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $t_events WHERE unit_id = %d", $u->id ) );
    echo "        " . $u->name . ": " . ( $u->ical_url ? "calendario OK" : "SIN calendario" ) . ", $n eventos\n";
}
$prox = wp_next_scheduled( 'hvkp_sync_airbnb_calendars' );
echo "        Sincronizacion automatica: " . ( $prox ? 'programada para ' . date_i18n( 'd/m/Y H:i', $prox ) : 'NO PROGRAMADA' ) . "\n";

echo "\n== Ultimas reservas de Airbnb registradas ==\n";
$ultimas = $wpdb->get_results(
    "SELECT a.airbnb_code, a.check_in, a.check_out, a.status, u.name AS unidad
     FROM $t_abnb a LEFT JOIN $t_units u ON a.unit_id = u.id
     ORDER BY a.check_in DESC LIMIT 12"
);
foreach ( $ultimas as $r ) {
    echo "        " . str_pad( $r->airbnb_code, 14 ) . " " . $r->check_in . " -> " . $r->check_out . "  " . $r->unidad . " (" . $r->status . ")\n";
}
echo "\nListo. Este script puede borrarse: rm -f hvkp-diag-reserva.php\n";
