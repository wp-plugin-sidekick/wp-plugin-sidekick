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
