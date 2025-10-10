<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Encola los estilos y scripts necesarios para el frontend del plugin.
 */
function cdb_empleo_enqueue_scripts() {
    // Estilos base del plugin.
    wp_enqueue_style(
        'cdb-empleo-style',
        CDB_EMPLEO_URL . 'assets/css/estilo-ofertas.css',
        array(),
        '1.0.0'
    );

    // Estilos para los avisos configurables.
    wp_enqueue_style(
        'cdb-empleo-mensajes',
        CDB_EMPLEO_URL . 'assets/css/config-mensajes.css',
        array(),
        '1.0.0'
    );

    // Generar CSS dinámico para cada tipo de aviso.
    $tipos = cdb_empleo_get_tipos_color();
    $css   = '';
    foreach ( $tipos as $slug => $t ) {
        $selector = '.cdb-aviso.' . $t['class'] . ', .cdb-aviso-' . $slug;
        $css     .= $selector . '{background-color:' . $t['bg'] . ';color:' . $t['text'] . ';';
        if ( '0px' === $t['border_width'] ) {
            $css .= 'border:none;border-left:4px solid ' . $t['border_color'] . ';';
        } else {
            $css .= 'border:' . $t['border_width'] . ' solid ' . $t['border_color'] . ';';
        }
        $css .= 'border-radius:' . $t['border_radius'] . ';}';
    }
    if ( $css ) {
        wp_add_inline_style( 'cdb-empleo-mensajes', $css );
    }

    // Cargar los scripts solo en páginas que utilicen el formulario de ofertas.
    if ( is_singular() ) {
        global $post;
        if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'cdb_form_oferta' ) ) {
            wp_enqueue_script(
                'cdb-empleo-script',
                CDB_EMPLEO_URL . 'assets/js/script-ofertas.js',
                array( 'jquery', 'jquery-ui-autocomplete' ),
                '1.0.0',
                true
            );

            wp_localize_script(
                'cdb-empleo-script',
                'cdbEmpleo',
                array(
                    'ajaxurl'  => admin_url( 'admin-ajax.php' ),
                    'mensajes' => array(
                        'campos_requeridos' => cdb_empleo_get_mensaje_text( 'campos_requeridos' ),
                        'fecha_invalida'    => cdb_empleo_get_mensaje_text( 'fecha_invalida' ),
                        'error_generico'    => cdb_empleo_get_mensaje_text( 'error_generico' ),
                        'error_solicitud'   => cdb_empleo_get_mensaje_text( 'error_solicitud' ),
                    ),
                )
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'cdb_empleo_enqueue_scripts' );
