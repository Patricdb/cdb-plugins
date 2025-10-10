<?php
/**
 * Template for integraciones page.
 *
 * @package cdb-empleado
 */
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Integraciones', 'cdb-empleado' ); ?></h1>
    <?php cdb_empleado_admin_tabs( 'cdb-empleado-integraciones' ); ?>

    <p>
        <?php esc_html_e( 'Consulta la sección de Shortcodes para ejemplos y uso de códigos abreviados.', 'cdb-empleado' ); ?>
    </p>

    <h2><?php esc_html_e( 'Hooks y filtros', 'cdb-empleado' ); ?></h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Hook/filtro', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Propósito', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Parámetros', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Retorno', 'cdb-empleado' ); ?></th>
                <th><?php esc_html_e( 'Ejemplo', 'cdb-empleado' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>cdb_empleado_inyectar_grafica</code></td>
                <td><?php esc_html_e( 'Habilita/inhibe la gráfica en single de empleado.', 'cdb-empleado' ); ?></td>
                <td><code>bool $enabled, int $empleado_id</code></td>
                <td><code>bool</code></td>
                <td>
                    <code>add_filter('cdb_empleado_inyectar_grafica','__return_false');</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_inyectar_grafica','__return_false');">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_empleado_inyectar_calificacion</code></td>
                <td><?php esc_html_e( 'Controla el bloque de calificación.', 'cdb-empleado' ); ?></td>
                <td><code>bool $enabled, int $empleado_id</code></td>
                <td><code>bool</code></td>
                <td>
                    <code>add_filter('cdb_empleado_inyectar_calificacion','__return_false');</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_inyectar_calificacion','__return_false');">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_grafica_empleado_html</code></td>
                <td><?php esc_html_e( 'Sustituye el HTML de la gráfica.', 'cdb-empleado' ); ?></td>
                <td><code>string $html, int $empleado_id, array $args</code></td>
                <td><code>string</code></td>
                <td>
                    <code>add_filter('cdb_grafica_empleado_html','mi_callback',10,3);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_grafica_empleado_html','mi_callback',10,3);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_grafica_empleado_form_html</code></td>
                <td><?php esc_html_e( 'Personaliza el formulario de calificación.', 'cdb-empleado' ); ?></td>
                <td><code>string $html, int $empleado_id, array $args</code></td>
                <td><code>string</code></td>
                <td>
                    <code>add_filter('cdb_grafica_empleado_form_html','mi_form',10,3);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_grafica_empleado_form_html','mi_form',10,3);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_grafica_empleado_scores_table_html</code></td>
                <td><?php esc_html_e( 'Ajusta la tabla de puntuaciones.', 'cdb-empleado' ); ?></td>
                <td><code>string $html, int $empleado_id, array $args</code></td>
                <td><code>string</code></td>
                <td>
                    <code>add_filter('cdb_grafica_empleado_scores_table_html','mi_tabla',10,3);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_grafica_empleado_scores_table_html','mi_tabla',10,3);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_grafica_empleado_total</code></td>
                <td><?php esc_html_e( 'Modifica el puntaje total mostrado.', 'cdb-empleado' ); ?></td>
                <td><code>float $total, int $empleado_id</code></td>
                <td><code>float</code></td>
                <td>
                    <code>add_filter('cdb_grafica_empleado_total','mi_total',10,2);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_grafica_empleado_total','mi_total',10,2);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_empleado_use_new_card</code></td>
                <td><?php esc_html_e( 'Decide uso de tarjeta alternativa.', 'cdb-empleado' ); ?></td>
                <td><code>bool $use_new, int $empleado_id</code></td>
                <td><code>bool</code></td>
                <td>
                    <code>add_filter('cdb_empleado_use_new_card','__return_true');</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_use_new_card','__return_true');">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_grafica_empleado_notice</code></td>
                <td><?php esc_html_e( 'Personaliza aviso en la sección de calificación.', 'cdb-empleado' ); ?></td>
                <td><code>string $msg, int $empleado_id</code></td>
                <td><code>string</code></td>
                <td>
                    <code>add_filter('cdb_grafica_empleado_notice','mi_aviso',10,2);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_grafica_empleado_notice','mi_aviso',10,2);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_empleado_card_data</code></td>
                <td><?php esc_html_e( 'Filtra datos mostrados en la tarjeta.', 'cdb-empleado' ); ?></td>
                <td><code>array $data, int $empleado_id</code></td>
                <td><code>array</code></td>
                <td>
                    <code>add_filter('cdb_empleado_card_data','mi_data',10,2);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_card_data','mi_data',10,2);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_empleado_rank_ttl</code></td>
                <td><?php esc_html_e( 'Ajusta duración del caché de rankings.', 'cdb-empleado' ); ?></td>
                <td><code>int $seconds</code></td>
                <td><code>int</code></td>
                <td>
                    <code>add_filter('cdb_empleado_rank_ttl', fn()=>300);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_rank_ttl', fn()=>300);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <td><code>cdb_empleado_rank_current</code></td>
                <td><?php esc_html_e( 'Modifica ranking calculado para un empleado.', 'cdb-empleado' ); ?></td>
                <td><code>mixed $rank, int $empleado_id</code></td>
                <td><code>mixed</code></td>
                <td>
                    <code>add_filter('cdb_empleado_rank_current','mi_rank',10,2);</code>
                    <button type="button" class="button cdb-copy" data-copy="add_filter('cdb_empleado_rank_current','mi_rank',10,2);">
                        <?php esc_html_e( 'Copiar', 'cdb-empleado' ); ?>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>

    <p>
        <?php esc_html_e( 'Más información en:', 'cdb-empleado' ); ?>
        <a href="https://github.com/proyectocdb/cdb-empleado#hooks-y-filtros" target="_blank"><?php esc_html_e( 'README – Hooks y filtros', 'cdb-empleado' ); ?></a>,
        <a href="https://developer.wordpress.org/plugins/hooks/" target="_blank"><?php esc_html_e( 'Hooks en WordPress', 'cdb-empleado' ); ?></a>.
    </p>
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
