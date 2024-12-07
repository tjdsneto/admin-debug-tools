<?php
/**
 * Admin Debug Tools Plugin.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin;

use AdminDebugTools\Plugin\Core\WpLogParser;
use AdminDebugTools\Plugin\Exceptions\UserFrienldlyException;
use AdminDebugTools\Plugin\Options\Options;
use AdminDebugTools\Plugin\RestApi\RestApi;
use AdminDebugTools\Plugin\Utils\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Plugin.
 *
 * @since 1.0.0
 */
class Plugin {

	/**
	 * Holds a static instance of the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var Plugin
	 */
	protected static $instance;

	/**
	 * Holds a static instance of the container.
	 *
	 * @since 1.0.0
	 *
	 * @var Container
	 */
	protected static $container;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'Admin Debug Tools';

	/**
	 * The assets base URL for this plugin.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Executes the plugin by setting up the hooks to make the plugin work.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {
		// Initialize the plugin.
		add_action( 'init', array( $this, 'init' ) );

		// Hide the unrelated admin notices.
		add_action( 'admin_print_scripts', array( $this, 'hide_unrelated_admin_notices' ) );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->setup_container();

		static::$container->get( Options::class )->init();

		static::$container->get( RestApi::class )->init();

		if ( is_admin() ) {
			static::$container->get( Bootstrap\Admin\Pages::class )->init();
		}
	}

	/**
	 * Sets up the plugin's container.
	 *
	 * It defines which classes should be singleton and how to instantiate certain classes.
	 *
	 * The container has auto-wiring set up, so other classes are auto binded if they're not meant to be singletons
	 * or don't have any special constructor argument needs.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_container() {
		static::$container = new Container();

		static::$container->singleton( Options::class );
		static::$container->singleton( RestApi::class );
		static::$container->singleton( Bootstrap\Admin\Pages::class );

		static::$container->add(
			WpLogParser::class,
			function () {
				if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && file_exists( WP_DEBUG_LOG ) ) {
					$log_file_path = WP_DEBUG_LOG;
				}

				// Check if ini_get is enabled.
				if ( function_exists( 'ini_get' ) ) {
					$log_file_path = ini_get( 'error_log' );
				} else {
					$log_file_path = null;
				}

				// Validate the log file path.
				if ( empty( $log_file_path ) || ! file_exists( $log_file_path ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
					throw new UserFrienldlyException( __( 'Log file not found or inaccessible.', 'admin-debug-tools' ) );
				}

				try {
					return new WpLogParser( new \SplFileObject( $log_file_path, 'r' ) );
				} catch ( \Exception $e ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
					throw new UserFrienldlyException( __( 'Error while trying to parse the log file.', 'admin-debug-tools' ), 0, $e );
				}
			}
		);
	}

	/**
	 * Convenience method to get entries from the plugin's container.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The entry id.
	 *
	 * @throws Exceptions\Container\NotFoundException  No entry was found for **this** identifier.
	 * @throws Exceptions\Container\ContainerException Error while retrieving the entry.
	 *
	 * @return mixed The container entry.
	 */
	public static function get( $id ) {
		return static::$container->get( $id );
	}

	/**
	 * Hides unrelated admin notices.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function hide_unrelated_admin_notices() {
		// Bail if we're not on a Admin Debug Tools screen.
		if ( ! Utils::is_plugin_page() ) {
			return;
		}

		global $wp_filter;

		$notices_type = array(
			'user_admin_notices',
			'admin_notices',
			'all_admin_notices',
		);

		foreach ( $notices_type as $type ) {
			if ( empty( $wp_filter[ $type ]->callbacks ) || ! is_array( $wp_filter[ $type ]->callbacks ) ) {
				continue;
			}

			foreach ( $wp_filter[ $type ]->callbacks as $priority => $hooks ) {
				foreach ( $hooks as $name => $arr ) {
					if ( is_object( $arr['function'] ) && $arr['function'] instanceof \Closure ) {
						unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
						continue;
					}

					$class = ! empty( $arr['function'][0] ) && is_object( $arr['function'][0] ) ? strtolower( get_class( $arr['function'][0] ) ) : '';

					if ( ! empty( $class ) && preg_match( '/^(?:adbtl)/', $class ) ) {
						continue;
					}

					if ( ! empty( $name ) && ! preg_match( '/^(?:adbtl)/', $name ) ) {
						unset( $wp_filter[ $type ]->callbacks[ $priority ][ $name ] );
					}
				}
			}
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Plugin ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}
}
