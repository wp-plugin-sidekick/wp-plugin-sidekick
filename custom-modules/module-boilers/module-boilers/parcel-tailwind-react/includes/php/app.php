<?php
/**
 * Set up the App page.
 *
 * @package ParcelTailwindReact
 */

declare(strict_types=1);

namespace ParcelTailwindReact;

/**
 * Create a page where the app will be rendered.
 *
 * @since 1.0
 * @return void
 */
function render_app() {
	if ( ! isset( $_GET['ParcelTailwindReact'] ) ) {
		return;
	}
	?>
	<html>
		<head>
			<title>ParcelTailwindReact</title>
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/build/style.css' ); ?>" media="all">
		</head>
		<body>
			<div id="ParcelTailwindReact"></div>
			<script type="text/javascript" src="<?php echo esc_url( module_data()['url'] . '/includes/js/build/index.js' ); ?>"></script>
		</body>
	</html>
	<?php
	die();
}
add_action( 'init', __NAMESPACE__ . '\render_app' );
