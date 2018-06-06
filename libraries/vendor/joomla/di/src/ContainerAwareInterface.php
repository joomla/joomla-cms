<?php
/**
 * Part of the Joomla Framework DI Package
 *
 * @copyright  Copyright (C) 2013 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\DI;

/**
 * Defines the interface for a Container Aware class.
 *
 * @since  1.0
 */
interface ContainerAwareInterface
{
	/**
	 * Get the DI container.
	 *
	 * @return  Container
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException May be thrown if the container has not been set.
	 * @deprecated  2.0  The getter will no longer be part of the interface.
	 */
	public function getContainer();

	/**
	 * Set the DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function setContainer(Container $container);
}
