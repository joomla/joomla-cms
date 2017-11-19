<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 * @var    \Joomla\DI\Resource[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $resources = [];

	/**
	 * Parent for hierarchical containers.
	 *
	 * In fact, this can be any PSR-11 compatible container, which gets decorated by this
	 *
	 * @var    ContainerInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $parent;

	/**
	 * Constructor for the DI Container
	 *
	 * @param   ContainerInterface  $parent  Parent for hierarchical containers.
	 *
	 * @since   1.0
	 */
	public function __construct(ContainerInterface $parent = null)
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
	 * @since   1.0
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
		if (isset($this->aliases[$resourceName]))
		{
			return $this->aliases[$resourceName];
		}

		return $resourceName;
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
			return call_user_func([$this->resources[$key], $method]);
		}

		if ($this->parent instanceof Container)
		{
			return call_user_func([$this->parent, $method], $key);
		}

		if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
		{
			// We don't know, if parent supports the 'shared' or 'protected' concept, so we assume the default
			return $default;
		}

		throw new KeyNotFoundException(sprintf("Resource '%s' has not been registered with the container.", $resourceName));
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

		if (in_array($key, $buildStack, true))
		{
			$buildStack = [];

			throw new DependencyResolutionException("Can't resolve circular dependency");
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

			throw new DependencyResolutionException("$key can not be instantiated.");
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
	 * that have been defined in the current Provider, not parent providers.
	 *
	 * @param   string    $resourceName  The unique identifier for the Closure or property.
	 * @param   callable  $callable      A callable to wrap the original service Closure.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function extend($resourceName, callable $callable)
	{
		$key = $this->resolveAlias($resourceName);
		$resource = $this->getResource($key, true);

		$closure = function ($c) use ($callable, $resource)
		{
			return $callable($resource->getInstance(), $c);
		};

		$this->set($key, $closure, $resource->isShared());
	}

	/**
	 * Build an array of constructor parameters.
	 *
	 * @param   \ReflectionMethod  $method  Method for which to build the argument array.
	 *
	 * @return  array  Array of arguments to pass to the method.
	 *
	 * @since   1.0
	 * @throws  DependencyResolutionException
	 */
	private function getMethodArgs(\ReflectionMethod $method)
	{
		$methodArgs = [];

		foreach ($method->getParameters() as $param)
		{
			$dependency        = $param->getClass();
			$dependencyVarName = $param->getName();

			// If we have a dependency, that means it has been type-hinted.
			if ($dependency !== null)
			{
				$dependencyClassName = $dependency->getName();

				// If the dependency class name is registered with this container or a parent, use it.
				if ($this->getResource($dependencyClassName) !== null)
				{
					$depObject = $this->get($dependencyClassName);
				}
				else
				{
					$depObject = $this->buildObject($dependencyClassName);
				}

				if ($depObject instanceof $dependencyClassName)
				{
					$methodArgs[] = $depObject;
					continue;
				}
			}

			// Finally, if there is a default parameter, use it.
			if ($param->isOptional())
			{
				$methodArgs[] = $param->getDefaultValue();
				continue;
			}

			// Couldn't resolve dependency, and no default was provided.
			throw new DependencyResolutionException(sprintf('Could not resolve dependency: %s', $dependencyVarName));
		}

		return $methodArgs;
	}

	/**
	 * Set a resource. If the value is null unsets the resource.
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

		$mode = $shared ? Resource::SHARE : Resource::NO_SHARE;
		$mode |= $protected ? Resource::PROTECT : Resource::NO_PROTECT;

		$this->resources[$key] = new Resource($this, $value, $mode);

		return $this;
	}

	/**
	 * Convenience method for creating protected keys.
	 *
	 * @param   string    $key       Name of resources key to set.
	 * @param   callable  $callback  Callable function to run when requesting the specified $key.
	 * @param   boolean   $shared    True to create and store a shared instance.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function protect($key, $callback, $shared = false)
	{
		return $this->set($key, $callback, $shared, true);
	}

	/**
	 * Convenience method for creating shared keys.
	 *
	 * @param   string    $key        Name of resources key to set.
	 * @param   callable  $callback   Callable function to run when requesting the specified $key.
	 * @param   boolean   $protected  True to create and store a shared instance.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function share($key, $callback, $protected = false)
	{
		return $this->set($key, $callback, true, $protected);
	}

	/**
	 * Get the raw data assigned to a key.
	 *
	 * @param   string   $key   The key for which to get the stored item.
	 * @param   boolean  $bail  Throw an exception, if the key is not found
	 *
	 * @return  \Joomla\DI\Resource
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  KeyNotFoundException
	 */
	public function getResource(string $key, bool $bail = false)
	{
		if (isset($this->resources[$key]))
		{
			return $this->resources[$key];
		}

		if ($this->parent instanceof Container)
		{
			return $this->parent->getResource($key);
		}

		if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
		{
			return new Resource($this, $this->parent->get($key), Resource::SHARE | Resource::PROTECT);
		}

		if ($bail)
		{
			throw new KeyNotFoundException(sprintf('Key %s has not been registered with the container.', $key));
		}

		return null;
	}

	/**
	 * Method to force the container to return a new instance
	 * of the results of the callback for requested $key.
	 *
	 * @param   string  $key  Name of the resources key to get.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
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
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function registerServiceProvider(ServiceProviderInterface $provider)
	{
		$provider->register($this);

		return $this;
	}
}
