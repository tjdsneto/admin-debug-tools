<?php
/**
 * Admin Debug Tools Filesystem.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Filesystem utility class that wraps WP_Filesystem.
 *
 * @since 1.0.0
 */
class Filesystem {

	/**
	 * The WP_Filesystem instance.
	 *
	 * @since 1.0.0
	 *
	 * @var \WP_Filesystem
	 */
	private $wp_filesystem;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->initialize_filesystem();
	}

	/**
	 * Initializes the WP_Filesystem.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if filesystem was initialized successfully.
	 */
	private function initialize_filesystem() {
		global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			return false;
		}

		$this->wp_filesystem = $wp_filesystem;
		return true;
	}

	/**
	 * Reads contents from a file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Path to the file.
	 * @return string|false The file contents or false on failure.
	 */
	public function get_contents( $file ) {
		return $this->wp_filesystem->get_contents( $file );
	}

	/**
	 * Writes contents to a file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file     Path to the file.
	 * @param string $contents The contents to write.
	 * @return bool True on success, false on failure.
	 */
	public function put_contents( $file, $contents ) {
		return $this->wp_filesystem->put_contents( $file, $contents );
	}

	/**
	 * Checks if a file or directory exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path to check.
	 * @return bool Whether the path exists.
	 */
	public function exists( $path ) {
		return $this->wp_filesystem->exists( $path );
	}

	/**
	 * Checks if path is a directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path Path to check.
	 * @return bool Whether path is a directory.
	 */
	public function is_dir( $path ) {
		return $this->wp_filesystem->is_dir( $path );
	}

	/**
	 * Creates a directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path  Path for new directory.
	 * @param mixed  $chmod Optional. The permissions as octal number (0644 for example).
	 * @param mixed  $chown Optional. A user name or number.
	 * @param mixed  $chgrp Optional. A group name or number.
	 * @return bool True on success, false on failure.
	 */
	public function mkdir( $path, $chmod = false, $chown = false, $chgrp = false ) {
		return $this->wp_filesystem->mkdir( $path, $chmod, $chown, $chgrp );
	}

	/**
	 * Deletes a file or directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $path      Path to remove.
	 * @param bool   $recursive Optional. If set to true, changes file group recursively.
	 * @return bool True on success, false on failure.
	 */
	public function delete( $path, $recursive = false ) {
		return $this->wp_filesystem->delete( $path, $recursive );
	}

	/**
	 * Gets the filesystem method.
	 *
	 * @since 1.0.0
	 *
	 * @return string The filesystem method.
	 */
	public function get_method() {
		return $this->wp_filesystem->method;
	}

	/**
	 * Checks if a file is writable.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Path to file.
	 * @return bool Whether the file is writable.
	 */
	public function is_writable( $file ) {
		return $this->wp_filesystem->is_writable( $file );
	}

	/**
	 * Gets the file permissions.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Path to the file.
	 * @return string|false Mode in octal form, false if doesn't exist.
	 */
	public function get_chmod( $file ) {
		return $this->wp_filesystem->getchmod( $file );
	}
}
