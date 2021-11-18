<?php
/**
 * Module Name: Block Module
 * Description: A ready to code Gutenberg Block.
 *
 * @package BlockModule
 */

declare(strict_types=1);

namespace BlockModule;

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

require 'includes/php/enqueue-scripts.php';