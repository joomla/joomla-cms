<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1ApplicationWeb.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1ApplicationWebTest extends TestCase
{
	/**
	 * Tests the getMessage method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetMessage()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('loadIdentity'))
			->getMockForAbstractClass();

		$message = $this->getMockBuilder('JOAuth1Message')
			->disableOriginalConstructor()
			->getMock();

		TestReflection::setValue($app, 'message', $message);

		$this->assertSame($message, $app->getMessage());
	}

	/**
	 * Tests loadIdentity getting the id from the OAuth authentication method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadIdentityOAuth()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doOAuthAuthentication', 'doBasicAuthentication'))
			->getMockForAbstractClass();

		$app->expects($this->once())
			->method('doOAuthAuthentication')
			->will($this->returnValue(54321));

		$users = @TestReflection::getValue('JUser', 'instances');
		$user = $this->getMockBuilder('JUser')
			->disableOriginalConstructor();

		$users[54321] = $user;
		TestReflection::setValue('JUser', 'instances', $users);

		$app->loadIdentity();

		$this->assertSame($user, $app->getIdentity());
	}

	/**
	 * Tests loadIdentity getting the id from the basic authentication method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testLoadIdentityBasic()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doOAuthAuthentication', 'doBasicAuthentication'))
			->getMockForAbstractClass();

		$app->expects($this->once())
			->method('doOAuthAuthentication')
			->will($this->returnValue(0));

		$app->expects($this->once())
			->method('doBasicAuthentication')
			->will($this->returnValue(54321));

		$users = TestReflection::getValue('JUser', 'instances');
		$user = $this->getMockBuilder('JUser')
			->disableOriginalConstructor();

		$users[54321] = $user;
		TestReflection::setValue('JUser', 'instances', $users);

		$app->loadIdentity();

		$this->assertSame($user, $app->getIdentity());
	}

	/**
	 * Tests the sendInvalidAuthMessage method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSendInvalidAuthMessage()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('setBody', 'triggerEvent', 'respond', 'close', 'setHeader'))
			->getMockForAbstractClass();

		$app->expects($this->at(0))
			->method('setHeader')
			->with('WWW-Authenticate', 'OAuth');

		$app->expects($this->at(1))
			->method('setHeader')
			->with('Status', '401 Unauthorized');

		$app->expects($this->at(2))
			->method('setBody')
			->with('message');

		$app->expects($this->at(3))
			->method('triggerEvent')
			->with('onBeforeRespond');

		$app->expects($this->at(4))
			->method('respond');

		$app->expects($this->at(5))
			->method('triggerEvent')
			->with('onAfterRespond');

		$app->expects($this->at(6))
			->method('close');

		$app->sendInvalidAuthMessage('message', 'OAuth');
	}
}
