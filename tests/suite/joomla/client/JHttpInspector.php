<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Client
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JHttp class to expose protected properties and methods.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Client
 * @since       11.3
 */
class JHttpInspector extends JHttp
{
	/**
	 * Method for inspecting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   11.3
	 */
	public function __get($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			trigger_error('Undefined or private property: ' . __CLASS__.'::'.$name, E_USER_ERROR);

			return null;
		}
	}

	/**
	* Method to connect to a server and get the resource.
	*
	* @param   JUri  $uri  The URI to connect with.
	*
	* @return  mixed  Connection resource on success or boolean false on failure.
	*
	* @since   11.3
	*/
	public function connect(JUri $uri)
	{
		return parent::connect($uri);
	}
}