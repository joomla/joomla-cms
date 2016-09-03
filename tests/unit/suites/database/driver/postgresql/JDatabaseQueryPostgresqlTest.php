<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JDatabaseQueryPostgresql.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.3
 */
class JDatabaseQueryPostgresqlTest extends TestCase
{
	/**
	 * @var    JDatabaseDriver  A mock of the JDatabaseDriver object for testing purposes.
	 * @since  13.1
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseQueryPostgresql
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Data for the testNullDate test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestNullDate()
	{
		return array(
			// Quoted, expected
			array(true, "'_1970-01-01 00:00:00_'"),
			array(false, "1970-01-01 00:00:00"),
		);
	}

	/**
	 * Data for the testNullDate test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestQuote()
	{
		return array(
			// Text, escaped, expected
			array('text', false, '\'text\''),
		);
	}

	/**
	 * Data for the testJoin test.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	public function dataTestJoin()
	{
		return array(
			// $type, $conditions
			array('', 		'b ON b.id = a.id'),
			array('INNER',	'b ON b.id = a.id'),
			array('OUTER',	'b ON b.id = a.id'),
			array('LEFT',	'b ON b.id = a.id'),
			array('RIGHT',	'b ON b.id = a.id'),
		);
	}

	/**
	 * A mock callback for the database quoteName method.
	 *
	 * We use this method to ensure that JDatabaseQuery's quoteName method uses the
	 * the database object's quoteName method.
	 *
	 * @param   string  $text  The input text.
	 *
	 * @return  string
	 *
	 * @since   11.3
	 */
	public function mockQuoteName($text)
	{
		return '"' . $text . '"';
	}

	/**
	 * Callback for the dbo getQuery method.
	 *
	 * @param   boolean  $new  True to get a new query, false to get the last query.
	 *
	 * @return  JDatabaseQueryPostgresql
	 *
	 * @since   11.3
	 */
	public function mockGetQuery($new = false)
	{
		if ($new)
		{
			return new JDatabaseQueryPostgresql($this->dbo);
		}
		else
		{
			return $this->$lastQuery;
		}
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->dbo = $this->getMockDatabase('Postgresql', array(), '1970-01-01 00:00:00', 'Y-m-d H:i:s');

		$this->_instance = new JDatabaseQueryPostgresql($this->dbo);
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
	 * Test for the JDatabaseQueryPostgresql::__string method for a 'select' case.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__toStringSelect()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select('a.id')
			->from('a')
			->innerJoin('b ON b.id = a.id')
			->where('b.id = 1')
			->group('a.id')
				->having('COUNT(a.id) > 3')
			->order('a.id');

		$this->assertEquals(
			PHP_EOL . "SELECT a.id" .
			PHP_EOL . "FROM a" .
			PHP_EOL . "INNER JOIN b ON b.id = a.id" .
			PHP_EOL . "WHERE b.id = 1" .
			PHP_EOL . "GROUP BY a.id" .
			PHP_EOL . "HAVING COUNT(a.id) > 3" .
			PHP_EOL . "ORDER BY a.id",
			(string) $q,
			'Tests for correct rendering.'
		);
	}

