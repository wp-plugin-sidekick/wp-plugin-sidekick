<?php
/**
 * Module Name: Block Module
 * Description: A ready to code Gutenberg Block.
 *
 * @package BlockModule
 */

declare(strict_types=1);

namespace BlockModule;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require 'includes/php/enqueue-scripts.php';