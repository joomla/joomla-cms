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
 * @package     Joomla.UnitTest
 * @subpackage  Component
 *
 * @since       12.3
 */
class JControllerFormTest extends TestCase
{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		$this->saveFactoryState();

		JFactory::$application = $this->getMockApplication();
		JFactory::$config = $this->getMockConfig();

		parent::setUp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();
		parent::tearDown();
	}

	/**
	 * Tests the JControllerForm constructor.
	 *
	 * @since   11.1
	 *
	 * @return  void
	 */
	public function testConstructor()
	{
		// Test the auto-naming of the _option, _context, _view_item and _view_list
		$object = new MincesControllerMince(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path' => JPATH_BASE . '/component/com_foobar'
			)
		);

		$this->assertAttributeEquals('com_minces', 'option', $object, 'Checks the _option variable was created properly.');

		$this->assertAttributeEquals('mince', 'context', $object, 'Check the _context variable was created properly.');

		$this->assertAttributeEquals('mince', 'view_item', $object, 'Check the _view_item variable was created properly.');

		$this->assertAttributeEquals('minces', 'view_list', $object, 'Check the _view_list variable was created properly.');

		// Test for correct pluralisation.
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
	 * Test JControllerForm::getModel
	 *
	 * @todo    Implement testGetModel().
	 *
	 * @return  void
	 */
	public function testGetModel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerForm::display
	 *
	 * @todo    Implement testDisplay().
	 *
	 * @return  void
	 */
	public function testDisplay()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerForm::add
	 *
	 * @todo    Implement testAdd().
	 *
	 * @return  void
	 */
	public function testAdd()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerForm::edit
	 *
	 * @todo    Implement testEdit().
	 *
	 * @return  void
	 */
	public function testEdit()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerForm::cancel
	 *
	 * @todo    Implement testCancel().
	 *
	 * @return  void
	 */
	public function testCancel()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerForm::save
	 *
	 * @todo    Implement testSave().
	 *
	 * @return  void
	 */
	public function testSave()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
