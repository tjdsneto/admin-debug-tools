<?php
/**
 * Admin Debug Tools Log Parser.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Core;

use AdminDebugTools\Plugin\Utils\Date;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collection class for managing log lines.
 *
 * Handles groups of LogLine objects along with metadata about the source file.
 *
 * @since 1.0.0
 */
class LogLineCollection {

	/**
	 * Starting line number in source file.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $start_line;

	/**
	 * Last line number in source file.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $last_line;

	/**
	 * Array of LogLine objects.
	 *
	 * @since 1.0.0
	 * @var LogLine[]
	 */
	protected array $lines;

	/**
	 * File size in bytes.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $file_size;

	/**
	 * Last modified timestamp.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected int $last_modified;

	/**
	 * Path to the source file.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected string $file_path;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param FileContent $file_content File content object to initialize from.
	 */
	public function __construct( FileContent $file_content ) {
		$this->start_line    = $file_content->get_start_line();
		$this->last_line     = $file_content->get_last_line();
		$this->file_size     = $file_content->get_file_size();
		$this->last_modified = $file_content->get_last_modified();
		$this->file_path     = $file_content->get_file_path();
		$this->lines         = array();
	}

	/**
	 * Check if the collection is empty.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if empty, false otherwise.
	 */
	public function is_empty() {
		return empty( $this->lines );
	}

	/**
	 * Get all log lines.
	 *
	 * @since 1.0.0
	 * @return LogLine[] Array of log line objects.
	 */
	public function get_lines() {
		return $this->lines;
	}

	/**
	 * Set the log lines array.
	 *
	 * @since 1.0.0
	 * @param LogLine[] $lines Array of log line objects.
	 * @return self
	 */
	public function set_lines( array $lines ) {
		$this->lines = $lines;
		return $this;
	}

	/**
	 * Get a specific log line by index.
	 *
	 * @since 1.0.0
	 * @param int $index Array index of the log line.
	 * @return LogLine|null Log line object if exists, null otherwise.
	 */
	public function get( int $index ) {
		return $this->lines[ $index ] ?? null;
	}

	/**
	 * Slice the log lines array starting from given index.
	 *
	 * @since 1.0.0
	 * @param int $start Starting index to slice from.
	 */
	public function slice( int $start ) {
		$this->start_line += $start;
		$this->lines       = array_slice( $this->lines, $start );
	}

	/**
	 * Convert collection to array format.
	 *
	 * @since 1.0.0
	 * @return array Collection data as associative array.
	 */
	public function to_array() {
		return array(
			'file_path'     => $this->file_path,
			'start'         => $this->start_line,
			'end'           => $this->last_line,
			'lines'         => array_map(
				function ( LogLine $line ) {
					return $line->to_array();
				},
				$this->lines
			),
			'file_size'     => $this->file_size,
			'last_modified' => Date::format_date_str( $this->last_modified ),
		);
	}
}
