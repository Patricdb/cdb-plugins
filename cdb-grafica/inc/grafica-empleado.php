<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// inc/grafica-empleado.php

/**
 * Devuelve el nombre pluralizado de un rol de usuario.
 */
function cdb_empleado_plural_role( $role ) {
    switch ( strtolower( $role ) ) {
        case 'tutor':
            return 'Tutores';
        case 'empleador':
            return 'Empleadores';
        case 'empleado':
        default:
            return 'Empleados';
    }
}

// ------------------------------------------------------------------
// 1. Registro del bloque "grafica-empleado".
// ------------------------------------------------------------------
function registrar_bloque_grafica_empleado() {
    $asset_file = plugin_dir_path(dirname(__FILE__)) . 'build/empleado/index.asset.php';
    if (!file_exists($asset_file)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Archivo index.asset.php para empleado no encontrado.');
        }
        return;
    }
    $asset_data = include $asset_file;
    if (!isset($asset_data['dependencies']) || !is_array($asset_data['dependencies']) || !isset($asset_data['version'])) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('index.asset.php para empleado tiene una estructura inválida.');
        }
        return;
    }
    $script_path = plugin_dir_path(dirname(__FILE__)) . 'build/empleado/index.js';
    $script_url  = plugins_url('build/empleado/index.js', dirname(__FILE__));
    wp_register_script(
        'cdb-grafica-empleado',
        $script_url,
        $asset_data['dependencies'],
        filemtime($script_path),
        true
    );
    register_block_type('cdb/grafica-empleado', array(
        'editor_script'   => 'cdb-grafica-empleado',
        'render_callback' => 'renderizar_bloque_grafica_empleado',
    ));
}
add_action('init', 'registrar_bloque_grafica_empleado');

// ------------------------------------------------------------------
// Assets y renderizado común
// ------------------------------------------------------------------
function cdb_grafica_enqueue_assets(): void {
    $base_path = plugin_dir_path( dirname( __FILE__ ) );

    if ( ! wp_style_is( 'cdb-grafica-style', 'enqueued' ) ) {
        wp_enqueue_style(
            'cdb-grafica-style',
            plugins_url( 'style.css', dirname( __FILE__ ) ),
            [],
            filemtime( $base_path . 'style.css' )
        );
    }

    $ui_defaults = [
        'accordion_bg'       => '#FAF8EE',
        'accordion_border'   => '#cdb888',
        'accordion_hover'    => '#cdb121',
        'form_bg'            => '#FAF8EE',
        'form_border'        => '#cdb888',
        'table_header_bg'    => '#cdb121',
        'table_header_color' => '#000000',
        'font_family'        => 'Arial, sans-serif',
    ];
    $ui_estilos = wp_parse_args( get_option( 'cdb_grafica_ui_estilos', [] ), $ui_defaults );
    $css  = ':root{';
    $css .= '--cdb-accordion-bg:' . sanitize_hex_color( $ui_estilos['accordion_bg'] ) . ';';
    $css .= '--cdb-accordion-border:' . sanitize_hex_color( $ui_estilos['accordion_border'] ) . ';';
    $css .= '--cdb-accordion-hover:' . sanitize_hex_color( $ui_estilos['accordion_hover'] ) . ';';
    $css .= '--cdb-form-bg:' . sanitize_hex_color( $ui_estilos['form_bg'] ) . ';';
    $css .= '--cdb-form-border:' . sanitize_hex_color( $ui_estilos['form_border'] ) . ';';
    $css .= '--cdb-table-header-bg:' . sanitize_hex_color( $ui_estilos['table_header_bg'] ) . ';';
    $css .= '--cdb-table-header-color:' . sanitize_hex_color( $ui_estilos['table_header_color'] ) . ';';
    $css .= '--cdb-font-family:' . sanitize_text_field( $ui_estilos['font_family'] ) . ';';
    $css .= '}';
    wp_add_inline_style( 'cdb-grafica-style', $css );

    if ( ! wp_script_is( 'cdb-grafica-script', 'enqueued' ) ) {
        wp_enqueue_script(
            'cdb-grafica-script',
            plugins_url( 'script.js', dirname( __FILE__ ) ),
            [ 'jquery' ],
            filemtime( $base_path . 'script.js' ),
            true
        );
    }

    if ( ! wp_script_is( 'chartjs', 'enqueued' ) ) {
        if ( ! wp_script_is( 'chartjs', 'registered' ) ) {
            wp_register_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js',
                [],
                '4.3.0',
                false
            );
        }
        wp_enqueue_script( 'chartjs' );
    }

    if ( ! has_action( 'wp_footer', 'generar_grafica_empleado_en_frontend' ) ) {
        add_action( 'wp_footer', 'generar_grafica_empleado_en_frontend', 99 );
    }
}

function cdb_grafica_empleado_render( int $empleado_id, array $attrs = [] ): string {
    cdb_grafica_enqueue_assets();
    return cdb_grafica_build_empleado_html( $empleado_id, $attrs );
}

