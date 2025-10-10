<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shortcode para listar eventos con diseño mejorado, cuenta regresiva y ordenados por fecha.
 *
 * Uso: [cdb_eventos_lista limit="10"]
 *
 * Muestra una lista de eventos próximos (ordenados de forma ascendente según la fecha) e inyecta una cuenta atrás
 * y un icono de calendario en el título.
 */
function cdb_eventos_lista_shortcode( $atts ) {
    // Definir atributos y valores por defecto.
    $atts = shortcode_atts( array(
        'limit' => 10,
    ), $atts, 'cdb_eventos_lista' );

    // Configurar la query para obtener eventos que aún no han ocurrido, ordenados por fecha.
    $args = array(
        'post_type'      => 'evento',
        'posts_per_page' => intval( $atts['limit'] ),
        'post_status'    => 'publish',
        'meta_key'       => '_cdb_eventos_fecha_hora',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_cdb_eventos_fecha_hora',
                'value'   => current_time( 'Y-m-d\TH:i' ),
                'compare' => '>=',
                'type'    => 'DATETIME'
            )
        )
    );
    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {
        echo '<div class="cdb-eventos-lista">';
        while ( $query->have_posts() ) {
            $query->the_post();
            // Obtener metadatos relevantes.
            $fecha_hora = get_post_meta( get_the_ID(), '_cdb_eventos_fecha_hora', true );
            $ubicacion  = get_post_meta( get_the_ID(), '_cdb_eventos_ubicacion', true );

            // Calcular la cuenta regresiva.
            $countdown_text = '';
            if ( ! empty( $fecha_hora ) ) {
                // Se asume que la fecha se guarda en el formato "Y-m-d\TH:i" (ej. 2025-03-25T14:30).
                $evento_datetime = DateTime::createFromFormat( 'Y-m-d\TH:i', $fecha_hora, wp_timezone() );
                if ( $evento_datetime ) {
                    $now = new DateTime( 'now', wp_timezone() );
                    $interval = $now->diff( $evento_datetime );
                    if ( $interval->invert ) {
                        $countdown_text = '<p class="countdown">' . esc_html( cdb_eventos_get_mensaje_text( 'evento_finalizado' ) ) . '</p>';
                    } else {
                        $days  = $interval->days;
                        $hours = $interval->h;
                        $countdown_text = '<p class="countdown">' . sprintf( esc_html__( 'Faltan %d días y %d horas para que comience el evento.', 'cdb-eventos' ), $days, $hours ) . '</p>';
                    }
                }
            }
            ?>
            <article class="cdb-evento-item card" style="margin-bottom: 2rem;">
                <header class="card-header">
                    <h2 class="card-title">
                        <span class="dashicons dashicons-calendar" style="margin-right: 5px;"></span>
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h2>
                </header>
                <div class="card-body">
                    <?php if ( $ubicacion ) : ?>
                        <p class="card-text"><strong><?php esc_html_e( 'Ubicación:', 'cdb-eventos' ); ?></strong> <?php echo esc_html( $ubicacion ); ?></p>
                    <?php endif; ?>
                    <?php echo wp_kses_post( $countdown_text ); ?>
                    <div class="cdb-evento-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                </div>
                <footer class="card-footer">
                    <a href="<?php the_permalink(); ?>" class="btn btn-primary"><?php esc_html_e( 'Ver más', 'cdb-eventos' ); ?></a>
                </footer>
            </article>
            <?php
        }
        echo '</div>';
    } else {
        echo wp_kses_post( cdb_eventos_get_mensaje( 'sin_eventos' ) );
    }
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'cdb_eventos_lista', 'cdb_eventos_lista_shortcode' );

/**
 * Registrar un bloque de Gutenberg para listar eventos.
 *
 * Este bloque es dinámico y se renderiza mediante la función callback.
 */
function cdb_eventos_register_block() {
    // Verificar que la función register_block_type exista (WordPress 5.0+)
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type( 'cdb/eventos-lista', array(
            'render_callback' => 'cdb_eventos_lista_block_render',
            'attributes'      => array(
                'limit' => array(
                    'type'    => 'number',
                    'default' => 10,
                ),
            ),
        ) );
    }
}
add_action( 'init', 'cdb_eventos_register_block' );

/**
 * Render callback para el bloque de eventos.
 *
 * @param array $attributes Atributos del bloque.
 * @return string HTML generado para el bloque.
 */
function cdb_eventos_lista_block_render( $attributes ) {
    $limit = isset( $attributes['limit'] ) ? intval( $attributes['limit'] ) : 10;

    // Configurar la query de eventos
    $args = array(
        'post_type'      => 'evento',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    );
    $query = new WP_Query( $args );

    ob_start();

    if ( $query->have_posts() ) {
        echo '<div class="cdb-eventos-lista">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $fecha_hora = get_post_meta( get_the_ID(), '_cdb_eventos_fecha_hora', true );
            $ubicacion  = get_post_meta( get_the_ID(), '_cdb_eventos_ubicacion', true );
            ?>
            <div class="cdb-evento-item">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <?php if ( $fecha_hora ) : ?>
                    <p><strong><?php esc_html_e( 'Fecha y hora:', 'cdb-eventos' ); ?></strong> <?php echo esc_html( $fecha_hora ); ?></p>
                <?php endif; ?>
                <?php if ( $ubicacion ) : ?>
                    <p><strong><?php esc_html_e( 'Ubicación:', 'cdb-eventos' ); ?></strong> <?php echo esc_html( $ubicacion ); ?></p>
                <?php endif; ?>
                <div class="cdb-evento-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo wp_kses_post( cdb_eventos_get_mensaje( 'sin_eventos' ) );
    }
    wp_reset_postdata();

    return ob_get_clean();
}
