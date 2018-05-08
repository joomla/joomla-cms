<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Defines the trait for a Container Aware Class.
 *
 * @since  1.2
 * @note   Traits are available in PHP 5.4+
 */
trait ContainerAwareTrait
{
	/**
	 * DI Container
	 *
	 * @var    Container
	 * @since  1.2
	 */
	private $container;

	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   1.2
	 * @throws  \UnexpectedValueException May be thrown if the container has not been set.
	 * @note    As of 2.0 this method will be protected.
	 */
	public function getContainer()
	{
		if ($this->container)
		{
			return $this->container;
		}

		throw new \UnexpectedValueException('Container not set in ' . __CLASS__);
	}

	/**
	 * Set the DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  mixed  Returns itself to support chaining.
	 *
	 * @since   1.2
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}
}