function cdb_grafica_empleado_shortcode( $atts ): string {
    $atts = shortcode_atts( [ 'id' => get_the_ID() ], $atts, 'cdb_grafica_empleado' );
    $empleado_id = (int) $atts['id'];
    unset( $atts['id'] );
    return cdb_grafica_empleado_render( $empleado_id, $atts );
}
add_shortcode( 'cdb_grafica_empleado', 'cdb_grafica_empleado_shortcode' );

// ------------------------------------------------------------------
// 2. Construye el HTML de la gráfica para un empleado específico.
// ------------------------------------------------------------------
function cdb_grafica_build_empleado_html( int $empleado_id, array $attrs = [] ): string {
    global $wpdb;

    if ( $empleado_id <= 0 ) {
        return '';
    }

    $defaults = [
        'max_width' => '',
        'class'     => '',
        'id_suffix' => '',
    ];
    $attrs = wp_parse_args( $attrs, $defaults );

    $table_name = $wpdb->prefix . 'grafica_empleado_results';

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE post_id = %d AND user_role IS NOT NULL",
            $empleado_id
        )
    );

    $criterios = cdb_get_criterios_empleado();
    $grupos    = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $grupos[ $grupo_nombre ] = array_keys( $campos );
    }

    $roles_data = [];
    foreach ( $results as $row ) {
        $rol = strtolower( $row->user_role );
        if ( ! isset( $roles_data[ $rol ] ) ) {
            $roles_data[ $rol ] = [];
        }
        foreach ( $grupos as $grupo_nombre => $campos ) {
            if ( ! isset( $roles_data[ $rol ][ $grupo_nombre ] ) ) {
                $roles_data[ $rol ][ $grupo_nombre ] = [ 'suma' => 0, 'cuenta' => 0 ];
            }
            foreach ( $campos as $campo ) {
                if ( isset( $row->$campo ) && 0 != $row->$campo ) {
                    $roles_data[ $rol ][ $grupo_nombre ]['suma']   += $row->$campo;
                    $roles_data[ $rol ][ $grupo_nombre ]['cuenta'] += 1;
                }
            }
        }
    }

    $datasets = [];
    foreach ( $roles_data as $rol => $grupos_info ) {
        $promedios     = [];
        $tiene_valores = false;

        foreach ( $grupos as $grupo_nombre => $campos ) {
            $suma     = $grupos_info[ $grupo_nombre ]['suma'] ?? 0;
            $cuenta   = $grupos_info[ $grupo_nombre ]['cuenta'] ?? 0;
            $promedio = $cuenta > 0 ? round( $suma / $cuenta, 1 ) : 0;
            if ( $cuenta > 0 ) {
                $tiene_valores = true;
            }
            $promedios[] = $promedio;
        }

        if ( $tiene_valores ) {
            $total_rol = array_sum( $promedios );
            $datasets[] = [
                'role'  => $rol,
                'data'  => $promedios,
                'label' => cdb_empleado_plural_role( $rol ) . ' – ' . __( 'Puntuación de Gráfica', 'cdb-grafica' ) . ': ' . $total_rol,
            ];
        }
    }

    $promedios_globales = [];
    foreach ( $grupos as $grupo_nombre => $campos ) {
        $total_grupo = 0;
        $count       = 0;
        foreach ( $results as $row ) {
            foreach ( $campos as $campo ) {
                if ( isset( $row->$campo ) && $row->$campo != 0 ) {
                    $total_grupo += $row->$campo;
                    $count++;
                }
            }
        }
        $promedios_globales[] = $count > 0 ? round( $total_grupo / $count, 1 ) : 0;
    }
    $total = round( array_sum( $promedios_globales ), 1 );
    $total = (float) $total;
    $total = apply_filters( 'cdb_grafica_empleado_total', $total, $empleado_id );

    if ( $empleado_id && get_post_type( $empleado_id ) === 'empleado' ) {
        update_post_meta( $empleado_id, 'cdb_puntuacion_total', $total );
    }

    $siglas = array_map(
        static function ( $grupo ) {
            return strtok( $grupo, ' ' );
        },
        array_keys( $grupos )
    );

    $data = [
        'labels'   => $siglas,
        'datasets' => $datasets,
    ];

    $defaults_colors = [
        'empleado_background'  => 'rgba(75, 192, 192, 0.2)',
        'empleado_border'      => 'rgba(75, 192, 192, 1)',
        'empleador_background' => 'rgba(54, 162, 235, 0.2)',
        'empleador_border'     => 'rgba(54, 162, 235, 1)',
        'tutor_background'     => 'rgba(255, 99, 132, 0.2)',
        'tutor_border'         => 'rgba(255, 99, 132, 1)',
        'ticks_color'          => '#666666',
        'ticks_backdrop'       => '',
    ];
    $opts        = get_option( 'cdb_grafica_colores', $defaults_colors );

    $role_colors = [
        'empleado'  => [
            'background' => $opts['empleado_background'] ?? $defaults_colors['empleado_background'],
            'border'     => $opts['empleado_border'] ?? $defaults_colors['empleado_border'],
        ],
        'empleador' => [
            'background' => $opts['empleador_background'] ?? $defaults_colors['empleador_background'],
            'border'     => $opts['empleador_border'] ?? $defaults_colors['empleador_border'],
        ],
        'tutor'     => [
            'background' => $opts['tutor_background'] ?? $defaults_colors['tutor_background'],
            'border'     => $opts['tutor_border'] ?? $defaults_colors['tutor_border'],
        ],
    ];

    $attrs['backgroundColor']    = $role_colors['empleado']['background'];
    $attrs['borderColor']        = $role_colors['empleado']['border'];
    $attrs['ticksColor']         = $opts['ticks_color'] ?? $defaults_colors['ticks_color'];
    $attrs['ticksBackdropColor'] = $opts['ticks_backdrop'] ?? $defaults_colors['ticks_backdrop'];

    $style_defaults = [
        'border_width'     => 2,
        'legend_font_size' => 14,
        'ticks_step'       => 1,
        'ticks_min'        => 0,
        'ticks_max'        => 10,
    ];
    $estilos = get_option( 'cdb_grafica_estilos', $style_defaults );

    $div_id = 'grafica-empleado';
    if ( ! empty( $attrs['id_suffix'] ) ) {
        $div_id .= '-' . sanitize_key( $attrs['id_suffix'] );
    }
    $class_attr = $attrs['class'] ? ' class="' . esc_attr( $attrs['class'] ) . '"' : '';
    $style_attr = $attrs['max_width'] ? ' style="max-width:' . esc_attr( $attrs['max_width'] ) . ';"' : '';

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $div_id ); ?>"<?php echo $class_attr . $style_attr; ?>
         data-valores="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"
        data-role-colors="<?php echo esc_attr( wp_json_encode( $role_colors ) ); ?>"
        data-background-color="<?php echo esc_attr( $attrs['backgroundColor'] ); ?>"
        data-border-color="<?php echo esc_attr( $attrs['borderColor'] ); ?>"
        data-border-width="<?php echo esc_attr( $estilos['border_width'] ); ?>"
        data-legend-font="<?php echo esc_attr( $estilos['legend_font_size'] ); ?>"
        data-ticks-color="<?php echo esc_attr( $attrs['ticksColor'] ); ?>"
        data-ticks-backdrop-color="<?php echo esc_attr( $attrs['ticksBackdropColor'] ); ?>"
        data-ticks-step="<?php echo esc_attr( $estilos['ticks_step'] ); ?>"
        data-ticks-min="<?php echo esc_attr( $estilos['ticks_min'] ); ?>"
        data-ticks-max="<?php echo esc_attr( $estilos['ticks_max'] ); ?>">
    </div>
    <?php
    return ob_get_clean();
}

