<?php
/**
 * Template for textos settings page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Textos', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-textos' ); ?>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'cdb_empleado_textos' );
        do_settings_sections( 'cdb-empleado-textos' );
        submit_button();
        ?>
    </form>
</div>
