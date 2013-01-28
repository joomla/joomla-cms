<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/JDatabaseQuerySqlsrvInspector.php';
require_once JPATH_PLATFORM . '/joomla/database/query/sqlsrv.php';

/**
 * Test class for JDatabaseSqlsrvQuery.
*
* @package     Joomla.UnitTest
* @subpackage  Database
*
* @since       11.3
*/
class JDatabaseSqlsrvQueryTest extends TestCase
{
	/**
	 * @var  JDatabase  A mock of the JDatabase object for testing purposes.
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseSqlsrvQuery
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

		$this->_instance = new JDatabaseQuerySqlsrvInspector($this->dbo);
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
	 * Tests the JDatabaseSqlsrvQuery::DateAdd method
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
}