function renderizar_bloque_grafica_empleado( $attributes, $content ) {
    $empleado_id = get_the_ID();
    return cdb_grafica_empleado_render( (int) $empleado_id, $attributes );
}

add_filter(
    'cdb_grafica_empleado_html',
    function ( $html, $empleado_id, $attrs = [] ) {
        if ( ! $empleado_id ) {
            return $html;
        }
        return cdb_grafica_empleado_render( (int) $empleado_id, (array) $attrs );
    },
    10,
    3
);

// ------------------------------------------------------------------
// 3. Generar la gráfica en el frontend.
// ------------------------------------------------------------------
function generar_grafica_empleado_en_frontend() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const elements = document.querySelectorAll('[id^="grafica-empleado"]');
        if (!elements.length || typeof Chart === 'undefined') {
            return;
        }
        elements.forEach(function (dataElement) {
            const data = JSON.parse(dataElement.dataset.valores);
            const ctx = document.createElement('canvas');
            dataElement.appendChild(ctx);

            const colores = JSON.parse(dataElement.dataset.roleColors || '{}');

            const chartData = {
                labels: data.labels,
                datasets: []
            };

            if (Array.isArray(data.datasets)) {
                data.datasets.forEach(function (ds) {
                    const cfg = colores[ds.role] || {};
                    chartData.datasets.push({
                        label: ds.label,
                        data: ds.data,
                        backgroundColor: cfg.background || 'gray',
                        borderColor: cfg.border || 'gray',
                        borderWidth: 2
                    });
                });
            }

            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true } },
                scales: {
                    r: {
                        ticks: {
                            beginAtZero: true,
                            stepSize: 1,
                            max: 10,
                            min: 0,
                            color: dataElement.dataset.ticksColor,
                            backdropColor: dataElement.dataset.ticksBackdropColor || undefined
                        },
                        suggestedMin: 0,
                        suggestedMax: 10
                    }
                }
            };

            new Chart(ctx, {
                type: 'radar',
                data: chartData,
                options: options
            });
        });
    });
    </script>
    <?php
}

// ------------------------------------------------------------------
// 4. Crear la tabla de resultados con dbDelta.
// ------------------------------------------------------------------
function grafica_empleado_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $charset_collate = $wpdb->get_charset_collate();

    // Sin comentarios en línea dentro de la SQL
    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        user_role VARCHAR(50) NOT NULL,
        direccion FLOAT NOT NULL,
        camarero FLOAT NOT NULL,
        venta FLOAT NOT NULL,
        ritmo_sala FLOAT NOT NULL,
        satisfaccion FLOAT NOT NULL,
        cooperacion FLOAT NOT NULL,
        orden FLOAT NOT NULL,
        cocina_local FLOAT NOT NULL,
        cocinero FLOAT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ------------------------------------------------------------------
