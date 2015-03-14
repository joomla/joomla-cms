<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

use InvalidArgumentException;
use Closure;

/**
 * Implementation of a DispatcherInterface supporting
 * prioritized listeners.
 *
 * @since  1.0
 */
class Dispatcher implements DispatcherInterface
{
	/**
	 * An array of registered events indexed by
	 * the event names.
	 *
	 * @var    EventInterface[]
	 *
	 * @since  1.0
	 */
	protected $events = array();

	/**
	 * A regular expression that will filter listener method names.
	 *
	 * @var    string
	 * @since  1.0
	 * @deprecated
	 */
	protected $listenerFilter;

	/**
	 * An array of ListenersPriorityQueue indexed
	 * by the event names.
	 *
	 * @var    ListenersPriorityQueue[]
	 *
	 * @since  1.0
	 */
	protected $listeners = array();

	/**
	 * Set an event to the dispatcher.
	 * It will replace any event with the same name.
	 *
	 * @param   EventInterface  $event  The event.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function setEvent(EventInterface $event)
	{
		$this->events[$event->getName()] = $event;

		return $this;
	}

	/**
	 * Sets a regular expression to filter the class methods when adding a listener.
	 *
	 * @param   string  $regex  A regular expression (for example '^on' will only register methods starting with "on").
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since       1.0
	 * @deprecated  Incorporate a method in your listener object such as `getEvents` to feed into the `setListener` method.
	 */
	public function setListenerFilter($regex)
	{
		$this->listenerFilter = $regex;

		return $this;
	}

	/**
	 * Add an event to this dispatcher, only if it is not existing.
	 *
	 * @param   EventInterface  $event  The event.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function addEvent(EventInterface $event)
	{
		if (!isset($this->events[$event->getName()]))
		{
			$this->events[$event->getName()] = $event;
		}

		return $this;
	}

	/**
	 * Tell if the given event has been added to this dispatcher.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  boolean  True if the listener has the given event, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasEvent($event)
	{
		if ($event instanceof EventInterface)
		{
			$event = $event->getName();
		}

		return isset($this->events[$event]);
	}

	/**
	 * Get the event object identified by the given name.
	 *
	 * @param   string  $name     The event name.
	 * @param   mixed   $default  The default value if the event was not registered.
	 *
	 * @return  EventInterface|mixed  The event of the default value.
	 *
	 * @since   1.0
	 */
	public function getEvent($name, $default = null)
	{
		if (isset($this->events[$name]))
		{
			return $this->events[$name];
		}

		return $default;
	}

	/**
	 * Remove an event from this dispatcher.
	 * The registered listeners will remain.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function removeEvent($event)
	{
		if ($event instanceof EventInterface)
		{
			$event = $event->getName();
		}

		if (isset($this->events[$event]))
		{
			unset($this->events[$event]);
		}

		return $this;
	}

	/**
	 * Get the registered events.
	 *
	 * @return  EventInterface[]  The registered event.
	 *
	 * @since   1.0
	 */
	public function getEvents()
	{
		return $this->events;
	}

	/**
	 * Clear all events.
	 *
	 * @return  EventInterface[]  The old events.
	 *
	 * @since   1.0
	 */
	public function clearEvents()
	{
		$events = $this->events;
		$this->events = array();

		return $events;
	}

	/**
	 * Count the number of registered event.
	 *
	 * @return  integer  The numer of registered events.
	 *
	 * @since   1.0
	 */
	public function countEvents()
	{
		return count($this->events);
	}

