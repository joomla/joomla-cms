<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JUcmBase.
 *
 * @package     Joomla.UnitTest
 * @subpackage  UCM
 * @since       3.2
 */
class JUcmBaseTest extends TestCaseDatabase
{
	/**
	 * @var    JUcmBase
	 * @since  3.2
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockWeb();

		$this->object = new JUcmBase;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the __construct()
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__construct()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getLanguageId() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testStore()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getType() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetType()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the mapBase() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testMapBase()
	{
		$this->markTestSkipped('Test not implemented.');
	}
}
