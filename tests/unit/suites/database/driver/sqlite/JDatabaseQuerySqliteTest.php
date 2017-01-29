<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseQuerySqlite.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.3
 */
class JDatabaseQuerySqliteTest extends TestCase
{
	/**
	 * @var    JDatabaseDriverSqlite  A mock of the JDatabaseDriver object for testing purposes.
	 * @since  13.1
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseQuerySqlite
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->dbo = $this->getMockDatabase('Sqlite');

		$this->_instance = new JDatabaseQuerySqlite($this->dbo);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     PHPUnit_Framework_TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->dbo);
		unset($this->_instance);
		parent::tearDown();
	}

	/**
	 * Data for the testDateAdd test.
	 *
	 * @return  array
	 *
	 * @since   13.1
	 */
	public function seedDateAdd()
	{
		return array(
			// date, interval, datepart, expected
			'Add date'			=> array('2008-12-31', '1', 'DAY', "datetime('2008-12-31', '+1 DAY')"),
			'Subtract date'		=> array('2008-12-31', '-1', 'DAY', "datetime('2008-12-31', '-1 DAY')"),
			'Add datetime'		=> array('2008-12-31 23:59:59', '1', 'DAY', "datetime('2008-12-31 23:59:59', '+1 DAY')"),
			'Add microseconds'	=> array('2008-12-31 23:59:59', '53', 'microseconds', "datetime('2008-12-31 23:59:59', '+0.053 seconds')"),
		);
	}

	/**
	 * Tests the JDatabaseSqliteQuery::DateAdd method
	 *
	 * @param   datetime  $date      The date or datetime to add to.
	 * @param   string    $interval  The maximum length of the text.
	 * @param   string    $datePart  The part of the date to be added to (such as day or micosecond)
	 * @param   string    $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  seedDateAdd
	 * @since   13.1
	 */
	public function testDateAdd($date, $interval, $datePart, $expected)
	{
		$this->assertEquals(
			$expected,
			$this->_instance->dateAdd($date, $interval, $datePart)
		);
	}

	/**
	 * Tests the JDatabaseQuerySqlite::currentTimestamp method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuerySqlite::currentTimestamp
	 * @since   3.4
	 */
	public function testCurrentTimestamp()
	{
		$this->assertEquals(
			'CURRENT_TIMESTAMP',
			$this->_instance->currentTimestamp()
		);
	}

	/**
	 * Test for the JDatabaseQuerySqlite::__string method for a 'selectRowNumber' case.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test__toStringSelectRowNumber()
	{
		$this->_instance
			->select('id')
			->selectRowNumber('ordering', 'new_ordering')
			->from('a')
			->where('catid = 1');

		$this->assertEquals(
			PHP_EOL . "SELECT w.*, ROW_NUMBER() AS new_ordering" .
			PHP_EOL . "FROM (" .
			PHP_EOL . "SELECT id" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering" .
			PHP_EOL . ") AS w,(SELECT ROW_NUMBER(0)) AS r" .
			PHP_EOL . "ORDER BY NULL",
			(string) $this->_instance
		);

		$this->_instance
			->clear()
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'))
			->select('id')
			->from('a')
			->where('catid = 1');

		$this->assertEquals(
			PHP_EOL . "SELECT w.*, ROW_NUMBER() AS `ordering`" .
			PHP_EOL . "FROM (" .
			PHP_EOL . "SELECT id" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering DESC" .
			PHP_EOL . ") AS w,(SELECT ROW_NUMBER(0)) AS r" .
			PHP_EOL . "ORDER BY NULL",
			(string) $this->_instance
		);

		$this->_instance
			->clear('select')
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'));

		$this->assertEquals(
			PHP_EOL . "SELECT ROW_NUMBER() AS `ordering`" .
			PHP_EOL . "FROM (" .
			PHP_EOL . "SELECT 1" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering DESC" .
			PHP_EOL . ") AS w,(SELECT ROW_NUMBER(0)) AS r" .
			PHP_EOL . "ORDER BY NULL",
			(string) $this->_instance
		);
	}
}
