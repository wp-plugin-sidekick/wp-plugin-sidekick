<?php
/**
 * API Endpoint which generates a new module in a plugin.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace WPPS\ApiGenerateModule;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the REST API routes and callbacks.
 */
class Api_Generate_Module extends \WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpps/v' . $version;
		$base      = 'generatemodule';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'generate_module' ),
					'permission_callback' => array( $this, 'generate_module_permission_check' ),
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
			'module_name'        => '',
			'module_namespace'   => '',
			'module_description' => '',
			'module_boiler'      => '',
			'module_plugin'      => '',
		);
	}

	/**
	 * Generate Module Files.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function generate_module( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem      = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
		$plugins_dir        = $wp_filesystem->wp_plugins_dir();
		$wp_modules_dir     = $plugins_dir . '/' . $params['module_plugin'] . '/wp-modules/';
		$boiler_dir         = $plugins_dir . '/wp-plugin-sidekick/wp-modules/module-boilers/module-boilers/' . $params['module_boiler'];
		$new_module_dirname = sanitize_title_with_dashes( $params['module_name'] );
		$new_module_dir     = $wp_modules_dir . sanitize_title_with_dashes( $params['module_name'] );

		// Ensure the wp-modules directory exists.
		if ( ! file_exists( $wp_modules_dir ) ) {
			$wp_filesystem->mkdir( $wp_modules_dir );
		}

		// Create the new module directory.
		$wp_filesystem->mkdir( $new_module_dir );

		// Copy the boiler module into it.
		copy_dir( $boiler_dir, $new_module_dir );

		// Rename the main module file.
		rename( $new_module_dir . '/' . $params['module_boiler'] . '.php', $new_module_dir . '/' . $new_module_dirname . '.php' );

		// Add plugin data to module args.
		$params['plugin_namespace' ] = \WPPS\StringFixer\get_plugin_namespace( $params['module_plugin'] );
		$params['plugin_dirname' ]   = $params['module_plugin'];

		// Fix strings
		$strings_fixed = \WPPS\StringFixer\recursive_module_string_fixer( $new_module_dir, $params );

		$updated_modules_in_plugin = \WPPS\ModuleDataFunctions\get_plugin_modules( $params['module_plugin'] );

		if ( ! $strings_fixed ) {
			return new \WP_REST_Response( $strings_fixed, 400 );
		} else {
			return new \WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Module successfully created.', 'wpps' ),
					'modules' => $updated_modules_in_plugin,
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
	public function generate_module_permission_check( $request ) {
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
			'module_name'        => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The name of the module.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'module_namespace'   => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The top level namespace of the module.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'module_description' => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The description of the module.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'module_boiler'      => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The dir name of the module boiler to use.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'module_plugin'      => array(
				'required'          => false,
				'type'              => 'string',
				'description'       => __( 'The dir name of the plugin where this module will live.', 'wpps' ),
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
	$api_route = new Api_Generate_Module();
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
		'title'      => 'generate_module',
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
