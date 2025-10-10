<?php
/**
 * Plugin Name: CdB_login
 * Description: Plugin para redirigir a los visitantes al login y personalizar accesos según roles.
 * Version: 1.0.0
 * Author: CdB_
 * Author URI: https://proyectocdb.es
 */

// Evita el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Cargar archivos del plugin
require_once plugin_dir_path( __FILE__ ) . 'includes/redirects.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/custom-login.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'admin/logs.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/logging.php';

register_activation_hook( __FILE__, 'cdb_login_activate' );
register_uninstall_hook( __FILE__, 'cdb_login_uninstall' );

function cdb_login_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cdb_login_access_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        username varchar(60) NOT NULL,
        user_ip varchar(100) NOT NULL,
        access_time datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}

function cdb_login_uninstall() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cdb_login_access_log';
    $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

    $options = [
        'cdb_login_background_color',
        'cdb_login_background_image',
        'cdb_login_button_color',
        'cdb_login_button_hover_color',
        'cdb_login_field_focus_color',
        'cdb_login_border_radius',
        'cdb_login_button_text',
        'cdb_login_limit_attempts',
        'cdb_login_redirect_message',
        'cdb_login_tracking_role',
        'cdb_login_text_color',
        'cdb_login_link_color',
        'cdb_login_link_hover_color',
        'cdb_login_eye_icon_color',
        'cdb_login_eye_icon_hover_color',
        'cdb_login_eye_icon_url',
        'cdb_login_font_family'
    ];

    foreach ( $options as $option ) {
        delete_option( $option );
    }
}

/**
 * Obtiene la URL de una página por su título con caché en transients
 */
function cdb_get_page_url_by_title($title) {
    // Definir clave de caché
    $cache_key = 'cdb_page_url_' . sanitize_title($title);
    $cached_url = get_transient($cache_key);

    if ($cached_url !== false) {
        return esc_url($cached_url);
    }

    // Crear consulta segura
    $query = new WP_Query([
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => $title, // Búsqueda por título
    ]);

    $url = '#'; // Valor predeterminado si no se encuentra la página

    if ($query->have_posts()) {
        $query->the_post();
        $url = get_permalink();
        wp_reset_postdata();
    }

    // Almacenar en caché por 24 horas
    set_transient($cache_key, $url, DAY_IN_SECONDS);

    return esc_url($url);
}

/**
 * Agrega enlaces de Condiciones de Uso y Política de Privacidad con la frase explicativa en la página de login
 */
function cdb_agregar_enlaces_login() {
    echo '<p style="text-align: center; font-size: 14px;">
        Al acceder, aceptas nuestras  <a href="' . cdb_get_page_url_by_title("Condiciones de Uso") . '">condiciones de uso</a> 
         y confirmas que has leído nuestra  <a href="' . cdb_get_page_url_by_title("Política de Privacidad") . '">política de privacidad</a>.
    </p>';
}
add_action('login_footer', 'cdb_agregar_enlaces_login');
