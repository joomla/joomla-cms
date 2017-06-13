<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseIteratorPostgresql.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       12.1
 */
class JDatabaseIteratorPostgresqlTest extends TestCaseDatabasePostgresql
{
	/**
	 * Data provider for the testForEach method
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	public function casesForEachData()
	{
		return [
			// Testing 'stdClass' type without specific index, offset or limit
			[
				'title',
				'#__dbtest',
				null,
				'stdClass',
				0,
				0,
				[
					(object) ['title' => 'Testing'],
					(object) ['title' => 'Testing2'],
					(object) ['title' => 'Testing3'],
					(object) ['title' => 'Testing4']
				],
				null
			],

			// Testing 'stdClass' type, limit=2 without specific index or offset
			[
				'title',
				'#__dbtest',
				null,
				'stdClass',
				2,
				0,
				[
					(object) ['title' => 'Testing'],
					(object) ['title' => 'Testing2']
				],
				null
			],

			// Testing 'stdClass' type, offset=2 without specific index or limit
			[
				'title',
				'#__dbtest',
				null,
				'stdClass',
				20,
				2,
				[
					(object) ['title' => 'Testing3'],
					(object) ['title' => 'Testing4']
				],
				null
			],

			// Testing 'stdClass' type, index='title' without specific offset or limit
			[
				'title, id',
				'#__dbtest',
				'title',
				'stdClass',
				0,
				0,
				[
					'Testing'  => (object) ['title' => 'Testing', 'id' => '1'],
					'Testing2' => (object) ['title' => 'Testing2', 'id' => '2'],
					'Testing3' => (object) ['title' => 'Testing3', 'id' => '3'],
					'Testing4' => (object) ['title' => 'Testing4', 'id' => '4']
				],
				null,
			],

			// Testing 'UnexistingClass' type, index='title' without specific offset or limit
			[
				'title',
				'#__dbtest',
				'title',
				'UnexistingClass',
				0,
				0,
				[],
				'InvalidArgumentException',
			],
		];
	}

	/**
	 * Test foreach control
	 *
	 * @param   string   $select     Fields to select
	 * @param   string   $from       Table to search for
	 * @param   string   $column     The column to use as a key.
	 * @param   string   $class      The class on which to bind the result rows.
	 * @param   integer  $limit      The result set record limit.
	 * @param   integer  $offset     The result set record offset.
	 * @param   array    $expected   Array of expected results
	 * @param   mixed    $exception  Exception thrown
	 *
	 * @return  void
	 *
	 * @dataProvider casesForEachData
	 *
	 * @since    12.1
	 */
	public function testForEach($select, $from, $column, $class, $limit, $offset, $expected, $exception)
	{
		if ($exception)
		{
			$this->setExpectedException($exception);
		}

		self::$driver->setQuery(self::$driver->getQuery(true)->select($select)->from($from), $offset, $limit);
		$iterator = self::$driver->getIterator($column, $class);

		// Run the Iterator pattern
		$this->assertEquals($expected,iterator_to_array($iterator));
	}

	/**
	 * Test count
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testCount()
	{
		self::$driver->setQuery(self::$driver->getQuery(true)->select('title')->from('#__dbtest'));
		$this->assertCount(4, self::$driver->getIterator());

		self::$driver->setQuery(self::$driver->getQuery(true)->select('title')->from('#__dbtest'), 0, 2);
		$this->assertCount(2, self::$driver->getIterator());

		self::$driver->setQuery(self::$driver->getQuery(true)->select('title')->from('#__dbtest'), 3, 2);
		$this->assertCount(1, self::$driver->getIterator());
	}
}
