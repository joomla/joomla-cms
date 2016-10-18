<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Response
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/mock.application.php';

/**
 * Test class for JResponseJson.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JResponseJson
 * @since       3.1
 */
class JResponseJsonTest extends TestCase
{
	/**
	 * Set up for testing
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = new JApplicationResponseJsonMock;
	}

	/**
	 * Tear down test
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests a simple success response where only the JResponseJson
	 * class is instantiated and send
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testSimpleSuccess()
	{
		$output = new JResponseJson;

		$response = json_decode($output);

		$this->assertTrue($response->success);
	}

	/**
	 * Tests a success response with data to send back
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testSuccessWithData()
	{
		$data          = new stdClass;
		$data->value   = 5;
		$data->average = 7.9;

		$output = new JResponseJson($data);

		$response = json_decode($output);

		$this->assertTrue($response->success);
		$this->assertSame(5, $response->data->value);
		$this->assertSame(7.9, $response->data->average);
	}

	/**
	 * Tests a response indicating an error where an exception
	 * is passed into the object in order to set 'success' to false.
	 *
	 * The message of the exception is automatically sent back in 'message'.
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testFailureWithException()
	{
		$output = new JResponseJson(new Exception('This and that went wrong'));

		$response = json_decode($output);

		$this->assertFalse($response->success);
		$this->assertSame('This and that went wrong', $response->message);
	}

	/**
	 * Tests a response indicating an error where the third argument
	 * is used to set 'success' to false and the second to set the message
	 *
	 * This way data can also be send back using the first argument.
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testFailureWithData()
	{
		$data          = new stdClass;
		$data->value   = 6;
		$data->average = 8.9;

		$output = new JResponseJson($data, 'Something went wrong', true);

		$response = json_decode($output);

		$this->assertFalse($response->success);
		$this->assertSame('Something went wrong', $response->message);
		$this->assertSame(6, $response->data->value);
		$this->assertSame(8.9, $response->data->average);
	}

	/**
	 * Tests a response indicating an error where more messages
	 * are sent back besides the main response message of the exception
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testFailureWithMessages()
	{
		JFactory::$application->enqueueMessage('This part was successful');
		JFactory::$application->enqueueMessage('You should not do that', 'warning');

		$output = new JResponseJson(new Exception('A major error occurred'));

		$response = json_decode($output);

		$this->assertFalse($response->success);
		$this->assertSame('A major error occurred', $response->message);
		$this->assertSame('This part was successful', $response->messages->message[0]);
		$this->assertSame('You should not do that', $response->messages->warning[0]);
	}

	/**
	 * Tests a response indicating an error where messages
	 * of the message queue should be ignored
	 *
	 * Note: The third parameter $error will be ignored
	 * if an exception is used for indicating an error
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testFailureWithIgnoreMessages()
	{
		JFactory::$application->enqueueMessage('This part was successful');
		JFactory::$application->enqueueMessage('You should not do that', 'warning');

		$output = new JResponseJson(new Exception('A major error occurred'), null, false, true);

		$response = json_decode($output);

		$this->assertFalse($response->success);
		$this->assertSame('A major error occurred', $response->message);
		$this->assertNull($response->messages);
	}

	/**
	 * Tests a simple success response where only the JResponseJson
	 * class is instantiated and send, but this time with additional messages
	 *
	 * @return void
	 *
	 * @since  3.1
	 */
	public function testSuccessWithMessages()
	{
		JFactory::$application->enqueueMessage('This part was successful');
		JFactory::$application->enqueueMessage('This one was also successful');

		$output = new JResponseJson;

		$response = json_decode($output);

		$this->assertTrue($response->success);
		$this->assertSame('This part was successful', $response->messages->message[0]);
		$this->assertSame('This one was also successful', $response->messages->message[1]);
	}
}
