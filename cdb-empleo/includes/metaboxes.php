<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo
}

/**
 * Registra el metabox para la información de la oferta.
 */
function cdb_agregar_metaboxes() {
    add_meta_box(
        'cdb_info_oferta',                      // ID del metabox
        __( 'Información de la Oferta', 'cdb-empleo' ), // Título
        'cdb_mostrar_metabox_info_oferta',      // Callback que muestra el contenido
        'oferta_empleo',                        // Tipo de contenido (CPT)
        'normal',                               // Contexto
        'default'                               // Prioridad
    );
}
add_action( 'add_meta_boxes', 'cdb_agregar_metaboxes' );

/**
 * Muestra el contenido del metabox con los campos personalizados.
 *
 * @param WP_Post $post Objeto del post actual.
 */
function cdb_mostrar_metabox_info_oferta( $post ) {
    // Agregar nonce para seguridad
    wp_nonce_field( 'cdb_metabox_nonce_action', 'cdb_metabox_nonce' );
    
    // Recuperar valores guardados (si existen)
    $cdb_bar                = get_post_meta( $post->ID, 'cdb_bar', true );
    $cdb_posicion           = get_post_meta( $post->ID, 'cdb_posicion', true );
    $cdb_tipo_oferta        = get_post_meta( $post->ID, 'cdb_tipo_oferta', true );
    $cdb_fecha_incorporacion= get_post_meta( $post->ID, 'cdb_fecha_incorporacion', true );
    $cdb_fecha_fin          = get_post_meta( $post->ID, 'cdb_fecha_fin', true );
    $cdb_nivel_salarial     = get_post_meta( $post->ID, 'cdb_nivel_salarial', true );
    $cdb_funciones          = get_post_meta( $post->ID, 'cdb_funciones', true );
    
    // Obtener los bares publicados (se asume CPT "bar")
    $bares = get_posts( array(
        'post_type'      => 'bar',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ) );
    
    // Obtener las posiciones publicadas (se asume CPT "posicion")
    $posiciones = get_posts( array(
        'post_type'      => 'cdb_posiciones',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_cdb_posiciones_score',
        'orderby'        => 'meta_value_num',
        'order'          => 'ASC'
    ) );

    ?>
    <p>
        <label for="cdb_bar"><?php _e( 'Bar que lo oferta:', 'cdb-empleo' ); ?></label><br />
        <select name="cdb_bar" id="cdb_bar">
            <option value=""><?php _e( 'Selecciona un bar', 'cdb-empleo' ); ?></option>
            <?php
            if ( $bares ) {
                foreach ( $bares as $bar ) {
                    echo '<option value="' . esc_attr( $bar->ID ) . '" ' . selected( $cdb_bar, $bar->ID, false ) . '>' . esc_html( get_the_title( $bar->ID ) ) . '</option>';
                }
            }
            ?>
        </select>
    </p>
    <p>
        <label for="cdb_posicion"><?php _e( 'Posición a ofertar:', 'cdb-empleo' ); ?></label><br />
        <select name="cdb_posicion" id="cdb_posicion">
            <option value=""><?php _e( 'Selecciona una posición', 'cdb-empleo' ); ?></option>
            <?php
            if ( $posiciones ) {
                foreach ( $posiciones as $posicion ) {
                    echo '<option value="' . esc_attr( $posicion->ID ) . '" ' . selected( $cdb_posicion, $posicion->ID, false ) . '>' . esc_html( get_the_title( $posicion->ID ) ) . '</option>';
                }
            }
            ?>
        </select>
    </p>
    <p>
        <label for="cdb_tipo_oferta"><?php _e( 'Tipo de oferta:', 'cdb-empleo' ); ?></label><br />
        <select name="cdb_tipo_oferta" id="cdb_tipo_oferta">
            <option value=""><?php _e( 'Selecciona el tipo de oferta', 'cdb-empleo' ); ?></option>
            <?php
            $tipos = array(
                'Extra'                   => __( 'Extra', 'cdb-empleo' ),
                'Cuadrilla de eventos'    => __( 'Cuadrilla de eventos', 'cdb-empleo' ),
                'Jornada completa'        => __( 'Jornada completa', 'cdb-empleo' ),
                'Media jornada'           => __( 'Media jornada', 'cdb-empleo' ),
            );
            foreach ( $tipos as $key => $label ) {
                echo '<option value="' . esc_attr( $key ) . '" ' . selected( $cdb_tipo_oferta, $key, false ) . '>' . esc_html( $label ) . '</option>';
            }
            ?>
        </select>
    </p>
    <p>
        <label for="cdb_fecha_incorporacion"><?php _e( 'Fecha y hora de incorporación:', 'cdb-empleo' ); ?></label><br />
        <input type="datetime-local" name="cdb_fecha_incorporacion" id="cdb_fecha_incorporacion" value="<?php echo esc_attr( $cdb_fecha_incorporacion ); ?>" />
    </p>
    <p>
        <label for="cdb_fecha_fin"><?php _e( 'Fecha y hora de fin:', 'cdb-empleo' ); ?></label><br />
        <input type="datetime-local" name="cdb_fecha_fin" id="cdb_fecha_fin" value="<?php echo esc_attr( $cdb_fecha_fin ); ?>" />
    </p>
    <p>
        <label for="cdb_nivel_salarial"><?php _e( 'Nivel salarial (0 a 4):', 'cdb-empleo' ); ?></label><br />
        <select name="cdb_nivel_salarial" id="cdb_nivel_salarial">
            <option value=""><?php _e( 'Selecciona el nivel salarial', 'cdb-empleo' ); ?></option>
            <?php
            for ( $i = 0; $i <= 4; $i++ ) {
                echo '<option value="' . $i . '" ' . selected( $cdb_nivel_salarial, $i, false ) . '>' . $i . '</option>';
            }
            ?>
        </select>
    </p>
    <p>
        <label for="cdb_funciones"><?php _e( 'Funciones a realizar:', 'cdb-empleo' ); ?></label><br />
        <textarea name="cdb_funciones" id="cdb_funciones" rows="4" cols="50"><?php echo esc_textarea( $cdb_funciones ); ?></textarea>
    </p>
    <?php
}

