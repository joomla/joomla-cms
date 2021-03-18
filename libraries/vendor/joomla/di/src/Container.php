<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

use Joomla\DI\Exception\DependencyResolutionException;
use Joomla\DI\Exception\KeyNotFoundException;
use Joomla\DI\Exception\ProtectedKeyException;
use Psr\Container\ContainerInterface;

/**
 * The Container class.
 *
 * @since  1.0
 */
class Container implements ContainerInterface
{
	/**
	 * Holds the key aliases.
	 *
	 * Format:
	 * 'alias' => 'key'
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $aliases = [];

	/**
	 * Holds the resources.
	 *
	 * @var    ContainerResource[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $resources = [];

	/**
	 * Parent for hierarchical containers.
	 *
	 * In fact, this can be any PSR-11 compatible container, which gets decorated by this
	 *
	 * @var    Container|ContainerInterface|null
	 * @since  1.0
	 */
	protected $parent;

	/**
	 * Holds the service tag mapping.
	 *
	 * @var    array
	 * @since  1.5.0
	 */
	protected $tags = [];

	/**
	 * Constructor for the DI Container
	 *
	 * @param   ContainerInterface|null  $parent  Parent for hierarchical containers.
	 *
	 * @since   1.0
	 */
	public function __construct(?ContainerInterface $parent = null)
	{
		$this->parent = $parent;
	}

	/**
	 * Retrieve a resource
	 *
	 * @param   string  $resourceName  Name of the resource to get.
	 *
	 * @return  mixed  The requested resource
	 *
	 * @since   1.0
	 * @throws  KeyNotFoundException
	 */
	public function get($resourceName)
	{
		$key = $this->resolveAlias($resourceName);

		if (!isset($this->resources[$key]))
		{
			if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
			{
				return $this->parent->get($key);
			}

			throw new KeyNotFoundException(sprintf("Resource '%s' has not been registered with the container.", $resourceName));
		}

		return $this->resources[$key]->getInstance();
	}

	/**
	 * Check if specified resource exists.
	 *
	 * @param   string  $resourceName  Name of the resource to check.
	 *
	 * @return  boolean  true if key is defined, false otherwise
	 *
	 * @since   1.5.0
	 */
	public function has($resourceName)
	{
		$key = $this->resolveAlias($resourceName);

		if (!isset($this->resources[$key]))
		{
			if ($this->parent instanceof ContainerInterface)
			{
				return $this->parent->has($key);
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to check if specified dataStore key exists.
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  True for success
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use ContainerInterface::has() instead
	 */
	public function exists($key)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::has() instead.',
				__METHOD__,
				ContainerInterface::class
			),
			E_USER_DEPRECATED
		);

		return $this->has($key);
	}

	/**
	 * Create an alias for a given key for easy access.
	 *
	 * @param   string  $alias  The alias name
	 * @param   string  $key    The key to alias
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function alias($alias, $key)
	{
		$this->aliases[$alias] = $key;

		return $this;
	}

	/**
	 * Resolve a resource name.
	 *
	 * If the resource name is an alias, the corresponding key is returned.
	 * If the resource name is not an alias, the resource name is returned unchanged.
	 *
	 * @param   string  $resourceName  The key to search for.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function resolveAlias($resourceName)
	{
		return $this->aliases[$resourceName] ?? $resourceName;
	}

	/**
	 * Check whether a resource is shared
	 *
	 * @param   string  $resourceName  Name of the resource to check.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isShared(string $resourceName): bool
	{
		return $this->hasFlag($resourceName, 'isShared', true);
	}

	/**
	 * Check whether a resource is protected
	 *
	 * @param   string  $resourceName  Name of the resource to check.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isProtected(string $resourceName): bool
	{
		return $this->hasFlag($resourceName, 'isProtected', true);
	}

	/**
	 * Check whether a flag (i.e., one of 'shared' or 'protected') is set
	 *
	 * @param   string   $resourceName  Name of the resource to check.
	 * @param   string   $method        Method to delegate to
	 * @param   boolean  $default       Default return value
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  KeyNotFoundException
	 */
	private function hasFlag(string $resourceName, string $method, bool $default = true): bool
	{
		$key = $this->resolveAlias($resourceName);

		if (isset($this->resources[$key]))
		{
			return \call_user_func([$this->resources[$key], $method]);
		}

		if ($this->parent instanceof self)
		{
			return \call_user_func([$this->parent, $method], $key);
		}

		if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
		{
			// We don't know if the parent supports the 'shared' or 'protected' concept, so we assume the default
			return $default;
		}

		throw new KeyNotFoundException(sprintf("Resource '%s' has not been registered with the container.", $resourceName));
	}

