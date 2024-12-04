<?php
/**
 * Admin Debug Tools Log Parser.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to handle file content and metadata.
 *
 * @since 1.0.0
 */
class FileContent {

	/**
	 * Starting line number.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $start_line;

	/**
	 * Last line number.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $last_line;

	/**
	 * Array of file lines.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected array $lines;

	/**
	 * File size in bytes.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $file_size;

	/**
	 * Last modified timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $last_modified;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $file_path;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file_path     Path to the file.
	 * @param int    $start_line    Starting line number.
	 * @param int    $last_line     Last line number.
	 * @param array  $lines         Array of file lines.
	 * @param int    $file_size     File size in bytes.
	 * @param int    $last_modified Last modified timestamp.
	 */
	public function __construct( $file_path, $start_line, $last_line, $lines, $file_size, $last_modified ) {
		$this->file_path     = $file_path;
		$this->start_line    = $start_line;
		$this->last_line     = $last_line;
		$this->lines         = $lines;
		$this->file_size     = $file_size;
		$this->last_modified = $last_modified;
	}

	/**
	 * Convert object to array format.
	 *
	 * @since 1.0.0
	 *
	 * @return array File content data as associative array.
	 */
	public function to_array() {
		return array(
			'file_path'     => $this->file_path,
			'start'         => $this->start_line,
			'end'           => $this->last_line,
			'lines'         => $this->lines,
			'file_size'     => $this->file_size,

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- I intentioanlly want to use the server's timezone.
			'last_modified' => date( 'Y-m-d H:i:s', $this->last_modified ),
		);
	}

	/**
	 * Get file path.
	 *
	 * @since 1.0.0
	 *
	 * @return string File path.
	 */
	public function get_file_path() {
		return $this->file_path;
	}

	/**
	 * Get start line number.
	 *
	 * @since 1.0.0
	 *
	 * @return int Start line number.
	 */
	public function get_start_line() {
		return $this->start_line;
	}

	/**
	 * Get file lines.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of file lines.
	 */
	public function get_lines() {
		return $this->lines;
	}

	/**
	 * Get file size.
	 *
	 * @since 1.0.0
	 *
	 * @return int File size in bytes.
	 */
	public function get_file_size() {
		return $this->file_size;
	}

	/**
	 * Get last modified timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @return int Last modified timestamp.
	 */
	public function get_last_modified() {
		return $this->last_modified;
	}

	/**
	 * Get last line number.
	 *
	 * @since 1.0.0
	 *
	 * @return int Last line number.
	 */
	public function get_last_line() {
		return $this->last_line;
	}
}
