<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelper.
 */
class JUcmTypeTest extends TestCaseDatabase
{
	/**
	 * @var    JUcmType
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

		JFactory::$application = $this->getMockApplication();
		$this->object = new JUcmType;
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
	 * Tests the getTypeByAlias() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTypeByAlias()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getTypeId() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetTypeId()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the __get() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function test__get()
	{
		$this->markTestSkipped('Test not implemented.');
	}

}
