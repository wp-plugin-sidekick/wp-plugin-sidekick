<?php
/**
 * API Endpoint which allows you to run shell commands.
 *
 * @package AddOnBuilder
 */

declare(strict_types=1);

namespace WPPS\ApiRunShellCommands;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set up the REST API routes and callbacks.
 */
class Api_Run_Shell_Command extends \WP_REST_Controller {

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version   = '1';
		$namespace = 'wpps/v' . $version;
		$base      = 'runshellcommand';
		register_rest_route(
			$namespace,
			'/' . $base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'run_shell_command' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'runshellcommand', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/killmoduleshellcommand',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'kill_module_shell_command' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'killmoduleshellcommand', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/getmoduleshellcommand',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_module_shell_command' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'getmoduleshellcommand', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/phpcs',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'phpcs' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'phpcs', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/phpunit',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'phpunit' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'phpunit', false ),
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
		);
	}

	/**
	 * Run a shell command.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function run_shell_command( $request ) {

		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Change to this modules directory first, then run the command(s).
		$set_path       = 'export PATH="$PATH:"/usr/local/bin/; ';
		$go_to_location = 'cd ' . $wp_filesystem->wp_plugins_dir() . $params['location'] . '; ';
		$command        = $set_path . $go_to_location . $params['command'];
		$job_identifier = $params['job_identifier'];

		$result = do_shell_command( $command, $job_identifier );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}

	}

	/**
	 * Kill a module's shell command.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function kill_module_shell_command( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		update_file_option( $params['job_identifier'], false );

		// The item was successfully created.
		return new \WP_REST_Response( true, 200 );
	}

	/**
	 * Get a currently-running job's error and output data.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function get_module_shell_command_output( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		$response = array();

		$response['errors'] = get_file_option( 'wpps_error_' . $job_identifier );
		$response['output'] = get_file_option( 'wpps_output_' . $job_identifier );

		// The item was successfully created.
		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Run phpcs for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function phpcs( $request ) {

		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Change to the wp-plugin-studio directory first so we can use it's phpcs functions without needing them in each plugin/module.
		$set_path       = 'export PATH="$PATH:"/usr/local/bin/; ';
		$go_to          = 'cd ' . $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio; ';
		$command        = $set_path . $go_to . './vendor/bin/phpcs --report=json -q --standard=' . $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio/phpcs.xml ' . $wp_filesystem->wp_plugins_dir() . $params['location'];
		$job_identifier = $params['job_identifier'];

		$result = do_shell_command( $command, $job_identifier );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Run phpunit for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function phpunit( $request ) {

		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Change to the wp-plugin-studio directory first so we can use it's phpcs functions without needing them in each plugin/module.
		$set_path       = 'export PATH="$PATH:"/usr/local/bin/; ';

		// First, go to the directory containing the dockerfile and build it.
		$go_to_docker   = 'cd ' . $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio/custom-modules/phpunit/includes;';
		$build_docker   = 'docker-compose up --build -d;';
		$go_to_plugins  = 'cd ' . $wp_filesystem->wp_content_dir() . ';';
		$run_docker     = 'docker-compose -f plugins/wp-plugin-studio/custom-modules/phpunit/includes/docker-compose.yml run wordpress vendor/bin/phpunit --bootstrap plugins/wp-plugin-studio/custom-modules/phpunit/includes/testers/bootstrap.php plugins/' . $params['location'] . '/tests/*';
		$command        = $set_path . $go_to_docker . $build_docker . $go_to_plugins . $run_docker;
		$job_identifier = $params['job_identifier'];

		$result = do_shell_command( $command, $job_identifier );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Zip a plugin for delivery.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function zip_plugin( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Change to the wp-plugin-studio directory first so we can use it's phpcs functions without needing them in each plugin/module.
		$set_path = 'export PATH="$PATH:"/usr/local/bin/; ';

		// First, go to the directory containing the dockerfile and build it.
		$go_to_plugins  = 'cd ' . $wp_filesystem->wp_plugins_dir() . '/wp-plugin-studio;';
		$run_zip        = 'node .scripts/makezip.js ' . $params['location'];
		$command        = $set_path . $go_to_plugins . $run_zip;
		$job_identifier = $params['job_identifier'];

		$result = do_shell_command( $command, $job_identifier );

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
	public function run_shell_command_permission_check( $request ) {
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
			'job_identifier' => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'A unique slug that represents this action.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		if ( 'runshellcommand' === $type || 'phpcs' === $type ) {
			$return_args['location'] = array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The directory name of the plugin where the module exists.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			);
		}

		// Require the command paramater if running a 'runshellcommand'.
		if ( 'runshellcommand' === $type ) {
			$return_args['command'] = array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The shell command to run.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			);
		}

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
	$api_route = new Api_Run_Shell_Command();
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
		'title'      => 'run_shell_command',
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
