<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Container;

defined('_JEXEC') || die;

use FOF30\Pimple\Container;

class ContainerBase extends Container
{
	/**
	 * Magic getter for alternative syntax, e.g. $container->foo instead of $container['foo']
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException if the identifier is not defined
	 */
	function __get($name)
	{
		return $this->offsetGet($name);
	}

	/**
	 * Magic setter for alternative syntax, e.g. $container->foo instead of $container['foo']
	 *
	 * @param   string  $name   The unique identifier for the parameter or object
	 * @param   mixed   $value  The value of the parameter or a closure for a service
	 *
	 * @throws \RuntimeException Prevent override of a frozen service
	 */
	function __set($name, $value)
	{
		// Special backwards compatible handling for the mediaVersion service
		if ($name == 'mediaVersion')
		{
			$this[$name]->setMediaVersion($value);

			return;
		}

		$this->offsetSet($name, $value);
	}
}
