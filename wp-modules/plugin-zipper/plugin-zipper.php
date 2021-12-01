<?php
/**
 * Module Name: Get WP Filesystem
 * Description: A one-line function get the the WP Filesystem API up and running, which lets you manage files on the server.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 * Text Domain: wpps-get-filesystem
 *
 * @package WPPS\GetWpFilesystem;
 */

declare(strict_types=1);

namespace WPPS\PluginZipper;

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

//require 'includes/php/get-wp-filesystem.php';
