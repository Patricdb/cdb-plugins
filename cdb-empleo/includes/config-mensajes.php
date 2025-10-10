<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register admin menu and page for message configuration.
 */
function cdb_empleo_mensajes_admin_menu() {
    // Ensure main menu exists only once.
    global $admin_page_hooks;
    if ( ! isset( $admin_page_hooks['cdb-empleo'] ) ) {
        add_menu_page(
            __( 'CdB Empleo', 'cdb-empleo' ),
            __( 'CdB Empleo', 'cdb-empleo' ),
            'manage_options',
            'cdb-empleo',
            '__return_null',
            'dashicons-id',
            4
        );
    }

    add_submenu_page(
        'cdb-empleo',
        __( 'Configuración de Mensajes y Avisos', 'cdb-empleo' ),
        __( 'Mensajes', 'cdb-empleo' ),
        'manage_options',
        'cdb-empleo-config-mensajes',
        'cdb_empleo_config_mensajes_page'
    );

    // Remove duplicated submenu pointing to the top-level page.
    remove_submenu_page( 'cdb-empleo', 'cdb-empleo' );
}
add_action( 'admin_menu', 'cdb_empleo_mensajes_admin_menu' );

/**
 * Enqueue assets for the configuration page.
 */
function cdb_empleo_mensajes_admin_enqueue( $hook ) {
    if ( 'cdb-empleo_page_cdb-empleo-config-mensajes' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'cdb-empleo-mensajes', CDB_EMPLEO_URL . 'assets/css/config-mensajes.css', array(), '1.0.0' );
    wp_enqueue_script( 'cdb-empleo-config-mensajes', CDB_EMPLEO_URL . 'assets/js/config-mensajes.js', array( 'jquery', 'wp-color-picker' ), '1.0.0', true );
}
add_action( 'admin_enqueue_scripts', 'cdb_empleo_mensajes_admin_enqueue' );

/**
 * Normalize CSS size values, defaulting to px.
 */
function cdb_empleo_normalize_size( $value, $default = '0px' ) {
    $value = trim( (string) $value );
    if ( '' === $value ) {
        return $default;
    }
    if ( preg_match( '/^\d+(?:\.\d+)?(px|rem|em|%)$/', $value ) ) {
        return $value;
    }
    if ( preg_match( '/^\d+(?:\.\d+)?$/', $value ) ) {
        return $value . 'px';
    }
    return $default;
}

/**
 * Render the configuration page.
 */
