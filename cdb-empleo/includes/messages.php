<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default messages and helpers for configurable notices.
 */

function cdb_empleo_get_mensajes_defaults() {
    return array(
        'login_requerido' => array(
            'texto'      => __( 'Debes iniciar sesión para realizar esta acción.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'ya_suscrito' => array(
            'texto'      => __( 'Ya estás suscrito a esta oferta.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'info',
            'mostrar'    => 1,
        ),
        'suscripcion_ok' => array(
            'texto'      => __( '¡Te has suscrito correctamente a la oferta!', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'exito',
            'mostrar'    => 1,
        ),
        'suscripcion_eliminada' => array(
            'texto'      => __( 'Has eliminado tu suscripción a la oferta.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'info',
            'mostrar'    => 1,
        ),
        'no_suscrito' => array(
            'texto'      => __( 'No estás suscrito a ninguna oferta de empleo.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'info',
            'mostrar'    => 1,
        ),
        'sin_ofertas' => array(
            'texto'      => __( 'No hay ofertas disponibles.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'sin_suscripciones' => array(
            'texto'      => __( 'No hay suscripciones para esta oferta.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'sin_permiso_form' => array(
            'texto'      => __( 'No tienes permisos para realizar esta acción.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'campos_requeridos' => array(
            'texto'      => __( 'Por favor, completa todos los campos requeridos.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'fecha_invalida' => array(
            'texto'      => __( 'La fecha y hora de incorporación debe ser anterior a la fecha y hora de fin.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'error_generico' => array(
            'texto'      => __( 'Ocurrió un error.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'error_solicitud' => array(
            'texto'      => __( 'Error en la solicitud.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
        'oferta_registrada' => array(
            'texto'      => __( 'Oferta de empleo registrada exitosamente.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'exito',
            'mostrar'    => 1,
        ),
        'error_crear_oferta' => array(
            'texto'      => __( 'Error al crear la oferta.', 'cdb-empleo' ),
            'secundario' => '',
            'tipo'       => 'aviso',
            'mostrar'    => 1,
        ),
    );
}

/**
 * Obtain all registered notice types.
 */
function cdb_empleo_get_tipos_color() {
    $defaults = array(
        'aviso' => array(
            'name'         => __( 'Aviso', 'cdb-empleo' ),
            'class'        => 'cdb-aviso--aviso',
            'bg'           => '#dc2626',
            'text'         => '#ffffff',
            'border_color' => '#dc2626',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
        'info' => array(
            'name'         => __( 'Info', 'cdb-empleo' ),
            'class'        => 'cdb-aviso--info',
            'bg'           => '#5bc0de',
            'text'         => '#ffffff',
            'border_color' => '#5bc0de',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
        'exito' => array(
            'name'         => __( 'Éxito', 'cdb-empleo' ),
            'class'        => 'cdb-aviso--exito',
            'bg'           => '#5cb85c',
            'text'         => '#ffffff',
            'border_color' => '#5cb85c',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ),
    );

    $tipos = get_option( 'cdb_empleo_tipos_color', array() );

    // Normalize legacy keys and add new defaults.
    foreach ( $tipos as $slug => &$t ) {
        if ( isset( $t['nombre'] ) && ! isset( $t['name'] ) ) {
            $t['name'] = $t['nombre'];
            unset( $t['nombre'] );
        }
        if ( isset( $t['color'] ) && ! isset( $t['bg'] ) ) {
            $t['bg'] = $t['color'];
            unset( $t['color'] );
        }
        if ( ! isset( $t['class'] ) || false === strpos( $t['class'], 'cdb-aviso--' ) ) {
            $t['class'] = 'cdb-aviso--' . $slug;
        }
        $t = wp_parse_args( $t, array(
            'bg'           => isset( $defaults[ $slug ] ) ? $defaults[ $slug ]['bg'] : '#cccccc',
            'text'         => isset( $defaults[ $slug ] ) ? $defaults[ $slug ]['text'] : '#000000',
            'border_color' => isset( $defaults[ $slug ] ) ? $defaults[ $slug ]['border_color'] : '#cccccc',
            'border_width' => '0px',
            'border_radius'=> '4px',
        ) );
    }
    unset( $t );

    return wp_parse_args( $tipos, $defaults );
}

/**
 * Register a new type/color programmatically.
 */
function cdb_empleo_register_tipo_color( $slug, $args ) {
    if ( empty( $slug ) ) {
        return;
    }
    $tipos = cdb_empleo_get_tipos_color();
    if ( isset( $args['nombre'] ) && ! isset( $args['name'] ) ) {
        $args['name'] = $args['nombre'];
        unset( $args['nombre'] );
    }
    if ( isset( $args['color'] ) && ! isset( $args['bg'] ) ) {
        $args['bg'] = $args['color'];
        unset( $args['color'] );
    }
    $tipos[ $slug ] = wp_parse_args( $args, array(
        'name'         => $slug,
        'class'        => 'cdb-aviso--' . $slug,
        'bg'           => '#cccccc',
        'text'         => '#000000',
        'border_color' => '#cccccc',
        'border_width' => '0px',
        'border_radius'=> '4px',
    ) );
    update_option( 'cdb_empleo_tipos_color', $tipos );
}

/**
 * Get CSS class for a type/color.
 */
function cdb_empleo_get_tipo_color_class( $slug ) {
    $tipos = cdb_empleo_get_tipos_color();
    return isset( $tipos[ $slug ] ) ? $tipos[ $slug ]['class'] : $tipos['aviso']['class'];
}

/**
 * Return only the text of a message, without markup.
 */
function cdb_empleo_get_mensaje_text( $clave, $default = '' ) {
    $defaults = cdb_empleo_get_mensajes_defaults();
    if ( '' === $default && isset( $defaults[ $clave ] ) ) {
        $default = $defaults[ $clave ]['texto'];
    }
    return get_option( 'cdb_empleo_mensaje_' . $clave, $default );
}

/**
 * Build HTML for a message.
 */
function cdb_empleo_get_mensaje( $clave ) {
    $defaults = cdb_empleo_get_mensajes_defaults();
    if ( ! isset( $defaults[ $clave ] ) ) {
        return '';
    }
    $def = $defaults[ $clave ];

    $texto      = get_option( 'cdb_empleo_mensaje_' . $clave, $def['texto'] );
    $secundario = get_option( 'cdb_empleo_mensaje_' . $clave . '_secundario', $def['secundario'] );
    $tipo       = get_option( 'cdb_empleo_color_' . $clave, $def['tipo'] );
    $mostrar    = get_option( 'cdb_empleo_mensaje_' . $clave . '_mostrar', $def['mostrar'] );

    if ( ! $mostrar ) {
        return '';
    }

    $class  = cdb_empleo_get_tipo_color_class( $tipo );
    $legacy = 'cdb-aviso-' . $tipo;

    $html  = '<div class="cdb-aviso ' . esc_attr( $class ) . ' ' . esc_attr( $legacy ) . '">';
    $html .= '<strong class="cdb-mensaje-destacado">' . esc_html( $texto ) . '</strong>';
    $html .= '<span class="cdb-mensaje-secundario">' . esc_html( $secundario ) . '</span>';
    $html .= '</div>';

    return $html;
}

/**
 * Get message string prepared for JavaScript contexts.
 */
function cdb_empleo_get_mensaje_js( $clave ) {
    return esc_js( cdb_empleo_get_mensaje_text( $clave ) );
}

/**
 * Get default message text for translation extraction.
 */
function cdb_empleo_get_mensaje_i18n( $clave ) {
    $defaults = cdb_empleo_get_mensajes_defaults();
    return isset( $defaults[ $clave ] ) ? $defaults[ $clave ]['texto'] : '';
}

function cdb_empleo_render_mensaje( $clave ) {
    echo cdb_empleo_get_mensaje( $clave );
}

?>
