<?php
/**
 * Admin Debug Tools Options.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Options;

use AdminDebugTools\Plugin\Utils\Arr;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Options.
 *
 * @since 1.0.0
 */
class Options {

	/**
	 * Holds the name for the Admin Debug Tools main option record.
	 *
	 * @since 1.0.0
	 */
	const OPTION_NAME = 'admin_debug_tools_options';

	/**
	 * Sets our options if not found in the DB.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Check/set the plugin options.
		$option = get_option( static::OPTION_NAME );
		if ( empty( $option ) ) {
			$option = $this->default_options();
			update_option( static::OPTION_NAME, $option );
		}
	}

	/**
	 * Loads the default plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of default plugin options.
	 */
	public function default_options() {
		$options = array(
			'installed'      => time(),
		);

		return apply_filters( 'admin_debug_tools_default_options', $options );
	}

	/**
	 * Returns an option value.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $key      The option value to get for given key.
	 * @param  mixed  $fallback The fallback value.
	 *
	 * @return mixed            The main option array for the plugin, or requsted value.
	 */
	public function get( $key = '', $fallback = null ) {
		$option = get_option( static::OPTION_NAME );

		return Arr::get( $option, $key, $fallback );
	}

	/**
	 * Sets the value of the specified option key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The option key to set.
	 * @param mixed  $value The value to set for the option key.
	 *
	 * @return Options The Options instance.
	 */
	public function set( $key = '', $value = '' ) {
		$option = get_option( static::OPTION_NAME );

		// We use the returned value as opposed to the passed value as reference, because
		// passing values by reference does not work with Facades.
		$option = Arr::set( $option, $key, $value );

		update_option( static::OPTION_NAME, $option );

		return $this;
	}
}
