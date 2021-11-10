<?php
/**
 * Set up the App page.
 *
 * @package ParcelSassReact
 */

declare(strict_types=1);

namespace ParcelSassReact;

/**
 * Create a page where the app will be rendered.
 *
 * @since 1.0
 * @return void
 */
function render_app() {
	if ( ! isset( $_GET['ParcelSassReact'] ) ) {
		return;
	}
	?>
	<html>
		<head>
			<title>ParcelSassReact</title>
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/build/style.css' ); ?>" media="all">
		</head>
		<body>
			<div id="ParcelSassReact"></div>
			<script type="text/javascript" src="<?php echo esc_url( module_data()['url'] . '/includes/js/build/index.js' ); ?>"></script>
		</body>
	</html>
	<?php
	die();
}
add_action( 'init', __NAMESPACE__ . '\render_app' );
