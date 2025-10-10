<?php
if (!defined('ABSPATH')) {
    exit; // Evitar acceso directo
}

class CdB_AccessLogs_Admin {
    private $table_name;
    private $logs_per_page = 20; // Número de registros por página

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cdb_login_access_log';

        add_action('admin_menu', [$this, 'add_access_logs_menu']);
    }

    /**
     * Agrega la página de "Registro de Accesos" en el panel de administración.
     */
    public function add_access_logs_menu() {
        add_submenu_page(
            'cdb-login-settings',
            __('Registro de Accesos', 'cdb-login'),
            __('Registro de Accesos', 'cdb-login'),
            'manage_options',
            'cdb-login-access-log',
            [$this, 'render_access_logs_page']
        );
    }

    /**
     * Renderiza la página de gestión de registros de accesos con paginación.
     */
    public function render_access_logs_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('No tienes permisos para acceder a esta página.', 'cdb-login'));
        }

        global $wpdb;

        // Verificar si la tabla existe antes de ejecutar consultas
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") !== $this->table_name) {
            echo '<div class="error"><p>' . esc_html__('La tabla de accesos no existe.', 'cdb-login') . '</p></div>';
            return;
        }

        $current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $this->logs_per_page;

        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY access_time DESC LIMIT %d OFFSET %d",
            $this->logs_per_page,
            $offset
        ));

        $total_logs = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->table_name}"));

        $total_pages = ceil($total_logs / $this->logs_per_page);

        // Procesar eliminación de registros
        if (!empty($_POST['cdb_delete_access_logs']) && check_admin_referer('cdb_login_logs')) {
            $wpdb->query("DELETE FROM {$this->table_name}");
            echo '<div class="updated"><p>' . esc_html__('Registros de accesos eliminados correctamente.', 'cdb-login') . '</p></div>';
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Registro de Accesos', 'cdb-login'); ?></h1>
            <p><?php _e('Total de accesos registrados:', 'cdb-login'); ?> <strong><?php echo esc_html($total_logs); ?></strong></p>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'cdb-login'); ?></th>
                        <th><?php _e('Usuario', 'cdb-login'); ?></th>
                        <th><?php _e('IP', 'cdb-login'); ?></th>
                        <th><?php _e('Fecha y Hora', 'cdb-login'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)) : ?>
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
                            <td colspan="4"><?php _e('No hay accesos registrados.', 'cdb-login'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Paginación segura
            if ($total_pages > 1) {
                echo '<div class="tablenav"><div class="tablenav-pages">';
                echo paginate_links([
                    'base'    => esc_url(add_query_arg('paged', '%#%', admin_url('admin.php?page=cdb-login-access-log'))),
                    'format'  => '',
                    'current' => $current_page,
                    'total'   => $total_pages,
                    'prev_text' => __('« Anterior', 'cdb-login'),
                    'next_text' => __('Siguiente »', 'cdb-login'),
                ]);
                echo '</div></div>';
            }
            ?>

            <h2><?php _e('Eliminar Registros', 'cdb-login'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('cdb_login_logs'); ?>
                <input type="hidden" name="cdb_delete_access_logs" value="1">
                <button type="submit" class="button button-primary" onclick="return confirm('<?php _e('¿Estás seguro de que deseas eliminar todos los registros de accesos?', 'cdb-login'); ?>');">
                    <?php _e('Borrar Todos los Registros', 'cdb-login'); ?>
                </button>
            </form>
        </div>
        <?php
    }
}

// Instancia la clase
new CdB_AccessLogs_Admin();
