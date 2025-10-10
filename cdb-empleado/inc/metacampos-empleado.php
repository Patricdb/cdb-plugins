<?php
/**
 * Metacampos para el CPT Empleado
 */

if (!defined('ABSPATH')) {
    exit; // Evitar acceso directo
}

/**
 * Registrar metacampos para empleados
 */
function cdb_empleado_registrar_metacampos() {
    add_meta_box(
        'cdb_empleado_info',
        'Información del Empleado',
        'cdb_empleado_mostrar_metacampos',
        'empleado',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'cdb_empleado_registrar_metacampos');

/**
 * Mostrar los metacampos en el editor de empleados
 */
function cdb_empleado_mostrar_metacampos($post) {
    $disponible = get_post_meta($post->ID, 'disponible', true);
    if ('' === $disponible) {
        $disponible = (string) get_option('default_disponible', 1);
    }
    $label = get_option('label_disponible', 'Disponible');

    wp_nonce_field('cdb_empleado_guardar_metacampos', 'cdb_empleado_nonce');

    ?>
    <p>
        <label for="disponible"><strong><?php echo esc_html($label); ?>:</strong></label>
        <select name="disponible" id="disponible">
            <option value="1" <?php selected($disponible, '1'); ?>><?php esc_html_e('Sí', 'cdb-empleado'); ?></option>
            <option value="0" <?php selected($disponible, '0'); ?>><?php esc_html_e('No', 'cdb-empleado'); ?></option>
        </select>
    </p>
    <?php
}

/**
 * Guardar los metacampos cuando se actualiza el empleado
 */
function cdb_empleado_guardar_metacampos($post_id) {
    if (!isset($_POST['cdb_empleado_nonce']) || !wp_verify_nonce($_POST['cdb_empleado_nonce'], 'cdb_empleado_guardar_metacampos')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, 'disponible', sanitize_text_field($_POST['disponible']));
}
add_action('save_post', 'cdb_empleado_guardar_metacampos');
