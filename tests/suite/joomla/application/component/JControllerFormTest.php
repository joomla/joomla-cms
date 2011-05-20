<?php
/**
 * @version		$Id: JControllerFormTest.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once JPATH_PLATFORM.'/joomla/application/component/controllerform.php';
require_once 'JControllerFormInspector.php';

/**
 * Test class for JControllerForm.
 *
 * @since  11.1
 */
class JControllerFormTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests the JControllerForm constructor.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testConstructor()
	{
		//
		// Test the auto-naming of the _option, _context, _view_item and _view_list
		//
		$object = new MincesControllerMince(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		// Check the _option variable was created properly.
		$this->assertThat(
			$object->option,
			$this->equalTo('com_minces')
		);

		// Check the _context variable was created properly.
		$this->assertThat(
			$object->context,
			$this->equalTo('mince')
		);

		// Check the _view_item variable was created properly.
		$this->assertThat(
			$object->view_item,
			$this->equalTo('mince')
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$object->view_list,
			$this->equalTo('minces')
		);

		//
		// Test for correct pluralisation.
		//

		$object = new MiniesControllerMiny(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$object->view_list,
			$this->equalTo('minies')
		);

		$object = new MintsControllerMint(
			array(
				// Neutralise a JPATH_COMPONENT not defined error.
				'base_path'	=> JPATH_BASE.'/component/com_foobar'
			)
		);

		// Check the _view_list variable was created properly.
		$this->assertThat(
			$object->view_list,
			$this->equalTo('mints')
		);
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