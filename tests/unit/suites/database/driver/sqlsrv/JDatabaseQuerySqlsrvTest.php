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
	 * Test for the JDatabaseQuerySqlsrv::__splitSqlExpression method.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test__splitSqlExpression()
	{
		$columns = ' DISTINCT -[catid] AS [\\"] '
			. ',/* /*/a.id/*"*//*x */'
			. ', a .title /*  */AS [a\'title]]x]'
			. ',\'\'\'\'\'\' /* AS */ '
			. ',[catid] AS """" '
			. ",-- x'x\n# comment(x\na.lft/* AS x*/ AS lft  /**/  "
			. ',LOWER(SUBSTRING(/*x.title(*/a.language, /**/1,/*,,x.title,,*/ 7)) AS columnAlias3'
			. ',LOWER("const(a,b,c,/*COMent in string STAY*/") "alias"'
			. ",/**/+ /**/ +/**/ /**/- +/**/a.end /**/ + /**/ 1-3"
			. ",'sta''tic' 'static'"
			. ", 1. AS x";

		$expected = array(
			'DISTINCT - [catid] AS [\\"]',
			'a.id',
			'a.title AS [a\'title]]x]',
			'\'\'\'\'\'\'',
			'[catid] AS """"',
			'a.lft AS lft',
			'LOWER(SUBSTRING(a.language, 1, 7)) AS columnAlias3',
			'LOWER("const(a,b,c,/*COMent in string STAY*/") "alias"',
			'++-+ a.end + 1 - 3',
			"'sta''tic' 'static'",
			'1. AS x',
		);

		$columns = TestReflection::invoke($this->_instance, 'splitSqlExpression', $columns);

		foreach ($columns as $i => $column)
		{
			$columns[$i] = implode(' ', $column);
		}

		$this->assertEquals(
			$expected,
			$columns
		);

		$columns = "ALL-[catid] AS []]] "
			. ', - id';

		$expected = array(
			'ALL - [catid] AS []]]',
			'- id',
		);

		$columns = TestReflection::invoke($this->_instance, 'splitSqlExpression', $columns);

		foreach ($columns as $i => $column)
		{
			$columns[$i] = implode(' ', $column);
		}

		$this->assertEquals(
			$expected,
			$columns
		);
	}

	/**
	 * Test for the JDatabaseQuerySqlsrv::fixSelectAliases method.
	 *
	 * @return  JDatabaseQuerySqlsrv
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test__fixSelectAliases()
	{
		$this->_instance->select('*'
			. ',a.*'
			. ',id_9'
			. ',a.id/*()*/'
			. ',a.title AS atitle'
			. ',  [a] ./**/ [column] /**/ALIAS_  '
			. ',#__content.id id'
			. ',#__content.id'
			. ',\'static\' as  "alias"'
			. ',\'static\' \'static\''
			. ',LOWER(SUBSTRING(a.language, 1, 7)) AS [very lower]'
			. ',LOWER("const(a,b,c")'
			. ",DAY('2016-12-31') +\na.end"
			. ',a +*+b'
			. ',[a] *b/**/ +([a] / /**/[b]) + + /**/ +[c]/**/ -\'static\''
			. ',\'static\'+ \'static\''
			. ',\'static\''
			. ', 0'
			. ', 1.1 + + + 1'
			. ', COUNT(#__content.*)'
			. ', CAST(e AS d_type(10)) + \'\''
			. ', NULL'
			. ', @@IDENTITY'
			. ', -a.id'
			. ', eNULL'
			. ','
		);

		$expected = array(
			'*',
			'a.*',
			'id_9',
			'a.id',
			'a.title AS atitle',
			'[a]. [column] AS ALIAS_',
			'#__content.id AS id',
			'#__content.id',
			'\'static\' AS "alias"',
			'\'static\' AS \'static\'',
			'LOWER(SUBSTRING(a.language, 1, 7)) AS [very lower]',
			'LOWER("const(a,b,c") AS [columnAlias11]',
			'DAY(\'2016-12-31\') + a.end AS [columnAlias12]',
			'a +*+ b AS [columnAlias13]',
			'[a] * b + ([a] / [b]) +++ [c] - \'static\' AS [columnAlias14]',
			'\'static\' + \'static\' AS [columnAlias15]',
			'\'static\' AS [columnAlias16]',
			'0 AS [columnAlias17]',
			'1.1 +++ 1 AS [columnAlias18]',
			'COUNT(#__content.*) AS [columnAlias19]',
			'CAST(e AS d_type(10)) + \'\' AS [columnAlias20]',
			'NULL AS [columnAlias21]',
			'@@IDENTITY AS [columnAlias22]',
			'- a.id AS [columnAlias23]',
			'eNULL',
			'',
		);

		TestReflection::invoke($this->_instance, 'fixSelectAliases');

		$this->assertEquals(
			$expected,
			$this->_instance->select->getElements()
		);

		$this->_instance
			->clear()
			->select('DISTINCT + + + id'
			. ", - +- +-a . [id_9]"
			. ", - +- +-a. [id_9]"
			. ", - +- +-a .[id_9]"
			. ", - +- +-a.[id_9]"
			. ", + - + ix"
			. ", ++ ix"
		);

		$expected = array(
			'DISTINCT +++ id',
			'-+-+- a. [id_9] AS [columnAlias1]',
			'-+-+- a. [id_9] AS [columnAlias2]',
			'-+-+- a.[id_9] AS [columnAlias3]',
			'-+-+- a.[id_9] AS [columnAlias4]',
			'+-+ ix AS [columnAlias5]',
			'++ ix',
		);

		TestReflection::invoke($this->_instance, 'fixSelectAliases');

		$this->assertEquals(
			$expected,
			$this->_instance->select->getElements()
		);

		$this->_instance
			->clear()
			->select('DISTINCT - + + + [a] . id'
			. ", [a] . [id_9]"
			. ", c + /**/ + [a].[b]"
			. ", [a].[b] + c"
		);

		$expected = array(
			'DISTINCT -+++ [a]. id AS [columnAlias0]',
			'[a]. [id_9]',
			'c ++ [a].[b] AS [columnAlias2]',
			'[a].[b] + c AS [columnAlias3]',
		);

		TestReflection::invoke($this->_instance, 'fixSelectAliases');

		$this->assertEquals(
			$expected,
			$this->_instance->select->getElements()
		);

		$this->_instance
			->clear()
			->select('DISTINCT +id'
			. ", ''+a . id_9 'alias'"
		);

		$expected = array(
			'DISTINCT + id',
			"'' + a. id_9 AS 'alias'",
		);

		TestReflection::invoke($this->_instance, 'fixSelectAliases');

		$this->assertEquals(
			$expected,
			$this->_instance->select->getElements()
		);
	}

	/**
	 * Test for the JDatabaseQuerySqlsrv::fixGroupColumns method.
	 *
	 * @return  JDatabaseQuerySqlsrv
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function test__fixGroupColumns()
	{
		$this->_instance
			->clear()
			->select('a.id, a.catid AS acatid, 1 AS state, COUNT(*), \'const\'\'\'\'text: \' + \'E\', \'"\' + a.name + \'"\'')
			->select('123 - (12 % 5) * 4')
			->select('+ 2, NULL, +\'s\'')
			->from('#__content AS a')
			->group('acatid');

		$this->assertEquals(
			PHP_EOL . 'SELECT a.id,a.catid AS acatid,1 AS state,COUNT(*) AS [columnAlias3]' .
			',\'const\'\'\'\'text: \' + \'E\' AS [columnAlias4],\'"\' + a.name + \'"\' AS [columnAlias5]' .
			',123 - (12 % 5) * 4 AS [columnAlias6]' .
			',+ 2 AS [columnAlias7],NULL AS [columnAlias8],+ \'s\' AS [columnAlias9]' .
			PHP_EOL . 'FROM #__content AS a' .
			PHP_EOL . "GROUP BY a.catid,a.id,'const''''text: ' + 'E','\"' + a.name + '\"',123 - (12 % 5) * 4",
			(string) $this->_instance
		);

		// Alias in GROUP BY statement
		$this->_instance
			->clear()
			->select('id, catid AS acatid, COUNT(*)')
			->from('#__content')
			->group('acatid');

		$this->assertEquals(
			PHP_EOL . 'SELECT id,catid AS acatid,COUNT(*) AS [columnAlias2]' .
			PHP_EOL . 'FROM #__content' .
			PHP_EOL . 'GROUP BY catid,id',
			(string) $this->_instance
		);

		// Column from ORDER BY will be added to GROUP BY
		$this->_instance
			->clear()
			->select('id, catid AS acatid, COUNT(*)')
			->from('#__content')
			->group('acatid')
			->order('hits');

		$this->assertEquals(
			PHP_EOL . 'SELECT id,catid AS acatid,COUNT(*) AS [columnAlias2]' .
			PHP_EOL . 'FROM #__content' .
			PHP_EOL . 'GROUP BY catid,id,hits' .
			PHP_EOL . 'ORDER BY hits',
			(string) $this->_instance
		);

		// Aggregate expression from ORDER BY will not be added to GROUP BY
		$this->_instance
			->clear()
			->select('[a].[id], c.id AS catid,c.name AS     [ x ]   , COUNT(*)  count')
			->from('#__content AS a')
			->innerJoin('(SELECT * FROM #__categories) c ON a.catid = c.id')
			->group('catid,[ x ]')
			->order('COUNT(*)');

		$this->assertEquals(
			PHP_EOL . 'SELECT [a].[id],c.id AS catid,c.name AS [ x ],COUNT(*) AS count' .
			PHP_EOL . 'FROM #__content AS a' .
			PHP_EOL . 'INNER JOIN (SELECT * FROM #__categories) c ON a.catid = c.id' .
			PHP_EOL . 'GROUP BY c.id,c.name,[a].[id]' .
			PHP_EOL . 'ORDER BY COUNT(*)',
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
			PHP_EOL . 'UPDATE a' .
			PHP_EOL . 'SET a.id = 2' .
			PHP_EOL . 'FROM #__foo AS a' .
			PHP_EOL . 'INNER JOIN b ON b.id = a.id' .
			PHP_EOL . 'WHERE b.id = 1',
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
