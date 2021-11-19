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

	$module_dir_path = module_dir_path( __FILE__ );
	$module_dir_url  = module_dir_path( __FILE__ );

	if ( file_exists( $module_dir_path . 'includes/js/build/index.asset.php' ) ) {
		$dependencies = require $module_dir_path . 'includes/js/build/index.asset.php';
		$dependencies = $dependencies['dependencies'];
	} else {
		$dependencies = array();
	}

	// Include the frontend component so it can render inside Gutenberg.
	$js_url = $module_dir_url . 'includes/js/build/index.js';
	$js_ver = filemtime( $js_url );
	wp_register_script( 'blockmodulejs', $js_url, $dependencies, $js_ver, true );

	$css_url = $module_dir_url . 'includes/js/build/index.css';
	$css_ver = filemtime( $css_url );
	wp_register_style( 'blockmodulestyle', $css_url, array(), $css_ver );

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
