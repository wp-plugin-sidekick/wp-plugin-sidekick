<?php
/**
 * API Endpoint which fixes strings.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace WPPS\ApiStringFixer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the REST API routes and callbacks.
 */
class Api_String_Fixer extends \WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpps/v' . $version;
		register_rest_route(
			$namespace,
			'/stringfixer',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'string_fixer' ),
					'permission_callback' => array( $this, 'string_fixer_permission_check' ),
					'args'                => $this->request_args(),
				),
				'schema' => 'response_item_schema',
			)
		);
	}

	/**
	 * The default args to auto-fill for any request.
	 */
	public function default_args() {
		$default_plugin_args = \WPPS\StringFixer\default_plugin_args();
		$default_module_args = \WPPS\StringFixer\default_module_args();

		return array(
			'plugin_dirname'      => '',
			'plugin_args' => \WPPS\StringFixer\default_plugin_args(),
			'modules' => \WPPS\StringFixer\default_module_args(),
		);
	}

	/**
	 * Fix strings.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function string_fixer( $request ) {
		$params = $request->get_params();

		// Set keys to what the string fixer wants.
		$plugin_args = array(
			'plugin_name' => $params['pluginData']['Name'],
			'plugin_dirname' => $params['pluginData']['dirname'],
			'plugin_textdomain' => $params['pluginData']['TextDomain'],
			'plugin_namespace' => $params['pluginData']['Namespace'],
			'plugin_description' => $params['pluginData']['description'],
			'plugin_uri' => $params['pluginData']['PluginURI'],
			'min_wp_version' => $params['pluginData']['RequiresWP'],
			'min_php_version' => $params['pluginData']['RequiresPHP'],
			'UpdateURI' => $params['pluginData']['updateUri'],
		);
	
		$wp_filesystem    = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
		$plugins_dir      = $wp_filesystem->wp_plugins_dir();
		$plugin_path      = $plugins_dir . $params['pluginData']['dirname'];
		$plugin_file_path = $plugin_path . '/' . $params['pluginData']['dirname'] . '.php';

		// Fix strings.
		$strings_fixed = \WPPS\StringFixer\fix_plugin_strings( $plugin_file_path, $plugin_args );

		$modules_glob = glob( $plugin_path . '/wp-modules/*' );

		// Loop through each module in this plugin and fix all module strings.
		foreach ( $params['pluginData']['modules'] as $module ) {
			$module_args = array(
				'plugin_namespace'   => $params['pluginData']['Namespace'],
				'plugin_dirname'     => $params['pluginData']['dirname'],
				'module_name'        => $module['name'],
				'module_namespace'   => $module['namespace'],
				'module_description' => $module['description'],
			);
			$strings_fixed = \WPPS\StringFixer\recursive_module_string_fixer( $module['dir'], $module_args );
		}

		if ( ! $strings_fixed ) {
			return new \WP_REST_Response( $strings_fixed, 400 );
		} else {
			return new \WP_REST_Response(
				array(
					'success'     => true,
					'message'     => __( 'Plugin/Module headers, namespace definitions, and package tags successfully updated.', 'wpps' ),
					'plugin_data' => $params,
				),
				200
			);
		}
	}

	/**
	 * Allow only administrators to run shell commands.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function string_fixer_permission_check( $request ) {
		return true;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function request_args() {
		$return_args = array(
			'pluginData'        => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The shell command to run.', 'wpps' ),
				'validate_callback' => '__return_true',
				'sanitize_callback' => array( $this, 'sanitize_plugin_data' ),
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

	/**
	 * Validation callback for plugin data passed in.
	 *
	 * @param mixed           $value   Value of the the parameter.
	 * @param WP_REST_Request $request Current request object.
	 * @param string          $param   The name of the parameter.
	 */
	public function sanitize_plugin_data( $value ) {
		// TO DO: Sanitize each value.
		return $value;
	}
}

/**
 * Initialize the REST route.
 *
 * @since 1.0.0
 * @return void
 */
function instantiate_rest_api_routes() {
	$api_route = new Api_String_Fixer();
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
		'title'      => 'string_fixer',
		'type'       => 'object',

		// These define the items which will actually be returned by the endpoint.
		'properties' => array(
			'result' => array(
				'description' => esc_html__( 'The result of running the shell command.', 'wpps' ),
				'type'        => 'string',
				'readonly'    => true,
			),
		),
	);
}