// 5. Formulario de calificaciones para empleados.
// ------------------------------------------------------------------
function cdb_grafica_notice_html( $msg, $empleado_id ) {
    $msg = apply_filters( 'cdb_grafica_empleado_notice', $msg, (int) $empleado_id );
    return '<p class="cdb-grafica-notice cdb-grafica-notice--warn">' . esc_html( $msg ) . '</p>';
}

function cdb_grafica_build_empleado_form_html( int $empleado_id, array $args = [] ): string {
    $post_id = $empleado_id ? (int) $empleado_id : get_the_ID();
    $args    = wp_parse_args( (array) $args, [ 'embed_chart' => true ] );

    cdb_grafica_enqueue_assets();

    // Verificar permiso global
    if ( ! current_user_can( 'submit_grafica_empleado' ) ) {
        return cdb_grafica_notice_html( __( 'No tienes permisos para enviar resultados.', 'cdb-grafica' ), $post_id );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $user_id    = get_current_user_id();
    $user       = wp_get_current_user();
    $roles      = (array) $user->roles;
    $post       = get_post( $post_id );

    if ( ! $post || $post->post_type !== 'empleado' ) {
        return cdb_grafica_notice_html( __( 'Este contenido no es un empleado válido.', 'cdb-grafica' ), $post_id );
    }

    // Determinar si se muestra el formulario o no:
    $puede_calificar = true;
    $mensaje         = '';

    // Verificaciones por rol:
    if ( in_array( 'empleado', $roles, true ) ) {
        // 1) ¿Está intentando calificar a su propio empleado?
        if ( $post->post_author == $user_id ) {
            $puede_calificar = false;
            $mensaje         = __( 'No puedes calificar a tu propio empleado.', 'cdb-grafica' );
        } else {
            // 2) Verificar si ambos comparten algún equipo en wp_cdb_experiencia
            if ( function_exists( 'cdb_obtener_empleado_id' ) ) {
                $mi_empleado_id = cdb_obtener_empleado_id( $user_id );
            } else {
                return cdb_grafica_notice_html( __( 'Required function cdb_obtener_empleado_id is missing.', 'cdb-grafica' ), $post_id );
            }
            if ( ! $mi_empleado_id ) {
                $puede_calificar = false;
                $mensaje         = __( 'No se encontró tu perfil de empleado.', 'cdb-grafica' );
            } else {
                // Consulta: ¿existe un equipo_id compartido entre "mi_empleado_id" y "$post_id" en wp_cdb_experiencia?
                $existe_equipo_compartido = $wpdb->get_var(
                    $wpdb->prepare(
                        "
                SELECT 1
                FROM {$wpdb->prefix}cdb_experiencia e1
                JOIN {$wpdb->prefix}cdb_experiencia e2
                      ON e1.equipo_id = e2.equipo_id
                WHERE e1.empleado_id = %d
                  AND e2.empleado_id = %d
                LIMIT 1
            ",
                        $mi_empleado_id,
                        $post_id
                    )
                );

                if ( ! $existe_equipo_compartido ) {
                    $puede_calificar = false;
                    $mensaje         = __( 'No puedes calificar a un empleado que no pertenece a tu mismo equipo.', 'cdb-grafica' );
                }
            }
        }
    }

    if ( in_array( 'empleador', $roles, true ) && $puede_calificar ) {
        // 1) Obtener los bares del empleador (autor = $user_id)
        $bares_del_empleador = get_posts(
            [
                'post_type'      => 'bar',
                'post_status'    => 'publish',
                'author'         => $user_id,
                'fields'         => 'ids',
                'posts_per_page' => -1,
            ]
        );
        $bares_del_empleador = $bares_del_empleador ?: [];

        // 2) Verificar si el empleado (post_id) tiene cdb_experiencia en alguno de esos bares
        //    Si no existe coincidencia, no puede calificarlo.
        if ( empty( $bares_del_empleador ) ) {
            $puede_calificar = false;
            $mensaje         = __( 'No tienes ningún bar registrado para calificar.', 'cdb-grafica' );
        } else {
            $in_bares = implode( ',', array_map( 'intval', $bares_del_empleador ) );

            // ¿El empleado (post_id) tiene experiencia en alguno de esos bares?
            $existe_relacion = $wpdb->get_var(
                $wpdb->prepare(
                    "
            SELECT 1
            FROM {$wpdb->prefix}cdb_experiencia
            WHERE empleado_id = %d
              AND bar_id IN ($in_bares)
            LIMIT 1
        ",
                    $post_id
                )
            );

            if ( ! $existe_relacion ) {
                $puede_calificar = false;
                $mensaje         = __( 'No pertenece a tu equipo.', 'cdb-grafica' );
            }
        }
    }

    if ( ! $puede_calificar ) {
        $mensaje = $mensaje ?: __( 'No puedes calificar a este empleado.', 'cdb-grafica' );
        return cdb_grafica_notice_html( $mensaje, $post_id );
    }

    // Obtener datos existentes (si alguno)
    $existing_data = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d AND user_id = %d",
            $post_id,
            $user_id
        ),
        ARRAY_A
    );

    // Definir los nombres y descripciones de las características
    $grupos = cdb_get_criterios_empleado();

    $embed_chart = apply_filters( 'cdb_grafica_empleado_form_embed_chart', ! empty( $args['embed_chart'] ), $post_id, $args );

    ob_start();
    if ( $embed_chart ) {
        echo apply_filters( 'cdb_grafica_empleado_html', '', $post_id, $args );
    }
    ?>
    <form method="post" action="">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
        <?php wp_nonce_field('submit_grafica_empleado', 'grafica_empleado_nonce'); ?>

        <?php foreach ($grupos as $grupo_nombre => $campos):
            $campos_visibles = array_filter(
                $campos,
                static function ($info) {
                    return ! isset($info['visible']) || $info['visible'];
                }
            );
            if (empty($campos_visibles)) {
                continue;
            }
        ?>
            <div class="accordion">
                <div class="accordion-header">
                    <button type="button" class="accordion-toggle">
                        <?php echo esc_html($grupo_nombre); ?>
                    </button>
                </div>
                <div class="accordion-content" style="display: none;">
                    <?php foreach ($campos_visibles as $campo_slug => $campo_info):
                        $valor_existente = isset($existing_data[$campo_slug]) ? $existing_data[$campo_slug] : '';
                    ?>
                        <label for="<?php echo esc_attr($campo_slug); ?>">
                            <strong><?php echo esc_html($campo_info['label']); ?></strong><br>
                            <em><?php echo esc_html($campo_info['descripcion']); ?></em>
                        </label>
                        <input 
                            type="range" 
                            id="<?php echo esc_attr($campo_slug); ?>"
                            name="<?php echo esc_attr($campo_slug); ?>" 
                            value="<?php echo esc_attr($valor_existente !== '' ? $valor_existente : 0); ?>" 
                            min="0" 
                            max="10" 
                            step="1" 
                            oninput="document.getElementById('<?php echo esc_attr($campo_slug); ?>_output').value = this.value"
                        >
                        <output id="<?php echo esc_attr($campo_slug); ?>_output">
                            <?php echo esc_html($valor_existente !== '' ? $valor_existente : 0); ?>
                        </output>
                    <br>

                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <button type="submit" name="submit_grafica_empleado">Enviar</button>
    </form>
    <?php
    return ob_get_clean();
}

