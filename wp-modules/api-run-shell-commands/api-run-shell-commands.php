<?php
/**
 * Module Name: API - Run Shell Commands.
 * Description: Run any shell command using the REST API. This module should not be used in a production environment.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 *
 * @package maintainer
 */

declare(strict_types=1);

namespace WPPS\ApiRunShellCommands;

function module_data() {
	return [
		'dir' => plugin_dir_path( __FILE__ ),
		'url' => plugin_dir_url( __FILE__ ),
	];
}

require 'includes/php/class-api-run-shell-command.php';