	/**
	 * Test for the JDatabaseQuery::__string method for a 'update' case.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__toStringUpdate()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->update('#__foo AS a')
			->join('INNER', 'b ON b.id = a.id')
			->set('a.id = 2')
			->where('b.id = 1');

		$this->assertEquals(
			PHP_EOL . "UPDATE #__foo AS a" .
			PHP_EOL . "SET a.id = 2" .
			PHP_EOL . "FROM b" .
			PHP_EOL . "WHERE b.id = 1 AND b.id = a.id",
			(string) $q
		);
	}

	/**
	 * Test for year extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringYear()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->year($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (YEAR FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for month extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringMonth()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->month($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (MONTH FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for day extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringDay()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->day($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (DAY FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for hour extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringHour()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->hour($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (HOUR FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for minute extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringMinute()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->minute($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (MINUTE FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for seconds extraction from date.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function test__toStringSecond()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$q->select($q->second($q->quoteName('col')))->from('table');

		$this->assertEquals(
			PHP_EOL . "SELECT EXTRACT (SECOND FROM \"col\")" . PHP_EOL . "FROM table",
			(string) $q
		);
	}

	/**
	 * Test for INSERT INTO clause with subquery.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__toStringInsert_subquery()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);
		$subq = new JDatabaseQueryPostgresql($this->dbo);
		$subq->select('col2')->where('a=1');

		$q->insert('table')->columns('col')->values($subq);

		$this->assertEquals(
			PHP_EOL . "INSERT INTO table" . PHP_EOL . "(col)" . PHP_EOL . "(" . PHP_EOL . "SELECT col2" . PHP_EOL . "WHERE a=1)",
			(string) $q
		);

		$q->clear();
		$q->insert('table')->columns('col')->values('3');

		$this->assertEquals(
			PHP_EOL . "INSERT INTO table" . PHP_EOL . "(col) VALUES " . PHP_EOL . "(3)",
			(string) $q
		);
	}

	/**
	 * Test for the castAsChar method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCastAsChar()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			'123::text',
			$q->castAsChar('123')
		);

	}

	/**
	 * Test for the charLength method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testCharLength()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			'CHAR_LENGTH(a.title)',
			$q->charLength('a.title')
		);
	}

	/**
	 * Test chaining.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testChaining()
	{
		$q = $this->dbo->getQuery(true)->select('foo');

		$this->assertInstanceOf('JDatabaseQuery', $q);
	}

	/**
	 * Test for the clear method (clearing all types and clauses).
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClear_all()
	{
		$properties = array(
			'select',
			'delete',
			'update',
			'insert',
			'from',
			'join',
			'set',
			'where',
			'group',
			'having',
			'order',
			'columns',
			'values',
			'forShare',
			'forUpdate',
			'limit',
			'noWait',
			'offset',
			'returning',
		);

		$q = new JDatabaseQueryPostgresql($this->dbo);

		// First pass - set the values.
		foreach ($properties as $property)
		{
			TestReflection::setValue($q, $property, $property);
		}

		// Clear the whole query.
		$q->clear();

		// Check that all properties have been cleared
		foreach ($properties as $property)
		{
			$this->assertEmpty($q->$property);
		}

		// And check that the type has been cleared.
		$this->assertNull($q->type);
	}

	/**
	 * Test for the clear method (clearing each clause).
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClear_clause()
	{
		$clauses = array(
			'from',
			'join',
			'set',
			'where',
			'group',
			'having',
			'order',
			'columns',
			'values',
			'forShare',
			'forUpdate',
			'limit',
			'noWait',
			'offset',
			'returning',
		);

		// Test each clause.
		foreach ($clauses as $clause)
		{
			$q = new JDatabaseQueryPostgresql($this->dbo);

			// Set the clauses
			foreach ($clauses as $clause2)
			{
				TestReflection::setValue($q, $clause2, $clause2);
			}

			// Clear the clause.
			$q->clear($clause);

			// Check that clause was cleared.
			$this->assertNull($q->$clause);

			// Check the state of the other clauses.
			foreach ($clauses as $clause2)
			{
				if ($clause != $clause2)
				{
					$this->assertEquals(
						$clause2,
						$q->$clause2,
						"Clearing '$clause' resulted in '$clause2' having a value of " . $q->$clause2 . '.'
					);
				}
			}
		}
	}

	/**
	 * Test for the clear method (clearing each query type).
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testClear_type()
	{
		$types = array(
			'select',
			'delete',
			'update',
			'insert',
			'forShare',
			'forUpdate',
			'limit',
			'noWait',
			'offset',
			'returning',
		);

		$clauses = array(
			'from',
			'join',
			'set',
			'where',
			'group',
			'having',
			'order',
			'columns',
			'values',
		);

		$q = new JDatabaseQueryPostgresql($this->dbo);

		// Set the clauses.
		foreach ($clauses as $clause)
		{
			TestReflection::setValue($q, $clause, $clause);
		}

		// Check that all properties have been cleared
		foreach ($types as $type)
		{
			// Set the type.
			TestReflection::setValue($q, $type, $type);

			// Clear the type.
			$q->clear($type);

			// Check the type has been cleared.
			$this->assertNull(
				$q->type
			);

			$this->assertNull(
				$q->$type
			);

			// Now check the claues have not been affected.
			foreach ($clauses as $clause)
			{
				$this->assertEquals(
					$clause,
					$q->$clause
				);
			}
		}
	}

	/**
	 * Test for "concatenate" words.
	 *
	 * @return  void
	 */
	public function testConcatenate()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals('foo || bar', $q->concatenate(array('foo', 'bar')));

