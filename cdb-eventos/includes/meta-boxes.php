<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Agrega el meta box para los detalles del evento.
 */
add_action( 'add_meta_boxes', 'cdb_eventos_add_meta_boxes' );
function cdb_eventos_add_meta_boxes() {
    add_meta_box(
        'cdb_eventos_details',                   // ID del meta box
        __( 'Detalles del Evento', 'cdb-eventos' ), // Título del meta box
        'cdb_eventos_render_meta_box',           // Función que renderiza el contenido
        'evento',                                // Tipo de post donde se mostrará
        'normal', 
        'default'
    );
}

/**
 * Renderiza el contenido del meta box en el editor de eventos.
 *
 * @param WP_Post $post El objeto del post actual.
 */
function cdb_eventos_render_meta_box( $post ) {
    // Crear un nonce para seguridad
    wp_nonce_field( 'cdb_eventos_save_meta_box_data', 'cdb_eventos_meta_box_nonce' );

    // Obtener valores guardados (si existen)
    $fecha_hora  = get_post_meta( $post->ID, '_cdb_eventos_fecha_hora', true );
    $ubicacion   = get_post_meta( $post->ID, '_cdb_eventos_ubicacion', true );
    $tipo_evento = get_post_meta( $post->ID, '_cdb_eventos_tipo_evento', true );
    $capacidad   = get_post_meta( $post->ID, '_cdb_eventos_capacidad', true );

    // Opciones para el tipo de evento.
    $tipos = array(
        'formacion'    => __( 'Formación', 'cdb-eventos' ),
        'encuentro'    => __( 'Encuentro', 'cdb-eventos' ),
        'cata'         => __( 'Cata', 'cdb-eventos' ),
        'presentacion' => __( 'Presentación', 'cdb-eventos' ),
        'taller'       => __( 'Taller', 'cdb-eventos' ),
    );
    ?>
    <p>
        <label for="cdb_eventos_fecha_hora"><?php _e( 'Fecha y hora:', 'cdb-eventos' ); ?></label><br>
        <input type="datetime-local" id="cdb_eventos_fecha_hora" name="cdb_eventos_fecha_hora" value="<?php echo esc_attr( $fecha_hora ); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="cdb_eventos_ubicacion"><?php _e( 'Ubicación:', 'cdb-eventos' ); ?></label><br>
        <input type="text" id="cdb_eventos_ubicacion" name="cdb_eventos_ubicacion" value="<?php echo esc_attr( $ubicacion ); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="cdb_eventos_tipo_evento"><?php _e( 'Tipo de evento:', 'cdb-eventos' ); ?></label><br>
        <select id="cdb_eventos_tipo_evento" name="cdb_eventos_tipo_evento" style="width:100%;">
            <option value=""><?php _e( 'Seleccione un tipo', 'cdb-eventos' ); ?></option>
            <?php foreach ( $tipos as $key => $label ) : ?>
                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $tipo_evento, $key ); ?>>
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="cdb_eventos_capacidad"><?php _e( 'Capacidad máxima:', 'cdb-eventos' ); ?></label><br>
        <input type="number" id="cdb_eventos_capacidad" name="cdb_eventos_capacidad" value="<?php echo esc_attr( $capacidad ); ?>" style="width:100%;" />
    </p>
    <?php
}

/**
 * Guarda los metadatos del meta box cuando se guarda el post.
 *
 * @param int $post_id ID del post actual.
 */
add_action( 'save_post', 'cdb_eventos_save_meta_box_data' );
function cdb_eventos_save_meta_box_data( $post_id ) {
    // Verificar el nonce.
    if ( ! isset( $_POST['cdb_eventos_meta_box_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['cdb_eventos_meta_box_nonce'], 'cdb_eventos_save_meta_box_data' ) ) {
        return;
    }

    // Evitar guardar durante un autosave.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Verificar permisos de usuario.
    if ( isset( $_POST['post_type'] ) && 'evento' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Guardar el campo "Fecha y hora"
    if ( isset( $_POST['cdb_eventos_fecha_hora'] ) ) {
        $fecha_hora = sanitize_text_field( $_POST['cdb_eventos_fecha_hora'] );
        update_post_meta( $post_id, '_cdb_eventos_fecha_hora', $fecha_hora );
    }

    // Guardar el campo "Ubicación"
    if ( isset( $_POST['cdb_eventos_ubicacion'] ) ) {
        $ubicacion = sanitize_text_field( $_POST['cdb_eventos_ubicacion'] );
        update_post_meta( $post_id, '_cdb_eventos_ubicacion', $ubicacion );
    }

    // Guardar el campo "Tipo de evento"
    if ( isset( $_POST['cdb_eventos_tipo_evento'] ) ) {
        $tipo_evento = sanitize_text_field( $_POST['cdb_eventos_tipo_evento'] );
        update_post_meta( $post_id, '_cdb_eventos_tipo_evento', $tipo_evento );
    }

    // Guardar el campo "Capacidad máxima"
    if ( isset( $_POST['cdb_eventos_capacidad'] ) ) {
        $capacidad = intval( $_POST['cdb_eventos_capacidad'] );
        update_post_meta( $post_id, '_cdb_eventos_capacidad', $capacidad );
    }
}
