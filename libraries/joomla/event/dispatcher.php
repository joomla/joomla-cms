<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Event
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.base.observable');

/**
 * Class to handle dispatching of events.
 *
 * This is the Observable part of the Observer design pattern
 * for the event architecture.
 *
 * @package		Joomla.Framework
 * @subpackage	Event
 * @since		1.5
 * @see			JPlugin
 * @link		http://docs.joomla.org/Tutorial:Plugins Plugin tutorials
 */
class JDispatcher extends JObservable
{
	/**
	 * Returns the global Event Dispatcher object, only creating it
	 * if it doesn't already exist.
	 *
	 * @access	public
	 * @return	JDispatcher		The EventDispatcher object.
	 * @since	1.5
	 */
	public static function getInstance()
	{
		static $instance;

		if (!is_object($instance)) {
			$instance = new JDispatcher();
		}

		return $instance;
	}

	/**
	 * Registers an event handler to the event dispatcher
	 *
	 * @access	public
	 * @param	string	$event		Name of the event to register handler for
	 * @param	string	$handler	Name of the event handler
	 * @return	void
	 * @since	1.5
	 */
	public function register($event, $handler)
	{
		// Are we dealing with a class or function type handler?
		if (function_exists($handler))
		{
			// Ok, function type event handler... lets attach it.
			$method = array('event' => $event, 'handler' => $handler);
			$this->attach($method);
		}
		elseif (class_exists($handler))
		{
			// Ok, class type event handler... lets instantiate and attach it.
			$this->attach(new $handler($this));
		}
		else
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::sprintf('JLIB_EVENT_ERROR_DISPATCHER', $handler));
		}
	}

	/**
	 * Triggers an event by dispatching arguments to all observers that handle
	 * the event and returning their return values.
	 *
	 * @access	public
	 * @param	string	$event		The event to trigger.
	 * @param	array	$args		An array of arguments.
	 * @return	array	An array of results from each function call.
	 * @since	1.5
	 */
	public function trigger($event, $args = array())
	{
		// Initialise variables.
		$result = array();

		/*
		 * If no arguments were passed, we still need to pass an empty array to
		 * the call_user_func_array function.
		 */
		$args = (array)$args;

		$event = strtolower($event);

		// Check if any plugins are attached to the event.
		if (!isset($this->_methods[$event]) || empty($this->_methods[$event])) {
			// No Plugins Associated To Event!
			return $result;
		}
		// Loop through all plugins having a method matching our event
		foreach ($this->_methods[$event] AS $key)
		{
			// Check if the plugin is present.
			if (!isset($this->_observers[$key])) {
				continue;
			}

			// Fire the event for an object based observer.
			if (is_object($this->_observers[$key])) {
				$args['event'] = $event;
				$value = $this->_observers[$key]->update($args);
			}
			// Fire the event for a function based observer.
			elseif (is_array($this->_observers[$key])) {
				$value = call_user_func_array($this->_observers[$key]['handler'], $args);
			}
			if (isset($value)) {
				$result[] = $value;
			}
		}

		return $result;
	}
}