function cdb_empleo_config_mensajes_page() {
    $mensajes_defaults = cdb_empleo_get_mensajes_defaults();
    $tipos             = cdb_empleo_get_tipos_color();

    // Map of keys to labels
    $mensajes = array(
        'login_requerido'     => __( 'Login requerido', 'cdb-empleo' ),
        'ya_suscrito'         => __( 'Ya suscrito', 'cdb-empleo' ),
        'suscripcion_ok'      => __( 'Suscripción correcta', 'cdb-empleo' ),
        'suscripcion_eliminada' => __( 'Suscripción eliminada', 'cdb-empleo' ),
        'sin_ofertas'         => __( 'Sin ofertas', 'cdb-empleo' ),
        'no_suscrito'         => __( 'Sin suscripción', 'cdb-empleo' ),
        'sin_suscripciones'   => __( 'Sin suscripciones', 'cdb-empleo' ),
        'sin_permiso_form'    => __( 'Sin permisos', 'cdb-empleo' ),
        'campos_requeridos'   => __( 'Campos requeridos', 'cdb-empleo' ),
        'fecha_invalida'      => __( 'Fecha inválida', 'cdb-empleo' ),
        'error_generico'      => __( 'Error genérico', 'cdb-empleo' ),
        'error_solicitud'     => __( 'Error de solicitud', 'cdb-empleo' ),
        'oferta_registrada'   => __( 'Oferta registrada', 'cdb-empleo' ),
        'error_crear_oferta'  => __( 'Error al crear oferta', 'cdb-empleo' ),
    );

    if ( isset( $_POST['cdb_empleo_config_mensajes_nonce'] ) && wp_verify_nonce( $_POST['cdb_empleo_config_mensajes_nonce'], 'cdb_empleo_config_mensajes_save' ) ) {
        foreach ( $mensajes as $clave => $label ) {
            $texto      = isset( $_POST['cdb_empleo_mensaje_' . $clave] ) ? sanitize_text_field( $_POST['cdb_empleo_mensaje_' . $clave] ) : '';
            $secundario = isset( $_POST['cdb_empleo_mensaje_' . $clave . '_secundario'] ) ? sanitize_text_field( $_POST['cdb_empleo_mensaje_' . $clave . '_secundario'] ) : '';
            $tipo       = isset( $_POST['cdb_empleo_color_' . $clave] ) ? sanitize_text_field( $_POST['cdb_empleo_color_' . $clave] ) : 'aviso';
            $mostrar    = isset( $_POST['cdb_empleo_mensaje_' . $clave . '_mostrar'] ) ? 1 : 0;

            update_option( 'cdb_empleo_mensaje_' . $clave, $texto );
            update_option( 'cdb_empleo_mensaje_' . $clave . '_secundario', $secundario );
            update_option( 'cdb_empleo_color_' . $clave, $tipo );
            update_option( 'cdb_empleo_mensaje_' . $clave . '_mostrar', $mostrar );
        }

        if ( isset( $_POST['tipos_color'] ) && is_array( $_POST['tipos_color'] ) ) {
            $tipos_guardar = array();
            foreach ( $_POST['tipos_color'] as $tipo ) {
                $slug = isset( $tipo['slug'] ) ? sanitize_title( $tipo['slug'] ) : '';
                if ( empty( $slug ) ) {
                    continue;
                }
                $bg           = isset( $tipo['bg'] ) ? sanitize_hex_color( $tipo['bg'] ) : '';
                $text         = isset( $tipo['text'] ) ? sanitize_hex_color( $tipo['text'] ) : '';
                $border_color = isset( $tipo['border_color'] ) ? sanitize_hex_color( $tipo['border_color'] ) : '';
                $border_width = isset( $tipo['border_width'] ) ? cdb_empleo_normalize_size( $tipo['border_width'], '0px' ) : '0px';
                $border_radius = isset( $tipo['border_radius'] ) ? cdb_empleo_normalize_size( $tipo['border_radius'], '0px' ) : '0px';

                $tipos_guardar[ $slug ] = array(
                    'name'         => isset( $tipo['name'] ) ? sanitize_text_field( $tipo['name'] ) : '',
                    'class'        => isset( $tipo['class'] ) ? sanitize_text_field( $tipo['class'] ) : '',
                    'bg'           => $bg,
                    'text'         => $text,
                    'border_color' => $border_color,
                    'border_width' => $border_width,
                    'border_radius'=> $border_radius,
                );
            }
            update_option( 'cdb_empleo_tipos_color', $tipos_guardar );
            $tipos = cdb_empleo_get_tipos_color();
        }
        echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados.', 'cdb-empleo' ) . '</p></div>';
    }

    echo '<div class="wrap"><h1>' . esc_html__( 'Configuración de Mensajes y Avisos', 'cdb-empleo' ) . '</h1>';
    echo '<form method="post">';
    wp_nonce_field( 'cdb_empleo_config_mensajes_save', 'cdb_empleo_config_mensajes_nonce' );

    foreach ( $mensajes as $clave => $label ) {
        $def   = $mensajes_defaults[ $clave ];
        $texto = get_option( 'cdb_empleo_mensaje_' . $clave, $def['texto'] );
        $sec   = get_option( 'cdb_empleo_mensaje_' . $clave . '_secundario', $def['secundario'] );
        $tipo  = get_option( 'cdb_empleo_color_' . $clave, $def['tipo'] );
        $mostrar = get_option( 'cdb_empleo_mensaje_' . $clave . '_mostrar', $def['mostrar'] );
        echo '<div class="cdb-mensaje-row">';
        echo '<h2>' . esc_html( $label ) . '</h2>';
        echo '<div class="cdb-aviso-preview" id="preview_' . esc_attr( $clave ) . '">' . cdb_empleo_get_mensaje( $clave ) . '</div>';
        echo '<p><label>' . esc_html__( 'Texto principal', 'cdb-empleo' ) . '<br><input type="text" class="cdb-mensaje-input" data-preview="preview_' . esc_attr( $clave ) . '" name="cdb_empleo_mensaje_' . esc_attr( $clave ) . '" value="' . esc_attr( $texto ) . '" /></label></p>';
        echo '<p><label>' . esc_html__( 'Texto secundario', 'cdb-empleo' ) . '<br><input type="text" class="cdb-mensaje-input" data-preview="preview_' . esc_attr( $clave ) . '" name="cdb_empleo_mensaje_' . esc_attr( $clave ) . '_secundario" value="' . esc_attr( $sec ) . '" /></label></p>';
        echo '<p><label>' . esc_html__( 'Tipo / Color', 'cdb-empleo' ) . '<br><select name="cdb_empleo_color_' . esc_attr( $clave ) . '">';
        foreach ( $tipos as $slug => $t ) {
            echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $tipo, $slug, false ) . '>' . esc_html( $t['name'] ) . '</option>';
        }
        echo '</select></label></p>';
        echo '<p><label><input type="checkbox" class="cdb-mostrar-checkbox" data-preview="preview_' . esc_attr( $clave ) . '" name="cdb_empleo_mensaje_' . esc_attr( $clave ) . '_mostrar" value="1" ' . checked( $mostrar, 1, false ) . ' /> ' . esc_html__( 'Mostrar aviso', 'cdb-empleo' ) . '</label></p>';
        echo '</div>';
    }

    echo '<h2>' . esc_html__( 'Tipos de aviso', 'cdb-empleo' ) . '</h2>';
    echo '<table class="widefat" id="cdb-tipos-color"><thead><tr>';
    echo '<th>' . esc_html__( 'Slug', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Nombre', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Clase', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Color', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Texto', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Color del borde', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Grosor del borde', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Radio del borde', 'cdb-empleo' ) . '</th>';
    echo '<th>' . esc_html__( 'Vista previa', 'cdb-empleo' ) . '</th>';
    echo '<th></th></tr></thead><tbody>';
    $i = 0;
    foreach ( $tipos as $slug => $t ) {
        $style = 'background-color:' . esc_attr( $t['bg'] ) . ';color:' . esc_attr( $t['text'] ) . ';';
        if ( '0px' === $t['border_width'] ) {
            $style .= 'border-left:4px solid ' . esc_attr( $t['border_color'] ) . ';';
        } else {
            $style .= 'border:' . esc_attr( $t['border_width'] ) . ' solid ' . esc_attr( $t['border_color'] ) . ';';
        }
        $style .= 'border-radius:' . esc_attr( $t['border_radius'] ) . ';';
        echo '<tr>';
        echo '<td><input type="text" name="tipos_color[' . $i . '][slug]" value="' . esc_attr( $slug ) . '" /></td>';
        echo '<td><input type="text" name="tipos_color[' . $i . '][name]" value="' . esc_attr( $t['name'] ) . '" /></td>';
        echo '<td><input type="text" name="tipos_color[' . $i . '][class]" value="' . esc_attr( $t['class'] ) . '" /></td>';
        echo '<td><input type="text" class="cdb-color-picker" name="tipos_color[' . $i . '][bg]" value="' . esc_attr( $t['bg'] ) . '" /></td>';
        echo '<td><input type="text" class="cdb-color-picker" name="tipos_color[' . $i . '][text]" value="' . esc_attr( $t['text'] ) . '" /></td>';
        echo '<td><input type="text" class="cdb-color-picker" name="tipos_color[' . $i . '][border_color]" value="' . esc_attr( $t['border_color'] ) . '" /></td>';
        echo '<td><select name="tipos_color[' . $i . '][border_width]">';
        foreach ( array( '0px', '1px', '2px', '4px' ) as $bw ) {
            echo '<option value="' . esc_attr( $bw ) . '" ' . selected( $t['border_width'], $bw, false ) . '>' . esc_html( $bw ) . '</option>';
        }
        echo '</select></td>';
        echo '<td><select name="tipos_color[' . $i . '][border_radius]">';
        foreach ( array( '0px', '4px', '6px', '8px' ) as $br ) {
            echo '<option value="' . esc_attr( $br ) . '" ' . selected( $t['border_radius'], $br, false ) . '>' . esc_html( $br ) . '</option>';
        }
        echo '</select></td>';
        echo '<td><div class="cdb-aviso cdb-aviso-preview" style="' . $style . '"><strong class="cdb-mensaje-destacado">' . esc_html__( 'Vista previa', 'cdb-empleo' ) . '</strong><span class="cdb-mensaje-secundario"></span></div></td>';
        echo '<td><button type="button" class="button cdb-remove-row">&times;</button></td>';
        echo '</tr>';
        $i++;
    }
    echo '</tbody></table>';
    echo '<p><button type="button" class="button" id="cdb-add-color-row">' . esc_html__( 'Añadir tipo', 'cdb-empleo' ) . '</button></p>';

    submit_button();
    echo '</form></div>';
}
?>
