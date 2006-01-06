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

jimport('joomla.common.base.observer');

/**
 * Event dispatcher class
 *
 * @package 	Joomla.Framework
 * @since 1.0
 */
class JEventDispatcher extends JObservable 
{
	/**
	* Constructor
	* 
	* @access protected
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
	 * @access public
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
	* Registers a function to the event dispatcher
	*
	* @access public
	* @param string The event name
	* @param string The function name
	* @since 1.1
	*/
	function register( $event, $handler ) {
		$this->attach(array( 'event' => $event, 'handler' => $handler ));
	}

	/**
	* Calls all functions associated with an event group
	*
	* @access public
	* @param string The event name
	* @param array An array of arguments
	* @param boolean True is unpublished bots are to be processed [DEPRECEATED]
	* @return array An array of results from each function call
	* @since 1.1
	*/
	function trigger( $event, $args=null, $doUnpublished=false )
	{
		if ($args === null) {
			$args = array();
		}
		if ($event == 'onPrepareContent' || $doUnpublished) {
			// prepend the published argument
			array_unshift( $args, null );
		}

		$result = array();

		foreach ($this->_observers as $observer) 
		{
			if (is_array($observer) && $observer['event'] == $event) 
			{
				// We are handling a function or a deprecated plugin
				if (function_exists( $observer['handler'] )) {
					$result[] = call_user_func_array( $observer['handler'], $args );
				} else {
					JError::raiseWarning( 'SOME_ERROR_CODE', 'JEventDispatcher::dispatch: Event Handler Method does not exist.', 'Method called: '.$observer['handler']);
				}
			} 
			else
			{
				// We are handling an observer object
				if (is_object($observer)) {
					$args['event'] = $event;
					$result[] = $observer->update($args);
				}
			} 
		}
	
		return $result;
	}
	/**
	* Same as trigger but only returns the first event and
	* allows for a variable argument list
	*
	* @access public
	* @param string The event name
	* @return array The result of the first function call
	*/
	function call( $event ) {

		$args =& func_get_args();
		array_shift( $args );

		foreach ($this->_observers as $observer) {

			if (is_array($observer) && $observer['event'] == $event) {
				// We are handling a function or JBot
				if (function_exists( $observer['handler'] )) {
					$result[] = call_user_func_array( $observer['handler'], $args );
				} else {
					JError::raiseWarning( 'SOME_ERROR_CODE', 'JEventDispatcher::dispatch: Event Handler Method does not exist.', 'Method called: '.$observer['handler']);
				}
			} elseif (is_object($observer)) {
				$args['event'] = $event;
				$result[] = $observer->update($args);
			} else {
				// Continue
			}
		}

		return null;
	}

	/**
	 * This method fires the given event and passes all aditional arguements to the
	 * event handler.  It handles both JBot functions and JPlugin objects that are
	 * registered to the event.
	 *
	 * @access public
	 * @param string $event The event to fire on all observers
	 * @return array An array of return values from the observers
	 * @since 1.1
	 */
	function dispatch( $event ) {

		$args = func_get_args();
		array_shift( $args );

		$result = array();

		foreach ($this->_observers as $observer) {

			if (is_array($observer) && $observer['event'] == $event) {
				// We are handling a function or JBot
				if (function_exists( $observer['handler'] )) {
					$result[] = call_user_func_array( $observer['handler'], $args );
				} else {
					JError::raiseWarning( 'SOME_ERROR_CODE', 'JEventDispatcher::dispatch: Event Handler Method does not exist.', 'Method called: '.$observer['handler']);
				}
			} elseif (is_object($observer)) {
				$args['event'] = $event;
				$result[] = $observer->update($args);
			} else {
				// Continue
			}
		}

		return $result;
	}
}
?>