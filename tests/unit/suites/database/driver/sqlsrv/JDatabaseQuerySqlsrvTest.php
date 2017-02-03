<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseQuerySqlsrv.
*
* @package     Joomla.UnitTest
* @subpackage  Database
* @since       11.3
*/
class JDatabaseQuerySqlsrvTest extends TestCase
{
	/**
	 * @var    JDatabaseDriver  A mock of the JDatabaseDriver object for testing purposes.
	 * @since  13.1
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseQuerySqlsrv
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

		$this->dbo = $this->getMockDatabase();

		$this->_instance = new JDatabaseQuerySqlsrv($this->dbo);
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
			'Add date'			=> array('2008-12-31', '1', 'day', "DATEADD('day', '1', '2008-12-31')"),
			'Subtract date'		=> array('2008-12-31', '-1', 'day', "DATEADD('day', '-1', '2008-12-31')"),
			'Add datetime'		=> array('2008-12-31 23:59:59', '1', 'day', "DATEADD('day', '1', '2008-12-31 23:59:59')"),
		);
	}

	/**
	 * Tests the JDatabaseQuerySqlsrv::dateAdd method
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
		$this->assertThat(
			$this->_instance->dateAdd($date, $interval, $datePart),
			$this->equalTo($expected)
		);
	}

	/**
	 * Test for the JDatabaseQuerySqlsrv::__string method for a 'selectRowNumber' case.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function test__toStringSelectRowNumber()
	{
		$this->_instance
			->select('id')
			->selectRowNumber('ordering', 'new_ordering')
			->from('a')
			->where('catid = 1');

		$this->assertEquals(
			PHP_EOL . "SELECT id,ROW_NUMBER() OVER (ORDER BY ordering) AS new_ordering" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1",
			(string) $this->_instance
		);

		$this->_instance
			->clear()
			->select('id')
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'))
			->from('a')
			->where('catid = 1');

		$this->assertEquals(
			PHP_EOL . "SELECT id,ROW_NUMBER() OVER (ORDER BY ordering DESC) AS [ordering]" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1",
			(string) $this->_instance
		);

		$this->_instance
			->clear('select')
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'));

		$this->assertEquals(
			PHP_EOL . "SELECT ROW_NUMBER() OVER (ORDER BY ordering DESC) AS [ordering]" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1",
			(string) $this->_instance
		);
	}

	/**
	 * Test for the JDatabaseQuerySqlsrv::__processLimit method.
	 *
	 * @return  JDatabaseQuerySqlsrv
	 *
	 * @since   3.7.0
	 */
	public function test__processLimit()
	{
		$this->_instance
			->select('id, COUNT(*) AS count')
			->from('a')
			->where('id = 1');

		$this->assertEquals(
			PHP_EOL . 'SELECT id,COUNT(*) AS count' .
			PHP_EOL . 'FROM a' .
			PHP_EOL . 'WHERE id = 1',
			$this->_instance->processLimit((string) $this->_instance, 0)
		);

		$this->assertEquals(
			PHP_EOL . 'SELECT TOP 30 id,COUNT(*) AS count' .
			PHP_EOL . 'FROM a' .
			PHP_EOL . 'WHERE id = 1',
			$this->_instance->processLimit((string) $this->_instance, 30)
		);

		$this->assertEquals(
			PHP_EOL . 'SELECT * FROM (SELECT *, ROW_NUMBER() OVER (ORDER BY (SELECT 0)) AS RowNumber FROM (' .
			PHP_EOL . 'SELECT TOP 4 id,COUNT(*) AS count' .
			PHP_EOL . 'FROM a' .
			PHP_EOL . 'WHERE id = 1' .
			PHP_EOL . ') AS A) AS A WHERE RowNumber > 3',
			$this->_instance->processLimit((string) $this->_instance, 1, 3)
		);
	}

	/**
	 * Test for the JDatabaseQuery::__string method for a 'update' case.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function test__toStringUpdate()
	{
		$this->_instance
			->update('#__foo AS a')
			->join('INNER', 'b ON b.id = a.id')
			->set('a.id = 2')
			->where('b.id = 1');

		$string = (string) $this->_instance;

		$this->assertEquals(
			PHP_EOL . "UPDATE a" .
			PHP_EOL . "SET a.id = 2" .
			PHP_EOL . "FROM #__foo AS a" .
			PHP_EOL . "INNER JOIN b ON b.id = a.id" .
			PHP_EOL . "WHERE b.id = 1",
			$string
		);

		// Run method __toString() again on the same query
		$this->assertEquals(
			$string,
			(string) $this->_instance
		);
	}

	/**
	 * Mock quoteName method.
	 *
	 * @param   string  $value  The value to be quoted.
	 *
	 * @return  string  The value passed wrapped in MySQL quotes.
	 *
	 * @since   11.3
	 */
	public function mockQuoteName($value)
	{
		return "[$value]";
	}
}
