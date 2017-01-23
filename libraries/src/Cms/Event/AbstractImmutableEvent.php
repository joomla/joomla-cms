<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Event;

defined('JPATH_PLATFORM') or die;

use BadMethodCallException;

/**
 * This class implements the immutable base Event object used system-wide to offer orthogonality.
 *
 * @see    Joomla\Cms\Event\AbstractEvent
 * @since  __DEPLOY_VERSION__
 */
class AbstractImmutableEvent extends AbstractEvent
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		if ($this->constructed)
		{
			throw new BadMethodCallException(
				sprintf('Cannot reconstruct the AbstractImmutableEvent %s.', $this->name)
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
	 * @since   __DEPLOY_VERSION__
	 * @throws  BadMethodCallException
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
	 * @since   __DEPLOY_VERSION__
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
