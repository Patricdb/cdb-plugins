<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// inc/grafica-bar.php

// ------------------------------------------------------------------
// 1. Registrar el bloque de gráfica "bar".
// ------------------------------------------------------------------
function registrar_bloque_grafica_bar() {
    $asset_file = plugin_dir_path(dirname(__FILE__)) . 'build/bar/index.asset.php';

    if (!file_exists($asset_file)) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Archivo index.asset.php para bar no encontrado.');
        }
        return;
    }

    $asset_data = include $asset_file;
    if (
        !isset($asset_data['dependencies']) 
        || !is_array($asset_data['dependencies']) 
        || !isset($asset_data['version'])
    ) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('index.asset.php para bar tiene una estructura inválida.');
        }
        return;
    }

    $script_path = plugin_dir_path(dirname(__FILE__)) . 'build/bar/index.js';
    $script_url  = plugins_url('build/bar/index.js', dirname(__FILE__));
    wp_register_script(
        'cdb-grafica-bar',
        $script_url,
        $asset_data['dependencies'],
        filemtime($script_path),
        true
    );

    register_block_type('cdb/grafica-bar', array(
        'editor_script'   => 'cdb-grafica-bar',
        'render_callback' => 'renderizar_bloque_grafica_bar',
    ));
}
add_action('init', 'registrar_bloque_grafica_bar');

// ------------------------------------------------------------------
// 2. Render callback: se calcula la gráfica y se almacena la puntuación total.
// ------------------------------------------------------------------
function renderizar_bloque_grafica_bar($attributes, $content) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_bar_results';
    $post_id    = get_the_ID();

    cdb_grafica_enqueue_assets();

    // Recuperar todas las experiencias para este bar desde wp_cdb_experiencia
