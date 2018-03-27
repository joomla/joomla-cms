<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/stubs/callback/inspector.php';
require_once __DIR__ . '/stubs/callback/helper.php';

/**
 * Test class for JLogLoggerCallback.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       12.2
 */
class JLogLoggerCallbackTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor01()
	{
		// Create a callback function
		$callback = function ($entry)
		{
		};

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor02()
	{
		// Create a callback function (since php 5.3)
		$callback = function ($entry)
		{
		};

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor03()
	{
		// Use a defined function
		$callback = 'jLogLoggerCallbackTestHelperFunction';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor04()
	{
		// Use a defined static method
		$callback = array('JLogLoggerCallbackTestHelper', 'callback01');

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor05()
	{
		// Use a defined static method (since php 5.2.3)
		$callback = 'JLogLoggerCallbackTestHelper::callback01';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testConstructor06()
	{
		// Use a defined object method
		$obj = new JLogLoggerCallbackTestHelper;
		$callback = array($obj, 'callback02');

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);

		// Callback was set.
		$this->assertEquals($logger->callback, $callback, 'Line: ' . __LINE__);

		// Callback is callable
		$this->assertInternalType('callable', $logger->callback, 'Line: ' . __LINE__);
	}

	/**
	 * Test the JLogLoggerCallback::__construct method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 * @expectedException  RuntimeException
	 */
	public function testConstructorForException()
	{
		$options = array();

		new JLogLoggerCallback($options);
	}

	/**
	 * Test the JLogLoggerCallback::addEntry method.
	 *
	 * @return  null
	 *
	 * @since   12.2
	 */
	public function testAddEntry()
	{
		// Use a defined static method (since php 5.2.3)
		$callback = 'JLogLoggerCallbackTestHelper::callback01';

		// Setup the basic configuration.
		$config = array(
			'callback' => $callback
		);
		$logger = new JLogLoggerCallbackInspector($config);
		$entry = new JLogEntry('Testing Entry');

		$logger->addEntry($entry);
		$this->assertEquals(JLogLoggerCallbackTestHelper::$lastEntry, $entry, 'Line: ' . __LINE__);
	}
}
