<?php

/**
 * Module Name: Module Data Functions
 * Description: This module contains functions for getting data about modules
 * Namespace: ModuleDataFunctions
 *
 * @package wp-plugin-studio
 */

declare(strict_types=1);

namespace WPPS\ModuleDataFunctions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function module_data() {
	return [
		'dir' => plugin_dir_path( __FILE__ ),
		'url' => plugin_dir_url( __FILE__ ),
	];
}


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

	$module_data['slug'] = basename( $file, '.php' );

	return $module_data;

}

/**
 * Get the list of manageable plugins that currently exist.
 *
 * @since 1.0
 * @return void
 */
function get_plugin_modules( $plugin_dirname ) {
	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	$modules = array();

	$plugin_path = $wp_filesystem_api->wp_plugins_dir() . $plugin_dirname;

	$modules_glob = glob( $plugin_path . '/wp-modules/*' );

	foreach ( $modules_glob as $module ) {
		$module_name = basename( $module );
		$filename    = $module_name . '.php';
		$filepath    = $module . '/' . $filename;
		$module_data = get_module_data( $filepath );

		$modules[ $module_name ] = $module_data;
	}

	return $modules;
}


/**
 * Get the module boilers available.
 *
 * @since 1.0
 * @return void
 */
function get_module_boilers() {

	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	$module_boilers = array();

	$plugin_path = $wp_filesystem_api->wp_plugins_dir() . 'wp-plugin-studio';

	$modules_glob = glob( $plugin_path . '/wp-modules/module-boilers/module-boilers/*' );

	foreach ( $modules_glob as $module ) {
		$module_name = basename( $module );
		$filename    = $module_name . '.php';
		$filepath    = $module . '/' . $filename;
		$module_data = get_module_data( $filepath );

		$module_boilers[ $module_name ] = $module_data;
	}

	return $module_boilers;
}