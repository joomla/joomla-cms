<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JWeb library.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage  Application
 */
class JWebInspector extends JWeb
{
	public $config;
	public $response;
	public $session;

	public function __construct()
	{
		return parent::__construct();
	}

	public function detectRequestURI()
	{
		return parent::detectRequestURI();
	}

	public function detectClientPlatform($userAgent)
	{
		return parent::detectClientPlatform($userAgent);
	}

	public function detectClientEngine($userAgent)
	{
		return parent::detectClientEngine($userAgent);
	}

	public function detectClientBrowser($userAgent)
	{
		return parent::detectClientBrowser($userAgent);
	}

	public function loadClientInformation($userAgent = null)
	{
		return parent::loadClientInformation($userAgent);
	}

	public function fetchConfigurationData()
	{
		return parent::fetchConfigurationData();
	}

	public function loadSystemURIs()
	{
		return parent::loadSystemURIs();
	}

	public function testHelperClient($ua)
	{
		$_SERVER['HTTP_USER_AGENT'] = $ua;

		$this->detectClientInformation();

		return $this->config->get('client');
	}
}
