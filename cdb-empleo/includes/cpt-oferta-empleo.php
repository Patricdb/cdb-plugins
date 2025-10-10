<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo
}

function cdb_registrar_cpt_oferta_empleo() {

    $labels = array(
        'name'                  => _x( 'Ofertas de Empleo', 'Post Type General Name', 'cdb-empleo' ),
        'singular_name'         => _x( 'Oferta de Empleo', 'Post Type Singular Name', 'cdb-empleo' ),
        'menu_name'             => __( 'Ofertas de Empleo', 'cdb-empleo' ),
        'name_admin_bar'        => __( 'Oferta de Empleo', 'cdb-empleo' ),
        'archives'              => __( 'Archivo de Ofertas', 'cdb-empleo' ),
        'attributes'            => __( 'Atributos de la Oferta', 'cdb-empleo' ),
        'parent_item_colon'     => __( 'Oferta Superior:', 'cdb-empleo' ),
        'all_items'             => __( 'Todas las Ofertas', 'cdb-empleo' ),
        'add_new_item'          => __( 'Añadir Nueva Oferta', 'cdb-empleo' ),
        'add_new'               => __( 'Añadir Nueva', 'cdb-empleo' ),
        'new_item'              => __( 'Nueva Oferta', 'cdb-empleo' ),
        'edit_item'             => __( 'Editar Oferta', 'cdb-empleo' ),
        'update_item'           => __( 'Actualizar Oferta', 'cdb-empleo' ),
        'view_item'             => __( 'Ver Oferta', 'cdb-empleo' ),
        'view_items'            => __( 'Ver Ofertas', 'cdb-empleo' ),
        'search_items'          => __( 'Buscar Oferta', 'cdb-empleo' ),
        'not_found'             => __( 'No se encontró ninguna oferta', 'cdb-empleo' ),
        'not_found_in_trash'    => __( 'No se encontró ninguna oferta en la papelera', 'cdb-empleo' ),
        'featured_image'        => __( 'Imagen destacada', 'cdb-empleo' ),
        'set_featured_image'    => __( 'Establecer imagen destacada', 'cdb-empleo' ),
        'remove_featured_image' => __( 'Eliminar imagen destacada', 'cdb-empleo' ),
        'use_featured_image'    => __( 'Usar como imagen destacada', 'cdb-empleo' ),
        'insert_into_item'      => __( 'Insertar en la oferta', 'cdb-empleo' ),
        'uploaded_to_this_item' => __( 'Subido a esta oferta', 'cdb-empleo' ),
        'items_list'            => __( 'Lista de ofertas', 'cdb-empleo' ),
        'items_list_navigation' => __( 'Navegación de la lista de ofertas', 'cdb-empleo' ),
        'filter_items_list'     => __( 'Filtrar lista de ofertas', 'cdb-empleo' ),
    );

    $args = array(
        'label'                 => __( 'Oferta de Empleo', 'cdb-empleo' ),
        'description'           => __( 'Ofertas de Empleo para la plataforma CdB', 'cdb-empleo' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'taxonomies'            => array( 'category', 'post_tag' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-businessman',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Habilita el editor de bloques (Gutenberg)
    );

    register_post_type( 'oferta_empleo', $args );
}
add_action( 'init', 'cdb_registrar_cpt_oferta_empleo', 0 );
