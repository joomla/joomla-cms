<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * @covers JEditor::getInstance
	 */
	public function testGetInstance()
	{
		$this->assertInstanceOf(
			'JEditor',
			JEditor::getInstance('none')
		);
	}

	/**
	 * Tests the getState method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @covers JEditor::getState
	 */
	public function testGetState()
	{
		// Preload the state to test it
		TestReflection::setValue($this->object, '_state', 'JEditor::getState()');

		$this->assertEquals(
			'JEditor::getState()',
			$this->object->getState()
		);
	}

	/**
	 * @testdox Test attaching a single closure as an observer in the JEditor class
	 *
	 * @since  3.4.4
	 */
	public function testAttachWithClosure()
	{
		$testObserver = array(
			'event' => 'onInit',
			'handler' => function () {
				return 'teststring';
			}
		);
		$this->object->attach($testObserver);

		$this->assertAttributeSame(
			array($testObserver),
			'_observers',
			$this->object,
			'Observer was not attached to the editor'
		);

		$this->assertAttributeSame(
			array(
				'oninit' => array(
					0 => 0
				)
			),
			'_methods',
			$this->object,
			'The method for the test observer was not stored correctly'
		);
	}

	/**
	 * @testdox Test attaching multiple closures as observers in the JEditor class using the same event names
	 *
	 * @since  3.4.4
	 */
	public function testAttachWithMultipleClosuresForSameEvent()
	{
		$testObserver = array(
			'event' => 'onInit',
			'handler' => function () {
				return 'teststring';
			}
		);
		$testObserver2 = array(
			'event' => 'onInit',
			'handler' => function () {
				return 'secondTestString';
			}
		);
		$this->object->attach($testObserver);
		$this->object->attach($testObserver2);

		$this->assertAttributeSame(
			array($testObserver, $testObserver2),
			'_observers',
			$this->object,
			'Observers were not attached to the editor'
		);

		$this->assertAttributeSame(
			array(
				'oninit' => array(
					0 => 0,
					1 => 1,
				)
			),
			'_methods',
			$this->object,
			'The methods for the test observers were not stored correctly'
		);
	}

	/**
	 * @testdox Test attaching multiple closures as observers in the JEditor class with different event names
	 *
	 * @since  3.4.4
	 */
	public function testAttachWithMultipleClosuresForDifferentEvents()
	{
		$testObserver = array(
			'event' => 'onInit',
			'handler' => function () {
				return 'teststring';
			}
		);
		$testObserver2 = array(
			'event' => 'onAfterStuff',
			'handler' => function () {
				return 'secondTestString';
			}
		);
		$this->object->attach($testObserver);
		$this->object->attach($testObserver2);

		$this->assertAttributeSame(
			array($testObserver, $testObserver2),
			'_observers',
			$this->object,
			'Observers were not attached to the editor'
		);

		$this->assertAttributeSame(
			array(
				'oninit' => array(
					0 => 0,
				),
				'onafterstuff' => array(
					0 => 1
				)
			),
			'_methods',
			$this->object,
			'The methods for the test observers were not stored correctly'
		);
	}

	/**
	 * @testdox Test an observer object is correctly stored in the JEditor class
	 *
	 * @since  3.4.4
	 */
	public function testAttachWithClass()
	{
		$testObserver = new EditorObserver;
		$this->object->attach($testObserver);

		$this->assertAttributeSame(
			array($testObserver),
			'_observers',
			$this->object,
			'Observer was not attached to the editor'
		);
	}
}
