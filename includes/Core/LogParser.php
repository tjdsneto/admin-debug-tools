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
 * Admin Debug Tools Log Parser.
 *
 * Parses log files and extracts structured data like dates, log types, and relationships between log entries.
 *
 * @since 1.0.0
 */
class LogParser {

	/**
	 * Parse an array of log lines into structured LogLine objects.
	 *
	 * @since 1.0.0
	 * @param array $lines Array of raw log lines to parse.
	 * @return array Array of parsed LogLine objects.
	 */
	public function parse( array $lines ) {
		$parsed = array();

		$prev_key = null;
		foreach ( $lines as $line_number => $line ) {
			$parsed_line = $this->parse_line( $line, $parsed[ $prev_key ] ?? null );
			$parsed_line->set_line_number( $line_number );

			if ( $parsed_line->is_children() && null !== $prev_key ) {
				$parsed[ $prev_key ]->add_child( $parsed_line );
			} else {
				$parsed[] = $parsed_line;
				$prev_key = count( $parsed ) - 1;
			}
		}

		return $parsed;
	}

	/**
	 * Parse a single log line into a LogLine object.
	 *
	 * @since 1.0.0
	 * @param string       $line            Raw log line to parse.
	 * @param LogLine|null $prev_parsed_line Previous parsed log line for context.
	 * @return LogLine Parsed log line object.
	 */
	public function parse_line( string $line, ?LogLine $prev_parsed_line = null ) {
		$log_line = new LogLine( $line );

		$log_line = $this->parse_date( $log_line );

		$log_line = $this->parse_type( $log_line );

		$log_line = $this->parse_children( $log_line, $prev_parsed_line );

		return $log_line;
	}

	/**
	 * Parse and extract date information from a log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $log_line Log line object to parse date from.
	 * @return LogLine Modified log line with extracted date info.
	 */
	public function parse_date( LogLine $log_line ) {
		$regex = '/^\[\d{2}-\w{3}-\d{4} \d{2}:\d{2}:\d{2} UTC\]/';

		// TODO: try to parse date from this other format: [2024-06-17 19:52:45].

		if ( preg_match( $regex, $log_line->get_message(), $matches ) ) {
			$date_str = substr( $matches[0], 1, -1 );
			$date     = \DateTime::createFromFormat( 'd-M-Y H:i:s e', $date_str );

			$new_message = substr( $log_line->get_message(), strlen( $matches[0] ) + 1 );

			$log_line->set_message( $new_message )
				->set_date( $date_str, $date->getTimestamp() );
		}

		return $log_line;
	}

	/**
	 * Parse and extract log type information from a log line.
	 *
	 * @since 1.0.0
	 * @param LogLine $log_line Log line object to parse type from.
	 * @return LogLine Modified log line with extracted type info.
	 */
	public function parse_type( LogLine $log_line ) {
		$map = array(
			'PHP Notice'                  => 'notice',
			'PHP Warning'                 => 'warning',
			'PHP Fatal error'             => 'error',
			'PHP Parse error'             => 'error',
			'PHP Deprecated'              => 'deprecation',
			'PHP Recoverable fatal error' => 'error',
			'PHP User Error'              => 'error',
			'PHP User Warning'            => 'warning',
			'PHP User Notice'             => 'notice',
			'PHP Strict Standards'        => 'warning',
			'PHP Core Warning'            => 'warning',
			'PHP Core Error'              => 'error',
			'PHP Core Notice'             => 'notice',
			'PHP Compile Error'           => 'error',
			'PHP Compile Warning'         => 'warning',
			'PHP Compile Notice'          => 'notice',
			'PHP Stack trace'             => 'trace',
			'Stack trace'                 => 'trace',
		);

		foreach ( $map as $key => $type ) {
			$regex = "/^(($key):\s?)/i";

			if ( preg_match( $regex, $log_line->get_message(), $matches ) ) {
				$log_line->set_type( $type, $matches[2] );

				// If it's a trace line, we don't need to remove the type from the message. The others are removed just so we can present them in a different way in the UI.
				if ( 'trace' !== $type ) {
					$log_line->set_message( trim( substr( $log_line->get_message(), strlen( $matches[0] ) ) ) );
				}

				return $log_line;
			}
		}

		$match_trace = $this->match_trace_line( $log_line->get_message() );

		if ( $match_trace ) {
			list($trace_order, $replace) = $match_trace;
			$log_line->set_type( 'trace', 'PHP Stack trace' )->set_trace_order( $trace_order );

			$new_message = preg_replace( '/^' . preg_quote( $replace, '/' ) . '/', '', $log_line->get_message() );
			$log_line->set_message( $new_message );
			return $log_line;
		}

		$log_line->set_type( 'log' );

		return $log_line;
	}

	/**
	 * Check if a line matches known stack trace patterns.
	 *
	 * @since 1.0.0
	 * @param string $line Log line to check for trace patterns.
	 * @return array|false Array containing trace order and replacement text if matched, false otherwise.
	 */
	public function match_trace_line( string $line ) {
		// PHP   1. {main}() /var/www/testing-site/index.php:0.
		$regex = '/^PHP\s*(\d+)\.\s/';

		if ( preg_match( $regex, $line, $matches ) ) {
			return array( $matches[1], $matches[0] );
		}

		// #1 /var/www/testing-site/index.php(0): require().
		$regex = '/(^#(\d+)\s+)\/[^:)(]+\(\d+\):/';

		if ( preg_match( $regex, $line, $matches ) ) {
			return array( $matches[2], $matches[1] );
		}

		return false;
	}

	/**
	 * Parse and determine if a log line is a child of another log entry.
	 *
	 * @since 1.0.0
	 * @param LogLine      $log_line Log line to check for child status.
	 * @param LogLine|null $prev_parsed Previous log line for context.
	 * @return LogLine Modified log line with child status determined.
	 */
	public function parse_children( LogLine $log_line, ?LogLine $prev_parsed ) {
		// If there's not a datetime, it's not standalone log line so it must be a children log line.
		if ( ! $log_line->has_date() ) {
			return $log_line->set_is_children( true );
		}

		// If type is trace, so it must be part of a stacktrace output and a children line.
		if ( 'trace' === $log_line->get_type() ) {
			return $log_line->set_is_children( true );
		}

		// It it has a known type, it's a standalone log line.
		if ( 'log' !== $log_line->get_type() ) {
			return $log_line->set_is_children( false );
		}

		// I fwe don't know the previous line, we might not be able to tell if this is a children line or not, so we default to false.
		if ( null === $prev_parsed ) {
			return $log_line->set_is_children( false );
		}

		// If the previous line was a simple log line, this must not be a children line.
		if ( 'log' === $log_line->get_type() ) {
			return $log_line->set_is_children( false );
		}

		/**
		 * If it reached here, that means the previous line was not a simple log line. Then, we can start looking for some known patterns that might indicate this is a children line.
		 */

		// If the message contains the term "stack trace", it mght be a good indicator that is a children line.
		if ( preg_match( '/^PHP Stack trace:/i', $log_line->get_message(), $matches ) ) {
			return $log_line->set_is_children( true );
		}

		$match_trace = $this->match_trace_line( $log_line->get_message() );

		// Stack trace lines usually start with a number followed by a space.
		if ( $match_trace ) {
			$log_line->set_is_children( true )
				->set_trace_order( $match_trace[1] );

			$new_message = preg_replace( '/^' . preg_quote( $match_trace[0], '/' ) . '/', '', $log_line->get_message() );
			return $log_line->set_message( $new_message );
		}

		return $log_line->set_is_children( false );
	}
}
