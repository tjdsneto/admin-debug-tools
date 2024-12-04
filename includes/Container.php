<?php
/**
 * Admin Debug Tools Container.
 *
 * @package AdminDebugTools
 */

namespace AdminDebugTools\Plugin;

use AdminDebugTools\Plugin\Exceptions\Container\ContainerException;
use AdminDebugTools\Plugin\Exceptions\Container\NotFoundException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Debug Tools Container.
 *
 * Altought not explicit yet, this class intends to implement the PSR-11 Container interface
 *
 * @see https://www.php-fig.org/psr/psr-11/
 *
 * @since 1.0.0
 */
class Container {

	/**
	 * Holds the bindings for the container's entries.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $bindings = array();

	/**
	 * Holds the instances for the container's singleton entries.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $singletons = array();

	/**
	 * Adds an entry to the container.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $id The id for the entry.
	 * @param mixed   $resolver The resolver for the entry.
	 * @param boolean $singleton Whether the entry should alwasys return the same instance of the entry.
	 *
	 * @return void
	 */
	public function add( $id, $resolver = null, $singleton = false ) {
		if ( null === $resolver ) {
			$resolver = $id;
		}

		$this->bindings[ $id ] = $resolver;

		if ( $singleton ) {
			$this->singletons[ $id ] = null;
		}
	}

	/**
	 * Convenience method to add an entry to the container as a singleton entry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The id for the entry.
	 * @param mixed  $resolver The resolver for the entry.
	 *
	 * @return void
	 */
	public function singleton( $id, $resolver = null ) {
		$this->add( $id, $resolver, true );
	}

	/**
	 * Finds an entry of the container by its id and returns it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The id of the entry to look for.
	 *
	 * @throws NotFoundException  No entry was found for **this** id.
	 * @throws ContainerException Error while retrieving the entry.
	 *
	 * @return mixed Entry.
	 */
	public function get( $id ) {
		if ( isset( $this->singletons[ $id ] ) ) {
			if ( is_null( $this->singletons[ $id ] ) ) {
				$this->singletons[ $id ] = $this->resolve( $id );
			}

			return $this->singletons[ $id ];
		}

		return $this->resolve( $id );
	}

	/**
	 * Resolves a binding from the container.
	 *
	 * If the binding is a factory function, we calls the function, otherwise it has auto-wiring set up, which means
	 * it will automatically try to instantiate a class.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The id of the entry to look for.
	 *
	 * @throws ContainerException Error while retrieving the entry.
	 *
	 * @return mixed Entry.
	 */
	protected function resolve( $id ) {
		if ( $this->has( $id ) ) {
			$binding = $this->bindings[ $id ];

			// If it's a factory function, we call it passing the instance of the container as a parameter so the factory
			// can use the container to retrieve other entries.
			if ( is_callable( $binding ) ) {
				return $binding( $this );
			}
		}

		// If it didn't find the binding or the binding was not a callable, let's see if we can auto instantiate it.
		$binding = $binding ?? $id;

		// 1. Inspect the class that we are trying to get from the container
		$reflection_class = new \ReflectionClass( $binding );

		if ( ! $reflection_class->isInstantiable() ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
			throw new ContainerException( 'Class "' . $id . '" is not instantiable.' );
		}

		// 2. Inspect the constructor of the class
		$constructor = $reflection_class->getConstructor();

		if ( ! $constructor ) {
			return new $id();
		}

		// 3. Inspect the constructor parameters (dependencies)
		$parameters = $constructor->getParameters();

		if ( ! $parameters ) {
			return new $id();
		}

		// 4. If the constructor parameter is a class then try to resolve that class using the container
		$dependencies = array_map(
			function ( \ReflectionParameter $param ) use ( $id ) {
				$name = $param->getName();
				$type = $param->getType();

				if ( ! $type ) {
					throw new ContainerException(
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
						'Failed to resolve class "' . $id . '" because param "' . $name . '" is missing a type hint.'
					);
				}

				if ( $type instanceof \ReflectionUnionType ) {
					throw new ContainerException(
						// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
						'Failed to resolve class "' . $id . '" because of union type for param "' . $name . '".'
					);
				}

				if ( $type instanceof \ReflectionNamedType && ! $type->isBuiltin() ) {
					return $this->get( $type->getName() );
				}

				throw new ContainerException(
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- The exceptions are not meant to be outputted as HTML.
					'Failed to resolve class "' . $id . '" because invalid param "' . $name . '".'
				);
			},
			$parameters
		);

		return $reflection_class->newInstanceArgs( $dependencies );
	}

	/**
	 * Returns true if the container can return an entry for the given id.
	 * Returns false otherwise.
	 *
	 * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
	 * It does however mean that `get($id)` will not throw a `NotFoundException`.
	 *
	 * @since 1.0.0
	 *
	 * @param string $id The id of the entry to look for.
	 *
	 * @return bool Whether it found the entry or not.
	 */
	public function has( $id ) {
		return isset( $this->bindings[ $id ] );
	}
}
