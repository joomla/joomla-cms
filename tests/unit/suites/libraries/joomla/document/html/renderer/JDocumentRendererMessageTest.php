<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once JPATH_PLATFORM . '/joomla/document/html/renderer/message.php';

/**
 * Test class for JDocumentRendererMessage
 */
class JDocumentRendererMessageTest extends TestCaseDatabase
{
	/**
	 * The instance of the object to test.
	 *
	 * @var  JDocumentRendererMessage
	 */
	private $instance;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();
		JFactory::$document = $this->getMockDocument();
		JFactory::$session = $this->getMockSession();

		$this->instance = new JDocumentRendererMessage(JFactory::getDocument());
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * @testdox  Test the default return for render
	 */
	public function testTheDefaultReturnForRender()
	{
		$this->assertContains('<div id="system-message-container"', $this->instance->render('unused'));
	}
}
