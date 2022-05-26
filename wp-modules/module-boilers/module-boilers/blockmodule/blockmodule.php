<?php
/**
 * Module Name: Block Module
 * Description: A ready to code Gutenberg Block.
 * Namespace: BlockModule
 *
 * @package BlockModule
 */

declare(strict_types=1);

namespace BlockModule;

// Exit if accessed directly.
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
	$module_dir_url  = module_dir_url( __FILE__ );

	if ( file_exists( $module_dir_path . 'js/build/index.asset.php' ) ) {
		$dependencies = require $module_dir_path . 'js/build/index.asset.php';
		$dependencies = $dependencies['dependencies'];
	} else {
		return;
	}

	// Include the frontend component so it can render inside Gutenberg.
	$js_url = $module_dir_url . 'js/build/index.js';
	$js_ver = filemtime( $module_dir_path . 'js/build/index.js' );
	wp_register_script( 'blockmodulejs', $js_url, $dependencies, $js_ver, true );

	$css_url = $module_dir_url . 'js/build/index.css';
	$css_ver = filemtime( $module_dir_path . 'js/build/index.css' );
	wp_register_style( 'blockmodulestyle', $css_url, array(), $css_ver );

	// Register the block.
	register_block_type(
		'blockmodule/blockmodule',
		array(
			'api_version'   => 2,
			'editor_script' => 'blockmodulejs',
			'editor_style'  => 'blockmodulestyle',
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\enqueue_block' );
