<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Initialise.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1ControllerInitialiseTest extends TestCase
{
	/**
	 * The test object.
	 * @var JOAuth1Initalise
	 */
	protected $object;

	/**
	 * The DB object.
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * The options object.
	 * @var JDatabaseDriver
	 */
	protected $options;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();
	}

	/**
	 * Tests the execute method for successful execution.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExecute()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doPasswordAuthentication', 'getMessage', 'get', 'setHeader', 'setBody'))
			->getMockForAbstractClass();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();

		$message = $this->getMockBuilder('JOAuth1Message')
			->setMethods(array('isValid'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerInitialise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$app->expects($this->once())
			->method('getMessage')
			->will($this->returnValue($message));

		$app->expects($this->once())
			->method('get')
			->with('oauth.tokenlifetime')
			->will($this->returnValue(3600));

		$message->signature = 'signature';
		$message->consumerKey = 'consumerKey';
		$message->callback = 'callback';

		$credentials->expects($this->once())
			->method('initialise')
			->with('consumerKey', 'callback', 3600);

		$credentials->expects($this->once())
			->method('getKey')
			->will($this->returnValue('credentialKey'));

		$credentials->expects($this->once())
			->method('getSecret')
			->will($this->returnValue('credentialSecret'));

		$app->expects($this->once())
			->method('setHeader')
			->with('status', '200');

		$app->expects($this->once())
			->method('setBody')
			->with('oauth_token=credentialKey&oauth_token_secret=credentialSecret&oauth_callback_confirmed=1');

		$controller->execute();
	}

	/**
	 * Tests the execute method for bad app.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 *
	 * @expectedException  LogicException
	 */
	public function testExecuteBadApp()
	{
		$app = $this->getMockBuilder('JApplicationWeb')
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$input = new JInput;

		$controller = $this->getMockBuilder('JOAuth1ControllerInitialise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$controller->execute();
	}

	/**
	 * Tests the execute method with bad signature.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExecuteBadSignature()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doPasswordAuthentication', 'getMessage', 'sendInvalidAuthMessage', 'setHeader', 'setBody'))
			->getMockForAbstractClass();
		$input = new JInput;

		$message = $this->getMockBuilder('JOAuth1Message')
			->setMethods(array('isValid'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerInitialise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$app->expects($this->once())
			->method('getMessage')
			->will($this->returnValue($message));

		$message->signature = '';

		$app->expects($this->once())
			->method('sendInvalidAuthMessage')
			->with('Invalid OAuth request signature.');

		$controller->execute();
	}

}
