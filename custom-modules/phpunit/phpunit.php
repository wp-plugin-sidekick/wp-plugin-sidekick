<?php
/**
 * Module Name: PHPUnit
 * Description: This module runs WordPress integration tests with PHPUnit inside a docker container.
 * Version: 1.0.0.0
 * Author: johnstonphilip
 *
 * @package WPPS\PHPUnit;
 */

declare(strict_types=1);

namespace WPPS\PHPUnit;

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

// Spin up docker while in wp-plugin-studio/custom-modules/phpunit/includes:
//docker-compose up --build -d

// From the wp-content/plugins directory, run:
// docker-compose -f wp-plugin-studio/custom-modules/phpunit/includes/docker-compose.yml  run wordpress vendor/bin/phpunit --bootstrap wp-plugin-studio/custom-modules/phpunit/includes/testers/bootstrap.php plugin1/tests/*

// To take the dockr offline:
// docker-compose down