<?php
if ( php_sapi_name() !== 'cli' ) { exit; } // solo terminal, nunca via web
/**
 * HOMVUK Home PRO v2.0 (hvkp-home5)
 *
 * Instalador SIN archivos PHP persistentes (a prueba del borrado del servidor):
 *  1. Hero de portada: se inyecta como widget HTML de Elementor en la base de datos.
 *  2. Precios "/pornoche" -> "/por noche": archivo de traducciones .mo (binario, no PHP)
 *     en wp-content/languages/themes/, que tiene prioridad sobre el del tema.
 *  3. Limpia restos del plugin homvuk-home-pro (borrado por el servidor).
 *  4. Purga caches y verifica el resultado en vivo.
 *
 * Uso:  php hvkp-home5.php            (instalar)
 *       php hvkp-home5.php restore    (revertir hero + traducciones)
 */

$HVKP_MO_B64 = '3hIElQAAAAA7AAAAHAAAAPQBAAAAAAAAAAAAAAAAAADMAwAACAAAAM0DAAAJAAAA1gMAAAgAAADgAwAAKgAAAOkDAAAJAAAAFAQAAAcAAAAeBAAACAAAACYEAAAJAAAALwQAAAoAAAA5BAAADAAAAEQEAAAHAAAAUQQAABAAAABZBAAABwAAAGoEAAAOAAAAcgQAACcAAACBBAAACAAAAKkEAAAHAAAAsgQAABEAAAC6BAAACgAAAMwEAAALAAAA1wQAAAwAAADjBAAADQAAAPAEAAANAAAA/gQAABEAAAAMBQAABgAAAB4FAAAIAAAAJQUAAAYAAAAuBQAABQAAADUFAAAGAAAAOwUAAAQAAABCBQAACAAAAEcFAABMAAAAUAUAAAUAAACdBQAADAAAAKMFAAAFAAAAsAUAABgAAAC2BQAADQAAAM8FAAAIAAAA3QUAAAsAAADmBQAABgAAAPIFAAAPAAAA+QUAAAYAAAAJBgAABwAAABAGAAAIAAAAGAYAAAcAAAAhBgAABAAAACkGAAAQAAAALgYAAAYAAAA/BgAABAAAAEYGAAAIAAAASwYAAAcAAABUBgAACQAAAFwGAAAIAAAAZgYAAEEAAABvBgAALwAAALEGAAA5AAAA4QYAAAkAAAAbBwAABgAAACUHAACXAQAALAcAAAkAAADECAAACgAAAM4IAAAJAAAA2QgAAC4AAADjCAAACAAAABIJAAAIAAAAGwkAAAkAAAAkCQAACgAAAC4JAAAQAAAAOQkAABQAAABKCQAADQAAAF8JAAAYAAAAbQkAAAoAAACGCQAAEgAAAJEJAAA0AAAApAkAAA4AAADZCQAACAAAAOgJAAAdAAAA8QkAAAsAAAAPCgAADAAAABsKAAAMAAAAKAoAAAkAAAA1CgAABQAAAD8KAAAMAAAARQoAAAcAAABSCgAACgAAAFoKAAAIAAAAZQoAAA8AAABuCgAABQAAAH4KAAAGAAAAhAoAAAoAAACLCgAATAAAAJYKAAAJAAAA4woAAAgAAADtCgAABgAAAPYKAAAdAAAA/QoAAB0AAAAbCwAACAAAADkLAAALAAAAQgsAAA0AAABOCwAACQAAAFwLAAAIAAAAZgsAAAkAAABvCwAABwAAAHkLAAAIAAAAgQsAAA8AAACKCwAAFgAAAJoLAAAHAAAAsQsAABEAAAC5CwAACgAAAMsLAAAGAAAA1gsAAAoAAADdCwAADQAAAOgLAABEAAAA9gsAADsAAAA7DAAARQAAAHcMAAAJAAAAvQwAAAcAAADHDAAAACAvcGVyZGF5ACAvcGVyaG91cgAgUmV2aWV3cwAlMSRzIHJldmlldyBmb3IgJTIkcwAlMSRzIHJldmlld3MgZm9yICUyJHMALyBNb250aGx5AC9wZXJkYXkAL3BlcmhvdXIAL3Blcm5pZ2h0AEFkZCBSZXZpZXcAQWRkIGEgcmV2aWV3AEFkZCBuZXcAQWRkIG5ldyBjYXRlZ29yeQBBZGRyZXNzAEFzayBhIFF1ZXN0aW9uAEJlIHRoZSBmaXJzdCB0byByZXZpZXcgJmxkcXVvOyVzJnJkcXVvOwBCb29rIE5vdwBCb29raW5nAEJyb3dzZSBIb3QgT2ZmZXJzAENhdGVnb3JpZXMARGVzY3JpcHRpb24ARGVzY3JpcHRpb24gAERyb3Atb2ZmIERhdGUARW1haWwgYWRkcmVzcwBFeHRyYSBJbmZvcm1hdGlvbgBGcmlkYXkATG9jYXRpb24ATG9nIGluAExvZ2luAE1vbmRheQBOYW1lAE5vIHByaWNlAE9ubHkgbG9nZ2VkIGluIGN1c3RvbWVycyB3aG8gaGF2ZSBwdXJjaGFzZWQgdGhpcyBwcm9kdWN0IG1heSBsZWF2ZSBhIHJldmlldy4AUGhvbmUAUGljay11cCBEYXRlAFByaWNlAFByaWNlIGJ5IGRheSBvZiB0aGUgd2VlawBQcm9kdWN0IFRpdGxlAFF1YW50aXR5AFJlbWVtYmVyIG1lAFJlbnRhbABSZXF1ZXN0IEJvb2tpbmcAUmV2aWV3AFJldmlld3MAU2F0dXJkYXkAU2VlIG1hcABTZW5kAFNpbWlsYXIgTGlzdGluZ3MAU3VuZGF5AFRhZ3MAVGh1cnNkYXkAVHVlc2RheQBXZWRuZXNkYXkAV2Vla2RheXMAWW91IG11c3QgPGEgaHJlZj0iJXMiPnB1cmNoYXNlPC9hPiB0aGUgcHJvZHVjdCB0byBwb3N0IGEgY29tbWVudC4AWW91IG11c3QgYmUgJTEkc2xvZ2dlZCBpbiUyJHMgdG8gcG9zdCBhIHJldmlldy4AWW91IG11c3QgYmUgPGEgaHJlZj0iJXMiPmxvZ2dlZCBpbjwvYT4gdG8gcG9zdCBhIGNvbW1lbnQuAFlvdXIgbmFtZQBwZXJkYXkAUHJvamVjdC1JZC1WZXJzaW9uOiBlbnRveApSZXBvcnQtTXNnaWQtQnVncy1UbzogClBPVC1DcmVhdGlvbi1EYXRlOiAyMDIyLTA2LTE2IDAzOjQ4KzAwMDAKUE8tUmV2aXNpb24tRGF0ZTogMjAyMy0wNy0yMSAwMzowMiswMDAwCkxhc3QtVHJhbnNsYXRvcjogCkxhbmd1YWdlLVRlYW06IEVzcGHDsW9sCkxhbmd1YWdlOiBlc19FUwpNSU1FLVZlcnNpb246IDEuMApDb250ZW50LVR5cGU6IHRleHQvcGxhaW47IGNoYXJzZXQ9VVRGLTgKQ29udGVudC1UcmFuc2Zlci1FbmNvZGluZzogOGJpdApQbHVyYWwtRm9ybXM6IG5wbHVyYWxzPTI7IHBsdXJhbD1uICE9IDE7ClgtRG9tYWluOiBlbnRveApYLUdlbmVyYXRvcjogTG9jbyBodHRwczovL2xvY2FsaXNlLmJpei8KWC1Mb2NvLVZlcnNpb246IDIuNi40OyB3cC02LjIuMgoAIC9hbCBkw61hACAvcG9yIGhvcmEAT3BpbmlvbmVzACUxJHMgb3BpbmnDs24gcG9yICUyJHMAJTEkcyBvcGluaW9uZXMgcG9yICUyJHMAL01lbnN1YWwAL2FsIGTDrWEAL3BvciBob3JhAC9wb3Igbm9jaGUAQWdyZWdhciBPcGluacOzbgBBZ3JlZ2FyIHVuYSBPcGluacOzbgBBZ3JlZ2FyIG51ZXZvAEFncmVnYXIgbnVldmEgY2F0ZWdvcsOtYQBEaXJlY2Npw7NuAEhhY2VyIHVuYSBQcmVndW50YQBTZSBlbCBwcmltZXJvIGVuIGRlamFyIHVuYSBvcGluacOzbiAmbGRxdW87JXMmcmRxdW87AFJlc2VydmFyIEFob3JhAFJlc2VydmFyAE9mZXJ0YXMgSW50ZXJlc2FudGVzIHBhcmEgVMOtAENhdGVnb3LDrWFzAERlc2NyaXBjacOzbgBEZXNjcmlwY2nDs24AQ2hlY2stb3V0AEVtYWlsAE1hcyBkZXRhbGxlcwBWaWVybmVzAFViaWNhY2nDs24ASW5ncmVzYXIASW5pY2lhciBTZXNpw7NuAEx1bmVzAE5vbWJyZQBTaW4gUHJlY2lvAFNvbG8gbG9zIGNsaWVudGVzIHF1ZSByZXNlcnZhcm9uIGVzdGEgZXhwZXJpZW5jaWEgcHVlZGVuIGRlamFyIHN1IG9waW5pw7NuLiAAVGVsw6lmb25vAENoZWNrLUluAFByZWNpbwBQcmVjaW9zIHBvciBkw61hIGRlIGxhIFNlbWFuYQBUw610dWxvIG8gTm9tYnJlIGRlbCBQcm9kdWN0bwBQZXJzb25hcwBSZWNvcmRhcm1lIABBcnJlbmRhbWllbnRvAENvbnRhY3RhcgBPcGluacOzbgBPcGluaW9uZXMAU8OhYmFkbwBWZXIgTWFwYQBFbnZpYXIgQ29uc3VsdGEARXhwZXJpZW5jaWFzIFNpbWlsYXJlcwBEb21pbmdvAE51YmUgZGUgRXRpcXVldGFzAE1pw6lyY29sZXMATWFydGVzAE1pw6lyY29sZXMARMOtYSBwb3IgZMOtYQBEZWJlcyA8YSBocmVmPSIlcyI+aGFjZXIgdW5hIHJlc2VydmE8L2E+IHBhcmEgcHVibGljYXIgdW5hIE9waW5pw7NuLgBEZWJlcyAlMSRzIGluaWNpYXIgc2VzacOzbiAlMiRzIHBhcmEgcHVibGljYXIgdW5hIG9waW5pw7NuLgBEZWJlcyA8YSBocmVmPSIlcyI+aW5pY2lhciBzZXNpw7NuPC9hPiAgcGFyYSBwdWJsaWNhciB1biBjb21lbnRhcmlvLiAAVHUgTm9tYnJlAGFsIGTDrWEA';
$HVKP_MO_MD5 = '11ba52f3b3b5573de07bbd95039ff4c0';

