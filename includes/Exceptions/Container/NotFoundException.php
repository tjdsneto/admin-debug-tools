<?php
/**
 * Admin Debug Tools Container Not Found Exception.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Exceptions\Container;

use AdminDebugTools\Plugin\Exceptions\Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Container Not Found Exception.
 *
 * Altought not explicit yet, this class intends to implement the PSR-11 NotFoundExceptionInterface.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 *
 * @since 1.0.0
 */
class NotFoundException extends Exception {

}
