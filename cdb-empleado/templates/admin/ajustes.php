<?php
/**
 * Template for general settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'CdB Empleado', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_general' );
        do_settings_sections( 'cdb-empleado' );
        submit_button();
        ?>
    </form>
</div>
