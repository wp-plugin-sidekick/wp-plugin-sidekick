<?php
/**
 * Plugin Name: Plugin Boiler
 * Plugin URI: https://wppluginstudio.com
 * Description: Plugin Boiler Description
 * Version: 1.0.0
 * Author: pluginboilerauthor
 * Text Domain: wpps
 * Domain Path: languages
 * License: GPLv2 or later
 *
 * @package Plugin_Boiler
 */

namespace WPPS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatically include custom modules, which sit in the "custom-modules" directory.
 *
 * @return void
 */
function include_custom_modules() {

	$custom_modules = glob( plugin_dir_path( __FILE__ ) . 'custom-modules/*' );

	foreach ( $custom_modules as $custom_module ) {

		$module_name = basename( $custom_module );
		$filename    = $module_name . '.php';
		$filepath    = $custom_module . '/' . $filename;

		if ( is_readable( $filepath ) ) {
			// If the module data exists, load it.
			require $filepath;
		} else {
			// Translators: The name of the module, and the filename that needs to exist inside that module.
			echo esc_html( sprintf( __( 'The module called "%1$s" has a problem. It needs a file called "%2$s" to exist in its root directory.', 'wpps' ), $module_name, $filename ) );
			exit;
		}
	}

}
add_action( 'plugins_loaded', __NAMESPACE__ . '\include_custom_modules' );
