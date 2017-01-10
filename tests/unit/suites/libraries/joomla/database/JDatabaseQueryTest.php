<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require_once __DIR__ . '/JDatabaseQueryInspector.php';

/**
 * Test class for JDatabaseQuery.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Database
 * @since       11.1
 */
class JDatabaseQueryTest extends TestCase
{
	/**
	 * @var    JDatabaseDriver  A mock of the JDatabaseDriver object for testing purposes.
	 * @since  13.1
	 */
	protected $dbo;

	/**
	 * The instance of the object to test.
	 *
	 * @var    JDatabaseQuery
	 * @since  12.3
	 */
	private $_instance;

	/**
	 * Data for the testNullDate test.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function seedNullDateTest()
	{
		return array(
			// @todo quoted, expected
			array(true, "'_0000-00-00 00:00:00_'"),
			array(false, "0000-00-00 00:00:00"),
		);
	}

	/**
	 * Data for the testNullDate test.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function seedQuoteTest()
	{
		return array(
			// Text, escaped, expected
			array('text', false, "'text'"),
			array('text', true, "'_text_'"),
			array(array('text1', 'text2'), false, array("'text1'", "'text2'")),
			array(array('text1', 'text2'), true, array("'_text1_'", "'_text2_'")),
		);
	}

	/**
	 * Test for the JDatabaseQuery::__call method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__call()
	{
		$this->assertThat(
			$this->_instance->e('foo'),
			$this->equalTo($this->_instance->escape('foo')),
			'Tests the e alias of escape.'
		);

		$this->assertThat(
			$this->_instance->q('foo'),
			$this->equalTo($this->_instance->quote('foo')),
			'Tests the q alias of quote.'
		);

		$this->assertThat(
			$this->_instance->qn('foo'),
			$this->equalTo($this->_instance->quoteName('foo')),
			'Tests the qn alias of quoteName.'
		);

		$this->assertThat(
			$this->_instance->foo(),
			$this->isNull(),
			'Tests for an unknown method.'
		);
	}

	/**
	 * Test for the JDatabaseQuery::__get method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__get()
	{
		$this->_instance->select('*');
		$this->assertEquals('select', $this->_instance->type);
	}

	/**
	 * Test for FROM clause with subquery.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__toStringFrom_subquery()
	{
		$subq = new JDatabaseQueryInspector($this->dbo);
		$subq->select('col2')->from('table')->where('a=1');

		$this->_instance->select('col')->from($subq, 'alias');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "SELECT col" . PHP_EOL .
				"FROM ( " . PHP_EOL . "SELECT col2" . PHP_EOL . "FROM table" . PHP_EOL . "WHERE a=1 ) AS `alias`"
			)
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
		$subq = new JDatabaseQueryInspector($this->dbo);
		$subq->select('col2')->where('a=1');

		$this->_instance->insert('table')->columns('col')->values($subq);

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "INSERT INTO table" . PHP_EOL . "(col)" . PHP_EOL . "(" . PHP_EOL . "SELECT col2" . PHP_EOL . "WHERE a=1)")
		);

		$this->_instance->clear();
		$this->_instance->insert('table')->columns('col')->values('3');
		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "INSERT INTO table" . PHP_EOL . "(col) VALUES " . PHP_EOL . "(3)")
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
		$this->_instance->select($this->_instance->year($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT YEAR(`col`)" . PHP_EOL . "FROM table")
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
		$this->_instance->select($this->_instance->month($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT MONTH(`col`)" . PHP_EOL . "FROM table")
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
		$this->_instance->select($this->_instance->day($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT DAY(`col`)" . PHP_EOL . "FROM table")
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
		$this->_instance->select($this->_instance->hour($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT HOUR(`col`)" . PHP_EOL . "FROM table")
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
		$this->_instance->select($this->_instance->minute($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT MINUTE(`col`)" . PHP_EOL . "FROM table")
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
		$this->_instance->select($this->_instance->second($this->_instance->quoteName('col')))->from('table');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(PHP_EOL . "SELECT SECOND(`col`)" . PHP_EOL . "FROM table")
		);
	}

	/**
	 * Test for the JDatabaseQuery::__string method for a 'select' case.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function test__toStringSelect()
	{
		$this->_instance->select('a.id')
			->from('a')
			->innerJoin('b ON b.id = a.id')
			->where('b.id = 1')
			->group('a.id')
			->having('COUNT(a.id) > 3')
			->union('SELECT c.id FROM c')
			->unionAll('SELECT d.id FROM d')
			->order('id');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "SELECT a.id" .
					PHP_EOL . "FROM a" .
					PHP_EOL . "INNER JOIN b ON b.id = a.id" .
					PHP_EOL . "WHERE b.id = 1" .
					PHP_EOL . "GROUP BY a.id" .
					PHP_EOL . "HAVING COUNT(a.id) > 3" .
					PHP_EOL . "UNION (SELECT c.id FROM c)" .
					PHP_EOL . "UNION ALL (SELECT d.id FROM d)" .
					PHP_EOL . "ORDER BY id"
			),
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
		$this->_instance->update('#__foo AS a')
			->join('INNER', 'b ON b.id = a.id')
			->set('a.id = 2')
			->where('b.id = 1');

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "UPDATE #__foo AS a" .
					PHP_EOL . "INNER JOIN b ON b.id = a.id" .
					PHP_EOL . "SET a.id = 2" .
					PHP_EOL . "WHERE b.id = 1"
			),
			'Tests for correct rendering.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::call method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCall()
	{
		$this->assertSame($this->_instance, $this->_instance->call('foo'), 'Checks chaining');
		$this->_instance->call('bar');
		$this->assertEquals('CALL foo,bar', trim($this->_instance->call), 'Checks method by rendering.');
	}

	/**
	 * Tests the call property in  method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testCall__toString()
	{
		$this->assertEquals('CALL foo', trim($this->_instance->call('foo')), 'Checks method by rendering.');
	}

	/**
	 * Test for the castAsChar method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testCastAsChar()
	{
		$this->assertThat(
			$this->_instance->castAsChar('123'),
			$this->equalTo('123'),
			'The default castAsChar behaviour is to return the input.'
		);

	}

	/**
	 * Test for the charLength method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testCharLength()
	{
		$this->assertThat(
			$this->_instance->charLength('a.title'),
			$this->equalTo('CHAR_LENGTH(a.title)')
		);

		$this->assertThat(
			$this->_instance->charLength('a.title', '!=', '0'),
			$this->equalTo('CHAR_LENGTH(a.title) != 0')
		);

		$this->assertThat(
			$this->_instance->charLength('a.title', 'IS', 'NOT NULL'),
			$this->equalTo('CHAR_LENGTH(a.title) IS NOT NULL')
		);
	}

	/**
	 * Test for the clear method (clearing all types and clauses).
	 *
	 * @return  void
	 *
	 * @since   11.1
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
			'union',
			'unionAll',
			'exec',
			'call',
		);

		// First pass - set the values.
		foreach ($properties as $property)
		{
			TestReflection::setValue($this->_instance, $property, $property);
		}

		// Clear the whole query.
		$this->_instance->clear();

		// Check that all properties have been cleared
		foreach ($properties as $property)
		{
			$this->assertThat(
				$this->_instance->get($property),
				$this->equalTo(null)
			);
		}

		// And check that the type has been cleared.
		$this->assertThat(
			$this->_instance->type,
			$this->equalTo(null)
		);
	}

	/**
	 * Test for the clear method (clearing each clause).
	 *
	 * @return  void
	 *
	 * @since   11.1
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
			'union',
			'unionAll',
			'exec',
			'call',
		);

		// Test each clause.
		foreach ($clauses as $clause)
		{
			$q = new JDatabaseQueryInspector($this->dbo);

			// Set the clauses
			foreach ($clauses as $clause2)
			{
				TestReflection::setValue($q, $clause2, $clause2);
			}

			// Clear the clause.
			$q->clear($clause);

			// Check that clause was cleared.
			$this->assertThat(
				$q->get($clause),
				$this->equalTo(null)
			);

			// Check the state of the other clauses.
			foreach ($clauses as $clause2)
			{
				if ($clause != $clause2)
				{
					$this->assertThat(
						$q->get($clause2),
						$this->equalTo($clause2),
						"Clearing $clause resulted in $clause2 having a value of " . $q->get($clause2) . '.'
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
	 * @since   11.1
	 */
	public function testClear_type()
	{
		$types = array(
			'select',
			'delete',
			'update',
			'insert',
			'union',
			'unionAll',
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

		// Set the clauses.
		foreach ($clauses as $clause)
		{
			TestReflection::setValue($this->_instance, $clause, $clause);
		}

		// Check that all properties have been cleared
		foreach ($types as $type)
		{
			// Set the type.
			TestReflection::setValue($this->_instance, $type, $type);

			// Clear the type.
			$this->_instance->clear($type);

			// Check the type has been cleared.
			$this->assertThat(
				TestReflection::getValue($this->_instance, 'type'),
				$this->equalTo(null)
			);

			$this->assertThat(
				$this->_instance->get($type),
				$this->equalTo(null)
			);

			// Now check the claues have not been affected.
			foreach ($clauses as $clause)
			{
				$this->assertThat(
					$this->_instance->get($clause),
					$this->equalTo($clause)
				);
			}
		}
	}

	/**
	 * Tests the JDatabaseQuery::columns method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testColumns()
	{
		$this->assertThat(
			$this->_instance->columns('foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'columns')),
			$this->equalTo('(foo)'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->columns('bar');

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'columns')),
			$this->equalTo('(foo,bar)'),
			'Tests rendered value after second use.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::concatenate method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::concatenate
	 * @since   11.3
	 */
	public function testConcatenate()
	{
		$this->assertThat(
			$this->_instance->concatenate(array('foo', 'bar')),
			$this->equalTo('CONCATENATE(foo || bar)'),
			'Tests without separator.'
		);

		$this->assertThat(
			$this->_instance->concatenate(array('foo', 'bar'), ' and '),
			$this->equalTo("CONCATENATE(foo || '_ and _' || bar)"),
			'Tests without separator.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::currentTimestamp method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::currentTimestamp
	 * @since   11.3
	 */
	public function testCurrentTimestamp()
	{
		$this->assertThat(
			$this->_instance->currentTimestamp(),
			$this->equalTo('CURRENT_TIMESTAMP()')
		);
	}

	/**
	 * Tests the JDatabaseQuery::dateFormat method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::dateFormat
	 * @since   11.3
	 */
	public function testDateFormat()
	{
		$this->assertThat(
			$this->_instance->dateFormat(),
			$this->equalTo('Y-m-d H:i:s')
		);
	}

	/**
	 * Tests the JDatabaseQuery::dateFormat method for an expected exception.
	 *
	 * @return  void
	 *
	 * @covers             JDatabaseQuery::dateFormat
	 * @expectedException  RuntimeException
	 * @since              11.3
	 */
	public function testDateFormatException()
	{
		// Override the internal database for testing.
		TestReflection::setValue($this->_instance, 'db', new stdClass);

		$this->_instance->dateFormat();
	}

	/**
	 * Tests the JDatabaseQuery::delete method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testDelete()
	{
		$this->assertThat(
			$this->_instance->delete('#__foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			$this->_instance->type,
			$this->equalTo('delete'),
			'Tests the type property is set correctly.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'delete')),
			$this->equalTo('DELETE'),
			'Tests the delete element is set correctly.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'from')),
			$this->equalTo('FROM #__foo'),
			'Tests the from element is set correctly.'
		);
	}

	/**
	 * Tests the delete property in JDatabaseQuery::__toString method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testDelete__toString()
	{
		$this->_instance->delete('#__foo')
			->innerJoin('join')
			->where('bar=1');

		$this->assertEquals(
			implode(PHP_EOL, array('DELETE ', 'FROM #__foo', 'INNER JOIN join', 'WHERE bar=1')),
			trim($this->_instance)
		);
	}

	/**
	 * Tests the JDatabaseQuery::dump method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testDump()
	{
		$this->_instance->select('*')
			->from('#__foo');

		$this->assertThat(
			$this->_instance->dump(),
			$this->equalTo(
				'<pre class="jdatabasequery">' . PHP_EOL . "SELECT *" . PHP_EOL . "FROM foo" . '</pre>'
			),
			'Tests that the dump method replaces the prefix correctly.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::escape method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testEscape()
	{
		$this->assertThat(
			$this->_instance->escape('foo'),
			$this->equalTo('_foo_')
		);
	}

	/**
	 * Tests the JDatabaseQuery::escape method for an expected exception.
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              11.3
	 */
	public function testEscapeException()
	{
		// Override the internal database for testing.
		TestReflection::setValue($this->_instance, 'db', new stdClass);

		$this->_instance->escape('foo');
	}

	/**
	 * Tests the JDatabaseQuery::exec method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExec()
	{
		$this->assertSame($this->_instance, $this->_instance->exec('a.*'), 'Checks chaining');
		$this->_instance->exec('b.*');
		$this->assertEquals('EXEC a.*,b.*', trim(TestReflection::getValue($this->_instance, 'exec')), 'Checks method by rendering.');
	}

	/**
	 * Tests the exec property in JDatabaseQuery::__toString method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testExec__toString()
	{
		$this->assertEquals('EXEC a.*', trim($this->_instance->exec('a.*')));
	}

	/**
	 * Tests the JDatabaseQuery::from method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testFrom()
	{
		$this->assertThat(
			$this->_instance->from('#__foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim($this->_instance->from),
			$this->equalTo('FROM #__foo'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->from('#__bar');

		$this->assertThat(
			trim($this->_instance->from),
			$this->equalTo('FROM #__foo,#__bar'),
			'Tests rendered value after second use.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::group method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testGroup()
	{
		$this->assertThat(
			$this->_instance->group('foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim($this->_instance->group),
			$this->equalTo('GROUP BY foo'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->group('bar');

		$this->assertThat(
			trim($this->_instance->group),
			$this->equalTo('GROUP BY foo,bar'),
			'Tests rendered value after second use.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::having method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testHaving()
	{
		$this->assertThat(
			$this->_instance->having('COUNT(foo) > 1'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'having')),
			$this->equalTo('HAVING COUNT(foo) > 1'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->having('COUNT(bar) > 2');

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'having')),
			$this->equalTo('HAVING COUNT(foo) > 1 AND COUNT(bar) > 2'),
			'Tests rendered value after second use.'
		);

		// Reset the field to test the glue.
		TestReflection::setValue($this->_instance, 'having', null);
		$this->_instance->having('COUNT(foo) > 1', 'OR');
		$this->_instance->having('COUNT(bar) > 2');

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'having')),
			$this->equalTo('HAVING COUNT(foo) > 1 OR COUNT(bar) > 2'),
			'Tests rendered value with OR glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::innerJoin method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInnerJoin()
	{
		$q1 = new JDatabaseQueryInspector($this->dbo);
		$q2 = new JDatabaseQueryInspector($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertThat(
			$q1->innerJoin($condition),
			$this->identicalTo($q1),
			'Tests chaining.'
		);

		$q2->join('INNER', $condition);

		$this->assertThat(
			TestReflection::getValue($q1, 'join'),
			$this->equalTo(TestReflection::getValue($q2, 'join')),
			'Tests that innerJoin is an alias for join.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::insert method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testInsert()
	{
		$this->assertThat(
			$this->_instance->insert('#__foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			TestReflection::getValue($this->_instance, 'type'),
			$this->equalTo('insert'),
			'Tests the type property is set correctly.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'insert')),
			$this->equalTo('INSERT INTO #__foo'),
			'Tests the delete element is set correctly.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::join method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testJoin()
	{
		$this->assertThat(
			$this->_instance->join('INNER', 'foo ON foo.id = bar.id'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$data = TestReflection::getValue($this->_instance, 'join');
		$this->assertThat(
			trim($data[0]),
			$this->equalTo('INNER JOIN foo ON foo.id = bar.id'),
			'Tests that first join renders correctly.'
		);

		$this->_instance->join('OUTER', 'goo ON goo.id = car.id');

		$data = TestReflection::getValue($this->_instance, 'join');

		$this->assertThat(
			trim($data[1]),
			$this->equalTo('OUTER JOIN goo ON goo.id = car.id'),
			'Tests that second join renders correctly.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::leftJoin method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLeftJoin()
	{
		$q1 = new JDatabaseQueryInspector($this->dbo);
		$q2 = new JDatabaseQueryInspector($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertThat(
			$q1->leftJoin($condition),
			$this->identicalTo($q1),
			'Tests chaining.'
		);

		$q2->join('LEFT', $condition);

		$this->assertThat(
			TestReflection::getValue($q1, 'join'),
			$this->equalTo(TestReflection::getValue($q2, 'join')),
			'Tests that leftJoin is an alias for join.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::length method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testLength()
	{
		$this->assertThat(
			trim($this->_instance->length('foo')),
			$this->equalTo('LENGTH(foo)'),
			'Tests method renders correctly.'
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
	 * @dataProvider  seedNullDateTest
	 * @since         11.1
	 */
	public function testNullDate($quoted, $expected)
	{
		$this->assertThat(
			$this->_instance->nullDate($quoted),
			$this->equalTo($expected),
			'The nullDate method should be a proxy for the JDatabase::getNullDate method.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::nullDate method for an expected exception.
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              11.3
	 */
	public function testNullDateException()
	{
		// Override the internal database for testing.
		TestReflection::setValue($this->_instance, 'db', new stdClass);

		$this->_instance->nullDate();
	}

	/**
	 * Tests the JDatabaseQuery::order method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOrder()
	{
		$this->assertThat(
			$this->_instance->order('foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'order')),
			$this->equalTo('ORDER BY foo'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->order('bar');

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'order')),
			$this->equalTo('ORDER BY foo,bar'),
			'Tests rendered value after second use.'
		);

		$this->_instance->order(array('goo', 'car'));

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'order')),
			$this->equalTo('ORDER BY foo,bar,goo,car'),
			'Tests array input.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::outerJoin method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testOuterJoin()
	{
		$q1 = new JDatabaseQueryInspector($this->dbo);
		$q2 = new JDatabaseQueryInspector($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertThat(
			$q1->outerJoin($condition),
			$this->identicalTo($q1),
			'Tests chaining.'
		);

		$q2->join('OUTER', $condition);

		$this->assertThat(
			TestReflection::getValue($q1, 'join'),
			$this->equalTo(TestReflection::getValue($q2, 'join')),
			'Tests that outerJoin is an alias for join.'
		);
	}

	/**
	 * Tests the quote method.
	 *
	 * @param   boolean  $text      The value to be quoted.
	 * @param   boolean  $escape    True to escape the string, false to leave it unchanged.
	 * @param   string   $expected  The expected result.
	 *
	 * @return  void
	 *
	 * @since         11.1
	 * @dataProvider  seedQuoteTest
	 */
	public function testQuote($text, $escape, $expected)
	{
		$this->assertEquals($expected, $this->_instance->quote($text, $escape));
	}

	/**
	 * Tests the JDatabaseQuery::nullDate method for an expected exception.
	 *
	 * @return  void
	 *
	 * @expectedException  RuntimeException
	 * @since              11.3
	 */
	public function testQuoteException()
	{
		// Override the internal database for testing.
		TestReflection::setValue($this->_instance, 'db', new stdClass);

		$this->_instance->quote('foo');
	}

	/**
	 * Tests the quoteName method.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function testQuoteName()
	{
		$this->assertThat(
			$this->_instance->quoteName("test"),
			$this->equalTo("`test`"),
			'The quoteName method should be a proxy for the JDatabase::escape method.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::quoteName method for an expected exception.
	 *
	 * @return  void
	 *
	 * @covers             JDatabaseQuery::quoteName
	 * @expectedException  RuntimeException
	 * @since              11.3
	 */
	public function testQuoteNameException()
	{
		// Override the internal database for testing.
		TestReflection::setValue($this->_instance, 'db', new stdClass);

		$this->_instance->quoteName('foo');
	}

	/**
	 * Tests the JDatabaseQuery::rightJoin method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::rightJoin
	 * @since   11.3
	 */
	public function testRightJoin()
	{
		$q1 = new JDatabaseQueryInspector($this->dbo);
		$q2 = new JDatabaseQueryInspector($this->dbo);
		$condition = 'foo ON foo.id = bar.id';

		$this->assertThat(
			$q1->rightJoin($condition),
			$this->identicalTo($q1),
			'Tests chaining.'
		);

		$q2->join('RIGHT', $condition);

		$this->assertThat(
			TestReflection::getValue($q1, 'join'),
			$this->equalTo(TestReflection::getValue($q2, 'join')),
			'Tests that rightJoin is an alias for join.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::select method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::select
	 * @since   11.3
	 */
	public function testSelect()
	{
		$this->assertThat(
			$this->_instance->select('foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			$this->_instance->type,
			$this->equalTo('select'),
			'Tests the type property is set correctly.'
		);

		$this->assertThat(
			trim($this->_instance->select),
			$this->equalTo('SELECT foo'),
			'Tests the select element is set correctly.'
		);

		$this->_instance->select('bar');

		$this->assertThat(
			trim($this->_instance->select),
			$this->equalTo('SELECT foo,bar'),
			'Tests the second use appends correctly.'
		);

		$this->_instance->select(array('goo', 'car'));

		$this->assertThat(
			trim($this->_instance->select),
			$this->equalTo('SELECT foo,bar,goo,car'),
			'Tests the second use appends correctly.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::set method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::set
	 * @since   11.3
	 */
	public function testSet()
	{
		$this->assertThat(
			$this->_instance->set('foo = 1'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertEquals(
			'SET foo = 1',
			trim(TestReflection::getValue($this->_instance, 'set')),
			'Tests set with a string.'
		);

		$this->_instance->set('bar = 2');
		$this->assertEquals(
			"SET foo = 1\n\t, bar = 2",
			trim(TestReflection::getValue($this->_instance, 'set')),
			'Tests appending with set().'
		);

		// Clear the set.
		TestReflection::setValue($this->_instance, 'set', null);
		$this->_instance->set(array('foo = 1', 'bar = 2'));

		$this->assertEquals(
			"SET foo = 1\n\t, bar = 2",
			trim(TestReflection::getValue($this->_instance, 'set')),
			'Tests set with an array.'
		);

		// Clear the set.
		TestReflection::setValue($this->_instance, 'set', null);
		$this->_instance->set(array('foo = 1', 'bar = 2'), ';');

		$this->assertEquals(
			"SET foo = 1\n\t; bar = 2",
			trim(TestReflection::getValue($this->_instance, 'set')),
			'Tests set with an array and glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::setQuery method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetQuery()
	{
		$this->assertSame($this->_instance, $this->_instance->setQuery('Some SQL'), 'Check chaining.');
		$this->assertAttributeEquals('Some SQL', 'sql', $this->_instance, 'Checks the property was set correctly.');
		$this->assertEquals('Some SQL', (string) $this->_instance, 'Checks the rendering of the raw SQL.');
	}

	/**
	 * Tests rendering coupled with the JDatabaseQuery::setQuery method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testSetQuery__toString()
	{
		$this->assertEquals('Some SQL', trim($this->_instance->setQuery('Some SQL')));
	}

	/**
	 * Tests the JDatabaseQuery::update method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testUpdate()
	{
		$this->assertThat(
			$this->_instance->update('#__foo'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			$this->_instance->type,
			$this->equalTo('update'),
			'Tests the type property is set correctly.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'update')),
			$this->equalTo('UPDATE #__foo'),
			'Tests the update element is set correctly.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::values method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testValues()
	{
		$this->assertThat(
			$this->_instance->values('1,2,3'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'values')),
			$this->equalTo('(1,2,3)'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->values(array('4,5,6', '7,8,9'));

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'values')),
			$this->equalTo('(1,2,3),(4,5,6),(7,8,9)'),
			'Tests rendered value after second use and array input.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::where method.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function testWhere()
	{
		$this->assertThat(
			$this->_instance->where('foo = 1'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE foo = 1'),
			'Tests rendered value.'
		);

		// Add another column.
		$this->_instance->where(array('bar = 2', 'goo = 3'));

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE foo = 1 AND bar = 2 AND goo = 3'),
			'Tests rendered value after second use and array input.'
		);

		// Add more columns but specify different glue.
		// Note that the change of glue is ignored.
		$this->_instance->where(array('faz = 4', 'gaz = 5'), 'OR');
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE foo = 1 AND bar = 2 AND goo = 3 AND faz = 4 AND gaz = 5'),
			'Tests rendered value after third use, array input and different glue.'
		);

		// Clear the where
		TestReflection::setValue($this->_instance, 'where', null);
		$this->_instance->where(array('bar = 2', 'goo = 3'), 'OR');

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE bar = 2 OR goo = 3'),
			'Tests rendered value with glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::extendWhere method.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function testExtendWhere()
	{
		$this->assertThat(
			$this->_instance->where('foo = 1')->extendWhere('ABC', 'bar = 2'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '(foo = 1) ABC '
				. PHP_EOL . '(bar = 2)'),
			'Tests rendered value.'
		);

		// Add another set of where conditions.
		$this->_instance->extendWhere('XYZ', array('baz = 3', 'goo = 4'));
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) ABC '
				. PHP_EOL . '(bar = 2)) XYZ '
				. PHP_EOL . '(baz = 3 AND goo = 4)'),
			'Tests rendered value after second use and array input.'
		);

		// Add another set of where conditions with some different glue.
		$this->_instance->extendWhere('STU', array('faz = 5', 'gaz = 6'), 'VWX');
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) ABC '
				. PHP_EOL . '(bar = 2)) XYZ '
				. PHP_EOL . '(baz = 3 AND goo = 4)) STU '
				. PHP_EOL . '(faz = 5 VWX gaz = 6)'),
			'Tests rendered value after third use, array input and different glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::orWhere method.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function testOrWhere()
	{
		$this->assertThat(
			$this->_instance->where('foo = 1')->orWhere('bar = 2'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '(foo = 1) OR '
				. PHP_EOL . '(bar = 2)'),
			'Tests rendered value.'
		);

		// Add another set of where conditions.
		$this->_instance->orWhere(array('baz = 3', 'goo = 4'));
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) OR '
				. PHP_EOL . '(bar = 2)) OR '
				. PHP_EOL . '(baz = 3 AND goo = 4)'),
			'Tests rendered value after second use and array input.'
		);

		// Add another set of where conditions with some different glue.
		$this->_instance->orWhere(array('faz = 5', 'gaz = 6'), 'XOR');
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) OR '
				. PHP_EOL . '(bar = 2)) OR '
				. PHP_EOL . '(baz = 3 AND goo = 4)) OR '
				. PHP_EOL . '(faz = 5 XOR gaz = 6)'),
			'Tests rendered value after third use, array input and different glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::andWhere method.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function testAndWhere()
	{
		$this->assertThat(
			$this->_instance->where('foo = 1')->andWhere('bar = 2'),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);

		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '(foo = 1) AND '
				. PHP_EOL . '(bar = 2)'),
			'Tests rendered value.'
		);

		// Add another set of where conditions.
		$this->_instance->andWhere(array('baz = 3', 'goo = 4'));
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) AND '
				. PHP_EOL . '(bar = 2)) AND '
				. PHP_EOL . '(baz = 3 OR goo = 4)'),
			'Tests rendered value after second use and array input.'
		);

		// Add another set of where conditions with some different glue.
		$this->_instance->andWhere(array('faz = 5', 'gaz = 6'), 'XOR');
		$this->assertThat(
			trim(TestReflection::getValue($this->_instance, 'where')),
			$this->equalTo('WHERE '
				. PHP_EOL . '('
				. PHP_EOL . '('
				. PHP_EOL . '(foo = 1) AND '
				. PHP_EOL . '(bar = 2)) AND '
				. PHP_EOL . '(baz = 3 OR goo = 4)) AND '
				. PHP_EOL . '(faz = 5 XOR gaz = 6)'),
			'Tests rendered value after third use, array input and different glue.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::__clone method properly clones an array.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__clone_array()
	{
		$baseElement = new JDatabaseQueryInspector($this->getMockDatabase());

		$baseElement->testArray = array();

		$cloneElement = clone($baseElement);

		$baseElement->testArray[] = 'test';

		$this->assertNotSame($baseElement, $cloneElement);
		$this->assertCount(0, $cloneElement->testArray);
	}

	/**
	 * Tests the JDatabaseQuery::__clone method properly clones an object.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function test__clone_object()
	{
		$baseElement = new JDatabaseQueryInspector($this->getMockDatabase());

		$baseElement->testObject = new stdClass;

		$cloneElement = clone($baseElement);

		$this->assertNotSame($baseElement, $cloneElement);

		$this->assertNotSame($baseElement->testObject, $cloneElement->testObject);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionChain()
	{
		$this->assertThat(
			$this->_instance->union($this->_instance),
			$this->identicalTo($this->_instance),
			'Tests chaining.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionUnion()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union('SELECT name FROM foo');
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION (SELECT name FROM foo)"),
			'Tests rendered query with union.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionDistinctString()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union('SELECT name FROM foo', 'distinct');
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION DISTINCT (SELECT name FROM foo)"),
			'Tests rendered query with union distinct as a string.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionDistinctTrue()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union('SELECT name FROM foo', true);
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION DISTINCT (SELECT name FROM foo)"),
			'Tests rendered query with union distinct true.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionDistinctFalse()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union('SELECT name FROM foo', false);
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION (SELECT name FROM foo)"),
			'Tests rendered query with union distinct false.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionArray()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union(array('SELECT name FROM foo', 'SELECT name FROM bar'));
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION (SELECT name FROM foo)" . PHP_EOL . "UNION (SELECT name FROM bar)"),
			'Tests rendered query with two unions as an array.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionTwo()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union('SELECT name FROM foo');
		$this->_instance->union('SELECT name FROM bar');
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION (SELECT name FROM foo)" . PHP_EOL . "UNION (SELECT name FROM bar)"),
			'Tests rendered query with two unions sequentially.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::unionDistinct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionDistinct()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->unionDistinct('SELECT name FROM foo');
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			trim($teststring),
			$this->equalTo("UNION DISTINCT (SELECT name FROM foo)"),
			'Tests rendered query with unionDistinct.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::unionDistinct method.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUnionDistinctArray()
	{
		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->unionDistinct(array('SELECT name FROM foo', 'SELECT name FROM bar'));
		$teststring = (string) TestReflection::getValue($this->_instance, 'union');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION DISTINCT (SELECT name FROM foo)" . PHP_EOL . "UNION DISTINCT (SELECT name FROM bar)"),
			'Tests rendered query with two unions distinct.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method when passed a query object instead of a string.
	 *
	 * @return  void
	 *
	 * @since   12.??
	 */
	public function testUnionObject()
	{
		$this->_instance->select('name')->from('foo')->where('a=1');

		$q2 = new JDatabaseQueryInspector($this->dbo);
		$q2->select('name')->from('bar')->where('b=2');

		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union($q2);

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "SELECT name" . PHP_EOL .
				"FROM foo" . PHP_EOL .
				"WHERE a=1" . PHP_EOL .
				"UNION (" . PHP_EOL .
				"SELECT name" . PHP_EOL .
				"FROM bar" . PHP_EOL .
				"WHERE b=2)"
			)
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method when passed two query objects chained.
	 *
	 * @return  void
	 *
	 * @since   12.??
	 */
	public function testUnionObjectsChained()
	{
		$this->_instance->select('name')->from('foo')->where('a=1');

		$q2 = new JDatabaseQueryInspector($this->dbo);
		$q2->select('name')->from('bar')->where('b=2');

		$q3 = new JDatabaseQueryInspector($this->dbo);
		$q3->select('name')->from('baz')->where('c=3');

		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union($q2)->union($q3);

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "SELECT name" . PHP_EOL .
				"FROM foo" . PHP_EOL .
				"WHERE a=1" . PHP_EOL .
				"UNION (" . PHP_EOL .
				"SELECT name" . PHP_EOL .
				"FROM bar" . PHP_EOL .
				"WHERE b=2)" . PHP_EOL .
				"UNION (" . PHP_EOL .
				"SELECT name" . PHP_EOL .
				"FROM baz" . PHP_EOL .
				"WHERE c=3)"
			)
		);
	}

	/**
	 * Tests the JDatabaseQuery::union method when passed two query objects in an array.
	 *
	 * @return  void
	 *
	 * @since   12.??
	 */
	public function testUnionObjectsArray()
	{
		$this->_instance->select('name')->from('foo')->where('a=1');

		$q2 = new JDatabaseQueryInspector($this->dbo);
		$q2->select('name')->from('bar')->where('b=2');

		$q3 = new JDatabaseQueryInspector($this->dbo);
		$q3->select('name')->from('baz')->where('c=3');

		TestReflection::setValue($this->_instance, 'union', null);
		$this->_instance->union(array($q2, $q3));

		$this->assertThat(
			(string) $this->_instance,
			$this->equalTo(
				PHP_EOL . "SELECT name" . PHP_EOL .
				"FROM foo" . PHP_EOL .
				"WHERE a=1" . PHP_EOL .
				"UNION (" . PHP_EOL .
				"SELECT name" . PHP_EOL .
				"FROM bar" . PHP_EOL .
				"WHERE b=2)" . PHP_EOL .
				"UNION (" . PHP_EOL .
				"SELECT name" . PHP_EOL .
				"FROM baz" . PHP_EOL .
				"WHERE c=3)"
			)
		);
	}

	/**
	 * Tests the JDatabaseQuery::format method.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testFormat()
	{
		$result = $this->_instance->format('SELECT %n FROM %n WHERE %n = %a', 'foo', '#__bar', 'id', 10);
		$expected = 'SELECT ' . $this->_instance->qn('foo') . ' FROM ' . $this->_instance->qn('#__bar') .
			' WHERE ' . $this->_instance->qn('id') . ' = 10';
		$this->assertThat(
			$result,
			$this->equalTo($expected),
			'Line: ' . __LINE__ . '.'
		);

		$result = $this->_instance->format('SELECT %n FROM %n WHERE %n = %t OR %3$n = %Z', 'id', '#__foo', 'date');
		$expected = 'SELECT ' . $this->_instance->qn('id') . ' FROM ' . $this->_instance->qn('#__foo') .
			' WHERE ' . $this->_instance->qn('date') . ' = ' . $this->_instance->currentTimestamp() .
			' OR ' . $this->_instance->qn('date') . ' = ' . $this->_instance->nullDate(true);
		$this->assertThat(
			$result,
			$this->equalTo($expected),
			'Line: ' . __LINE__ . '.'
		);

	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->dbo = $this->getMockDatabase();

		$this->_instance = new JDatabaseQueryInspector($this->dbo);
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
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
			'Add date'		=> array('2008-12-31', '1', 'DAY', "DATE_ADD('2008-12-31', INTERVAL 1 DAY)"),
			'Subtract date'	=> array('2008-12-31', '-1', 'DAY', "DATE_ADD('2008-12-31', INTERVAL -1 DAY)"),
			'Add datetime'	=> array('2008-12-31 23:59:59', '1', 'DAY', "DATE_ADD('2008-12-31 23:59:59', INTERVAL 1 DAY)"),
		);
	}

	/**
	 * Tests the JDatabaseQuery::DateAdd method
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
	 * Tests the JDatabaseQuery::unionAll method.
	 *
	 * @return  void
	 *
	 * @covers  JDatabaseQuery::unionAll
	 * @since   13.1
	 */
	public function testUnionAllUnion()
	{
		TestReflection::setValue($this->_instance, 'unionAll', null);
		$this->_instance->unionAll('SELECT name FROM foo');
		$teststring = (string) TestReflection::getValue($this->_instance, 'unionAll');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION ALL (SELECT name FROM foo)"),
			'Tests rendered query with unionAll.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::unionAll method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUnionAllArray()
	{
		TestReflection::setValue($this->_instance, 'unionAll', null);
		$this->_instance->unionAll(array('SELECT name FROM foo', 'SELECT name FROM bar'));
		$teststring = (string) TestReflection::getValue($this->_instance, 'unionAll');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION ALL (SELECT name FROM foo)" . PHP_EOL . "UNION ALL (SELECT name FROM bar)"),
			'Tests rendered query with two union alls as an array.'
		);
	}

	/**
	 * Tests the JDatabaseQuery::unionAll method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 */
	public function testUnionAllTwo()
	{
		TestReflection::setValue($this->_instance, 'unionAll', null);
		$this->_instance->unionAll('SELECT name FROM foo');
		$this->_instance->unionAll('SELECT name FROM bar');
		$teststring = (string) TestReflection::getValue($this->_instance, 'unionAll');
		$this->assertThat(
			$teststring,
			$this->equalTo(PHP_EOL . "UNION ALL (SELECT name FROM foo)" . PHP_EOL . "UNION ALL (SELECT name FROM bar)"),
			'Tests rendered query with two union alls sequentially.'
		);
	}
}
