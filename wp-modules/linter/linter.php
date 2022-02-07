<?php
/**
 * Module Name: Linter
 * Description: This module contains functionality for linting and lint-fixing javascript files. Plugins built with WPPS can use this modules functions to link their javascript without needing any internal linting functions.
 * Namespace: WPPS\Linter
 *
 * @package wp-plugin-sidekick
 */

declare(strict_types=1);

namespace WPPS\Linter;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;}
