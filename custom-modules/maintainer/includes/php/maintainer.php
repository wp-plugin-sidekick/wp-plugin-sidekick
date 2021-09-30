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
	?>
	<html>
		<head>
			<title>Add On Maintainer</title>
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/build/style.css' ); ?>" media="all">
			<link rel="stylesheet" href="<?php echo esc_url( module_data()['url'] . 'includes/css/additional/additional-styles.css' ); ?>" media="all">
		</head>
		<body>
		<div id="addonmaintainer"></div>
		<script type="text/javascript">
			var aomManageableAddOns = <?php echo wp_json_encode( get_managable_plugins() ); ?>;
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

	$installed_plugins  = \get_plugins();

	$wp_filesystem_api = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

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

		$plugin_path = $wp_filesystem_api->wp_plugins_dir() . $manageable_plugins[ $dirname ]['dirname'];

		$modules_glob = glob( $plugin_path . '/custom-modules/*' );
		$modules      = array();

		foreach ( $modules_glob as $add_on_module ) {
			$module_name = basename( $add_on_module );
			$filename    = $module_name . '.php';
			$filepath    = $add_on_module . '/' . $filename;
			$module_data = get_module_data( $filepath );

			$modules[ $module_name ] = $module_data;
		}

		$manageable_plugins[ $dirname ]['modules'] = $modules;
	}

	return $manageable_plugins;
}


function get_module_data( $file ) {

	$all_headers = array(
		'name'        => 'Module Name',
		'version'     => 'Version',
		'description' => 'Description',
		'author'      => 'Author',
		'authoruri'   => 'Author URI',
		'textdomain'  => 'Text Domain',
		'requireswp'  => 'Requires at least',
		'requiresphp' => 'Requires PHP',
		'namespace'   => 'Namespace',
	);

	$module_data = \get_file_data( $file, $all_headers );

	// Append the directory path.
	$module_data['dir'] = plugin_dir_path( $file );
	$module_data['url'] = plugin_dir_url( $file );

	$module_data['slug'] = basename( $file, '.php' );

	return $module_data;

}


