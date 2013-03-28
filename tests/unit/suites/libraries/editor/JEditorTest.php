<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JEditor.
 */
class JEditorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var JEditor
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new JEditor;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * Tests the getInstance method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetInstance()
	{
		$this->assertThat(
			JEditor::getInstance('none'),
			$this->isInstanceOf('JEditor')
		);
	}

	/**
	 * Tests the getState method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function testGetState()
	{
		// Preload the state to test it
		TestReflection::setValue($this->object, '_state', 'JEditor::getState()');

		$this->assertThat(
			$this->object->getState(),
			$this->equalTo('JEditor::getState()')
		);
	}

	/**
	 * @todo   Implement testAttach().
	 */
	public function testAttach()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testDetach().
	 */
	public function testDetach()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testInitialise().
	 */
	public function testInitialise()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testDisplay().
	 */
	public function testDisplay()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testSave().
	 */
	public function testSave()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testGetContent().
	 */
	public function testGetContent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testSetContent().
	 */
	public function testSetContent()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo   Implement testGetButtons().
	 */
	public function testGetButtons()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
