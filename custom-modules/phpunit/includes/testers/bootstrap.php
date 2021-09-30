<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Plugin_Security_Scanner
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
require_once dirname( dirname( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) ) ) . '/vendor/autoload.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$plugins = glob( '/wordpress/plugins/*' );

	print_r( $plugins );
	foreach ( $plugins as $plugin ) {
		$plugin_name = basename( $plugin );
		$filename = $plugin_name . '.php';
		$filepath = $plugin . '/' . $filename;
		
		if ( is_readable( $filepath ) ) {
			require $filepath;
		}
		
	}

}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

tests_add_filter('wp_die_handler', function () {
	exit(1);
});

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
