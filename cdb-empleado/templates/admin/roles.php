<?php
/**
 * Template for roles settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Roles', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-roles' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_roles' );
        do_settings_sections( 'cdb-empleado-roles' );
        submit_button();
        ?>
    </form>
</div>