$results = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM $table_name WHERE post_id = %d
", $post_id));

    // Inicializar grupos desde los criterios centralizados
    $criterios = cdb_get_criterios_bar();
    $grupos    = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $grupos[ $grupo_nombre ] = array_keys( $campos );
    }

    // Usar solo las siglas como etiquetas de la gráfica
    $etiquetas_grafica = array_map(
        function ( $grupo ) {
            return strtok( $grupo, ' ' );
        },
        array_keys( $grupos )
    );

    // Calcular promedios
    $promedios = [];
    foreach ($grupos as $grupo_nombre => $campos) {
        $total_grupo = 0;
        $count       = 0;
        foreach ($results as $row) {
            foreach ($campos as $campo) {
                // Un valor 0 indica que el criterio no fue valorado y se ignora
                if (isset($row->$campo) && $row->$campo != 0) {
                    $total_grupo += $row->$campo;
                    $count++;
                }
            }
        }
        $promedios[] = $count > 0 ? round($total_grupo / $count, 1) : 0;
    }

    // Calcular total
    $total = round(array_sum($promedios), 1);

    // Guardar en meta si es un bar
    if ($post_id && get_post_type($post_id) === 'bar') {
        update_post_meta($post_id, 'cdb_puntuacion_total', $total);
    }

    // Datos para el frontend
    $data = [
        'labels'    => $etiquetas_grafica,
        'promedios' => $promedios,
        'total'     => $total,
    ];

    // Obtener colores configurados
    $defaults = [
        'bar_background'      => 'rgba(75, 192, 192, 0.2)',
        'bar_border'          => 'rgba(75, 192, 192, 1)',
        'ticks_color'         => '#666666',
        'ticks_backdrop'      => ''
    ];
    $opts     = get_option('cdb_grafica_colores', $defaults);
    $attributes['backgroundColor']   = $opts['bar_background'] ?? $defaults['bar_background'];
    $attributes['borderColor']       = $opts['bar_border'] ?? $defaults['bar_border'];
    $attributes['ticksColor']        = $opts['ticks_color'] ?? $defaults['ticks_color'];
    $attributes['ticksBackdropColor'] = $opts['ticks_backdrop'] ?? $defaults['ticks_backdrop'];

    $style_defaults = [
        'border_width'     => 2,
        'legend_font_size' => 14,
        'ticks_step'       => 1,
        'ticks_min'        => 0,
        'ticks_max'        => 10,
    ];
    $estilos = get_option( 'cdb_grafica_estilos', $style_defaults );

    ob_start();
    ?>
    <div id="grafica-bar"
         data-valores="<?php echo esc_attr(wp_json_encode($data)); ?>"
         data-background-color="<?php echo esc_attr($attributes['backgroundColor']); ?>"
         data-border-color="<?php echo esc_attr($attributes['borderColor']); ?>"
         data-border-width="<?php echo esc_attr( $estilos['border_width'] ); ?>"
         data-legend-font="<?php echo esc_attr( $estilos['legend_font_size'] ); ?>"
         data-ticks-color="<?php echo esc_attr($attributes['ticksColor']); ?>"
         data-ticks-backdrop-color="<?php echo esc_attr($attributes['ticksBackdropColor']); ?>"
         data-ticks-step="<?php echo esc_attr( $estilos['ticks_step'] ); ?>"
         data-ticks-min="<?php echo esc_attr( $estilos['ticks_min'] ); ?>"
         data-ticks-max="<?php echo esc_attr( $estilos['ticks_max'] ); ?>">
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// 3. Generar la gráfica en el frontend.
// ------------------------------------------------------------------
function generar_grafica_en_bloque() {
    ?>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const dataElement = document.getElementById('grafica-bar');
        if (dataElement) {
            const data = JSON.parse(dataElement.dataset.valores);
            const ctx  = document.createElement('canvas');
            dataElement.appendChild(ctx);

            const chartData = {
                labels: data.labels,
                datasets: [{
                    label: `<?php echo esc_js( __( 'Puntuación de Gráfica', 'cdb-grafica' ) ); ?>: ${data.total}`,
                    data: data.promedios,
                    backgroundColor: dataElement.dataset.backgroundColor,
                    borderColor: dataElement.dataset.borderColor,
                    borderWidth: 2
                }]
            };
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
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'generar_grafica_en_bloque');

// ------------------------------------------------------------------
// 4. Creación de la tabla con dbDelta.
// ------------------------------------------------------------------
function grafica_bar_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_bar_results';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) NOT NULL,
        user_id BIGINT(20) NOT NULL,
        user_role VARCHAR(50) NOT NULL,
        relacion_superiores FLOAT NOT NULL,
        salario FLOAT NOT NULL,
        espacio_seguro FLOAT NOT NULL,
        turnos_justos FLOAT NOT NULL,
        motivacion FLOAT NOT NULL,
        bienvenida FLOAT NOT NULL,
        formacion FLOAT NOT NULL,
        reputacion FLOAT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ------------------------------------------------------------------
// 5. Shortcode "grafica_bar_form": Mostrar formulario o mensaje según rol/relación.
// ------------------------------------------------------------------
add_shortcode('grafica_bar_form', function($atts) {
    $atts = shortcode_atts(['post_id' => get_the_ID()], $atts);

    if (!current_user_can('submit_grafica_bar')) {
        return '<p>' . esc_html__( 'No tienes permisos para enviar resultados.', 'cdb-grafica' ) . '</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'grafica_bar_results';
    $user_id    = get_current_user_id();
    $user       = wp_get_current_user();
    $roles      = (array) $user->roles;
    $post_id    = intval($atts['post_id']);
    $post       = get_post($post_id);

    if (!$post || $post->post_type !== 'bar') {
        return '<p>' . esc_html__( 'Este contenido no es un bar válido.', 'cdb-grafica' ) . '</p>';
    }

    // Inicialmente, se asume que se puede calificar
    $puede_calificar = true;
    $mensaje = '';

    // Reglas:
    // - Rol Empleador: mostrar formulario (inputs deshabilitados, sin botón)
    // - Rol Empleado: solo puede calificar si pertenece a un equipo cuyo meta "_cdb_equipo_bar" coincida con el ID del bar.
    if (in_array('empleador', $roles)) {
        $puede_calificar = false;
        $mensaje = ''; // Sin mensaje
    } else if (in_array('empleado', $roles)) {
    // 1) Obtener el CPT 'empleado' del usuario
    if ( function_exists( 'cdb_obtener_empleado_id' ) ) {
        $mi_empleado_id = cdb_obtener_empleado_id( $user_id );
    } else {
        return '<p>' . esc_html__( 'Required function cdb_obtener_empleado_id is missing.', 'cdb-grafica' ) . '</p>';
    }
    if (!$mi_empleado_id) {
        $puede_calificar = false;
        $mensaje = __( 'No perteneces a ningún equipo de este bar.', 'cdb-grafica' );
    } else {
        // 2) Verificar si existe alguna experiencia en wp_cdb_experiencia
        //    que vincule a este empleado con el bar (post_id).
        global $wpdb;
        $existe_relacion = $wpdb->get_var($wpdb->prepare("
            SELECT 1
            FROM {$wpdb->prefix}cdb_experiencia
            WHERE empleado_id = %d
              AND bar_id = %d
            LIMIT 1
        ", $mi_empleado_id, $post_id));

        // 3) Si no se encontró ningún registro que vincule empleado y bar,
        //    no se puede calificar.
        if (!$existe_relacion) {
            $puede_calificar = false;
            $mensaje = __( 'No perteneces a ningún equipo de este bar.', 'cdb-grafica' );
        }
    }
}

    // Otros roles sin restricción

    // Obtener datos existentes
    $existing_data = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE post_id = %d AND user_id = %d",
        $post_id,
        $user_id
    ), ARRAY_A);
    
    // Definir los nombres y descripciones de las características
    $grupos = cdb_get_criterios_bar();

// Encolar estilos y scripts (acordeón, etc.)
    cdb_grafica_enqueue_assets();

    ob_start();

    // Si es Empleado y no puede calificar, mostrar solo el mensaje y no el formulario
    if (!$puede_calificar && in_array('empleado', $roles)) {
        echo '<p style="color:red; font-weight:bold;">' . esc_html($mensaje) . '</p>';
        return ob_get_clean();
    }

    // Mostrar el formulario
    ?>
    <form method="post" action="">
        <input type="hidden" name="post_id" value="<?php echo esc_attr($post_id); ?>">
        <?php wp_nonce_field('submit_grafica_bar', 'grafica_bar_nonce'); ?>

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
                        <?php 
                        // Si es Empleador, inhabilitar el control
                        if (in_array('empleador', $roles)) {
                            echo 'disabled';
                        }
                        ?>
                    >
                    <output id="<?php echo esc_attr($campo_slug); ?>_output">
                        <?php echo esc_html($valor_existente !== '' ? $valor_existente : 0); ?>
                    </output>
                    <br>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php 
        // Mostrar botón solo si el rol no es Empleador
        if (!in_array('empleador', $roles)) : ?>
            <button type="submit" name="submit_grafica_bar">Enviar</button>
        <?php endif; ?>
    </form>
    <?php
    return ob_get_clean();
});

// ------------------------------------------------------------------
// 6. Procesar el envío del formulario "grafica_bar_form".
//    Valida roles y guarda o actualiza la puntuación en la tabla
//    personalizada para el bar actual.
// ------------------------------------------------------------------
function handle_grafica_bar_submission() {
    if (isset($_POST['submit_grafica_bar'])) {
        if (!isset($_POST['grafica_bar_nonce']) || !wp_verify_nonce($_POST['grafica_bar_nonce'], 'submit_grafica_bar')) {
            wp_die( esc_html__( 'Nonce inválido.', 'cdb-grafica' ) );
        }
        if (!current_user_can('submit_grafica_bar')) {
            wp_die( esc_html__( 'No tienes permisos para realizar esta acción.', 'cdb-grafica' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'grafica_bar_results';
        $user_id    = get_current_user_id();
        $user       = wp_get_current_user();
        $roles      = (array) $user->roles;
        $post_id    = intval($_POST['post_id']);
        $post       = get_post($post_id);

        if (!$post || $post->post_type !== 'bar') {
            wp_die( esc_html__( 'Bar inválido.', 'cdb-grafica' ) );
        }

        // Para Empleador, impedir envío
        if (in_array('empleador', $roles)) {
            wp_die( esc_html__( 'No puedes enviar calificaciones a un bar como Empleador.', 'cdb-grafica' ) );
        }

 // Para Empleado, verificar pertenencia al bar mediante wp_cdb_experiencia
if (in_array('empleado', $roles)) {
    if ( function_exists( 'cdb_obtener_empleado_id' ) ) {
        $mi_empleado_id = cdb_obtener_empleado_id( $user_id );
    } else {
        return '<p>' . esc_html__( 'Required function cdb_obtener_empleado_id is missing.', 'cdb-grafica' ) . '</p>';
    }
    if (!$mi_empleado_id) {
        wp_die( esc_html__( 'No perteneces a ningún equipo de este bar.', 'cdb-grafica' ) );
    }

    // Revisar en wp_cdb_experiencia si el empleado está asociado a este bar
    $existe_relacion = $wpdb->get_var($wpdb->prepare("
        SELECT 1
        FROM {$wpdb->prefix}cdb_experiencia
        WHERE empleado_id = %d
          AND bar_id = %d
        LIMIT 1
    ", $mi_empleado_id, $post_id));

    if (!$existe_relacion) {
        wp_die( esc_html__( 'No perteneces a ningún equipo de este bar.', 'cdb-grafica' ) );
    }
}

        // Otros roles sin restricción

        // Preparar datos para insertar o actualizar
        $data   = [
            'post_id' => $post_id,
            'user_id' => $user_id,
        ];
        $fields = $wpdb->get_col("SHOW COLUMNS FROM $table_name");

        if (in_array('user_role', $fields, true) && !empty($user->roles)) {
            $data['user_role'] = sanitize_text_field($user->roles[0]);
        }

        foreach ($fields as $field) {
            if ($field === 'id' || $field === 'created_at' || $field === 'user_role') {
                continue; // Ignore auto fields
            }
            if (isset($_POST[$field])) {
                $data[$field] = floatval($_POST[$field]);
            }
        }

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
            cdb_mails_send_new_review_notification( $row_id, 'bar', $accion );
        }

        wp_safe_redirect( get_permalink( $post_id ) );
        exit;
    }
}