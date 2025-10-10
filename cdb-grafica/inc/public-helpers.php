<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Devuelve la fecha/hora (Y-m-d H:i:s) de la última valoración para un empleado.
 *
 * @param int $empleado_id ID del empleado.
 * @return string|null Fecha/hora de la última valoración o null si no hay registros.
 */
function cdb_grafica_get_last_rating_datetime( int $empleado_id ): ?string {
    if ( $empleado_id <= 0 ) {
        return null;
    }

    $cache_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_last_{$empleado_id}", $empleado_id, 'last' );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return '' === $cached ? null : $cached;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    $sql_parts = apply_filters( 'cdb_grafica_last_rating_args', [
        'where' => '',
        'order' => '',
    ], $empleado_id );

    $sql = "SELECT MAX(created_at) FROM {$table} WHERE post_id = %d AND user_role IS NOT NULL";
    if ( ! empty( $sql_parts['where'] ) ) {
        $sql .= " AND {$sql_parts['where']}";
    }
    if ( ! empty( $sql_parts['order'] ) ) {
        $sql .= " {$sql_parts['order']}";
    }

    $datetime = $wpdb->get_var( $wpdb->prepare( $sql, $empleado_id ) );

    $ttl = (int) apply_filters( 'cdb_grafica_last_rating_ttl', 600, $empleado_id );
    set_transient( $cache_key, $datetime ? $datetime : '', $ttl );

    return $datetime ?: null;
}

/**
 * Devuelve totales de puntuación por rol y detalle opcional por grupos.
 *
 * @param int   $empleado_id ID del empleado.
 * @param array $args        Opciones: with_raw, bypass_cache.
 * @return array             Puntuaciones por rol y detalle si se solicita.
 */
function cdb_grafica_get_scores_by_role( int $empleado_id, array $args = [] ): array {
    $defaults = [
        'bypass_cache' => false,
        'with_raw'     => false,
    ];
    $args = wp_parse_args( $args, $defaults );

    $resultado_base = [ 'empleado' => 0.0, 'empleador' => 0.0, 'tutor' => 0.0 ];

    if ( $empleado_id <= 0 ) {
        if ( $args['with_raw'] ) {
            $resultado_base['raw'] = [];
        }
        return $resultado_base;
    }

    $transient_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_scores_{$empleado_id}", $empleado_id, 'scores' );
    if ( ! $args['bypass_cache'] ) {
        $cached = get_transient( $transient_key );
        if ( false !== $cached ) {
            return $cached;
        }
    }

    global $wpdb;
    $table = $wpdb->prefix . 'grafica_empleado_results';

    $criterios = cdb_get_criterios_empleado();
    $grupos    = [];
    $columnas  = [];
    foreach ( $criterios as $grupo_nombre => $campos ) {
        $grupos[ $grupo_nombre ] = array_keys( $campos );
        foreach ( $campos as $campo_slug => $info ) {
            $columnas[] = $campo_slug;
        }
    }
    $columnas    = array_unique( $columnas );
    $select_cols = implode( ', ', array_merge( $columnas, [ 'created_at', 'user_role' ] ) );

    $roles     = [ 'empleado', 'empleador', 'tutor' ];
    $resultado = $resultado_base;
    $detalle   = [];

    foreach ( $roles as $rol ) {
        $sql_parts = apply_filters( 'cdb_grafica_scores_args', [
            'where' => '',
            'order' => '',
        ], $rol, $empleado_id );

        $sql = "SELECT {$select_cols} FROM {$table} WHERE post_id = %d AND user_role = %s";
        if ( ! empty( $sql_parts['where'] ) ) {
            $sql .= " AND {$sql_parts['where']}";
        }
        if ( ! empty( $sql_parts['order'] ) ) {
            $sql .= " {$sql_parts['order']}";
        }

        $rows = $wpdb->get_results( $wpdb->prepare( $sql, $empleado_id, $rol ) );
        if ( empty( $rows ) ) {
            if ( $args['with_raw'] ) {
                $detalle[ $rol ] = [ 'grupos' => [], 'total' => 0.0 ];
            }
            continue;
        }

        $grupos_data = [];
        foreach ( $rows as $row ) {
            foreach ( $grupos as $grupo_nombre => $campos ) {
                if ( ! isset( $grupos_data[ $grupo_nombre ] ) ) {
                    $grupos_data[ $grupo_nombre ] = [ 'suma' => 0, 'cuenta' => 0 ];
                }
                foreach ( $campos as $campo ) {
                    if ( isset( $row->$campo ) && $row->$campo > 0 ) {
                        $grupos_data[ $grupo_nombre ]['suma']   += (float) $row->$campo;
                        $grupos_data[ $grupo_nombre ]['cuenta'] += 1;
                    }
                }
            }
        }

        $promedios = [];
        foreach ( $grupos as $grupo_nombre => $campos ) {
            $suma   = $grupos_data[ $grupo_nombre ]['suma'] ?? 0;
            $cuenta = $grupos_data[ $grupo_nombre ]['cuenta'] ?? 0;
            $avg    = $cuenta > 0 ? round( $suma / $cuenta, 1 ) : 0.0;
            $codigo = strtok( $grupo_nombre, ' ' );
            $promedios[ $codigo ] = $avg;
        }

        $total = round( array_sum( $promedios ), 1 );
        $resultado[ $rol ] = $total;

        if ( $args['with_raw'] ) {
            $detalle[ $rol ] = [ 'grupos' => $promedios, 'total' => $total ];
        }
    }

    if ( $args['with_raw'] ) {
        $resultado['raw'] = $detalle;
    }

    $ttl = (int) apply_filters( 'cdb_grafica_scores_ttl', 600, $empleado_id );
    if ( ! $args['bypass_cache'] ) {
        set_transient( $transient_key, $resultado, $ttl );
    }

    return $resultado;
}

