<?php
/**
 * @version		$Id: JControllerHelper.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application.Component
 */
class JControllerInspector extends JController
{
	public function addPath($type, $path)
	{
		return parent::addPath($type, $path);
	}

	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method for inspecting protected variables.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   11.1
	 */
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}
		else {
			trigger_error('Undefined or private property: ' . __CLASS__.'::'.$name, E_USER_ERROR);
			return;
		}
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