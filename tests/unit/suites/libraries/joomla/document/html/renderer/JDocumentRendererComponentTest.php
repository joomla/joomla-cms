<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once JPATH_PLATFORM . '/joomla/document/html/renderer/component.php';

/**
 * Test class for JDocumentRendererComponent
 */
class JDocumentRendererComponentTest extends TestCase
{
	/**
	 * The instance of the object to test.
	 *
	 * @var  JDocumentRendererComponent
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

		JFactory::$document = $this->getMockDocument();

		$this->instance = new JDocumentRendererComponent(JFactory::getDocument());
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
		$this->assertNull($this->instance->render());
	}
}
