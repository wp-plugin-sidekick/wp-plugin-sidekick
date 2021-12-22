<?php
/**
 * Module Name: Sample Module
 * Description: This is a sample module.
 * Namespace: SampleModule
 *
 * @package wp-plugin-studio
 */

declare(strict_types=1);

namespace PluginBoiler\SampleModule;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show a sample admin notice.
 */
function sample_admin_notice() {
	?>
	<div class="notice notice-success">
		<p><?php echo esc_html( __( 'I come from the sample module! I recommend removing this module and creating your own! Modules make it easy to add, share, and delete code as your project grows.', 'plugin-boiler' ) ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', __NAMESPACE__ . '\sample_admin_notice' );
