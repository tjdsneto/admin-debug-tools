<?php
/**
 * Admin Debug Tools Server-Sent Events (SSE) handler.
 *
 * Provides functionality for real-time log streaming using Server-Sent Events.
 * Monitors WordPress debug log file for changes and pushes updates to connected clients.
 *
 * @package AdminDebugTools
 * @since 1.0.0
 */

namespace AdminDebugTools\Plugin\Core;

use SplFileObject;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Server-Sent Events (SSE) handler class.
 *
 * Manages SSE connections and streams log file updates to clients in real-time.
 *
 * @since 1.0.0
 */
class SseServer {

	/**
	 * WordPress log parser instance.
	 *
	 * @since 1.0.0
	 * @var WpLogParser
	 */
	protected $wp_log;

	/**
	 * Time interval between update checks in seconds.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $time_interval;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param int $time_interval Optional. Interval between update checks in seconds. Default 2.
	 */
	public function __construct( $time_interval = 2 ) {
		$this->time_interval = $time_interval;

		$log_file_path = WP_CONTENT_DIR . '/debug.log'; // Adjust the path as necessary.

		if ( is_string( WP_DEBUG_LOG ) && file_exists( WP_DEBUG_LOG ) ) {
			$log_file_path = WP_DEBUG_LOG;
		}

		$this->wp_log = new WpLogParser( new SplFileObject( $log_file_path, 'r' ) );
	}

	/**
	 * Start the SSE stream.
	 *
	 * Initializes the SSE connection, sets appropriate headers, and begins
	 * streaming log updates to the client.
	 *
	 * @since 1.0.0
	 * @return \WP_REST_Response Response object on error or completion.
	 */
	public function start() {
		if ( ! $this->wp_log->exists() ) {
			return new \WP_REST_Response( null, 404 );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary for SSE debugging.
		error_log( 'SSE init.' );

		header( 'Content-Type: text/event-stream' );
		header( 'Cache-Control: no-cache' );
		header( 'Connection: keep-alive' );
		header( 'X-Accel-Buffering: no' );

		// Push data to the browser every second.
		ob_implicit_flush( true );
		ob_end_flush();

		$this->dispatch(
			wp_json_encode(
				array(
					// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- I intentioanlly want to use the server's timezone.
					'server_time'      => date( 'h:i:s', time() ),
					'output_buffering' => ini_get( 'output_buffering' ),
				)
			),
			'start'
		);

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary for SSE debugging.
		error_log( 'SSE start.' );

		$content_set = $this->wp_log->get_last_lines( 10 );

		$this->dispatch( wp_json_encode( $content_set->to_array() ) );

		while ( true ) {
			$updates = $this->wp_log->get_updates();

			if ( ! empty( $updates->get_lines() ) ) {
				$this->dispatch( wp_json_encode( $updates->to_array() ) );
			}

			if ( connection_aborted() ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary for SSE debugging.
				error_log( 'Connection aborted. Exiting SSE.' );
				break;
			}

			// Once per second.
			sleep( $this->time_interval );

			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary for SSE debugging.
			error_log( 'SSE tick.' );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Necessary for SSE debugging.
		error_log( 'Abort?' );
		return new \WP_REST_Response( null, 200 );
	}

	/**
	 * Constructs and sends SSE data to the client.
	 *
	 * Formats the message according to SSE specification and ensures it is
	 * immediately flushed to the client.
	 *
	 * @since 1.0.0
	 * @param string $msg        The message data to send.
	 * @param string $event_type Optional. The event type identifier. Default 'message'.
	 */
	public function dispatch( $msg, $event_type = 'message' ) {
		$data = "event: {$event_type}" . PHP_EOL . "data: $msg" . PHP_EOL . PHP_EOL;

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw output needed for SSE JSON data.
		echo $data;
		if ( ob_get_level() > 0 ) {
			ob_flush();
		}

		flush();
	}
}
