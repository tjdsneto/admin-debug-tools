<?php
/**
 * Uninstall Admin Debug Tools.
 *
 * Remove:
 * - Admin Debug Tools options
 * - Translation files
 *
 * @package AdminDebugTools
 *
 * @since 1.0.0
 *
 * @var WP_Filesystem_Base $wp_filesystem
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// phpcs:disable WordPress.DB.DirectDatabaseQuery

// Delete all the plugin options.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'admin_debug_tools\_%'" );

global $wp_filesystem;

// Remove translation files.
$adbtl_languages_directory = defined( 'WP_LANG_DIR' ) ? trailingslashit( WP_LANG_DIR ) : trailingslashit( WP_CONTENT_DIR ) . 'languages/';
$adbtl_translations        = glob( wp_normalize_path( $adbtl_languages_directory . 'plugins/admin-debug-tools' ) );

if ( ! empty( $adbtl_translations ) ) {
	foreach ( $adbtl_translations as $adbtl_file ) {
		$wp_filesystem->delete( $adbtl_file );
	}
}
