<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/web.php';

/**
 * Test class for JDaemon.
 */
class JWebTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// Include the inspector.
		include_once JPATH_TESTS.'/suite/joomla/application/TestStubs/JWeb_Inspector.php';

		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

		// Get a new JWebInspector instance.
		$this->inspector = new JWebInspector();

		parent::setUp();
	}

	/**
	 * Tests the JWeb::__construct method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__construct()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::allowCache method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAllowCache()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::appendBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testAppendBody()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::clearHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClearHeaders()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::close method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClose()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::compress method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCompress()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::detectRequestUri method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testDetectRequestUri()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::doExecute method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testDoExecute()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::Execute method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testExecute()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::fetchConfigurationData method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testFetchConfigurationData()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::get method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGet()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::getBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetBody()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::getHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetHeaders()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::getInstance method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGetInstance()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::initialise method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialise()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadConfiguration method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadConfiguration()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadDispatcher method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDispatcher()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadDocument method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadDocument()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadLanguage method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadLanguage()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadSession method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSession()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::loadSystemUris method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLoadSystemUris()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::prependBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testPrependBody()
	{
		$this->markTestIncomplete();
	}
}
