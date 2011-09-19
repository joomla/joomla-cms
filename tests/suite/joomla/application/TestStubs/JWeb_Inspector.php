<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JWeb class.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Application
 *
 * @since       11.3
 */
class JWebInspector extends JWeb
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
			throw new Exception('Undefined or private property: ' . __CLASS__.'::'.$name);
		}
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function compress()
	{
		return parent::compress();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  string
	 *
	 * @since   11.3
	 */
	protected function detectRequestUri()
	{
		return parent::detectRequestUri();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function doExecute()
	{
		// Do something?
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.3
	 */
	protected function fetchConfigurationData()
	{
		return parent::fetchConfigurationData();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadDispatcher()
	{
		return parent::loadDispatcher();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadDocument()
	{
		return parent::loadDocument();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadLanguage()
	{
		return parent::loadLanguage();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadSession()
	{
		return parent::loadSession();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function loadSystemUris()
	{
		return parent::loadSystemUris();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function render()
	{
		return parent::render();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	protected function respond()
	{
		return parent::respond();
	}
}
