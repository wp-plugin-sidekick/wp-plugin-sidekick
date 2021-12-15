<?php
/**
 * Module Name: Maintainer
 * Description: The frontend react app for WP Suitcase.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 * Text Domain: addonbuilder
 * Namespace: Addonbuilder\App
 *
 * @package maintainer
 */

declare(strict_types=1);

namespace Maintainer;

function module_data() {
	return [
		'dir' => plugin_dir_path( __FILE__ ),
		'url' => plugin_dir_url( __FILE__ ),
	];
}

require 'includes/php/maintainer.php';
