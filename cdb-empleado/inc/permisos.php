<?php
// Asegurar que el archivo no se acceda directamente
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Hacer que todos los usuarios con permisos adecuados aparezcan en el selector de autores.
 */
function cdb_mostrar_todos_los_autores( $query_args, $r ) {
    $query_args['who'] = ''; // Elimina la restricción de solo autores

    $roles = (array) get_option( 'cdb_empleado_selector_roles', array( 'administrator', 'editor', 'author', 'empleado' ) );
    if ( ! empty( $roles ) ) {
        $query_args['role__in'] = $roles;
    }

    return $query_args;
}
add_filter( 'wp_dropdown_users_args', 'cdb_mostrar_todos_los_autores', 10, 2 );
