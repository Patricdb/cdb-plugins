<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Renderiza la página de configuración de estilos de la gráfica.
function cdb_grafica_estilos_page() {
    if ( isset( $_POST['cdb_grafica_estilos_nonce'] ) && wp_verify_nonce( $_POST['cdb_grafica_estilos_nonce'], 'cdb_guardar_estilos' ) ) {
        $estilos = [
            'border_width'     => isset($_POST['border_width']) ? intval($_POST['border_width']) : 2,
            'legend_font_size' => isset($_POST['legend_font_size']) ? intval($_POST['legend_font_size']) : 14,
            'ticks_step'       => isset($_POST['ticks_step']) ? intval($_POST['ticks_step']) : 1,
            'ticks_min'        => isset($_POST['ticks_min']) ? intval($_POST['ticks_min']) : 0,
            'ticks_max'        => isset($_POST['ticks_max']) ? intval($_POST['ticks_max']) : 10,
        ];
        update_option( 'cdb_grafica_estilos', $estilos );
        echo '<div class="updated"><p>';
        esc_html_e( 'Estilos actualizados.', 'cdb-grafica' );
        echo '</p></div>';
    }

    $defaults = [
        'border_width'     => 2,
        'legend_font_size' => 14,
        'ticks_step'       => 1,
        'ticks_min'        => 0,
        'ticks_max'        => 10,
    ];
    $estilos = get_option( 'cdb_grafica_estilos', $defaults );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configurar Estilos', 'cdb-grafica' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'cdb_guardar_estilos', 'cdb_grafica_estilos_nonce' ); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Ancho de borde', 'cdb-grafica' ); ?></th>
                    <td><input type="number" name="border_width" value="<?php echo esc_attr( $estilos['border_width'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Tamaño de fuente de la leyenda', 'cdb-grafica' ); ?></th>
                    <td><input type="number" name="legend_font_size" value="<?php echo esc_attr( $estilos['legend_font_size'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Step de ticks', 'cdb-grafica' ); ?></th>
                    <td><input type="number" name="ticks_step" value="<?php echo esc_attr( $estilos['ticks_step'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Mínimo de ticks', 'cdb-grafica' ); ?></th>
                    <td><input type="number" name="ticks_min" value="<?php echo esc_attr( $estilos['ticks_min'] ); ?>" /></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Máximo de ticks', 'cdb-grafica' ); ?></th>
                    <td><input type="number" name="ticks_max" value="<?php echo esc_attr( $estilos['ticks_max'] ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
