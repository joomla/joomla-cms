<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Event
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.base.observable');

/**
 * Class to handle dispatching of events.
 *
 * This is the Observable part of the Observer design pattern
 * for the event architecture.
 *
 * @package 	Joomla.Framework
 * @subpackage	Event
 * @since	1.5
 * @see		JPlugin
 * @link http://dev.joomla.org/component/option,com_jd-wiki/Itemid,31/id,tutorials:plugins/ Plugins tutorial
 */
class JDispatcher extends JObservable
{
	/**
	 * Constructor
	 *
	 * @access	protected
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	/**
	 * Returns a reference to the global Event Dispatcher object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $dispatcher = &JDispatcher::getInstance();</pre>
	 *
	 * @access	public
	 * @return	JDispatcher	The EventDispatcher object.
	 * @since	1.5
	 */
	public static function & getInstance()
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
			$method = array ('event' => $event, 'handler' => $handler);
			$this->attach($method);
		}
		elseif (class_exists($handler))
		{
			 //Ok, class type event handler... lets instantiate and attach it.
			$this->attach(new $handler($this));
		}
		else
		{
			JError::raiseWarning('SOME_ERROR_CODE', 'JDispatcher::register: Event handler not recognized.', 'Handler: '.$handler );
		}
	}

	/**
	 * Triggers an event by dispatching arguments to all observers that handle
	 * the event and returning their return values.
	 *
	 * @access	public
	 * @param	string	$event			The event name
	 * @param	array	$args			An array of arguments
	 * @param	boolean	$doUnpublished	[DEPRECATED]
	 * @return	array	An array of results from each function call
	 * @since	1.5
	 */
	public function trigger($event, $args = null, $doUnpublished = false)
	{
		// Initialize variables
		$result = array ();

		/*
		 * If no arguments were passed, we still need to pass an empty array to
		 * the call_user_func_array function.
		 */
		if ($args === null) {
			$args = array ();
		}

		$event = strtolower($event);
		if(!isset($this->_methods[$event]) || empty($this->_methods[$event])) {
			//No Plugins Associated To Event!
			return $result;
		}
		//Loop through all plugins having a method matching our event
		foreach($this->_methods[$event] AS $key) {
			if(!isset($this->_observers[$key])) {
				//for some reason there's a disconnect...  Continue to next plugin key
				continue;
			}
			if(is_array($this->_observers[$key])) {
				if(is_callable($this->_observers[$key]['handler'])) {
					$result[] = call_user_func_array($this->_observers[$key]['handler'], $args);
				} else {
					JError::raiseWarning('SOME_ERROR_CODE', 'JDispatcher::trigger: Event Handler Method does not exist.', 'Method called: '.$observer['handler']);
				}
			} elseif(is_object($this->_observers[$key])) {
				$args['event'] = $event;
				if(is_callable(array($this->_observers[$key], 'update'))) {
					$result[] = $this->_observers[$key]->update($args);
				} else {
					/*
					 * At this point, we know that the registered observer is
					 * neither a function type observer nor an object type
					 * observer.  PROBLEM, lets throw an error.
					 */
					JError::raiseWarning('SOME_ERROR_CODE', 'JDispatcher::trigger: Unknown Event Handler.', $observer );
				}
			}
		}
		return $result;
	}
}

