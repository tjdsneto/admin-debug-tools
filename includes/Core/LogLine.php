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
 * Admin Debug Tools Log Line.
 *
 * @since 1.0.0
 */
class LogLine {

	/**
	 * Raw log line content.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $raw;

	/**
	 * Parsed date string.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null
	 */
	protected ?string $date = null;

	/**
	 * Unix timestamp of the log entry.
	 *
	 * @since 1.0.0
	 *
	 * @var int|null
	 */
	protected ?int $timestamp = null;

	/**
	 * Log entry type identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $type;

	/**
	 * Human-readable label for the log type.
	 *
	 * @since 1.0.0
	 *
	 * @var string|null
	 */
	protected ?string $type_label = null;

	/**
	 * Log message content.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected string $message;

	/**
	 * Line number in the log file.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $line_number;

	/**
	 * Order number for stack traces.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	protected int $trace_order;

	/**
	 * Whether this line is a child of another log entry.
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected bool $is_children;

	/**
	 * Child log entries.
	 *
	 * @since 1.0.0
	 *
	 * @var LogLine[]
	 */
	protected array $children;

	/**
	 * Additional metadata for the log entry.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected array $extra = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $raw Raw log line content.
	 */
	public function __construct( string $raw ) {
		$this->raw     = $raw;
		$this->message = $raw;
	}

	/**
	 * Convert log line to array format.
	 *
	 * @since 1.0.0
	 *
	 * @return array Log line data as associative array.
	 */
	public function to_array() {
		$parsed = array_merge(
			$this->extra,
			array(
				'raw'         => $this->raw,
				'datetime'    => $this->date,
				'timestamp'   => $this->timestamp,
				'type'        => $this->type,
				'type_label'  => $this->type_label,
				'line_number' => $this->line_number,
				'is_children' => $this->is_children,
				'message'     => $this->message,
			)
		);

		if ( isset( $this->trace_order ) ) {
			$parsed['trace_order'] = $this->trace_order;
		}

		if ( ! empty( $this->children ) ) {
			$parsed['children'] = array_map(
				function ( LogLine $line ) {
					return $line->to_array();
				},
				$this->children
			);
		}

		return $parsed;
	}

	/**
	 * Add a child log line.
	 *
	 * @since 1.0.0
	 *
	 * @param LogLine $line Child log line to add.
	 * @return void
	 */
	public function add_child( LogLine $line ) {
		$this->children[] = $line;
	}

	/**
	 * Get raw log line content.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_raw() {
		return $this->raw;
	}

	/**
	 * Get parsed date string.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function get_date() {
		return $this->date;
	}

	/**
	 * Get log entry type.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get human-readable type label.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null
	 */
	public function get_type_label() {
		return $this->type_label;
	}

	/**
	 * Get log message content.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Check if this is a child log entry.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_children() {
		return $this->is_children;
	}

	/**
	 * Check if this log entry has children.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_children() {
		return ! empty( $this->children );
	}

	/**
	 * Get child log entries.
	 *
	 * @since 1.0.0
	 *
	 * @return LogLine[]
	 */
	public function get_children() {
		return $this->children;
	}

	/**
	 * Set child log entries.
	 *
	 * @since 1.0.0
	 *
	 * @param LogLine[] $children Array of child log entries.
	 * @return void
	 */
	public function set_children( array $children ) {
		$this->children = $children;
	}

	/**
	 * Set whether this is a child log entry.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $is_children Whether this is a child entry.
	 * @return self
	 */
	public function set_is_children( bool $is_children ) {
		$this->is_children = $is_children;
		return $this;
	}

	/**
	 * Set log message content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $message Log message content.
	 * @return self
	 */
	public function set_message( string $message ) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Set log entry type and label.
	 *
	 * @since 1.0.0
	 *
	 * @param string      $type       Log entry type identifier.
	 * @param string|null $type_label Human-readable type label.
	 * @return self
	 */
	public function set_type( string $type, ?string $type_label = null ) {
		$this->type       = $type;
		$this->type_label = $type_label;
		return $this;
	}

	/**
	 * Check if log entry has a date.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function has_date() {
		return ! empty( $this->date );
	}

	/**
	 * Set log entry date and timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @param string $date      Formatted date string.
	 * @param int    $timestamp Unix timestamp.
	 * @return self
	 */
	public function set_date( string $date, int $timestamp ) {
		$this->date      = $date;
		$this->timestamp = $timestamp;
		return $this;
	}

	/**
	 * Get log entry timestamp.
	 *
	 * @since 1.0.0
	 *
	 * @return int|null
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}

	/**
	 * Get line number in log file.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_line_number() {
		return $this->line_number;
	}

	/**
	 * Set line number in log file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $line_number Line number.
	 * @return self
	 */
	public function set_line_number( int $line_number ) {
		$this->line_number = $line_number;
		return $this;
	}

	/**
	 * Set trace order number.
	 *
	 * @since 1.0.0
	 *
	 * @param int $trace_order Order number in stack trace.
	 * @return self
	 */
	public function set_trace_order( int $trace_order ) {
		$this->trace_order = $trace_order;
		return $this;
	}

	/**
	 * Get trace order number.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function get_trace_order() {
		return $this->trace_order;
	}

	/**
	 * Set extra metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   Metadata key.
	 * @param mixed  $value Metadata value.
	 * @return self
	 */
	public function set_extra( string $key, $value ) {
		$this->extra[ $key ] = $value;
		return $this;
	}
}
