<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once __DIR__ . '/stubs/controllerform.php';

/**
 * Test class for JControllerForm.
 *
 * @since  11.1
 */
class JControllerFormTest extends TestCase
{
	/**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
	protected function setUp()
	{
		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$config = $this->getMockConfig();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
	}

	/**
	 * Tests the JControllerForm constructor.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @covers  JControllerForm::__construct
	 */
	public function testConstructor()
	{
		//
		// Test the auto-naming of the _option, _context, _view_item and _view_list
		//
		$object = new MincesControllerMince(
			array(
			// Neutralise a JPATH_COMPONENT not defined error.
			'base_path' => JPATH_BASE . '/component/com_foobar'));

		$this->assertAttributeEquals('com_minces', 'option', $object, 'Checks the _option variable was created properly.');

		$this->assertAttributeEquals('mince', 'context', $object, 'Check the _context variable was created properly.');

		$this->assertAttributeEquals('mince', 'view_item', $object, 'Check the _view_item variable was created properly.');

		$this->assertAttributeEquals('minces', 'view_list', $object, 'Check the _view_list variable was created properly.');

		//
		// Test for correct pluralisation.
		//

		$object = new MiniesControllerMiny(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path' => JPATH_BASE . '/component/com_foobar'
			)
		);

		$this->assertAttributeEquals('minies', 'view_list', $object, 'Check the _view_list variable was created properly');

		$object = new MintsControllerMint(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path' => JPATH_BASE . '/component/com_foobar'
			)
		);

		$this->assertAttributeEquals('mints', 'view_list', $object, 'Check the _view_list variable was created properly');
	}

	/**
	 * @todo Implement testGetModel().
	 */
	public function testGetModel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testDisplay().
	 */
	public function testDisplay()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testAdd().
	 */
	public function testAdd()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testEdit().
	 */
	public function testEdit()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testCancel().
	 */
	public function testCancel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * @todo Implement testSave().
	 */
	public function testSave()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
