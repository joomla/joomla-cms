<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseQueryMysqli.
*
* @package     Joomla.UnitTest
* @subpackage  Database
* @since       3.7.0
*/
class JDatabaseQueryMysqliTest extends TestCase
{
	/**
	 * @var    JDatabaseDriver  A mock of the JDatabaseDriver object for testing purposes.
	 * @since  13.1
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseQueryMysqli
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

		$this->_instance = new JDatabaseQueryMysqli($this->dbo);
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @see     \PHPUnit\Framework\TestCase::tearDown()
	 * @since   3.6
	 */
	protected function tearDown()
	{
		unset($this->dbo);
		unset($this->_instance);
		parent::tearDown();
	}

	/**
	 * Test for the JDatabaseQueryMysqli::__string method for a 'selectRowNumber' case.
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
			PHP_EOL . "SELECT * FROM (" .
			PHP_EOL . "SELECT id,(SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS new_ordering" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering" .
			PHP_EOL . ") w",
			(string) $this->_instance
		);

		$this->_instance
			->clear()
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'))
			->select('id')
			->from('a')
			->where('catid = 1');

		$this->assertEquals(
			PHP_EOL . "SELECT * FROM (" .
			PHP_EOL . "SELECT (SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS `ordering`,id" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering DESC" .
			PHP_EOL . ") w",
			(string) $this->_instance
		);

		$this->_instance
			->clear('select')
			->selectRowNumber('ordering DESC', $this->_instance->quoteName('ordering'));

		$this->assertEquals(
			PHP_EOL . "SELECT * FROM (" .
			PHP_EOL . "SELECT (SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS `ordering`" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "WHERE catid = 1" .
			PHP_EOL . "ORDER BY ordering DESC" .
			PHP_EOL . ") w",
			(string) $this->_instance
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
			PHP_EOL . "UPDATE #__foo AS a" .
			PHP_EOL . "INNER JOIN b ON b.id = a.id" .
			PHP_EOL . "SET a.id = 2" .
			PHP_EOL . "WHERE b.id = 1",
			$string
		);

		// Run method __toString() again on the same query
		$this->assertEquals(
			$string,
			(string) $this->_instance
		);
	}
}
