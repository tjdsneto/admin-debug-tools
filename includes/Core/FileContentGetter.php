<?php
/**
 * Admin Debug Tools File Content Getter.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools File Content Getter.
 *
 * @since 1.0.0
 */
class FileContentGetter {

	/**
	 * The file object.
	 *
	 * @var \SplFileObject
	 */
	protected $file;

	/**
	 * The last line number.
	 *
	 * @var int
	 */
	protected $last_line;

	/**
	 * The file size.
	 *
	 * @var int
	 */
	protected $file_size;

	/**
	 * The last modified time.
	 *
	 * @var int
	 */
	protected $last_modified;

	/**
	 * Constructor.
	 *
	 * @param \SplFileObject $file The file object.
	 */
	public function __construct( \SplFileObject $file ) {
		$this->file = $file;
		$this->file->seek( PHP_INT_MAX ); // Go to the end of the file to get the total line count.
		$this->last_line     = $this->file->key() + 1; // We have to add 1, because the key starts at zero.
		$this->file_size     = $this->file->getSize();
		$this->last_modified = $this->file->getMTime();
	}

	/**
	 * Get the last X lines from the file.
	 *
	 * @since 1.0.0
	 *
	 * @param int $line_number The number of lines to get from the end of the file or the end line.
	 * @param int $end_line    The ending line number. When provided, it should return the last X lines from that end line.
	 *
	 * @return FileContent The file content.
	 */
	public function get_last_lines( int $line_number, int $end_line = null ) {
		$start_line = max( ( null !== $end_line ? $end_line : $this->last_line ) - $line_number, 0 );
		return $this->get_from_line( $start_line, $end_line );
	}

	/**
	 * Get the file content from a specific line.
	 *
	 * @since 1.0.0
	 *
	 * @param int $start_line The starting line number.
	 * @param int $end_line   The ending line number. WHen provided, the function will stop at this line.
	 *
	 * @return FileContent The file content.
	 */
	public function get_from_line( int $start_line, int $end_line = null ) {
		$seek_line = $start_line;
		$this->file->rewind(); // Go back to the beginning of the file.
		$this->file->seek( $seek_line ); // Move to the starting line.
		$lines = array();
		while ( ! $this->file->eof() && ( null === $end_line || $seek_line < $end_line ) ) {
			$content = $this->file->fgets();

			if ( ! empty( trim( $content ) ) ) {
				$lines[ ++$seek_line ] = $content;
			}
		}
		$this->last_line = $seek_line;

		return new FileContent( $this->file->getRealPath(), $start_line, $this->last_line, $lines, $this->file_size, $this->last_modified );
	}

	/**
	 * Get updates from the file since the last check.
	 *
	 * @since 1.0.0
	 *
	 * @return FileContent The updated file content.
	 */
	public function get_updates() {
		clearstatcache();

		$file_size = $this->file->getSize();

		if ( $file_size < $this->file_size ) {
			$this->last_line = 0;
		}

		$this->file_size     = $file_size;
		$this->last_modified = $this->file->getMTime();
		return $this->get_from_line( $this->last_line );
	}
}
