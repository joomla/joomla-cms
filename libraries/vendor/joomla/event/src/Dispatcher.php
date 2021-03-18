<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * Implementation of a DispatcherInterface supporting prioritized listeners.
 *
 * @since  1.0
 */
class Dispatcher implements DispatcherInterface
{
	/**
	 * An array of registered events indexed by the event names.
	 *
	 * @var    EventInterface[]
	 * @since  1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	protected $events = [];

	/**
	 * An array of ListenersPriorityQueue indexed by the event names.
	 *
	 * @var    ListenersPriorityQueue[]
	 * @since  1.0
	 */
	protected $listeners = [];

	/**
	 * Set an event to the dispatcher. It will replace any event with the same name.
	 *
	 * @param   EventInterface  $event  The event.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function setEvent(EventInterface $event)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		$this->events[$event->getName()] = $event;

		return $this;
	}

	/**
	 * Add an event to this dispatcher, only if it is not existing.
	 *
	 * @param   EventInterface  $event  The event.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function addEvent(EventInterface $event)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

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
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function hasEvent($event)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

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
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function getEvent($name, $default = null)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		if (isset($this->events[$name]))
		{
			return $this->events[$name];
		}

		return $default;
	}

	/**
	 * Remove an event from this dispatcher. The registered listeners will remain.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function removeEvent($event)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

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
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function getEvents()
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		return $this->events;
	}

	/**
	 * Clear all events.
	 *
	 * @return  EventInterface[]  The old events.
	 *
	 * @since   1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function clearEvents()
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		$events       = $this->events;
		$this->events = [];

		return $events;
	}

	/**
	 * Count the number of registered event.
	 *
	 * @return  integer  The numer of registered events.
	 *
	 * @since   1.0
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	public function countEvents()
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated without replacement and will be removed in 3.0.',
				__METHOD__
			),
			E_USER_DEPRECATED
		);

		return \count($this->events);
	}

	/**
	 * Attaches a listener to an event
	 *
	 * @param   string    $eventName  The event to listen to.
	 * @param   callable  $callback   A callable function
	 * @param   integer   $priority   The priority at which the $callback executed
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	public function addListener(string $eventName, callable $callback, int $priority = 0): bool
	{
		if (!isset($this->listeners[$eventName]))
		{
			$this->listeners[$eventName] = new ListenersPriorityQueue;
		}

		$this->listeners[$eventName]->add($callback, $priority);

		return true;
	}

	/**
	 * Get the priority of the given listener for the given event.
	 *
	 * @param   string    $eventName  The event to listen to.
	 * @param   callable  $callback   A callable function
	 *
	 * @return  mixed  The listener priority or null if the listener doesn't exist.
	 *
	 * @since   1.0
	 */
	public function getListenerPriority($eventName, callable $callback)
	{
		if (isset($this->listeners[$eventName]))
		{
			return $this->listeners[$eventName]->getPriority($callback);
		}
	}

	/**
	 * Get the listeners registered to the given event.
	 *
	 * @param   string|null  $event  The event to fetch listeners for or null to fetch all listeners
	 *
	 * @return  callable[]  An array of registered listeners sorted according to their priorities.
	 *
	 * @since   1.0
	 */
	public function getListeners(?string $event = null)
	{
		if ($event !== null)
		{
			if (isset($this->listeners[$event]))
			{
				return $this->listeners[$event]->getAll();
			}

			return [];
		}

		$dispatcherListeners = [];

		foreach ($this->listeners as $registeredEvent => $listeners)
		{
			$dispatcherListeners[$registeredEvent] = $listeners->getAll();
		}

		return $dispatcherListeners;
	}

