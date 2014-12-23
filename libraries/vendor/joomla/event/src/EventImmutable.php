<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use BadMethodCallException;

/**
 * Implementation of an immutable Event.
 * An immutable event cannot be modified after instanciation :
 *
 * - its propagation cannot be stopped
 * - its arguments cannot be modified
 *
 * You may want to use this event when you want to ensure that
 * the listeners won't manipulate it.
 *
 * @since  1.0
 */
final class EventImmutable extends AbstractEvent
{
	/**
	 * A flag to see if the constructor has been
	 * already called.
	 *
	 * @var  boolean
	 */
	private $constructed = false;

	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   1.0
	 */
	public function __construct($name, array $arguments = array())
	{
		if ($this->constructed)
		{
			throw new BadMethodCallException(
				sprintf('Cannot reconstruct the EventImmutable %s.', $this->name)
			);
		}

		$this->constructed = true;

		parent::__construct($name, $arguments);
	}

	/**
	 * Set the value of an event argument.
	 *
	 * @param   string  $name   The argument name.
	 * @param   mixed   $value  The argument value.
	 *
	 * @return  void
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   1.0
	 */
	public function offsetSet($name, $value)
	{
		throw new BadMethodCallException(
			sprintf(
				'Cannot set the argument %s of the immutable event %s.',
				$name,
				$this->name
			)
		);
	}

	/**
	 * Remove an event argument.
	 *
	 * @param   string  $name  The argument name.
	 *
	 * @return  void
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   1.0
	 */
	public function offsetUnset($name)
	{
		throw new BadMethodCallException(
			sprintf(
				'Cannot remove the argument %s of the immutable event %s.',
				$name,
				$this->name
			)
		);
	}
}