function hvkp5_hero_html() {
    return <<<'HTML'
<style>
.hhp-hero{position:relative;border-radius:22px;overflow:hidden;margin:8px auto 36px;max-width:1320px;min-height:430px;display:flex;align-items:center;justify-content:center;text-align:center;isolation:isolate}
.hhp-hero-bg{position:absolute;inset:0;z-index:-1;background:linear-gradient(180deg,rgba(13,17,38,.72) 0%,rgba(13,17,38,.55) 55%,rgba(232,67,147,.38) 140%),url(/wp-content/uploads/2023/06/42801614-ddf6-4b73-a2e6-4b46e5f902ee-scaled-1-2048x1362.jpeg) center 45%/cover no-repeat;transform:scale(1.02)}
.hhp-hero-inner{padding:64px 24px;max-width:820px}
.hhp-eyebrow{display:inline-block;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);color:#fff;font-size:.82rem;font-weight:600;border-radius:999px;padding:7px 16px;letter-spacing:.02em;margin-bottom:18px}
.hhp-title{color:#fff;font-size:clamp(1.9rem,4.6vw,3.1rem);font-weight:800;line-height:1.12;letter-spacing:-.02em;margin:0 0 14px}
.hhp-title em{font-style:normal;color:#ff8ac2}
.hhp-sub{color:rgba(255,255,255,.88);font-size:clamp(.98rem,1.6vw,1.12rem);line-height:1.6;margin:0 auto 26px;max-width:640px}
.hhp-ctas{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-bottom:24px}
.hhp-btn{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:14px 28px;font-weight:700;font-size:1rem;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease}
.hhp-btn:hover{transform:translateY(-2px)}
.hhp-btn-primary{background:linear-gradient(135deg,#e84393,#d63384);color:#fff!important;box-shadow:0 10px 26px rgba(232,67,147,.45)}
.hhp-btn-wa{background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.4);color:#fff!important;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
.hhp-btn-wa:hover{background:rgba(37,211,102,.9);border-color:transparent}
.hhp-chips{display:flex;gap:8px;justify-content:center;flex-wrap:wrap}
.hhp-chips span{background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.20);color:rgba(255,255,255,.92);border-radius:999px;padding:5px 14px;font-size:.8rem;font-weight:600}
@media (max-width:640px){.hhp-hero{border-radius:0 0 20px 20px;margin:0 0 26px;min-height:400px}.hhp-hero-inner{padding:52px 18px}.hhp-ctas .hhp-btn{padding:13px 22px;font-size:.95rem}}
.entox_head_product{border-radius:16px 16px 0 0;overflow:hidden}
ul.products li.product,li.product{border-radius:16px!important;overflow:hidden;transition:transform .18s ease,box-shadow .18s ease!important}
ul.products li.product:hover,li.product:hover{transform:translateY(-4px);box-shadow:0 16px 34px rgba(16,24,40,.14)!important}
.entox-product-thumbnail img{transition:transform .35s ease}
li.product:hover .entox-product-thumbnail img{transform:scale(1.04)}
@media (prefers-reduced-motion:reduce){.hhp-btn,li.product,.entox-product-thumbnail img{transition:none!important}.hhp-btn:hover,li.product:hover{transform:none!important}}
</style>
<section class="hhp-hero" id="hhp-hero">
<div class="hhp-hero-bg"></div>
<div class="hhp-hero-inner">
<span class="hhp-eyebrow">&#9733; SuperAnfitri&oacute;n &middot; +500 hu&eacute;spedes felices &middot; 5.0</span>
<p class="hhp-title">Arriendo temporal <em>premium</em><br>en los mejores barrios de Santiago</p>
<p class="hhp-sub">Departamentos y casas amobladas listas para vivir &mdash; estad&iacute;as cortas, corporativas y relocation, con atenci&oacute;n 5 estrellas y sin intermediarios.</p>
<div class="hhp-ctas">
<a class="hhp-btn hhp-btn-primary" href="#" onclick="var h=document.getElementById('hhp-hero');window.scrollTo({top:h.offsetTop+h.offsetHeight-40,behavior:'smooth'});return false;">Explorar propiedades</a>
<a class="hhp-btn hhp-btn-wa" href="https://wa.me/56940211459?text=Hola%20HOMVUK%2C%20me%20interesa%20arrendar%20una%20propiedad." target="_blank" rel="noopener"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg> WhatsApp</a>
</div>
<div class="hhp-chips"><span>Vitacura</span><span>Las Condes</span><span>La Dehesa</span><span>Puc&oacute;n</span><span>Lago Colb&uacute;n</span></div>
</div>
</section>
HTML;
}

function hvkp5_inject_hero( $json, $hero_html ) {
    $data = json_decode( $json, true );
    if ( ! is_array( $data ) || empty( $data ) ) {
        return array( null, 'datos-elementor-invalidos' );
    }
    if ( false !== strpos( $json, 'hvkphero' ) ) {
        return array( null, 'ya-instalado' );
    }
    $widget = array(
        'id'         => 'hvkphw01',
        'elType'     => 'widget',
        'settings'   => array( 'html' => $hero_html ),
        'elements'   => array(),
        'widgetType' => 'html',
    );
    $first = $data[0];
    if ( isset( $first['elType'] ) && 'container' === $first['elType'] ) {
        $wrap = array(
            'id'       => 'hvkphero',
            'elType'   => 'container',
            'settings' => array(),
            'elements' => array( $widget ),
            'isInner'  => false,
        );
    } else {
        $wrap = array(
            'id'       => 'hvkphero',
            'elType'   => 'section',
            'settings' => array(),
            'elements' => array( array(
                'id'       => 'hvkphcol',
                'elType'   => 'column',
                'settings' => array( '_column_size' => 100 ),
                'elements' => array( $widget ),
                'isInner'  => false,
            ) ),
            'isInner'  => false,
        );
    }
    array_unshift( $data, $wrap );
    $encode = function_exists( 'wp_json_encode' ) ? 'wp_json_encode' : 'json_encode';
    $new    = call_user_func( $encode, $data );
    if ( ! is_string( $new ) || null === json_decode( $new, true ) ) {
        return array( null, 'fallo-al-reencodear' );
    }
    return array( $new, 'ok' );
}

// ---- Modo de prueba local (no requiere WordPress) ----
if ( getenv( 'HVKP5_TEST' ) ) {
    $fail = 0;
    $check = function ( $label, $cond ) use ( &$fail ) {
        echo ( $cond ? "[OK]    " : "[ERROR] " ) . $label . "\n";
        if ( ! $cond ) { $fail++; }
    };

    $hero = hvkp5_hero_html();
    $check( 'hero contiene seccion y estilos', strpos( $hero, 'hhp-hero' ) !== false && strpos( $hero, '<style>' ) !== false );

    // Esquema clasico (section/column)
    $sec = json_encode( array( array( 'id' => 'abc1234', 'elType' => 'section', 'settings' => array(), 'elements' => array(), 'isInner' => false ) ) );
    list( $out, $why ) = hvkp5_inject_hero( $sec, $hero );
    $d = json_decode( $out, true );
    $check( 'section: inyecta al inicio', 'ok' === $why && 'hvkphero' === $d[0]['id'] && 'section' === $d[0]['elType'] );
    $check( 'section: widget html anidado en columna', 'html' === $d[0]['elements'][0]['elements'][0]['widgetType'] );
    $check( 'section: contenido original intacto', 'abc1234' === $d[1]['id'] );

    // Esquema flexbox (container)
    $con = json_encode( array( array( 'id' => 'xyz9876', 'elType' => 'container', 'settings' => array(), 'elements' => array() ) ) );
    list( $out2, $why2 ) = hvkp5_inject_hero( $con, $hero );
    $d2 = json_decode( $out2, true );
    $check( 'container: inyecta al inicio', 'ok' === $why2 && 'container' === $d2[0]['elType'] );
    $check( 'container: widget html directo', 'html' === $d2[0]['elements'][0]['widgetType'] );

    // Idempotencia y casos borde
    list( $out3, $why3 ) = hvkp5_inject_hero( $out, $hero );
    $check( 'idempotente: segundo pase no duplica', null === $out3 && 'ya-instalado' === $why3 );
    list( $out4, $why4 ) = hvkp5_inject_hero( '', $hero );
    $check( 'json vacio: rechazado', null === $out4 );
    list( $out5, $why5 ) = hvkp5_inject_hero( 'no-es-json', $hero );
    $check( 'json corrupto: rechazado', null === $out5 );

    // El .mo embebido decodifica y pasa md5
    $bin = base64_decode( $GLOBALS['HVKP_MO_B64'] );
    $check( 'mo: md5 correcto', md5( $bin ) === $GLOBALS['HVKP_MO_MD5'] );
    $check( 'mo: magic gettext valido', "\xde\x12\x04\x95" === substr( $bin, 0, 4 ) || "\x95\x04\x12\xde" === substr( $bin, 0, 4 ) );
    $check( 'mo: contiene /por noche', strpos( $bin, '/por noche' ) !== false && strpos( $bin, '/pornoche' ) === false );

    echo $fail ? "FALLARON $fail pruebas\n" : "TODAS LAS PRUEBAS PASARON\n";
    exit( $fail ? 1 : 0 );
}

// ---- Ejecucion real ----
require __DIR__ . '/wp-load.php';

$mode = isset( $argv[1] ) ? $argv[1] : 'install';
$ok   = array();
$warn = array();
$err  = array();

$purge_caches = function () {
    if ( class_exists( '\Elementor\Plugin' ) ) {
        try { \Elementor\Plugin::instance()->files_manager->clear_cache(); } catch ( \Throwable $e ) {}
    }
    if ( function_exists( 'wp_cache_flush' ) ) { wp_cache_flush(); }
    do_action( 'litespeed_purge_all' );
    if ( function_exists( 'rocket_clean_domain' ) ) { rocket_clean_domain(); }
    if ( function_exists( 'w3tc_flush_all' ) ) { w3tc_flush_all(); }
    if ( function_exists( 'wp_cache_clear_cache' ) ) { wp_cache_clear_cache(); }
};

$mo_dir    = WP_CONTENT_DIR . '/languages/themes';
$mo_target = $mo_dir . '/entox-es_ES.mo';

if ( 'restore' === $mode ) {
    $b = get_option( 'hvkp_home5_backup' );
    if ( is_array( $b ) && ! empty( $b['post_id'] ) && ! empty( $b['data'] ) ) {
        update_post_meta( (int) $b['post_id'], '_elementor_data', wp_slash( $b['data'] ) );
        delete_post_meta( (int) $b['post_id'], '_elementor_css' );
        $ok[] = 'Hero revertido: _elementor_data restaurado desde respaldo';
    } else {
        $warn[] = 'No hay respaldo del hero para restaurar';
    }
    if ( file_exists( $mo_target . '.bak-home5' ) ) {
        copy( $mo_target . '.bak-home5', $mo_target );
        $ok[] = 'Traducciones: restaurado entox-es_ES.mo previo';
    } elseif ( file_exists( $mo_target ) ) {
        unlink( $mo_target );
        $ok[] = 'Traducciones: eliminado el override entox-es_ES.mo';
    }
    $purge_caches();
    echo "== RESTAURACION ==\n";
    foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
    foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
    exit( 0 );
}

// 1) Traducciones de precios (.mo binario, prioridad sobre el tema)
$bin = base64_decode( $HVKP_MO_B64 );
if ( md5( $bin ) !== $HVKP_MO_MD5 ) {
    echo "[ERROR] El archivo .mo embebido no paso la verificacion md5. Nada fue modificado.\n";
    exit( 1 );
}
if ( ! is_dir( $mo_dir ) ) {
    wp_mkdir_p( $mo_dir );
}
if ( file_exists( $mo_target ) && md5_file( $mo_target ) !== $HVKP_MO_MD5 ) {
    copy( $mo_target, $mo_target . '.bak-home5' );
}
file_put_contents( $mo_target, $bin );
if ( md5_file( $mo_target ) === $HVKP_MO_MD5 ) {
    $ok[] = 'Traducciones instaladas: "/por noche", "/por hora", "/al dia" (entox-es_ES.mo)';
} else {
    $err[] = 'No se pudo escribir ' . $mo_target;
}
// WP 6.5+ prefiere .l10n.php si existe; apartarlo para que mande el .mo nuevo
if ( file_exists( $mo_dir . '/entox-es_ES.l10n.php' ) ) {
    rename( $mo_dir . '/entox-es_ES.l10n.php', $mo_dir . '/entox-es_ES.l10n.php.bak-home5' );
    $ok[] = 'Apartado entox-es_ES.l10n.php antiguo (tenia prioridad sobre el .mo)';
}

// 2) Hero en la portada (solo base de datos, via Elementor)
$front_id = (int) get_option( 'page_on_front' );
if ( ! $front_id ) {
    $err[] = 'No hay pagina de portada configurada (page_on_front vacio)';
} else {
    $json = get_post_meta( $front_id, '_elementor_data', true );
    if ( ! is_string( $json ) || '' === trim( $json ) ) {
        $err[] = "La portada (post $front_id) no tiene datos de Elementor";
    } else {
        list( $new, $why ) = hvkp5_inject_hero( $json, hvkp5_hero_html() );
        if ( 'ya-instalado' === $why ) {
            $ok[] = 'Hero ya estaba instalado en la portada (no se duplico)';
        } elseif ( null === $new ) {
            $err[] = "No se pudo inyectar el hero: $why";
        } else {
            update_option( 'hvkp_home5_backup', array( 'post_id' => $front_id, 'data' => $json, 'ts' => time() ), false );
            update_post_meta( $front_id, '_elementor_data', wp_slash( $new ) );
            delete_post_meta( $front_id, '_elementor_css' );
            $check = get_post_meta( $front_id, '_elementor_data', true );
            if ( is_string( $check ) && false !== strpos( $check, 'hvkphero' ) ) {
                $ok[] = "Hero instalado en la portada (post $front_id) via base de datos";
            } else {
                $err[] = 'El hero no quedo guardado en _elementor_data';
            }
        }
    }
}

// 3) Limpiar restos del plugin homvuk-home-pro (el servidor borro su archivo)
$active = get_option( 'active_plugins', array() );
$pos    = array_search( 'homvuk-home-pro/homvuk-home-pro.php', (array) $active, true );
if ( false !== $pos ) {
    unset( $active[ $pos ] );
    update_option( 'active_plugins', array_values( $active ) );
    $ok[] = 'Desactivada la referencia al plugin homvuk-home-pro (su archivo fue borrado por el servidor)';
}
if ( is_dir( WP_CONTENT_DIR . '/plugins/homvuk-home-pro' ) ) {
    @rmdir( WP_CONTENT_DIR . '/plugins/homvuk-home-pro' );
}

// 4) Purga de caches
$purge_caches();
$ok[] = 'Caches purgadas (Elementor + cache de pagina si existe)';

// 5) Verificacion en vivo
$resp = wp_remote_get( home_url( '/?hvkp5=' . time() ), array( 'timeout' => 30, 'sslverify' => false ) );
if ( is_wp_error( $resp ) ) {
    $warn[] = 'No se pudo verificar en vivo: ' . $resp->get_error_message();
} else {
    $body = (string) wp_remote_retrieve_body( $resp );
    if ( false !== strpos( $body, 'hhp-hero' ) ) {
        $ok[] = 'Verificado: hero visible en la portada';
    } else {
        $warn[] = 'El hero aun no aparece en el HTML de la portada (puede ser cache; revisar en unos minutos)';
    }
    if ( false !== strpos( $body, 'pornoche' ) ) {
        $warn[] = 'La portada aun muestra "/pornoche" (puede ser cache; revisar en unos minutos)';
    } elseif ( false !== strpos( $body, 'por noche' ) ) {
        $ok[] = 'Verificado: precios muestran "/por noche"';
    }
}

echo "== HOMVUK Home PRO v2.0 (sin archivos PHP persistentes) ==\n";
foreach ( $ok as $m )   { echo "[OK]    $m\n"; }
foreach ( $warn as $m ) { echo "[AVISO] $m\n"; }
foreach ( $err as $m )  { echo "[ERROR] $m\n"; }
if ( $err ) { exit( 1 ); }
echo "Listo. Este instalador puede borrarse: rm -f hvkp-home5.php\n";
echo "Para revertir: php hvkp-home5.php restore\n";
