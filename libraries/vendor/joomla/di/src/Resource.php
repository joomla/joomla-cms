<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Defines the representation of a resource.
 *
 * @since  __DEPLOY_VERSION__
 */
class Resource
{
	/**
	 * Defines the resource as non-shared
	 *
	 * @const  integer
	 * @since  __DEPLOY_VERSION__
	 */
	const NO_SHARE = 0;

	/**
	 * Defines the resource as shared
	 *
	 * @const  integer
	 * @since  __DEPLOY_VERSION__
	 */
	const SHARE = 1;

	/**
	 * Defines the resource as non-protected
	 *
	 * @const  integer
	 * @since  __DEPLOY_VERSION__
	 */
	const NO_PROTECT = 0;

	/**
	 * Defines the resource as protected
	 *
	 * @const  integer
	 * @since  __DEPLOY_VERSION__
	 */
	const PROTECT = 2;

	/**
	 * The container the resource is assigned to
	 *
	 * @var    Container
	 * @since  __DEPLOY_VERSION__
	 */
	private $container;

	/**
	 * The object instance for a shared object
	 *
	 * @var    mixed
	 * @since  __DEPLOY_VERSION__
	 */
	private $instance = null;

	/**
	 * The factory object
	 *
	 * @var    callable
	 * @since  __DEPLOY_VERSION__
	 */
	private $factory = null;

	/**
	 * Flag if the resource is shared
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $shared = false;

	/**
	 * Flag if the resource is protected
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	private $protected = false;

	/**
	 * Create a resource representation
	 *
	 * @param   Container  $container  The container
	 * @param   mixed      $value      The resource or its factory closure
	 * @param   integer    $mode       Resource mode, defaults to Resource::NO_SHARE | Resource::NO_PROTECT
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(Container $container, $value, int $mode = 0)
	{
		$this->container = $container;
		$this->shared    = ($mode & self::SHARE) === self::SHARE;
		$this->protected = ($mode & self::PROTECT) === self::PROTECT;

		if (is_callable($value))
		{
			$this->factory = $value;
		}
		else
		{
			if ($this->shared)
			{
				$this->instance = $value;
			}

			if (is_object($value))
			{
				$this->factory = function () use ($value)
				{
					return clone $value;
				};
			}
			else
			{
				$this->factory = function () use ($value)
				{
					return $value;
				};
			}
		}
	}

	/**
	 * Check whether the resource is shared
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isShared(): bool
	{
		return $this->shared;
	}

	/**
	 * Check whether the resource is protected
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isProtected(): bool
	{
		return $this->protected;
	}

	/**
	 * Get an instance of the resource
	 *
	 * If a factory was provided, the resource is created and - if it is a shared resource - cached internally.
	 * If the resource was provided directly, that resource is returned.
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getInstance()
	{
		$callable = $this->factory;

		if ($this->isShared())
		{
			if ($this->instance === null)
			{
				$this->instance = $callable($this->container);
			}

			return $this->instance;
		}

		return $callable($this->container);
	}

	/**
	 * Get the factory
	 *
	 * @return  callable
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getFactory()
	{
		return $this->factory;
	}

	/**
	 * Reset the resource
	 *
	 * The instance cache is cleared, so that the next call to get() returns a new instance.
	 * This has an effect on shared, non-protected resources only.
	 *
	 * @return  boolean  True if the resource was reset, false otherwise
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function reset(): bool
	{
		if ($this->isShared() && !$this->isProtected())
		{
			$this->instance = null;

			return true;
		}

		return false;
	}
}
