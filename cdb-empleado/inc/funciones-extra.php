<?php
/**
 * Shortcode: [equipos_del_empleado empleado_id="123"]
 * Muestra los equipos a los que pertenece un Empleado, con enlace a cada Posición.
 */
function cdb_equipos_del_empleado_shortcode($atts) {
    // Atributos por defecto
    $atts = shortcode_atts(array(
        'empleado_id' => 0
    ), $atts, 'equipos_del_empleado');

    $empleado_id = (int) $atts['empleado_id'];

    // Si no se especifica empleado_id, usamos el ID del post actual
    if (!$empleado_id) {
        $empleado_id = get_the_ID();
    }

    // Verificamos que sea realmente un CPT "empleado"
    if (get_post_type($empleado_id) !== 'empleado') {
        return '<p>No se ha especificado un empleado válido para mostrar sus equipos.</p>';
    }

    // Obtenemos el nombre del Empleado
    $empleado_nombre = get_the_title($empleado_id);

    global $wpdb;
    $tabla_exp = $wpdb->prefix . 'cdb_experiencia';

    // 1. Obtener IDs de equipos donde participa este empleado
    $equipos_ids = $wpdb->get_col(
        $wpdb->prepare("
            SELECT DISTINCT equipo_id
            FROM $tabla_exp
            WHERE empleado_id = %d
        ", $empleado_id)
    );

    // Si no hay equipos, mensaje
    if (empty($equipos_ids)) {
        return '<p>' . esc_html($empleado_nombre) . ' no forma parte de ningún equipo.</p>';
    }

    // 2. Traer los posts del CPT "equipo"
    $equipos = get_posts(array(
        'post_type'               => 'equipo',
        'posts_per_page'          => -1,
        'post_status'             => 'publish',
        'post__in'                => $equipos_ids,
        'orderby'                 => 'meta_value_num',
        'meta_key'                => '_cdb_equipo_year',
        'order'                   => 'DESC',
        'cache_results'           => false,
        'update_post_meta_cache'  => false,
        'update_post_term_cache'  => false,
    ));

    if (empty($equipos)) {
        return '<p>' . esc_html($empleado_nombre) . ' no forma parte de ningún equipo publicado.</p>';
    }

    // 3. Renderizamos
    ob_start();
    ?>
    <div class="equipos-del-empleado">
        <h3>Equipos de <?php echo esc_html($empleado_nombre); ?></h3>

        <?php foreach ($equipos as $equipo_post) : ?>
            <div class="equipo-item">
                <h4>
                    <a href="<?php echo esc_url(get_permalink($equipo_post->ID)); ?>">
                        <?php echo esc_html($equipo_post->post_title); ?>
                    </a>
                </h4>
                <?php
                // Obtener las posiciones específicas de este empleado en cada equipo
                $filas = $wpdb->get_results($wpdb->prepare("
                    SELECT posicion_id
                    FROM $tabla_exp
                    WHERE empleado_id = %d
                      AND equipo_id   = %d
                ", $empleado_id, $equipo_post->ID));

                if ($filas) {
                    // Ordenar por 'pos_score' si es necesario
                    foreach ($filas as $fila) {
                        $fila->pos_score = (int) get_post_meta($fila->posicion_id, '_cdb_posiciones_score', true);
                    }
                    usort($filas, function($a, $b) {
                        return $b->pos_score <=> $a->pos_score;
                    });

                    echo '<ul>';
                    foreach ($filas as $fila) {
                        $pos_id   = (int) $fila->posicion_id;
                        $pos_post = get_post($pos_id);

                        if ($pos_post && $pos_post->post_type === 'cdb_posiciones') {
                            $pos_nombre = get_the_title($pos_post);
                            $pos_link   = get_permalink($pos_post);
                        } else {
                            $pos_nombre = 'Posición desconocida';
                            $pos_link   = '#';
                        }

                        echo '<li>';
                            // Enlazamos la posición si tenemos un título
                            if ($pos_nombre && $pos_nombre !== 'Posición desconocida') {
                                echo '<a href="' . esc_url($pos_link) . '">';
                                echo esc_html($pos_nombre);
                                echo '</a>';
                            } else {
                                echo esc_html($pos_nombre);
                            }
                        echo '</li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No se registraron posiciones específicas para este equipo.</p>';
                }
                ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Registrar el shortcode
function cdb_equipos_del_empleado_registrar_shortcode() {
    add_shortcode('equipos_del_empleado', 'cdb_equipos_del_empleado_shortcode');
}
add_action('init', 'cdb_equipos_del_empleado_registrar_shortcode');

// Inyectar automáticamente la gráfica y el listado de equipos en single-empleado
function cdb_inyectar_equipos_del_empleado_en_contenido($content) {
    $empleado_id = get_queried_object_id();
    if (get_post_type($empleado_id) !== 'empleado' || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $is_self = function_exists('cdb_obtener_empleado_id')
        && (int) cdb_obtener_empleado_id( get_current_user_id() ) === (int) $empleado_id;

    if (false === apply_filters('cdb_empleado_inyectar_grafica', true, $empleado_id)) {
        $hero_html = '';
    } else {
        $attrs        = array('id_suffix' => 'content');
        $grafica_html = apply_filters('cdb_grafica_empleado_html', '', $empleado_id, $attrs);

        if ( apply_filters( 'cdb_empleado_use_new_card', false ) ) {
            ob_start();
            $empleado_id = get_the_ID();
            include plugin_dir_path( __DIR__ ) . 'templates/empleado-card-oct.php';
            $tarjeta_oct = ob_get_clean();
            $card_html   = $tarjeta_oct;
        } else {
            $empleado_author   = (int) get_post_field('post_author', $empleado_id);
            $disponible        = ('1' === get_post_meta($empleado_id, 'disponible', true));
            $total             = (float) apply_filters('cdb_grafica_empleado_total', 0, $empleado_id);
            $current_user      = wp_get_current_user();
            $es_rol_empleado   = in_array('empleado', (array) $current_user->roles, true);

            $card_html  = '<div class="cdb-empleado-card">';
            $card_html .= '<div class="cdb-empleado-card__avatar">' . get_avatar($empleado_author, 96) . '</div>';
            $card_html .= '<div class="cdb-empleado-card__name">' . esc_html(get_the_title($empleado_id)) . '</div>';
            if ( ! $es_rol_empleado ) {
                $card_html .= '<div class="cdb-empleado-card__estado">';
                $card_html .= '<strong>' . esc_html__( 'Estado:', 'cdb-empleado' ) . '</strong> ';
                $card_html .= '<span class="cdb-pill ' . ( $disponible ? 'ok' : 'off' ) . '">';
                $card_html .= $disponible ? __( 'Disponible', 'cdb-empleado' ) : __( 'No disponible', 'cdb-empleado' );
                $card_html .= '</span></div>';
            }
            $card_html .= '<div class="cdb-empleado-card__score" aria-label="Puntuación total">'
                        .  number_format_i18n($total, 0) . '</div>';
            $card_html .= '</div>';
        }

        $hero_html  = '<section class="cdb-empleado-hero">';
        $hero_html .= $card_html;
        $hero_html .= '<div class="cdb-empleado-grafica-wrap">' . $grafica_html . '</div>';
        $hero_html .= '</section>';
    }

    $calificacion_block = '';
    if ( true === apply_filters( 'cdb_empleado_inyectar_calificacion', true, $empleado_id ) ) {
        if ( $is_self ) {
            $body_html = apply_filters( 'cdb_grafica_empleado_scores_table_html', '', $empleado_id, array( 'with_legend' => true ) );
        } else {
            if ( current_user_can( 'submit_grafica_empleado' ) ) {
                $body_html = apply_filters( 'cdb_grafica_empleado_form_html', '', $empleado_id, array( 'id_suffix' => 'content', 'embed_chart' => false ) );
            } else {
                $notice    = apply_filters( 'cdb_grafica_empleado_notice', '', $empleado_id );
                $body_html = ! empty( $notice ) ? '<div class="cdb-grafica-empleado-notice">' . $notice . '</div>'
                    : apply_filters( 'cdb_grafica_empleado_scores_table_html', '', $empleado_id, array( 'with_legend' => true ) );
            }
        }

        $calificacion_block = '<div class="cdb-empleado-calificacion-wrap">' . $body_html . '</div>';
    }

    $shortcode_output = do_shortcode('[equipos_del_empleado empleado_id="' . $empleado_id . '"]');
    return $content . $hero_html . $calificacion_block . $shortcode_output;
}
add_filter('the_content', 'cdb_inyectar_equipos_del_empleado_en_contenido');
