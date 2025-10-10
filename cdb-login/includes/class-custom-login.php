<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CDB_Custom_Login {
    
    public function __construct() {
        add_action( 'login_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_action( 'login_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'login_enqueue_scripts', [ $this, 'customize_login_styles' ] );
        add_filter( 'login_form_defaults', [ $this, 'custom_login_button_text' ] );
        add_filter( 'login_headerurl', [ $this, 'custom_login_url' ] );
        add_filter( 'login_headertext', [ $this, 'custom_login_text' ] );
        add_filter( 'login_display_language_dropdown', '__return_false' );
        add_action( 'login_footer', [ $this, 'remove_back_to_blog' ] );
        add_action( 'login_footer', [ $this, 'add_countdown_script' ] );
    }

    public function enqueue_styles() {
        wp_enqueue_style( 'cdb-login-custom-style', plugin_dir_url( __FILE__ ) . 'assets/css/custom-login.css', [], null );
    }

    public function enqueue_scripts() {
        $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/custom-login.js';
        if ( file_exists( $script_path ) ) {
            wp_enqueue_script( 'cdb-login-script', plugin_dir_url( __FILE__ ) . 'assets/js/custom-login.js', [], null, true );
        }
    }

    public function customize_login_styles() {
        $background_color   = get_option( 'cdb_login_background_color', '#f4f4f4' );
        $background_image   = get_option( 'cdb_login_background_image', '' );
        $button_color       = get_option( 'cdb_login_button_color', '#0073aa' );
        $button_hover_color = get_option( 'cdb_login_button_hover_color', '#005177' );
        $field_focus_color  = get_option( 'cdb_login_field_focus_color', '#0073aa' );
        $border_radius      = get_option( 'cdb_login_border_radius', '12' );
        $logo_url           = plugin_dir_url( __FILE__ ) . 'assets/cdb_logo.png';

        echo '<style type="text/css">
            body.login {
                background-color: ' . esc_attr( $background_color ) . ';
                background-image: ' . ( ! empty( $background_image ) ? 'url(' . esc_url( $background_image ) . ')' : 'none' ) . ';
                background-size: cover;
                background-position: center;
            }

            body.login h1 a {
                background-image: url("' . esc_url( $logo_url ) . '") !important;
                width: 300px;
                height: 80px;
                background-size: contain;
                background-repeat: no-repeat;
                display: block;
            }

            body.login form#loginform {
                border-radius: ' . esc_attr( $border_radius ) . 'px !important;
                background: #fff !important;
                padding: 20px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                border: 1px solid #ddd !important;
            }

            body.login .button-primary {
                background-color: ' . esc_attr( $button_color ) . ';
                border-color: ' . esc_attr( $button_color ) . ';
                border-radius: ' . esc_attr( $border_radius ) . 'px !important;
            }
            body.login .button-primary:hover {
                background-color: ' . esc_attr( $button_hover_color ) . ';
                border-color: ' . esc_attr( $button_hover_color ) . ';
            }

            body.login input[type="text"],
            body.login input[type="password"],
            body.login input[type="email"] {
                border-radius: ' . esc_attr( $border_radius ) . 'px !important;
            }
            body.login input[type="text"]:focus,
            body.login input[type="password"]:focus,
            body.login input[type="email"]:focus {
                border-color: ' . esc_attr( $field_focus_color ) . ';
                box-shadow: 0 0 2px ' . esc_attr( $field_focus_color ) . ';
            }
        </style>';
    }

    public function custom_login_button_text( $defaults ) {
        $defaults['label_log_in'] = esc_html( get_option( 'cdb_login_button_text', 'Iniciar sesión' ) );
        return $defaults;
    }

    public function custom_login_url() {
        return home_url();
    }

    public function custom_login_text() {
        return 'Regresar a la web principal';
    }

    public function remove_back_to_blog() {
        echo '<style type="text/css">#backtoblog { display: none !important; }</style>';
    }


    public function add_countdown_script() {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log("Script de cuenta atrás cargado correctamente."); 

            const countdownElement = document.getElementById("cdb-countdown");
            const countdownDate = new Date(countdownElement.getAttribute("data-countdown-date")).getTime();

            if (!countdownElement || isNaN(countdownDate)) {
                console.error("Error: No se pudo obtener la fecha de cuenta atrás.");
                return;
            }

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = countdownDate - now;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    countdownElement.textContent = "¡El evento ha comenzado!";
                    return;
                }

                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `La temporada comienza en: ${days}d ${hours}h ${minutes}m ${seconds}s`;
            }

            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        });
        </script>';
    }
}

// Inicializar la clase
new CDB_Custom_Login();
