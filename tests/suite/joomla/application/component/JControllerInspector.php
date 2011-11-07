<?php
/**
 * @version   $Id: JControllerHelper.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * General inspector class for JController.
 *
 * @package    Joomla.UnitTest
 * @subpackage Application.Component
 * @since      11.3
 */
class JControllerInspector extends JController
{
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	* Method for inspecting protected variables.
	*
	* @return mixed The value of the class variable.
	*/
	public function __get($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			trigger_error('Undefined or private property: ' . __CLASS__ . '::' . $name, E_USER_ERROR);
			return null;
		}
	}

	/**
	* Sets any property from the class.
	*
	* @param   string  $property  The name of the class property.
	* @param   string  $value     The value of the class property.
	*
	* @return void
	*/
	public function __set($property, $value)
	{
		$this->$property = $value;
	}

	/**
	 * Calls any inaccessible method from the class.
	 * 
	 * @param   string  $name        Name of the method to invoke 
	 * @param   array   $parameters  Parameters to be handed over to the original method
	 * 
	 * @return mixed The return value of the method 
	 */
	public function __call($name, $parameters = false)
	{
		return call_user_func_array(array($this,$name), $parameters);
	}
}

class TestController extends JController
{
	public function task1() {}

	public function task2() {}

	protected function task3() {}

	private function _task4() {}

}

class TestTestController extends TestController
{
	public function task5() {}
}