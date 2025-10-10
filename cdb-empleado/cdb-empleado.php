<?php
/**
 * Plugin Name: CdB_Empleado
 * Plugin URI:  https://proyectocdb.es
 * Description: Plugin para gestionar el tipo de contenido "Empleado" en la plataforma CdB.
 * Version:     1.0.0
 * Author:      CdB_
 * Author URI:  https://proyectocdb.es
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cdb-empleado
 */

// Bloqueo de acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo al archivo
}

/**
 * Clase principal del plugin CdB Empleado.
 */
class Cdb_Empleado_Plugin {

    /**
     * Constructor: carga dependencias y registra hooks.
     */
    public function __construct() {
        // Inclusiones necesarias.
        require_once plugin_dir_path( __FILE__ ) . 'inc/metacampos-empleado.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/roles-capacidades.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/permisos.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/funciones-extra.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/ajustes.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/template-tags.php';

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( __CLASS__, 'registrar_cpt_empleado' ) );
        add_action( 'add_meta_boxes', array( $this, 'registrar_meta_box' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'front_assets' ) );
        add_action( 'template_redirect', array( $this, 'nocache' ) );
        add_action( 'save_post_empleado', array( $this, 'guardar_equipo_year' ) );
        add_action( 'restrict_manage_posts', array( $this, 'filtros_listado_empleados' ) );
        add_action( 'pre_get_posts', array( $this, 'aplicar_filtros_listado_empleados' ) );
        add_action( 'manage_posts_extra_tablenav', array( $this, 'acciones_listado_empleados' ), 20, 1 );
    }

    /**
     * Cargar el dominio de traducción del plugin.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'cdb-empleado', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Registrar el Custom Post Type "Empleado".
     */
    public static function registrar_cpt_empleado() {
        $labels = array(
            'name'               => _x( 'Empleados', 'Post Type General Name', 'cdb-empleado' ),
            'singular_name'      => _x( 'Empleado', 'Post Type Singular Name', 'cdb-empleado' ),
            'menu_name'          => __( 'Empleados', 'cdb-empleado' ),
            'name_admin_bar'     => __( 'Empleado', 'cdb-empleado' ),
            'add_new'            => __( 'Añadir Nuevo', 'cdb-empleado' ),
            'add_new_item'       => __( 'Añadir Nuevo Empleado', 'cdb-empleado' ),
            'new_item'           => __( 'Nuevo Empleado', 'cdb-empleado' ),
            'edit_item'          => __( 'Editar Empleado', 'cdb-empleado' ),
            'view_item'          => __( 'Ver Empleado', 'cdb-empleado' ),
            'all_items'          => __( 'Todos los Empleados', 'cdb-empleado' ),
            'search_items'       => __( 'Buscar Empleados', 'cdb-empleado' ),
            'not_found'          => __( 'No se encontraron empleados', 'cdb-empleado' ),
            'not_found_in_trash' => __( 'No se encontraron empleados en la papelera', 'cdb-empleado' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'cdb-empleado',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'empleado' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 4,
            'menu_icon'          => 'dashicons-businessperson',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'author', 'custom-fields' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'empleado', $args );
    }

    /**
     * Registro de metabox de Equipo y Año.
     */
    public function registrar_meta_box() {
        add_meta_box(
            'cdb_empleado_equipo_year',
            __( 'Equipo y Año', 'cdb-empleado' ),
            array( $this, 'meta_box_callback' ),
            'empleado',
            'side'
        );
    }

    /**
     * Obtener todos los equipos con título y año.
     *
     * @return array
     */
    public static function get_equipos() {
        $ids = get_posts( array(
            'post_type'               => 'equipo',
            'posts_per_page'          => -1,
            'post_status'             => 'publish',
            'meta_key'                => '_cdb_equipo_year',
            'orderby'                 => 'meta_value_num',
            'order'                   => 'DESC',
            'cache_results'           => false,
            'update_post_meta_cache'  => false,
            'update_post_term_cache'  => false,
            'no_found_rows'           => true,
            'fields'                  => 'ids',
        ) );

        if ( empty( $ids ) ) {
            return array();
        }

        $posts  = array_map( 'get_post', $ids );
        $titles = wp_list_pluck( $posts, 'post_title', 'ID' );

        $years_data = array_map( function( $id ) {
            return array(
                'ID'   => $id,
                'year' => get_post_meta( $id, '_cdb_equipo_year', true ),
            );
        }, $ids );
        $years = wp_list_pluck( $years_data, 'year', 'ID' );

        $equipos = array();
        foreach ( $ids as $id ) {
            $equipos[] = array(
                'ID'             => $id,
                'post_title'     => isset( $titles[ $id ] ) ? $titles[ $id ] : '',
                '_cdb_equipo_year' => isset( $years[ $id ] ) ? $years[ $id ] : '',
            );
        }

        return $equipos;
    }

    /**
     * Encolar el script de la metabox y pasar los datos de equipos.
     */
    public function admin_assets( $hook ) {
        global $post_type;
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }
        if ( 'empleado' !== $post_type ) {
            return;
        }

        $equipos = self::get_equipos();

        $script_path   = plugin_dir_path( __FILE__ ) . 'assets/js/equipo-year.js';
        $script_version = file_exists( $script_path ) ? filemtime( $script_path ) : false;

        wp_enqueue_script(
            'cdb-empleado-metabox',
            plugin_dir_url( __FILE__ ) . 'assets/js/equipo-year.js',
            array(),
            $script_version ?: null,
            true
        );

        wp_localize_script(
            'cdb-empleado-metabox',
            'cdbEmpleadoEquiposData',
            $equipos
        );

        wp_localize_script(
            'cdb-empleado-metabox',
            'cdbEmpleadoTexts',
            array(
                'select_team' => __( 'Seleccionar un Equipo', 'cdb-empleado' ),
            )
        );
    }

    /**
     * Encolar estilos del perfil del empleado en el frontal.
     */
    public function front_assets() {
        if ( ! is_admin() && is_singular( 'empleado' ) ) {
            $style_path   = plugin_dir_path( __FILE__ ) . 'assets/css/perfil-empleado.css';
            $style_version = file_exists( $style_path ) ? filemtime( $style_path ) : false;

            wp_enqueue_style(
                'cdb-perfil-empleado',
                plugins_url( 'assets/css/perfil-empleado.css', __FILE__ ),
                array(),
                $style_version ?: null
            );
        }
    }

    /**
     * Desactivar cache en la página singular de Empleado.
     */
    public function nocache() {
        if ( is_singular( 'empleado' ) ) {
            nocache_headers();
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            header( 'Cache-Control: post-check=0, pre-check=0', false );
            header( 'Pragma: no-cache' );
        }
    }

    /**
     * Render de la Metabox para asignar Equipo y Año.
     */
    public function meta_box_callback( $post ) {
        $selected_equipo = get_post_meta( $post->ID, '_cdb_empleado_equipo', true );
        $selected_year   = get_post_meta( $post->ID, '_cdb_empleado_year', true );

        wp_nonce_field( 'cdb_empleado_equipo_nonce_action', 'cdb_empleado_equipo_nonce' );

        $equipos = self::get_equipos();

        echo '<label for="cdb_empleado_year">' . __( 'Seleccionar Año:', 'cdb-empleado' ) . '</label>';
        echo '<select name="cdb_empleado_year" id="cdb_empleado_year">';
        echo '<option value="">' . __( 'Seleccionar un Año', 'cdb-empleado' ) . '</option>';

        $years = array();
        foreach ( $equipos as $equipo ) {
            $year = $equipo['_cdb_equipo_year'];
            if ( $year && ! in_array( $year, $years, true ) ) {
                $years[] = $year;
                echo '<option value="' . esc_attr( $year ) . '" ' . selected( $selected_year, $year, false ) . '>' . esc_html( $year ) . '</option>';
            }
        }
        echo '</select><br><br>';

        echo '<label for="cdb_empleado_equipo">' . __( 'Seleccionar un Equipo:', 'cdb-empleado' ) . '</label>';
        echo '<select name="cdb_empleado_equipo" id="cdb_empleado_equipo">';
        echo '<option value="">' . __( 'Seleccionar un Equipo', 'cdb-empleado' ) . '</option>';

        foreach ( $equipos as $equipo ) {
            $year = $equipo['_cdb_equipo_year'];
            if ( $selected_year == $year ) {
                echo '<option value="' . esc_attr( $equipo['ID'] ) . '" ' . selected( $selected_equipo, $equipo['ID'], false ) . '>' . esc_html( $equipo['post_title'] ) . '</option>';
            }
        }
        echo '</select>';
    }

    /**
     * Guardar los valores de Equipo y Año del empleado.
     */
    public function guardar_equipo_year( $post_id ) {
        if ( ! isset( $_POST['cdb_empleado_equipo_nonce'] ) ||
            ! wp_verify_nonce( $_POST['cdb_empleado_equipo_nonce'], 'cdb_empleado_equipo_nonce_action' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['cdb_empleado_year'] ) ) {
            update_post_meta( $post_id, '_cdb_empleado_year', sanitize_text_field( $_POST['cdb_empleado_year'] ) );
        }

        if ( isset( $_POST['cdb_empleado_equipo'] ) ) {
            $equipo_id = absint( $_POST['cdb_empleado_equipo'] );

            if ( $equipo_id ) {
                $equipo_post = get_post( $equipo_id );

                if ( $equipo_post && 'equipo' === $equipo_post->post_type && 'publish' === $equipo_post->post_status ) {
                    update_post_meta( $post_id, '_cdb_empleado_equipo', $equipo_id );
                } else {
                    delete_post_meta( $post_id, '_cdb_empleado_equipo' );
                    error_log( "cdb-empleado: ID de equipo inválido {$equipo_id} para el empleado {$post_id}" );
                }
            } else {
                delete_post_meta( $post_id, '_cdb_empleado_equipo' );
            }
        }
    }

    /**
     * Mostrar filtros personalizados en el listado de empleados.
     */
    public function filtros_listado_empleados() {
        global $typenow;
        if ( 'empleado' !== $typenow ) {
            return;
        }

        $equipos       = self::get_equipos();
        $current_team  = isset( $_GET['equipo'] ) ? absint( $_GET['equipo'] ) : '';
        $current_year  = isset( $_GET['cdb_year'] ) ? sanitize_text_field( $_GET['cdb_year'] ) : '';

        echo '<select name="equipo" class="postform">';
        echo '<option value="">' . esc_html__( 'Todos los equipos', 'cdb-empleado' ) . '</option>';
        foreach ( $equipos as $eq ) {
            printf( '<option value="%1$d" %2$s>%3$s</option>', $eq['ID'], selected( $current_team, $eq['ID'], false ), esc_html( $eq['post_title'] ) );
        }
        echo '</select>';

        $years = wp_list_pluck( $equipos, '_cdb_equipo_year' );
        $years = array_unique( array_filter( $years ) );
        sort( $years );

        echo '<select name="cdb_year" class="postform">';
        echo '<option value="">' . esc_html__( 'Todos los años', 'cdb-empleado' ) . '</option>';
        foreach ( $years as $year ) {
            printf( '<option value="%1$s" %2$s>%1$s</option>', esc_html( $year ), selected( $current_year, $year, false ) );
        }
        echo '</select>';
    }

    /**
     * Aplicar filtros personalizados a la consulta del listado de empleados.
     *
     * @param WP_Query $query Consulta principal.
     */
    public function aplicar_filtros_listado_empleados( $query ) {
        global $pagenow;
        $post_type = $_GET['post_type'] ?? '';

        if ( 'edit.php' !== $pagenow || 'empleado' !== $post_type || ! $query->is_main_query() ) {
            return;
        }

        $meta_query = array();

        if ( ! empty( $_GET['equipo'] ) ) {
            $meta_query[] = array(
                'key'   => '_cdb_empleado_equipo',
                'value' => absint( $_GET['equipo'] ),
            );
        }

        if ( ! empty( $_GET['cdb_year'] ) ) {
            $meta_query[] = array(
                'key'   => '_cdb_empleado_year',
                'value' => sanitize_text_field( $_GET['cdb_year'] ),
            );
        }

        if ( ! empty( $meta_query ) ) {
            if ( count( $meta_query ) > 1 ) {
                $meta_query['relation'] = 'AND';
            }
            $query->set( 'meta_query', $meta_query );
        }
    }

    /**
     * Añadir botones y enlaces en el listado de empleados.
     *
     * @param string $which Posición del tablenav.
     */
    public function acciones_listado_empleados( $which ) {
        global $typenow;
        if ( 'empleado' !== $typenow || 'top' !== $which ) {
            return;
        }

        $add_url = admin_url( 'post-new.php?post_type=empleado' );
        $doc_url = 'https://github.com/proyectocdb/cdb-empleado#readme';

        echo '<div class="cdb-listado-acciones">';
        echo '<a href="' . esc_url( $add_url ) . '" class="button button-primary">' . esc_html__( 'Añadir Empleado', 'cdb-empleado' ) . '</a> ';
        echo '<a href="' . esc_url( $doc_url ) . '" class="button" target="_blank">' . esc_html__( 'Documentación', 'cdb-empleado' ) . '</a>';
        echo '</div>';
    }

    /**
     * Activación del plugin - Refrescar los enlaces permanentes.
     */
    public static function activar() {
        self::registrar_cpt_empleado();
        if ( function_exists( 'cdb_empleado_registrar_rol' ) ) {
            cdb_empleado_registrar_rol();
        }
        flush_rewrite_rules();
    }

    /**
     * Desactivación del plugin - Limpiar reglas de reescritura.
     */
    public static function desactivar() {
        if ( function_exists( 'cdb_empleado_eliminar_rol' ) ) {
            cdb_empleado_eliminar_rol();
        }
        flush_rewrite_rules();
    }
}