	/**
	 * Add a listener to this dispatcher, only if not already registered to these events.
	 * If no events are specified, it will be registered to all events matching it's methods name.
	 * In the case of a closure, you must specify at least one event name.
	 *
	 * @param   object|Closure  $listener  The listener
	 * @param   array           $events    An associative array of event names as keys
	 *                                     and the corresponding listener priority as values.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @throws  InvalidArgumentException
	 *
	 * @since   1.0
	 */
	public function addListener($listener, array $events = array())
	{
		if (!is_object($listener))
		{
			throw new InvalidArgumentException('The given listener is not an object.');
		}

		// We deal with a closure.
		if ($listener instanceof Closure)
		{
			if (empty($events))
			{
				throw new InvalidArgumentException('No event name(s) and priority
				specified for the Closure listener.');
			}

			foreach ($events as $name => $priority)
			{
				if (!isset($this->listeners[$name]))
				{
					$this->listeners[$name] = new ListenersPriorityQueue;
				}

				$this->listeners[$name]->add($listener, $priority);
			}

			return $this;
		}

		// We deal with a "normal" object.
		$methods = get_class_methods($listener);

		if (!empty($events))
		{
			$methods = array_intersect($methods, array_keys($events));
		}

		// @deprecated
		$regex = $this->listenerFilter ?: '.*';

		foreach ($methods as $event)
		{
			// @deprecated - this outer `if` is deprecated.
			if (preg_match("#$regex#", $event))
			{
				// Retain this inner code after removal of the outer `if`.
				if (!isset($this->listeners[$event]))
				{
					$this->listeners[$event] = new ListenersPriorityQueue;
				}

				$priority = isset($events[$event]) ? $events[$event] : Priority::NORMAL;

				$this->listeners[$event]->add($listener, $priority);
			}
		}

		return $this;
	}

	/**
	 * Get the priority of the given listener for the given event.
	 *
	 * @param   object|Closure         $listener  The listener.
	 * @param   EventInterface|string  $event     The event object or name.
	 *
	 * @return  mixed  The listener priority or null if the listener doesn't exist.
	 *
	 * @since   1.0
	 */
	public function getListenerPriority($listener, $event)
	{
		if ($event instanceof EventInterface)
		{
			$event = $event->getName();
		}

		if (isset($this->listeners[$event]))
		{
			return $this->listeners[$event]->getPriority($listener);
		}

		return null;
	}

	/**
	 * Get the listeners registered to the given event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  object[]  An array of registered listeners sorted according to their priorities.
	 *
	 * @since   1.0
	 */
	public function getListeners($event)
	{
		if ($event instanceof EventInterface)
		{
			$event = $event->getName();
		}

		if (isset($this->listeners[$event]))
		{
			return $this->listeners[$event]->getAll();
		}

		return array();
	}

	/**
	 * Tell if the given listener has been added.
	 * If an event is specified, it will tell if the listener is registered for that event.
	 *
	 * @param   object|Closure         $listener  The listener.
	 * @param   EventInterface|string  $event     The event object or name.
	 *
	 * @return  boolean  True if the listener is registered, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasListener($listener, $event = null)
	{
		if ($event)
		{
			if ($event instanceof EventInterface)
			{
				$event = $event->getName();
			}

			if (isset($this->listeners[$event]))
			{
				return $this->listeners[$event]->has($listener);
			}
		}
		else
		{
			foreach ($this->listeners as $queue)
			{
				if ($queue->has($listener))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Remove the given listener from this dispatcher.
	 * If no event is specified, it will be removed from all events it is listening to.
	 *
	 * @param   object|Closure         $listener  The listener to remove.
	 * @param   EventInterface|string  $event     The event object or name.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function removeListener($listener, $event = null)
	{
		if ($event)
		{
			if ($event instanceof EventInterface)
			{
				$event = $event->getName();
			}

			if (isset($this->listeners[$event]))
			{
				$this->listeners[$event]->remove($listener);
			}
		}

		else
		{
			foreach ($this->listeners as $queue)
			{
				$queue->remove($listener);
			}
		}

		return $this;
	}

	/**
	 * Clear the listeners in this dispatcher.
	 * If an event is specified, the listeners will be cleared only for that event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  Dispatcher  This method is chainable.
	 *
	 * @since   1.0
	 */
	public function clearListeners($event = null)
	{
		if ($event)
		{
			if ($event instanceof EventInterface)
			{
				$event = $event->getName();
			}

			if (isset($this->listeners[$event]))
			{
				unset($this->listeners[$event]);
			}
		}

		else
		{
			$this->listeners = array();
		}

		return $this;
	}

	/**
	 * Count the number of registered listeners for the given event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  integer  The number of registered listeners for the given event.
	 *
	 * @since   1.0
	 */
	public function countListeners($event)
	{
		if ($event instanceof EventInterface)
		{
			$event = $event->getName();
		}

		return isset($this->listeners[$event]) ? count($this->listeners[$event]) : 0;
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
		if (!($event instanceof EventInterface))
		{
			if (isset($this->events[$event]))
			{
				$event = $this->events[$event];
			}

			else
			{
				$event = new Event($event);
			}
		}

		if (isset($this->listeners[$event->getName()]))
		{
			foreach ($this->listeners[$event->getName()] as $listener)
			{
				if ($event->isStopped())
				{
					return $event;
				}

				if ($listener instanceof Closure)
				{
					call_user_func($listener, $event);
				}

				else
				{
					call_user_func(array($listener, $event->getName()), $event);
				}
			}
		}

		return $event;
	}
}
