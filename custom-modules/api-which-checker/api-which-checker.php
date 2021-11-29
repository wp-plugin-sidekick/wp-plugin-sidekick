<?php
/**
 * Module Name: API - Which Checker
 * Description: Check for things like "which php" or "which node".
 * Version: 1.0.0.0
 * Author: johnstonphilip
 *
 * @package maintainer
 */

declare(strict_types=1);

namespace WPPS\ApiWhichChecker;

function module_data() {
	return [
		'dir' => plugin_dir_path( __FILE__ ),
		'url' => plugin_dir_url( __FILE__ ),
	];
}

require 'includes/php/class-api-which-checker.php';
require 'includes/php/do-shell-command.php';
