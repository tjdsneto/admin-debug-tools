<?php
/**
 * Admin Debug Tools Arr Utils.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Arr Utils.
 *
 * @since 1.0.0
 */
class Arr {

	/**
	 * Retrieves a value from a nested array using "dot" notation.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $arr The array from which the value should be retrieved.
	 * @param string $key The key of the value to retrieve, using "dot" notation for nested values.
	 * @param mixed  $fallback The value to return if the specified key does not exist.
	 *
	 * @return mixed The value at the specified key in the array, or the fallback value if the key does not exist.
	 */
	public static function get( $arr, $key, $fallback = null ) {
		$current = $arr;

		if ( ! empty( $key ) ) {
			$keys = explode( '.', $key );
			foreach ( $keys as $k ) {
				if ( isset( $current[ $k ] ) ) {
					$current = $current[ $k ];
				} else {
					return $fallback;
				}
			}
		}

		return $current;
	}

	/**
	 * Sets a value in a nested array using "dot" notation.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $arr The array in which the value should be set.
	 * @param string $key The key at which the value should be set, using "dot" notation for nested values.
	 * @param mixed  $value The value to set.
	 *
	 * @return array The array with the value set.
	 */
	public static function set( &$arr, $key, $value ) {

		if ( is_null( $key ) ) {
			$arr = $value;
			return $arr;
		}

		$keys = explode( '.', $key );

		foreach ( $keys as $i => $key ) {
			if ( count( $keys ) === 1 ) {
				break;
			}

			unset( $keys[ $i ] );

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset( $arr[ $key ] ) || ! is_array( $arr[ $key ] ) ) {
				$arr[ $key ] = array();
			}

			$arr = &$arr[ $key ];
		}

		$arr[ array_shift( $keys ) ] = $value;

		return $arr;
	}
}
