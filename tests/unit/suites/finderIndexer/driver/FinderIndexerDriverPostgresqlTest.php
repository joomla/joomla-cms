<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/driver/postgresql.php';

/**
 * Test class for FinderIndexerDriverPostgresql.
 */
class FinderIndexerDriverPostgresqlTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var FinderIndexerDriverPostgresql
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		$this->object = new FinderIndexerDriverPostgresql;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
	}

	/**
	 * @todo   Implement testIndex().
	 */
	public function testIndex()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.'
		);
	}

	/**
	 * @todo   Implement testRemove().
	 */
	public function testRemove()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.'
		);
	}

	/**
	 * @todo   Implement testOptimize().
	 */
	public function testOptimize()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.'
		);
	}
}