	/**
	 * Assign a tag to services.
	 *
	 * @param   string  $tag   The tag name
	 * @param   array   $keys  The service keys to tag
	 *
	 * @return  $this
	 *
	 * @since   1.5.0
	 */
	public function tag($tag, array $keys)
	{
		foreach ($keys as $key)
		{
			$resolvedKey = $this->resolveAlias($key);

			if (!isset($this->tags[$tag]))
			{
				$this->tags[$tag] = [];
			}

			$this->tags[$tag][] = $resolvedKey;
		}

		// Prune duplicates
		$this->tags[$tag] = array_unique($this->tags[$tag]);

		return $this;
	}

	/**
	 * Fetch all services registered to the given tag.
	 *
	 * @param   string  $tag  The tag name
	 *
	 * @return  array  The resolved services for the given tag
	 *
	 * @since   1.5.0
	 */
	public function getTagged($tag)
	{
		$services = [];

		if (isset($this->tags[$tag]))
		{
			foreach ($this->tags[$tag] as $service)
			{
				$services[] = $this->get($service);
			}
		}

		return $services;
	}

	/**
	 * Build an object of the requested class
	 *
	 * Creates an instance of the class specified by $resourceName with all dependencies injected.
	 * If the dependencies cannot be completely resolved, a DependencyResolutionException is thrown.
	 *
	 * @param   string   $resourceName  The class name to build.
	 * @param   boolean  $shared        True to create a shared resource.
	 *
	 * @return  object|false  Instance of class specified by $resourceName with all dependencies injected.
	 *                        Returns an object if the class exists and false otherwise
	 *
	 * @since   1.0
	 * @throws  DependencyResolutionException if the object could not be built (due to missing information)
	 */
	public function buildObject($resourceName, $shared = false)
	{
		static $buildStack = [];

		$key = $this->resolveAlias($resourceName);

		if (\in_array($key, $buildStack, true))
		{
			$buildStack = [];

			throw new DependencyResolutionException(sprintf('Cannot resolve circular dependency for "%s"', $key));
		}

		$buildStack[] = $key;

		if ($this->has($key))
		{
			$resource = $this->get($key);
			array_pop($buildStack);

			return $resource;
		}

		try
		{
			$reflection = new \ReflectionClass($key);
		}
		catch (\ReflectionException $e)
		{
			array_pop($buildStack);

			return false;
		}

		if (!$reflection->isInstantiable())
		{
			$buildStack = [];

			if ($reflection->isInterface())
			{
				throw new DependencyResolutionException(
					sprintf('There is no service for "%s" defined, cannot autowire a class service for an interface.', $key)
				);
			}

			if ($reflection->isAbstract())
			{
				throw new DependencyResolutionException(
					sprintf('There is no service for "%s" defined, cannot autowire an abstract class.', $key)
				);
			}

			throw new DependencyResolutionException(sprintf('"%s" cannot be instantiated.', $key));
		}

		$constructor = $reflection->getConstructor();

		// If there are no parameters, just return a new object.
		if ($constructor === null)
		{
			// There is no constructor, just return a new object.
			$callback = function () use ($key)
			{
				return new $key;
			};
		}
		else
		{
			$newInstanceArgs = $this->getMethodArgs($constructor);

			$callback = function () use ($reflection, $newInstanceArgs)
			{
				return $reflection->newInstanceArgs($newInstanceArgs);
			};
		}

		$this->set($key, $callback, $shared);

		$resource = $this->get($key);
		array_pop($buildStack);

		return $resource;
	}

