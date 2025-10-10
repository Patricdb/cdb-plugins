<?php
/**
 * Template for metacampos settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Metacampos', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-metacampos' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_metacampos' );
        do_settings_sections( 'cdb-empleado-metacampos' );
        submit_button();
        ?>
    </form>
</div>
