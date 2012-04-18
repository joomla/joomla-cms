<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/event/event.php';

/**
 * General inspector class for JEvent.
 *
 * @package Joomla.UnitTest
 * @subpackage Event
 * @since 11.3
 */
class JEventInspector extends JEvent
{
	/**
	* Method for inspecting protected variables.
	*
	* @return mixed The value of the class variable.
	*/
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		} else {
			trigger_error('Undefined or private property: ' . __CLASS__.'::'.$name, E_USER_ERROR);
			return null;
		}
	}
	
	/**
	 * Mock Event Method
	 * 
	 * @return mixed A value to test against
	 */
	public function onTestEvent($var1 = null, $var2 = null)
	{
		$return = '';
		if (is_string($var1)) {
			$return .= $var1;
		}
		if (is_string($var2)) {
			$return .= $var2;
		}
		if(is_array($var1)) {
			$return .= implode('',$var1);
		}
		return $return;
	}
}

/**
 * Mock function to test event system in JEventDispatcher
 * 
 * @return string Static string "JEventDispatcherMockFunction executed"
 * 
 * @since 11.3
 */
function JEventMockFunction()
{
	return 'JEventDispatcherMockFunction executed';
}