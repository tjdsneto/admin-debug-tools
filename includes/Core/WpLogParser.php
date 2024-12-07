<?php
/**
 * Admin Debug Tools WP Log Parser.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\Core;

use AdminDebugTools\Plugin\Utils\Date;
use AdminDebugTools\Plugin\Utils\Filesystem;
use SplFileObject;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools WP Log Parser.
 *
 * Parses WordPress log files and provides methods to read, analyze and manipulate log content.
 * Handles log file operations like reading, clearing, backing up, and parsing log entries.
 * Provides functionality to extract stack traces, format file paths, and generate editor links.
 *
 * @since 1.0.0
 */
class WpLogParser {

	/**
	 * File content getter instance.
	 *
	 * @since 1.0.0
	 * @var FileContentGetter
	 */
	protected FileContentGetter $file_getter;

	/**
	 * File object instance.
	 *
	 * @since 1.0.0
	 * @var SplFileObject
	 */
	protected SplFileObject $file;

	/**
	 * Log parser instance.
	 *
	 * @since 1.0.0
	 * @var LogParser
	 */
	protected LogParser $log_parser;

	/**
	 * Filesystem instance.
	 *
	 * @since 1.0.0
	 * @var Filesystem
	 */
	protected Filesystem $filesystem;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param SplFileObject $file File object to parse.
	 */
	public function __construct( SplFileObject $file ) {
		$this->file        = $file;
		$this->file_getter = new FileContentGetter( $this->file );
		$this->log_parser  = new LogParser();
		$this->filesystem  = new Filesystem();
	}

	/**
	 * Check if log file exists.
	 *
	 * @since 1.0.0
	 * @return bool True if file exists.
	 */
	public function exists() {
		return $this->file->isFile();
	}

	/**
	 * Get log file size.
	 *
	 * @since 1.0.0
	 * @return int File size in bytes.
	 */
	public function get_size() {
		return $this->file->getSize();
	}

	/**
	 * Get log file path.
	 *
	 * @since 1.0.0
	 * @return string Full path to log file.
	 */
	public function get_log_file_path() {
		return $this->file->getPathname();
	}

