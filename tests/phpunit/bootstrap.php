<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package BlockInvokers
 */

declare(strict_types = 1);

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "$_tests_dir/includes/functions.php" ) ) {
	echo "Could not find $_tests_dir/includes/functions.php. Set WP_TESTS_DIR, or run the tests through wp-env." . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

require_once "$_tests_dir/includes/functions.php";

/**
 * Loads the plugin under test.
 *
 * @return void
 */
function _manually_load_plugin(): void {
	require dirname( __DIR__, 2 ) . '/block-invokers.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require "$_tests_dir/includes/bootstrap.php";
