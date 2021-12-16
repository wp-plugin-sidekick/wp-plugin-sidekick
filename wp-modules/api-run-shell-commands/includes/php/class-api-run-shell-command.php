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
			'/phplint',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'phplint' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'phplint', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/phplintfix',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'phplintfix' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'phplintfix', false ),
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
		register_rest_route(
			$namespace,
			'/csslint',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'csslint' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'csslint', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/csslintfix',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'csslintfix' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'csslintfix', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/jslint',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'jslint' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'jslint', false ),
				),
				'schema' => 'response_item_schema',
			)
		);
		register_rest_route(
			$namespace,
			'/jslintfix',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'jslintfix' ),
					'permission_callback' => array( $this, 'run_shell_command_permission_check' ),
					'args'                => $this->request_args( 'jslintfix', false ),
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

		$go_to_location = 'cd ' . $wp_filesystem->wp_plugins_dir() . $params['location'] . '; ';
		$command        = $go_to_location . $params['command'] . '; ';
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier );

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

		\WPPS\DoShellCommand\update_file_option( 'wpps_' . $params['job_identifier'], false );

		// The item was successfully created.
		return new \WP_REST_Response( true, 200 );
	}

	/**
	 * Run phplint for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function phplint( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		$path_to_plugin_dir  = $wp_filesystem->wp_plugins_dir() . $params['location'];
		$path_to_plugin_file = $path_to_plugin_dir . '/' . $params['location'] . '.php';

		// Open the file.
		$file_contents = $wp_filesystem->get_contents( $path_to_plugin_file );

		$namespace = \WPPS\StringFixer\get_plugin_namespace( $file_contents );

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( $path_to_plugin_file );

		// The cwd will be set to wp-plugin-studio/wp-modules/linter directory first so we can use it's phpcs functions without needing them in each plugin/module.
		$command        = 'sh phpcs.sh -p ' . $wp_filesystem->wp_plugins_dir() . $params['location'] . ' -n ' . $namespace . ' -t ' . $plugin_data['TextDomain'];
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio/wp-modules/linter/' );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Run phplintfix for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function phplintfix( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		$path_to_plugin_dir  = $wp_filesystem->wp_plugins_dir() . $params['location'];
		$path_to_plugin_file = $path_to_plugin_dir . '/' . $params['location'] . '.php';

		// Open the file.
		$file_contents = $wp_filesystem->get_contents( $path_to_plugin_file );

		$namespace = \WPPS\StringFixer\get_plugin_namespace( $file_contents );

		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data = get_plugin_data( $path_to_plugin_file );

		// The cwd will be set to wp-plugin-studio/wp-modules/linter directory first so we can use it's phpcs functions without needing them in each plugin/module.
		$command        = 'sh phpcs.sh -f 1 -p ' . $wp_filesystem->wp_plugins_dir() . $params['location'] . ' -n ' . $namespace . ' -t ' . $plugin_data['TextDomain'];
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio/wp-modules/linter/' );

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
		$command = 'sh phpunit.sh -p ' . $params['location'];
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_plugins_dir() . 'wp-plugin-studio/wp-modules/linter/' );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Lint CSS for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function csslint( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Note that WPPS linter module moves a package.json directly into wp-content, as many things (like stylelint not scanning node_modules) only works if scanning a child directory, not parent.
		// By making the wp-scripts package.json a "parent" to all plugins, it can then lint all plugins from a central place.
		$command = 'npm run lint:css "./plugins/' . $params['location'] . '/**/*.*css"; ';

		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_content_dir() );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Fix CSS for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function csslintfix( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Note that WPPS linter module moves a package.json directly into wp-content, as many things (like stylelint not scanning node_modules) only works if scanning a child directory, not parent.
		// By making the wp-scripts package.json a "parent" to all plugins, it can then lint all plugins from a central place.
		$command = 'npm run lint:css "./plugins/' . $params['location'] . '/**/*.*css" -- --fix; ';

		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_content_dir() );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Lint Js for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function jslint( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Note that WPPS linter module moves a package.json directly into wp-content, as many things (like stylelint not scanning node_modules) only works if scanning a child directory, not parent.
		// By making the wp-scripts package.json a "parent" to all plugins, it can then lint all plugins from a central place.
		$command = 'npm run lint:js "./plugins/' . $params['location'] . '"; ';

		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_content_dir() );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Fix Js for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function jslintfix( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		// Note that WPPS linter module moves a package.json directly into wp-content, as many things (like stylelint not scanning node_modules) only works if scanning a child directory, not parent.
		// By making the wp-scripts package.json a "parent" to all plugins, it can then lint all plugins from a central place.
		$command = 'npm run lint:js "./plugins/' . $params['location'] . '" -- --fix; ';

		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier, $wp_filesystem->wp_content_dir() );

		if ( is_wp_error( $result ) ) {
			return new \WP_REST_Response( $result, 400 );
		} else {
			return new \WP_REST_Response( $result, 200 );
		}
	}

	/**
	 * Fix PHP for a plugin.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function fixphp( $request ) {
		$params = wp_parse_args( $request->get_params(), $this->default_args() );

		$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

		$go_to          = 'cd ' . $wp_filesystem->wp_plugins_dir() . $params['location'] . '; ';
		$command        = $go_to . 'npm run lint:php:fix;';
		$job_identifier = $params['job_identifier'];

		$result = \WPPS\DoShellCommand\do_shell_command( $command, $job_identifier );

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

		// First, go to the directory containing the dockerfile and build it.
		$go_to_plugins  = 'cd ' . $wp_filesystem->wp_plugins_dir() . '/wp-plugin-studio;';
		$run_zip        = 'node .scripts/makezip.js ' . $params['location'];
		$command        = $go_to_plugins . $run_zip;
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

		if ( 'runshellcommand' === $type || 'phplint' === $type ) {
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