/**
 * Normaliza una puntuación a un porcentaje.
 *
 * @param float $score Puntuación a normalizar.
 * @param array $args  Opcional. {
 *     @type float  $max  Máximo para el 100%. Por defecto 40 filtrable mediante cdb_grafica_width_max.
 *     @type string $role Rol actual, si aplica.
 * }
 * @return float Porcentaje normalizado con dos decimales.
 * @since 1.2.0
 */
function cdb_grafica_get_width_pct_from_score( float $score, array $args = [] ): float {
    $max = $args['max'] ?? apply_filters( 'cdb_grafica_width_max', 40, $args );
    return max( 0, min( 100, round( ( $score / max( 1, $max ) ) * 100, 2 ) ) );
}

/**
 * Obtiene el color configurado para un rol y tipo dados.
 *
 * @param string $role Rol (empleado|empleador|tutor).
 * @param string $type Tipo de color: background o border. Predeterminado background.
 * @return string Color solicitado.
 * @since 1.2.0
 */
function cdb_grafica_get_color_by_role( string $role, string $type = 'background' ): string {
    $defaults = [
        'empleado_background'  => 'rgba(75, 192, 192, 0.2)',
        'empleado_border'      => 'rgba(75, 192, 192, 1)',
        'empleador_background' => 'rgba(54, 162, 235, 0.2)',
        'empleador_border'     => 'rgba(54, 162, 235, 1)',
        'tutor_background'     => 'rgba(255, 99, 132, 0.2)',
        'tutor_border'         => 'rgba(255, 99, 132, 1)',
    ];
    $opts  = get_option( 'cdb_grafica_colores', $defaults );
    $role  = strtolower( $role );
    $key   = "{$role}_{$type}";
    $value = $opts[ $key ] ?? $defaults[ $key ] ?? '';

    return apply_filters( 'cdb_grafica_color_by_role', $value, $role, $type, $opts );
}

add_action(
    'cdb_grafica_after_save',
    function ( int $empleado_id ): void {
        $scores_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_scores_{$empleado_id}", $empleado_id, 'scores' );
        $last_key   = apply_filters( 'cdb_grafica_transient_key', "cdbg_last_{$empleado_id}", $empleado_id, 'last' );
        $etotal_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_etotal_{$empleado_id}", $empleado_id, 'empleado_total' );
        $eavgs_key  = apply_filters( 'cdb_grafica_transient_key', "cdbg_eavgs_{$empleado_id}", $empleado_id, 'empleado_group_avgs' );

        delete_transient( $scores_key );
        delete_transient( $last_key );
        delete_transient( $etotal_key );
        delete_transient( $eavgs_key );
    }
);

/**
 * Filters the HTML of an employee chart.
 *
 * @param string $html        HTML result.
 * @param int    $empleado_id Employee ID.
 * @param array  $attrs       Optional attributes.
 *
 * @return string
 */
/*
 * apply_filters( 'cdb_grafica_empleado_html', string $html, int $empleado_id, array $attrs = [] )
 */

/**
 * Filters the calculated total score for an employee.
 *
 * @param float $total       Total score.
 * @param int   $empleado_id Employee ID.
 *
 * @return float
 */
/*
 * apply_filters( 'cdb_grafica_empleado_total', float $total, int $empleado_id )
 */

/**
 * Filters the notice message shown in the rating form.
 *
 * @param string $notice      Default notice message.
 * @param int    $empleado_id Employee ID.
 *
 * @return string
 */
/*
 * apply_filters( 'cdb_grafica_empleado_notice', string $notice, int $empleado_id )
 */

