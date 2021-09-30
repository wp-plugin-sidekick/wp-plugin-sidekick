<?php
/*
Plugin Name: WP Plugin Studio
Plugin URI: https://wppluginstudio.com
Description: An interface for building WordPress plugins and themes.
Version: 1.0.0.0
Author: johnstonphilip
Text Domain: wpps
Domain Path: languages
License: GPLv3
*/

/*
Copyright 2021  AddOn Builder  (email : johnstonphilip@gmail.com)

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>
*/

namespace WPPS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Automatically include any "WPPS Modules", which sit in the "custom-modules" directory, and have a file called "index.php".
 *
 * @since 1.0
 * @return void
 */
function include_add_on_modules() {

	global $wpps_modules;

	$add_on_modules = glob( plugin_dir_path( __FILE__ ) . 'custom-modules/*' );

	foreach ( $add_on_modules as $add_on_module ) {

		$module_name = basename( $add_on_module );

		// Store the data about this module in a global so it can be accessed from other functions.
		$wpps_modules[ $module_name ] = array(
			'path' => plugin_dir_path( $add_on_module ),
			'url'  => plugin_dir_path( $add_on_module ),
		);

		$filename = $module_name . '.php';
		$filepath = $add_on_module . '/' . $filename;

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
add_action( 'plugins_loaded', __NAMESPACE__ . '\include_add_on_modules' );

/**
 * Setup plugin constants.
 *
 * @since 1.0
 * @return void
 */
function setup_constants() {

	// Plugin version.
	if ( ! defined( 'wpps_VERSION' ) ) {

		$wpps_version = '1.0.0.0';

		// If SCRIPT_DEBUG is enabled, break the browser cache.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			define( 'wpps_VERSION', $wpps_version . time() );
		} else {
			define( 'wpps_VERSION', $wpps_version );
		}
	}

	// Plugin Folder Path.
	if ( ! defined( 'wpps_PLUGIN_DIR' ) ) {
		define( 'wpps_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	// Plugin Folder URL.
	if ( ! defined( 'wpps_PLUGIN_URL' ) ) {
		define( 'wpps_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	// Plugin Root File.
	if ( ! defined( 'wpps_PLUGIN_FILE' ) ) {
		define( 'wpps_PLUGIN_FILE', __FILE__ );
	}

}
setup_constants();

function get_module_data( $file ) {

	$all_headers = array(
		'name'        => 'Module Name',
		'version'     => 'Version',
		'description' => 'Description',
		'author'      => 'Author',
		'authoruri'   => 'Author URI',
		'textdomain'  => 'Text Domain',
		'requireswp'  => 'Requires at least',
		'requiresphp' => 'Requires PHP',
		'namespace'   => 'Namespace',
	);

	$module_data = \get_file_data( $file, $all_headers );

	// Append the directory path.
	$module_data['dir'] = plugin_dir_path( $file );
	$module_data['url'] = plugin_dir_url( $file );

	return $module_data;

}


function module_data( $module_name ) {

	global $wpps_modules;

	foreach ( $wpps_modules as $wpps_module_name => $wpps_module_data ) {
		if ( $module_name === $wpps_module_name ) {
			return $wpps_module_data;
		}
	}

	return false;
}
