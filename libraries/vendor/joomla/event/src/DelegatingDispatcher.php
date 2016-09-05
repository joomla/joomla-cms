<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * A dispatcher delegating its methods to an other dispatcher.
 *
 * @since  1.0
 */
final class DelegatingDispatcher implements DispatcherInterface
{
	/**
	 * The delegated dispatcher.
	 *
	 * @var    DispatcherInterface
	 *
	 * @since  1.0
	 */
	private $dispatcher;

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The delegated dispatcher.
	 *
	 * @since   1.0
	 */
	public function __construct(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 *
	 * @since   1.0
	 */
	public function triggerEvent($event)
	{
		return $this->dispatcher->triggerEvent($event);
	}
}
