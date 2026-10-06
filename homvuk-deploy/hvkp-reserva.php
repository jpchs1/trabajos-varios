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
 * Revisar y corregir:
 *   php hvkp-reserva.php revisar            que reservas hay y que esta cruzado
 *   php hvkp-reserva.php simular CODIGO     a que reserva llega ese codigo
 *   php hvkp-reserva.php enlace CODIGO      prepara el acceso de quien ya tiene
 *       codigo de Airbnb, y con nombre= y apellido= lo saluda por su nombre
 *   php hvkp-reserva.php cancelar CODIGO    la deja fuera y revoca sus enlaces
 *   php hvkp-reserva.php mover CODIGO unidad=magnolio-1506
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

/**
 * Caza los marcadores de ejemplo pegados tal cual.
 *
 * Pasa a menudo: se copia una linea de instrucciones con SU-APELLIDO o MONTO
 * dentro y se ejecuta sin cambiarlo. Da igual de quien sea la culpa; el
 * resultado es un huesped llamado "Alfredo SU-APELLIDO", y eso lo ve el
 * huesped. Mas vale negarse y decirlo.
 */
function hvkr_marcador( $v ) {
    $t = strtoupper( trim( (string) $v ) );
    if ( '' === $t ) {
        return false;
    }
    $palabras = 'APELLIDO|NOMBRE|CODIGO|MONTO|SLUG|NUMERO|UNIDAD|FECHA|EMAIL|TELEFONO|NOTAS';
    return (bool) preg_match( '/^(SU|TU|EL|LA|MI)?[\s_-]*(' . $palabras . ')S?([\s_-]|$)/', $t )
        || (bool) preg_match( '/(QUE-?SALGA|LO-?QUE-?SEA|XXXX|\.\.\.)/', $t );
}

function hvkr_rechaza_marcadores( $args ) {
    foreach ( array( 'nombre', 'apellido', 'unidad', 'codigo', 'email', 'telefono' ) as $k ) {
        if ( isset( $args[ $k ] ) && hvkr_marcador( $args[ $k ] ) ) {
            echo "[ERROR] '{$args[$k]}' parece un ejemplo, no un dato real.\n";
            echo "        Esa linea venia con un hueco que habia que rellenar.\n";
            echo "        Cambia $k= por el valor de verdad y vuelve a correrla.\n";
            exit( 1 );
        }
    }
    foreach ( $args as $k => $v ) {
        if ( is_int( $k ) && hvkr_marcador( $v ) ) {
            echo "[ERROR] '$v' parece un ejemplo, no un dato real.\n";
            echo "        Esa linea venia con un hueco que habia que rellenar.\n";
            exit( 1 );
        }
    }
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
    $unidad = ! empty( $r->unit_name ) ? $r->unit_name : '(sin unidad)';
    printf( "  %s %s · %s\n", $r->guest_name, $r->guest_last_name, $unidad );
    printf( "  %s al %s · %d huespedes · %s\n",
        $r->check_in, $r->check_out, (int) $r->guests_count, $r->source );
    printf( "\n  Codigo:  %s\n", $r->reservation_code );
    printf( "  Enlace:  %s\n", hvkr_enlace( (int) $r->id ) );
}

$args = hvkr_args( $argv );
$modo = isset( $args[0] ) ? strtolower( $args[0] ) : 'crear';
hvkr_rechaza_marcadores( $args );

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

