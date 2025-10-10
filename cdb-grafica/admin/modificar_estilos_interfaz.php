<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Renderiza la página de configuración de estilos de la interfaz.
function cdb_grafica_ui_page() {
    if ( isset( $_POST['cdb_grafica_ui_nonce'] ) && wp_verify_nonce( $_POST['cdb_grafica_ui_nonce'], 'cdb_guardar_ui_estilos' ) ) {
        $estilos = [
            'accordion_bg'       => sanitize_hex_color( $_POST['accordion_bg'] ?? '' ),
            'accordion_border'   => sanitize_hex_color( $_POST['accordion_border'] ?? '' ),
            'accordion_hover'    => sanitize_hex_color( $_POST['accordion_hover'] ?? '' ),
            'form_bg'            => sanitize_hex_color( $_POST['form_bg'] ?? '' ),
            'form_border'        => sanitize_hex_color( $_POST['form_border'] ?? '' ),
            'table_header_bg'    => sanitize_hex_color( $_POST['table_header_bg'] ?? '' ),
            'table_header_color' => sanitize_hex_color( $_POST['table_header_color'] ?? '' ),
            'font_family'        => sanitize_text_field( $_POST['font_family'] ?? '' ),
        ];
        update_option( 'cdb_grafica_ui_estilos', $estilos );
        echo '<div class="updated"><p>';
        esc_html_e( 'Estilos de interfaz actualizados.', 'cdb-grafica' );
        echo '</p></div>';
    }

    $defaults = [
        'accordion_bg'       => '#FAF8EE',
        'accordion_border'   => '#cdb888',
        'accordion_hover'    => '#cdb121',
        'form_bg'            => '#FAF8EE',
        'form_border'        => '#cdb888',
        'table_header_bg'    => '#cdb121',
        'table_header_color' => '#000000',
        'font_family'        => 'Arial, sans-serif',
    ];
    $estilos = wp_parse_args( get_option( 'cdb_grafica_ui_estilos', [] ), $defaults );

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configurar Estilos de Interfaz', 'cdb-grafica' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'cdb_guardar_ui_estilos', 'cdb_grafica_ui_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Acordeón - Fondo', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="accordion_bg" value="<?php echo esc_attr( $estilos['accordion_bg'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Acordeón - Borde', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="accordion_border" value="<?php echo esc_attr( $estilos['accordion_border'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Acordeón - Hover', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="accordion_hover" value="<?php echo esc_attr( $estilos['accordion_hover'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Formulario - Fondo', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="form_bg" value="<?php echo esc_attr( $estilos['form_bg'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Formulario - Borde', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="form_border" value="<?php echo esc_attr( $estilos['form_border'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Cabecera de Tabla - Fondo', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="table_header_bg" value="<?php echo esc_attr( $estilos['table_header_bg'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Cabecera de Tabla - Color', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="table_header_color" value="<?php echo esc_attr( $estilos['table_header_color'] ); ?>" class="cdb-color-field" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Tipografía global', 'cdb-grafica' ); ?></th>
                    <td><input type="text" name="font_family" value="<?php echo esc_attr( $estilos['font_family'] ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
    jQuery(function($){ $('.cdb-color-field').wpColorPicker(); });
    </script>
    <?php
}

