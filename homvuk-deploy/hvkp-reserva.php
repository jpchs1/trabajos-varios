<?php
/*
 * Nunca por web. Se comprueba por la peticion, no por el SAPI: en algunos
 * cPanel el `php` de la terminal es un binario CGI, y comparar con 'cli' a
 * secas hacia que el script terminara en silencio, sin imprimir nada.
 */
if ( PHP_SAPI !== 'cli' && ( isset( $_SERVER['REQUEST_METHOD'] ) || isset( $_SERVER['HTTP_HOST'] ) ) ) {
    exit;
}
// Si algo revienta, que se vea aqui y no solo en un log que nadie mira.
@ini_set( 'display_errors', '1' );
@ini_set( 'log_errors', '1' );
error_reporting( E_ALL );
/**
 * HOMVUK - Dar acceso al portal a un huesped (hvkp-reserva)
 *
 * El portal de /Magnolio/ pide un codigo de reserva. Quien llega por Airbnb y
 * ya tiene el suyo entra con ese; quien escribe por WhatsApp, o quien reservo
 * fuera de cualquier plataforma, no tiene ninguno y hay que crearselo.
 *
 * Esto hace lo mismo que la pantalla de Reservas del escritorio -crea la
 * reserva, genera el token y deja listo el enlace- pero en una linea, y
 * ademas imprime el enlace directo, que es lo que de verdad se le manda al
 * huesped: con el no tiene que teclear ningun codigo.
 *
 * Crear:
 *   php hvkp-reserva.php unidad=magnolio-306 nombre=Antonia apellido=Perez \
 *       in=2026-10-28 out=2026-11-30 pax=5
 *
 * Opcionales:
 *   codigo=HMABC12345   el codigo que ya tiene el huesped (p.ej. el de Airbnb),
 *                       para que entre con el que conoce. Si no, se inventa uno.
 *   email=...           y con  avisar=si  se le manda el enlace por correo
 *   telefono=...        origen=airbnb|whatsapp|directo      notas="..."
 *
 * Consultar:
 *   php hvkp-reserva.php listar
 *   php hvkp-reserva.php ver CODIGO
 *   php hvkp-reserva.php unidades
 */

printf( "HOMVUK %s · PHP %s (%s) · %s\n", basename( __FILE__ ), PHP_VERSION, PHP_SAPI, date( 'Y-m-d H:i' ) );

/**
 * Encuentra la instalacion de WordPress.
 *
 * Asumir que esta en la misma carpeta que el script fallaba en silencio -el
 * require moria antes de que nada se imprimiera- cuando WordPress no vive en
 * public_html. Se busca hacia arriba y un nivel hacia abajo, y se puede forzar
 * con WP_PATH=/ruta/a/wordpress php <script>.
 */
function hvkp_buscar_wordpress( $desde ) {
    $forzado = getenv( 'WP_PATH' );
    if ( $forzado ) {
        $forzado = rtrim( $forzado, '/' );
        foreach ( array( $forzado . '/wp-load.php', $forzado ) as $c ) {
            if ( is_file( $c ) && 'wp-load.php' === basename( $c ) ) {
                return $c;
            }
        }
        echo "[ERROR] WP_PATH=$forzado no contiene wp-load.php\n";
        exit( 1 );
    }

    $dir = $desde;
    for ( $i = 0; $i < 6; $i++ ) {
        if ( is_file( $dir . '/wp-load.php' ) ) {
            return $dir . '/wp-load.php';
        }
        $padre = dirname( $dir );
        if ( $padre === $dir ) {
            break;
        }
        $dir = $padre;
    }

    // Un nivel hacia abajo. Si aparece mas de una instalacion no se elige por
    // el agente: equivocarse de sitio aqui es escribir en la base que no era.
    foreach ( array( $desde, dirname( $desde ) ) as $base ) {
        $cand = array_values( array_filter( (array) glob( $base . '/*/wp-load.php' ), 'is_file' ) );
        if ( 1 === count( $cand ) ) {
            return $cand[0];
        }
        if ( count( $cand ) > 1 ) {
            echo "[ERROR] Hay varias instalaciones de WordPress cerca:\n";
            foreach ( $cand as $c ) {
                echo '          ' . dirname( $c ) . "\n";
            }
            echo "        Elige una:  WP_PATH=<la que sea> php " . basename( __FILE__ ) . "\n";
            exit( 1 );
        }
    }
    return '';
}

$hvkp_wp = hvkp_buscar_wordpress( __DIR__ );
if ( ! $hvkp_wp ) {
    echo "[ERROR] No encontre WordPress (wp-load.php) desde " . __DIR__ . "\n";
    echo "        Buscalo con:  find ~ -maxdepth 4 -name wp-load.php 2>/dev/null\n";
    echo "        Y luego:      WP_PATH=/la/carpeta/que/salga php " . basename( __FILE__ ) . "\n";
    exit( 1 );
}
if ( dirname( $hvkp_wp ) !== __DIR__ ) {
    echo 'WordPress: ' . dirname( $hvkp_wp ) . "\n";
}
echo "\n";

require $hvkp_wp;
global $wpdb;

