<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/messagequeue/mock.application.php';

/**
 * Test class for JLogLoggerMessageQueue.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JLogLoggerMessageQueueTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var   mixed  The main application object from JFactory while we mock it out.
	 * @since 11.1
	 */
	protected $app;

	/**
	 * Setup for testing.
	 *
	 * @return void
	 */
	public function setUp()
	{
		parent::setUp();

		$this->app = JFactory::$application;
		JFactory::$application = new JApplicationMock;
	}

	/**
	 * Tear down.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		JFactory::$application = $this->app;
	}

	/**
	 * Test the JLogLoggerMessageQueue::addEntry method.
	 *
	 * @covers  JLogLoggerMessageQueue::addEntry
	 *
	 * @return void
	 */
	public function testAddEntry01()
	{
		// Create bogus config.
		$config = array();

		// Get an instance of the logger.
		$logger = new JLogLoggerMessagequeue($config);

		// Add a basic error message, it ignores the category.
		$logger->addEntry(new JLogEntry('TESTING', JLog::ERROR, 'DePrEcAtEd'));
		$expected = array(
			array('message' => 'TESTING', 'type' => 'error')
		);
		$this->assertEquals(JFactory::$application->queue, $expected, 'Line: ' . __LINE__);

		// Now lets add a debug message that should be ignored.
		$logger->addEntry(new JLogEntry('Debugging', JLog::DEBUG));
		$expected = array(
			array('message' => 'TESTING', 'type' => 'error')
		);
		$this->assertEquals(JFactory::$application->queue, $expected, 'Line: ' . __LINE__);

		// Next we add a regular info message.
		$logger->addEntry(new JLogEntry('My information message.', JLog::INFO));
		$expected = array(
			array('message' => 'TESTING', 'type' => 'error'),
			array('message' => 'My information message.', 'type' => 'message')
		);
		$this->assertEquals(JFactory::$application->queue, $expected, 'Line: ' . __LINE__);

		// Who's on notice?
		$logger->addEntry(new JLogEntry('You are on NOTICE!', JLog::NOTICE));
		$expected = array(
			array('message' => 'TESTING', 'type' => 'error'),
			array('message' => 'My information message.', 'type' => 'message'),
			array('message' => 'You are on NOTICE!', 'type' => 'notice'),
		);
		$this->assertEquals(JFactory::$application->queue, $expected, 'Line: ' . __LINE__);

		// One last "warning" and we'll call it a day.
		$logger->addEntry(new JLogEntry('You\'ve been warned...', JLog::WARNING));
		$expected = array(
			array('message' => 'TESTING', 'type' => 'error'),
			array('message' => 'My information message.', 'type' => 'message'),
			array('message' => 'You are on NOTICE!', 'type' => 'notice'),
			array('message' => 'You\'ve been warned...', 'type' => 'warning'),
		);
		$this->assertEquals(JFactory::$application->queue, $expected, 'Line: ' . __LINE__);
	}
}