function cdb_grafica_empleado_form_shortcode( $atts ) {
    $atts        = wp_parse_args( (array) $atts, [ 'id_suffix' => '' ] );
    $empleado_id = isset( $atts['post_id'] ) ? (int) $atts['post_id'] : get_the_ID();
    return cdb_grafica_build_empleado_form_html( (int) $empleado_id, $atts );
}
add_shortcode( 'grafica_empleado_form', 'cdb_grafica_empleado_form_shortcode' );

add_filter(
    'cdb_grafica_empleado_form_html',
    function ( $html, $empleado_id, $args = [] ) {
        if ( ! $empleado_id ) {
            return $html;
        }
        return cdb_grafica_build_empleado_form_html( (int) $empleado_id, (array) $args );
    },
    10,
    3
);

function cdb_grafica_build_empleado_scores_table_html( int $empleado_id, array $args = [] ): string {
    global $wpdb;

    if ( $empleado_id <= 0 ) {
        return '';
    }

    cdb_grafica_enqueue_assets();
    $args = wp_parse_args( $args, [ 'with_legend' => false ] );

    $table_name = $wpdb->prefix . 'grafica_empleado_results';
    $results    = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE post_id = %d AND user_role IS NOT NULL",
            $empleado_id
        )
    );

    $criterios = cdb_get_criterios_empleado();
    $roles     = [ 'empleado', 'empleador', 'tutor' ];

    $totales = [];
    foreach ( $results as $row ) {
        $rol = strtolower( $row->user_role );
        if ( ! in_array( $rol, $roles, true ) ) {
            continue;
        }
        foreach ( $criterios as $grupo_campos ) {
            foreach ( $grupo_campos as $campo_slug => $info ) {
                if ( isset( $row->$campo_slug ) && 0 != $row->$campo_slug ) {
                    if ( ! isset( $totales[ $rol ][ $campo_slug ] ) ) {
                        $totales[ $rol ][ $campo_slug ] = [ 'suma' => 0, 'cuenta' => 0 ];
                    }
                    $totales[ $rol ][ $campo_slug ]['suma']   += (float) $row->$campo_slug;
                    $totales[ $rol ][ $campo_slug ]['cuenta'] += 1;
                }
            }
        }
    }

    $scores = [];
    foreach ( $roles as $rol ) {
        foreach ( $criterios as $grupo_campos ) {
            foreach ( $grupo_campos as $campo_slug => $info ) {
                $suma   = $totales[ $rol ][ $campo_slug ]['suma'] ?? 0;
                $cuenta = $totales[ $rol ][ $campo_slug ]['cuenta'] ?? 0;
                $scores[ $rol ][ $campo_slug ] = $cuenta > 0 ? ( $suma / $cuenta ) : null;
            }
        }
    }

    $group_scores = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        foreach ( $roles as $rol ) {
            $suma   = 0;
            $cuenta = 0;
            foreach ( $campos as $campo_slug => $info ) {
                $valor = $scores[ $rol ][ $campo_slug ] ?? null;
                if ( null !== $valor ) {
                    $suma += $valor;
                    $cuenta++;
                }
            }
            $group_scores[ $grupo_nombre ][ $rol ] = $cuenta > 0 ? ( $suma / $cuenta ) : null;
        }
    }

    $c_emp   = cdb_grafica_get_color_by_role( 'empleado' );
    $c_empdr = cdb_grafica_get_color_by_role( 'empleador' );
    $c_tutor = cdb_grafica_get_color_by_role( 'tutor' );

    $legend_html = '';
    if ( $args['with_legend'] ) {
        $legend_html = sprintf(
            '<div class="cdb-scores-legend"><span class="role role-emp"><i style="background:%1$s"></i> %2$s</span><span class="role role-empdr"><i style="background:%3$s"></i> %4$s</span><span class="role role-tutor"><i style="background:%5$s"></i> %6$s</span></div>',
            esc_attr( $c_emp ),
            esc_html__( 'Empleados', 'cdb-grafica' ),
            esc_attr( $c_empdr ),
            esc_html__( 'Empleadores', 'cdb-grafica' ),
            esc_attr( $c_tutor ),
            esc_html__( 'Tutores', 'cdb-grafica' )
        );
    }

    $print_cell = static function ( $valor ) {
        echo null !== $valor ? esc_html( round( (float) $valor, 1 ) ) : '–';
    };

    ob_start();
    echo $legend_html; ?>
    <table class="cdb-grafica-scores"
           style="--color-empleado:<?php echo esc_attr( $c_emp ); ?>;
                  --color-empleador:<?php echo esc_attr( $c_empdr ); ?>;
                  --color-tutor:<?php echo esc_attr( $c_tutor ); ?>;">
        <caption class="cdb-scores-title"><?php esc_html_e( 'Tus calificaciones:', 'cdb-grafica' ); ?></caption>
        <colgroup>
            <col class="col-criterio" style="width:auto">
            <col class="col-emp" style="width:3ch">
            <col class="col-empdr" style="width:3ch">
            <col class="col-tutor" style="width:3ch">
        </colgroup>
        <?php foreach ( $criterios as $grupo_nombre => $campos ) : ?>
        <tbody class="grupo">
            <tr class="group-header">
                <th class="criterio-cell">
                    <button class="group-toggle" type="button" aria-expanded="false">
                        <?php echo esc_html( $grupo_nombre ); ?>
                    </button>
                </th>
                <?php foreach ( $roles as $rol ) : ?>
                    <td class="score-cell"><?php $print_cell( $group_scores[ $grupo_nombre ][ $rol ] ?? null ); ?></td>
                <?php endforeach; ?>
            </tr>
            <?php foreach ( $campos as $campo_slug => $info ) : ?>
                <tr>
                    <th scope="row" class="criterio-cell">
                        <?php echo esc_html( $info['label'] ); ?>
                        <?php if ( ! empty( $info['descripcion'] ) ) : ?>
                            <small><?php echo esc_html( $info['descripcion'] ); ?></small>
                        <?php endif; ?>
                    </th>
                    <?php foreach ( $roles as $rol ) : ?>
                        <td class="score-cell"><?php $print_cell( $scores[ $rol ][ $campo_slug ] ?? null ); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <?php endforeach; ?>
    </table>
    <?php
    return ob_get_clean();
}

