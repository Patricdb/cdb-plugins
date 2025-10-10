<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode para mostrar el formulario de oferta de empleo.
 *
 * Este shortcode carga el template 'form-oferta-template.php' ubicado en la carpeta 'templates'.
 *
 * Uso: [cdb_form_oferta]
 */
function cdb_empleo_form_shortcode() {
    ob_start();
    include CDB_EMPLEO_PATH . 'templates/form-oferta-template.php';
    return ob_get_clean();
}
add_shortcode( 'cdb_form_oferta', 'cdb_empleo_form_shortcode' );

/**
 * Shortcode para mostrar el listado de ofertas de empleo.
 *
 * Este shortcode consulta el CPT "oferta_empleo" y muestra los resultados en un layout en grid.
 * Uso: [cdb_listado_ofertas posts_per_page="10"]
 */
function cdb_empleo_listado_shortcode( $atts ) {
    // Atributos por defecto
    $atts = shortcode_atts( array(
        'posts_per_page' => 10,
    ), $atts, 'cdb_listado_ofertas' );

    $query_args = array(
        'post_type'      => 'oferta_empleo',
        'posts_per_page' => intval( $atts['posts_per_page'] ),
        'post_status'    => 'publish',
    );

    $query = new WP_Query( $query_args );
    ob_start();

    if ( $query->have_posts() ) {
        echo '<div class="ofertas-grid">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $bar_id             = get_post_meta( get_the_ID(), 'cdb_bar', true );
            $posicion_id        = get_post_meta( get_the_ID(), 'cdb_posicion', true );
            $tipo_oferta        = get_post_meta( get_the_ID(), 'cdb_tipo_oferta', true );
            $fecha_incorporacion= get_post_meta( get_the_ID(), 'cdb_fecha_incorporacion', true );
            $fecha_fin          = get_post_meta( get_the_ID(), 'cdb_fecha_fin', true );

            // Formatear las fechas con hora en formato 24h
            $fecha_incorporacion_formatted = $fecha_incorporacion
                ? date_i18n( 'H:i \d\e\l l d \d\e F \d\e Y', strtotime( $fecha_incorporacion ) )
                : '';
            $fecha_fin_formatted = $fecha_fin
                ? date_i18n( 'H:i \d\e\l l d \d\e F \d\e Y', strtotime( $fecha_fin ) )
                : '';
            ?>
            <div class="oferta-card">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="oferta-thumbnail">
                        <?php the_post_thumbnail( 'medium' ); ?>
                    </div>
                <?php endif; ?>
                <h2 class="oferta-title"><?php the_title(); ?></h2>
                <div class="oferta-meta">
                    <p><strong>Bar:</strong> <?php echo $bar_id ? esc_html( get_the_title( $bar_id ) ) : 'No asignado'; ?></p>
                    <p><strong>Posición:</strong> <?php echo $posicion_id ? esc_html( get_the_title( $posicion_id ) ) : 'No asignado'; ?></p>
                    <p><strong>Tipo:</strong> <?php echo esc_html( $tipo_oferta ); ?></p>
                    <p><strong>Incorporación:</strong> <?php echo esc_html( $fecha_incorporacion_formatted ); ?></p>
                    <p><strong>Fin:</strong> <?php echo esc_html( $fecha_fin_formatted ); ?></p>
                </div>
                <div class="oferta-excerpt">
                    <?php the_excerpt(); ?>
                </div>
                <?php echo do_shortcode( '[cdb_oferta_suscripcion oferta_id="' . get_the_ID() . '"]' ); ?>
                </div>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
    } else {
        echo cdb_empleo_get_mensaje( 'sin_ofertas' );
    }

    return ob_get_clean();
}
add_shortcode( 'cdb_listado_ofertas', 'cdb_empleo_listado_shortcode' );

/**
 * Shortcode para suscribirse a una oferta de empleo.
 *
 * Uso: [cdb_oferta_suscripcion oferta_id="123"]
 */
