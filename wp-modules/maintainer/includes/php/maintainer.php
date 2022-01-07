<?php
/**
 * Set up the App page.
 *
 * @package Addonmaintainer
 */

declare(strict_types=1);

namespace Maintainer;

/**
 * Create a page where the app will be populated.
 *
 * @since 1.0
 * @return void
 */
function render_app() {
	if ( ! isset( $_GET['addonmaintainer'] ) ) {
		return;
	}
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	?>
	<html>
		<head>
			<title>WP Plugin Studio</title>
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/build/style.css' ); ?>" media="all">
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/additional/additional-styles.css' ); ?>" media="all">
		</head>
		<body>
		<div id="addonmaintainer"></div>
		<script type="text/javascript">
			var wpContentDir =  '<?php echo esc_html( $wp_filesystem->wp_content_dir() ); ?>';
			var wpPluginsDir =  '<?php echo esc_html( $wp_filesystem->wp_plugins_dir() ); ?>';
			var wppsApiEndpoints = {
				generatePlugin: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/generateplugin',
				generateModule: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/generatemodule',
				runShellCommand: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/runshellcommand',
				whichChecker: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/whichchecker',
				phplint: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/phplint',
				phplintfix: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/phplintfix',
				phpUnit: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/phpunit',
				csslint: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/csslint',
				csslintfix: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/csslintfix',
				jslint: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/jslint',
				jslintfix: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/jslintfix',
				killShellCommand: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/killmoduleshellcommand',
			};
			var wppsPlugins = <?php echo wp_json_encode( get_managable_plugins() ); ?>;
			var wppsModuleBoilers = <?php echo wp_json_encode( \WPPS\ModuleDataFunctions\get_module_boilers() ); ?>;
		</script>
		<script type="text/javascript" src="<?php echo esc_url( module_data()['url'] . '/includes/js/build/index.js' ); ?>"></script>
		</body>
	</html>
	<?php
	die();
}
add_action( 'init', __NAMESPACE__ . '\render_app' );

/**
 * Get the list of manageable plugins that currently exist.
 *
 * @since 1.0
 * @return void
 */
function get_managable_plugins() {
	// Check if get_plugins() function exists. This is required on the front end of the
	// site, since it is in a file that is normally only loaded in the admin.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins = \get_plugins();

	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	$plugins_path      = $wp_filesystem_api->wp_plugins_dir();

	$manageable_plugins = array();

	foreach ( $installed_plugins as $key => $installed_plugin ) {
		$dirname = basename(
			$key, // Get the key which holds the folder/file name.
			'.php' // Strip away the .php part.
		);

		$manageable_plugins[ $dirname ] = $installed_plugins[ $key ];

		$manageable_plugins[ $dirname ]['filename'] = basename(
			$key
		);

		$manageable_plugins[ $dirname ]['dirname'] = basename(
			$key, // Get the key which holds the folder/file name.
			'.php' // Strip away the .php part.
		);

		$manageable_plugins[ $dirname ]['namespace'] = get_plugin_namespace( $plugins_path . $key );

		$manageable_plugins[ $dirname ]['modules'] = \WPPS\ModuleDataFunctions\get_plugin_modules( $dirname );
	}

	return $manageable_plugins;
}

function get_plugin_namespace( $plugin_file ) {
	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	// Open the file.
	$file_contents = $wp_filesystem_api->get_contents( $plugin_file );
	preg_match_all( '/(?<=namespace).*(?=;)/', $file_contents, $matches );

	return $matches[0];
}
