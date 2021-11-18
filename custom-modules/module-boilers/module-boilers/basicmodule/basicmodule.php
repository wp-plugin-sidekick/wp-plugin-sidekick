<?php
/**
 * Module Name: Basic Module
 * Description: This is a basic module, ready for action.
 *
 * @package BasicModule
 */

declare(strict_types=1);

namespace BasicModule;

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