<?php
/**
 * Admin Debug Tools Asset Utils.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Asset Utils.
 *
 * @since 1.0.0
 */
class Asset {

	/**
	 * Enqueues JavaScript and CSS assets.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file        The base filename without extension.
	 * @param array  $js_data     Optional. Data to localize to the script. Default empty array.
	 * @param array  $style_files Optional. Additional CSS files to enqueue. Default empty array.
	 *
	 * @return void
	 */
	public static function enqueue( $file, $js_data = array(), $style_files = array() ) {
		$asset_file = include Utils::dir_path( "build/$file.asset.php" );

		foreach ( $style_files as $style_file ) {
			wp_enqueue_style( "adbtl-{$style_file}-style", Utils::dir_url( "build/$style_file.css" ), array(), $asset_file['version'] );
		}

		wp_register_script( "adbtl-$file", Utils::dir_url( "build/$file.js" ), $asset_file['dependencies'], $asset_file['version'], true );

		if ( ! empty( $js_data ) ) {
			$key = array_key_first( $js_data );
			wp_localize_script( "adbtl-$file", $key, $js_data[ $key ] );
		}

		wp_set_script_translations( "adbtl-$file", 'admin-debug-tools', Utils::dir_path( 'languages' ) );
		wp_enqueue_script( "adbtl-$file" );
	}
}
