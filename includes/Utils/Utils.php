<?php
/**
 * Admin Debug Tools Utils.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Utils.
 *
 * @since 1.0.0
 */
class Utils {

	/**
	 * Returns a URL for the given path relative to the plugin directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path relative to the plugin directory.
	 *
	 * @return string URL for the given path relative to the plugin directory.
	 */
	public static function dir_url( $path = '' ) {
		return plugin_dir_url( ADMIN_DEBUG_TOOLS_FILE ) . $path;
	}

	/**
	 * Returns a path for the given path relative to the plugin directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path relative to the plugin directory.
	 *
	 * @return string Path for the given path relative to the plugin directory.
	 */
	public static function dir_path( $path ) {
		return plugin_dir_path( ADMIN_DEBUG_TOOLS_FILE ) . $path;
	}

	/**
	 * Checks if given (or current) page is an Admin Debug Tools admin page.
	 *
	 * @since 1.0.0
	 *
	 * @param string $page Page to check. Falls back to $_REQUEST['page'].
	 *
	 * @return boolean Whether given (or current) page is an Admin Debug Tools admin page.
	 */
	public static function is_plugin_page( $page = null ) {
		$screen = get_current_screen();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Nonce not required.
		if ( empty( $page ) && ! empty( $_REQUEST['page'] ) ) {
			$page = sanitize_key( wp_unslash( $_REQUEST['page'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return ! empty( $page ) && 'debug-log' === $page;
	}

	/**
	 * The access capability required for access to Admin Debug Tools pages/features.
	 *
	 * @since  1.0.0
	 *
	 * @param  string|null $slug The feature slug. Null by default.
	 *
	 * @return string The access capability.
	 */
	public static function access_capability( $slug = null ) {
		return apply_filters( 'admin_debug_tools_access_capability', 'manage_options', $slug );
	}

	/**
	 * Check if user has access capability required for access to Admin Debug Tools pages/features.
	 *
	 * @since  1.0.0
	 *
	 * @param  string|null $slug The feature slug. Null by default.
	 *
	 * @return bool Whether user has access.
	 */
	public static function user_can_access( $slug = null ) {
		return current_user_can( self::access_capability( $slug ) );
	}
}
