<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/EditorObserver.php';

/**
 * Test class for JEditor.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Editor
 * @since       3.0
 */
class JEditorTest extends \PHPUnit\Framework\TestCase
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
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->object);
		parent::tearDown();
	}

	/**
	 * Tests the getInstance method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @covers JEditor::getInstance
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JEditor',
			JEditor::getInstance('none')
		);
	}
}
