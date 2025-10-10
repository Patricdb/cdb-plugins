<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

global $wpdb;
$tables = [
    $wpdb->prefix . 'grafica_bar_results',
    $wpdb->prefix . 'grafica_empleado_results',
];
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