	/**
	 * Convenience method for building a shared object.
	 *
	 * @param   string  $resourceName  The class name to build.
	 *
	 * @return  object|false  Instance of class specified by $resourceName with all dependencies injected.
	 *                        Returns an object if the class exists and false otherwise
	 *
	 * @since   1.0
	 */
	public function buildSharedObject($resourceName)
	{
		return $this->buildObject($resourceName, true);
	}

	/**
	 * Create a child Container with a new property scope that has the ability to access the parent scope when resolving.
	 *
	 * @return  Container  A new container with the current as a parent
	 *
	 * @since   1.0
	 */
	public function createChild()
	{
		return new static($this);
	}

	/**
	 * Extend a defined service Closure by wrapping the existing one with a new callable function.
	 *
	 * This works very similar to a decorator pattern.  Note that this only works on service Closures
	 * that have been defined in the current container, not parent containers.
	 *
	 * @param   string    $resourceName  The unique identifier for the Closure or property.
	 * @param   callable  $callable      A callable to wrap the original service Closure.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  KeyNotFoundException
	 */
	public function extend($resourceName, callable $callable)
	{
		$key      = $this->resolveAlias($resourceName);
		$resource = $this->getResource($key, true);

		$closure = function ($c) use ($callable, $resource)
		{
			return $callable($resource->getInstance(), $c);
		};

		$this->set($key, $closure, $resource->isShared());
	}

	/**
	 * Build an array of method arguments.
	 *
	 * @param   \ReflectionMethod  $method  Method for which to build the argument array.
	 *
	 * @return  array  Array of arguments to pass to the method.
	 *
	 * @since   1.0
	 * @throws  DependencyResolutionException
	 */
	private function getMethodArgs(\ReflectionMethod $method): array
	{
		$methodArgs = [];

		foreach ($method->getParameters() as $param)
		{
			// Check for a typehinted dependency
			if ($param->hasType())
			{
				try
				{
					$dependency = $param->getClass();
				}
				catch (\ReflectionException $exception)
				{
					// If this is a nullable parameter, then don't error out
					if ($param->allowsNull())
					{
						$methodArgs[] = null;

						continue;
					}

					throw new DependencyResolutionException(
						sprintf(
							'Could not resolve the parameter "$%s" of "%s::%s()": The "%s" class does not exist.',
							$param->name,
							$method->class,
							$method->name,
							$param->getType()->getName()
						),
						0,
						$exception
					);
				}

				// Check for a class, if it doesn't have one then it is a scalar type, which we cannot handle if a mandatory argument
				if ($dependency === null)
				{
					// If the param is optional, then fall through to the optional param handling later in this method
					if (!$param->isOptional())
					{
						$message = 'Could not resolve the parameter "$%s" of "%s::%s()":';
						$message .= ' Scalar parameters cannot be autowired and the parameter does not have a default value.';

						throw new DependencyResolutionException(
							sprintf(
								$message,
								$param->name,
								$method->class,
								$method->name
							)
						);
					}
				}
				else
				{
					$dependencyClassName = $dependency->getName();

					// If the dependency class name is registered with this container or a parent, use it.
					if ($this->getResource($dependencyClassName) !== null)
					{
						$depObject = $this->get($dependencyClassName);
					}
					else
					{
						try
						{
							$depObject = $this->buildObject($dependencyClassName);
						}
						catch (DependencyResolutionException $exception)
						{
							// If this is a nullable parameter, then don't error out
							if ($param->allowsNull())
							{
								$methodArgs[] = null;

								continue;
							}

							$message = 'Could not resolve the parameter "$%s" of "%s::%s()":';
							$message .= ' No service for "%s" exists and the dependency could not be autowired.';

							throw new DependencyResolutionException(
								sprintf(
									$message,
									$param->name,
									$method->class,
									$method->name,
									$dependencyClassName
								),
								0,
								$exception
							);
						}
					}

					if ($depObject instanceof $dependencyClassName)
					{
						$methodArgs[] = $depObject;

						continue;
					}
				}
			}

			// If there is a default parameter and it can be read, use it.
			if ($param->isOptional() && $param->isDefaultValueAvailable())
			{
				try
				{
					$methodArgs[] = $param->getDefaultValue();

					continue;
				}
				catch (\ReflectionException $exception)
				{
					throw new DependencyResolutionException(
						sprintf(
							'Could not resolve the parameter "$%s" of "%s::%s()": Unable to read the default parameter value.',
							$param->name,
							$method->class,
							$method->name
						),
						0,
						$exception
					);
				}
			}

			// If an untyped variadic argument, skip it
			if (!$param->hasType() && $param->isVariadic())
			{
				continue;
			}

			// At this point the argument cannot be resolved, most likely cause is an untyped required argument
			throw new DependencyResolutionException(
				sprintf(
					'Could not resolve the parameter "$%s" of "%s::%s()": The argument is untyped and has no default value.',
					$param->name,
					$method->class,
					$method->name
				)
			);
		}

		return $methodArgs;
	}

