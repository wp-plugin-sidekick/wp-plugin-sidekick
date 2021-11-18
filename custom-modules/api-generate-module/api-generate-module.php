<?php
/**
 * Module Name: API - Generate Module
 * Description: Generate a new module using the REST API.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 *
 * @package maintainer
 */

declare(strict_types=1);

namespace WPPS\ApiGenerateModule;

function module_data() {
	return [
		'dir' => plugin_dir_path( __FILE__ ),
		'url' => plugin_dir_url( __FILE__ ),
	];
}

require 'includes/php/class-api-generate-module.php';
