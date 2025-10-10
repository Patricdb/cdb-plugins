<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Agrega el rol "Empleador" si aún no existe, asignándole capacidades básicas y
 * capacidades específicas para gestionar ofertas de empleo.
 */
function cdb_empleo_agregar_rol_empleador() {
    if ( ! get_role( 'empleador' ) ) {
        add_role(
            'empleador',
            __( 'Empleador', 'cdb-empleo' ),
            array(
                'read'                 => true,
                'edit_posts'           => true,
                'delete_posts'         => true,
                'publish_posts'        => true,
                'upload_files'         => true,
                // Capacidades personalizadas para el CPT "oferta_empleo"
                'create_oferta_empleo' => true,
                'edit_oferta_empleo'   => true,
                'delete_oferta_empleo' => true,
                'publish_oferta_empleo'=> true,
            )
        );
    }
}
add_action( 'init', 'cdb_empleo_agregar_rol_empleador' );

/**
 * Agrega las capacidades personalizadas para el CPT "oferta_empleo" al rol "administrator".
 */
function cdb_empleo_agregar_caps_administrator() {
    $role = get_role( 'administrator' );
    if ( $role ) {
        $caps = array(
            'create_oferta_empleo',
            'edit_oferta_empleo',
            'delete_oferta_empleo',
            'publish_oferta_empleo',
        );
        foreach ( $caps as $cap ) {
            $role->add_cap( $cap );
        }
    }
}
add_action( 'init', 'cdb_empleo_agregar_caps_administrator' );