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
 * Recursively loop through a directories files, fixing strings as we go.
 *
 * @param string $dir The directory where strings will be recursively replaced.
 * @param array  $strings The relevant strings used to create the plugin file header.
 * @param string $mode Either 'module' or 'plugin' to indicate the type off strings to fix.
 */
function recursive_dir_string_fixer( string $dir, array $strings, string $mode ) {

	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	$dir_list      = glob( $dir . '/*' );
	$result        = false;

	// Loop through all files.
	foreach ( $dir_list as $dir_file ) {
		// Rename strings.
		$result = fix_strings( $dir_file, $strings, $mode );

		// If this is a directory, loop through it.
		recursive_dir_string_fixer( $dir_file, $strings, $mode );
	}

	return $result;
}

/**
 * Rename all strings in a file.
 *
 * @param string $file The incoming contents of the file we are fixing the header of.
 * @param array  $strings The relevant strings used to create the plugin file header.
 * @param string $mode Either 'module' or 'plugin' to indicate the type off strings to fix.
 */
function fix_strings( string $file, array $strings, string $mode ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	// Open the file.
	$file_contents = $wp_filesystem->get_contents( $file );

	// Make sure the file header is correct.
	if ( 'plugin' === $mode ) {
		$file_contents = fix_plugin_file_header( $file_contents, $strings );
		$file_contents = fix_namespace( $file_contents, $strings['plugin_namespace'] );
		$file_contents = fix_package_tag( $file_contents, $strings['plugin_dirname'] );
	}

	if ( 'module' === $mode ) {
		$file_contents = fix_module_file_header( $file_contents, $strings );
		$file_contents = fix_namespace( $file_contents, $strings['module_namespace'] );
		$file_contents = fix_package_tag( $file_contents, $strings['module_plugin'] );
	}

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
 * Rewrite the namespace definition.
 *
 * @param string $file_contents The incoming contents of the file we are fixing the header of.
 * @param string $namespace The namespace to use.
 */
function fix_namespace( string $file_contents, string $namespace ) {
	$pattern = '~namespace .*;~';
	$fixed   = 'namespace ' . $namespace . ';';

	// Find the namespace deifition.
	$match_found = preg_match( $pattern, $file_contents, $matches );

	// Replace it if found.
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

	$fixed_file_header = '/**
 * Module Name: ' . $strings['module_name'] . '
 * Description: ' . $strings['module_description'] . '
 * Namespace: ' . $strings['module_namespace'] . '
 *
 * @package ' . $strings['module_plugin'] . '
 */';

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