	/**
	 * Set a resource to the container. If the value is null the resource is removed.
	 *
	 * @param   string   $key        Name of resources key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared     True to create and store a shared instance.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  ProtectedKeyException  Thrown if the provided key is already set and is protected.
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		$key = $this->resolveAlias($key);

		$hasKey = $this->has($key);

		if ($hasKey && $this->isProtected($key))
		{
			throw new ProtectedKeyException(sprintf("Key %s is protected and can't be overwritten.", $key));
		}

		if ($value === null && $hasKey)
		{
			unset($this->resources[$key]);

			return $this;
		}

		$mode = $shared ? ContainerResource::SHARE : ContainerResource::NO_SHARE;
		$mode |= $protected ? ContainerResource::PROTECT : ContainerResource::NO_PROTECT;

		$this->resources[$key] = new ContainerResource($this, $value, $mode);

		return $this;
	}

	/**
	 * Shortcut method for creating protected keys.
	 *
	 * @param   string   $key     Name of dataStore key to set.
	 * @param   mixed    $value   Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared  True to create and store a shared instance.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function protect($key, $value, $shared = false)
	{
		return $this->set($key, $value, $shared, true);
	}

	/**
	 * Shortcut method for creating shared keys.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function share($key, $value, $protected = false)
	{
		return $this->set($key, $value, true, $protected);
	}

	/**
	 * Get the raw data assigned to a key.
	 *
	 * @param   string   $key   The key for which to get the stored item.
	 * @param   boolean  $bail  Throw an exception, if the key is not found
	 *
	 * @return  ContainerResource|null  The resource if present, or null if instructed to not bail
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  KeyNotFoundException
	 */
	public function getResource(string $key, bool $bail = false): ?ContainerResource
	{
		if (isset($this->resources[$key]))
		{
			return $this->resources[$key];
		}

		if ($this->parent instanceof self)
		{
			return $this->parent->getResource($key);
		}

		if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
		{
			return new ContainerResource($this, $this->parent->get($key), ContainerResource::SHARE | ContainerResource::PROTECT);
		}

		if ($bail)
		{
			throw new KeyNotFoundException(sprintf('Key %s has not been registered with the container.', $key));
		}

		return null;
	}

	/**
	 * Method to force the container to return a new instance of the results of the callback for requested $key.
	 *
	 * @param   string  $key  Name of the resources key to get.
	 *
	 * @return  mixed   Results of running the callback for the specified key.
	 *
	 * @since   1.0
	 */
	public function getNewInstance($key)
	{
		$key = $this->resolveAlias($key);

		$this->getResource($key, true)->reset();

		return $this->get($key);
	}

	/**
	 * Register a service provider to the container.
	 *
	 * @param   ServiceProviderInterface  $provider  The service provider to register.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function registerServiceProvider(ServiceProviderInterface $provider)
	{
		$provider->register($this);

		return $this;
	}

	/**
	 * Retrieve the keys for services assigned to this container.
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	public function getKeys()
	{
		return array_unique(array_merge(array_keys($this->aliases), array_keys($this->resources)));
	}
}
