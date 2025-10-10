
<?php
// Evitar el acceso directo al archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Evita el acceso directo.
}

add_action('login_enqueue_scripts', function() {
    // Obtener configuraciones
    $background_color   = get_option('cdb_login_background_color', '#f4f4f4');
    $background_image   = get_option('cdb_login_background_image', '');
    $button_color       = get_option('cdb_login_button_color', '#0073aa');
    $button_hover_color = get_option('cdb_login_button_hover_color', '#005177');
    $field_focus_color  = get_option('cdb_login_field_focus_color', '#0073aa');
    $font_family        = get_option('cdb_login_font_family', 'Arial, sans-serif');
    $logo_url           = plugin_dir_url(__FILE__) . 'assets/cdb_logo.png';
    $eye_icon_url       = get_option('cdb_login_eye_icon_url', '');
    $icon_color         = get_option('cdb_login_eye_icon_color', '#0073aa');
    $icon_hover_color   = get_option('cdb_login_eye_icon_hover_color', '#ff6600');
    $border_radius      = get_option('cdb_login_border_radius', '12');
    $text_color         = get_option('cdb_login_text_color', '#ffffff'); // Color del texto
    $link_color         = get_option('cdb_login_link_color', '#007cba'); // Color de los enlaces
    $link_hover_color   = get_option('cdb_login_link_hover_color', '#005a8c'); // Color de los enlaces (hover)

    echo '<style type="text/css">
        /* Estilo del fondo */
        body.login {
            background-color: ' . esc_attr($background_color) . ';
            background-image: ' . (!empty($background_image) ? 'url(' . esc_url($background_image) . ')' : 'none') . ';
            background-size: cover;
            background-position: center;
            font-family: ' . esc_attr($font_family) . ';
            color: ' . esc_attr($text_color) . ' !important;
        }

        /* Estilo del logo */
        body.login h1 a {
            background-image: url("' . esc_url($logo_url) . '");
            width: 300px;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            margin-bottom: 10px;
        }

        /* Asegurar que la caja del formulario tenga el borde redondeado */
        body.login #login {
            padding: 20px;
        }

        /* Sobreescribir el borde de login.min.css */
        body.login form#loginform {
            border-radius: ' . esc_attr($border_radius) . 'px !important;
            background: #fff !important;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #ddd !important;
        }

        /* Estilo de los botones */
        body.login .button-primary {
            background-color: ' . esc_attr($button_color) . ';
            border-color: ' . esc_attr($button_color) . ';
            border-radius: ' . esc_attr($border_radius) . 'px !important;
            font-family: ' . esc_attr($font_family) . ';
        }
        body.login .button-primary:hover {
            background-color: ' . esc_attr($button_hover_color) . ';
            border-color: ' . esc_attr($button_hover_color) . ';
        }

        /* Estilo de los campos de entrada */
        body.login input[type="text"],
        body.login input[type="password"],
        body.login input[type="email"] {
            border-radius: ' . esc_attr($border_radius) . 'px !important;
        }
        body.login input[type="text"]:focus,
        body.login input[type="password"]:focus,
        body.login input[type="email"]:focus {
            border-color: ' . esc_attr($field_focus_color) . ';
            box-shadow: 0 0 2px ' . esc_attr($field_focus_color) . ';
        }

        /* Estilo del ícono del ojo */
        .login .button.wp-hide-pw {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            color: ' . esc_attr($icon_color) . ' !important;
        }
        .login .button.wp-hide-pw span.dashicons {
            font-size: 20px;
            color: ' . esc_attr($icon_color) . ' !important;
        }
        .login .button.wp-hide-pw:hover span.dashicons {
            color: ' . esc_attr($icon_hover_color) . ' !important;
        }

        ' . (!empty($eye_icon_url) ? '
        /* Ícono personalizado del ojo */
        .login .button.wp-hide-pw span.dashicons::before {
            content: "";
            background-image: url(' . esc_url($eye_icon_url) . ');
            background-size: contain;
            background-repeat: no-repeat;
            display: inline-block;
            width: 20px;
            height: 20px;
        }
        ' : '') . '

        /* Color de los enlaces */
        body.login a {
            color: ' . esc_attr($link_color) . ' !important;
        }

        body.login a:hover {
            color: ' . esc_attr($link_hover_color) . ' !important;
        }
        
  /* Ajuste de márgenes en el texto de términos y condiciones */
