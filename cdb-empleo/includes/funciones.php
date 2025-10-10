<?php
// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Forzar la carga del template del archive para el CPT "oferta_empleo".
 *
 * Si se detecta que se está visualizando el archive del CPT "oferta_empleo",
 * se utiliza el template ubicado en el plugin.
 */
function cdb_empleo_template_archive( $template ) {
    if ( is_post_type_archive( 'oferta_empleo' ) ) {
        $plugin_template = CDB_EMPLEO_PATH . 'templates/archive-oferta_empleo.php';
        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'cdb_empleo_template_archive' );
