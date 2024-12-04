<?php
/**
 * Admin Debug Tools Admin Pages.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Bootstrap\Admin;

use AdminDebugTools\Plugin\Utils\Asset;
use AdminDebugTools\Plugin\Utils\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Admin Pages.
 *
 * @since 1.0.0
 */
class Pages {


	/**
	 * Sets up the hooks for the admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		// Add hook for the Admin menu page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Add hook for the Admin App scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add hook for the Admin footer text.
		add_filter( 'update_footer', array( $this, 'add_plugin_version_to_footer' ), 999 );
		add_filter( 'admin_footer_text', array( $this, 'add_admin_footer_text' ) );
	}

	/**
	 * Adds the admin menu and pages.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_admin_menu() {
		add_management_page(
			__( 'Debug Log', 'admin-debug-tools' ), // Page title.
			__( 'Debug Log', 'admin-debug-tools' ), // Menu title.
			Utils::access_capability( 'menu' ), // Capability.
			'debug-log', // Menu slug.
			array( $this, 'render_app' ) // Function that handles the display of the menu page.
		);
	}

	/**
	 * Outputs the markup for the Admin Debug Tools Admin App.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_app() {
		require_once Utils::dir_path( 'includes/Views/admin-app.php' );
	}

	/**
	 * Enqueue scripts for the Admin Debug Tools Admin App.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'tools_page_debug-log' !== $hook ) {
			return;
		}

		Asset::enqueue(
			'index',
			array(
				'AppData' => array(
					'sseUrl'              => get_rest_url( null, 'admin-debug-tools/v1/debug-log/sse' ),
					'debugLogDownloadUrl' => set_url_scheme( add_query_arg( '_adtnonce', wp_create_nonce( '/admin-debug-tools/v1/debug-log/download' ), get_rest_url( null, 'admin-debug-tools/v1/debug-log/download' ) ), 'https' ),
				),
			),
			array( 'style-index', 'index' )
		);
	}

	/**
	 * Customizes the plugin version footer text on the Admin Debug Tools Admin App.
	 *
	 * @since 1.0.0
	 *
	 * @param string $footer_text  The default footer text.
	 *
	 * @return string $text Amended footer text.
	 */
	public function add_plugin_version_to_footer( $footer_text ) {
		if ( ! Utils::is_plugin_page() ) {
			return $footer_text;
		}

		$plugin_data = get_plugin_data( ADMIN_DEBUG_TOOLS_FILE, false, false );
		$url         = 'https://wordpress.org/plugins/admin-debug-log/#developers';

		/* translators: %1$s - Admin Debug Tools plugin URL, %2$s - plugin version number */
		$footer_text = sprintf( __( '<a href="%1$s" target="_blank" rel="noopener noreferrer">Admin Debug Tools v%2$s</a>', 'admin-debug-tools' ), $url, $plugin_data['Version'] );
		return $footer_text;
	}

	/**
	 * Customizes the footer text on the Admin Debug Tools Admin App.
	 *
	 * @since 1.0.0
	 *
	 * @param string $text  The default admin footer text.
	 *
	 * @return string $text Amended admin footer text.
	 */
	public function add_admin_footer_text( $text ) {
		if ( ! Utils::is_plugin_page() ) {
			return $text;
		}

		$url = 'https://wordpress.org/support/plugin/admin-debug-log/reviews?filter=5#new-post';

		/* translators: %1$s - Adming Debug Log plugin reviews url - Adming Debug Log is the name of the plugin. */
		$text = sprintf( __( 'Thank you for using the <strong>Adming Debug Log</strong> plugin! Please, let us know how are you liking it on <a href="%1$s" target="_blank" rel="noopener noreferrer">WordPress.org</a> and help us improve.', 'admin-debug-tools' ), $url );

		return $text;
	}
}
