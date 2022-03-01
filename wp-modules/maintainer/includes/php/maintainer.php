<?php
/**
 * Set up the App page.
 *
 * @package Addonmaintainer
 */

declare(strict_types=1);

namespace Maintainer;


/**
 * Enqueue the scripts.
 */
function enqueue_scripts() {
	if ( ! isset( $_GET['addonmaintainer'] ) ) {
		return;
	}
	remove_all_actions( 'wp_enqueue_scripts' );

	$module_dir_path = module_dir_path( __FILE__ );
	$module_dir_url  = module_dir_url( __FILE__ );

	if ( file_exists( $module_dir_path . 'includes/js/build/index.asset.php' ) ) {
		$dependencies = require $module_dir_path . 'includes/js/build/index.asset.php';
		$dependencies = $dependencies['dependencies'];
	} else {
		return;
	}

	// Include the app.
	$js_url = $module_dir_url . 'includes/js/build/index.js';
	$js_ver = filemtime( $module_dir_path . 'includes/js/build/index.js' );
	wp_enqueue_script( 'wppluginsidekick', $js_url, $dependencies, $js_ver, true );

	// Enqueue sass and Tailwind styles, combined automatically using PostCSS in wp-scripts.
	$css_url = $module_dir_url . 'includes/js/build/index.css';
	$css_ver = filemtime( $module_dir_path . 'includes/js/build/index.css' );
	wp_enqueue_style( 'wppluginsidekick_style', $css_url, array(), $css_ver );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts', 0 );

function remove_global_styles() {
	if ( ! isset( $_GET['addonmaintainer'] ) ) {
		return;
	}
	remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
	remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
}
add_action('after_setup_theme', __NAMESPACE__ . '\remove_global_styles', 10, 0);

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
			<title>WP Plugin Sidekick</title>
			<?php wp_head(); ?>
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
				stringfixer: '<?php echo esc_url( get_bloginfo( 'wpurl' ) ); ?>/wp-json/wpps/v1/stringfixer',
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
			var wppsPlugins = <?php echo wp_json_encode( \WPPS\PluginDataFunctions\get_all_plugins() ); ?>;
			var wppsModuleBoilers = <?php echo wp_json_encode( \WPPS\ModuleDataFunctions\get_module_boilers() ); ?>;
		</script>
		<?php echo wp_footer(); ?>
		</body>
	</html>
	<?php
	die();
}
add_action( 'template_redirect', __NAMESPACE__ . '\render_app' );
