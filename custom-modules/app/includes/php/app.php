<?php
/**
 * Set up the App page.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace app;

/**
 * Create a page where the app will be populated.
 *
 * @since 1.0
 * @return void
 */
function render_app() {
	if ( ! isset( $_GET['addonbuilder'] ) ) {
		return;
	}

	?>
	<html>
		<head>
			<title>Add On Builder</title>
			<link rel="stylesheet" href="<?php echo add_on_module_data()['url'] . '/includes/css/build/style.css'; ?>" media="all">
		</head>
		<body>
		<div id="addonbuilder"></div>
		<script type="text/javascript" src="<?php echo add_on_module_data()['url'] . '/includes/js/build/index.js'; ?>"></script>
		</body>
	</html>
	<?php
	die();
}
add_action( 'init', __NAMESPACE__ . '\render_app' );