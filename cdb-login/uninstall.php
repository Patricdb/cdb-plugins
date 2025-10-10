<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}
require_once __DIR__ . '/cdb-login.php';
cdb_login_uninstall();
