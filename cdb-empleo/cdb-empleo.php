<?php
/**
 * Plugin Name: CdB Empleo
 * Plugin URI: https://proyectocdb.es
 * Description: Plugin para la gestión de Ofertas de Empleo en la plataforma CdB.
 * Version: 1.0.0
 * Author: CdB_
 * Author URI: https://proyectocdb.es
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo
}

// Definir constantes del plugin
define( 'CDB_EMPLEO_PATH', plugin_dir_path( __FILE__ ) );
define( 'CDB_EMPLEO_URL', plugin_dir_url( __FILE__ ) );

/**
 * Carga el textdomain para las traducciones.
 */
function cdb_empleo_load_textdomain() {
    load_plugin_textdomain( 'cdb-empleo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'cdb_empleo_load_textdomain' );

// Incluir archivos necesarios
require_once CDB_EMPLEO_PATH . 'includes/cpt-oferta-empleo.php';
require_once CDB_EMPLEO_PATH . 'includes/metaboxes.php';
require_once CDB_EMPLEO_PATH . 'includes/ajax-handlers.php';
require_once CDB_EMPLEO_PATH . 'includes/roles-permisos.php';
require_once CDB_EMPLEO_PATH . 'includes/messages.php';
require_once CDB_EMPLEO_PATH . 'includes/config-mensajes.php';
require_once CDB_EMPLEO_PATH . 'includes/scripts.php';
require_once CDB_EMPLEO_PATH . 'includes/shortcodes.php';
require_once CDB_EMPLEO_PATH . 'includes/funciones.php';

