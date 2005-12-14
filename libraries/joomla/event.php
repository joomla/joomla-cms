<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.classes.observer');

/**
 * Event handling class
 *
 * @abstract
 * @author Louis Landry <louis@webimagery.net>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JEvent extends JObserver {
	
	/**
	 * Event object to observe
	 * 
	 * @access private
	 * @var object
	 */
	var $_subject = null;

	/**
	 * Constructor
	 * 
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function __construct(& $subject) 
	{
		parent::__construct();
		
		// Set the subject to observe
		$this->_subject = & $subject;
		
		// Register the observer ($this) so we can be notified
		$this->_subject->attach($this);
	}


	/**
	 * Method to update the state of event objects
	 * 
	 * @abstract Implement in child classes
	 * @access public
	 * @return void
	 * @since 1.1
	 */
	function update() {
		JError::raiseError('9', 'JEvent::update: Method not implemented', 'This method should be implemented in a child class');
	}
}
 
 /**
* Event dispatcher class
* 
* @package Joomla
* @subpackage JFramework
* @since 1.0
*/
class JEventDispatcher extends JObservable
{
	/**
	* Constructor
	*/
	function __construct() {
		parent::__construct();
	}

	/**
	 * Returns a reference to the global Language object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $dispatcher = &JEventDispatcher::getInstance();</pre>
	 *
	 * @return JEventDispatcher  The EventDispatcher object.
	 * @since 1.1
	 */
	function &getInstance()
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[0])) {
			$instances[0] = new JEventDispatcher();
		}

		return $instances[0];
	}

	/**
	* Registers a function to a particular event group
	* 
	* @param string The event name
	* @param string The function name
	*/
	function registerFunction( $event, $function ) {
		$this->attach(array( $function ), $event);
	}

	/**
	* Calls all functions associated with an event group
	* 
	* @param string The event name
	* @param array An array of arguments
	* @param boolean True is unpublished bots are to be processed [DEPRECEATED]
	* @return array An array of results from each function call
	*/
	function trigger( $event, $args=null, $doUnpublished=false ) 
	{
		$result = array();

		if ($args === null) {
			$args = array();
		}
		if ($event == 'onPrepareContent' || $doUnpublished) {
			// prepend the published argument
			array_unshift( $args, null );
		}
		if (isset( $this->_observers[$event] )) {
			foreach ($this->_observers[$event] as $func) {
				if (function_exists( $func[0] )) {
					$result[] = call_user_func_array( $func[0], $args );
				}
			}
		}
		return $result;
	}
	/**
	* Same as trigger but only returns the first event and
	* allows for a variable argument list
	* 
	* @param string The event name
	* @return array The result of the first function call
	*/
	function call( $event ) {

		$args =& func_get_args();
		array_shift( $args );

		if (isset( $this->_observers[$event] )) {
			foreach ($this->_observers[$event] as $func) {
				if (function_exists( $func[0] )) {
						return call_user_func_array( $func[0], $args );
				}
			}
		}
		return null;
	}
}
?>