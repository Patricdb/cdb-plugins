<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handler AJAX para guardar una oferta de empleo desde el frontend.
 */
function cdb_guardar_oferta_callback() {

    // Verificar el nonce de seguridad.
    check_ajax_referer( 'cdb_form_nonce', 'security' );

   // Verificar que el usuario esté conectado y tenga permisos para crear ofertas
    $current_user = wp_get_current_user();
    if ( ! $current_user->exists() || ! current_user_can( 'create_oferta_empleo' ) ) {
        wp_send_json_error( array( 'message' => cdb_empleo_get_mensaje_text( 'sin_permiso_form' ) ) );
    }


    // Recoger y sanitizar los datos del formulario.
    $bar_id             = isset( $_POST['bar_id'] ) ? intval( $_POST['bar_id'] ) : 0;
    $posicion_id        = isset( $_POST['posicion_id'] ) ? intval( $_POST['posicion_id'] ) : 0;
    $tipo_oferta        = isset( $_POST['tipo_oferta'] ) ? sanitize_text_field( $_POST['tipo_oferta'] ) : '';
    $fecha_incorporacion= isset( $_POST['fecha_incorporacion'] ) ? sanitize_text_field( $_POST['fecha_incorporacion'] ) : '';
    $fecha_fin          = isset( $_POST['fecha_fin'] ) ? sanitize_text_field( $_POST['fecha_fin'] ) : '';
    $nivel_salarial     = isset( $_POST['nivel_salarial'] ) ? sanitize_text_field( $_POST['nivel_salarial'] ) : '';
    $funciones          = isset( $_POST['funciones'] ) ? sanitize_textarea_field( $_POST['funciones'] ) : '';

    // Validar que se hayan completado todos los campos requeridos.
    if ( empty( $bar_id ) || empty( $posicion_id ) || empty( $tipo_oferta ) || empty( $fecha_incorporacion ) || empty( $fecha_fin ) || $nivel_salarial === '' || empty( $funciones ) ) {
        wp_send_json_error( array( 'message' => cdb_empleo_get_mensaje_text( 'campos_requeridos' ) ) );
    }

    // Verificar coherencia de las fechas
    $inicio_ts = strtotime( $fecha_incorporacion );
    $fin_ts    = strtotime( $fecha_fin );
    if ( false !== $inicio_ts && false !== $fin_ts && $inicio_ts >= $fin_ts ) {
        wp_send_json_error( array( 'message' => cdb_empleo_get_mensaje_text( 'fecha_invalida' ) ) );
    }

    // Crear un título para la oferta (por ejemplo, combinando el nombre del bar y el tipo de oferta).
    $bar_title = get_the_title( $bar_id );
    $post_title = $bar_title . ' - ' . $tipo_oferta;

    // Insertar la nueva oferta de empleo.
    $post_data = array(
        'post_title'   => $post_title,
        'post_content' => $funciones,  // Se puede utilizar el contenido para detallar las funciones.
        'post_status'  => 'publish',
        'post_type'    => 'oferta_empleo',
        'post_author'  => $current_user->ID,
    );

    $post_id = wp_insert_post( $post_data );

    if ( is_wp_error( $post_id ) ) {
        wp_send_json_error( array( 'message' => cdb_empleo_get_mensaje_text( 'error_crear_oferta' ) ) );
    }

    // Guardar los campos personalizados como meta.
    update_post_meta( $post_id, 'cdb_bar', $bar_id );
    update_post_meta( $post_id, 'cdb_posicion', $posicion_id );
    update_post_meta( $post_id, 'cdb_tipo_oferta', $tipo_oferta );
    update_post_meta( $post_id, 'cdb_fecha_incorporacion', $fecha_incorporacion );
    update_post_meta( $post_id, 'cdb_fecha_fin', $fecha_fin );
    update_post_meta( $post_id, 'cdb_nivel_salarial', $nivel_salarial );
    update_post_meta( $post_id, 'cdb_funciones', $funciones );

    wp_send_json_success( array(
        'message' => cdb_empleo_get_mensaje_text( 'oferta_registrada' ),
        'post_id' => $post_id,
        'reload'  => true, // Indica si se debe recargar la página
    ) );
}
add_action( 'wp_ajax_cdb_guardar_oferta', 'cdb_guardar_oferta_callback' );
// No se añade el hook para usuarios no autenticados, ya que el formulario es exclusivo para "Empleador".
