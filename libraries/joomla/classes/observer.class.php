<?php
/**
* @version $Id: $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.classes.object');

/**
 * Abstract observer class to implement the observer design pattern
 *
 * @abstract
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JObserver extends JObject {
	
	/**
	 * Constructor
	 */
	function __construct() {
	}

	/**
	 * Method to update the state of observable objects
	 * 
	 * @abstract Implement in child classes
	 * @access public
	 * @return void
	 */
	function update() {
		JError::raiseError('9', 'JObserver::update: Method not implemented', 'This method should be implemented in a child class');
	}
}

/**
 * Abstract observable class to implement the observer design pattern
 *
 * @abstract
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
 
class JObservable extends JObject 
{
	/**
	 * An array of Observer objects to notify
	 * 
	 * @access private
	 * @var array
	 */
	var $_observers = array();

	
	/**
	 * Constructor
	 */
	function __construct() {
		$this->_observers = array();
	}

	/**
	 * Update each attached observer object
	 * 
	 * @access public
	 * @return void
	 */
	function notify($namespace = '_unknow') 
	{
		// Iterate through the _observers array
		foreach ($this->_observers[$namespace] as $observer) {
			$observer->update();
		}
	}

	/**
	 * Attach an observer object
	 * 
	 * @access public
	 * @param object $observer An observer object to attach
	 * @return void
	 */
	function attach( $observer, $namespace = '_unknow') {
		$this->_observers[$namespace][] = $observer;
	}
	
	/**
	 * Detach an observer object
	 * 
	 * @access public
	 * @param object $observer An observer object to detach
	 * @return void
	 */
	function detach( $observer) {
		//TODO :: create detach method
	}
}
?>

