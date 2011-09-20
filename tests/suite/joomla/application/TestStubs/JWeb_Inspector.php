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
	public function getClassProperty($name)
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
	 * Method for setting protected static $instance.
	 *
	 * @param   mixed  $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassInstance($value)
	{
		self::$instance = $value;
	}

	/**
	 * Method for setting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void.
	 *
	 * @since   11.3
	 */
	public function setClassProperty($name, $value)
	{
		if (property_exists($this, $name))
		{
			$this->$name = $value;
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
	public function detectRequestUri()
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
	public function doExecute()
	{
		$this->triggerEvent('JWebDoExecute');
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $fileName  The name of the configuration file (default is 'configuration').
	 *
	 * @return  mixed  Either an array or object to be loaded into the configuration object.
	 *
	 * @since   11.3
	 */
	public function fetchConfigurationData($fileName = null)
	{
		return parent::fetchConfigurationData($fileName);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function loadDispatcher()
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
	public function loadDocument()
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
	public function loadLanguage()
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
	public function loadSession()
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
	public function loadSystemUris()
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
	public function render()
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
	public function respond()
	{
		return parent::respond();
	}
}
