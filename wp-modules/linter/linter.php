<?php
/**
 * Module Name: JS Linter
 * Description: This module contains functionality for linting and lint-fixing javascript files. Plugins built with WPPS can use this modules functions to link their javascript without needing any internal linting functions.
 * Namespace: WPPS\JSLinter
 *
 * @package wp-plugin-studio
 */

declare(strict_types=1);

namespace WPPS\JSLinter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function hoist_linter_to_wp_content() {
	$wp_filesystem       = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	$wp_content_dir      = $wp_filesystem->wp_content_dir();
	
	// Hoist package.json File.
	$linter_package_json = module_dir_path( __FILE__ ) . 'package.json';
	$new_package_json    = $wp_content_dir . 'package.json';
	
	// Copy the linter files to wp-content
	if ( ! $wp_filesystem->exists( $new_package_json ) ) {
		copy( $linter_package_json, $new_package_json );
	}

	// Run `npm install` if the node_modules directory does not exist.
	if ( ! $wp_filesystem->exists( $wp_content_dir . 'node_modules' ) ) {
		$result = \WPPS\DoShellCommand\do_shell_command( 'npm install', 'wpps_linter_npm_install', $wp_content_dir );
	}

	// Hoist composer.json file and phpcs.xml file.
	$linter_composer_json = module_dir_path( __FILE__ ) . 'composer.json';
	$new_composer_json    = $wp_content_dir . 'composer.json';
	$linter_phpcs_xml     = module_dir_path( __FILE__ ) . 'phpcs.xml';
	$new_phpcs_xml        = $wp_content_dir . 'phpcs.xml';
	
	// Copy the linter files to wp-content
	if ( ! $wp_filesystem->exists( $new_composer_json ) ) {
		copy( $linter_composer_json, $new_composer_json );
		copy( $linter_phpcs_xml, $new_phpcs_xml );
	}

	// Run `composer install` if the vender directory does not exist.
	if ( ! $wp_filesystem->exists( $wp_content_dir . 'vendor' ) ) {
		$result = \WPPS\DoShellCommand\do_shell_command( 'composer install', 'wpps_linter_composer_install', $wp_content_dir );
	}

}
add_action( 'admin_init', __NAMESPACE__ . '\hoist_linter_to_wp_content' );


function generate_phpcsxml( $details ) {
	
}
add_action( 'admin_init', __NAMESPACE__ . '\generate_phpcsxml' );
