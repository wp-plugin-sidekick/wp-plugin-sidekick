<?php
/**
 * Module Name: Parcel Tailwind React
 * Description: This module sets up a frontend react app built with Parcel.
 *
 * @package ParcelTailwindReact
 */

declare(strict_types=1);

namespace ParcelTailwindReact;

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
