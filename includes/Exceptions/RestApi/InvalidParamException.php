<?php
/**
 * Admin Debug Tools Rest API Invalid Param Exception.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Exceptions\RestApi;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Rest API Invalid Param Exception.
 *
 * @since 1.0.0
 */
class InvalidParamException extends RestApiException {
	/**
	 * The default error code.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $default_code = 400;
}
