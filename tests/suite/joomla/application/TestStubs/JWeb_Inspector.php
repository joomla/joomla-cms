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
	 * Allows public access to protected method.
	 *
	 * @return  JWeb
	 *
	 * @since   11.3
	 */
	public function __construct()
	{
		return parent::__construct();
	}

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
	 * Allows public access to protected method.
	 *
	 * @return  string
	 *
	 * @since   11.3
	 */
	public function detectRequestURI()
	{
		return parent::detectRequestURI();
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  string
	 *
	 * @since   11.3
	 */
	public function detectClientPlatform($userAgent)
	{
		return parent::detectClientPlatform($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  string
	 *
	 * @since   11.3
	 */
	public function detectClientEngine($userAgent)
	{
		return parent::detectClientEngine($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function detectClientBrowser($userAgent)
	{
		return parent::detectClientBrowser($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function loadClientInformation($userAgent = null)
	{
		return parent::loadClientInformation($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @return  mixed
	 *
	 * @since   11.3
	 */
	public function fetchConfigurationData()
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
	public function loadSystemURIs()
	{
		return parent::loadSystemURIs();
	}

	/**
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testHelperClient($ua)
	{
		$_SERVER['HTTP_USER_AGENT'] = $ua;

		$this->detectClientInformation();

		return $this->config->get('client');
	}
}
