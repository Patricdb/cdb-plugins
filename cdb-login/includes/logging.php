<?php
// Registrar accesos de usuarios al iniciar sesión
add_action('wp_login', function($user_login, $user){
    global $wpdb;
    $table_name = $wpdb->prefix . 'cdb_login_access_log';

    // Obtener el rol configurado
    $tracking_role = get_option('cdb_login_tracking_role', 'administrator');

    if ($tracking_role === 'disabled') {
        return;
    }

    if (in_array($tracking_role, (array) $user->roles)) {
        $wpdb->insert(
            $table_name,
            [
                'user_id'     => $user->ID,
                'username'    => $user_login,
                'user_ip'     => $_SERVER['REMOTE_ADDR'],
                'access_time' => current_time('mysql')
            ],
            ['%d','%s','%s','%s']
        );
    }
},10,2);
