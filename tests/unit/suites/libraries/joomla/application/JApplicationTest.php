<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JApplication.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       12.2
 */
class JApplicationTest extends TestCase
{
	/**
	 * Object under test
	 *
	 * @var  JApplication
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->object = new JApplication(array('session' => false));
		parent::setUp();
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->object = null;
		parent::tearDown();
	}

	/**
	 * Test JApplication::getInstance
	 *
	 * @todo    Implement testGetInstance().
	 *
	 * @return  void
	 */
	public function testGetInstance()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::initialise
	 *
	 * @todo    Implement testInitialise().
	 *
	 * @return  void
	 */
	public function testInitialise()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::__construct
	 *
	 * @return  void
	 */
	public function testConstruct()
	{
		$this->assertThat(
			$this->object->input,
			$this->isInstanceOf('JInput'),
			__LINE__ . 'JApplication->input not initialized properly'
		);

		$this->assertInstanceOf(
			'JApplicationWebClient',
			$this->object->client,
			'Client property wrong type'
		);
	}

	/**
	 * Test JApplication::route
	 *
	 * @todo    Implement testRoute().
	 *
	 * @return  void
	 */
	public function testRoute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::dispatch
	 *
	 * @todo    Implement testDispatch().
	 *
	 * @return  void
	 */
	public function testDispatch()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::render
	 *
	 * @todo    Implement testRender().
	 *
	 * @return  void
	 */
	public function testRender()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::close
	 *
	 * @todo    Implement testClose().
	 *
	 * @return  void
	 */
	public function testClose()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::redirect
	 *
	 * @todo    Implement testRedirect().
	 *
	 * @return  void
	 */
	public function testRedirect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::enqueueMessage
	 *
	 * @todo    Implement testEnqueueMessage().
	 *
	 * @return  void
	 */
	public function testEnqueueMessage()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getMessageQueue
	 *
	 * @todo    Implement testGetMessageQueue().
	 *
	 * @return  void
	 */
	public function testGetMessageQueue()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getCfg
	 *
	 * @todo    Implement testGetCfg().
	 *
	 * @return  void
	 */
	public function testGetCfg()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getName
	 *
	 * @todo    Implement testGetName().
	 *
	 * @return  void
	 */
	public function testGetName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getUserState
	 *
	 * @todo    Implement testGetUserState().
	 *
	 * @return  void
	 */
	public function testGetUserState()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::setUserState
	 *
	 * @todo    Implement testSetUserState().
	 *
	 * @return  void
	 */
	public function testSetUserState()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getUserStateFromRequest
	 *
	 * @todo    Implement testGetUserStateFromRequest().
	 *
	 * @return  void
	 */
	public function testGetUserStateFromRequest()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::registerEvent
	 *
	 * @todo    Implement testRegisterEvent().
	 *
	 * @return  void
	 */
	public function testRegisterEvent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::triggerEvent
	 *
	 * @todo    Implement testTriggerEvent().
	 *
	 * @return  void
	 */
	public function testTriggerEvent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::login
	 *
	 * @todo    Implement testLogin().
	 *
	 * @return  void
	 */
	public function testLogin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::logout
	 *
	 * @todo    Implement testLogout().
	 *
	 * @return  void
	 */
	public function testLogout()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getTemplate
	 *
	 * @todo    Implement testGetTemplate().
	 *
	 * @return  void
	 */
	public function testGetTemplate()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getRouter
	 *
	 * @todo    Implement testGetRouter().
	 *
	 * @return  void
	 */
	public function testGetRouter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::stringUrlSafe
	 *
	 * @todo    Implement testStringURLSafe().
	 *
	 * @return  void
	 */
	public function testStringURLSafe()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getPathway
	 *
	 * @todo    Implement testGetPathway().
	 *
	 * @return  void
	 */
	public function testGetPathway()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getMenu
	 *
	 * @todo    Implement testGetMenu().
	 *
	 * @return  void
	 */
	public function testGetMenu()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Testing JApplication::getHash
	 *
	 * @return  void
	 */
	public function testGetHash()
	{
		// Temporarily override the config cache in JFactory.
		$temp = JFactory::$config;
		JFactory::$config = new JObject(array('secret' => 'foo'));

		$this->assertThat(
			JApplication::getHash('This is a test'),
			$this->equalTo(md5('foo' . 'This is a test')),
			'Tests that the secret string is added to the hash.'
		);

		JFactory::$config = $temp;
	}

	/**
	 * Test JApplication::checkSession
	 *
	 * @todo    Implement testCheckSession().
	 *
	 * @return  void
	 */
	public function testCheckSession()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::getClientId
	 *
	 * @todo    Implement testGetClientId().
	 *
	 * @return  void
	 */
	public function testGetClientId()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::isAdmin
	 *
	 * @todo    Implement testIsAdmin().
	 *
	 * @return  void
	 */
	public function testIsAdmin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::isSite
	 *
	 * @todo    Implement testIsSite().
	 *
	 * @return  void
	 */
	public function testIsSite()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::isWinOs
	 *
	 * @todo    Implement testIsWinOS().
	 *
	 * @return  void
	 */
	public function testIsWinOS()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JApplication::isSSLConnection
	 *
	 * @return  void
	 */
	public function testIsSSLConnection()
	{
		unset($_SERVER['HTTPS']);

		$this->assertThat(
			$this->object->isSSLConnection(),
			$this->equalTo(false)
		);

		$_SERVER['HTTPS'] = 'on';

		$this->assertThat(
			$this->object->isSSLConnection(),
			$this->equalTo(true)
		);
	}
}
