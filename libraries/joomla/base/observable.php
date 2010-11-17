<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Abstract observable class to implement the observer design pattern
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JObservable extends JObject
{
	/**
	 * An array of Observer objects to notify
	 *
	 * @access protected
	 * @var array
	 */
	protected $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @access protected
	 * @var mixed
	 */
	protected $_state = null;

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @access protected
	 * @var array
	 */
	protected $_methods = array();

	/**
	 * Constructor
	 *
	 * @access protected - Make Sure it's not directly instansiated
	 */
	function __construct()
	{
		$this->_observers = array();
	}

	/**
	 * Get the state of the JObservable object
	 *
	 * @return	mixed	The state of the object.
	 * @since	1.5
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Update each attached observer object and return an array of their return values
	 *
	 * @return	array	Array of return values from the observers
	 * @since	1.5
	 */
	public function notify()
	{
		// Iterate through the _observers array
		foreach ($this->_observers as $observer)
		{
			$return[] = $observer->update();
		}

		return $return;
	}

	/**
	 * Attach an observer object
	 *
	 * @param	object	$observer	An observer object to attach
	 *
	 * @return	void
	 * @since	1.5
	 */
	public function attach($observer)
	{
		if (is_array($observer)) {
			if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler'])) {
				return;
			}

			// Make sure we haven't already attached this array as an observer
			foreach ($this->_observers as $check)
			{
				if (is_array($check) && $check['event']==$observer['event'] && $check['handler']==$observer['handler']) {
					return;
				}
			}

			$this->_observers[] = $observer;
			end($this->_observers);
			$methods = array($observer['event']);
		}
		else {
			if (!($observer instanceof JObserver)) {
				return;
			}

			// Make sure we haven't already attached this object as an observer
			$class = get_class($observer);

			foreach ($this->_observers as $check)
			{
				if ($check instanceof $class) {
					return;
				}
			}

			$this->_observers[] = $observer;
			$methods = array_diff(get_class_methods($observer), get_class_methods('JPlugin'));
		}

		$key = key($this->_observers);

		foreach($methods AS $method)
		{
			$method = strtolower($method);

			if (!isset($this->_methods[$method])) {
				$this->_methods[$method] = array();
			}

			$this->_methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @param	object	$observer	An observer object to detach.
	 *
	 * @return	boolean	True if the observer object was detached.
	 * @since 1.5
	 */
	public function detach($observer)
	{
		// Initialise variables.
		$retval = false;

		$key = array_search($observer, $this->_observers);

		if ($key !== false) {
			unset($this->_observers[$key]);
			$retval = true;

			foreach($this->_methods AS &$method)
			{
				$k = array_search($key, $method);

				if ($k !== false) {
					unset($method[$k]);
				}
			}
		}

		return $retval;
	}
}
