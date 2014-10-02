<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JControllerAdmin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Controller
 *
 * @since       12.3
 */
class JControllerAdminTest extends TestCase
{
	/**
	 * @var JControllerAdmin
	 */
	protected $object;

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
	 * Test JControllerAdmin::delete
	 *
	 * @todo    Implement testDelete().
	 *
	 * @return  void
	 */
	public function testDelete()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerAdmin::display
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
	 * Test JControllerAdmin::publish
	 *
	 * @todo Implement testPublish().
	 *
	 * @return  void
	 */
	public function testPublish()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerAdmin::reorder
	 *
	 * @todo    Implement testReorder().
	 *
	 * @return  void
	 */
	public function testReorder()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerAdmin::saveorder
	 *
	 * @todo    Implement testSaveorder().
	 *
	 * @return  void
	 */
	public function testSaveorder()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	 * Test JControllerAdmin::checkin
	 *
	 * @todo    Implement testCheckin().
	 *
	 * @return  void
	 */
	public function testCheckin()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
