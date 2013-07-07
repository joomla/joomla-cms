<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JOAuth1Client.
 *
 * @package     Joomla.UnitTest
 * @subpackage  JOAuth1
 *
 * @since       12.3
 */
class JOAuth1ControllerAuthoriseTest extends TestCase
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
			->setMethods(array('getIdentity', 'setBody', 'doPasswordAuthentication'))
			->getMock();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();
		$identity = $this->getMockBuilder('JUser')
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerAuthorise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$input->get->set('oauth_token', 'OAUTH_TOKEN');

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$credentials->expects($this->once())
			->method('load')
			->with('OAUTH_TOKEN');

		$credentials->expects($this->once())
			->method('getType')
			->will($this->returnValue(JOAuth1Credentials::TEMPORARY));

		$app->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($identity));

		$identity->expects($this->at(0))
			->method('get')
			->with('guest')
			->will($this->returnValue(false));

		$identity->expects($this->at(1))
			->method('get')
			->with('id')
			->will($this->returnValue(10));

		$credentials->expects($this->once())
			->method('authorise')
			->with(10);

		$credentials->expects($this->any())
			->method('getCallbackUrl')
			->will($this->returnValue('oob'));

		$credentials->expects($this->any())
			->method('getVerifierKey')
			->will($this->returnValue('Verifier Key'));

		$app->expects($this->once())
			->method('setBody')
			->with('{"status":"Credentials authorised","oauth_token":null,"oauth_verifier":"Verifier Key"}');

		$controller->execute();
	}

	/**
	 * Tests the execute method for successful execution.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExecuteCallback()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('getIdentity', 'redirect', 'doPasswordAuthentication'))
			->getMock();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();
		$identity = $this->getMockBuilder('JUser')
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerAuthorise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$input->get->set('oauth_token', 'OAUTH_TOKEN');

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$credentials->expects($this->once())
			->method('load')
			->with('OAUTH_TOKEN');

		$credentials->expects($this->once())
			->method('getType')
			->will($this->returnValue(JOAuth1Credentials::TEMPORARY));

		$app->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($identity));

		$identity->expects($this->at(0))
			->method('get')
			->with('guest')
			->will($this->returnValue(false));

		$identity->expects($this->at(1))
			->method('get')
			->with('id')
			->will($this->returnValue(10));

		$credentials->expects($this->once())
			->method('authorise')
			->with(10);

		$credentials->expects($this->any())
			->method('getCallbackUrl')
			->will($this->returnValue('http://example.com/callback'));

		$app->expects($this->once())
			->method('redirect')
			->with('http://example.com/callback');

		$controller->execute();
	}

	/**
	 * Tests the execute method when user isn't signed in.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExecuteNotSignedIn()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doPasswordAuthentication', 'getIdentity', 'setHeader', 'setBody'))
			->getMockForAbstractClass();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();
		$identity = $this->getMockBuilder('JUser')
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerAuthorise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$input->get->set('oauth_token', 'OAUTH_TOKEN');

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$credentials->expects($this->once())
			->method('load')
			->with('OAUTH_TOKEN');

		$credentials->expects($this->once())
			->method('getType')
			->will($this->returnValue(JOAuth1Credentials::TEMPORARY));

		$app->expects($this->any())
			->method('getIdentity')
			->will($this->returnValue($identity));

		$identity->expects($this->at(0))
			->method('get')
			->with('guest')
			->will($this->returnValue(true));

		$app->expects($this->once())
			->method('setHeader')
			->with('status', '400');

		$app->expects($this->once())
			->method('setBody')
			->with('You must first sign in.');

		$controller->execute();
	}

	/**
	 * Tests the execute method for credentials that are not token credentials (i.e. cannot be revoked).
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExecuteNotTemporary()
	{
		$app = $this->getMockBuilder('JOAuth1ApplicationWeb')
			->disableOriginalConstructor()
			->setMethods(array('doPasswordAuthentication', 'getIdentity', 'setHeader', 'setBody'))
			->getMock();
		$input = new JInput;
		$credentials = $this->getMockBuilder('JOAuth1Credentials')
			->disableOriginalConstructor()
			->getMock();
		$identity = $this->getMockBuilder('JIdentity')
			->disableOriginalConstructor()
			->getMock();

		$controller = $this->getMockBuilder('JOAuth1ControllerAuthorise')
			->setConstructorArgs(array($input, $app))
			->setMethods(array('createCredentials'))
			->getMock();

		$input->get->set('oauth_token', 'OAUTH_TOKEN');

		$controller->expects($this->once())
			->method('createCredentials')
			->will($this->returnValue($credentials));

		$credentials->expects($this->once())
			->method('load')
			->with('OAUTH_TOKEN');

		$credentials->expects($this->once())
			->method('getType')
			->will($this->returnValue(JOAuth1Credentials::TOKEN));

		$app->expects($this->once())
			->method('setHeader')
			->with('status', '400');

		$app->expects($this->once())
			->method('setBody')
			->with('The token is not for a temporary credentials set.');

		$controller->execute();
	}
}
