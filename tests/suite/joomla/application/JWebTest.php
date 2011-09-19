<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/application/web.php';
include_once __DIR__.'/TestStubs/JWeb_Inspector.php';

/**
 * Test class for JWeb.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Application
 * @since       11.3
 */
class JWebTest extends JoomlaTestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function setUp()
	{
		parent::setUp();

		// Setup the system logger to echo all.
		JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

		$_SERVER['HTTP_HOST'] = 'mydomain.com';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0';

		// Get a new JWebInspector instance.
		$this->inspector = new JWebInspector;

		// We are only coupled to Document and Language in JFactory.
		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
		JFactory::$language = $this->getMockLanguage();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   11.1
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
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
	 * Tests the JWeb::__construct method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__constructDependancyInjection()
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
		$this->assertThat(
			$this->inspector->allowCache(),
			$this->isFalse(),
			'Return value of allowCache should be false by default.'
		);

		$this->assertThat(
			$this->inspector->allowCache(true),
			$this->isTrue(),
			'Return value of allowCache should return the new state.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('response')->cachable,
			$this->isTrue(),
			'Checks the internal cache property has been set.'
		);
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
		// Similulate a previous call to setBody or appendBody.
		$this->inspector->getClassProperty('response')->body = array('foo');

		$this->inspector->appendBody('bar');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo', 'bar')
			),
			'Checks the body array has been appended.'
		);

		$this->inspector->appendBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo', 'bar', 'Array')
			),
			'Checks that non-strings are converted to strings.'
		);
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
	 * Tests the JWeb::initialise method with default settings.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithDefaults()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::initialise method with false injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithFalse()
	{
		$this->inspector->initialise(false, false, false);

		$this->assertThat(
			$this->inspector->getClassProperty('session'),
			$this->equalTo(null),
			'Test that no session is defined.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('document'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);

		$this->assertThat(
			$this->inspector->getClassProperty('language'),
			$this->equalTo(null),
			'Test that no document is defined.'
		);
	}

	/**
	 * Tests the JWeb::initialise method with dependancy injection.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInitialiseWithInjection()
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
		// Similulate a previous call to a body method.
		$this->inspector->getClassProperty('response')->body = array('foo');

		$this->inspector->prependBody('bar');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('bar', 'foo')
			),
			'Checks the body array has been prepended.'
		);

		$this->inspector->prependBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('Array', 'bar', 'foo')
			),
			'Checks that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the JWeb::redirect method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRedirect()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::registerEvent method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRegisterEvent()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::render method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRender()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::respond method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRespond()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::sendHeaders method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSendHeaders()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::set method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSet()
	{
		$this->markTestIncomplete();
	}

	/**
	 * Tests the JWeb::setBody method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSetBody()
	{
		$this->inspector->setBody('foo');

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('foo')
			),
			'Checks the body array has been reset.'
		);

		$this->inspector->setBody(array('goo'));

		$this->assertThat(
			$this->inspector->getClassProperty('response')->body,
			$this->equalTo(
				array('Array')
			),
			'Checks reset and that non-strings are converted to strings.'
		);
	}

	/**
	 * Tests the JWeb::triggerEvents method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testTriggerEvents()
	{
		$this->markTestIncomplete();
	}
}
