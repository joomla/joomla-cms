<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to handle dispatching of events.
 *
 * This is the Observable part of the Observer design pattern
 * for the event architecture.
 *
 * @see         JPlugin
 * @since       12.1
 * @deprecated  4.0  The CMS' Event classes will be replaced with the `joomla/event` package
 */
class JEventDispatcher extends JObject
{
	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @var    mixed
	 * @since  11.3
	 */
	protected $_state = null;

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected $_methods = array();

	/**
	 * Stores the singleton instance of the dispatcher.
	 *
	 * @var    JEventDispatcher
	 * @since  11.3
	 */
	protected static $instance = null;

	/**
	 * Returns the global Event Dispatcher object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  JEventDispatcher  The EventDispatcher object.
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		if (self::$instance === null)
		{
			self::$instance = new static;
		}

		return self::$instance;
	}

	/**
	 * Get the state of the JEventDispatcher object
	 *
	 * @return  mixed    The state of the object.
	 *
	 * @since   11.3
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Registers an event handler to the event dispatcher
	 *
	 * @param   string  $event    Name of the event to register handler for
	 * @param   string  $handler  Name of the event handler
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function register($event, $handler)
	{
		// Are we dealing with a class or callback type handler?
		if (is_callable($handler))
		{
			// Ok, function type event handler... let's attach it.
			$method = array('event' => $event, 'handler' => $handler);
			$this->attach($method);
		}
		elseif (class_exists($handler))
		{
			// Ok, class type event handler... let's instantiate and attach it.
			$this->attach(new $handler($this));
		}
		else
		{
			throw new InvalidArgumentException('Invalid event handler.');
		}
	}

	/**
	 * Triggers an event by dispatching arguments to all observers that handle
	 * the event and returning their return values.
	 *
	 * @param   string  $event  The event to trigger.
	 * @param   array   $args   An array of arguments.
	 *
	 * @return  array  An array of results from each function call.
	 *
	 * @since   11.1
	 */
	public function trigger($event, $args = array())
	{
		$result = array();

		/*
		 * If no arguments were passed, we still need to pass an empty array to
		 * the call_user_func_array function.
		 */
		$args = (array) $args;

		$event = strtolower($event);

		// Check if any plugins are attached to the event.
		if (!isset($this->_methods[$event]) || empty($this->_methods[$event]))
		{
			// No Plugins Associated To Event!
			return $result;
		}

		// Loop through all plugins having a method matching our event
		foreach ($this->_methods[$event] as $key)
		{
			// Check if the plugin is present.
			if (!isset($this->_observers[$key]))
			{
				continue;
			}

			// Fire the event for an object based observer.
			if (is_object($this->_observers[$key]))
			{
				$args['event'] = $event;
				$value = $this->_observers[$key]->update($args);
			}
			// Fire the event for a function based observer.
			elseif (is_array($this->_observers[$key]))
			{
				$value = call_user_func_array($this->_observers[$key]['handler'], $args);
			}

			if (isset($value))
			{
				$result[] = $value;
			}
		}

		return $result;
	}

	/**
	 * Attach an observer object
	 *
	 * @param   object  $observer  An observer object to attach
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function attach($observer)
	{
		if (is_array($observer))
		{
			if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler']))
			{
				return;
			}

			// Make sure we haven't already attached this array as an observer
			foreach ($this->_observers as $check)
			{
				if (is_array($check) && $check['event'] === $observer['event'] && $check['handler'] === $observer['handler'])
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			$methods = array($observer['event']);
		}
		else
		{
			if (!($observer instanceof JEvent))
			{
				return;
			}

			// Make sure we haven't already attached this object as an observer
			$class = get_class($observer);

			foreach ($this->_observers as $check)
			{
				if ($check instanceof $class)
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			$methods = array_diff(get_class_methods($observer), get_class_methods('JPlugin'));
		}

		end($this->_observers);
		$key = key($this->_observers);

		foreach ($methods as $method)
		{
			$method = strtolower($method);

			if (!isset($this->_methods[$method]))
			{
				$this->_methods[$method] = array();
			}

			$this->_methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @param   object  $observer  An observer object to detach.
	 *
	 * @return  boolean  True if the observer object was detached.
	 *
	 * @since   11.3
	 */
	public function detach($observer)
	{
		$retval = false;

		$key = array_search($observer, $this->_observers);

		if ($key !== false)
		{
			unset($this->_observers[$key]);
			$retval = true;

			foreach ($this->_methods as &$method)
			{
				$k = array_search($key, $method);

				if ($k !== false)
				{
					unset($method[$k]);
				}
			}
		}

		return $retval;
	}
}