if ( ! class_exists( 'HVKP_Token' ) ) {
    echo "[ERROR] El plugin del portal no esta activo.\n";
    exit( 1 );
}

$TR = $wpdb->prefix . 'portal_reservations';
$TU = $wpdb->prefix . 'portal_units';
$TT = $wpdb->prefix . 'portal_access_tokens';

/** Los argumentos vienen como clave=valor, en cualquier orden. */
function hvkr_args( $argv ) {
    $a = array();
    foreach ( array_slice( $argv, 1 ) as $x ) {
        if ( false !== strpos( $x, '=' ) ) {
            list( $k, $v ) = explode( '=', $x, 2 );
            $a[ strtolower( trim( $k ) ) ] = trim( $v );
        } else {
            $a[] = $x;
        }
    }
    return $a;
}

function hvkr_unidad( $ref ) {
    global $wpdb, $TU;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM $TU WHERE ( slug = %s OR name = %s OR id = %d ) AND active = 1 LIMIT 1",
        $ref, $ref, (int) $ref
    ) );
}

function hvkr_fecha( $f ) {
    $t = strtotime( $f );
    return ( $t && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $f ) ) ? $f : '';
}

/** El enlace que se le manda al huesped: entra sin teclear nada. */
function hvkr_enlace( $res_id ) {
    global $wpdb, $TT;
    $token = $wpdb->get_var( $wpdb->prepare(
        "SELECT token FROM $TT WHERE reservation_id = %d AND active = 1 AND expires_at > NOW()
         ORDER BY created_at DESC LIMIT 1", $res_id ) );
    if ( ! $token ) {
        $token = HVKP_Token::create_for_reservation( $res_id );
    }
    return HVKP_Token::get_portal_url( $token );
}

function hvkr_mostrar( $r ) {
    printf( "  %s %s · %s\n", $r->guest_name, $r->guest_last_name, $r->unit_name );
    printf( "  %s al %s · %d huespedes · %s\n",
        $r->check_in, $r->check_out, (int) $r->guests_count, $r->source );
    printf( "\n  Codigo:  %s\n", $r->reservation_code );
    printf( "  Enlace:  %s\n", hvkr_enlace( (int) $r->id ) );
}

$args = hvkr_args( $argv );
$modo = isset( $args[0] ) ? strtolower( $args[0] ) : 'crear';

/* =========================================================================
 * Consultar
 * ========================================================================= */

if ( 'unidades' === $modo ) {
    echo "== Unidades del portal ==\n\n";
    foreach ( (array) $wpdb->get_results( "SELECT id, name, slug FROM $TU WHERE active = 1 ORDER BY name" ) as $u ) {
        printf( "  %-18s %s\n", $u->slug, $u->name );
    }
    echo "\n";
    exit( 0 );
}

if ( 'listar' === $modo ) {
    echo "== Reservas vigentes ==\n\n";
    $rs = $wpdb->get_results(
        "SELECT r.*, u.name AS unit_name FROM $TR r JOIN $TU u ON r.unit_id = u.id
         WHERE r.check_out >= CURDATE() AND r.status != 'cancelled'
         ORDER BY r.check_in" );
    if ( ! $rs ) {
        echo "  (ninguna)\n\n";
        exit( 0 );
    }
    foreach ( $rs as $r ) {
        printf( "  %-14s %-26s %-18s %s a %s\n", $r->reservation_code,
            mb_substr( $r->guest_name . ' ' . $r->guest_last_name, 0, 24 ),
            mb_substr( $r->unit_name, 0, 16 ), $r->check_in, $r->check_out );
    }
    echo "\n  El enlace de una: php hvkp-reserva.php ver CODIGO\n\n";
    exit( 0 );
}

if ( 'ver' === $modo ) {
    $cod = isset( $args[1] ) ? strtoupper( $args[1] ) : '';
    if ( ! $cod ) {
        echo "[ERROR] Uso: php hvkp-reserva.php ver CODIGO\n";
        exit( 1 );
    }
    $r = $wpdb->get_row( $wpdb->prepare(
        "SELECT r.*, u.name AS unit_name FROM $TR r JOIN $TU u ON r.unit_id = u.id
         WHERE r.reservation_code = %s", $cod ) );
    if ( ! $r ) {
        echo "[ERROR] No hay ninguna reserva con el codigo $cod.\n";
        echo "        Listalas con: php hvkp-reserva.php listar\n";
        exit( 1 );
    }
    echo "== Reserva $cod ==\n\n";
    hvkr_mostrar( $r );
    echo "\n";
    exit( 0 );
}

if ( 'crear' !== $modo ) {
    echo "[ERROR] Argumento no reconocido: $modo. Usa 'crear', 'ver', 'listar' o 'unidades'.\n";
    exit( 1 );
}

/* =========================================================================
 * Crear
 * ========================================================================= */

$falta = array();
foreach ( array( 'unidad', 'nombre', 'apellido', 'in', 'out' ) as $k ) {
    if ( empty( $args[ $k ] ) ) {
        $falta[] = $k;
    }
}
if ( $falta ) {
    echo '[ERROR] Faltan: ' . implode( ', ', $falta ) . "\n\n";
    echo "  php hvkp-reserva.php unidad=magnolio-306 nombre=Antonia apellido=Perez \\\n";
    echo "      in=2026-10-28 out=2026-11-30 pax=5\n\n";
    echo "  Las unidades:  php hvkp-reserva.php unidades\n";
    exit( 1 );
}

$u = hvkr_unidad( $args['unidad'] );
if ( ! $u ) {
    echo "[ERROR] No existe la unidad '{$args['unidad']}'.\n";
    echo "        Las que hay: php hvkp-reserva.php unidades\n";
    exit( 1 );
}

$in  = hvkr_fecha( $args['in'] );
$out = hvkr_fecha( $args['out'] );
if ( ! $in || ! $out || strtotime( $out ) <= strtotime( $in ) ) {
    echo "[ERROR] Las fechas van como 2026-10-28, y la salida despues de la entrada.\n";
    exit( 1 );
}

$codigo = isset( $args['codigo'] ) ? strtoupper( preg_replace( '/[^A-Za-z0-9\-]/', '', $args['codigo'] ) ) : '';
if ( ! $codigo ) {
    $codigo = 'HVK-' . strtoupper( wp_generate_password( 8, false, false ) );
}

// Un codigo repetido dejaria a dos huespedes entrando a la misma pantalla.
$ya = $wpdb->get_row( $wpdb->prepare(
    "SELECT r.*, u.name AS unit_name FROM $TR r JOIN $TU u ON r.unit_id = u.id
     WHERE r.reservation_code = %s", $codigo ) );
if ( $ya ) {
    echo "[AVISO] Ya existe una reserva con el codigo $codigo. No se creo otra.\n\n";
    hvkr_mostrar( $ya );
    echo "\n";
    exit( 0 );
}

// Y dos reservas pisandose en la misma unidad tampoco: casi siempre es que se
// esta cargando dos veces la misma.
$choque = $wpdb->get_results( $wpdb->prepare(
    "SELECT reservation_code, guest_name, guest_last_name, check_in, check_out
     FROM $TR WHERE unit_id = %d AND status != 'cancelled'
     AND check_in < %s AND check_out > %s",
    (int) $u->id, $out, $in ) );
if ( $choque ) {
    echo "[AVISO] Esas fechas se cruzan con lo que ya hay en {$u->name}:\n";
    foreach ( $choque as $c ) {
        printf( "          %-14s %s %s  (%s a %s)\n", $c->reservation_code,
            $c->guest_name, $c->guest_last_name, $c->check_in, $c->check_out );
    }
    echo "        Se creo igual. Si era la misma reserva, borra la que sobre desde\n";
    echo "        Escritorio -> HOMVUK Portal -> Reservations.\n\n";
}

$wpdb->insert( $TR, array(
    'unit_id'          => (int) $u->id,
    'guest_name'       => sanitize_text_field( $args['nombre'] ),
    'guest_last_name'  => sanitize_text_field( $args['apellido'] ),
    'guest_email'      => isset( $args['email'] ) ? sanitize_email( $args['email'] ) : null,
    'guest_phone'      => isset( $args['telefono'] ) ? sanitize_text_field( $args['telefono'] ) : null,
    'reservation_code' => $codigo,
    'check_in'         => $in,
    'check_out'        => $out,
    'guests_count'     => isset( $args['pax'] ) ? max( 1, (int) $args['pax'] ) : 1,
    'source'           => isset( $args['origen'] ) ? sanitize_text_field( $args['origen'] ) : 'direct',
    'notes'            => isset( $args['notas'] ) ? sanitize_textarea_field( $args['notas'] ) : null,
    'status'           => 'confirmed',
) );

$res_id = (int) $wpdb->insert_id;
if ( ! $res_id ) {
    echo '[ERROR] No se pudo crear la reserva: ' . $wpdb->last_error . "\n";
    exit( 1 );
}

$r = $wpdb->get_row( $wpdb->prepare(
    "SELECT r.*, u.name AS unit_name FROM $TR r JOIN $TU u ON r.unit_id = u.id
     WHERE r.id = %d", $res_id ) );

echo "[OK]    Reserva creada.\n\n";
hvkr_mostrar( $r );

if ( ! empty( $args['email'] ) && isset( $args['avisar'] )
    && in_array( strtolower( $args['avisar'] ), array( 'si', 'sí', 'yes', '1' ), true ) ) {
    if ( class_exists( 'HVKP_Email' ) && HVKP_Email::send_portal_link( $r ) ) {
        echo "\n[OK]    Enlace enviado a {$r->guest_email}\n";
    } else {
        echo "\n[AVISO] No se pudo enviar el correo. El enlace de arriba sirve igual.\n";
    }
}

echo "\n  El huesped puede entrar de dos maneras:\n";
echo "    · Con el enlace de arriba, sin teclear nada.\n";
echo "    · Entrando a " . home_url( '/Magnolio/' ) . " y poniendo el codigo.\n";
echo "\n  Para volver a verlo: php hvkp-reserva.php ver $codigo\n\n";
