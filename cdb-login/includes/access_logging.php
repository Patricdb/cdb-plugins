<?php
// Crear página en el menú de administración para gestionar y visualizar el registro de accesos
add_action('admin_menu', function() {
    add_submenu_page(
        'cdb-login-settings',
        'Registro de Accesos',
        'Registro de Accesos',
        'manage_options',
        'cdb-login-access-log',
        'cdb_login_render_access_log_page'
    );
});

// Renderizar la página de gestión del registro de accesos con una tabla
function cdb_login_render_access_log_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cdb_login_access_log';

    // Manejar la eliminación de registros
    if (isset($_POST['cdb_delete_access_logs'])) {
        $wpdb->query("DELETE FROM $table_name");
        echo '<div class="updated"><p>Registros de accesos eliminados correctamente.</p></div>';
    }

    // Guardar cambios en la configuración del rol a rastrear
    if (isset($_POST['cdb_save_tracking_role'])) {
        update_option('cdb_login_tracking_role', sanitize_text_field($_POST['cdb_login_tracking_role']));
        echo '<div class="updated"><p>Configuración de rastreo actualizada.</p></div>';
    }

    // Obtener los registros de accesos
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY access_time DESC LIMIT 20");

    // Obtener el total de accesos registrados
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

    // Obtener el rol seleccionado actualmente
    $selected_role = get_option('cdb_login_tracking_role', 'administrator');
    $roles = wp_roles()->roles;
    ?>
    <div class="wrap">
        <h1>Registro de Accesos</h1>
        <p>Total de accesos registrados: <strong><?php echo esc_html($count); ?></strong></p>
        
        <form method="post">
            <h2>Configuración del Registro de Accesos</h2>
            <label for="cdb_login_tracking_role">Selecciona el rol a rastrear:</label>
            <select name="cdb_login_tracking_role">
                <option value="disabled" <?php selected($selected_role, 'disabled'); ?>>Desactivado</option>
                <?php foreach ($roles as $role_slug => $role_data) : ?>
                    <option value="<?php echo esc_attr($role_slug); ?>" <?php selected($selected_role, $role_slug); ?>>
                        <?php echo esc_html($role_data['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description">Selecciona el rol cuyos accesos serán rastreados o desactívalo.</p>
            <br>
            <button type="submit" name="cdb_save_tracking_role" class="button button-primary">Guardar Configuración</button>
        </form>

        <hr>

        <h2>Últimos 20 accesos registrados</h2>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>IP</th>
                    <th>Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs) : ?>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log->id); ?></td>
                            <td><?php echo esc_html($log->username); ?></td>
                            <td><?php echo esc_html($log->user_ip); ?></td>
                            <td><?php echo esc_html($log->access_time); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4">No hay accesos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <hr>

        <h2>Eliminar Registros</h2>
        <form method="post">
            <input type="hidden" name="cdb_delete_access_logs" value="1">
            <button type="submit" class="button button-primary" onclick="return confirm('¿Estás seguro de que deseas eliminar todos los registros de accesos?');">
                Borrar Todos los Registros
            </button>
        </form>
    </div>
    <?php
}

// Modificar la función de registro de accesos para respetar la configuración de desactivación
add_action('wp_login', function($user_login, $user) {
    global $wpdb;

    // Obtener el rol configurado en ajustes
    $tracking_role = get_option('cdb_login_tracking_role', 'administrator');

    // No registrar accesos si está desactivado
    if ($tracking_role === 'disabled') {
        return;
    }

    // Verificar si el usuario tiene el rol configurado
    if (in_array($tracking_role, (array) $user->roles)) {
        $table_name = $wpdb->prefix . 'cdb_login_access_log';
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $wpdb->insert(
            $table_name,
            [
                'user_id' => $user->ID,
                'username' => $user_login,
                'user_ip' => $user_ip,
                'access_time' => current_time('mysql')
            ],
            ['%d', '%s', '%s', '%s']
        );
    }
}, 10, 2);
?>
