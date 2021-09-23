<?php
/**
 * Function which runs a shell command, stores the output and error in a log file, and has a killswitch file as well to kill the script.
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
 * Run a shell command
 *
 * @param string $command the shell command to run.
 * @param string $job_identifier a unique string to represent this job.
 * @return WP_Error|array
 */
function do_shell_command( $command, $job_identifier ) {
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
		update_file_option( 'wpps_' . $job_identifier, true );
		update_file_option( 'wpps_error_' . $job_identifier, $error );
		update_file_option( 'wpps_output_' . $job_identifier, $output );
	} else {
		return new \WP_Error( 'error', __( 'Something went wrong.', '' ) );
	}

	// Initialize the loop to remain or kill the process.
	$stay_alive = true;

	while ( $stay_alive ) {

		$proc_details = proc_get_status( $proc );

		// If the proess has stopped running, kill it.
		if ( ! $proc_details['running'] ) {
			$stay_alive = false;
		} else {
			// Check to see if a command came in to kill this process, or if it should stay alive (true).
			$stay_alive = boolval( get_file_option( 'wpps_' . $job_identifier ) );
		}

		// And regardless of the database, if the command itself has finished, close this request.
		if ( connection_aborted() === 1 ) {
			$stay_alive = false;
		}

		// If this process should stay alive.
		if ( $stay_alive ) {

			// Extend the PHP timeout to 10 seconds from now so we don't hit a limit there.
			set_time_limit( time() + 10 );

			$error  = trim( stream_get_contents( $pipes[2] ) );
			$output = trim( stream_get_contents( $pipes[1] ) );

			$error_appended  = $error . get_file_option( 'wpps_error_' . $job_identifier );
			$output_appended = $output . get_file_option( 'wpps_output_' . $job_identifier );

			update_file_option( 'wpps_error_' . $job_identifier, $error_appended );
			update_file_option( 'wpps_output_' . $job_identifier, $output_appended );

			// Wait 10000 microseconds seconds before checking if we should keep this process alive again.
			usleep( 10000 );

		} else {
			$proc_details = proc_get_status( $proc );
			$error        = trim( stream_get_contents( $pipes[2] ) );
			$output       = trim( stream_get_contents( $pipes[1] ) );

			$error_appended = $error . get_file_option( 'wpps_error_' . $job_identifier );

			$output_appended = $output . get_file_option( 'wpps_output_' . $job_identifier );

			// Kill the process.
			shell_exec( 'kill -9 ' . $pid ); // phpcs:ignore

			$output .= wp_json_encode(
				array(
					'pid'     => $pid,
					'details' => $proc_details,
					'error'   => $error_appended,
					'output'  => $output_appended,
				)
			);
			// The item was successfully created.
			return $output;
		}
	}
}

/**
 * Update an option stored in a file. Using a file like this bypasses WP object caching.
 *
 * @param string $option_name The name of the option.
 * @param string $option_value The value of the option.
 */
function update_file_option( $option_name, $option_value ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();

	if ( ! $wp_filesystem->is_dir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' ) ) {
		/* directory didn't exist, so let's create it */
		$wp_filesystem->mkdir( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' );
	}

	$wp_filesystem->put_contents( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $option_name, $option_value );
}

/**
 * Update an option stored in a file. Using a file like this bypasses WP object caching.
 *
 * @param string $option_name The name of the option.
 */
function get_file_option( $option_name ) {
	$wp_filesystem = \WPPS\GetWpFilesystem\get_wp_filesystem_api();
	return $wp_filesystem->get_contents( $wp_filesystem->wp_content_dir() . '.wpps-studio-data/' . $option_name );
}