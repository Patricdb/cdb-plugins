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
require_once plugin_dir_path( __FILE__ ) . 'includes/access_logging.php';

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