	/**
	 * Clear log file contents.
	 *
	 * @since 1.0.0
	 * @param bool $should_backup Whether to backup file before clearing.
	 * @throws \Exception If unable to open or clear file.
	 */
	public function clear( $should_backup = false ) {
		if ( $should_backup ) {
			$backup_path = $this->backup();
		}

		// Clear the file using WP_Filesystem.
		if ( ! $this->filesystem->put_contents( $this->file->getPathname(), '' ) ) {
			throw new \Exception( __( 'Unable to clear file contents.', 'admin-debug-tools' ) );
		}

		if ( $should_backup ) {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is an intentional log message.
			// translators: %s: The path where the log file is saved.
			error_log( sprintf( __( "Log file saved at '%s' and cleared.", 'admin-debug-tools' ), $backup_path ) );
			// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- This is an intentional log message.
		error_log( __( 'Log file cleared.', 'admin-debug-tools' ) );
	}

	/**
	 * Create backup of log file.
	 *
	 * @since 1.0.0
	 * @return string Path to backup file.
	 * @throws \Exception If unable to create backup.
	 */
	private function backup() {
		$timestamp   = ( new \DateTime() )->format( 'Ymd_His' );
		$file_path   = $this->file->getPathname();
		$file_info   = pathinfo( $file_path );
		$backup_path = $file_info['dirname'] . '/' . $file_info['filename'] . '_' . $timestamp . '.' . $file_info['extension'];

		if ( ! file_exists( $file_path ) || ! copy( $file_path, $backup_path ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			throw new \Exception( __( 'Unable to create backup file.', 'admin-debug-tools' ) );
		}

		return $backup_path;
	}

	/**
	 * Get last N lines from log file.
	 *
	 * @since 1.0.0
	 * @param int  $line_number Number of lines to get.
	 * @param int  $end_line    Ending line number.
	 * @param bool $strict Whether to enforce exact line count.
	 * @return LogLineCollection Collection of parsed log lines.
	 */
	public function get_last_lines( int $line_number, int $end_line = null, $strict = false ) {
		$file_content = $this->file_getter->get_last_lines( $line_number, $end_line );

		$log_line_collection = $this->parse_lines( $file_content );

		if ( ! $strict && ! $log_line_collection->is_empty() ) {
			$lines                   = $log_line_collection->get_lines();
			$first_non_children_line = null;

			foreach ( $lines as $index => $line ) {
				if ( ! $line->is_children() ) {
					$first_non_children_line = $index;
					break;
				}
			}

			if ( null !== $first_non_children_line ) {
				if ( $first_non_children_line > 0 ) {
					$log_line_collection->slice( $first_non_children_line );
				}
			} else {
				return $this->get_last_lines( $line_number * 2, $end_line );
			}
		}

		return $log_line_collection;
	}

	/**
	 * Get lines starting from specified line number.
	 *
	 * @since 1.0.0
	 * @param int $start_line Starting line number.
	 * @return LogLineCollection Collection of parsed log lines.
	 */
	public function get_from_line( int $start_line ) {
		$file_content = $this->file_getter->get_from_line( $start_line );

		return $this->parse_lines( $file_content );
	}

	/**
	 * Get updates since last read.
	 *
	 * @since 1.0.0
	 * @return LogLineCollection Collection of new log lines.
	 */
	public function get_updates() {
		$file_content = $this->file_getter->get_updates();

		return $this->parse_lines( $file_content );
	}

	/**
	 * Parse file content into log line collection.
	 *
	 * @since 1.0.0
	 * @param FileContent $file_content File content to parse.
	 * @return LogLineCollection Collection of parsed log lines.
	 */
	public function parse_lines( FileContent $file_content ) {
		$log_collection = new LogLineCollection( $file_content );

		$parsed_log_lines = $this->log_parser->parse( $file_content->get_lines() );

		$parsed_log_lines = array_map( array( $this, 'parse_line' ), $parsed_log_lines );

		$log_collection->set_lines( $parsed_log_lines );

		return $log_collection;
	}

	/**
	 * Parse individual log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $line Log line to parse.
	 * @return LogLine Parsed log line.
	 */
	public function parse_line( LogLine $line ) {
		$line = $this->localize_datetime( $line );
		$line = $this->normalize_stacktrace_order( $line );
		$line = $this->conseal_directory( $line );

		if ( $line->has_children() ) {
			$line->set_children( array_map( array( $this, 'parse_line' ), $line->get_children() ) );
		}

		return $line;
	}

	/**
	 * Localize datetime in log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $line Log line to process.
	 * @return LogLine Processed log line.
	 */
	public function localize_datetime( LogLine $line ) {
		if ( ! $line->has_date() ) {
			return $line;
		}

		$line->set_extra( 'datetime_formatted', Date::format_date_str( $line->get_date() ) );

		return $line;
	}

	/**
	 * Normalize stack trace order in log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $line Log line to normalize.
	 * @return LogLine Normalized log line.
	 */
	public function normalize_stacktrace_order( LogLine $line ) {
		// If there are no children, there's nothing to do.
		if ( ! $line->has_children() ) {
			return $line;
		}

		// I want to find the first index of the $line['children'] that meets a certain criteria.

		$abs_path = str_replace( '\\', '/', ABSPATH );
		$abs_path = str_replace( '/', '\/', rtrim( ABSPATH, '/' ) );
		$regex    = "/{$abs_path}\/index.php/";

		$index = null;
		foreach ( $line->get_children() as $i => $child ) {
			if ( preg_match( $regex, $child->get_message(), $matches ) ) {
				$index = $i;
				break;
			}
		}

		// If we can't find the index, we can't know the correct order.
		if ( null === $index ) {
			return $line;
		}

		// If the index is in the first or second position, the order must be inverted.
		if ( 0 === $index || 1 === $index ) {
			// Get the part of the array from $index to the end and reverse it.
			$reversed_part = array_reverse( array_slice( $line->get_children(), $index ) );

			$children = $line->get_children();

			// Replace the original part of the array with the reversed part.
			array_splice( $children, $index, count( $reversed_part ), $reversed_part );

			$line->set_children( $children );

			return $line;
		}

		return $line;
	}

	/**
	 * Conceal directory paths in log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $line Log line to process.
	 * @return LogLine Processed log line.
	 */
	public function conseal_directory( LogLine $line ) {
		if ( strpos( $line->get_message(), '/var/www/testing-site' ) !== false ) {
			// Might be a stacktrace.

			$abs_path = str_replace( '\\', '/', '/var/www/testing-site' );
			$abs_path = str_replace( '/', '\/', rtrim( '/var/www/testing-site', '/' ) );

			// PHP Warning:  Undefined property: SamplePlugin\Plugin\RestApi\Controllers\SampleController::$repository in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php on line 87.
			// /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php(300): SamplePlugin\Plugin\RestApi\Controllers\SampleController->get(Object(WP_REST_Request)).
			$pattern1 = "/({$abs_path}\/[^:]+\.php)\((\d+)\):\s/";

			// PHP   1. {main}() /var/www/testing-site/index.php:0
			// #0 /var/www/testing-site/wp-includes/class-wp-hook.php(324): SamplePlugin\Plugin\Bootstrap\Admin\Pages->function_name('').
			// /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/RestApi.php:300 - get().
			// Fatal error:  Uncaught Error: Call to a member function get_entries_count() on null in /var/www/testing-site/wp-content/plugins/sample-plugin/includes/RestApi/Controllers/SampleController.php:87.
			$pattern2 = "/({$abs_path}\/[^:]+\.php):(\d+)(\s-\s)?/";

			// PHP Warning:  mysqli_real_connect(): (HY000/2002): No such file or directory in /var/www/testing-site/wp-includes/class-wpdb.php on line 1987.
			$pattern3 = "/({$abs_path}\/[^:]+\.php) on line (\d+)/";

			// Do not use is_children here. Some children are not stack traces.
			$path_replacement = 'trace' === $line->get_type() ? '' : '{{fileLink}}';

			if ( preg_match( $pattern1, $line->get_message(), $matches ) ) {
				$line->set_extra( 'stack_file', $matches[1] );
				$line->set_extra( 'stack_line', $matches[2] );

				$full_path     = $matches[1];
				$relative_path = str_replace( ABSPATH, '', $full_path );
				$line->set_extra( 'stack_file_formatted', $relative_path );
				$link = $this->build_file_link( $full_path, $matches[2] );
				if ( ! empty( $link ) ) {
					$line->set_extra( 'stack_file_link', $link );
				}

				$path_match = $matches[0];

				if ( preg_match( '/^#(\d+)\s/', $line->get_message(), $matches ) ) {
					$line->set_extra( 'stack_order', $matches[1] );
					$line->set_message( trim( preg_replace( '/^' . preg_quote( $matches[0], '/' ) . '/', '', $line->get_message() ) ) );
				}

				$line->set_message( trim( str_replace( $path_match, $path_replacement, $line->get_message() ) ) );
			} elseif ( preg_match( $pattern2, $line->get_message(), $matches ) ) {
				$line->set_extra( 'stack_file', $matches[1] );
				$line->set_extra( 'stack_line', $matches[2] );

				$full_path     = $matches[1];
				$relative_path = str_replace( ABSPATH, '', $full_path );
				$line->set_extra( 'stack_file_formatted', $relative_path );
				$link = $this->build_file_link( $full_path, $matches[2] );
				if ( ! empty( $link ) ) {
					$line->set_extra( 'stack_file_link', $link );
				}

				$path_match = $matches[0];

				$line->set_message( trim( str_replace( $path_match, $path_replacement, $line->get_message() ) ) );
			} elseif ( preg_match( $pattern3, $line->get_message(), $matches ) ) {
				$line->set_extra( 'stack_file', $matches[1] );
				$line->set_extra( 'stack_line', $matches[2] );

				$full_path     = $matches[1];
				$relative_path = str_replace( ABSPATH, '', $full_path );
				$line->set_extra( 'stack_file_formatted', $relative_path );
				$link = $this->build_file_link( $full_path, $matches[2] );
				if ( ! empty( $link ) ) {
					$line->set_extra( 'stack_file_link', $link );
				}

				$path_match = $matches[0];

				$line->set_message( trim( str_replace( $path_match, $path_replacement, $line->get_message() ) ) );
			}
		}

		return $line;
	}

	/**
	 * Build file link for editor.
	 *
	 * @since 1.0.0
	 * @param string $file_path File path.
	 * @param int    $line_number Line number.
	 * @return string|null Editor URL or null if not available.
	 */
	public function build_file_link( string $file_path, int $line_number ) {
		$type           = $this->get_file_type( $file_path );
		$wp_content_dir = rtrim( WP_CONTENT_DIR, '/' );

		// TODO: consider defined( 'DISALLOW_FILE_EDIT' ).

		switch ( $type ) {
			case 'plugin':
				$plugin_path  = str_replace( $wp_content_dir . '/plugins/', '', $file_path );
				$plugin_paths = explode( '/', $plugin_path );
				$plugin       = $plugin_paths[0];
				$file         = $plugin_paths[1];
				return add_query_arg(
					array(
						'file'   => $plugin_path,
						'plugin' => $this->get_plugin_path( $plugin ),
						'line'   => $line_number,
					),
					admin_url( 'plugin-editor.php' )
				);
			case 'theme':
				$theme_path  = str_replace( $wp_content_dir . '/themes/', '', $file_path );
				$theme_paths = explode( '/', $theme_path );
				$theme       = $theme_paths[0];
				$file        = $theme_paths[1];
				return add_query_arg(
					array(
						'file'  => $theme_path,
						'theme' => $theme,
						'line'  => $line_number,
					),
					admin_url( 'theme-editor.php' )
				);
			case 'core':
				$core_path  = str_replace( ABSPATH, '', $file_path );
				$wp_version = get_bloginfo( 'version' );
				return 'https://github.com/WordPress/wordpress/blob/' . $wp_version . '/' . $core_path . "#L{$line_number}";
		}

		return null;
	}

	/**
	 * Get file type based on path.
	 *
	 * @since 1.0.0
	 * @param string $file_path File path.
	 * @return string File type (plugin|theme|mu-plugin|core|unknown).
	 */
	public function get_file_type( string $file_path ) {
		$wp_content_dir = rtrim( WP_CONTENT_DIR, '/' ); // This will give us the path to the wp-content directory.
		$wp_dir         = ABSPATH; // This will give us the path to the WordPress directory.

		if ( strpos( $file_path, $wp_content_dir . '/plugins/' ) !== false ) {
			return 'plugin';
		} elseif ( strpos( $file_path, $wp_content_dir . '/themes/' ) !== false ) {
			return 'theme';
		} elseif ( strpos( $file_path, $wp_content_dir . '/mu-plugins/' ) !== false ) {
			return 'mu-plugin';
		} elseif ( strpos( $file_path, $wp_dir ) !== false ) {
			return 'core';
		} else {
			return 'unknown';
		}
	}

	/**
	 * Get plugin file path from directory name.
	 *
	 * @since 1.0.0
	 * @param string $plugin_dir Plugin directory name.
	 * @return string|null Plugin file path or null if not found.
	 */
	protected function get_plugin_path( string $plugin_dir ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Get all installed plugins.
		$all_plugins = get_plugins();

		// Iterate through the plugins to find the one that matches the slug.
		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$plugin_directory = dirname( $plugin_path );
			if ( $plugin_directory === $plugin_dir ) {
				return $plugin_path; // Return the plugin name in the format 'plugin-directory/plugin-file.php'.
			}
		}

		return null; // Return null if no plugin matches the slug.
	}
}
