<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function cdb_eventos_mensajes_admin_menu() {
    $parent_slug = 'edit.php?post_type=evento';
    add_submenu_page(
        $parent_slug,
        __( 'Configuración de Mensajes y Avisos', 'cdb-eventos' ),
        __( 'Mensajes', 'cdb-eventos' ),
        'manage_options',
        'cdb_eventos_mensajes',
        'cdb_eventos_mensajes_admin_page'
    );
}
add_action( 'admin_menu', 'cdb_eventos_mensajes_admin_menu' );

function cdb_eventos_mensajes_admin_enqueue( $hook ) {
    if ( 'evento_page_cdb_eventos_mensajes' !== $hook ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_style( 'cdb-eventos-config-mensajes', CDB_EVENTOS_URL . 'assets/css/config-mensajes.css', array(), '1.0' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script( 'cdb-eventos-config-mensajes', CDB_EVENTOS_URL . 'assets/js/config-mensajes.js', array( 'jquery', 'wp-color-picker' ), '1.0', true );
}
add_action( 'admin_enqueue_scripts', 'cdb_eventos_mensajes_admin_enqueue' );

function cdb_eventos_mensajes_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_POST['cdb_eventos_mensajes_nonce'] ) && wp_verify_nonce( $_POST['cdb_eventos_mensajes_nonce'], 'cdb_eventos_guardar_mensajes' ) ) {
        $mensajes = cdb_eventos_get_mensajes_default();
        if ( isset( $_POST['mensaje'] ) && is_array( $_POST['mensaje'] ) ) {
            foreach ( $mensajes as $clave => $msg ) {
                if ( isset( $_POST['mensaje'][ $clave ] ) ) {
                    $datos = $_POST['mensaje'][ $clave ];
                    $mensajes[ $clave ]['texto']     = sanitize_text_field( $datos['texto'] );
                    $mensajes[ $clave ]['secundario']= sanitize_text_field( $datos['secundario'] );
                    $mensajes[ $clave ]['tipo']      = sanitize_key( $datos['tipo'] );
                    $mensajes[ $clave ]['mostrar']   = isset( $datos['mostrar'] ) ? 1 : 0;
                }
            }
        }
        update_option( 'cdb_eventos_mensajes', $mensajes );

        $tipos = array();
        if ( isset( $_POST['tipos'] ) && is_array( $_POST['tipos'] ) ) {
            $slugs   = isset( $_POST['tipos']['slug'] ) ? (array) $_POST['tipos']['slug'] : array();
            $nombres = isset( $_POST['tipos']['name'] ) ? (array) $_POST['tipos']['name'] : array();
            $clases  = isset( $_POST['tipos']['class'] ) ? (array) $_POST['tipos']['class'] : array();
            $bg      = isset( $_POST['tipos']['bg'] ) ? (array) $_POST['tipos']['bg'] : array();
            $text    = isset( $_POST['tipos']['text'] ) ? (array) $_POST['tipos']['text'] : array();
            $bcolor  = isset( $_POST['tipos']['border_color'] ) ? (array) $_POST['tipos']['border_color'] : array();
            $bwidth  = isset( $_POST['tipos']['border_width'] ) ? (array) $_POST['tipos']['border_width'] : array();
            $bradius = isset( $_POST['tipos']['border_radius'] ) ? (array) $_POST['tipos']['border_radius'] : array();
            $count   = max( count( $slugs ), count( $nombres ) );
            for ( $i = 0; $i < $count; $i++ ) {
                if ( ! isset( $slugs[ $i ], $nombres[ $i ], $clases[ $i ], $bg[ $i ], $text[ $i ], $bcolor[ $i ], $bwidth[ $i ], $bradius[ $i ] ) ) {
                    continue;
                }
                $slug = sanitize_key( $slugs[ $i ] );
                if ( empty( $slug ) ) {
                    continue;
                }
                $bw = trim( $bwidth[ $i ] );
                $br = trim( $bradius[ $i ] );
                $tipos[ $slug ] = array(
                    'name'         => sanitize_text_field( $nombres[ $i ] ),
                    'class'        => sanitize_html_class( $clases[ $i ] ),
                    'bg'           => sanitize_hex_color( $bg[ $i ] ),
                    'text'         => sanitize_hex_color( $text[ $i ] ),
                    'border_color' => sanitize_hex_color( $bcolor[ $i ] ),
                    'border_width' => ( is_numeric( $bw ) ? absint( $bw ) . 'px' : sanitize_text_field( $bw ) ),
                    'border_radius'=> ( is_numeric( $br ) ? absint( $br ) . 'px' : sanitize_text_field( $br ) ),
                );
            }
        }
        update_option( 'cdb_eventos_tipos_color', $tipos );
        echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados.', 'cdb-eventos' ) . '</p></div>';
    }

    $mensajes = cdb_eventos_get_mensajes();
    $tipos    = cdb_eventos_get_tipos_color();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Configuración de Mensajes y Avisos', 'cdb-eventos' ); ?></h1>
        <form method="post">
            <?php wp_nonce_field( 'cdb_eventos_guardar_mensajes', 'cdb_eventos_mensajes_nonce' ); ?>
            <h2><?php esc_html_e( 'Mensajes', 'cdb-eventos' ); ?></h2>
            <table class="form-table">
                <tbody>
                <?php foreach ( $mensajes as $clave => $mensaje ) : ?>
                    <tr>
                        <th scope="row"><label for="mensaje-<?php echo esc_attr( $clave ); ?>"><?php echo esc_html( $clave ); ?></label></th>
                        <td>
                            <input type="text" id="mensaje-<?php echo esc_attr( $clave ); ?>" name="mensaje[<?php echo esc_attr( $clave ); ?>][texto]" value="<?php echo esc_attr( $mensaje['texto'] ); ?>" class="regular-text" />
                            <input type="text" name="mensaje[<?php echo esc_attr( $clave ); ?>][secundario]" value="<?php echo esc_attr( $mensaje['secundario'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Texto secundario', 'cdb-eventos' ); ?>" />
                            <select name="mensaje[<?php echo esc_attr( $clave ); ?>][tipo]">
                                <?php foreach ( $tipos as $slug => $tipo ) : ?>
                                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $mensaje['tipo'], $slug ); ?>><?php echo esc_html( $tipo['nombre'] ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label><input type="checkbox" name="mensaje[<?php echo esc_attr( $clave ); ?>][mostrar]" value="1" <?php checked( $mensaje['mostrar'], 1 ); ?> /> <?php esc_html_e( 'Mostrar', 'cdb-eventos' ); ?></label>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <h2><?php esc_html_e( 'Tipos de aviso', 'cdb-eventos' ); ?></h2>
            <table class="form-table" id="cdb-eventos-tipos">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Slug', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Nombre', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Clase', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Color', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Color del texto', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Color del borde', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Grosor', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Radio', 'cdb-eventos' ); ?></th>
                        <th><?php esc_html_e( 'Previsualización', 'cdb-eventos' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $tipos as $slug => $tipo ) : ?>
                    <tr>
                        <td><input type="text" name="tipos[slug][]" value="<?php echo esc_attr( $slug ); ?>" /></td>
                        <td><input type="text" name="tipos[name][]" value="<?php echo esc_attr( $tipo['name'] ); ?>" /></td>
                        <td><input type="text" name="tipos[class][]" value="<?php echo esc_attr( $tipo['class'] ); ?>" /></td>
                        <td><input type="text" class="cdb-color-field" name="tipos[bg][]" value="<?php echo esc_attr( $tipo['bg'] ); ?>" /></td>
                        <td><input type="text" class="cdb-color-field" name="tipos[text][]" value="<?php echo esc_attr( $tipo['text'] ); ?>" /></td>
                        <td><input type="text" class="cdb-color-field" name="tipos[border_color][]" value="<?php echo esc_attr( $tipo['border_color'] ); ?>" /></td>
                        <td><input type="text" name="tipos[border_width][]" value="<?php echo esc_attr( $tipo['border_width'] ); ?>" class="small-text" /></td>
                        <td><input type="text" name="tipos[border_radius][]" value="<?php echo esc_attr( $tipo['border_radius'] ); ?>" class="small-text" /></td>
                        <td class="cdb-preview-cell"><div class="cdb-aviso cdb-aviso-preview"><strong class="cdb-mensaje-destacado"><?php esc_html_e( 'Texto', 'cdb-eventos' ); ?></strong></div><small class="cdb-contraste-aviso" style="display:none;color:#a00;"><?php esc_html_e( 'Contraste bajo', 'cdb-eventos' ); ?></small></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button id="cdb-eventos-add-tipo" class="button"><?php esc_html_e( 'Añadir tipo', 'cdb-eventos' ); ?></button></p>

            <?php submit_button( __( 'Guardar cambios', 'cdb-eventos' ) ); ?>
        </form>
    </div>
    <?php
}
