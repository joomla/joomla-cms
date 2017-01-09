<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use InvalidArgumentException;

/**
 * Default Event class.
 *
 * @since  1.0
 */
class Event extends AbstractEvent
{
	/**
	 * Add an event argument, only if it is not existing.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function addArgument($name, $value)
	{
		if (!isset($this->arguments[$name]))
		{
			$this->arguments[$name] = $value;
		}

		return $this;
	}

	/**
	 * Add argument to event.
	 *
	 * @param   string  $name   Argument name.
	 * @param   mixed   $value  Value.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setArgument($name, $value)
	{
		$this->arguments[$name] = $value;

		return $this;
	}

	/**
	 * Remove an event argument.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  mixed  The old argument value or null if it is not existing.
	 *
	 * @since   1.0
	 */
	public function removeArgument($name)
	{
		$return = null;

		if (isset($this->arguments[$name]))
		{
			$return = $this->arguments[$name];
			unset($this->arguments[$name]);
		}

		return $return;
	}

	/**
	 * Clear all event arguments.
	 *
	 * @return  array  The old arguments.
	 *
	 * @since   1.0
	 */
	public function clearArguments()
	{
		$arguments       = $this->arguments;
		$this->arguments = [];

		return $arguments;
	}

	/**
	 * Stop the event propagation.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use stopPropogation instead
	 */
	public function stop()
	{
		$this->stopPropagation();
	}

	/**
	 * Set the value of an event argument.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  InvalidArgumentException  If the argument name is null.
	 */
	public function offsetSet($name, $value)
	{
		if (is_null($name))
		{
			throw new InvalidArgumentException('The argument name cannot be null.');
		}

		$this->setArgument($name, $value);
	}

	/**
	 * Remove an event argument.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function offsetUnset($name)
	{
		$this->removeArgument($name);
	}
}
