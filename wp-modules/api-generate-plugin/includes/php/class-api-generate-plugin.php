<?php
/**
 * API Endpoint which generates a new plugin in wp-content.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace WPPS\ApiGeneratePlugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the REST API routes and callbacks.
 */
class Api_Generate_Plugin extends \WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpps/v' . $version;
		$base      = 'generateplugin';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'generate_plugin' ),
					'permission_callback' => array( $this, 'generate_plugin_permission_check' ),
					'args'                => $this->request_args( 'runshellcommand', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
	}

	/**
	 * The default args to auto-fill for any request.
	 */
	public function default_args() {
		return array(
			'plugin_name'        => '',
			'plugin_dirname'     => '',
			'plugin_textdomain'  => '',
			'plugin_namespace'   => '',
			'plugin_description' => '',
			'plugin_version'     => '1.0.0',
			'plugin_author'      => '',
			'plugin_uri'         => '',
			'min_wp_version'     => '',
			'min_php_version'    => '',
			'plugin_license'     => 'GPLv2 or later',
			'update_uri'         => '',
		);
	}

	/**
	 * Generate Plugin Files.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function generate_plugin( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem     = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
		$plugins_dir       = $wp_filesystem->wp_plugins_dir();
		$plugin_boiler_dir = $plugins_dir . '/wp-plugin-studio/wp-modules/plugin-boiler/plugin-boiler/';
		$new_plugin_dir    = $plugins_dir . $params['plugin_dirname'];

		// Create the new plugin directory.
		$wp_filesystem->mkdir( $new_plugin_dir );

		// Copy the boiler plugin into it.
		copy_dir( $plugin_boiler_dir, $new_plugin_dir );

		// Rename the main plugin file.
		rename( $new_plugin_dir . '/plugin-boiler.php', $new_plugin_dir . '/' . $params['plugin_dirname'] . '.php' );

		// Fix strings.
		$strings_fixed = \WPPS\StringFixer\recursive_dir_string_fixer( $new_plugin_dir, $params, 'plugin' );

		if ( ! $strings_fixed ) {
			return new \WP_REST_Response( $strings_fixed, 400 );
		} else {
			return new \WP_REST_Response(
				array(
					'success'     => true,
					'message'     => __( 'Plugin successfully created.', 'wpps' ),
					'plugin_data' => $params,
				),
				200
			);
		}
	}

	/**
	 * Allow only administrators to generate plugins.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function generate_plugin_permission_check( $request ) {
		return true;
	}

	/**
	 * Get the query params for collections
	 *
	 * @param  string $type The type of endpoint being run.
	 * @return array
	 */
	public function request_args( $type ) {
		$return_args = array(
			'plugin_name'        => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The name of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_dirname'     => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The directory name of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_textdomain'  => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The textdomain of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_namespace'   => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The top level namespace of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_description' => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The description of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_version'     => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The version of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_author'      => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The author of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_license'     => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The license of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'plugin_uri'         => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The uri of the plugin.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		return $return_args;
	}

	/**
	 * Validation callback for simple string parameters.
	 *
	 * @param mixed           $value   Value of the the parameter.
	 * @param WP_REST_Request $request Current request object.
	 * @param string          $param   The name of the parameter.
	 */
	public function validate_arg_is_string( $value, $request, $param ) {
		$attributes = $request->get_attributes();

		if ( isset( $attributes['args'][ $param ] ) ) {
			$argument = $attributes['args'][ $param ];
			// Check to make sure our argument is a string.

			if ( 'string' === $argument['type'] && ! is_string( $value ) ) {
				// Translators: 1: The name of the paramater in question. 2: The required variable type.
				return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'wpps' ), $param, 'string' ), array( 'status' => 400 ) );
			}
		} else {
			// Translators: The name of the paramater which was passed, but not registered.
			return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'wpps' ), $param ), array( 'status' => 400 ) );
		}

		// If we got this far then the data is valid.
		return true;
	}
}

/**
 * Initialize the REST route.
 *
 * @since 1.0.0
 * @return void
 */
function instantiate_rest_api_routes() {
	$api_route = new Api_Generate_Plugin();
	$api_route->register_routes();
}
add_action( 'rest_api_init', __NAMESPACE__ . '\instantiate_rest_api_routes', 11 );

/**
 * Retrieves the item's schema, conforming to JSON Schema.
 * The properties value is what you can expect to see in a successful return/response from this endpoint.
 *
 * @since 1.0.0
 *
 * @return array Item schema data.
 */
function response_item_schema() {
	return array(
		// This tells the spec of JSON Schema we are using which is draft 4.
		'$schema'    => 'https://json-schema.org/draft-04/schema#',
		// The title property marks the identity of the resource.
		'title'      => 'generate_plugin',
		'type'       => 'object',

		// These define the items which will actually be returned by the endpoint.
		'properties' => array(
			'result' => array(
				'description' => esc_html__( 'The result of the request.', 'wpps' ),
				'type'        => 'string',
				'readonly'    => true,
			),
		),
	);
}
