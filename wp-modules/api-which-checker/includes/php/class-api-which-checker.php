<?php
/**
 * API Endpoint which allows you to run shell commands.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace WPPS\ApiWhichChecker;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the REST API routes and callbacks.
 */
class Api_Which_Checker extends \WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpps/v' . $version;
		register_rest_route(
			$namespace,
			'/whichchecker',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'which_checker' ),
					'permission_callback' => array( $this, 'which_checker_permission_check' ),
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
		return array(
			'command' => '',
			'job_identifier' => '',
		);
	}

	/**
	 * Run a shell command.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function which_checker( $request ) {

		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$command        = $params['command'];
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}

	}

	/**
	 * Allow only administrators to run shell commands.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function which_checker_permission_check( $request ) {
		return true;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function request_args() {

		$return_args = array(
			'command' => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The shell command to run.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'job_identifier' => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'A unique slug that represents this action.', 'wpps' ),
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
	$api_route = new Api_Which_Checker();
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
		'title'      => 'which_checker',
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