add_filter(
    'cdb_grafica_empleado_scores_table_html',
    function ( $html, $empleado_id, $args = [] ) {
        if ( ! $empleado_id ) {
            return $html;
        }
        return cdb_grafica_build_empleado_scores_table_html( (int) $empleado_id, (array) $args );
    },
    10,
    3
);

/**
 * Acordeón de solo lectura con 3 valores por criterio (empleado/empleador/tutor).
 * Devuelve '' solo si $empleado_id <= 0.
 * Encola style y JS del acordeón si no están.
 *
 * @param int   $empleado_id
 * @param array $args { id_suffix:string('content'), show_legend:bool(true) }
 * @return string HTML
 */
function cdb_grafica_build_empleado_readonly_html( $empleado_id, $args = array() ) {
    if ( $empleado_id <= 0 ) {
        return '';
    }

    cdb_grafica_enqueue_assets();

    $args = wp_parse_args( (array) $args, array(
        'id_suffix'   => 'content',
        'show_legend' => true,
    ) );

    $criterios = cdb_get_criterios_empleado();
    $grupos    = array();
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $slug       = strtok( $grupo_nombre, ' ' );
        $campo_lbl  = $grupo_nombre;
        if ( 1 === count( $campos ) ) {
            $campo_lbl = reset( $campos )['label'];
        }
        $grupos[ $slug ] = array(
            'label'  => $grupo_nombre,
            'campos' => array(
                $slug => array( 'label' => $campo_lbl ),
            ),
        );
    }

    $scores      = cdb_grafica_get_scores_by_role( (int) $empleado_id, array( 'with_raw' => true ) );
    $legend_html = '';
    if ( ! empty( $args['show_legend'] ) ) {
        $c_emp   = cdb_grafica_get_color_by_role( 'empleado' );
        $c_empdr = cdb_grafica_get_color_by_role( 'empleador' );
        $c_tutor = cdb_grafica_get_color_by_role( 'tutor' );
        $legend_html = sprintf(
            '<div class="cdb-scores-legend"><span class="role role-emp"><i style="background:%1$s"></i> %2$s</span><span class="role role-empdr"><i style="background:%3$s"></i> %4$s</span><span class="role role-tutor"><i style="background:%5$s"></i> %6$s</span></div>',
            esc_attr( $c_emp ),
            esc_html__( 'Empleados', 'cdb-grafica' ),
            esc_attr( $c_empdr ),
            esc_html__( 'Empleadores', 'cdb-grafica' ),
            esc_attr( $c_tutor ),
            esc_html__( 'Tutores', 'cdb-grafica' )
        );
    }

    $id = 'cdb-readonly-' . sanitize_key( $args['id_suffix'] ?? 'content' );

    ob_start();
    ?>
