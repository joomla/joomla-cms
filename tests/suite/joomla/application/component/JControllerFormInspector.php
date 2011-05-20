<?php
/**
 * @version		$Id: JControllerHelper.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * General inspector class for JControllerForm.
 *
 * @package		Joomla.UnitTest
 * @subpackage	Application.Component
 * @since       11.1
 */
class JControllerFormInspector extends JControllerForm
{
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

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application.Component
 * @since       11.1
 */
class MincesControllerMince extends JControllerFormInspector
{
}

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application.Component
 * @since       11.1
 */
class MiniesControllerMiny extends JControllerFormInspector
{
}

/**
 * @package		Joomla.UnitTest
 * @subpackage	Application.Component
 * @since       11.1
 */
class MintsControllerMint extends JControllerFormInspector
{
}