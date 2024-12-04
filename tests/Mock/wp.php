<?php

if ( ! function_exists( 'format_date_str' ) ) {
	function format_date_str( $thing ) {
		return $thing;
	}
}


if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default_value ) {
		return $default_value;
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	function date_i18n( $format, $timestamp ) {
		return $format;
	}
}

if ( ! function_exists( 'get_bloginfo' ) ) {
	function get_bloginfo( $thing ) {
		switch ( $thing ) {
			case 'version':
				return '6.5';
			default:
				return $thing;
		}
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $thing ) {
		return $thing;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	function add_query_arg( ...$args ) {
		$queryargs = $args[0];
		$url       = $args[1];
		if ( 3 === func_num_args() ) {
			$queryargs = array(
				$args[0] => $args[1],
			);
			$url       = $args[2];
		}

		return $url . '?' . http_build_query( $queryargs );
	}
}

if ( ! function_exists( 'get_plugins' ) ) {
	function get_plugins() {
		return array(
			'sample-plugin/sample-plugin.php' => array(),
		);
	}
}

if ( ! function_exists( 'WP_Filesystem' ) ) {
	function WP_Filesystem() {
		return true;
	}
}