body.login p {
    margin-top: 2px !important;
    margin-bottom: 2px !important;
    text-align: center !important;
    font-size: 14px !important;
    line-height: 1.6 !important;
    max-width: 90% !important;
    margin-left: auto !important;
    margin-right: auto !important;
    display: block !important;
}
    </style>';
});

// Cambia el texto del botón de inicio de sesión
add_filter( 'login_form_defaults', function( $defaults ) {
    $button_text = get_option( 'cdb_login_button_text', 'Iniciar sesión' );
    $defaults['label_log_in'] = esc_html( $button_text );
    return $defaults;
} );

// Cambia la URL y el texto del logo
add_filter( 'login_headerurl', function() {
    return home_url();
} );
add_filter( 'login_headertext', function() {
    return 'Regresar a la web principal';
} );

// Elimina el selector de idioma en el login
add_filter( 'login_display_language_dropdown', '__return_false' );

// Oculta el enlace "Ir a CdB"
add_action( 'login_footer', function() {
    echo '<style type="text/css">#backtoblog { display: none !important; }</style>';
} );

// Agregar script personalizado a la pantalla de login
function cdb_enqueue_login_script() {
    $script_path = plugin_dir_path( __FILE__ ) . 'assets/js/custom-login.js';
    if ( file_exists( $script_path ) ) {
        wp_enqueue_script( 'cdb-login-script', plugin_dir_url( __FILE__ ) . 'assets/js/custom-login.js', array(), null, true );
    }
}
add_action( 'login_enqueue_scripts', 'cdb_enqueue_login_script' );

// Agregar un mensaje animado y la cuenta atrás en la pantalla de login
 add_action('login_header', function() {
    $countdown_date = '2025-03-21T00:00:00'; // Fecha de la cuenta atrás
    echo '
    <div id="cdb-countdown" data-countdown-date="' . esc_attr($countdown_date) . '" style="display: none; text-align: center; font-size: 18px; font-weight: bold; color: #ffffff; margin-top: 10px;"></div>';
 });

 add_action('login_footer', function() {
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log("Script de cuenta atrás cargado correctamente."); // Depuración

        const messageElement = document.getElementById("cdb-login-message");
        const countdownElement = document.getElementById("cdb-countdown");

        if (!messageElement || !countdownElement) {
            console.warn("Elemento cdb-login-message o cdb-countdown no encontrado en la página.");
            return;
        }

        // Obtener la fecha de cuenta atrás desde el atributo del HTML
        const countdownDateAttr = countdownElement.getAttribute("data-countdown-date");
        console.log("Fecha obtenida para cuenta atrás:", countdownDateAttr); // Depuración

        if (!countdownDateAttr) {
            console.error("No se ha definido una fecha de cuenta atrás en el HTML.");
            return;
        }

        const countdownDate = new Date(countdownDateAttr).getTime();
        if (isNaN(countdownDate)) {
            console.error("Fecha de cuenta atrás inválida:", countdownDateAttr);
            return;
        }

        function startCountdown() {
            console.log("Iniciando cuenta atrás..."); // Depuración
            countdownElement.style.display = "block"; // Asegurar que la cuenta atrás se muestre

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = countdownDate - now;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    countdownElement.textContent = "¡El evento ha comenzado!";
                    return;
                }

                // Calcular días, horas, minutos y segundos restantes
                const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

                countdownElement.innerHTML = `La temporada comienza en: ${days}d ${hours}h ${minutes}m ${seconds}s`;
            }

            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        }
        startCountdown();
        });
    });
    </script>';
 });
add_action( 'login_enqueue_scripts', function() {
    wp_enqueue_style('cdb-login-custom-style', plugin_dir_url( __FILE__ ) . 'assets/css/custom-login.css', [], null);
});