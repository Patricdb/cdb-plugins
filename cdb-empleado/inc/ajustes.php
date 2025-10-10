<?php
// Asegurar que el archivo no se acceda directamente.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Registrar ajustes y campos para CdB Empleado.
 */
function cdb_empleado_registrar_ajustes() {
    register_setting( 'cdb_empleado_general', 'inyectar_grafica', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_checkbox',
        'default'           => 1,
    ) );
    register_setting( 'cdb_empleado_general', 'inyectar_calificacion', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_checkbox',
        'default'           => 1,
    ) );

    add_settings_section(
        'cdb_empleado_general_section',
        __( 'Opciones generales', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado'
    );

    add_settings_field(
        'inyectar_grafica',
        __( 'Inyectar gráfica', 'cdb-empleado' ),
        'cdb_empleado_campo_inyectar_grafica',
        'cdb-empleado',
        'cdb_empleado_general_section'
    );

    add_settings_field(
        'inyectar_calificacion',
        __( 'Inyectar calificación', 'cdb-empleado' ),
        'cdb_empleado_campo_inyectar_calificacion',
        'cdb-empleado',
        'cdb_empleado_general_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes' );

/**
 * Registrar ajustes de la tarjeta de empleado.
 */
function cdb_empleado_registrar_ajustes_tarjeta() {
    register_setting( 'cdb_empleado_tarjeta', 'usar_tarjeta_oct', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_checkbox',
        'default'           => 0,
    ) );
    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_ink', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default'           => '#66604e',
    ) );
    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_bg_start', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default'           => '#f5e8c8',
    ) );
    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_bg_end', array(
        'sanitize_callback' => 'sanitize_hex_color',
        'default'           => '#efe1b4',
    ) );
    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_font_body', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_fuente',
        'default'           => 'sans',
    ) );
    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_font_heading', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_fuente',
        'default'           => 'sans',
    ) );

    register_setting( 'cdb_empleado_tarjeta', 'tarjeta_oct_bg_svg', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_svg',
        'default'           => cdb_empleado_default_bg_svg(),
    ) );

    add_settings_section(
        'cdb_empleado_tarjeta_section',
        __( 'Tarjeta de Empleado', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado-tarjeta'
    );

    add_settings_field(
        'usar_tarjeta_oct',
        __( 'Usar tarjeta octogonal', 'cdb-empleado' ),
        'cdb_empleado_campo_usar_tarjeta_oct',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );

    add_settings_field(
        'tarjeta_oct_ink',
        __( 'Color de tinta', 'cdb-empleado' ),
        'cdb_empleado_campo_tarjeta_oct_ink',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );

    add_settings_field(
        'tarjeta_oct_bg',
        __( 'Gradiente de fondo', 'cdb-empleado' ),
        'cdb_empleado_campo_tarjeta_oct_bg',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );

    add_settings_field(
        'tarjeta_oct_bg_svg',
        __( 'SVG de fondo', 'cdb-empleado' ),
        'cdb_empleado_campo_tarjeta_oct_bg_svg',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );

    add_settings_field(
        'tarjeta_oct_font_body',
        __( 'Fuente principal', 'cdb-empleado' ),
        'cdb_empleado_campo_tarjeta_oct_font_body',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );

    add_settings_field(
        'tarjeta_oct_font_heading',
        __( 'Fuente de encabezados', 'cdb-empleado' ),
        'cdb_empleado_campo_tarjeta_oct_font_heading',
        'cdb-empleado-tarjeta',
        'cdb_empleado_tarjeta_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes_tarjeta' );

/**
 * Registrar ajustes de rendimiento.
 */
function cdb_empleado_registrar_ajustes_rendimiento() {
    register_setting( 'cdb_empleado_rendimiento', 'rank_ttl', array(
        'sanitize_callback' => 'absint',
        'default'           => 600,
    ) );

    add_settings_section(
        'cdb_empleado_rendimiento_section',
        __( 'Opciones de rendimiento', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado-rendimiento'
    );

    add_settings_field(
        'rank_ttl',
        __( 'TTL de ranking (segundos)', 'cdb-empleado' ),
        'cdb_empleado_campo_rank_ttl',
        'cdb-empleado-rendimiento',
        'cdb_empleado_rendimiento_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes_rendimiento' );

/**
 * Registrar ajustes de roles y capacidades.
 */
function cdb_empleado_registrar_ajustes_roles() {
    register_setting( 'cdb_empleado_roles', 'cdb_empleado_extra_caps', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_caps',
        'default'           => array(),
    ) );

    register_setting( 'cdb_empleado_roles', 'cdb_empleado_selector_roles', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_roles_selector',
        'default'           => array( 'administrator', 'editor', 'author', 'empleado' ),
    ) );

    add_settings_section(
        'cdb_empleado_roles_section',
        __( 'Roles y capacidades', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado-roles'
    );

    add_settings_field(
        'cdb_empleado_extra_caps',
        __( 'Capacidades extra', 'cdb-empleado' ),
        'cdb_empleado_campo_extra_caps',
        'cdb-empleado-roles',
        'cdb_empleado_roles_section'
    );

    add_settings_field(
        'cdb_empleado_selector_roles',
        __( 'Roles permitidos en selector', 'cdb-empleado' ),
        'cdb_empleado_campo_selector_roles',
        'cdb-empleado-roles',
        'cdb_empleado_roles_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes_roles' );

/**
 * Registrar ajustes de metacampos.
 */
function cdb_empleado_registrar_ajustes_metacampos() {
    register_setting( 'cdb_empleado_metacampos', 'label_disponible', array(
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => 'Disponible',
    ) );
    register_setting( 'cdb_empleado_metacampos', 'default_disponible', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_checkbox',
        'default'           => 1,
    ) );

    add_settings_section(
        'cdb_empleado_metacampos_section',
        __( 'Metacampo Disponible', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado-metacampos'
    );

    add_settings_field(
        'label_disponible',
        __( 'Etiqueta personalizada', 'cdb-empleado' ),
        'cdb_empleado_campo_label_disponible',
        'cdb-empleado-metacampos',
        'cdb_empleado_metacampos_section'
    );

    add_settings_field(
        'default_disponible',
        __( 'Valor por defecto', 'cdb-empleado' ),
        'cdb_empleado_campo_default_disponible',
        'cdb-empleado-metacampos',
        'cdb_empleado_metacampos_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes_metacampos' );

/**
 * Registrar ajustes de textos y datos adicionales.
 */
function cdb_empleado_registrar_ajustes_textos() {
    register_setting( 'cdb_empleado_textos', 'cdb_empleado_notice_default', array(
        'sanitize_callback' => 'sanitize_textarea_field',
        'default'           => '',
    ) );

    register_setting( 'cdb_empleado_textos', 'cdb_empleado_extra_data_fields', array(
        'sanitize_callback' => 'cdb_empleado_sanitizar_extra_data_fields',
        'default'           => array(),
    ) );

    add_settings_section(
        'cdb_empleado_textos_section',
        __( 'Textos y datos adicionales', 'cdb-empleado' ),
        '__return_false',
        'cdb-empleado-textos'
    );

    add_settings_field(
        'cdb_empleado_notice_default',
        __( 'Aviso por defecto', 'cdb-empleado' ),
        'cdb_empleado_campo_notice_default',
        'cdb-empleado-textos',
        'cdb_empleado_textos_section'
    );

    add_settings_field(
        'cdb_empleado_extra_data_fields',
        __( 'Campos de datos adicionales', 'cdb-empleado' ),
        'cdb_empleado_campo_extra_data_fields',
        'cdb-empleado-textos',
        'cdb_empleado_textos_section'
    );
}
add_action( 'admin_init', 'cdb_empleado_registrar_ajustes_textos' );

/**
 * Sanitizar valores de checkbox.
 *
 * @param mixed $valor Valor enviado desde el formulario.
 * @return int 1 si está marcado, 0 en caso contrario.
 */
function cdb_empleado_sanitizar_checkbox( $valor ) {
    return ! empty( $valor ) ? 1 : 0;
}

/**
 * Sanitizar selección de fuente.
 *
 * @param string $valor Valor enviado.
 * @return string Clave de fuente permitida.
 */
function cdb_empleado_sanitizar_fuente( $valor ) {
    $permitidas = array( 'sans', 'serif', 'mono' );
    return in_array( $valor, $permitidas, true ) ? $valor : 'sans';
}

/**
 * Sanitizar capacidades extra del rol empleado.
 *
 * @param array $valor Capacidades enviadas.
 * @return array Capacidades permitidas.
 */
function cdb_empleado_sanitizar_caps( $valor ) {
    $permitidas = array( 'edit_posts', 'upload_files' );
    $valor      = is_array( $valor ) ? $valor : array();
    return array_values( array_intersect( $valor, $permitidas ) );
}

/**
 * Sanitizar roles permitidos en el selector de autores.
 *
 * @param array $valor Roles enviados.
 * @return array Roles válidos.
 */
function cdb_empleado_sanitizar_roles_selector( $valor ) {
    global $wp_roles;
    $todos = array_keys( $wp_roles->roles );
    $valor = is_array( $valor ) ? $valor : array();
    return array_values( array_intersect( $valor, $todos ) );
}

/**
 * Sanitizar campos de datos adicionales para la tarjeta.
 *
 * @param string $valor Texto con pares meta|Etiqueta.
 * @return array Pares clave => etiqueta.
 */
function cdb_empleado_sanitizar_extra_data_fields( $valor ) {
    $valor  = is_string( $valor ) ? $valor : '';
    $lineas = array_filter( array_map( 'trim', explode( "\n", $valor ) ) );
    $pares  = array();
    foreach ( $lineas as $linea ) {
        $partes = array_map( 'trim', explode( '|', $linea, 2 ) );
        $key    = sanitize_key( $partes[0] ?? '' );
        $label  = sanitize_text_field( $partes[1] ?? '' );
        if ( $key && $label ) {
            $pares[ $key ] = $label;
        }
    }
    return $pares;
}

/**
 * Sanitizar SVG de fondo.
 *
 * @param string $svg Código SVG.
 * @return string SVG filtrado.
 */
function cdb_empleado_sanitizar_svg( $svg ) {
    $allowed            = wp_kses_allowed_html( 'post' );
    $allowed['svg']     = array(
        'xmlns' => true,
        'viewBox' => true,
        'width' => true,
        'height' => true,
        'fill' => true,
        'preserveAspectRatio' => true,
    );
    $allowed['g']       = array(
        'fill' => true,
        'clip-path' => true,
    );
    $allowed['path']    = array(
        'd' => true,
        'fill' => true,
        'fill-rule' => true,
        'clip-rule' => true,
        'stroke' => true,
        'stroke-width' => true,
    );
    return wp_kses( $svg, $allowed );
}

/**
 * Obtiene el SVG de fondo por defecto.
 *
 * @return string SVG por defecto.
 */
function cdb_empleado_default_bg_svg() {
    $file = plugin_dir_path( __FILE__ ) . '../assets/svg/tarjeta-oct-bg.svg';
    return file_exists( $file ) ? file_get_contents( $file ) : '';
}

/**
 * Campo checkbox para el ajuste inyectar_grafica.
 */
function cdb_empleado_campo_inyectar_grafica() {
    $valor = get_option( 'inyectar_grafica', 1 );
    echo '<input type="checkbox" name="inyectar_grafica" value="1" ' . checked( 1, $valor, false ) . ' />';
}

/**
 * Campo checkbox para el ajuste inyectar_calificacion.
 */
function cdb_empleado_campo_inyectar_calificacion() {
    $valor = get_option( 'inyectar_calificacion', 1 );
    echo '<input type="checkbox" name="inyectar_calificacion" value="1" ' . checked( 1, $valor, false ) . ' />';
}

/**
 * Campo checkbox para el ajuste usar_tarjeta_oct.
 */
function cdb_empleado_campo_usar_tarjeta_oct() {
    $valor = get_option( 'usar_tarjeta_oct', 0 );
    echo '<input type="checkbox" name="usar_tarjeta_oct" value="1" ' . checked( 1, $valor, false ) . ' />';
    echo '<p class="description">' . esc_html__( 'Activa el nuevo diseño de tarjeta octogonal.', 'cdb-empleado' ) . '</p>';
}

/**
 * Campo color para la variable --ink.
 */
function cdb_empleado_campo_tarjeta_oct_ink() {
    $valor = get_option( 'tarjeta_oct_ink', '#66604e' );
    echo '<input type="text" id="tarjeta_oct_ink" class="cdb-color-field" name="tarjeta_oct_ink" value="' . esc_attr( $valor ) . '" />';
    echo '<p class="description">' . sprintf( esc_html__( 'Color principal de la tarjeta. %s', 'cdb-empleado' ), '<a href="https://developer.wordpress.org/reference/functions/wp_color_picker/" target="_blank">' . esc_html__( 'Ayuda', 'cdb-empleado' ) . '</a>' ) . '</p>';
}

/**
 * Campo para gradiente de fondo.
 */
function cdb_empleado_campo_tarjeta_oct_bg() {
    $ini = get_option( 'tarjeta_oct_bg_start', '#f5e8c8' );
    $fin = get_option( 'tarjeta_oct_bg_end', '#efe1b4' );
    echo '<input type="text" id="tarjeta_oct_bg_start" class="cdb-color-field" name="tarjeta_oct_bg_start" value="' . esc_attr( $ini ) . '" /> ';
    echo '<input type="text" id="tarjeta_oct_bg_end" class="cdb-color-field" name="tarjeta_oct_bg_end" value="' . esc_attr( $fin ) . '" />';
    echo '<p class="description">' . esc_html__( 'Colores de inicio y fin del gradiente.', 'cdb-empleado' ) . '</p>';
}

/**
 * Campo textarea para SVG de fondo.
 */
function cdb_empleado_campo_tarjeta_oct_bg_svg() {
    $valor = get_option( 'tarjeta_oct_bg_svg', cdb_empleado_default_bg_svg() );
    echo '<textarea id="tarjeta_oct_bg_svg" name="tarjeta_oct_bg_svg" rows="5" class="large-text code">' . esc_textarea( $valor ) . '</textarea>';
    echo '<button type="button" id="tarjeta_oct_bg_svg_preview" class="button">' . esc_html__( 'Previsualizar', 'cdb-empleado' ) . '</button>';
    echo '<div id="tarjeta_oct_bg_svg_display"></div>';
    echo '<p class="description">' . esc_html__( 'SVG que se usará como fondo de la tarjeta.', 'cdb-empleado' ) . '</p>';
}

/**
 * Campo numérico para el ajuste rank_ttl.
 */
function cdb_empleado_campo_rank_ttl() {
    $valor = get_option( 'rank_ttl', 600 );
    echo '<input type="number" name="rank_ttl" value="' . esc_attr( $valor ) . '" min="0" step="1" />';
}

/**
 * Campo textarea para el aviso por defecto.
 */
function cdb_empleado_campo_notice_default() {
    $valor = get_option( 'cdb_empleado_notice_default', '' );
    echo '<textarea name="cdb_empleado_notice_default" rows="3" cols="50">' . esc_textarea( $valor ) . '</textarea>';
}

/**
 * Campo textarea para definir datos adicionales.
 */
function cdb_empleado_campo_extra_data_fields() {
    $valor   = (array) get_option( 'cdb_empleado_extra_data_fields', array() );
    $lineas  = array();
    foreach ( $valor as $key => $label ) {
        $lineas[] = $key . '|' . $label;
    }
    echo '<textarea name="cdb_empleado_extra_data_fields" rows="5" cols="50">' . esc_textarea( implode( "\n", $lineas ) ) . '</textarea>';
    echo '<p class="description">' . esc_html__( 'Una pareja por línea: meta_key|Etiqueta', 'cdb-empleado' ) . '</p>';
}

/**
 * Campo para seleccionar capacidades extra del rol empleado.
 */
function cdb_empleado_campo_extra_caps() {
    $valor = (array) get_option( 'cdb_empleado_extra_caps', array() );
    $caps  = array(
        'edit_posts'   => __( 'Editar entradas', 'cdb-empleado' ),
        'upload_files' => __( 'Subir archivos', 'cdb-empleado' ),
    );

    foreach ( $caps as $cap => $label ) {
        echo '<label><input type="checkbox" name="cdb_empleado_extra_caps[]" value="' . esc_attr( $cap ) . '" ' . checked( in_array( $cap, $valor, true ), true, false ) . ' /> ' . esc_html( $label ) . '</label><br />';
    }
}

/**
 * Campo para seleccionar roles permitidos en el selector de autores.
 */
function cdb_empleado_campo_selector_roles() {
    $valor = (array) get_option( 'cdb_empleado_selector_roles', array( 'administrator', 'editor', 'author', 'empleado' ) );
    global $wp_roles;

    foreach ( $wp_roles->roles as $role_key => $data ) {
        echo '<label><input type="checkbox" name="cdb_empleado_selector_roles[]" value="' . esc_attr( $role_key ) . '" ' . checked( in_array( $role_key, $valor, true ), true, false ) . ' /> ' . esc_html( $data['name'] ) . '</label><br />';
    }
}

/**
 * Campo de texto para la etiqueta del metacampo disponible.
 */
function cdb_empleado_campo_label_disponible() {
    $valor = get_option( 'label_disponible', 'Disponible' );
    echo '<input type="text" name="label_disponible" value="' . esc_attr( $valor ) . '" />';
}

/**
 * Campo select para el valor por defecto del metacampo disponible.
 */
function cdb_empleado_campo_default_disponible() {
    $valor = get_option( 'default_disponible', 1 );
    echo '<select name="default_disponible">';
    echo '<option value="1" ' . selected( $valor, 1, false ) . '>' . esc_html__( 'Sí', 'cdb-empleado' ) . '</option>';
    echo '<option value="0" ' . selected( $valor, 0, false ) . '>' . esc_html__( 'No', 'cdb-empleado' ) . '</option>';
    echo '</select>';
}

/**
 * Opciones de fuentes disponibles.
 */
function cdb_empleado_fuentes_disponibles() {
    return array(
        'sans' => array(
            'label' => __( 'Sans serif', 'cdb-empleado' ),
            'stack' => 'ui-sans-serif,system-ui,-apple-system,"Helvetica Neue",Arial,sans-serif',
        ),
        'serif' => array(
            'label' => __( 'Serif', 'cdb-empleado' ),
            'stack' => 'ui-serif,Georgia,Cambria,"Times New Roman",Times,serif',
        ),
        'mono' => array(
            'label' => __( 'Monospace', 'cdb-empleado' ),
            'stack' => 'ui-monospace,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace',
        ),
    );
}

/**
 * Campo select para fuente principal.
 */
function cdb_empleado_campo_tarjeta_oct_font_body() {
    $valor   = get_option( 'tarjeta_oct_font_body', 'sans' );
    $fuentes = cdb_empleado_fuentes_disponibles();
    echo '<select id="tarjeta_oct_font_body" name="tarjeta_oct_font_body">';
    foreach ( $fuentes as $key => $data ) {
        echo '<option value="' . esc_attr( $key ) . '" data-stack="' . esc_attr( $data['stack'] ) . '" ' . selected( $valor, $key, false ) . '>' . esc_html( $data['label'] ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . esc_html__( 'Fuente aplicada al contenido.', 'cdb-empleado' ) . '</p>';
}

/**
 * Campo select para fuente de encabezados.
 */
function cdb_empleado_campo_tarjeta_oct_font_heading() {
    $valor   = get_option( 'tarjeta_oct_font_heading', 'sans' );
    $fuentes = cdb_empleado_fuentes_disponibles();
    echo '<select id="tarjeta_oct_font_heading" name="tarjeta_oct_font_heading">';
    foreach ( $fuentes as $key => $data ) {
        echo '<option value="' . esc_attr( $key ) . '" data-stack="' . esc_attr( $data['stack'] ) . '" ' . selected( $valor, $key, false ) . '>' . esc_html( $data['label'] ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">' . sprintf( esc_html__( 'Fuente para encabezados. %s', 'cdb-empleado' ), '<a href="https://wordpress.org/documentation/article/using-themes/" target="_blank">' . esc_html__( 'Ayuda', 'cdb-empleado' ) . '</a>' ) . '</p>';
}

/**
 * Obtiene la ruta a una plantilla de administración.
 *
 * @param string $template Nombre de la plantilla sin extensión.
 * @return string Ruta completa del archivo de plantilla.
 */
function cdb_empleado_admin_template( $template ) {
    return plugin_dir_path( __FILE__ ) . '../templates/admin/' . $template . '.php';
}

/**
 * Configuración de submenús para el área de administración.
 *
 * @return array[]
 */
function cdb_empleado_get_submenus() {
    return array(
        array(
            'slug'       => 'cdb-empleado',
            'title'      => __( 'General', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_ajustes',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'edit.php?post_type=empleado',
            'title'      => __( 'Empleados', 'cdb-empleado' ),
            'callback'   => '',
            'capability' => 'edit_posts',
        ),
        array(
            'slug'       => 'cdb-empleado-tarjeta',
            'title'      => __( 'Tarjeta de Empleado', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_tarjeta',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-metacampos',
            'title'      => __( 'Metacampos', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_metacampos',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-roles',
            'title'      => __( 'Roles', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_roles',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-shortcodes',
            'title'      => __( 'Shortcodes', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_shortcodes',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-integraciones',
            'title'      => __( 'Integraciones', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_integraciones',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-textos',
            'title'      => __( 'Textos', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_textos',
            'capability' => 'manage_options',
        ),
        array(
            'slug'       => 'cdb-empleado-rendimiento',
            'title'      => __( 'Rendimiento', 'cdb-empleado' ),
            'callback'   => 'cdb_empleado_pagina_rendimiento',
            'capability' => 'manage_options',
        ),
    );
}

/**
 * Muestra pestañas de navegación para las páginas del plugin.
 *
 * @param string $actual Slug de la pestaña activa.
 */
function cdb_empleado_admin_tabs( $actual ) {
    echo '<h2 class="nav-tab-wrapper">';
    foreach ( cdb_empleado_get_submenus() as $submenu ) {
        $clase = ( $actual === $submenu['slug'] ) ? 'nav-tab nav-tab-active' : 'nav-tab';
        $url   = ( false !== strpos( $submenu['slug'], '.php' ) )
            ? admin_url( $submenu['slug'] )
            : admin_url( 'admin.php?page=' . $submenu['slug'] );
        printf(
            '<a href="%s" class="%s">%s</a>',
            esc_url( $url ),
            esc_attr( $clase ),
            esc_html( $submenu['title'] )
        );
    }
    echo '</h2>';
}

/**
 * Render de la página de ajustes generales.
 */
function cdb_empleado_pagina_ajustes() {
    include cdb_empleado_admin_template( 'ajustes' );
}

/**
 * Render de la página de ajustes de la tarjeta.
 */
function cdb_empleado_pagina_tarjeta() {
    include cdb_empleado_admin_template( 'tarjeta' );
}

/**
 * Render de la página de ajustes de metacampos.
 */
function cdb_empleado_pagina_metacampos() {
    include cdb_empleado_admin_template( 'metacampos' );
}

/**
 * Render de la página de ajustes de rendimiento.
 */
function cdb_empleado_pagina_rendimiento() {
    include cdb_empleado_admin_template( 'rendimiento' );
}

/**
 * Render de la página de ajustes de roles.
 */
function cdb_empleado_pagina_roles() {
    include cdb_empleado_admin_template( 'roles' );
}

/**
 * Render de la página de shortcodes.
 */
function cdb_empleado_pagina_shortcodes() {
    $shortcodes = array(
        array(
            'tag'     => 'equipos_del_empleado',
            'atts'    => array(
                'empleado_id' => __( 'ID del empleado (opcional; usa el post actual si se omite)', 'cdb-empleado' ),
            ),
            'desc'    => __( 'Lista equipos y posiciones del empleado consultando ${prefix}cdb_experiencia.', 'cdb-empleado' ),
            'example' => '[equipos_del_empleado empleado_id="15"]',
        ),
    );
    include cdb_empleado_admin_template( 'shortcodes' );
}

/**
 * Render de la página de integraciones.
 */
function cdb_empleado_pagina_integraciones() {
    include cdb_empleado_admin_template( 'integraciones' );
}

/**
 * Render de la página de textos y datos adicionales.
 */
function cdb_empleado_pagina_textos() {
    include cdb_empleado_admin_template( 'textos' );
}

/**
 * Registrar el menú y submenú de ajustes.
 */
function cdb_empleado_registrar_menu() {
    remove_submenu_page( 'cdb-empleado', 'edit.php?post_type=empleado' );

    add_menu_page(
        __( 'CdB Empleado', 'cdb-empleado' ),
        __( 'CdB Empleado', 'cdb-empleado' ),
        'manage_options',
        'cdb-empleado',
        'cdb_empleado_pagina_ajustes',
        '',
        4
    );

    foreach ( cdb_empleado_get_submenus() as $submenu ) {
        add_submenu_page(
            'cdb-empleado',
            $submenu['title'],
            $submenu['title'],
            $submenu['capability'],
            $submenu['slug'],
            $submenu['callback']
        );
    }

    remove_submenu_page( 'cdb-empleado', 'cdb-empleado' );
}
add_action( 'admin_menu', 'cdb_empleado_registrar_menu', 20 );

add_action( 'admin_notices', function() {
    $screen = get_current_screen();
    if ( 'edit' === $screen->base && 'empleado' === $screen->post_type ) {
        cdb_empleado_admin_tabs( 'edit.php?post_type=empleado' );
    }
} );

/**
 * Encola scripts y estilos para las páginas de ajustes del plugin.
 *
 * @param string $hook Identificador de la pantalla actual.
 */
function cdb_empleado_admin_enqueue( $hook ) {
    if ( false === strpos( $hook, 'cdb-empleado' ) ) {
        return;
    }

    wp_enqueue_style( 'wp-color-picker' );

    $style_path   = plugin_dir_path( __FILE__ ) . '../assets/css/admin.css';
    $style_version = file_exists( $style_path ) ? filemtime( $style_path ) : false;
    wp_enqueue_style(
        'cdb-empleado-admin',
        plugins_url( '../assets/css/admin.css', __FILE__ ),
        array( 'wp-color-picker' ),
        $style_version ?: null
    );

    $script_path   = plugin_dir_path( __FILE__ ) . '../assets/js/admin.js';
    $script_version = file_exists( $script_path ) ? filemtime( $script_path ) : false;
    wp_enqueue_script(
        'cdb-empleado-admin',
        plugins_url( '../assets/js/admin.js', __FILE__ ),
        array( 'wp-color-picker', 'jquery' ),
        $script_version ?: null,
        true
    );

    wp_localize_script( 'cdb-empleado-admin', 'cdbEmpleadoFonts', cdb_empleado_fuentes_disponibles() );
}
add_action( 'admin_enqueue_scripts', 'cdb_empleado_admin_enqueue' );

/**
 * Añade pestaña de ayuda a las pantallas del plugin.
 */
function cdb_empleado_admin_help() {
    $screen = get_current_screen();
    if ( strpos( $screen->id, 'cdb-empleado' ) === false ) {
        return;
    }

    $screen->add_help_tab( array(
        'id'      => 'cdb_empleado_help',
        'title'   => __( 'Ayuda', 'cdb-empleado' ),
        'content' => '<p>' . esc_html__( 'Consulta la documentación del plugin para obtener más información.', 'cdb-empleado' ) . '</p>' .
            '<p><a href="https://proyectocdb.es" target="_blank">' . esc_html__( 'Documentación', 'cdb-empleado' ) . '</a></p>',
    ) );
}
add_action( 'current_screen', 'cdb_empleado_admin_help' );

/**
 * Filtros para inyectar gráfica y calificación según los ajustes.
 */
function cdb_empleado_opcion_inyectar_grafica() {
    return (bool) get_option( 'inyectar_grafica', 1 );
}
add_filter( 'cdb_empleado_inyectar_grafica', 'cdb_empleado_opcion_inyectar_grafica' );

function cdb_empleado_opcion_inyectar_calificacion() {
    return (bool) get_option( 'inyectar_calificacion', 1 );
}
add_filter( 'cdb_empleado_inyectar_calificacion', 'cdb_empleado_opcion_inyectar_calificacion' );

/**
 * Filtro para usar tarjeta octogonal según ajuste.
 */
function cdb_empleado_opcion_usar_tarjeta_oct() {
    return (bool) get_option( 'usar_tarjeta_oct', 0 );
}
add_filter( 'cdb_empleado_use_new_card', 'cdb_empleado_opcion_usar_tarjeta_oct', 20 );

/**
 * Filtro para TTL del ranking según ajuste.
 */
function cdb_empleado_opcion_rank_ttl() {
    return (int) get_option( 'rank_ttl', 600 );
}
add_filter( 'cdb_empleado_rank_ttl', 'cdb_empleado_opcion_rank_ttl' );

/**
 * Filtro para el aviso por defecto en la calificación.
 *
 * @param string $msg Aviso actual.
 * @param int    $empleado_id ID del empleado.
 * @return string Aviso final.
 */
function cdb_empleado_opcion_notice_default( $msg, $empleado_id ) {
    if ( ! empty( $msg ) ) {
        return $msg;
    }

    return (string) get_option( 'cdb_empleado_notice_default', '' );
}
add_filter( 'cdb_grafica_empleado_notice', 'cdb_empleado_opcion_notice_default', 10, 2 );

/**
 * Filtro para añadir campos extra a los datos de la tarjeta.
 *
 * @param array $data Datos actuales.
 * @param int   $empleado_id ID del empleado.
 * @return array Datos modificados.
 */
function cdb_empleado_opcion_card_extra_data( $data, $empleado_id ) {
    $fields = (array) get_option( 'cdb_empleado_extra_data_fields', array() );
    foreach ( $fields as $meta_key => $label ) {
        $value = get_post_meta( $empleado_id, $meta_key, true );
        if ( '' !== $value && ! is_array( $value ) ) {
            if ( ! isset( $data['extra'] ) || ! is_array( $data['extra'] ) ) {
                $data['extra'] = array();
            }
            $data['extra'][ $meta_key ] = array(
                'label' => $label,
                'value' => $value,
            );
        }
    }

    return $data;
}
add_filter( 'cdb_empleado_card_data', 'cdb_empleado_opcion_card_extra_data', 20, 2 );
