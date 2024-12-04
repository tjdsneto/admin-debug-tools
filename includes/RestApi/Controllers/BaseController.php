<?php
/**
 * Admin Debug Tools Base Controller.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\RestApi\Controllers;

use AdminDebugTools\Plugin\Exceptions\RestApi\InvalidParamException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Base Controller.
 *
 * @since 1.0.0
 */
abstract class BaseController {

	/**
	 * Retrieves the specified parameter from the request and sanitizes it.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @param string           $key The parameter key to retrieve.
	 * @param string           $type The expected type of the parameter.
	 * @param array            $valid_options An array of valid options for the parameter (optional).
	 *
	 * @return mixed The sanitized parameter value.
	 *
	 * @throws InvalidParamException If the parameter value is invalid.
	 */
	public function get_param( \WP_REST_Request $request, $key, $type, $valid_options = array() ) {
		$value = $request->get_param( $key );

		return $this->validate_param( $key, $value, $type, $valid_options );
	}

	/**
	 * Retrieves and validates the JSON parameters from the request.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @param array            $schema The schema to validate against. This is an associative array where the keys are the names of the sections in the JSON object and the values are associative arrays of the properties and their types in each section.
	 *
	 * @return mixed The validated JSON value.
	 *
	 * @throws InvalidParamException If the parameter value is invalid.
	 */
	public function get_json_params( \WP_REST_Request $request, $schema ) {
		$json = $request->get_json_params();

		if ( empty( $json ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			throw new InvalidParamException( __( 'The payload is required.', 'admin-debug-tools' ) );
		}

		$validated_json = array();

		foreach ( $schema as $section => $properties ) {
			if ( ! isset( $json[ $section ] ) ) {
				// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
				/* translators: 1: JSON property name. */
				throw new InvalidParamException( sprintf( __( 'Missing JSON property: %1$s', 'admin-debug-tools' ), $section ) );
				// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			if ( is_array( $properties ) ) {
				$value = $this->validate_json( $json[ $section ], $properties );
			} else {
				$value = $this->validate_param( $section, $json[ $section ], $properties );
			}

			$validated_json[ $section ] = $value;
		}

		return $validated_json;
	}

	/**
	 * Validates a JSON object against a provided schema.
	 *
	 * This function iterates over each section and property in the schema.
	 * If a section or property is missing in the JSON object, it throws an InvalidParamException.
	 * If the properties are an array, it recursively validates the JSON object.
	 * Otherwise, it sanitizes the JSON object using the provided sanitization function.
	 *
	 * @since 1.0.0
	 *
	 * @param array $json The JSON object to validate.
	 * @param array $schema The schema to validate against. This is an associative array where the keys are the names of the sections in the JSON object and the values are associative arrays of the properties and their types in each section.
	 *
	 * @return array The validated JSON object.
	 *
	 * @throws InvalidParamException If a section or property is missing in the JSON object, or if the type of a property value is not as expected.
	 */
	public function validate_json( $json, $schema ) {
		$validated_json = array();

		foreach ( $schema as $section => $properties ) {
			if ( ! isset( $json[ $section ] ) ) {
				// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
				/* translators: 1: JSON property name. */
				throw new InvalidParamException( sprintf( __( 'Missing JSON property: %1$s', 'admin-debug-tools' ), $section ) );
				// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
			}

			if ( is_array( $properties ) ) {
				$value = $this->validate_json( $json[ $section ], $properties );
			} else {
				$value = $this->validate_param( $section, $json[ $section ], $properties );
			}

			$validated_json[ $section ] = $value;
		}

		return $validated_json;
	}

	/**
	 * Runs validations and returns the sanitized param value.
	 *
	 * The validation rules can be composed of the string 'required' and/or the param type.
	 *
	 * Example: 'required|int' or 'int'
	 *
	 * @since 1.0.0
	 *
	 * @param string $name The field name.
	 * @param mixed  $value The value to sanitize.
	 * @param string $validation_rule The validation rules.
	 * @param array  $valid_options An array of valid options for the parameter (optional).
	 *
	 * @return mixed The sanitized value.
	 *
	 * @throws InvalidParamException If the parameter value is invalid.
	 * @throws \Exception If the validation rules are invalid.
	 */
	public function validate_param( $name, $value, $validation_rule, $valid_options = array() ) {
		$is_required = false;
		$type        = $validation_rule;

		if ( strpos( $validation_rule, '|' ) !== false ) {
			$rules = explode( '|', $validation_rule );

			if ( 2 < count( $rules ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
				throw new \Exception( __( 'Invalid validation rule.', 'admin-debug-tools' ) );
			}

			foreach ( $rules as $rule ) {
				if ( 'required' === $rule ) {
					$is_required = true;
					continue;
				}

				$type = $rule;
			}
		}

		if ( $is_required && empty( $value ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			/* translators: Invalid param error. 1: the param name. */
			throw new InvalidParamException( sprintf( __( 'Missing required value for "%1$s" param.', 'admin-debug-tools' ), $name ) );
			// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$valid = $this->validate_type( $value, $type );

		if ( ! $valid ) {
			// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			/* translators: Invalid param error. 1: the invalid value, 2: the param name. */
			throw new InvalidParamException( sprintf( __( 'Invalid value "%1$s" for "%2$s" param.', 'admin-debug-tools' ), $value, $name ) );
			// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		$value = $this->sanitize_type( $value, $type );

		if ( ! empty( $value ) && ! empty( $valid_options ) && ! in_array( $value, $valid_options, true ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			/* translators: Invalid param error. 1: the invalid value, 2: the param name. */
			throw new InvalidParamException( sprintf( __( 'Invalid value "%1$s" for "%2$s" param.', 'admin-debug-tools' ), $value, $name ) );
			// phpcs:enable WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}

		return $value;
	}

	/**
	 * Validates the specified value based on the expected type.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value The value to sanitize.
	 * @param string $type The expected type of the value.
	 *
	 * @return bool Whether the value is valid or not.
	 */
	public function validate_type( $value, $type ) {
		switch ( $type ) {
			case 'email':
				return is_email( $value );
			default:
				return true;
		}
	}

	/**
	 * Sanitizes the specified value based on the expected type.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $value The value to sanitize.
	 * @param string $type The expected type of the value.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitize_type( $value, $type ) {
		switch ( $type ) {
			case 'int':
				return intval( $value );
			case 'string':
				return sanitize_text_field( $value );
			case 'email':
				return sanitize_email( $value );
			case 'key':
				return sanitize_key( $value );
			case 'bool':
				return boolval( $value );
			case 'array':
				return is_array( $value ) ? $value : array();
			default:
				return $value;
		}
	}
}
