<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Event;

defined('_JEXEC') || die;

use FOF30\Container\Container;

class Dispatcher implements Observable
{
	/** @var   Container  The container this event dispatcher is attached to */
	protected $container = null;

	/** @var   array  The observers attached to the dispatcher */
	protected $observers = [];

	/** @var   array  Maps events to observers */
	protected $events = [];

	/**
	 * Public constructor
	 *
	 * @param   Container  $container  The container this event dispatcher is attached to
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Returns the container this event dispatcher is attached to
	 *
	 * @return  Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Attaches an observer to the object
	 *
	 * @param   Observer  $observer  The observer to attach
	 *
	 * @return  Dispatcher  Ourselves, for chaining
	 */
	public function attach(Observer $observer)
	{
		$className = get_class($observer);

		// Make sure this observer is not already registered
		if (isset($this->observers[$className]))
		{
			return $this;
		}

		// Attach observer
		$this->observers[$className] = $observer;

		// Register the observable events
		$events = $observer->getObservableEvents();

		foreach ($events as $event)
		{
			$event = strtolower($event);

			if (!isset($this->events[$event]))
			{
				$this->events[$event] = [$className];
			}
			else
			{
				$this->events[$event][] = $className;
			}
		}

		return $this;
	}

	/**
	 * Detaches an observer from the object
	 *
	 * @param   Observer  $observer  The observer to detach
	 *
	 * @return  Dispatcher  Ourselves, for chaining
	 */
	public function detach(Observer $observer)
	{
		$className = get_class($observer);

		// Make sure this observer is already registered
		if (!isset($this->observers[$className]))
		{
			return $this;
		}

		// Unregister the observable events
		$events = $observer->getObservableEvents();

		foreach ($events as $event)
		{
			$event = strtolower($event);

			if (isset($this->events[$event]))
			{
				$key = array_search($className, $this->events[$event]);

				if ($key !== false)
				{
					unset($this->events[$event][$key]);

					if (empty($this->events[$event]))
					{
						unset ($this->events[$event]);
					}
				}
			}
		}

		// Detach observer
		unset($this->observers[$className]);

		return $this;
	}

	/**
	 * Is an observer object already registered with this dispatcher?
	 *
	 * @param   Observer  $observer  The observer to check if it's attached
	 *
	 * @return  boolean
	 */
	public function hasObserver(Observer $observer)
	{
		$className = get_class($observer);

		return $this->hasObserverClass($className);
	}

	/**
	 * Is there an observer of the specified class already registered with this dispatcher?
	 *
	 * @param   string  $className  The observer class name to check if it's attached
	 *
	 * @return  boolean
	 */
	public function hasObserverClass($className)
	{
		return isset($this->observers[$className]);
	}

	/**
	 * Returns an observer attached to this behaviours dispatcher by its class name
	 *
	 * @param   string  $className  The class name of the observer object to return
	 *
	 * @return  null|Observer
	 */
	public function getObserverByClass($className)
	{
		if (!$this->hasObserverClass($className))
		{
			return null;
		}

		return $this->observers[$className];
	}

	/**
	 * Triggers an event in the attached observers
	 *
	 * @param   string  $event  The event to attach
	 * @param   array   $args   Arguments to the event handler
	 *
	 * @return  array
	 */
	public function trigger($event, array $args = [])
	{
		$event = strtolower($event);

		$result = [];

		// Make sure the event is known to us, otherwise return an empty array
		if (!isset($this->events[$event]) || empty($this->events[$event]))
		{
			return $result;
		}

		foreach ($this->events[$event] as $className)
		{
			// Make sure the observer exists.
			if (!isset($this->observers[$className]))
			{
				continue;
			}

			// Get the observer
			$observer = $this->observers[$className];

			// Make sure the method exists
			if (!method_exists($observer, $event))
			{
				continue;
			}

			// Call the event handler and add its output to the return value. The switch allows for execution up to 2x
			// faster than using call_user_func_array
			switch (count($args))
			{
				case 0:
					$result[] = $observer->{$event}();
					break;
				case 1:
					$result[] = $observer->{$event}($args[0]);
					break;
				case 2:
					$result[] = $observer->{$event}($args[0], $args[1]);
					break;
				case 3:
					$result[] = $observer->{$event}($args[0], $args[1], $args[2]);
					break;
				case 4:
					$result[] = $observer->{$event}($args[0], $args[1], $args[2], $args[3]);
					break;
				case 5:
					$result[] = $observer->{$event}($args[0], $args[1], $args[2], $args[3], $args[4]);
					break;
				default:
					$result[] = call_user_func_array([$observer, $event], $args);
					break;
			}
		}

		// Return the observers' result in an array
		return $result;
	}

	/**
	 * Asks each observer to handle an event based on the provided arguments. The first observer to return a non-null
	 * result wins. This is a *very* simplistic implementation of the Chain of Command pattern.
	 *
	 * @param   string  $event  The event name to handle
	 * @param   array   $args   The arguments to the event
	 *
	 * @return  mixed  Null if the event can't be handled by any observer
	 */
	public function chainHandle($event, $args = [])
	{
		$event = strtolower($event);

		$result = null;

		// Make sure the event is known to us, otherwise return an empty array
		if (!isset($this->events[$event]) || empty($this->events[$event]))
		{
			return $result;
		}

		foreach ($this->events[$event] as $className)
		{
			// Make sure the observer exists.
			if (!isset($this->observers[$className]))
			{
				continue;
			}

			// Get the observer
			$observer = $this->observers[$className];

			// Make sure the method exists
			if (!method_exists($observer, $event))
			{
				continue;
			}

			// Call the event handler and add its output to the return value. The switch allows for execution up to 2x
			// faster than using call_user_func_array
			switch (count($args))
			{
				case 0:
					$result = $observer->{$event}();
					break;
				case 1:
					$result = $observer->{$event}($args[0]);
					break;
				case 2:
					$result = $observer->{$event}($args[0], $args[1]);
					break;
				case 3:
					$result = $observer->{$event}($args[0], $args[1], $args[2]);
					break;
				case 4:
					$result = $observer->{$event}($args[0], $args[1], $args[2], $args[3]);
					break;
				case 5:
					$result = $observer->{$event}($args[0], $args[1], $args[2], $args[3], $args[4]);
					break;
				default:
					$result = call_user_func_array([$observer, $event], $args);
					break;
			}

			if (!is_null($result))
			{
				return $result;
			}
		}

		// Return the observers' result in an array
		return $result;
	}
}
