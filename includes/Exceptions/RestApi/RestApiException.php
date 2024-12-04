<?php
/**
 * Admin Debug Tools Rest API Base Exception.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Exceptions\RestApi;

use AdminDebugTools\Plugin\Exceptions\Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Rest API Base Exception.
 *
 * @since 1.0.0
 */
class RestApiException extends Exception {

	/**
	 * The default error code.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $default_code = 500;

	/**
	 * Extra error message.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $extra_error = null;

	/**
	 * Constructs a new RestApiException.
	 *
	 * @since 1.0.0
	 *
	 * @param string     $message  The error message (default is an empty string).
	 * @param int        $code     The error code (default is 0). If not provided, the default code is used.
	 * @param \Throwable $previous The previous throwable used for exception chaining (default is null).
	 */
	public function __construct( $message = '', $code = 0, \Throwable $previous = null ) {
		if ( empty( $code ) ) {
			$code = $this->get_default_code();
		}

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Converts the exception to a WP_REST_Response.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response A response object with the error message and code from the exception.
	 */
	public function to_response() {
		$data = array(
			'error' => $this->getMessage(),
		);

		if ( ! empty( $this->extra_error ) ) {
			$data['extra_error'] = $this->extra_error;
		}

		return new \WP_REST_Response(
			$data,
			$this->getCode()
		);
	}

	/**
	 * Get default error code.
	 *
	 * @since 1.0.0
	 *
	 * @return int The default error code.
	 */
	public function get_default_code() {
		return $this->default_code;
	}

	/**
	 * Sets an additional error for the RestApiException.
	 *
	 * Meant to hold additional information about the error.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $extra_error The additional error to set.
	 *
	 * @return $this Returns the current instance to allow method chaining.
	 */
	public function set_extra_error( $extra_error ) {
		$this->extra_error = $extra_error;
		return $this;
	}

	/**
	 * Creates a new RestApiException from an exception.
	 *
	 * @since 1.0.0
	 *
	 * @param \Throwable $oex The original exception (default is null).
	 *
	 * @return RestApiException A new RestApiException with a generic error message and a 500 error code.
	 */
	public static function from_exception( \Throwable $oex ) {
		$ex = new RestApiException( $oex->getMessage(), $oex->getCode(), $oex );

		if ( ! empty( $oex->getPrevious() ) ) {
			$ex->set_extra_error( $oex->getPrevious()->getMessage() );
		}

		return $ex;
	}

	/**
	 * Creates a new RestApiException for an unexpected error.
	 *
	 * @since 1.0.0
	 *
	 * @param \Throwable $oex The original exception (default is null).
	 *
	 * @return RestApiException A new RestApiException with a generic error message and a 500 error code.
	 */
	public static function unexpected_error( \Throwable $oex = null ) {
		$ex = ( new RestApiException( __( 'An unexpected error occurred.', 'admin-debug-tools' ), 500, $oex ) );

		if ( ! empty( $oex ) ) {
			$ex->set_extra_error( $oex->getMessage() );
		}

		return $ex;
	}
}
