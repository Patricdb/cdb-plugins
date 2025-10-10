<?php
/**
 * Template for rendimiento settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Ajustes de rendimiento', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-rendimiento' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_rendimiento' );
        do_settings_sections( 'cdb-empleado-rendimiento' );
        submit_button();
        ?>
    </form>
</div>
