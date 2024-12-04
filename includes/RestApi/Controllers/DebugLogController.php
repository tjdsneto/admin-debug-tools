<?php
/**
 * Admin Debug Tools Debug Log Controller.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\RestApi\Controllers;

use AdminDebugTools\Plugin\Core\SseServer;
use AdminDebugTools\Plugin\Core\WpLogParser;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Debug Log Controller.
 *
 * @since 1.0.0
 */
class DebugLogController extends BaseController {

	/**
	 * The WordPress log parser instance.
	 *
	 * @since 1.0.0
	 *
	 * @var WpLogParser
	 */
	protected $wp_log_parser;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param WpLogParser $wp_log_parser The WordPress log parser instance.
	 */
	public function __construct( WpLogParser $wp_log_parser ) {
		$this->wp_log_parser = $wp_log_parser;
	}

	/**
	 * Retrieves log file updates.
	 *
	 * Route: GET /v1/debug-log/updates
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST API response.
	 */
	public function get_updates( \WP_REST_Request $request ) {
		$lines_param = $request['lines'] ?? 100;
		$start_line  = (int) $request['seek'] ?? 0;
		$direction   = $request['dir'] ?? 'downwards';

		if ( ! $this->wp_log_parser->exists() ) {
			return new \WP_REST_Response( null, 404 );
		}

		if ('downwards' === $direction) {
			$content_set = empty( $request['seek'] ) ? $this->wp_log_parser->get_last_lines( $lines_param ) : $this->wp_log_parser->get_from_line( $start_line );
		} elseif ('upwards' === $direction) {
			$content_set = $this->wp_log_parser->get_last_lines( $lines_param, $start_line );
		} else {
			return new \WP_REST_Response( null, 400 );
		}

		return new \WP_REST_Response( $content_set->to_array(), 200 );
	}

	/**
	 * Starts a Server-Sent Events connection.
	 *
	 * Route: GET /v1/debug-log/sse
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST API response.
	 */
	public function get_sse( \WP_REST_Request $request ) {
		$time_interval = $this->get_param( $request, 'sseti', 'int' );

		try {
			$sse = new SseServer();
			return $sse->start();
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( array(), 404 );
		}
	}

	/**
	 * Clears the debug log file.
	 *
	 * Route: POST /v1/debug-log/clear
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return \WP_REST_Response The REST API response.
	 */
	public function clear( \WP_REST_Request $request ) {
		$should_save = $this->get_param( $request, 'save', 'boolean' );

		$this->wp_log_parser->clear( $should_save );

		return new \WP_REST_Response( array(), 200 );
	}

	/**
	 * Downloads the debug log file.
	 *
	 * Route: GET /v1/debug-log/download
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return void|\WP_REST_Response The REST API response or void if file is downloaded.
	 */
	public function download( \WP_REST_Request $request ) {
		if ( ! $this->wp_log_parser->exists() ) {
			return new \WP_REST_Response( null, 404 );
		}

		$log_file_path = $this->wp_log_parser->get_log_file_path();

		if ( ! file_exists( $log_file_path ) ) {
			return new \WP_REST_Response( null, 404 );
		}

		// Set headers to force download.
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . basename( $log_file_path ) . '"' );
		header( 'Content-Length: ' . filesize( $log_file_path ) );

		// Clear output buffer to avoid memory issues.
		if ( ob_get_level() ) {
			ob_end_clean();
		}

		// // Use readfile to stream the file directly to the client.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Direct file access needed for streaming large log files.
		readfile( $log_file_path );
		exit;
	}
}
