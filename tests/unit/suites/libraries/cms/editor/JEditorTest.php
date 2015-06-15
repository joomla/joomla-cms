<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JEditor.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Editor
 * @since       3.0
 */
class JEditorTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Object under test
	 *
	 * @var    JEditor
	 * @since  3.0
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function setUp()
	{
		$this->object = new JEditor;
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
}
