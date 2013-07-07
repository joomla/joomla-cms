<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Convert.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1ControllerConvertTest extends TestCase
{
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
			->setMethods(array('doPasswordAuthentication', 'getMessage', 'setHeader', 'setBody'))
			->getMock();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();

		$message = $this->getMockBuilder('JOAuth1Message')
			->setMethods(array('isValid'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerConvert')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$app->expects($this->once())
			->method('getMessage')
			->will($this->returnValue($message));

		$message->signature = 'signature';
		$message->consumerKey = 'consumerKey';
		$message->callback = 'callback';

		$credentials->expects($this->once())
			->method('convert');

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
			->with('oauth_token=credentialKey&oauth_token_secret=credentialSecret');

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
			->setMethods(array('doPasswordAuthentication', 'getMessage', 'setHeader', 'setBody'))
			->getMock();

		$input = new JInput;

		$controller = $this->getMockBuilder('JOAuth1ControllerConvert')
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
			->setMethods(array('doPasswordAuthentication', 'getMessage', 'sendInvalidAuthMessage'))
			->getMock();
		$input = new JInput;

		$message = $this->getMockBuilder('JOAuth1Message')
			->setMethods(array('isValid'))
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerConvert')
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