if ( 'revisar' === $modo ) {
    $TA = $wpdb->prefix . 'portal_airbnb_reservations';
    $hay_tabla = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $TA ) ) === $TA;

    // La sincronizacion con Airbnb corre cada hora y es la que trae los
    // codigos nuevos. Si lleva mucho parada, un huesped recien reservado no
    // puede entrar todavia, y eso no se nota hasta que escribe preguntando.
    $ult = get_option( 'hvkp_last_airbnb_sync', '' );
    echo "== Sincronizacion con Airbnb ==\n\n";
    if ( ! $ult ) {
        echo "  Nunca corrio. Lanzala desde Escritorio -> HOMVUK Portal -> Airbnb.\n\n";
    } else {
        $hace = (int) round( ( current_time( 'timestamp' ) - strtotime( $ult ) ) / 60 );
        printf( "  Ultima vez: %s (hace %s)\n", $ult,
            $hace < 90 ? "$hace min" : round( $hace / 60 ) . ' h' );
        if ( $hace > 180 ) {
            echo "  Lleva parada mas de tres horas: deberia correr cada hora.\n";
        }
        echo "\n";
    }

    echo "== Reservas del portal ==\n\n";
    $rs = $wpdb->get_results(
        "SELECT r.*, u.name AS unit_name FROM $TR r LEFT JOIN $TU u ON r.unit_id = u.id
         ORDER BY r.check_in DESC LIMIT 60" );
    foreach ( (array) $rs as $r ) {
        printf( "  %-16s %-24s %-18s %s a %s  %s\n", $r->reservation_code,
            mb_substr( $r->guest_name . ' ' . $r->guest_last_name, 0, 22 ),
            mb_substr( (string) $r->unit_name, 0, 16 ), $r->check_in, $r->check_out, $r->status );
    }
    if ( ! $rs ) { echo "  (ninguna)\n"; }

    if ( $hay_tabla ) {
        echo "\n== Reservas de Airbnb ==\n\n";
        $as = $wpdb->get_results(
            "SELECT a.*, u.name AS unit_name FROM $TA a LEFT JOIN $TU u ON a.unit_id = u.id
             ORDER BY a.check_in DESC LIMIT 60" );
        foreach ( (array) $as as $a2 ) {
            printf( "  %-16s %-24s %-18s %s a %s  portal=%s\n", $a2->airbnb_code,
                mb_substr( $a2->guest_first_name . ' ' . $a2->guest_last_name, 0, 22 ),
                mb_substr( (string) $a2->unit_name, 0, 16 ), $a2->check_in, $a2->check_out,
                $a2->portal_reservation_id ? $a2->portal_reservation_id : '-' );
        }
        if ( ! $as ) { echo "  (ninguna)\n"; }
    }

    echo "\n== Problemas ==\n\n";
    $malo = 0;

    // Lo que explica que un huesped vea la reserva de otro: al validar un
    // codigo se mira primero la tabla del portal y despues la de Airbnb, asi
    // que un codigo repetido entre las dos lo atiende la del portal.
    if ( $hay_tabla ) {
        $choques = $wpdb->get_results(
            "SELECT a.airbnb_code, a.guest_first_name AS a_nom, a.guest_last_name AS a_ape,
                    r.id AS r_id, r.guest_name AS r_nom, r.guest_last_name AS r_ape,
                    ur.name AS r_unidad, ua.name AS a_unidad
             FROM $TA a
             JOIN $TR r ON r.reservation_code = a.airbnb_code
             LEFT JOIN $TU ur ON r.unit_id = ur.id
             LEFT JOIN $TU ua ON a.unit_id = ua.id" );
        foreach ( (array) $choques as $c ) {
            $malo++;
            echo "  [X] El codigo {$c->airbnb_code} esta en las dos tablas.\n";
            echo "      En Airbnb es de {$c->a_nom} {$c->a_ape} ({$c->a_unidad}),\n";
            echo "      pero en el portal lo tiene {$c->r_nom} {$c->r_ape} ({$c->r_unidad}).\n";
            echo "      Quien lo teclee va a ver la del portal. Quita la que sobre:\n";
            echo "        php hvkp-reserva.php cancelar {$c->airbnb_code}\n\n";
        }

        // Una fila de Airbnb apuntando a una reserva del portal de otro huesped.
        $cruces = $wpdb->get_results(
            "SELECT a.airbnb_code, a.guest_last_name AS a_ape, r.reservation_code,
                    r.guest_last_name AS r_ape
             FROM $TA a JOIN $TR r ON r.id = a.portal_reservation_id
             WHERE a.portal_reservation_id IS NOT NULL
               AND LOWER(a.guest_last_name) <> LOWER(r.guest_last_name)
               -- El iCal de Airbnb no trae nombres: los deja en 'Airbnb Guest'.
               -- Comparar contra ese relleno marcaba como cruce cualquier
               -- reserva a la que alguien le hubiera puesto el nombre a mano.
               AND LOWER(a.guest_last_name) NOT IN ('guest', 'airbnb guest', '')
               AND LOWER(r.guest_last_name) NOT IN ('guest', 'airbnb guest', '')" );
        foreach ( (array) $cruces as $c ) {
            $malo++;
            echo "  [X] {$c->airbnb_code} (de {$c->a_ape}) esta enlazada con la reserva\n";
            echo "      {$c->reservation_code}, que es de {$c->r_ape}.\n\n";
        }
    }

    // Dos reservas pisandose en la misma unidad.
    $solapes = $wpdb->get_results(
        "SELECT a.reservation_code AS c1, b.reservation_code AS c2, u.name AS unidad,
                a.guest_last_name AS ape1, b.guest_last_name AS ape2
         FROM $TR a JOIN $TR b ON a.unit_id = b.unit_id AND a.id < b.id
         LEFT JOIN $TU u ON a.unit_id = u.id
         WHERE a.status != 'cancelled' AND b.status != 'cancelled'
           AND a.check_in < b.check_out AND a.check_out > b.check_in" );
    foreach ( (array) $solapes as $s2 ) {
        $malo++;
        echo "  [!] {$s2->c1} ({$s2->ape1}) y {$s2->c2} ({$s2->ape2}) se pisan en {$s2->unidad}.\n\n";
    }

    if ( ! $malo ) { echo "  (ninguno)\n\n"; }
    exit( 0 );
}

if ( 'simular' === $modo ) {
    $cod = isset( $args[1] ) ? strtoupper( trim( $args[1] ) ) : '';
    if ( ! $cod ) {
        echo "[ERROR] Uso: php hvkp-reserva.php simular CODIGO\n";
        echo "        Dice a que reserva llega ese codigo, sin tocar nada.\n";
        exit( 1 );
    }

    echo "== Que pasa si alguien teclea $cod ==\n\n";

    // El portal mira en este orden, y se queda con la primera que encuentra.
    $r = $wpdb->get_row( $wpdb->prepare(
        "SELECT r.*, u.name AS unit_name FROM $TR r LEFT JOIN $TU u ON r.unit_id = u.id
         WHERE r.reservation_code = %s AND r.status != 'cancelled'", $cod ) );
    if ( $r ) {
        echo "  1. Lo encuentra entre las reservas del portal. Veria:\n\n";
        printf( "       Hola, %s\n", $r->guest_name );
        printf( "       %s · %s al %s\n\n", $r->unit_name, $r->check_in, $r->check_out );
        echo "     Enlace: " . hvkr_enlace( (int) $r->id ) . "\n\n";
        exit( 0 );
    }
    echo "  1. No esta entre las reservas del portal.\n";

    $TA = $wpdb->prefix . 'portal_airbnb_reservations';
    if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $TA ) ) !== $TA ) {
        echo "  2. No hay tabla de reservas de Airbnb.\n\n";
        echo "  => El portal le diria que el codigo no sirve.\n\n";
        exit( 0 );
    }
    $a2 = $wpdb->get_row( $wpdb->prepare(
        "SELECT a.*, u.name AS unit_name FROM $TA a LEFT JOIN $TU u ON a.unit_id = u.id
         WHERE a.airbnb_code = %s AND a.status != 'cancelled'", $cod ) );
    if ( ! $a2 ) {
        echo "  2. Tampoco entre las de Airbnb.\n\n";
        echo "  => El portal le diria que el codigo no sirve. Si la reserva existe en\n";
        echo "     Airbnb, falta sincronizarla: Escritorio -> HOMVUK Portal -> Airbnb.\n\n";
        exit( 0 );
    }

    echo "  2. Esta entre las de Airbnb. Veria:\n\n";
    printf( "       Hola, %s\n", $a2->guest_first_name );
    printf( "       %s · %s al %s\n\n", $a2->unit_name, $a2->check_in, $a2->check_out );
    if ( $a2->portal_reservation_id ) {
        $p = $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, u.name AS unit_name FROM $TR r LEFT JOIN $TU u ON r.unit_id = u.id
             WHERE r.id = %d", (int) $a2->portal_reservation_id ) );
        if ( $p ) {
            printf( "     Usa la reserva del portal %s (%s %s, %s).\n",
                $p->reservation_code, $p->guest_name, $p->guest_last_name, $p->unit_name );
            echo "     Enlace: " . hvkr_enlace( (int) $p->id ) . "\n\n";
        }
    } else {
        echo "     Todavia no tiene reserva del portal: se le crea una la primera vez\n";
        echo "     que entre, con el codigo ABB-$cod.\n\n";
    }
    exit( 0 );
}

if ( 'enlace' === $modo ) {
    $cod = isset( $args[1] ) ? strtoupper( trim( $args[1] ) ) : '';
    if ( ! $cod ) {
        echo "[ERROR] Uso: php hvkp-reserva.php enlace CODIGO\n";
        echo "        Con el codigo de Airbnb del huesped, le prepara su acceso.\n";
        echo "        Opcional: nombre=Alfredo apellido=Perez  para saludarlo por su nombre.\n";
        exit( 1 );
    }

    // El mismo camino que recorre el portal cuando el huesped teclea su
    // codigo, pero hecho aqui: asi se le puede mandar el enlace ya listo en
    // vez de pedirle que teclee nada.
    $res = HVKP_Token::validate_by_code( $cod );
    if ( ! $res ) {
        echo "[ERROR] El portal no reconoce el codigo $cod.\n";
        echo "        Miralo con: php hvkp-reserva.php simular $cod\n";
        exit( 1 );
    }

    // El iCal de Airbnb no trae nombres: llegan como 'Airbnb Guest', y el
    // portal saluda con eso. Si se sabe el real, se pone.
    if ( ! empty( $args['nombre'] ) || ! empty( $args['apellido'] ) ) {
        $campos = array();
        if ( ! empty( $args['nombre'] ) ) {
            $campos['guest_name'] = sanitize_text_field( $args['nombre'] );
        }
        if ( ! empty( $args['apellido'] ) ) {
            $campos['guest_last_name'] = sanitize_text_field( $args['apellido'] );
        }
        $wpdb->update( $TR, $campos, array( 'id' => (int) $res->id ) );
        $res = $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, u.name AS unit_name FROM $TR r LEFT JOIN $TU u ON r.unit_id = u.id
             WHERE r.id = %d", (int) $res->id ) );
    }

    echo "[OK]    Acceso listo.\n\n";
    hvkr_mostrar( $res );
    echo "\n  Mandale el enlace: entra directo, sin teclear nada.\n\n";
    exit( 0 );
}

if ( 'cancelar' === $modo ) {
    $cod = isset( $args[1] ) ? strtoupper( $args[1] ) : '';
    if ( ! $cod ) {
        echo "[ERROR] Uso: php hvkp-reserva.php cancelar CODIGO\n";
        exit( 1 );
    }
    $r = $wpdb->get_row( $wpdb->prepare(
        "SELECT r.*, u.name AS unit_name FROM $TR r LEFT JOIN $TU u ON r.unit_id = u.id
         WHERE r.reservation_code = %s", $cod ) );
    if ( ! $r ) {
        echo "[ERROR] No hay ninguna reserva del portal con el codigo $cod.\n";
        exit( 1 );
    }
    $wpdb->update( $TR, array( 'status' => 'cancelled' ), array( 'id' => (int) $r->id ) );
    $wpdb->update( $TT, array( 'active' => 0 ), array( 'reservation_id' => (int) $r->id ) );
    echo "[OK]    Cancelada la reserva $cod de {$r->guest_name} {$r->guest_last_name} ({$r->unit_name}).\n";
    echo "        Sus enlaces dejan de servir. No se borro nada: queda como 'cancelled'.\n";
    exit( 0 );
}

if ( 'mover' === $modo ) {
    $cod = isset( $args[1] ) ? strtoupper( $args[1] ) : '';
    $dst = isset( $args['unidad'] ) ? $args['unidad'] : '';
    if ( ! $cod || ! $dst ) {
        echo "[ERROR] Uso: php hvkp-reserva.php mover CODIGO unidad=magnolio-1506\n";
        exit( 1 );
    }
    $r = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $TR WHERE reservation_code = %s", $cod ) );
    if ( ! $r ) {
        echo "[ERROR] No hay ninguna reserva con el codigo $cod.\n";
        exit( 1 );
    }
    $u2 = hvkr_unidad( $dst );
    if ( ! $u2 ) {
        echo "[ERROR] No existe la unidad '$dst'. Las que hay: php hvkp-reserva.php unidades\n";
        exit( 1 );
    }
    $wpdb->update( $TR, array( 'unit_id' => (int) $u2->id ), array( 'id' => (int) $r->id ) );
    echo "[OK]    $cod ({$r->guest_name} {$r->guest_last_name}) queda en {$u2->name}.\n";
    exit( 0 );
}

if ( 'crear' !== $modo ) {
    echo "[ERROR] Argumento no reconocido: $modo. Usa 'crear', 'ver', 'listar',\n";
    echo "        'revisar', 'simular', 'enlace', 'cancelar', 'mover' o 'unidades'.\n";
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

// Al validar, el portal mira sus propias reservas ANTES que las de Airbnb. Asi
// que ponerle a una reserva del portal el codigo de Airbnb de otro huesped se
// lo secuestra: el dueño del codigo acaba viendo esta pantalla.
$TA = $wpdb->prefix . 'portal_airbnb_reservations';
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $TA ) ) === $TA ) {
    $duenno = $wpdb->get_row( $wpdb->prepare(
        "SELECT guest_first_name, guest_last_name, check_in, check_out
         FROM $TA WHERE airbnb_code = %s", $codigo ) );
    if ( $duenno && strcasecmp( trim( $duenno->guest_last_name ), trim( $args['apellido'] ) ) !== 0 ) {
        echo "[ERROR] El codigo $codigo ya es el de la reserva de Airbnb de\n";
        echo "        {$duenno->guest_first_name} {$duenno->guest_last_name} ({$duenno->check_in} a {$duenno->check_out}).\n";
        echo "        Si lo usas aqui, esa persona entraria a esta pantalla en vez de a la suya.\n";
        echo "        Deja el codigo fuera y se genera uno nuevo.\n";
        exit( 1 );
    }
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
