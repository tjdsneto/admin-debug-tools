<?php
/**
 * Admin Debug Tools Dashboard Controller.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\RestApi\Controllers;

use AdminDebugTools\Plugin\Core\FileContentGetter;
use AdminDebugTools\Plugin\Utils\Filesystem;
use SplFileObject;
use WPConfigTransformer;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Dashboard Controller.
 *
 * @since 1.0.0
 */
class WpConfigController extends BaseController {

	/**
	 * Retrieves the WP config.
	 *
	 * Route: GET /v1/wp-config
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response The REST API response.
	 */
	public function get() {
		$wp_config = array();

		if ( defined( 'WP_DEBUG' ) ) {
			$wp_config['WP_DEBUG'] = WP_DEBUG;
		}

		if ( defined( 'WP_DEBUG_LOG' ) ) {
			$wp_config['WP_DEBUG_LOG'] = WP_DEBUG_LOG;
		}

		if ( defined( 'WP_DEBUG_DISPLAY' ) ) {
			$wp_config['WP_DEBUG_DISPLAY'] = WP_DEBUG_DISPLAY;
		}

		if ( defined( 'SCRIPT_DEBUG' ) ) {
			$wp_config['SCRIPT_DEBUG'] = SCRIPT_DEBUG;
		}

		if ( defined( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ) {
			$wp_config['WP_DISABLE_FATAL_ERROR_HANDLER'] = WP_DISABLE_FATAL_ERROR_HANDLER;
		}

		if ( defined( 'SAVEQUERIES' ) ) {
			$wp_config['SAVEQUERIES'] = SAVEQUERIES;
		}

		// Check if ini_get is enabled.
		if ( function_exists( 'ini_get' ) ) {
			$wp_config['ERROR_LOG'] = ini_get( 'error_log' );
		}

		return new \WP_REST_Response(
			$wp_config,
			200
		);
	}

	/**
	 * Updates the WP config.
	 *
	 * Route: POST /v1/wp-config
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return \WP_REST_Response The REST API response.
	 */
	public function update( \WP_REST_Request $request ) {
		$wp_config = array();

		if ( $request->has_param( 'WP_DEBUG' ) ) {
			$wp_config['WP_DEBUG'] = $this->get_param( $request, 'WP_DEBUG', 'bool' );
		}

		if ( $request->has_param( 'WP_DEBUG_LOG' ) ) {
			$wp_debug_log = $this->get_param( $request, 'WP_DEBUG_LOG', 'string' );

			if ( ! empty( $wp_debug_log ) && ! in_array( $wp_debug_log, array( '0', '1', 'true', 'false' ), true ) ) {
				// Trim trailing slashes from the constants.
				$abs_path       = rtrim( ABSPATH, '/' );
				$wp_content_dir = rtrim( WP_CONTENT_DIR, '/' );

				// Replace the macro variables with their respective constant values.
				$wp_debug_log = str_replace(
					array( '{absPath}', '{wpContentDir}' ),
					array( $abs_path, $wp_content_dir ),
					$wp_debug_log
				);

				$real_path = realpath( $wp_debug_log );
				if ( $real_path ) {
					$wp_debug_log = $real_path;
				}
			}

			$wp_config['WP_DEBUG_LOG'] = $wp_debug_log;

			if ( empty( $wp_config['WP_DEBUG_LOG'] ) || '1' === $wp_config['WP_DEBUG_LOG'] ) {
				$wp_config['WP_DEBUG_LOG'] = (bool) $wp_config['WP_DEBUG_LOG'];
			}
		}

		if ( $request->has_param( 'WP_DEBUG_DISPLAY' ) ) {
			$wp_config['WP_DEBUG_DISPLAY'] = $this->get_param( $request, 'WP_DEBUG_DISPLAY', 'bool' );
		}

		if ( $request->has_param( 'SCRIPT_DEBUG' ) ) {
			$wp_config['SCRIPT_DEBUG'] = $this->get_param( $request, 'SCRIPT_DEBUG', 'bool' );
		}

		if ( $request->has_param( 'WP_DISABLE_FATAL_ERROR_HANDLER' ) ) {
			$wp_config['WP_DISABLE_FATAL_ERROR_HANDLER'] = $this->get_param( $request, 'WP_DISABLE_FATAL_ERROR_HANDLER', 'bool' );
		}

		if ( $request->has_param( 'SAVEQUERIES' ) ) {
			$wp_config['SAVEQUERIES'] = $this->get_param( $request, 'SAVEQUERIES', 'bool' );
		}

		$changed = array();
		try {
			$wp_config_path     = ABSPATH . 'wp-config.php';
			$config_transformer = new WPConfigTransformer( $wp_config_path );

			foreach ( $wp_config as $constant => $value ) {
				$raw = true;

				if ( is_string( $value ) ) {
					$raw = false;
				}

				if ( is_bool( $value ) ) {
					$value = $value ? 'true' : 'false';
				}

				$curr_value = $config_transformer->get_value( 'constant', $constant );

				if ( $curr_value === $value ) {
					continue;
				}

				$changed[ $constant ] = array(
					'old' => $curr_value,
					'new' => $value,
				);

				$config_transformer->update(
					'constant',
					$constant,
					$value,
					array(
						'raw'       => $raw,
						'normalize' => true,
					)
				);
			}
		} catch ( \Exception $e ) {
			$data = [
				'success' => false,
			];

			$data['message'] = $e->getMessage();

			if ('wp-config.php does not exist.' === $data['message']) {
				$data['message'] = __( 'The wp-config.php file does not exist.', 'admin-debug-tools' );
			}

			if ('wp-config.php is not writable.' === $data['message']) {
				$data['code'] = 'wp-config-not-writable';
				$data['message'] = __( 'The wp-config.php file is not writable.', 'admin-debug-tools' );
				$data['data'] = [
					'user' => get_current_user(),
				];
			}

			return new \WP_REST_Response(
				$data,
				500
			);
		}

		if ( ! empty( $wp_config['WP_DEBUG'] ) && ! empty( $wp_config['WP_DEBUG_LOG'] ) ) {
			[$success, $err_essage] = $this->test_logging( $wp_config['WP_DEBUG_LOG'] );

			if ( ! $success ) {
				return new \WP_REST_Response(
					array(
						'success' => false,
						'message' => $err_essage,
						'data'    => $wp_config,
					),
					206
				);
			}
		}

		return new \WP_REST_Response(
			array(
				'success' => true,
				'data'    => $wp_config,
			),
			200
		);
	}

	/**
	 * Tests if logging is working properly by attempting to write a test log entry.
	 *
	 * This method checks if the log directory exists and is writable, creates it if needed,
	 * writes a test log entry, and verifies the entry was written successfully.
	 *
	 * @since 1.0.0
	 *
	 * @param string $log_file The full path to the log file to test.
	 *
	 * @return array An array containing:
	 *               - bool   Whether the test was successful
	 *               - string|null Error message if test failed, null otherwise
	 */
	protected function test_logging( string $log_file ) {
		$filesystem = new Filesystem();

		// Get the parent directory of the log file.
		$log_dir = dirname( $log_file );

		// Check if the parent directory and the log file exist.
		if ( ! is_dir( $log_dir ) ) {
			// Check if the log directory is within the ABSPATH directory.
			if ( strpos( $log_dir, ABSPATH ) === 0 ) {
				// Try to create the directory.
				if ( ! $filesystem->mkdir( $log_dir, 0755, true ) ) {
					return array( false, __( 'Failed to create the log file directory.', 'admin-debug-tools' ) );
				}
			} else {
				return array( false, __( 'The log file directory does not exist and should be created first.', 'admin-debug-tools' ) );
			}
		}

		// Write a log entry.
		/* translators: %s: Current date and time */
		$log_entry = sprintf( __( 'Test log entry: %s', 'admin-debug-tools' ), date( 'Y-m-d H:i:s' ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- I intentioanlly want to use the server's timezone.

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is a test log entry.
		error_log( $log_entry, 3, $log_file );

		// Check if the log entry was written successfully.
		if ( $filesystem->exists( $log_file ) ) {
			$log_entry_found = false;

			$content_getter = new FileContentGetter( new SplFileObject( $log_file, 'r' ) );

			$last_10_content = $content_getter->get_last_lines( 10 );

			foreach ( $last_10_content->get_lines() as $line ) {
				if ( strpos( $line, $log_entry ) !== false ) {
					$log_entry_found = true;
					break;
				}
			}

			if ( $log_entry_found ) {
				return array( true, null );
			} else {
				if ( ! $filesystem->is_writable( $log_file ) ) {
					return array( false, __( 'The log file is not writable.', 'admin-debug-tools' ) );
				}

				return array( false, __( 'A testing log entry was successfully found. Check the WP_DEBUG_LOG config.', 'admin-debug-tools' ) );
			}
		}

		if ( ! $filesystem->is_writable( $log_dir ) ) {
			return array( false, __( 'The log file directory is not writable.', 'admin-debug-tools' ) );
		}

		return array( false, __( 'The log file did not get created. Check the WP_DEBUG_LOG config.', 'admin-debug-tools' ) );
	}
}