		$this->assertEquals("foo || '_ and _' || bar", $q->concatenate(array('foo', 'bar'), ' and '));
	}

	/**
	 * Test for FROM clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testFrom()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->from('#__foo'));

		$this->assertEquals('FROM #__foo', trim($q->from));

		// Add another column.
		$q->from('#__bar');

		$this->assertEquals('FROM #__foo,#__bar', trim($q->from));
	}

	/**
	 * Test for GROUP clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGroup()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->group('foo'));

		$this->assertEquals('GROUP BY foo', trim($q->group));

		// Add another column.
		$q->group('bar');

		$this->assertEquals('GROUP BY foo,bar', trim($q->group));
	}

	/**
	 * Test for HAVING clause using a simple condition and with glue for second one.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testHaving()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->having('COUNT(foo) > 1'));

		$this->assertEquals('HAVING COUNT(foo) > 1', trim($q->having));

		// Add another column.
		$q->having('COUNT(bar) > 2');

		$this->assertEquals('HAVING COUNT(foo) > 1 AND COUNT(bar) > 2', trim($q->having));

		// Reset the field to test the glue.
		$q->clear();
		$q->having('COUNT(foo) > 1', 'OR');
		$q->having('COUNT(bar) > 2');

		$this->assertEquals('HAVING COUNT(foo) > 1 OR COUNT(bar) > 2', trim($q->having));
	}

	/**
	 * Test for INNER JOIN clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInnerJoin()
	{
		$q  = new JDatabaseQueryPostgresql($this->dbo);
		$q2 = new JDatabaseQueryPostgresql($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertSame($q, $q->innerJoin($condition));

		$q2->join('INNER', $condition);

		$this->assertEquals(
			$q2->join,
			$q->join
		);
	}

	/**
	 * Test for JOIN clause using dataprovider to test all types of join.
	 *
	 * @param   string  $type        Type of JOIN, could be INNER, OUTER, LEFT, RIGHT
	 * @param   string  $conditions  Join condition
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider  dataTestJoin
	 */
	public function testJoin($type, $conditions)
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->join($type, $conditions));

		$type = empty($type) ? '' : $type . ' ';

		$this->assertEquals($type . 'JOIN ' . $conditions, trim($q->join[0]));
	}

	/**
	 * Test for LEFT JOIN clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLeftJoin()
	{
		$q  = new JDatabaseQueryPostgresql($this->dbo);
		$q2 = new JDatabaseQueryPostgresql($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertSame($q, $q->leftJoin($condition));

		$q2->join('LEFT', $condition);

		$this->assertEquals(
			$q2->join,
			$q->join
		);
	}

	/**
	 * Tests the quoteName method.
	 *
	 * @param   boolean  $quoted    The value of the quoted argument.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider  dataTestNullDate
	 */
	public function testNullDate($quoted, $expected)
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			$expected,
			$q->nullDate($quoted)
		);
	}

	/**
	 * Test for ORDER clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOrder()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->order('column'));

		$this->assertEquals('ORDER BY column', trim($q->order));

		// Add another column.
		$q->order('col2');

		$this->assertEquals('ORDER BY column,col2', trim($q->order));
	}

	/**
	 * Test for OUTER JOIN clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOuterJoin()
	{
		$q  = new JDatabaseQueryPostgresql($this->dbo);
		$q2 = new JDatabaseQueryPostgresql($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertSame($q, $q->outerJoin($condition));

		$q2->join('OUTER', $condition);

		$this->assertEquals(
			$q2->join,
			$q->join
		);
	}

	/**
	 * Tests the quoteName method.
	 *
	 * @param   boolean  $text      The value to be quoted.
	 * @param   boolean  $escape    True to escape the string, false to leave it unchanged.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @dataProvider  dataTestQuote
	 */
	public function testQuote($text, $escape, $expected)
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			$expected,
			$q->quote($text, $escape)
		);
	}

	/**
	 * Tests the quoteName method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testQuoteName()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			'"test"',
			$q->quoteName('test')
		);
	}

	/**
	 * Test for RIGHT JOIN clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testRightJoin()
	{
		$q  = new JDatabaseQueryPostgresql($this->dbo);
		$q2 = new JDatabaseQueryPostgresql($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertSame($q, $q->rightJoin($condition));

		$q2->join('RIGHT', $condition);

		$this->assertEquals(
			$q2->join,
			$q->join
		);
	}

	/**
	 * Test for SELECT clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testSelect()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->select('foo'));

		$this->assertEquals('SELECT foo', trim($q->select));

		// Add another column.
		$q->select('bar');

		$this->assertEquals('SELECT foo,bar', trim($q->select));

		$q->select(array('goo', 'car'));

		$this->assertEquals('SELECT foo,bar,goo,car', trim($q->select));
	}

	/**
	 * Test for WHERE clause using a simple condition and with glue for second one.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testWhere()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->where('foo = 1'));

		$this->assertEquals('WHERE foo = 1', trim($q->where));

		// Add another column.
		$q->where(array('bar = 2', 'goo = 3'));

		$this->assertEquals('WHERE foo = 1 AND bar = 2 AND goo = 3', trim($q->where));

		// Clear the where
		$q->clear();
		$q->where(array('bar = 2', 'goo = 3'), 'OR');

		$this->assertEquals('WHERE bar = 2 OR goo = 3', trim($q->where));
	}

	/**
	 * Tests the JDatabaseQueryPostgresql::escape method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testEscape()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertEquals(
			'_foo_',
			$q->escape('foo')
		);
	}

	/**
	 * Test for FOR UPDATE clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testForUpdate()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->forUpdate('#__foo'));

		$this->assertEquals('FOR UPDATE OF #__foo', trim($q->forUpdate));

		$q->forUpdate('#__bar');

		$this->assertEquals('FOR UPDATE OF #__foo, #__bar', trim($q->forUpdate));

		// Testing glue
		$q->clear();

		$q->forUpdate('#__foo', ';');
		$q->forUpdate('#__bar');

		$this->assertEquals('FOR UPDATE OF #__foo; #__bar', trim($q->forUpdate));
	}

	/**
	 * Test for FOR SHARE clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testForShare()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->forShare('#__foo'));

		$this->assertEquals('FOR SHARE OF #__foo', trim($q->forShare));

		$q->forShare('#__bar');

		$this->assertEquals('FOR SHARE OF #__foo, #__bar', trim($q->forShare));

		// Testing glue
		$q->clear();
		$q->forShare('#__foo', ';');
		$q->forShare('#__bar');

		$this->assertEquals('FOR SHARE OF #__foo; #__bar', trim($q->forShare));
	}

	/**
	 * Test for NOWAIT clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testNoWait()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->noWait());

		$this->assertEquals('NOWAIT', trim($q->noWait));
	}

	/**
	 * Test for LIMIT clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLimit()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->limit(5));

		$this->assertEquals('LIMIT 5', trim($q->limit));
	}

	/**
	 * Test for OFFSET clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOffset()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->offset(10));

		$this->assertEquals('OFFSET 10', trim($q->offset));
	}

	/**
	 * Test for RETURNING clause.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testReturning()
	{
		$q = new JDatabaseQueryPostgresql($this->dbo);

		$this->assertSame($q, $q->returning('id'));

		$this->assertEquals('RETURNING id', trim($q->returning));
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
			'Add date'		=> array('2008-12-31', '1', 'day', "timestamp '2008-12-31' + interval '1 day'"),
			'Subtract date'	=> array('2008-12-31', '-1', 'day', "timestamp '2008-12-31' - interval '1 day'"),
			'Add datetime'	=> array('2008-12-31 23:59:59', '1', 'day', "timestamp '2008-12-31 23:59:59' + interval '1 day'"),
		);
	}

	/**
	 * Tests the JDatabasePostgresqlQuery::DateAdd method
	 *
	 * @param   datetime  $date      The date or datetime to add to.
	 * @param   string    $interval  The maximum length of the text.
	 * @param   string    $datePart  The part of the date to be added to (such as day or micosecond).
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
}
