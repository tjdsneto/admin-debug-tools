<?php
/**
 * Admin Debug Tools
 *
 * @package           AdminDebugTools
 * @author            Tiago Neto
 * @copyright         2024 Tiago Neto
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Admin Debug Tools
 * Plugin URI:        https://github.com/tjdsneto/admin-debug-tools
 * Description:       Admin Debug Tools makes it easy to manage your site's logs and debug settings directly from the dashboard, without needing to edit backend files.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Tiago Neto
 * Author URI:        https://tjdsneto.com
 * Text Domain:       admin-debug-tools
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load the plugin textdomain.
add_action( 'plugins_loaded', 'admin_debug_tools_textdomain' );

if ( ! defined( 'ADMIN_DEBUG_TOOLS_FILE' ) ) {
	define( 'ADMIN_DEBUG_TOOLS_FILE', __FILE__ );
}

if ( ! defined( 'ADMIN_DEBUG_TOOLS_DIR' ) ) {
	define( 'ADMIN_DEBUG_TOOLS_DIR', plugin_dir_path( ADMIN_DEBUG_TOOLS_FILE ) );
}

// Autoload Composer packages.
require_once ADMIN_DEBUG_TOOLS_DIR . 'vendor/autoload.php';

/**
 * Loads the plugin textdomain for translation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function admin_debug_tools_textdomain() {
	$domain = 'admin-debug-tools';

	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
	$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	load_plugin_textdomain( $domain, false, dirname( plugin_basename( ADMIN_DEBUG_TOOLS_FILE ) ) . '/languages/' );
}

$plugin = \AdminDebugTools\Plugin\Plugin::get_instance();
$plugin->run();
