<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 * @since       3.2
 */
class JHelperTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JHelper
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

		$this->object = new JHelper;
	}

	/**
	 * Tests the getCurrentLanguage()
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetCurrentLanguage()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getRowData() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testGetRowData()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getDataObject() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDataObject()
	{
		$this->markTestSkipped('Test not implemented.');
	}
}
