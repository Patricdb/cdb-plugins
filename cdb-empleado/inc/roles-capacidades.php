<?php
/**
 * Definición del rol personalizado "empleado".
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registrar el rol personalizado "empleado".
 *
 * Las capacidades extra se obtienen del ajuste "cdb_empleado_extra_caps".
 */
function cdb_empleado_registrar_rol() {
    $extras = (array) get_option( 'cdb_empleado_extra_caps', array() );

    $caps = array( 'read' => true );
    foreach ( $extras as $cap ) {
        $caps[ $cap ] = true;
    }

    add_role( 'empleado', 'Empleado', $caps );
}

/**
 * Eliminar el rol personalizado "empleado".
 */
function cdb_empleado_eliminar_rol() {
    remove_role( 'empleado' );
}
