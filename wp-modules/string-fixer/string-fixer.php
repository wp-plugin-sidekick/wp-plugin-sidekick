<?php
/**
 * Module Name: String Fixer
 * Description: This module contains functions for scanning directories, and ensuring strings as are they should be for plugins and modules.
 *
 * @package WPPS
 */

declare(strict_types=1);

namespace WPPS\StringFixer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fix main plugin file strings.
 *
 * @param string $file The incoming contents of the file we are fixing the header of.
 * @param array  $strings The relevant strings used to create the plugin file header.

 */
function fix_plugin_strings( string $file, array $strings ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	// Open the file.
	$file_contents = $wp_filesystem->get_contents( $file );

	$file_contents = fix_plugin_file_header( $file_contents, $strings );
	$file_contents = fix_plugin_namespace( $file_contents, $strings['plugin_namespace'] );

	$wp_filesystem->put_contents( $file, $file_contents );
}

/**
 * Recursively loop through a directories files, fixing strings as we go.
 *
 * @param string $dir The directory where strings will be recursively replaced.
 * @param array  $strings The relevant strings used to create the plugin file header.
 */
function recursive_module_string_fixer( string $dir, array $strings ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	$dir_list      = glob( $dir . '/*' );
	$result        = false;

	// Loop through all files.
	foreach ( $dir_list as $dir_file ) {

		// Rename strings.
		$result = fix_module_strings( $dir_file, $strings );
		
		// If this is a directory, loop through it.
		if ( $wp_filesystem->is_dir( $dir_file ) ) {
			if (
				strpos( $dir_file, 'node_modules') === false &&
				strpos( $dir_file, 'vendor') === false
			) {
				recursive_module_string_fixer( $dir_file, $strings );
			}
		}
	}

	return $result;
}

/**
 * Fix strings in a module.
 *
 * @param string $file The incoming contents of the file we are fixing the header of.
 * @param array  $strings The relevant strings used to create the plugin file header.
 */
function fix_module_strings( string $file, array $strings ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	// Open the file.
	$file_contents = $wp_filesystem->get_contents( $file );

	$file_contents = fix_module_file_header( $file_contents, $strings );
	$file_contents = fix_module_namespace( $file_contents, $strings['plugin_namespace'], $strings['module_namespace'] );
	$file_contents = fix_package_tag( $file_contents, $strings['plugin_dirname'] );

	$wp_filesystem->put_contents( $file, $file_contents );

	return true;
}

/**
 * Rewrite a Plugin File header, and return the file contents.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param array  $strings The relevant strings used to create the plugin file header.
 */
function fix_plugin_file_header( string $file_contents, array $strings ) {
	$fixed_file_header = '/**
 * Plugin Name: ' . $strings['plugin_name'] . '
 * Plugin URI: ' . $strings['plugin_uri'] . '
 * Description: ' . $strings['plugin_description'] . '
 * Version: ' . $strings['plugin_version'] . '
 * Author: ' . $strings['author'] . '
 * Text Domain: ' . $strings['plugin_textdomain'] . '
 * Domain Path: languages
 * License: ' . $strings['plugin_license'] . '
 * Namespace: ' . $strings['plugin_namespace'] . '
 *
 * @package ' . $strings['plugin_dirname'] . '
 */';

	$pattern = '~\/\*\*[^*] \* Plugin Name:[^;]*\*/~';

	// Find the file header.
	$match_found = preg_match( $pattern, $file_contents, $matches );

	// Replace it if found.
	if ( $match_found ) {
		$file_contents = str_replace( $matches[0], $fixed_file_header, $file_contents );
	}

	return $file_contents;
}

/**
 * Get a plugin's namespace.
 *
 * @param string $plugin_dirname The directory name of the plugin in question.
 */
function get_plugin_namespace( string $plugin_dirname ) {
	// Check if get_plugins() function exists. This is required on the front end of the
	// site, since it is in a file that is normally only loaded in the admin.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins = \get_plugins();

	foreach ( $installed_plugins as $key => $installed_plugin ) {
		$dirname = basename(
			$key, // Get the key which holds the folder/file name.
			'.php' // Strip away the .php part.
		);
		
		if ( $dirname === $plugin_dirname ) {
			return $installed_plugin['Namespace'];
		}
	}

	return false;
}

