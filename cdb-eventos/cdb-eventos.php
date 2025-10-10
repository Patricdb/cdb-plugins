<?php
/**
 * Plugin Name: CdB Eventos
 * Description: Plugin para la gestión de eventos en proyectocdb.es (producto, formación, catas, talleres, presentaciones, etc.).
 * Version: 1.0.0
 * Author: CdB_
 * License: GPL2
 * Text Domain: cdb-eventos
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CDB_EVENTOS_PATH' ) ) {
    define( 'CDB_EVENTOS_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CDB_EVENTOS_URL' ) ) {
    define( 'CDB_EVENTOS_URL', plugin_dir_url( __FILE__ ) );
}

// Incluir archivos de funcionalidades
require_once CDB_EVENTOS_PATH . 'includes/meta-boxes.php';
require_once CDB_EVENTOS_PATH . 'includes/inscripciones.php';
require_once CDB_EVENTOS_PATH . 'includes/visibilidad.php';
require_once CDB_EVENTOS_PATH . 'includes/usuario-suscripciones.php';
require_once CDB_EVENTOS_PATH . 'includes/messages.php';
require_once CDB_EVENTOS_PATH . 'includes/config-mensajes.php';
require_once CDB_EVENTOS_PATH . 'includes/scripts.php';

/**
 * Cargar el dominio de traducción para el plugin.
 */
function cdb_eventos_load_textdomain() {
    load_plugin_textdomain( 'cdb-eventos', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'cdb_eventos_load_textdomain' );

// Clase principal del plugin
class CdB_Eventos {

    public function __construct() {
        // Registrar el Custom Post Type "evento"
        add_action( 'init', array( $this, 'cpt_evento' ) );
    }

    public function cpt_evento() {
        $labels = array(
            'name'               => __( 'Eventos', 'cdb-eventos' ),
            'singular_name'      => __( 'Evento', 'cdb-eventos' ),
            'menu_name'          => __( 'Eventos', 'cdb-eventos' ),
            'name_admin_bar'     => __( 'Evento', 'cdb-eventos' ),
            'add_new'            => __( 'Añadir Nuevo', 'cdb-eventos' ),
            'add_new_item'       => __( 'Añadir Nuevo Evento', 'cdb-eventos' ),
            'new_item'           => __( 'Nuevo Evento', 'cdb-eventos' ),
            'edit_item'          => __( 'Editar Evento', 'cdb-eventos' ),
            'view_item'          => __( 'Ver Evento', 'cdb-eventos' ),
            'all_items'          => __( 'Todos los Eventos', 'cdb-eventos' ),
            'search_items'       => __( 'Buscar Eventos', 'cdb-eventos' ),
            'parent_item_colon'  => __( 'Evento Padre:', 'cdb-eventos' ),
            'not_found'          => __( 'No se encontraron eventos.', 'cdb-eventos' ),
            'not_found_in_trash' => __( 'No se encontraron eventos en la papelera.', 'cdb-eventos' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'evento' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
            'show_in_rest'       => true,  // Habilita el editor de bloques (Gutenberg)
        );
        register_post_type( 'evento', $args );
    }

}

// Inicializar el plugin
new CdB_Eventos();
