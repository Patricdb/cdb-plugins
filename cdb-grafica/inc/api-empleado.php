<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// inc/api-empleado.php

/**
 * Obtiene el total de puntuación para el rol empleado.
 *
 * @param int $empleado_id ID del empleado.
 * @return float Total de puntuación.
 */
function cdb_grafica_get_empleado_total( int $empleado_id ): float {
    if ( $empleado_id <= 0 ) {
        return 0.0;
    }

    $cache_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_etotal_{$empleado_id}", $empleado_id, 'empleado_total' );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return (float) $cached;
    }

    $scores = cdb_grafica_get_scores_by_role( $empleado_id, [ 'role' => 'empleado', 'with_raw' => true ] );
    $total  = 0.0;
    if ( isset( $scores['raw']['empleado']['total'] ) ) {
        $total = (float) $scores['raw']['empleado']['total'];
    }

    $total = apply_filters( 'cdb_grafica_empleado_total', $total, $empleado_id );

    $ttl = (int) apply_filters( 'cdb_grafica_scores_ttl', 600, $empleado_id );
    set_transient( $cache_key, $total, $ttl );

    return $total;
}

/**
 * Obtiene los promedios por grupo para el rol empleado.
 *
 * @param int $empleado_id ID del empleado.
 * @return array Promedios por grupo.
 */
function cdb_grafica_get_empleado_group_avgs( int $empleado_id ): array {
    if ( $empleado_id <= 0 ) {
        return [];
    }

    $cache_key = apply_filters( 'cdb_grafica_transient_key', "cdbg_eavgs_{$empleado_id}", $empleado_id, 'empleado_group_avgs' );
    $cached    = get_transient( $cache_key );
    if ( false !== $cached ) {
        return (array) $cached;
    }

    $scores = cdb_grafica_get_scores_by_role( $empleado_id, [ 'role' => 'empleado', 'with_raw' => true ] );
    $avgs   = [];
    if ( isset( $scores['raw']['empleado']['grupos'] ) && is_array( $scores['raw']['empleado']['grupos'] ) ) {
        $avgs = $scores['raw']['empleado']['grupos'];
    }

    $avgs = apply_filters( 'cdb_grafica_empleado_group_avgs', $avgs, $empleado_id );

    $ttl = (int) apply_filters( 'cdb_grafica_scores_ttl', 600, $empleado_id );
    set_transient( $cache_key, $avgs, $ttl );

    return $avgs;
}
