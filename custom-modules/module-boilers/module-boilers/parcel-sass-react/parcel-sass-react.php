<?php
/**
 * Module Name: ParcelSassReact
 * Description: This module sets up a frontend react app built with Parcel.
 *
 * @package ParcelSassReact
 */

declare(strict_types=1);

namespace ParcelSassReact;

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