/**
 * Guarda los datos de los metaboxes al guardar el post.
 *
 * @param int $post_id ID del post actual.
 */
function cdb_guardar_metaboxes( $post_id ) {

    // Verificar el nonce
    if ( ! isset( $_POST['cdb_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['cdb_metabox_nonce'], 'cdb_metabox_nonce_action' ) ) {
        return $post_id;
    }

    // Evitar guardado en autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Comprobar permisos del usuario
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }

    // Guardar cada campo si está definido
    if ( isset( $_POST['cdb_bar'] ) ) {
        update_post_meta( $post_id, 'cdb_bar', sanitize_text_field( $_POST['cdb_bar'] ) );
    }
    if ( isset( $_POST['cdb_posicion'] ) ) {
        update_post_meta( $post_id, 'cdb_posicion', sanitize_text_field( $_POST['cdb_posicion'] ) );
    }
    if ( isset( $_POST['cdb_tipo_oferta'] ) ) {
        update_post_meta( $post_id, 'cdb_tipo_oferta', sanitize_text_field( $_POST['cdb_tipo_oferta'] ) );
    }
    if ( isset( $_POST['cdb_fecha_incorporacion'] ) ) {
        update_post_meta( $post_id, 'cdb_fecha_incorporacion', sanitize_text_field( $_POST['cdb_fecha_incorporacion'] ) );
    }
    if ( isset( $_POST['cdb_fecha_fin'] ) ) {
        update_post_meta( $post_id, 'cdb_fecha_fin', sanitize_text_field( $_POST['cdb_fecha_fin'] ) );
    }
    if ( isset( $_POST['cdb_nivel_salarial'] ) ) {
        update_post_meta( $post_id, 'cdb_nivel_salarial', sanitize_text_field( $_POST['cdb_nivel_salarial'] ) );
    }
    if ( isset( $_POST['cdb_funciones'] ) ) {
        update_post_meta( $post_id, 'cdb_funciones', sanitize_textarea_field( $_POST['cdb_funciones'] ) );
    }
}
add_action( 'save_post', 'cdb_guardar_metaboxes' );
