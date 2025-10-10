<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cdb_eventos_enqueue_scripts() {
    wp_enqueue_style( 'cdb-eventos', CDB_EVENTOS_URL . 'assets/cdb-eventos.css', array(), '1.0' );
    wp_enqueue_style( 'cdb-eventos-config-mensajes', CDB_EVENTOS_URL . 'assets/css/config-mensajes.css', array(), '1.0' );
    $tipos = cdb_eventos_get_tipos_color();
    $css = '';
    foreach ( $tipos as $slug => $tipo ) {
        $bg    = isset( $tipo['bg'] ) ? $tipo['bg'] : '#fff';
        $color = isset( $tipo['text'] ) ? $tipo['text'] : '#000';
        $bcolor = isset( $tipo['border_color'] ) ? $tipo['border_color'] : $bg;
        $bwidth = isset( $tipo['border_width'] ) ? $tipo['border_width'] : '0px';
        $bradius= isset( $tipo['border_radius'] ) ? $tipo['border_radius'] : '0px';
        $css .= '.cdb-aviso.cdb-aviso--' . esc_attr( $slug ) . '{background-color:' . esc_attr( $bg ) . ';color:' . esc_attr( $color ) . ';border:' . esc_attr( $bwidth ) . ' solid ' . esc_attr( $bcolor ) . ';border-radius:' . esc_attr( $bradius ) . ';}';
        if ( '0px' === $bwidth || '0' === $bwidth ) {
            $css .= '.cdb-aviso.cdb-aviso--' . esc_attr( $slug ) . '{border-left:4px solid ' . esc_attr( $bcolor ) . ';}';
        }
    }
    if ( $css ) {
        wp_add_inline_style( 'cdb-eventos-config-mensajes', $css );
    }
    wp_enqueue_script( 'cdb-eventos-mensajes', CDB_EVENTOS_URL . 'assets/js/mensajes.js', array(), '1.0', true );
    wp_localize_script( 'cdb-eventos-mensajes', 'cdb_eventos_mensajes', cdb_eventos_get_mensajes_for_js() );
}
add_action( 'wp_enqueue_scripts', 'cdb_eventos_enqueue_scripts' );

function cdb_eventos_get_mensajes_for_js() {
    $mensajes = cdb_eventos_get_mensajes();
    $out = array();
    foreach ( $mensajes as $clave => $mensaje ) {
        $out[ $clave ] = $mensaje['texto'];
    }
    return $out;
}
