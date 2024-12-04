<?php
/**
 * Admin Debug Tools REST API.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin\RestApi;

use AdminDebugTools\Plugin\Exceptions\RestApi\RestApiException;
use AdminDebugTools\Plugin\Exceptions\UserFrienldlyException;
use AdminDebugTools\Plugin\Plugin;
use AdminDebugTools\Plugin\RestApi\Controllers\DebugLogController;
use AdminDebugTools\Plugin\RestApi\Controllers\WpConfigController;
use AdminDebugTools\Plugin\Utils\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools REST API.
 *
 * @since 1.0.0
 */
class RestApi {


	/**
	 * The REST API Namespace
	 *
	 *  @since 1.0.0
	 *
	 * @var string The namespace
	 */
	protected $namespace = 'admin-debug-tools/v1';

	/**
	 * Sets up the hooks for the REST API.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );

		// Replace the default WordPress REST API dispatcher with our own.
		add_filter( 'rest_dispatch_request', array( $this, 'resolve_route' ), 10, 4 );
	}

	/**
	 * Returns an array of REST API route definitions.
	 *
	 * @since 1.0.0
	 *
	 * @return array An array of REST API routes.
	 */
	public function get_routes() {
		return array(
			'wp-config'          => array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'permission_callback' => array( $this, 'logged_in_and_can_access_route' ),
					'callback'            => array( WpConfigController::class, 'get' ),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'permission_callback' => array( $this, 'logged_in_and_can_access_route' ),
					'callback'            => array( WpConfigController::class, 'update' ),
				),
			),
			'debug-log/updates'  => array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'logged_in_and_can_access_route' ),
				'callback'            => array( DebugLogController::class, 'get_updates' ),
			),
			'debug-log/sse'      => array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'logged_in_and_can_access_route' ),
				'callback'            => array( DebugLogController::class, 'get_sse' ),
			),
			'debug-log/clear'    => array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'permission_callback' => array( $this, 'logged_in_and_can_access_route' ),
				'callback'            => array( DebugLogController::class, 'clear' ),
			),
			'debug-log/download' => array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'validate_nonce' ),
				'callback'            => array( DebugLogController::class, 'download' ),
			),
		);
	}

	/**
	 * Registers the REST API routes.
	 *
	 * Hook: rest_api_init
	 *
	 * @see https://developer.wordpress.org/reference/hooks/rest_api_init/
	 * @see https://developer.wordpress.org/reference/functions/register_rest_route/
	 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/routes-and-endpoints/
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		foreach ( $this->get_routes() as $route => $args ) {

			// Check if route is a single endpoint or an array of endpoints. A single-endpoint route would have the
			// callback key set directly. If single-endpoint, we add it to an array so we can parse it the same way
			// we parse an array of endpoint.
			if ( ! empty( $args['callback'] ) ) {
				$args = array( $args );
			}

			$that = $this;
			$args = array_map(
				function ( $args ) use ( $that ) {
					return array_merge(
						$args,
						// By default, all routes have their callback set to fallback. This is a safety
						// measure in case the route resolver doesn't work.
						array(
							'callback' => array( $that, 'fallback' ),
						)
					);
				},
				$args
			);

			// If single-endpoint, let's use the one args.
			if ( 1 === count( $args ) ) {
				$args = $args[0];
			}

			register_rest_route(
				$this->namespace,
				$route,
				$args
			);
		}
	}

	/**
	 * Fallback method for invalid REST API routes.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_Error A WP_Error object indicating that the route is invalid.
	 */
	public function fallback() {
		return new \WP_Error( 'rest_invalid_handler', __( 'Invalid route.', 'admin-debug-tools' ), array( 'status' => 500 ) );
	}

	/**
	 * Resolves the REST API route to a controller method.
	 *
	 * This filter replaces the default WordPress REST API dispatcher with our own.
	 *
	 * Hook: rest_dispatch_request
	 *
	 * @see https://developer.wordpress.org/reference/hooks/rest_dispatch_request/
	 *
	 * @since 1.0.0
	 *
	 * @param mixed            $result  The result of the REST API request.
	 * @param \WP_REST_Request $request The REST API request.
	 * @param string           $route   The REST API route.
	 *
	 * @return mixed The result of the REST API request.
	 */
	public function resolve_route( $result, $request, $route ) {
		$routes = $this->get_routes();

		// If the route doesn't match our namespace, return null so the WordPress's native dispatcher can take over.
		if ( strpos( $route, '/' . $this->namespace . '/' ) !== 0 ) {
			return $result;
		}

		$route = str_replace( '/' . $this->namespace . '/', '', $route );

		if ( ! isset( $routes[ $route ] ) ) {
			return new \WP_Error( 'rest_invalid_route', __( 'Invalid route.', 'admin-debug-tools' ), array( 'status' => 404 ) );
		}

		$route = $routes[ $route ];

		// Check if route is a single endpoint or an array of endpoints. A single-endpoint route would have the
		// callback key set directly. If multi-endpoint, we need to look for the right endpoint args using the
		// request method.
		if ( ! isset( $route['callback'] ) && ! empty( $route ) ) {
			$route = array_filter(
				$route,
				function ( $args ) use ( $request ) {
					return false !== strpos( $args['methods'], $request->get_method() );
				}
			);

			$route = array_shift( $route );
		}

		if ( ! isset( $route['callback'] ) ) {
			return new \WP_Error( 'rest_invalid_handler', __( 'The handler for the route is invalid', 'admin-debug-tools' ), array( 'status' => 500 ) );
		}

		$callback = $route['callback'];

		if ( is_array( $callback ) ) {
			try {
				$controller = Plugin::get( $callback[0] );
				$method     = $callback[1];

				return $controller->$method( $request );
			} catch ( RestApiException $e ) {
				return $e->to_response();
			} catch ( UserFrienldlyException $e ) {
				return RestApiException::from_exception( $e )->to_response();
			} catch ( \Exception $e ) {
				return RestApiException::unexpected_error( $e )->to_response();
			}
		}

		return $callback( $request );
	}

	/**
	 * Determines if the logged-in user can access the REST API route.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the user can access the route, false otherwise.
	 */
	public function logged_in_and_can_access_route() {
		return is_user_logged_in() && Utils::user_can_access( 'api' );
	}

	/**
	 * Validate the nonce for a given request.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request The current request object.
	 *
	 * @return bool True if the nonce is valid, false otherwise.
	 */
	public function validate_nonce( $request ) {
		$nonce        = sanitize_text_field( $request->get_param( '_adtnonce' ) );
		$nonce_action = $request->get_route();

		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			return false;
		}

		return true;
	}
}
