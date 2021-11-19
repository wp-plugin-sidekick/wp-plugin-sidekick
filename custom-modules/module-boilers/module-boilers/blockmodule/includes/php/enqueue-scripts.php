<?php
/**
 * Set up the App page.
 *
 * @package projectx
 */

declare(strict_types=1);

namespace ProjectX\DesignBlock;

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue
 *
 * @since  1.0.0
 * @return void
 */
function enqueue_block() {

	if ( file_exists( module_data()['dir'] . 'includes/js/build/index.asset.php' ) ) {
		$dependencies = require module_data()['dir'] . 'includes/js/build/index.asset.php';
		$dependencies = $dependencies['dependencies'];
	} else {
		$dependencies = array();
	}

	// Include the frontend component so it can render inside Gutenberg.
	wp_register_script( 'blockmodulejs', module_data()['url'] . 'includes/js/build/index.js', $dependencies, time(), true );

	$theme_json = \WP_Theme_JSON_Resolver::get_merged_data();

	wp_register_style(
		'blockmodulestyle',
		module_data()['url'] . 'includes/js/build/index.css',
		array(),
		time()
	);

	// Register the block.
	register_block_type(
		'blockmodule/blockmodule',
		array(
			'api_version' => 2,
			'editor_script' => 'blockmodulejs',
			'editor_style'  => 'blockmodulestyle',
		)
	);

}
add_action( 'init', __NAMESPACE__ . '\enqueue_block' );