function cdb_oferta_suscripcion_shortcode( $atts ) {
    // Atributos por defecto
    $atts = shortcode_atts( array(
        'oferta_id' => 0,
    ), $atts, 'cdb_oferta_suscripcion' );

    $oferta_id = intval( $atts['oferta_id'] );
    if ( ! $oferta_id ) {
        // Si no se pasa un ID de oferta, no hacemos nada.
        return '';
    }

    // Verificar si el usuario está logueado.
    if ( ! is_user_logged_in() ) {
        return cdb_empleo_get_mensaje( 'login_requerido' );
    }

    $user_id = get_current_user_id();
    $suscripciones = get_post_meta( $oferta_id, '_cdb_oferta_inscripciones', true );
    if ( ! is_array( $suscripciones ) ) {
        $suscripciones = array();
    }

    // Verificar si el usuario ya está suscrito.
    if ( in_array( $user_id, $suscripciones ) ) {
        return cdb_empleo_get_mensaje( 'ya_suscrito' );
    }

    // Procesar la suscripción al recibir el formulario
    if ( isset( $_POST['cdb_oferta_suscripcion_nonce'] )
         && wp_verify_nonce( $_POST['cdb_oferta_suscripcion_nonce'], 'cdb_oferta_suscripcion_' . $oferta_id ) ) {

        // Agregar el ID del usuario a la lista de suscripciones.
        $suscripciones[] = $user_id;
        update_post_meta( $oferta_id, '_cdb_oferta_inscripciones', $suscripciones );

        return cdb_empleo_get_mensaje( 'suscripcion_ok' );
    }

    // Mostrar el formulario de suscripción
    ob_start();
    ?>
    <form method="post">
        <?php wp_nonce_field( 'cdb_oferta_suscripcion_' . $oferta_id, 'cdb_oferta_suscripcion_nonce' ); ?>
        <input type="submit" value="Suscribirse a la oferta">
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cdb_oferta_suscripcion', 'cdb_oferta_suscripcion_shortcode' );

/**
 * Agregar un meta box en el administrador para visualizar las suscripciones a las ofertas.
 */
function cdb_ofertas_inscripciones_meta_box() {
    add_meta_box(
        'cdb_ofertas_inscripciones',
        __( 'Suscripciones a la Oferta', 'cdb-ofertas' ),
        'cdb_ofertas_inscripciones_meta_box_callback',
        'oferta_empleo', // <-- Aquí
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'cdb_ofertas_inscripciones_meta_box' );

/**
 * Callback para renderizar el meta box de suscripciones a ofertas.
 *
 * @param WP_Post $post Objeto del post actual (oferta).
 */
function cdb_ofertas_inscripciones_meta_box_callback( $post ) {
    // Recuperamos las suscripciones guardadas en la meta key '_cdb_oferta_inscripciones'
    $suscripciones = get_post_meta( $post->ID, '_cdb_oferta_inscripciones', true );

    // Si no es un array o está vacío, mostramos un mensaje
    if ( ! is_array( $suscripciones ) || empty( $suscripciones ) ) {
        echo cdb_empleo_get_mensaje( 'sin_suscripciones' );
        return;
    }

    // Listamos la información de cada usuario suscrito
    echo '<ul>';
    foreach ( $suscripciones as $user_id ) {
        $user_info = get_userdata( $user_id );
        if ( $user_info ) {
            echo '<li>' . esc_html( $user_info->display_name ) . ' (' . esc_html( $user_info->user_email ) . ')</li>';
        }
    }
    echo '</ul>';
}

/**
 * Shortcode: [cdb_empleo_suscritos]
 *
 * Muestra la lista de ofertas de empleo en las que el usuario actual se ha suscrito, 
 * junto con una cuenta atrás que indica los días y horas restantes hasta el inicio de la oferta.
 * Permite eliminar su suscripción desde un botón "Eliminar".
 */
function cdb_empleo_suscritos_shortcode( $atts ) {
    // Verificar que el usuario esté logueado
    if ( ! is_user_logged_in() ) {
        return cdb_empleo_get_mensaje( 'login_requerido' );
    }

    $user_id = get_current_user_id();
    $mensaje_accion = '';

    // 1) Procesar la eliminación de suscripción si se envió el formulario
    if ( isset( $_POST['cdb_empleo_unsub_nonce'] )
         && wp_verify_nonce( $_POST['cdb_empleo_unsub_nonce'], 'cdb_empleo_unsub' )
         && isset( $_POST['oferta_id_to_remove'] ) ) {

        $oferta_id_to_remove = intval( $_POST['oferta_id_to_remove'] );

        $suscripciones = get_post_meta( $oferta_id_to_remove, '_cdb_oferta_inscripciones', true );
        if ( ! is_array( $suscripciones ) ) {
            $suscripciones = array();
        }

        // Eliminar el ID del usuario del array, si existe
        $key = array_search( $user_id, $suscripciones );
        if ( $key !== false ) {
            unset( $suscripciones[$key] );
            update_post_meta( $oferta_id_to_remove, '_cdb_oferta_inscripciones', $suscripciones );
            $mensaje_accion = cdb_empleo_get_mensaje( 'suscripcion_eliminada' );
        }
    }

    // 2) Realizar la consulta de ofertas a las que el usuario está suscrito
    $args = array(
        'post_type'      => 'oferta_empleo',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'cdb_fecha_incorporacion',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_cdb_oferta_inscripciones',
                'value'   => $user_id,
                'compare' => 'LIKE'
            )
        )
    );
    $query = new WP_Query( $args );

    // 3) Si no hay ofertas, avisar
    if ( ! $query->have_posts() ) {
        return $mensaje_accion . cdb_empleo_get_mensaje( 'no_suscrito' );
    }

    // 4) Mostrar la lista de ofertas con posibilidad de eliminar la suscripción
    ob_start();
    echo $mensaje_accion; // Mensaje de éxito al eliminar
    echo '<div class="cdb-empleo-suscritos-lista">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $oferta_id    = get_the_ID();
        $oferta_title = get_the_title();
        $oferta_link  = get_permalink();
        $fecha_incorporacion = get_post_meta( $oferta_id, 'cdb_fecha_incorporacion', true );

        // Calcular la cuenta atrás
        $countdown_text = '';
        if ( ! empty( $fecha_incorporacion ) ) {
            $oferta_datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', $fecha_incorporacion, wp_timezone() );
            if ( $oferta_datetime ) {
                $now = new DateTime( 'now', wp_timezone() );
                $interval = $now->diff( $oferta_datetime );
                if ( $interval->invert ) {
                    $countdown_text = '<p class="countdown">La oferta ya ha comenzado.</p>';
                } else {
                    $days  = $interval->days;
                    $hours = $interval->h;
                    $countdown_text = sprintf(
                        '<p class="countdown">Faltan %d días y %d horas para el inicio de la oferta.</p>',
                        $days,
                        $hours
                    );
                }
            }
        }

        echo '<div class="cdb-empleo-suscrito-item" style="margin-bottom: 2rem;">';
            echo '<h3 class="oferta-title"><span class="dashicons dashicons-clipboard" style="margin-right: 5px;"></span> 
                  <a href="' . esc_url( $oferta_link ) . '">' . esc_html( $oferta_title ) . '</a></h3>';
            echo $countdown_text;

            // Formulario para eliminar suscripción
            echo '<form method="post" style="margin-top: 1rem;">';
                wp_nonce_field( 'cdb_empleo_unsub', 'cdb_empleo_unsub_nonce' );
                echo '<input type="hidden" name="oferta_id_to_remove" value="' . esc_attr( $oferta_id ) . '">';
                echo '<input type="submit" value="Eliminar suscripción" style="padding: 5px 10px; font-size: 0.8rem;">';
            echo '</form>';

        echo '</div>';
    }
    echo '</div>';

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'cdb_empleo_suscritos', 'cdb_empleo_suscritos_shortcode' );

