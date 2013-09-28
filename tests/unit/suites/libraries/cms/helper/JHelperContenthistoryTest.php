<?php
/**
 * @package	    Joomla.UnitTest
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license	    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperContenthistory.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 * @since       3.2
 */
class JHelperContenthistoryTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var    JHelperContenthistory
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

		$this->object = new JHelperContenthistory;
	}

	/**
	 * Tests the deleteHistory() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function testDeleteHistory()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the getHistory method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function getHistory()
	{
		$this->markTestSkipped('Test not implemented.');
	}

	/**
	 * Tests the store() method
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function store()
	{
		$this->markTestSkipped('Test not implemented.');
	}
}