/**
 * Rewrite the namespace definition.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param string $namespace The namespace to use.
 */
function fix_plugin_namespace( string $file_contents, string $namespace ) {

	$pattern = '~namespace .+?(?=;|\\\\)~';
	$fixed   = 'namespace ' . $namespace;

	// Find the namespace deifition.
	$match_found = preg_match( $pattern, $file_contents, $matches );

	// Replace namespace if found.
	if ( $match_found ) {
		$file_contents = str_replace( $matches[0], $fixed, $file_contents );
	}

	return $file_contents;
}

/**
 * Rewrite the namespace definition for a module.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param string $plugin_namespace The namespace of the plugin (top level).
 * @param string $module_namespace The namespace of the module (second level).
 */
function fix_module_namespace( string $file_contents, string $plugin_namespace, string $module_namespace ) {

	$pattern = '~namespace .+?(?=;)~';
	$fixed   = 'namespace ' . $plugin_namespace . '\\' . $module_namespace;

	// Find the namespace deifition.
	$match_found = preg_match_all( $pattern, $file_contents, $matches );

	// Replace namespace if found.
	if ( $match_found ) {
		$file_contents = str_replace( $matches[0], $fixed, $file_contents );
	}

	return $file_contents;
}

/**
 * Rewrite a Module File header, and return the file contents.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param array  $strings The relevant strings used to create the module file header.
 */
function fix_module_file_header( string $file_contents, array $strings ) {
	if ( ! empty( $strings['module_namespace'] ) ) {
		$fixed_file_header = '/**
 * Module Name: ' . $strings['module_name'] . '
 * Description: ' . $strings['module_description'] . '
 * Namespace: ' . $strings['module_namespace'] . '
 *
 * @package ' . $strings['plugin_dirname'] . '
 */';
	} else {
		$fixed_file_header = '/**
 * Module Name: ' . $strings['module_name'] . '
 * Description: ' . $strings['module_description'] . '
 *
 * @package ' . $strings['plugin_dirname'] . '
 */';
	}
	$pattern = '~\/\*\*[^*] \* Module Name:[^;]*\*/~';

	// Find the file header.
	$file_contents = preg_replace( $pattern, $fixed_file_header, $file_contents );

	return $file_contents;
}

/**
 * Rewrite a file headers "@package" tag to ensure it is correct.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param string $package TThe string to use for the package tag.
 */
function fix_package_tag( string $file_contents, string $package ) {
	$fixed = '* @package ' . $package;

	$pattern = '~\* @package .*~';

	// Find the file header.
	$file_contents = preg_replace( $pattern, $fixed, $file_contents );

	return $file_contents;
}

/**
 * The default args for a plugin header.
 */
function default_plugin_args() {
	return array(
		'plugin_name'        => '',
		'plugin_dirname'     => '',
		'plugin_textdomain'  => '',
		'plugin_namespace'   => '',
		'plugin_description' => '',
		'plugin_version'     => '1.0.0',
		'plugin_author'      => '',
		'plugin_uri'         => '',
		'min_wp_version'     => '',
		'min_php_version'    => '',
		'plugin_license'     => 'GPLv2 or later',
		'update_uri'         => '',
	);
}


/**
 * The default args for a plugin header.
 */
function default_module_args() {
	return array(
		'module_name'        => '',
		'module_namespace'   => '',
		'module_description' => ''
	);
}

/**
 * Filter in that we would like to include "namespace" in plugin header data retrieval.
 *
 * @param string $plugin_dirname The directory name of the plugin in question.
 */
function add_namespace_as_plugin_header( $additional_plugin_headers ) {
	$additional_plugin_headers['Namespace'] = 'Namespace';
	return $additional_plugin_headers;
}
add_filter( 'extra_plugin_headers', __NAMESPACE__ . '\add_namespace_as_plugin_header' );
