<?php
/**
 * Template for tarjeta settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Tarjeta de empleado', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-tarjeta' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_tarjeta' );
        do_settings_sections( 'cdb-empleado-tarjeta' );
        submit_button();
        ?>
    </form>
    <div id="cdb-empleado-preview" class="cdb-preview">
        <h3 class="cdb-preview-title"><?php esc_html_e( 'Vista previa', 'cdb-empleado' ); ?></h3>
        <p><?php esc_html_e( 'Texto de ejemplo', 'cdb-empleado' ); ?></p>
    </div>
</div>
