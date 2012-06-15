<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/log/logger/callback.php';
require_once __DIR__ . '/stubs/callback/inspector.php';
require_once __DIR__ . '/stubs/callback/helper.php';

/**
 * Test class for JLoggerCallback.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       12.2
 */
class JLoggerCallbackTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor01()
	{
		// Create a callback function
		$callback = create_function('$entry', 'return;');

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor02()
	{
		// Create a callback function (since php 5.3)
		$callback = function($entry) {
			return;
		};

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor03()
	{
		// Use a defined function
		$callback = 'jLoggerCallbackTestHelperFunction';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor04()
	{
		// Use a defined static method
		$callback = array('JLoggerCallbackTestHelper', 'callback01');

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor05()
	{
		// Use a defined static method (since php 5.2.3)
		$callback = 'JLoggerCallbackTestHelper::callback01';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::__construct method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testConstructor06()
	{
		// Use a defined object method
		$obj = new JLoggerCallbackTestHelper;
		$callback = array($obj, 'callback02');

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertTrue(is_callable($logger->callback), 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLoggerCallback::addEntry method.
	 *
	 * @return  null
	 * 
	 * @since   12.2
	 */
	public function testAddEntry()
	{
		// Use a defined static method (since php 5.2.3)
		$callback = 'JLoggerCallbackTestHelper::callback01';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLoggerCallbackInspector($config);
		$entry = new JLogEntry('Testing Entry');

		$logger->addEntry($entry);
		$this->assertEquals(JLoggerCallbackTestHelper::$lastEntry, $entry, 'Line: ' . __LINE__);
	}
}
