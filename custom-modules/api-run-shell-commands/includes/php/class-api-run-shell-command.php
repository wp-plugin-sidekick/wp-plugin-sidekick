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
		$go_to_module   = 'cd ' . $wp_filesystem->wp_plugins_dir() . $params['plugin_dir_name'] . '/custom-modules/' . $params['module'] . '; ';
		$command        = $set_path . $go_to_module . $params['command'];
		$job_identifier = $params['job_identifier'];

		// Run the command.
		$descriptorspec = array(
			0 => array( 'pipe', 'r' ), // stdin.
			1 => array( 'pipe', 'w' ), // stdout.
			2 => array( 'pipe', 'w' ), // stderr.
		);

		$proc = proc_open( $command, $descriptorspec, $pipes ); // phpcs:ignore

		$proc_details = proc_get_status( $proc );
		$pid          = $proc_details['pid'];

		if ( is_resource( $proc ) ) {
			// Set streams to non blocking mode.
			stream_set_blocking( $pipes[2], false );
			stream_set_blocking( $pipes[1], false );

			$error  = trim( stream_get_contents( $pipes[2] ) );
			$output = trim( stream_get_contents( $pipes[1] ) );

			// Set a database option which we'll use to keep this command alive indefinitely, until stopped.
			$this->update_option( 'wpps_' . $job_identifier, true, $params['plugin_dir_name'], $params['module'] );
			$this->update_option( 'wpps_error_' . $job_identifier, $error, $params['plugin_dir_name'], $params['module'] );
			$this->update_option( 'wpps_output_' . $job_identifier, $output, $params['plugin_dir_name'], $params['module'] );
		} else {
			return new WP_REST_Response( array( 'error' => __( 'Something went wrong.', '' ) ), 400 );
		}

		// Initialize the loop to remain or kill the process.
		$stay_alive = true;
		$output = '';

		while ( $stay_alive ) {
			// Check the database to see if a command came in to kill this process, or if it should stay alive (true).
			$stay_alive = boolval( $this->get_option( 'wpps_' . $job_identifier, $params['plugin_dir_name'], $params['module'] ) );

			// And regardless of the database, if the command itself has finished, close this request.
			if ( connection_aborted() === 1 ) {
				$stay_alive = false;
			}

			// If this process should stay alive.
			if ( boolval( $stay_alive ) ) {

				// Extend the PHP timeout to 10 seconds from now so we don't hit a limit there.
				set_time_limit( time() + 10 );

				$error  = trim( stream_get_contents( $pipes[2] ) );
				$output = trim( stream_get_contents( $pipes[1] ) );

				$error_appended = $error . $this->get_option(
					'wpps_error_' . $job_identifier,
					$params['plugin_dir_name'],
					$params['module']
				);

				$output_appended = $output . $this->get_option(
					'wpps_output_' . $job_identifier,
					$params['plugin_dir_name'],
					$params['module']
				);

				$this->update_option(
					'wpps_error_' . $job_identifier,
					$error_appended,
					$params['plugin_dir_name'],
					$params['module']
				);

				$this->update_option(
					'wpps_output_' . $job_identifier,
					$output_appended,
					$params['plugin_dir_name'],
					$params['module']
				);

				// Wait 5 seconds before checking if we should keep this process alive.
				sleep( 5 );

			} else {
				// Kill the process.
				shell_exec( 'kill -9 ' . $pid ); // phpcs:ignore

				$output .= wp_json_encode(
					array(
						'pid'    => $proc_details,
						'error'  => isset( $error ) ? $error : '',
						'output' => isset( $output ) ? $output : '',
					)
				);
				// The item was successfully created.
				return new \WP_REST_Response( $output, 200 );
			}
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

		$this->update_option( 'wpps_' . $params['job_identifier'], false, $params['plugin_dir_name'], $params['module'] );

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

		$response['errors'] = $this->get_option( 'wpps_error_' . $job_identifier, $params['plugin_dir_name'], $params['module'] );
		$response['output'] = $this->get_option( 'wpps_output_' . $job_identifier, $params['plugin_dir_name'], $params['module'] );

		// The item was successfully created.
		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Update an option stored in a file. Using a file like this bypasses WP object caching.
	 *
	 * @param string $option_name The name of the option.
	 * @param string $option_value The value of the option.
	 * @param string $plugin The name of the plugin.
	 * @param string $module The value of the module.
	 */
	public function update_option( $option_name, $option_value, $plugin, $module ) {
		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		$module_data = module_data();

		if ( ! $wp_filesystem->is_dir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' ) ) {
			/* directory didn't exist, so let's create it */
			$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' );
		}

		if ( ! $wp_filesystem->is_dir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin ) ) {
			/* directory didn't exist, so let's create it */
			$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin );
			$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin . '/custom-modules/' );
		}

		if ( ! $wp_filesystem->is_dir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin . '/custom-modules/' . $module ) ) {
			/* directory didn't exist, so let's create it */
			$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin . '/custom-modules/' . $module );
		}

		$wp_filesystem->put_contents( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin . '/custom-modules/' . $module . '/' . $option_name, $option_value );
	}

	/**
	 * Update an option stored in a file. Using a file like this bypasses WP object caching.
	 *
	 * @param string $option_name The name of the option.
	 * @param string $plugin The name of the plugin.
	 * @param string $module The value of the module.
	 */
	public function get_option( $option_name, $plugin, $module ) {
		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
		return $wp_filesystem->get_contents( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $plugin . '/custom-modules/' . $module . '/' . $option_name );
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
			'plugin_dir_name' => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The directory name of the plugin where the module exists.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'module'          => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'The name of the directory of the module in question.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
			'job_identifier'  => array(
				'required'          => true,
				'type'              => 'string',
				'description'       => __( 'A unique slug that represents this action.', 'wpps' ),
				'validate_callback' => array( $this, 'validate_arg_is_string' ),
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

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
