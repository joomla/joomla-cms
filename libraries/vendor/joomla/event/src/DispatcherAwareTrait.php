<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Defines the trait for a Dispatcher Aware Class.
 *
 * @since  1.2.0
 */
trait DispatcherAwareTrait
{
	/**
	 * Event Dispatcher
	 *
	 * @var    DispatcherInterface
	 * @since  1.2.0
	 */
	private $dispatcher;

	/**
	 * Get the event dispatcher.
	 *
	 * @return  DispatcherInterface
	 *
	 * @since   1.2.0
	 * @throws  \UnexpectedValueException May be thrown if the dispatcher has not been set.
	 */
	public function getDispatcher()
	{
		if ($this->dispatcher)
		{
			return $this->dispatcher;
		}

		throw new \UnexpectedValueException('Dispatcher not set in ' . __CLASS__);
	}

	/**
	 * Set the dispatcher to use.
	 *
	 * @param   DispatcherInterface  $dispatcher  The dispatcher to use.
	 *
	 * @return  $this
	 *
	 * @since   1.2.0
	 */
	public function setDispatcher(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;

		return $this;
	}
}