<div class="accordion cdb-readonly" id="<?php echo esc_attr( $id ); ?>">
  <?php if ( ! empty( $legend_html ) && ! empty( $args['show_legend'] ) ) echo $legend_html; ?>

  <?php foreach ( $grupos as $grupo_slug => $grupo_data ): ?>
    <div class="accordion-item">
      <div class="accordion-header">
        <button class="accordion-toggle" type="button"><?php echo esc_html( $grupo_data['label'] ); ?></button>
      </div>
      <div class="accordion-content">
        <?php foreach ( $grupo_data['campos'] as $campo_slug => $campo_info ): ?>
          <div class="cdb-readonly-row">
            <span class="cdb-readonly-label"><?php echo esc_html( $campo_info['label'] ); ?></span>
            <span class="cdb-score-pills">
              <?php
                $v_emp = $scores['raw']['empleado']['grupos'][ $campo_slug ] ?? '';
                $v_jef = $scores['raw']['empleador']['grupos'][ $campo_slug ] ?? '';
                $v_tut = $scores['raw']['tutor']['grupos'][ $campo_slug ] ?? '';
                $print = function( $val, $role, $bg, $bd ) {
                    $empty = ( '' === $val || null === $val );
                    printf(
                        '<span class="cdb-score-pill -%1$s%4$s" style="background:%2$s;border-color:%3$s">%5$s</span>',
                        esc_attr( $role ),
                        esc_attr( $bg ),
                        esc_attr( $bd ),
                        $empty ? ' is-empty' : '',
                        $empty ? '&ndash;' : esc_html( $val )
                    );
                };
                $print( $v_emp, 'emp', cdb_grafica_get_color_by_role( 'empleado', 'background' ), cdb_grafica_get_color_by_role( 'empleado', 'border' ) );
                $print( $v_jef, 'boss', cdb_grafica_get_color_by_role( 'empleador', 'background' ), cdb_grafica_get_color_by_role( 'empleador', 'border' ) );
                $print( $v_tut, 'tutor', cdb_grafica_get_color_by_role( 'tutor', 'background' ), cdb_grafica_get_color_by_role( 'tutor', 'border' ) );
              ?>
            </span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php
    return ob_get_clean();
}

add_filter(
    'cdb_grafica_empleado_readonly_html',
    function( $html, $empleado_id, $args = array() ) {
        return cdb_grafica_build_empleado_readonly_html( $empleado_id, $args );
    },
    10,
    3
);

