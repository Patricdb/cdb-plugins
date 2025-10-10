<?php
// Evitar acceso directo al archivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode: [cdb_eventos_inscritos]
 *
 * Muestra la lista de eventos en los que el usuario actual se ha inscrito, junto con una cuenta atrás
 * que indica los días y horas restantes hasta que comience el evento.
 * Permite, además, eliminar la inscripción.
 */
function cdb_eventos_inscritos_shortcode( $atts ) {
    if ( ! is_user_logged_in() ) {
        return cdb_eventos_get_mensaje( 'login_eventos_usuario' );
    }

    $user_id = get_current_user_id();
    $mensaje_accion = '';

    // 1) Procesar la eliminación de la inscripción
    if ( isset( $_POST['cdb_evento_unsub_nonce'] )
         && wp_verify_nonce( $_POST['cdb_evento_unsub_nonce'], 'cdb_evento_unsub' )
         && isset( $_POST['evento_id_to_remove'] ) ) {

        $evento_id_to_remove = intval( $_POST['evento_id_to_remove'] );

        // Cargamos la lista de inscripciones en el meta
        $inscripciones = get_post_meta( $evento_id_to_remove, '_cdb_eventos_inscripciones', true );
        if ( ! is_array( $inscripciones ) ) {
            $inscripciones = array();
        }

        // Buscamos y eliminamos el user_id si está suscrito
        $key = array_search( $user_id, $inscripciones );
        if ( $key !== false ) {
            unset( $inscripciones[$key] );
            update_post_meta( $evento_id_to_remove, '_cdb_eventos_inscripciones', $inscripciones );
            $mensaje_accion = '<p>' . esc_html__( 'Has eliminado tu inscripción del evento.', 'cdb-eventos' ) . '</p>';
        }
    }

    // 2) Consultar los eventos en los que está inscrito el usuario
    $args = array(
        'post_type'      => 'evento',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => '_cdb_eventos_fecha_hora',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_cdb_eventos_inscripciones',
                'value'   => '"' . $user_id . '"',
                'compare' => 'LIKE'
            )
        )
    );
    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return wp_kses_post( $mensaje_accion ) . cdb_eventos_get_mensaje( 'sin_eventos_usuario' );
    }

    // 3) Mostrar el listado y el botón para eliminar la inscripción
    ob_start();
    echo wp_kses_post( $mensaje_accion ); // Mensaje tras eliminar la inscripción
    echo '<div class="cdb-eventos-inscritos-lista">';

    while ( $query->have_posts() ) {
        $query->the_post();
        $evento_id    = get_the_ID();
        $evento_title = get_the_title();
        $evento_link  = get_permalink();
        $fecha_hora   = get_post_meta( $evento_id, '_cdb_eventos_fecha_hora', true );
        $countdown_text = '';

        if ( ! empty( $fecha_hora ) ) {
            $evento_datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', $fecha_hora, wp_timezone() );
            if ( $evento_datetime ) {
                $now = new DateTime( 'now', wp_timezone() );
                $interval = $now->diff( $evento_datetime );
                if ( $interval->invert ) {
                    $countdown_text = '<p class="countdown">' . esc_html__( 'El evento ya ha finalizado.', 'cdb-eventos' ) . '</p>';
                } else {
                    $days  = $interval->days;
                    $hours = $interval->h;
                    $countdown_text = '<p class="countdown">' . sprintf( esc_html__( 'Faltan %d días y %d horas para que comience el evento.', 'cdb-eventos' ), $days, $hours ) . '</p>';
                }
            }
        }

        echo '<div class="cdb-evento-inscrito-item" style="margin-bottom: 2rem;">';
            echo '<h3 class="evento-title"><span class="dashicons dashicons-calendar" style="margin-right: 5px;"></span> 
                  <a href="' . esc_url( $evento_link ) . '">' . esc_html( $evento_title ) . '</a></h3>';
            echo wp_kses_post( $countdown_text );

            // Agregamos el formulario para eliminar la inscripción
            echo '<form method="post" style="margin-top: 1rem;">';
                wp_nonce_field( 'cdb_evento_unsub', 'cdb_evento_unsub_nonce' );
                echo '<input type="hidden" name="evento_id_to_remove" value="' . esc_attr( $evento_id ) . '">';
                echo '<input type="submit" value="' . esc_attr__( 'Eliminar inscripción', 'cdb-eventos' ) . '" style="padding: 5px 10px; font-size: 0.8rem;">';
            echo '</form>';
        echo '</div>';
    }

    echo '</div>';
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'cdb_eventos_inscritos', 'cdb_eventos_inscritos_shortcode' );

