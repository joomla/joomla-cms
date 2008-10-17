<?php
/**
* @version		$Id:observer.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Base
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

/**
 * Abstract observable class to implement the observer design pattern
 *
 * @abstract
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.5
 */
class JObservable extends JObject
{
	/**
	 * An array of Observer objects to notify
	 *
	 * @access private
	 * @var array
	 */
	protected $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @access private
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
	protected function __construct() {
		$this->_observers = array();
	}

	/**
	 * Get the state of the JObservable object
	 *
	 * @access public
	 * @return mixed The state of the object
	 * @since 1.5
	 */
	public function getState() {
		return $this->_state;
	}

	/**
	 * Update each attached observer object and return an array of their return values
	 *
	 * @access public
	 * @return array Array of return values from the observers
	 * @since 1.5
	 */
	public function notify()
	{
		// Iterate through the _observers array
		foreach ($this->_observers as $observer) {
			$return[] = $observer->update();
		}
		return $return;
	}

	/**
	 * Attach an observer object
	 *
	 * @access public
	 * @param object $observer An observer object to attach
	 * @return void
	 * @since 1.5
	 */
	public function attach( &$observer)
	{
		// Make sure we haven't already attached this object as an observer
		if (is_object($observer))
		{

			$class = get_class($observer);
			foreach ($this->_observers as $check) {
				if ($check INSTANCEOF $class) {
					return;
				}
			}
			$this->_observers[] =& $observer;
			$methods = get_class_methods($observer);
		} else {
			$this->_observers[] =& $observer;
			$methods = array($observer['event']);
		}
		end($this->_observers);
		$key = key($this->_observers);
		foreach($methods AS $method) {
			$method = strtolower($method);
			if(!isset($this->_methods[$method])) {
				$this->_methods[$method] = array();
			}
			$this->_methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @access public
	 * @param object $observer An observer object to detach
	 * @return boolean True if the observer object was detached
	 * @since 1.5
	 */
	public function detach( $observer)
	{
		// Initialize variables
		$retval = false;

		$key = array_search($observer, $this->_observers);

		if ( $key !== false )
		{
			unset($this->_observers[$key]);
			$retval = true;
			foreach($this->_methods AS &$method) {
				$k = array_search($key, $method);
				if($k !== false) {
					unset($method[$k]);
				}
			}
		}
		return $retval;
	}
}

