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
	 * @var    array
	 * @since  1.0
	 */
	protected $aliases = array();

	/**
	 * Holds the shared instances.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $instances = array();

	/**
	 * Holds the keys, their callbacks, and whether or not
	 * the item is meant to be a shared resource.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $dataStore = array();

	/**
	 * Parent for hierarchical containers.
	 *
	 * @var    Container|ContainerInterface
	 * @since  1.0
	 */
	protected $parent;

	/**
	 * Holds the service tag mapping.
	 *
	 * @var    array
	 * @since  1.5.0
	 */
	protected $tags = array();

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
	 * Create an alias for a given key for easy access.
	 *
	 * @param   string  $alias  The alias name
	 * @param   string  $key    The key to alias
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function alias($alias, $key)
	{
		$this->aliases[$alias] = $key;

		return $this;
	}

	/**
	 * Search the aliases property for a matching alias key.
	 *
	 * @param   string  $key  The key to search for.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	protected function resolveAlias($key)
	{
		if (isset($this->aliases[$key]))
		{
			return $this->aliases[$key];
		}

		if ($this->parent instanceof Container)
		{
			return $this->parent->resolveAlias($key);
		}

		return $key;
	}

	/**
	 * Assign a tag to services.
	 *
	 * @param   string  $tag   The tag name
	 * @param   array   $keys  The service keys to tag
	 *
	 * @return  Container  This object for chaining.
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
				$this->tags[$tag] = array();
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
		$services = array();

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
	 * Build an object of class $key;
	 *
	 * @param   string   $key     The class name to build.
	 * @param   boolean  $shared  True to create a shared resource.
	 *
	 * @return  object|false  Instance of class specified by $key with all dependencies injected.
	 *                        Returns an object if the class exists and false otherwise
	 *
	 * @since   1.0
	 */
	public function buildObject($key, $shared = false)
	{
		static $buildStack = array();

		$resolvedKey = $this->resolveAlias($key);

		if (in_array($resolvedKey, $buildStack, true))
		{
			$buildStack = array();

			throw new DependencyResolutionException("Can't resolve circular dependency");
		}

		$buildStack[] = $resolvedKey;

		if ($this->has($resolvedKey))
		{
			$resource = $this->get($resolvedKey);
			array_pop($buildStack);

			return $resource;
		}

		try
		{
			$reflection = new \ReflectionClass($resolvedKey);
		}
		catch (\ReflectionException $e)
		{
			array_pop($buildStack);

			return false;
		}

		if (!$reflection->isInstantiable())
		{
			$buildStack = array();

			throw new DependencyResolutionException("$resolvedKey can not be instantiated.");
		}

		$constructor = $reflection->getConstructor();

		// If there are no parameters, just return a new object.
		if ($constructor === null)
		{
			$callback = function () use ($resolvedKey) {
				return new $resolvedKey;
			};
		}
		else
		{
			$newInstanceArgs = $this->getMethodArgs($constructor);

			// Create a callable for the dataStore
			$callback = function () use ($reflection, $newInstanceArgs) {
				return $reflection->newInstanceArgs($newInstanceArgs);
			};
		}

		$this->set($resolvedKey, $callback, $shared);

		$resource = $this->get($resolvedKey);
		array_pop($buildStack);

		return $resource;
	}

	/**
	 * Convenience method for building a shared object.
	 *
	 * @param   string  $key  The class name to build.
	 *
	 * @return  object|false  Instance of class specified by $key with all dependencies injected.
	 *                        Returns an object if the class exists and false otherwise
	 *
	 * @since   1.0
	 */
	public function buildSharedObject($key)
	{
		return $this->buildObject($key, true);
	}

	/**
	 * Create a child Container with a new property scope that
	 * that has the ability to access the parent scope when resolving.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function createChild()
	{
		return new static($this);
	}

	/**
	 * Extend a defined service Closure by wrapping the existing one with a new Closure.  This
	 * works very similar to a decorator pattern.  Note that this only works on service Closures
	 * that have been defined in the current Provider, not parent providers.
	 *
	 * @param   string    $key       The unique identifier for the Closure or property.
	 * @param   \Closure  $callable  A Closure to wrap the original service Closure.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  KeyNotFoundException
	 */
	public function extend($key, \Closure $callable)
	{
		$key = $this->resolveAlias($key);
		$raw = $this->getRaw($key);

		if ($raw === null)
		{
			throw new KeyNotFoundException(sprintf('The requested key %s does not exist to extend.', $key));
		}

		$closure = function ($c) use ($callable, $raw) {
			return $callable($raw['callback']($c), $c);
		};

		$this->set($key, $closure, $raw['shared']);
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
	protected function getMethodArgs(\ReflectionMethod $method)
	{
		$methodArgs = array();

		foreach ($method->getParameters() as $param)
		{
			$dependency = $param->getClass();
			$dependencyVarName = $param->getName();

			// If we have a dependency, that means it has been type-hinted.
			if ($dependency !== null)
			{
				$dependencyClassName = $dependency->getName();

				// If the dependency class name is registered with this container or a parent, use it.
				if ($this->getRaw($dependencyClassName) !== null)
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
	 * Method to set the key and callback to the dataStore array.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared     True to create and store a shared instance.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 * @throws  ProtectedKeyException  Thrown if the provided key is already set and is protected.
	 */
	public function set($key, $value, $shared = false, $protected = false)
	{
		if (isset($this->dataStore[$key]) && $this->dataStore[$key]['protected'] === true)
		{
			throw new ProtectedKeyException(sprintf("Key %s is protected and can't be overwritten.", $key));
		}

		// If the provided $value is not a closure, make it one now for easy resolution.
		if (!is_callable($value))
		{
			$value = function () use ($value) {
				return $value;
			};
		}

		$this->dataStore[$key] = array(
			'callback' => $value,
			'shared' => $shared,
			'protected' => $protected
		);

		return $this;
	}

	/**
	 * Convenience method for creating protected keys.
	 *
	 * @param   string   $key     Name of dataStore key to set.
	 * @param   mixed    $value   Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $shared  True to create and store a shared instance.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function protect($key, $value, $shared = false)
	{
		return $this->set($key, $value, $shared, true);
	}

	/**
	 * Convenience method for creating shared keys.
	 *
	 * @param   string   $key        Name of dataStore key to set.
	 * @param   mixed    $value      Callable function to run or string to retrive when requesting the specified $key.
	 * @param   boolean  $protected  True to protect this item from being overwritten. Useful for services.
	 *
	 * @return  Container  This object for chaining.
	 *
	 * @since   1.0
	 */
	public function share($key, $value, $protected = false)
	{
		return $this->set($key, $value, true, $protected);
	}

	/**
	 * Method to retrieve the results of running the $callback for the specified $key;
	 *
	 * @param   string   $key       Name of the dataStore key to get.
	 * @param   boolean  $forceNew  True to force creation and return of a new instance.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 *
	 * @since   1.0
	 * @throws  KeyNotFoundException
	 */
	public function get($key, $forceNew = false)
	{
		$key = $this->resolveAlias($key);
		$raw = $this->getRaw($key);

		if ($raw === null)
		{
			throw new KeyNotFoundException(sprintf('Key %s has not been registered with the container.', $key));
		}

		if ($raw['shared'])
		{
			if ($forceNew || !isset($this->instances[$key]))
			{
				$this->instances[$key] = $raw['callback']($this);
			}

			return $this->instances[$key];
		}

		return call_user_func($raw['callback'], $this);
	}

	/**
	 * Method to check if specified dataStore key exists.
	 *
	 * @param   string  $key  Name of the dataStore key to check.
	 *
	 * @return  boolean  True for success
	 *
	 * @since   1.5.0
	 */
	public function has($key)
	{
		$key = $this->resolveAlias($key);

		$exists = (bool) $this->getRaw($key);

		if ($exists === false && $this->parent instanceof ContainerInterface)
		{
			$exists = $this->parent->has($key);
		}

		return $exists;
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
	 * Get the raw data assigned to a key.
	 *
	 * @param   string  $key  The key for which to get the stored item.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	protected function getRaw($key)
	{
		if (isset($this->dataStore[$key]))
		{
			return $this->dataStore[$key];
		}

		$aliasKey = $this->resolveAlias($key);

		if ($aliasKey !== $key && isset($this->dataStore[$aliasKey]))
		{
			return $this->dataStore[$aliasKey];
		}

		if ($this->parent instanceof Container)
		{
			return $this->parent->getRaw($key);
		}

		if ($this->parent instanceof ContainerInterface && $this->parent->has($key))
		{
			$callback = $this->parent->get($key);

			if (!is_callable($callback))
			{
				$callback = function () use ($callback) {
					return $callback;
				};
			}

			return array(
				'callback'  => $callback,
				'shared'    => true,
				'protected' => true,
			);
		}

		return null;
	}

	/**
	 * Method to force the container to return a new instance
	 * of the results of the callback for requested $key.
	 *
	 * @param   string  $key  Name of the dataStore key to get.
	 *
	 * @return  mixed   Results of running the $callback for the specified $key.
	 *
	 * @since   1.0
	 */
	public function getNewInstance($key)
	{
		return $this->get($key, true);
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

	/**
	 * Retrieve the keys for services assigned to this container.
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	public function getKeys()
	{
		return array_unique(array_merge(array_keys($this->aliases), array_keys($this->dataStore)));
	}
}