// Registrar hooks de activación y desactivación.
register_activation_hook( __FILE__, array( 'Cdb_Empleado_Plugin', 'activar' ) );
register_deactivation_hook( __FILE__, array( 'Cdb_Empleado_Plugin', 'desactivar' ) );

// Instanciar el plugin.
new Cdb_Empleado_Plugin();

// Encolar estilos de la tarjeta octogonal solo cuando el flag esté activo.
add_action( 'wp_enqueue_scripts', function() {
  if ( apply_filters( 'cdb_empleado_use_new_card', false ) ) {
    wp_register_style( 'cdb-empleado-card-oct', plugins_url( 'assets/css/empleado-card-oct.css', __FILE__ ), [], '1.0' );
    wp_enqueue_style( 'cdb-empleado-card-oct' );

    $ink      = get_option( 'tarjeta_oct_ink', '#66604e' );
    $bg_start = get_option( 'tarjeta_oct_bg_start', '#f5e8c8' );
    $bg_end   = get_option( 'tarjeta_oct_bg_end', '#efe1b4' );

    $fonts    = function_exists( 'cdb_empleado_fuentes_disponibles' ) ? cdb_empleado_fuentes_disponibles() : array();
    $body_key = get_option( 'tarjeta_oct_font_body', 'sans' );
    $head_key = get_option( 'tarjeta_oct_font_heading', 'sans' );
    $body_ff  = $fonts[ $body_key ]['stack'] ?? $fonts['sans']['stack'] ?? 'ui-sans-serif,system-ui,-apple-system,"Helvetica Neue",Arial,sans-serif';
    $head_ff  = $fonts[ $head_key ]['stack'] ?? $body_ff;

    $css  = '.cdb-empcard8{--ink:' . $ink . ';background:linear-gradient(180deg,' . $bg_start . ',' . $bg_end . ');font-family:' . $body_ff . ';}';
    $css .= '.cdb-empcard8 .t{font-family:' . $head_ff . ';}';

    wp_add_inline_style( 'cdb-empleado-card-oct', $css );
  }
}, 20 );

