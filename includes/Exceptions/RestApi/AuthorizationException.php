<?php
/**
 * Admin Debug Tools Rest API Authorization Exception.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Exceptions\RestApi;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Rest API Authorization Exception.
 *
 * @since 1.0.0
 */
class AuthorizationException extends RestApiException {
	/**
	 * The default error code.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $default_code = 401;

	/**
	 * Get default error code.
	 *
	 * @since 1.0.0
	 *
	 * @return int The default error code.
	 */
	public function get_default_code() {
		return rest_authorization_required_code();
	}
}
