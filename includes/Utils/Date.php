<?php
/**
 * Admin Debug Tools Date Utils.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Date Utils.
 *
 * @since 1.0.0
 */
class Date {

	/**
	 * Formats a date and time string.
	 *
	 * This method converts a date and time string to a Unix timestamp, then formats it according to the WordPress date and time settings.
	 *
	 * @since 1.0.0
	 *
	 * @param string|int $timestamp A timestamp or a date and time string to format.
	 *
	 * @return string The formatted date and time string.
	 */
	public static function format_date_str( $timestamp ) {
		if ( is_string( $timestamp ) ) {
			// Convert the date and time string to a Unix timestamp.
			$timestamp = strtotime( $timestamp );
		}

		$date_format = get_option( 'date_format', 'F j, Y' );
		$time_format = get_option( 'time_format', 'g:i a' );
		$time_offset = get_option( 'gmt_offset', 0 );

		return date_i18n( "{$date_format} {$time_format}", $timestamp + ( $time_offset * 3600 ) );
	}
}
