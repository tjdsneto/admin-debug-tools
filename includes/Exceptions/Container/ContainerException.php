<?php
/**
 * Admin Debug Tools Container Exception.
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
 * Admin Debug Tools Container Exception.
 *
 * Altought not explicit yet, this class intends to implement the PSR-11 ContainerExceptionInterface.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 *
 * @since 1.0.0
 */
class ContainerException extends Exception {

}
