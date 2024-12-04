<?php
/**
 * Bootstrap the plugin unit testing environment.
 *
 * Support for:
 *
 * 1. `WP_DEVELOP_DIR` and `WP_TESTS_DIR` environment variables
 * 2. Plugin installed inside of WordPress.org developer checkout
 * 3. Tests checked out to /tmp
 */
define( 'ADBTL_PLUGIN_DIR', dirname( __DIR__ ) );

// Define ABSPATH, so the ABSPATH tests on every class doesn't fail.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/var/www/testing-site/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', '/var/www/testing-site/wp-content/' );
}

// Load autoload file.
if ( file_exists( ADBTL_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
	require_once ADBTL_PLUGIN_DIR . '/vendor/autoload.php';
} else {
	// We dun screwed up.
	throw new \Exception( 'Autoloader is missing. Try running `composer install` in the `' . ADBTL_PLUGIN_DIR . "` directory.\n\n" );
}

$realAbspath = dirname( dirname( dirname( ADBTL_PLUGIN_DIR ) ) );

// require_once $realAbspath . '/wp-admin/includes/file.php';

require_once ADBTL_PLUGIN_DIR . '/tests/Mock/wp.php';