	/**
	 * Tell if the given listener has been added.
	 *
	 * If an event is specified, it will tell if the listener is registered for that event.
	 *
	 * @param   callable  $callback   The callable to check is listening to the event.
	 * @param   string    $eventName  An optional event name to check a listener is subscribed to.
	 *
	 * @return  boolean  True if the listener is registered, false otherwise.
	 *
	 * @since   1.0
	 */
	public function hasListener(callable $callback, ?string $eventName = null)
	{
		if ($eventName)
		{
			if (isset($this->listeners[$eventName]))
			{
				return $this->listeners[$eventName]->has($callback);
			}
		}
		else
		{
			foreach ($this->listeners as $queue)
			{
				if ($queue->has($callback))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Removes an event listener from the specified event.
	 *
	 * @param   string    $eventName  The event to remove a listener from.
	 * @param   callable  $listener   The listener to remove.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeListener(string $eventName, callable $listener): void
	{
		if (isset($this->listeners[$eventName]))
		{
			$this->listeners[$eventName]->remove($listener);
		}
	}

	/**
	 * Clear the listeners in this dispatcher.
	 *
	 * If an event is specified, the listeners will be cleared only for that event.
	 *
	 * @param   string  $event  The event name.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function clearListeners($event = null)
	{
		if ($event)
		{
			if (isset($this->listeners[$event]))
			{
				unset($this->listeners[$event]);
			}
		}
		else
		{
			$this->listeners = [];
		}

		return $this;
	}

	/**
	 * Count the number of registered listeners for the given event.
	 *
	 * @param   string  $event  The event name.
	 *
	 * @return  integer
	 *
	 * @since   1.0
	 */
	public function countListeners($event)
	{
		return isset($this->listeners[$event]) ? \count($this->listeners[$event]) : 0;
	}

	/**
	 * Adds an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addSubscriber(SubscriberInterface $subscriber): void
	{
		foreach ($subscriber->getSubscribedEvents() as $eventName => $params)
		{
			if (\is_array($params))
			{
				$this->addListener($eventName, [$subscriber, $params[0]], $params[1] ?? Priority::NORMAL);
			}
			else
			{
				$this->addListener($eventName, [$subscriber, $params]);
			}
		}
	}

	/**
	 * Removes an event subscriber.
	 *
	 * @param   SubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeSubscriber(SubscriberInterface $subscriber): void
	{
		foreach ($subscriber->getSubscribedEvents() as $eventName => $params)
		{
			if (\is_array($params))
			{
				$this->removeListener($eventName, [$subscriber, $params[0]]);
			}
			else
			{
				$this->removeListener($eventName, [$subscriber, $params]);
			}
		}
	}

	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param   string          $name   The name of the event to dispatch.
	 * @param   EventInterface  $event  The event to pass to the event handlers/listeners.
	 *                                  If not supplied, an empty EventInterface instance is created.
	 *                                  Note, not passing an event is deprecated and will be required as of 3.0.
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch(string $name, ?EventInterface $event = null): EventInterface
	{
		if (!($event instanceof EventInterface))
		{
			@trigger_error(
				sprintf(
					'Not passing an event object to %s() is deprecated, as of 3.0 the $event argument will be required.',
					__METHOD__
				),
				E_USER_DEPRECATED
			);

			$event = $this->getDefaultEvent($name);
		}

		if (isset($this->listeners[$event->getName()]))
		{
			foreach ($this->listeners[$event->getName()] as $listener)
			{
				if ($event->isStopped())
				{
					return $event;
				}

				$listener($event);
			}
		}

		return $event;
	}

	/**
	 * Trigger an event.
	 *
	 * @param   EventInterface|string  $event  The event object or name.
	 *
	 * @return  EventInterface  The event after being passed through all listeners.
	 *
	 * @since   1.0
	 * @deprecated  3.0  Use dispatch() instead.
	 */
	public function triggerEvent($event)
	{
		@trigger_error(
			sprintf(
				'%1$s() is deprecated and will be removed in 3.0, use %2$s::dispatch() instead.',
				__METHOD__,
				DispatcherInterface::class
			),
			E_USER_DEPRECATED
		);

		if (!($event instanceof EventInterface))
		{
			$event = $this->getDefaultEvent($event);
		}

		return $this->dispatch($event->getName(), $event);
	}

	/**
	 * Get an event object for the specified event name
	 *
	 * @param   string  $name  The event name to get an EventInterface object for
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @deprecated  3.0  Default event objects will no longer be supported
	 */
	private function getDefaultEvent(string $name): EventInterface
	{
		if (isset($this->events[$name]))
		{
			return $this->events[$name];
		}

		return new Event($name);
	}
}
