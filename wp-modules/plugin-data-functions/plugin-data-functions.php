<?php
/**
 * Module Name: Plugin Data Functions
 * Description: This module contains functions for getting and setting plugin data.
 * Namespace: PluginDataFunctions
 *
 * @package WPPS
 */

declare(strict_types=1);

namespace WPPS\PluginDataFunctions;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The default args for a plugin header.
 */
function default_plugin_args() {
	return array(
		'plugin_dirname'     => '',
		'plugin_name'        => '',
		'plugin_uri'         => '',
		'plugin_description' => '',
		'plugin_version'     => '1.0.0',
		'plugin_author'      => '',
		'plugin_author_uri'  => '',
		'plugin_textdomain'  => '',
		'plugin_license'     => 'GPLv2 or later',
		'plugin_license_uri' => '',
		'plugin_min_wp_version'     => '',
		'plugin_min_php_version'    => '',
		'plugin_network'    => '',
		'plugin_update_uri'         => '',
		'plugin_namespace'   => '',
		'plugin_modules'   => array(),
	);
}

/**
 * Get the list of plugins that currently exist.
 *
 * @param string $plugin_dirname The name of the directory of the plugin in question.
 * @return array
 */
function get_plugin_data( $plugin_dirname ) {
	$all_plugins = get_all_plugins();

	if ( $all_plugins[ $plugin_dirname ] ) {
		return $all_plugins[ $plugin_dirname ];
	}

	return false;
}

/**
 * Get the list of plugins that currently exist.
 *
 * @since 1.0
 * @return array
 */
function get_all_plugins() {
	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	$plugins_path      = $wp_filesystem_api->wp_plugins_dir();
	$plugins_glob      = glob( $plugins_path . '*' );
	$all_plugins_data  = array();

	// Create the map of file header keys to standardized internal value keys.
	$all_headers = array(
		'plugin_dirname'     => '',
		'plugin_name'        => 'Plugin Name',
		'plugin_uri'         => 'Plugin URI',
		'plugin_description' => 'Description',
		'plugin_version'     => 'Version',
		'plugin_author'      => 'Author',
		'plugin_author_uri'  => 'Author URI',
		'plugin_textdomain'  => 'Text Domain',
		'plugin_license'     => 'License',
		'plugin_license_uri' => 'License URI',
		'plugin_min_wp_version'     => 'Requires at least',
		'plugin_min_php_version'    => 'Requires PHP',
		'plugin_network'    => 'Network',
		'plugin_update_uri'         => 'Update URI',
		'plugin_namespace'   => 'Namespace',
	);

	// Loop through each directory in the plugins directory.
	foreach ( $plugins_glob as $plugin_dir ) {
		$plugin_dirname = basename( $plugin_dir );
		$filepath       = $plugin_dir . '/' . $plugin_dirname . '.php';

		if ( ! is_readable( $filepath ) ) {
			continue;
		}

		$plugin_data                   = \get_file_data( $filepath, $all_headers );
		$plugin_data['plugin_dirname'] = $plugin_dirname;
		$plugin_data['plugin_modules'] = \WPPS\ModuleDataFunctions\get_plugin_modules( $plugin_dirname );

		// Add this plugin's data to the main plugins array.
		$all_plugins_data[ $plugin_dirname ] = $plugin_data;
	}

	return $all_plugins_data;
}
