<?php
/**
 * Module Name: Plugin Boiler
 * Description: This module contains a plugin-boiler, which is what is used to generate a new blank plugin.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 *
 * @package WPPS\PluginBoiler;
 */

declare(strict_types=1);

namespace WPPS\PluginBoiler;

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

// Contrary to a normal module, we do not include the code directly, as it is simply copied, modified, and pasted by the plugin-creator module.
