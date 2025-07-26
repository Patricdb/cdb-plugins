<?php
// Redirige a los visitantes al login si no están registrados
add_action( 'template_redirect', function() {
    if ( ! is_user_logged_in() && ! is_admin() ) {
        // Excluir páginas de acceso público
        $paginas_publicas = array('condiciones-de-uso', 'politica-de-privacidad', 'login');

        if ( is_page( $paginas_publicas ) ) {
            return; // Permitir acceso sin redirigir al login
        }

        wp_redirect( wp_login_url() );
        exit;
    }
});

add_filter( 'login_redirect', function( $redirect_to, $request, $user ) {
    if (!is_wp_error($user) && isset($user->ID)) {
        return home_url('/hola/');
    }
    return $redirect_to;
}, 10, 3 );