// ------------------------------------------------------------------
// 6. Procesar el envío del formulario "grafica_empleado_form".
//    Repite las validaciones de rol y guarda o actualiza los datos
//    en la tabla personalizada del empleado evaluado.
// ------------------------------------------------------------------
function handle_grafica_empleado_submission() {
    if ( isset( $_POST['submit_grafica_empleado'] ) ) {
        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;

        // Repetir validaciones para seguridad
        if ( ! isset( $_POST['grafica_empleado_nonce'] ) || ! wp_verify_nonce( $_POST['grafica_empleado_nonce'], 'submit_grafica_empleado' ) ) {
            $mensaje = __( 'Nonce inválido.', 'cdb-grafica' );
            $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
            wp_die( esc_html( $mensaje ) );
        }
        if ( ! current_user_can( 'submit_grafica_empleado' ) ) {
            $mensaje = __( 'No tienes permisos para realizar esta acción.', 'cdb-grafica' );
            $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
            wp_die( esc_html( $mensaje ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'grafica_empleado_results';
        $user_id    = get_current_user_id();
        $user       = wp_get_current_user();
        $roles      = (array) $user->roles;
        $post       = get_post( $post_id );

        if ( ! $post ) {
            $mensaje = __( 'Empleado inválido.', 'cdb-grafica' );
            $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
            wp_die( esc_html( $mensaje ) );
        }
        if ( 'empleado' !== $post->post_type ) {
            $mensaje = __( 'No es un post de tipo empleado.', 'cdb-grafica' );
            $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
            wp_die( esc_html( $mensaje ) );
        }

// Validaciones de rol
if (in_array('empleado', $roles)) {
    // 1) Evitar que un empleado califique su propio empleado
    if ($post->post_author == $user_id) {
        $mensaje = __( 'No puedes calificar a tu propio empleado.', 'cdb-grafica' );
        $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
        wp_die( esc_html( $mensaje ) );
    }

    // 2) Verificar si ambos (quien califica y el calificado) comparten equipo en wp_cdb_experiencia
    if ( function_exists( 'cdb_obtener_empleado_id' ) ) {
        $mi_empleado_id = cdb_obtener_empleado_id( $user_id );
    } else {
        $mensaje = __( 'Required function cdb_obtener_empleado_id is missing.', 'cdb-grafica' );
        $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
        return '<p>' . esc_html( $mensaje ) . '</p>';
    }
    if ( ! $mi_empleado_id ) {
        $mensaje = __( 'No se encontró tu perfil de empleado.', 'cdb-grafica' );
        $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
        wp_die( esc_html( $mensaje ) );
    }

    // Consulta: ¿existe un equipo_id compartido entre "mi_empleado_id" y "$post_id" en wp_cdb_experiencia?
    $existe_equipo_compartido = $wpdb->get_var($wpdb->prepare("
        SELECT 1
        FROM {$wpdb->prefix}cdb_experiencia e1
        JOIN {$wpdb->prefix}cdb_experiencia e2 
              ON e1.equipo_id = e2.equipo_id
        WHERE e1.empleado_id = %d
          AND e2.empleado_id = %d
        LIMIT 1
    ", $mi_empleado_id, $post_id));

    if ( ! $existe_equipo_compartido ) {
        $mensaje = __( 'No puedes calificar a un empleado que no pertenece a tu mismo equipo.', 'cdb-grafica' );
        $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
        wp_die( esc_html( $mensaje ) );
    }
}

if (in_array('empleador', $roles)) {
    // 1) Bares del empleador (autor = $user_id)
    $bares_del_empleador = get_posts([
        'post_type'      => 'bar',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'fields'         => 'ids',
        'posts_per_page' => -1
    ]);
    $bares_del_empleador = $bares_del_empleador ?: [];

    // 2) Verificar si el empleado (post_id) tiene experiencia en alguno de esos bares
    if (empty($bares_del_empleador)) {
        $mensaje = __( 'No tienes bares para calificar a este empleado.', 'cdb-grafica' );
        $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
        wp_die( esc_html( $mensaje ) );
    } else {
        $in_bares = implode(',', array_map('intval', $bares_del_empleador));

        $existe_relacion = $wpdb->get_var($wpdb->prepare("
            SELECT 1
            FROM {$wpdb->prefix}cdb_experiencia
            WHERE empleado_id = %d
              AND bar_id IN ($in_bares)
            LIMIT 1
        ", $post_id));

        if ( ! $existe_relacion ) {
            $mensaje = __( 'No pertenece a tu equipo.', 'cdb-grafica' );
            $mensaje = apply_filters( 'cdb_grafica_empleado_notice', $mensaje, $post_id );
            wp_die( esc_html( $mensaje ) );
        }
    }
}

        // Otros roles sin restricciones

        // Preparar datos
        $data   = [
            'post_id' => $post_id,
            'user_id' => $user_id,
        ];
        $fields     = $wpdb->get_col("SHOW COLUMNS FROM $table_name");

        // Añadir el rol si la columna existe
        if (in_array('user_role', $fields, true) && !empty($user->roles)) {
            $data['user_role'] = sanitize_text_field($user->roles[0]);
        }

        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $field !== 'id' && $field !== 'created_at' && $field !== 'user_role') {
                $data[$field] = floatval($_POST[$field]);
            }
        }

        // Insertar o actualizar
        $existing_row = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE post_id = %d AND user_id = %d",
            $post_id,
            $user_id
        ));

        if ($existing_row) {
            $wpdb->update($table_name, $data, ['id' => $existing_row]);
            $row_id = $existing_row;
            $accion = 'actualizacion';
        } else {
            $wpdb->insert($table_name, $data);
            $row_id = $wpdb->insert_id;
            $accion = 'nueva';
        }

        if ( ! empty( $row_id ) ) {
            cdb_mails_send_new_review_notification( $row_id, 'empleado', $accion );
        }

        /**
         * Acciones tras guardar/actualizar una valoración.
         * Permite a otros plugins reaccionar y se invalida la caché interna.
         */
        do_action( 'cdb_grafica_after_save', (int) $post_id );

        // Redirigir
        wp_safe_redirect( get_permalink( $post_id ) );
        exit;
    }
}
