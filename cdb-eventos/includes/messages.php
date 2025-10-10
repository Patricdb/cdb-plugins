<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cdb_eventos_get_mensajes_default() {
    return array(
        'login_requerido' => array(
            'texto'     => __( 'Debes iniciar sesión para inscribirte en este evento.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'aviso',
            'mostrar'   => true,
        ),
        'ya_inscrito' => array(
            'texto'     => __( 'Ya estás inscrito en este evento.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'info',
            'mostrar'   => true,
        ),
        'inscripcion_ok' => array(
            'texto'     => __( '¡Te has inscrito correctamente en el evento!', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'exito',
            'mostrar'   => true,
        ),
        'error_generico' => array(
            'texto'     => __( 'Ha ocurrido un error. Por favor, inténtalo de nuevo más tarde.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'error',
            'mostrar'   => true,
        ),
        'sin_eventos' => array(
            'texto'     => __( 'No hay eventos disponibles.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'info',
            'mostrar'   => true,
        ),
        'evento_finalizado' => array(
            'texto'     => __( 'El evento ya ha finalizado.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'aviso',
            'mostrar'   => true,
        ),
        'sin_inscripciones' => array(
            'texto'     => __( 'No hay inscripciones para este evento.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'info',
            'mostrar'   => true,
        ),
        'sin_eventos_usuario' => array(
            'texto'     => __( 'No estás inscrito en ningún evento.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'info',
            'mostrar'   => true,
        ),
        'login_eventos_usuario' => array(
            'texto'     => __( 'Debes iniciar sesión para ver tus eventos inscritos.', 'cdb-eventos' ),
            'secundario'=> '',
            'tipo'      => 'aviso',
            'mostrar'   => true,
        ),
    );
}

function cdb_eventos_get_mensajes() {
    $defaults = cdb_eventos_get_mensajes_default();
    $saved    = get_option( 'cdb_eventos_mensajes', array() );
    return wp_parse_args( $saved, $defaults );
}

function cdb_eventos_get_tipos_color_default() {
    return array(
        'info' => array(
            'name'         => __( 'Información', 'cdb-eventos' ),
            'class'        => 'cdb-aviso--info',
            'bg'           => '#46bfe2',
            'text'         => '#0a4b78',
            'border_color' => '#46bfe2',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
        'exito' => array(
            'name'         => __( 'Éxito', 'cdb-eventos' ),
            'class'        => 'cdb-aviso--exito',
            'bg'           => '#46b450',
            'text'         => '#1e5220',
            'border_color' => '#46b450',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
        'aviso' => array(
            'name'         => __( 'Aviso', 'cdb-eventos' ),
            'class'        => 'cdb-aviso--aviso',
            'bg'           => '#ffb900',
            'text'         => '#604400',
            'border_color' => '#ffb900',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
        'error' => array(
            'name'         => __( 'Error', 'cdb-eventos' ),
            'class'        => 'cdb-aviso--error',
            'bg'           => '#dc3232',
            'text'         => '#760000',
            'border_color' => '#dc3232',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
    );
}

function cdb_eventos_get_tipos_color() {
    $defaults = cdb_eventos_get_tipos_color_default();
    $saved    = get_option( 'cdb_eventos_tipos_color', array() );
    $tipos    = wp_parse_args( $saved, $defaults );
    foreach ( $tipos as $slug => $tipo ) {
        $base = isset( $defaults[ $slug ] ) ? $defaults[ $slug ] : array();
        $tipo = wp_parse_args( $tipo, $base );

        // Migración perezosa desde claves antiguas
        if ( isset( $tipo['nombre'] ) && ! isset( $tipo['name'] ) ) {
            $tipo['name'] = $tipo['nombre'];
        }
        if ( isset( $tipo['clase'] ) && ! isset( $tipo['class'] ) ) {
            $tipo['class'] = 'cdb-aviso--' . $tipo['clase'];
        }
        if ( isset( $tipo['color'] ) && ! isset( $tipo['bg'] ) ) {
            $tipo['bg'] = $tipo['color'];
        }
        if ( isset( $tipo['color_texto'] ) && ! isset( $tipo['text'] ) ) {
            $tipo['text'] = $tipo['color_texto'];
        }
        if ( empty( $tipo['border_color'] ) ) {
            $tipo['border_color'] = $tipo['bg'];
        }
        if ( empty( $tipo['border_width'] ) ) {
            $tipo['border_width'] = '0px';
        }
        if ( empty( $tipo['border_radius'] ) ) {
            $tipo['border_radius'] = '4px';
        }
        $tipos[ $slug ] = $tipo;
    }
    return $tipos;
}

function cdb_eventos_get_mensaje_text( $clave ) {
    $mensajes = cdb_eventos_get_mensajes();
    return isset( $mensajes[ $clave ]['texto'] ) ? $mensajes[ $clave ]['texto'] : '';
}

function cdb_eventos_get_mensaje( $clave ) {
    $mensajes = cdb_eventos_get_mensajes();
    if ( ! isset( $mensajes[ $clave ] ) ) {
        return '';
    }
    $mensaje = $mensajes[ $clave ];
    if ( empty( $mensaje['mostrar'] ) ) {
        return '';
    }
    $tipo  = isset( $mensaje['tipo'] ) ? $mensaje['tipo'] : 'info';
    $texto = cdb_eventos_get_mensaje_text( $clave );
    $sec   = isset( $mensaje['secundario'] ) ? $mensaje['secundario'] : '';
    $clase = 'cdb-aviso cdb-aviso--' . esc_attr( $tipo ) . ' cdb-eventos-mensaje cdb-eventos-' . esc_attr( $tipo );
    $html  = '<div class="' . $clase . '">';
    $html .= '<strong class="cdb-mensaje-destacado">' . wp_kses_post( $texto ) . '</strong>';
    if ( $sec ) {
        $html .= '<span class="cdb-mensaje-secundario">' . wp_kses_post( $sec ) . '</span>';
    }
    $html .= '</div>';
    return $html;
}

function cdb_eventos_get_mensaje_js( $clave ) {
    return esc_js( cdb_eventos_get_mensaje_text( $clave ) );
}
