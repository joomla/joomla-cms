<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JDaemon class.
 *
 * @package		Joomla.UnitTest
 * @subpackage  Application
 *
 * @since       11.1
 */
class JDaemonInspector extends JDaemon
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
	 * @return  boolean  True if identity successfully changed
	 *
	 * @since   11.3
	 */
	public function changeIdentity()
	{
		return parent::changeIdentity();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function gc()
	{
		return parent::gc();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function daemonize()
	{
		return parent::daemonize();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function setupSignalHandlers()
	{
		return parent::setupSignalHandlers();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function fork()
	{
		return parent::fork();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public function writeProcessIdFile()
	{
		return parent::writeProcessIdFile();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   boolean  $restart  True to restart the daemon on exit.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function shutdown($restart = false)
	{
		return parent::shutdown($restart);
	}
}
