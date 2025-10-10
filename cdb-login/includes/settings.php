<?php
// Añade un menú en el administrador
add_action( 'admin_menu', function() {
    add_menu_page(
        'Configuración CdB Login',
        'CdB Login',
        'manage_options',
        'cdb-login-settings',
        'cdb_login_render_settings_page',
        'dashicons-lock',
        3
    );
});

// Renderiza la página de configuración
function cdb_login_render_settings_page() {
    $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
    ?>
    <div class="wrap">
        <h1>Configuración de CdB Login</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=cdb-login-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">General</a>
            <a href="?page=cdb-login-settings&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">Avanzado</a>
        </h2>

        <form method="post" action="options.php">
            <?php
            if ( $active_tab === 'general' ) {
                settings_fields( 'cdb_login_general_settings_group' );
                do_settings_sections( 'cdb-login-general' );
            } elseif ( $active_tab === 'advanced' ) {
                settings_fields( 'cdb_login_advanced_settings_group' );
                do_settings_sections( 'cdb-login-advanced' );
            }
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Configuraciones generales y avanzadas
add_action( 'admin_init', function() {
    // Configuraciones generales
    add_settings_section(
        'cdb_login_general_settings_section',
        'Configuraciones Generales',
        function() {
            echo '<p>Ajustes básicos para CdB Login.</p>';
        },
        'cdb-login-general'
    );

    // Configuraciones estéticas generales
    $general_settings = [
    'cdb_login_background_color' => 'Color de Fondo',
    'cdb_login_text_color' => 'Color del Texto',
    'cdb_login_link_color' => 'Color de los Enlaces',
    'cdb_login_link_hover_color' => 'Color de los Enlaces (Hover)',
    'cdb_login_background_image' => 'Imagen de Fondo',
    'cdb_login_button_color' => 'Color del Botón',
    'cdb_login_button_hover_color' => 'Color del Botón (Hover)',
    'cdb_login_field_focus_color' => 'Color del Borde (Focus)',
    'cdb_login_button_text' => 'Texto del Botón',
    'cdb_login_border_radius' => 'Radio de las Esquinas',
    'cdb_login_eye_icon_color' => 'Color del Ícono del Ojo',
    'cdb_login_eye_icon_hover_color' => 'Color del Ícono del Ojo (Hover)',
    'cdb_login_eye_icon_url' => 'URL del Ícono del Ojo',
];


    foreach ( $general_settings as $key => $label ) {
        add_settings_field(
            $key,
            $label,
            function() use ( $key ) {
                $value = get_option( $key, '' );
                if ( strpos( $key, 'color' ) !== false || strpos( $key, 'background' ) !== false ) {
                    echo '<input type="color" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
                } elseif ( strpos( $key, 'url' ) !== false ) {
                    echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
                } else {
                    echo '<input type="text" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
                }
            },
            'cdb-login-general',
            'cdb_login_general_settings_section'
        );
        register_setting( 'cdb_login_general_settings_group', $key );
    }


    // Configuraciones avanzadas
    add_settings_section(
        'cdb_login_advanced_settings_section',
        'Configuraciones Avanzadas',
        function() {
            echo '<p>Ajustes avanzados para seguridad y accesibilidad.</p>';
        },
        'cdb-login-advanced'
    );

    add_settings_field(
        'cdb_login_limit_attempts',
        'Límite de Intentos de Login',
        function() {
            $value = get_option( 'cdb_login_limit_attempts', 5 );
            echo '<input type="number" name="cdb_login_limit_attempts" value="' . esc_attr( $value ) . '" class="small-text">';
            echo '<p class="description">Número máximo de intentos antes de bloquear al usuario temporalmente.</p>';
        },
        'cdb-login-advanced',
        'cdb_login_advanced_settings_section'
    );
    register_setting( 'cdb_login_advanced_settings_group', 'cdb_login_limit_attempts' );

    add_settings_field(
        'cdb_login_redirect_message',
        'Mensaje para Usuarios No Registrados',
        function() {
            $value = get_option( 'cdb_login_redirect_message', 'Debes iniciar sesión para acceder al sitio.' );
            echo '<textarea name="cdb_login_redirect_message" class="large-text" rows="3">' . esc_textarea( $value ) . '</textarea>';
            echo '<p class="description">Mensaje que se mostrará a los visitantes redirigidos al login.</p>';
        },
        'cdb-login-advanced',
        'cdb_login_advanced_settings_section'
    );
    register_setting( 'cdb_login_advanced_settings_group', 'cdb_login_redirect_message' );
});

// Cargar estilos en el panel de administración solo en la página de configuración del plugin
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'toplevel_page_cdb-login-settings') { // Verifica que estamos en la página del plugin
        wp_enqueue_style('cdb-login-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
    }
});
