<?php
/**
 * Template for shortcodes page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Shortcodes', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-shortcodes' ); ?>
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Shortcode', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Atributos', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Descripción', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Ejemplo', 'cdb-empleado' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $shortcodes as $sc ) : ?>
                <tr>
                    <td><code><?php echo esc_html( $sc['tag'] ); ?></code></td>
                    <td>
                        <?php foreach ( $sc['atts'] as $att => $label ) : ?>
                            <code><?php echo esc_html( $att ); ?></code> - <?php echo esc_html( $label ); ?><br />
                        <?php endforeach; ?>
                    </td>
                    <td><?php echo esc_html( $sc['desc'] ); ?></td>
                    <td>
                        <code><?php echo esc_html( $sc['example'] ); ?></code>
                        <button type="button" class="button cdb-copy" data-copy="<?php echo esc_attr( $sc['example'] ); ?>">
                            <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('cdb-copy')) {
            navigator.clipboard.writeText(e.target.dataset.copy);
            const original = e.target.textContent;
            e.target.textContent = '<?php echo esc_js( __( 'Copiado!', 'cdb-empleado' ) ); ?>';
            setTimeout(function(){ e.target.textContent = original; }, 2000);
        }
    });
    </script>
</div>
