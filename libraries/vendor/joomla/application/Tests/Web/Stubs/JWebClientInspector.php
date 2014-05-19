<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JWebClientInspector
 *
 * @since  1.0
 */
class JWebClientInspector extends Joomla\Application\Web\WebClient
{
	/**
	 * Allows public access to protected method.
	 *
	 * @return  void
	 *
	 * @since   1.0
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
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function detectBrowser($userAgent)
	{
		return parent::detectBrowser($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function detectEngine($userAgent)
	{
		return parent::detectEngine($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function detectPlatform($userAgent)
	{
		return parent::detectPlatform($userAgent);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $acceptEncoding  The accept encoding string to parse.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function detectEncoding($acceptEncoding)
	{
		return parent::detectEncoding($acceptEncoding);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $acceptLanguage  The accept language string to parse.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function detectLanguage($acceptLanguage)
	{
		return parent::detectLanguage($acceptLanguage);
	}

	/**
	 * Allows public access to protected method.
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function detectRobot($userAgent)
	{
		return parent::detectRobot($userAgent);
	}

	/**
	 * Method for inspecting protected variables.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed  The value of the class variable.
	 *
	 * @since   1.0
	 */
	public function getProperty($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}
		else
		{
			throw new Exception('Undefined or private property: ' . __CLASS__ . '::' . $name);
		}
	}

	/**
	 * loadClientInformation()
	 *
	 * @param   string  $userAgent  The user-agent string to parse.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadClientInformation($userAgent = null)
	{
		return parent::loadClientInformation($userAgent);
	}

	/**
	 * fetchConfigurationData()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function fetchConfigurationData()
	{
		return parent::fetchConfigurationData();
	}

	/**
	 * loadSystemURIs()
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function loadSystemURIs()
	{
		return parent::loadSystemURIs();
	}

	/**
	 * loadSystemURIs()
	 *
	 * @param   string  $ua  The user-agent string to parse.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function testHelperClient($ua)
	{
		$_SERVER['HTTP_USER_AGENT'] = $ua;

		$this->detectClientInformation();

		return $this->config->get('client');
	}
}
