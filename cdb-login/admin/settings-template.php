<?php
if (!defined('ABSPATH')) {
    exit; // Evitar acceso directo
}
?>

<div class="wrap">
    <h1><?php _e('Configuración de CdB Login', 'cdb-login'); ?></h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('cdb_login_settings_group');
        do_settings_sections('cdb-login-settings');
        submit_button();
        ?>
    </form>
</div>
